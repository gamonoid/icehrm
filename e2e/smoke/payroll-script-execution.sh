#!/usr/bin/env bash
#
# E2E: Payroll column JavaScript execution and validation
# --------------------------------------------------------
# Payroll columns (and data-import transforms) carry a JavaScript function that is
# evaluated per employee. It is executed by Utils\Js\JsSandbox — a PHP interpreter for a
# restricted JavaScript subset — which replaced the previous runner that shelled out to
# `node core/execute/execute.js` and evaluated the script inside vm2.
#
# This drives the endpoint the payroll-column editor uses (POST
# api/payroll_config/validate-script), which calls the same sandbox as a real payroll
# run, so it covers the whole path: HTTP -> ApiController -> JsValidator -> interpreter.
#
# Two halves:
#   [2..4]  EXECUTION — the stored functions and the author's samples compute correctly,
#           including the places where JavaScript and PHP disagree.
#   [5..7]  VALIDATION — a bad function is refused with a message that says what is
#           wrong and where, rather than a bare "invalid".
#
# Usage:  bash e2e/smoke/payroll-script-execution.sh
# Requires: the testing stack running (URL from e2e/.env) and the e2e accounts seeded.
#           No node: the sandbox is pure PHP, which is the point of it.

. "$(dirname "$0")/../helpers/smoke-lib.sh"

ENDPOINT='payroll_config/validate-script'

# The function stored on the GPSSA contribution columns.
GPSSA='var base = Number(gpssaBase)||0;\nvar rate = parseFloat(gpssaRate)||0;\nMath.round(base * rate * 100) / 100;'

echo "=================================================================="
echo " E2E: Payroll column JavaScript (execution + validation)"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Authenticate as admin"
# ------------------------------------------------------------------
login admin || die "cannot log in as admin"
TOKEN=$(api_token admin)
[ -n "$TOKEN" ] && ok "obtained a REST bearer token" || die "could not read a bearer token from $BASE/ui/"

# validate <label> <script-json-string> <parameters-json> -> RESP
call_validate() {
  api_post "$TOKEN" "$ENDPOINT" "{\"script\":\"$1\",\"parameters\":$2}"
}

# computes <label> <script> <parameters> <expected result>
computes() {
  local label="$1" script="$2" params="$3" want="$4" resp got valid
  resp=$(call_validate "$script" "$params")
  got=$(json_field "$resp" result)
  valid=$(json_field "$resp" valid)
  if [ "$got" = "$want" ] && [ "$valid" = "true" ]; then
    ok "$label -> $got"
  elif [ -z "$resp" ]; then
    bad "$label -> no response from $BASE/api/$ENDPOINT"
  else
    bad "$label -> got '$got' (valid=$valid), expected '$want'  [$(printf '%s' "$resp" | tr -d '\n' | head -c 200)]"
  fi
}

# refuses <label> <script> <parameters> <substring the message must contain>
refuses() {
  local label="$1" script="$2" params="$3" needle="$4" resp valid err
  resp=$(call_validate "$script" "$params")
  valid=$(json_field "$resp" valid)
  err=$(json_field "$resp" error)
  if [ "$valid" = "true" ]; then
    bad "$label -> was ACCEPTED (result $(json_field "$resp" result))"
  elif printf '%s' "$err" | grep -qi -- "$needle"; then
    ok "$label -> $err"
  else
    bad "$label -> refused, but the message does not mention '$needle': $err"
  fi
}

# ------------------------------------------------------------------
echo; echo "[Phase 2] The stored payroll functions compute correctly"
# ------------------------------------------------------------------
computes "GPSSA base=5000 rate=5   (int)"    "$GPSSA" '{"gpssaBase":5000,"gpssaRate":5}'    '25000'
computes "GPSSA base=1234 rate=7   (int)"    "$GPSSA" '{"gpssaBase":1234,"gpssaRate":7}'    '8638'
computes "GPSSA base=0    rate=5   (int)"    "$GPSSA" '{"gpssaBase":0,"gpssaRate":5}'       '0'
computes "GPSSA string inputs coerce"        "$GPSSA" '{"gpssaBase":"5000","gpssaRate":"5"}' '25000'
# 1234 * 0.0725 is 89.46499999999999 in IEEE-754, so *100 falls below the .5 boundary
# and rounds DOWN. Pinning the figure payroll actually produces, not the decimal ideal.
computes "GPSSA fractional rate (rounding)"  "$GPSSA" '{"gpssaBase":1234,"gpssaRate":0.0725}' '89.46'

computes "Overtime pay" \
  'var hourly = (Number(basic)||0) / 30 / 8;\nvar pay = hourly * 1.25 * (Number(otNormal)||0) + hourly * 1.5 * (Number(otPremium)||0);\nMath.round(pay * 100) / 100;' \
  '{"basic":12000,"otNormal":4,"otPremium":2}' '400'

computes "EOSB monthly accrual" \
  'var days = parseFloat(eosbDays)||0;\nvar b = Number(basic)||0;\nMath.round(((b / 30) * days / 12) * 100) / 100;' \
  '{"eosbDays":21,"basic":9905}' '577.79'

# ------------------------------------------------------------------
echo; echo "[Phase 3] Author-supplied samples, verbatim"
# ------------------------------------------------------------------
# gpssaBase = 5000, gpssaRate = 0.05  ->  250
SP='{"gpssaBase":5000,"gpssaRate":0.05}'

computes "sample 1: bare trailing expression" "$GPSSA" "$SP" '250'

computes "sample 2: declares a function and calls it" \
  'var base = Number(gpssaBase)||0;\nvar rate = parseFloat(gpssaRate)||0;\nfunction fun1(base1, rate1) {\n    return Math.round(base1 * rate1 * 100) / 100;\n}\n\nfun1(base, rate);' \
  "$SP" '250'

computes "sample 3: let/const, a local, Math.pow" \
  'let base = Number(gpssaBase)||0;\nconst rate = parseFloat(gpssaRate)||0;\nfunction fun1(base1, rate1) {\n    let r = Math.pow(10, 3);\n\n    return r + Math.round(base1 * rate1 * 100) / 100;\n}\n\nfun1(base, rate);' \
  "$SP" '1250'

computes "sample 4: irregular whitespace" \
  'let base = Number(gpssaBase)||     0;\nconst rate = parseFloat(gpssaRate)||0;\nfunction fun1(base1, rate1) {\n    let r = Math.pow(   10,     0);\n    \n    return r + Math.round(base1 * rate1 * 100) / 100;\n}\n\nfun1(base, rate);' \
  "$SP" '251'

# ------------------------------------------------------------------
echo; echo "[Phase 4] The wider language surface authors can use"
# ------------------------------------------------------------------
computes "helper functions calling one another" \
  'function gross(b, a) { return b + a; }\nfunction tax(g) { return g * 0.1; }\nMath.round((gross(1000, 200) - tax(gross(1000, 200))) * 100) / 100;' \
  '{}' '1080'

computes "loop, array and reduce" \
  'var xs = [];\nfor (var i = 1; i <= 5; i++) { xs.push(i * i); }\nxs.reduce(function (a, b) { return a + b; }, 0);' \
  '{}' '55'

computes "string methods" \
  "var s = ' a,b,c ';\\ns.trim().split(',').reverse().join('-').toUpperCase();" '{}' 'C-B-A'

# Single-quoted JS strings on purpose: the script is pasted into a JSON body, so a
# double quote here would terminate the JSON string and the request arrives with no
# script at all ("Script is required").
computes "guard clauses and ternary" \
  "function band(x) {\\n  if (x > 100) { return 'high'; }\\n  return x > 50 ? 'mid' : 'low';\\n}\\nband(120) + band(60) + band(10);" \
  '{}' 'highmidlow'

computes "modulo keeps the sign of the dividend" '-7 % 3;' '{}' '-1'

# ------------------------------------------------------------------
echo; echo "[Phase 5] Invalid functions are refused with a reason"
# ------------------------------------------------------------------
refuses "missing closing brace names the line" \
  'function f(x) {\n  return x * 2;\n' '{}' 'unclosed'

refuses "missing closing parenthesis" \
  'var a = Math.round(1.5;' '{}' 'Expected'

refuses "unterminated string" \
  "var a = 'abc;" '{}' 'Unterminated string'

refuses "an empty function" '   ' '{}' 'empty'

# ------------------------------------------------------------------
echo; echo "[Phase 6] Unsupported constructs say so plainly"
# ------------------------------------------------------------------
refuses "arrow functions"   'var f = (x) => x * 2;\nf(1);' '{}' 'arrow'
refuses "new"               'var d = new Object();'        '{}' 'new'
refuses "class"             'class Foo {}'                 '{}' 'class'
refuses "try/catch"         'try { 1; } catch (e) { 2; }'  '{}' 'try/catch'
refuses "recursion"         'function f(n) { return n <= 0 ? 0 : f(n - 1); }\nf(3);' '{}' 'Recursion is not allowed'
refuses "an endless loop"   'while (true) { }'             '{}' 'exceeded'

# ------------------------------------------------------------------
echo; echo "[Phase 7] A misspelled variable is caught — the silent failure"
# ------------------------------------------------------------------
# This is valid JavaScript that RUNS: `gpssRate` is undefined, `|| 0` turns it into 0,
# and the column computes 0 instead of the contribution with nothing logged anywhere.
# Only validation can catch it, so this is the assertion that matters most here.
resp=$(call_validate 'var base = Number(gpssaBase)||0;\nvar rate = parseFloat(gpssRate)||0;\nMath.round(base * rate * 100) / 100;' "$SP")
err=$(json_field "$resp" error)
kind=$(json_field "$resp" kind)
if [ "$kind" = "undefined-variable" ] && printf '%s' "$err" | grep -q 'gpssRate'; then
  ok "misspelled variable reported -> $err"
else
  bad "misspelled variable not reported (kind=$kind): $err"
fi

if printf '%s' "$err" | grep -q 'did you mean "gpssaRate"'; then
  ok "the intended variable name is suggested"
else
  bad "no suggestion offered for the near-miss: $err"
fi

# A function using only supplied variables must NOT be flagged.
computes "supplied variables are not reported as unknown" "$GPSSA" "$SP" '250'

finish "(endpoint: $ENDPOINT)"
