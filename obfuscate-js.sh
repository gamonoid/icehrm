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
#
# `find | sort | while` rather than `while ... done < <(find ...)`: process
# substitution is a bash extension, and POSIX mode disables it — so `sh obfuscate-js.sh`
# died with "syntax error near unexpected token `<'" on macOS, where /bin/sh is bash in
# POSIX mode. It failed only after the earlier build steps had run, because a shell does
# not parse a compound command until it reaches it.
#
# The pipeline runs the loop in a subshell, which is harmless here: nothing set inside
# is read afterwards. `set -e` still propagates a gulp failure — the subshell exits
# non-zero, and that is the pipeline's status.
for root in extensions extensions-pro; do
  [ -d "$root" ] || continue
  find "$root" -type f -path "*/web/js/index.js" ! -path "*/node_modules/*" | sort |
  while IFS= read -r entry; do
    part=${entry#"$root"/}
    part=${part%/web/js/index.js}
    echo "==> gulp ejs --x$part --eprod"
    gulp ejs --x"$part" --eprod
  done
done
