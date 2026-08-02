#!/bin/bash
#
# Prepare an IceHRM release:
#   1. Build (and obfuscate) every JavaScript bundle via obfuscate-js.sh.
#   2. Run the Ant build (build.xml) which assembles + zips the release.
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

echo "==> [1/2] Building JavaScript bundles (obfuscate-js.sh)"
bash ./obfuscate-js.sh

echo "==> [2/2] Running Ant build (build.xml)"
ant build

echo "==> Release build complete."
