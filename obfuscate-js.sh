#!/bin/bash
#
# Build every extension's JavaScript bundle (with --eprod = production/obfuscated).
#
# The per-extension targets are discovered automatically from BOTH extension
# roots — extensions/ and extensions-pro/ — so any newly added extension is
# always included: every directory containing web/js/index.js becomes a
# `gulp ejs --x<name>` target, and `gulp ejs` resolves <name> against either
# root. No manual list to keep in sync.

set -e

# Core + shared asset bundles (web/, web/admin, web/modules).
gulp --eprod
gulp pro-admin-js --eprod
gulp pro-modules-js --eprod

# The editor extension embeds a quiz sub-app that must be built before its bundle.
( cd extensions/editor/user/web/js/quiz && npm install && npm run build )

# Every extension part (admin/user) with a web/js entry, from both roots.
for root in extensions extensions-pro; do
  [ -d "$root" ] || continue
  while IFS= read -r entry; do
    part=${entry#"$root"/}
    part=${part%/web/js/index.js}
    echo "==> gulp ejs --x$part --eprod"
    gulp ejs --x"$part" --eprod
  done < <(find "$root" -type f -path "*/web/js/index.js" ! -path "*/node_modules/*" | sort)
done
