#!/bin/bash
#
# Prepare an IceHRM release:
#   1. Verify VERSION_NUMBER was bumped with VERSION (the updater compares the number).
#   2. Build (and obfuscate) every JavaScript bundle via obfuscate-js.sh.
#   3. Run the Ant build (build.xml) which assembles + zips the release.
#
# Run from the repo root:  ./release.sh

set -e

cd "$(dirname "$0")"

# gulp lives under Node 20 on build machines — switch to it if nvm is present.
if [ -s "$HOME/.nvm/nvm.sh" ]; then
  # shellcheck disable=SC1091
  . "$HOME/.nvm/nvm.sh"
  nvm use 20 >/dev/null
fi
unset NODE_OPTIONS

echo "==> [1/3] Checking VERSION_NUMBER against VERSION (core/config.base.php)"
php ./test/version-number-check.php

echo "==> [2/3] Building JavaScript bundles (obfuscate-js.sh)"
bash ./obfuscate-js.sh

echo "==> [3/3] Running Ant build (build.xml)"
ant build

echo "==> Release build complete."
