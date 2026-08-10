#!/usr/bin/env bash
#
# E2E: Single-level expense approval (multi-level DISABLED)
# ---------------------------------------------------------
# Covers the expense approval flow when no approval chain applies, including the
# expense-specific rules layered on top of the shared workflow:
#
#     user1 submits an expense claim         -> Pending, NO approval chain
#     a status change with no reason         -> refused (reason is mandatory)
#     user1 tries to approve their own       -> refused
#     manager tries to mark it Paid          -> refused (managers may not pay)
#     manager (supervisor) approves          -> Approved  (single step)
#     admin marks it Paid                    -> Paid
#     manager tries to change the Paid claim -> refused
#
# Expenses extend the generic ApproveAdminActionManager::changeStatus with their own
# Expenses\Admin\Controller::changeStatus, which adds the mandatory reason and the two
# manager restrictions — so the module needs its own coverage rather than relying on
# the overtime tests.
#
# Settings and user1's approver chain are captured on entry and restored on exit.
#
# Usage:  bash e2e/smoke/expenses-single-level-approval.sh
# Requires: the testing stack running (URL from e2e/.env), the e2e accounts seeded,
#           and the expenses extension installed.

. "$(dirname "$0")/../helpers/smoke-lib.sh"

SETTING='Expense: Enable Multi Level Approvals'
PREAPPROVE='Expense: Pre-Approve Expenses'
MOD='admin=expenses'
MODEL='EmployeeExpense'
PAYEE="E2E Single Vendor $$"

resolve_employees

ex_status() { dbq "SELECT status FROM EmployeeExpenses WHERE id=$1"; }

echo "=================================================================="
echo " E2E: Single-level expense approval (multi-level DISABLED)"
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

setting_set "$SETTING" '0'
# Pre-approval would auto-approve on submit and there would be no workflow to test.
setting_set "$PREAPPROVE" '0'
mlv=$(setting_get "$SETTING")
[ "$mlv" = "0" ] && ok "multi-level expense approvals DISABLED (was '${ORIG_MLV}')" \
                 || bad "could not disable multi-level (value='$mlv')"

set_chain "$EMP_USER1" "$EMP_MANAGER" NULL NULL NULL
chain=$(read_chain "$EMP_USER1")
[ "$chain" = "$EMP_MANAGER|||" ] && ok "user1 supervised by manager ($EMP_MANAGER), no approvers set" \
                                 || bad "user1 chain wrong: '$chain'"

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
  --data-urlencode "payee=$PAYEE" -d "amount=125.75" \
  --data-urlencode "notes=E2E single-level expense" >/dev/null

EX_ID=$(dbq "SELECT id FROM EmployeeExpenses WHERE payee='$PAYEE' ORDER BY id DESC LIMIT 1")
[ -n "$EX_ID" ] || die "expense claim was not created"
add_cleanup "dbq \"DELETE FROM StatusChangeLogs WHERE type='EmployeeExpense' AND element=$EX_ID\" >/dev/null; dbq \"DELETE FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID\" >/dev/null; dbq \"DELETE FROM EmployeeExpenses WHERE id=$EX_ID\" >/dev/null"
ok "expense claim submitted (id=$EX_ID, 125.75)"

st=$(ex_status "$EX_ID")
[ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"

chainCount=$(dbq "SELECT COUNT(*) FROM EmployeeApprovals WHERE type='EmployeeExpense' AND element=$EX_ID")
[ "${chainCount:-x}" = "0" ] && ok "no approval chain created (single-level)" \
                             || bad "unexpected approval chain rows: $chainCount"

# ------------------------------------------------------------------
echo; echo "[Phase 3] Expense-specific guards"
# ------------------------------------------------------------------
login manager || die "cannot log in as manager"

# A reason is mandatory for EVERY expense status change, for every role.
resp=$(ca manager changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"\"}" "$MODEL" "$MOD")
st=$(ex_status "$EX_ID")
if [ "$st" = "Pending" ] && printf '%s' "$resp" | grep -qi 'reason is required'; then
  ok "status change without a reason is refused"
else
  bad "empty reason was accepted -> status='$st' (response: $(printf '%s' "$resp" | head -c 140))"
fi

# A manager may not mark an expense as Paid — that is an admin action.
resp=$(ca manager changeStatus "{\"id\":$EX_ID,\"status\":\"Paid\",\"reason\":\"try to pay\"}" "$MODEL" "$MOD")
st=$(ex_status "$EX_ID")
if [ "$st" = "Pending" ]; then
  ok "manager cannot mark an expense as Paid"
else
  bad "manager marked the expense '$st' (response: $(printf '%s' "$resp" | head -c 140))"
fi

# The owner must not approve their own claim.
login user1 || die "cannot log in as user1"
resp=$(ca user1 changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"self\"}" "$MODEL" "$MOD")
st=$(ex_status "$EX_ID")
if [ "$st" = "Pending" ]; then
  ok "owner cannot approve their own expense (still Pending)"
else
  bad "OWNER SELF-APPROVED their own expense -> status='$st' (response: $(printf '%s' "$resp" | head -c 140))"
fi

# ------------------------------------------------------------------
echo; echo "[Phase 4] Manager approves -> straight to Approved"
# ------------------------------------------------------------------
login manager || die "cannot log in as manager"
ca manager changeStatus "{\"id\":$EX_ID,\"status\":\"Approved\",\"reason\":\"e2e single-level\"}" "$MODEL" "$MOD" >/dev/null
st=$(ex_status "$EX_ID")
[ "$st" = "Approved" ] && ok "manager approved -> status = Approved (single step)" \
                       || bad "expected Approved, got '$st'"

logged=$(dbq "SELECT COUNT(*) FROM StatusChangeLogs WHERE type='EmployeeExpense' AND element=$EX_ID AND status_from='Pending' AND status_to='Approved'")
[ "${logged:-0}" -ge 1 ] && ok "approval logged (Pending -> Approved)" \
                         || bad "no Pending->Approved log entry"

# ------------------------------------------------------------------
echo; echo "[Phase 5] Admin pays it; the manager may not touch it afterwards"
# ------------------------------------------------------------------
login admin || die "cannot log in as admin"
ca admin changeStatus "{\"id\":$EX_ID,\"status\":\"Paid\",\"reason\":\"e2e payment\"}" "$MODEL" "$MOD" >/dev/null
st=$(ex_status "$EX_ID")
[ "$st" = "Paid" ] && ok "admin marked the expense Paid" || bad "expected Paid, got '$st'"

login manager || die "cannot log in as manager"
resp=$(ca manager changeStatus "{\"id\":$EX_ID,\"status\":\"Rejected\",\"reason\":\"reopen\"}" "$MODEL" "$MOD")
st=$(ex_status "$EX_ID")
if [ "$st" = "Paid" ]; then
  ok "manager cannot change the status of a Paid expense"
else
  bad "manager reopened a Paid expense -> '$st' (response: $(printf '%s' "$resp" | head -c 140))"
fi

finish "(expense id=$EX_ID; restored: multi-level='${ORIG_MLV}', pre-approve='${ORIG_PRE}', ${ORIG_CHAIN})"
