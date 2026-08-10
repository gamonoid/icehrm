#!/usr/bin/env bash
#
# Run every shell smoke test in this directory and summarise the result.
#
# Usage:  npm run test:smoke          (from the repo root or from e2e/)
#         sh e2e/smoke/run-all.sh     (directly)
#
# Any *.sh dropped in this directory is picked up automatically — no list to
# maintain here. Each smoke test exits 0 only when all of its assertions pass.
# Requires: the app running at the URL in e2e/.env, and the mysql container up.

set -uo pipefail
cd "$(dirname "$0")" || exit 1

pass=0
fail=0
failed=""

for t in *.sh; do
  # Don't recurse into this runner.
  [ "$t" = "run-all.sh" ] && continue
  echo
  echo "=================================================================="
  echo " SMOKE: $t"
  echo "=================================================================="
  if sh "$t"; then
    pass=$((pass + 1))
  else
    fail=$((fail + 1))
    failed="$failed $t"
  fi
done

if [ $((pass + fail)) -eq 0 ]; then
  echo "No smoke tests found in $(pwd)" >&2
  exit 1
fi

echo
echo "=================================================================="
echo " SMOKE SUMMARY: $pass passed, $fail failed"
[ -n "$failed" ] && echo " failed:$failed"
echo "=================================================================="
[ "$fail" -eq 0 ]
