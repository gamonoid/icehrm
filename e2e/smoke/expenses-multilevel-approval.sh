#!/usr/bin/env bash
#
# E2E: Multi-level expense approval
# ----------------------------------
# Covers the full multi-level approval chain for an expense claim by user1:
#
#     user1 submits                          -> Pending, chain L1/L2/L3 initialised
#     manager (supervisor) approves          -> Processing  (activates level 1)
#     user4 (level 3) tries to jump the queue -> refused, still Processing
#     user2 (approver1, Manager)  approves   -> Processing  (activates level 2)
#     user3 (approver2, Employee) approves   -> Processing  (activates level 3)
#     user4 (approver3, Employee) approves   -> Approved     (chain complete)
#     user1 sees it Approved with all three levels recorded.
#
# As in the overtime chain, approver1 is a Manager and approver2/3 are Employees so
# that both authorisation layers are exercised: changeStatus authorising a designated
# approver who has no employee-data access to the claimant, and the a=ca dispatcher
# admitting an Employee to the admin=expenses module ref the SPA routes approvals
# through (the user module's Approvals tab is shown to Employees).
#
# Every expense status change also requires a reason — enforced by
# Expenses\Admin\Controller::changeStatus for all roles, so each step supplies one.
#
# Settings and user1's approver chain are captured on entry and restored on exit.
#
# Usage:  bash e2e/smoke/expenses-multilevel-approval.sh
# Requires: the testing stack running (URL from e2e/.env), the e2e accounts seeded,
#           and the expenses extension installed.

. "$(dirname "$0")/../helpers/smoke-lib.sh"

SETTING='Expense: Enable Multi Level Approvals'
PREAPPROVE='Expense: Pre-Approve Expenses'
MOD='admin=expenses'
MODEL='EmployeeExpense'
PAYEE="E2E Multi Vendor $$"

resolve_employees

ex_status() { dbq "SELECT status FROM EmployeeExpenses WHERE id=$1"; }
ex_levels() { dbq "SELECT GROUP_CONCAT(CONCAT('L',level,'=',status) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$1"; }

echo "=================================================================="
echo " E2E: Multi-level expense approval"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Setup preconditions (as admin)"
# ------------------------------------------------------------------
login admin || die "cannot log in as admin"

ORIG_MLV=$(setting_get "$SETTING")
ORIG_PRE=$(setting_get "$PREAPPROVE")
add_cleanup "dbq \"UPDATE Settings SET value='${ORIG_MLV:-0}' WHERE name='$SETTING'\" >/dev/null"
add_cleanup "dbq \"UPDATE Settings SET value='${ORIG_PRE:-0}' WHERE name='$PREAPPROVE'\" >/dev/null"
capture_chain "$EMP_USER1"

setting_set "$SETTING" '1'
setting_set "$PREAPPROVE" '0'
mlv=$(setting_get "$SETTING")
[ "$mlv" = "1" ] && ok "multi-level expense approvals ENABLED (was '${ORIG_MLV}')" \
                 || bad "could not enable multi-level (value='$mlv')"

set_chain "$EMP_USER1" "$EMP_MANAGER" "$EMP_USER2" "$EMP_USER3" "$EMP_USER4"
want="$EMP_MANAGER|$EMP_USER2|$EMP_USER3|$EMP_USER4"
chain=$(read_chain "$EMP_USER1")
[ "$chain" = "$want" ] && ok "user1 chain: supervisor=$EMP_MANAGER, approvers=$EMP_USER2/$EMP_USER3/$EMP_USER4" \
                       || bad "user1 chain wrong: '$chain' (wanted '$want')"

lvl2=$(dbq "SELECT user_level FROM Users WHERE username='user2'")
lvl3=$(dbq "SELECT user_level FROM Users WHERE username='user3'")
info "approver roles: user2=$lvl2, user3=$lvl3, user4=$(dbq "SELECT user_level FROM Users WHERE username='user4'")"

# ------------------------------------------------------------------
echo; echo "[Phase 2] user1 submits an expense claim"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"

CATEGORY=$(dbq "SELECT id FROM ExpensesCategories ORDER BY id LIMIT 1")
METHOD=$(dbq "SELECT id FROM ExpensesPaymentMethods ORDER BY id LIMIT 1")
[ -n "$CATEGORY" ] && [ -n "$METHOD" ] || die "expense categories/payment methods are missing — is the database seeded?"

add_record user1 "$MODEL" extension 'expenses|user' \
  --data-urlencode "expense_date=$(date +%Y-%m-%d)" \
  -d "payment_method=$METHOD" -d "category=$CATEGORY" \
  --data-urlencode "payee=$PAYEE" -d "amount=310.00" \
  --data-urlencode "notes=E2E multi-level expense" >/dev/null

EX_ID=$(dbq "SELECT id FROM EmployeeExpenses WHERE payee='$PAYEE' ORDER BY id DESC LIMIT 1")
[ -n "$EX_ID" ] || die "expense claim was not created"
add_cleanup "dbq \"DELETE FROM StatusChangeLogs WHERE type='EmployeeExpense' AND element=$EX_ID\" >/dev/null; dbq \"DELETE FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID\" >/dev/null; dbq \"DELETE FROM EmployeeExpenses WHERE id=$EX_ID\" >/dev/null"
ok "expense claim submitted (id=$EX_ID, 310.00)"

st=$(ex_status "$EX_ID")
[ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"

built=$(dbq "SELECT GROUP_CONCAT(CONCAT('L',level,':',approver) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID")
[ "$built" = "L1:$EMP_USER2,L2:$EMP_USER3,L3:$EMP_USER4" ] \
  && ok "approval chain initialised: $built" \
  || bad "chain wrong: '$built'"

# ------------------------------------------------------------------
echo; echo "[Phase 3] Supervisor approves -> Processing"
# ------------------------------------------------------------------
login manager || die "cannot log in as manager"
ca manager changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"e2e supervisor\"}" "$MODEL" "$MOD" >/dev/null
st=$(ex_status "$EX_ID")
[ "$st" = "Processing" ] && ok "manager (supervisor) approved -> status = Processing" \
                        || bad "expected Processing after supervisor, got '$st'"

active=$(dbq "SELECT level FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID AND active=1")
[ "$active" = "1" ] && ok "approval level 1 is now active" || bad "expected level 1 active, got '$active'"

# ------------------------------------------------------------------
echo; echo "[Phase 4] Levels must be approved in order"
# ------------------------------------------------------------------
login user4 || die "cannot log in as user4"
resp=$(ca user4 changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"jump\"}" "$MODEL" "$MOD")
lv=$(ex_levels "$EX_ID")
if [ "$lv" = "L1=-1,L2=-1,L3=-1" ]; then
  ok "level 3 approver cannot approve out of turn (chain untouched: $lv)"
else
  bad "out-of-turn approval was accepted -> $lv (response: $(printf '%s' "$resp" | head -c 140))"
fi

# ------------------------------------------------------------------
echo; echo "[Phase 5] Approval chain, level by level"
# ------------------------------------------------------------------
approve_level() { # $1 username  $2 level  $3 label  $4 expected record status after
  login "$1" || { bad "cannot log in as $1"; return 1; }
  local resp st recorded
  resp=$(ca "$1" changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"e2e $3\"}" "$MODEL" "$MOD")
  st=$(ex_status "$EX_ID")
  # Assert the LEVEL was recorded, not just the record status. For every step but the
  # last the record stays "Processing" whether the level was approved or the call was
  # refused, so a status-only assertion passes even when nothing happened — which is
  # exactly how a refused Manager approver once slipped through green.
  recorded=$(dbq "SELECT status FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID AND level=$2")
  if [ "$st" = "$4" ] && [ "$recorded" = "1" ]; then
    ok "$3 approved -> level $2 recorded, status = $st"
  else
    bad "$3 approved -> expected status $4 with level $2 recorded, got status '$st' level '$recorded' (response: $(printf '%s' "$resp" | head -c 140))"
  fi
}
approve_level user2 1 "user2 (approver 1, $lvl2)"  "Processing"
approve_level user3 2 "user3 (approver 2, $lvl3)"  "Processing"
approve_level user4 3 "user4 (approver 3)"         "Approved"

# ------------------------------------------------------------------
echo; echo "[Phase 6] user1 sees the fully approved claim"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"
st=$(ex_status "$EX_ID")
[ "$st" = "Approved" ] && ok "user1's expense $EX_ID is Approved" \
                       || bad "user1's expense is '$st', expected Approved"

lv=$(ex_levels "$EX_ID")
[ "$lv" = "L1=1,L2=1,L3=1" ] && ok "all 3 approval levels recorded approved ($lv)" \
                            || bad "approval levels: $lv"

finish "(expense id=$EX_ID; restored: multi-level='${ORIG_MLV}', pre-approve='${ORIG_PRE}', ${ORIG_CHAIN})"
