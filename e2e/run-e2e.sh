#!/bin/bash
# Run the IceHRM e2e suite while watching it in a browser.
#
# Usage (from the e2e/ directory):
#   ./run-e2e.sh              # all 34 specs, visible browser (headed), sequential
#   ./run-e2e.sh --ui         # Playwright UI mode: spec list + live preview + time-travel
#   ./run-e2e.sh --debug      # step through with the Playwright Inspector
#   ./run-e2e.sh ui-switch    # just one spec (any playwright test args pass through)
#   SLOWMO=300 ./run-e2e.sh   # slow every action by 300ms so it's easy to follow
#
# The app must already be running at the URL configured in e2e/.env
# (E2E_BASE_URL, default http://localhost:9080/app/ui/ — docker compose up).

cd "$(dirname "$0")" || exit 1

# UI / debug modes manage their own browser window — pass through untouched.
for arg in "$@"; do
  if [ "$arg" = "--ui" ] || [ "$arg" = "--debug" ]; then
    exec npx playwright test "$@"
  fi
done

# Default: headed run of everything (config is headless:true; --headed overrides).
exec npx playwright test --headed "$@"
