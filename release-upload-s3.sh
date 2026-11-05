#!/bin/bash
#
# Publish a built IceHrm release zip to S3 as the updater's download:
#   s3://icehrm-public/upgrades/latest/icehrm.zip
#   (https://icehrm-public.s3.us-east-1.amazonaws.com/upgrades/latest/icehrm.zip,
#    FREE_URL in updater/lib/Package.php)
#
# Steps:
#   1. Validate the zip (integrity, VERSION/VERSION_NUMBER inside it match
#      core/config.base.php in this checkout).
#   2. Inspect the current latest/icehrm.zip; refuse a downgrade, skip if identical.
#   3. Back up the current latest to a local directory (for rollback).
#   4. Upload the new zip to upgrades/latest/icehrm.zip (no-cache, version + sha256
#      metadata), then best-effort to upgrades/v<version>/icehrm.zip.
#   5. Download latest through the public URL and verify its SHA-256.
#
# Usage (from the repo root, after ./release.sh):
#   ./release-upload-s3.sh [--zip build/release/icehrm.zip] [--profile s3-uploader]
#                          [--backup-dir ~/icehrm-release-backups]
#                          [--dry-run] [--force] [--yes]
#
#   --dry-run  show what would happen, write nothing to S3
#   --force    allow re-uploading an identical zip or a lower version
#   --yes      do not ask for confirmation before writing
#
# Needs only s3:GetObject + s3:PutObject on upgrades/latest/icehrm.zip
# (PutObject on upgrades/v*/ is optional). No ListBucket, tagging or copy rights.

set -euo pipefail

cd "$(dirname "$0")"

BUCKET="icehrm-public"
REGION="us-east-1"
PREFIX="upgrades"
LATEST_KEY="$PREFIX/latest/icehrm.zip"
PUBLIC_URL="https://$BUCKET.s3.$REGION.amazonaws.com/$LATEST_KEY"

ZIP="build/release/icehrm.zip"
PROFILE="${AWS_PROFILE:-s3-uploader}"
# Outside build/: the Ant build deletes build/ on every run.
BACKUP_DIR="$HOME/icehrm-release-backups"
DRY_RUN=0
FORCE=0
ASSUME_YES=0

while [ $# -gt 0 ]; do
  case "$1" in
    --zip) ZIP="$2"; shift 2 ;;
    --profile) PROFILE="$2"; shift 2 ;;
    --backup-dir) BACKUP_DIR="$2"; shift 2 ;;
    --dry-run) DRY_RUN=1; shift ;;
    --force) FORCE=1; shift ;;
    --yes|-y) ASSUME_YES=1; shift ;;
    -h|--help) sed -n '2,27p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) echo "Unknown option: $1" >&2; exit 2 ;;
  esac
done

die() { echo "ERROR: $*" >&2; exit 1; }
step() { echo; echo "==> $*"; }
sha256() { shasum -a 256 "$1" | awk '{print $1}'; }
aws_() { aws --profile "$PROFILE" --region "$REGION" "$@"; }
# VERSION / VERSION_NUMBER out of a config.base.php read on stdin.
read_define() { sed -n "s/.*define('$1', *'\([^']*\)').*/\1/p" | head -1; }
# Upload a local zip to an S3 key with release metadata.
put_zip() { # <local file> <key> <metadata> [extra aws args...]
  local file="$1" key="$2" metadata="$3"; shift 3
  aws_ s3 cp "$file" "s3://$BUCKET/$key" --content-type application/zip \
    --metadata "$metadata" --only-show-errors "$@"
}

for bin in aws unzip shasum curl python3; do
  command -v "$bin" >/dev/null || die "'$bin' is required"
done

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

# ---------------------------------------------------------------------------
step "[1/5] Validating $ZIP"
[ -f "$ZIP" ] || die "$ZIP not found - run ./release.sh first"
unzip -tq "$ZIP" >/dev/null || die "$ZIP is corrupt"

ZIP_CONFIG="$(unzip -p "$ZIP" icehrm/core/config.base.php 2>/dev/null)" \
  || die "icehrm/core/config.base.php missing from $ZIP"
NEW_VERSION="$(printf '%s' "$ZIP_CONFIG" | read_define VERSION)"
NEW_NUMBER="$(printf '%s' "$ZIP_CONFIG" | read_define VERSION_NUMBER)"
[ -n "$NEW_VERSION" ] && [ -n "$NEW_NUMBER" ] || die "could not read VERSION / VERSION_NUMBER from the zip"

REPO_VERSION="$(read_define VERSION < core/config.base.php)"
REPO_NUMBER="$(read_define VERSION_NUMBER < core/config.base.php)"
if [ "$NEW_VERSION" != "$REPO_VERSION" ] || [ "$NEW_NUMBER" != "$REPO_NUMBER" ]; then
  die "zip is $NEW_VERSION ($NEW_NUMBER) but core/config.base.php is $REPO_VERSION ($REPO_NUMBER) - rebuild with ./release.sh"
fi

NEW_SHA="$(sha256 "$ZIP")"
NEW_SIZE="$(wc -c < "$ZIP" | tr -d ' ')"
echo "   version  $NEW_VERSION ($NEW_NUMBER)"
echo "   size     $NEW_SIZE bytes"
echo "   sha256   $NEW_SHA"

# ---------------------------------------------------------------------------
step "[2/5] Inspecting current s3://$BUCKET/$LATEST_KEY (profile $PROFILE)"
aws_ sts get-caller-identity --query Arn --output text >/dev/null \
  || die "AWS credentials for profile '$PROFILE' are not working"

CUR_VERSION=""; CUR_NUMBER=""; CUR_SHA=""; HAVE_CURRENT=0
if aws_ s3api head-object --bucket "$BUCKET" --key "$LATEST_KEY" > "$TMP/head.json" 2>/dev/null; then
  HAVE_CURRENT=1
  CUR_SHA="$(python3 -c 'import json,sys; print(json.load(open(sys.argv[1])).get("Metadata",{}).get("sha256",""))' "$TMP/head.json")"
  if [ -n "$CUR_SHA" ] && [ "$CUR_SHA" = "$NEW_SHA" ] && [ "$FORCE" = 0 ]; then
    echo "   already this exact build (sha256 $CUR_SHA) - nothing to do (use --force to re-upload)."
    exit 0
  fi
  # Download it: needed for the rollback backup, and to read its version when the
  # object predates this script and carries no metadata.
  aws_ s3 cp "s3://$BUCKET/$LATEST_KEY" "$TMP/current.zip" --only-show-errors
  CUR_SHA="$(sha256 "$TMP/current.zip")"
  CUR_CONFIG="$(unzip -p "$TMP/current.zip" icehrm/core/config.base.php 2>/dev/null || true)"
  CUR_VERSION="$(printf '%s' "$CUR_CONFIG" | read_define VERSION)"
  CUR_NUMBER="$(printf '%s' "$CUR_CONFIG" | read_define VERSION_NUMBER)"
  echo "   version  ${CUR_VERSION:-unknown} (${CUR_NUMBER:-unknown})"
  echo "   sha256   $CUR_SHA"
else
  echo "   no current object (or not readable) - nothing to back up"
fi

if [ "$HAVE_CURRENT" = 1 ] && [ "$CUR_SHA" = "$NEW_SHA" ] && [ "$FORCE" = 0 ]; then
  echo; echo "latest/icehrm.zip is already this exact build - nothing to do (use --force to re-upload)."
  exit 0
fi
if [ -n "$CUR_NUMBER" ] && [ "$NEW_NUMBER" -lt "$CUR_NUMBER" ] && [ "$FORCE" = 0 ]; then
  die "refusing to downgrade latest from $CUR_VERSION to $NEW_VERSION (use --force to roll back on purpose)"
fi

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_FILE="$BACKUP_DIR/icehrm-${CUR_VERSION:-unknown}-$STAMP.zip"
VERSION_KEY="$PREFIX/v$NEW_VERSION/icehrm.zip"
METADATA="version=$NEW_VERSION,version-number=$NEW_NUMBER,sha256=$NEW_SHA"
ROLLBACK="aws --profile $PROFILE s3 cp '$BACKUP_FILE' s3://$BUCKET/$LATEST_KEY --content-type application/zip --cache-control 'no-cache, max-age=0'"

echo
echo "Plan:"
[ "$HAVE_CURRENT" = 1 ] && echo "   backup   s3://$BUCKET/$LATEST_KEY -> $BACKUP_FILE"
echo "   publish  $ZIP -> s3://$BUCKET/$LATEST_KEY"
echo "   archive  $ZIP -> s3://$BUCKET/$VERSION_KEY (best effort)"
if [ "$DRY_RUN" = 1 ]; then
  echo; echo "Dry run - nothing written."
  exit 0
fi

if [ "$ASSUME_YES" = 0 ]; then
  printf '\nReplace the live updater download with %s? [y/N] ' "$NEW_VERSION"
  read -r answer
  case "$answer" in y|Y|yes|YES) ;; *) echo "Aborted."; exit 1 ;; esac
fi

# ---------------------------------------------------------------------------
step "[3/5] Backing up the current latest"
if [ "$HAVE_CURRENT" = 1 ]; then
  mkdir -p "$BACKUP_DIR"
  mv "$TMP/current.zip" "$BACKUP_FILE"
  echo "   saved $BACKUP_FILE"
else
  echo "   skipped"
fi

# ---------------------------------------------------------------------------
step "[4/5] Uploading $NEW_VERSION"
put_zip "$ZIP" "$LATEST_KEY" "$METADATA" --cache-control "no-cache, max-age=0" \
  || die "upload to $LATEST_KEY failed - latest is unchanged"
echo "   published $LATEST_KEY"
if put_zip "$ZIP" "$VERSION_KEY" "$METADATA" 2>"$TMP/version-err"; then
  echo "   archived  $VERSION_KEY"
else
  echo "   WARNING: could not write $VERSION_KEY (skipped): $(tail -1 "$TMP/version-err" | cut -c1-160)"
fi

# ---------------------------------------------------------------------------
step "[5/5] Verifying the public download"
curl -fsS -o "$TMP/public.zip" "$PUBLIC_URL" || die "could not download $PUBLIC_URL"
PUBLIC_SHA="$(sha256 "$TMP/public.zip")"
if [ "$PUBLIC_SHA" != "$NEW_SHA" ]; then
  echo "ERROR: public download sha256 $PUBLIC_SHA does not match $NEW_SHA" >&2
  [ "$HAVE_CURRENT" = 1 ] && echo "Roll back with: $ROLLBACK" >&2
  exit 1
fi
echo "   $PUBLIC_URL"
echo "   sha256 matches ($PUBLIC_SHA)"

echo
echo "Done: latest/icehrm.zip is now $NEW_VERSION."
if [ "$HAVE_CURRENT" = 1 ]; then
  echo "Roll back with:"
  echo "   $ROLLBACK"
fi
echo "Remember: installations are only told about the update once the icehrm.com marketplace lists $NEW_VERSION."
