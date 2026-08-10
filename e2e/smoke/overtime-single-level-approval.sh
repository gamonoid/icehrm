#!/usr/bin/env bash
#
# E2E: Single-level overtime approval (multi-level DISABLED)
# ----------------------------------------------------------
# Covers the overtime approval flow when no approval chain applies:
#
#     user1 submits an overtime request        -> Pending, NO approval chain
#     user1 tries to approve it themselves     -> refused
#     user4 (unrelated employee) tries         -> refused
#     manager (user1's supervisor) approves    -> Approved   (single step, straight
#                                                  to Approved, not "Processing")
#     user1 sees it Approved, with a Pending -> Approved log entry.
#
# Overtime approval does NOT share the leave module's code path: leave has its own
# changeLeaveStatus action, while overtime (like travel and expenses) goes through the
# generic ApproveAdminActionManager::changeStatus. The two have diverged before, so
# they are covered separately.
#
# The multi-level setting and user1's approver chain are captured on entry and restored
# on exit, so the test leaves app configuration exactly as it found it.
#
# Usage:  bash e2e/smoke/overtime-single-level-approval.sh
# Requires: the testing stack running (URL from e2e/.env) and the e2e accounts seeded.

. "$(dirname "$0")/../helpers/smoke-lib.sh"

SETTING='Overtime: Enable Multi Level Approvals'
MOD='admin=overtime'
MODEL='EmployeeOvertime'
NOTES="E2E single-level overtime $$"

resolve_employees

ot_status() { dbq "SELECT status FROM EmployeeOvertime WHERE id=$1"; }

echo "=================================================================="
echo " E2E: Single-level overtime approval (multi-level DISABLED)"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Setup preconditions (as admin)"
# ------------------------------------------------------------------
login admin || die "cannot log in as admin"

ORIG_MLV=$(setting_get "$SETTING")
add_cleanup "dbq \"UPDATE Settings SET value='${ORIG_MLV:-0}' WHERE name='$SETTING'\" >/dev/null"
capture_chain "$EMP_USER1"

setting_set "$SETTING" '0'
mlv=$(setting_get "$SETTING")
[ "$mlv" = "0" ] && ok "multi-level overtime approvals DISABLED (was '${ORIG_MLV}')" \
                 || bad "could not disable multi-level (value='$mlv')"

# user1 reports to manager and has NO designated approvers. Clearing approver1..3 is
# what actually makes this single-level: ApprovalStatus::isDirectApproval() treats an
# employee with no approvers as direct-approval, so no chain is built even if the
# setting were on.
set_chain "$EMP_USER1" "$EMP_MANAGER" NULL NULL NULL
chain=$(read_chain "$EMP_USER1")
[ "$chain" = "$EMP_MANAGER|||" ] && ok "user1 supervised by manager ($EMP_MANAGER), no approvers set" \
                                 || bad "user1 chain wrong: '$chain'"

# ------------------------------------------------------------------
echo; echo "[Phase 2] user1 submits an overtime request"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"

OT_DATE=$(future_date 5)
CATEGORY=$(dbq "SELECT id FROM OvertimeCategories ORDER BY id LIMIT 1")
[ -n "$CATEGORY" ] || die "no overtime category exists — is the database seeded?"

add_record user1 "$MODEL" modules overtime \
  --data-urlencode "start_time=$OT_DATE 18:00:00" \
  --data-urlencode "end_time=$OT_DATE 20:00:00" \
  -d "category=$CATEGORY" \
  --data-urlencode "notes=$NOTES" >/dev/null

OT_ID=$(dbq "SELECT id FROM EmployeeOvertime WHERE notes='$NOTES' ORDER BY id DESC LIMIT 1")
[ -n "$OT_ID" ] || die "overtime request was not created"
add_cleanup "dbq \"DELETE FROM StatusChangeLogs WHERE type='EmployeeOvertime' AND element=$OT_ID\" >/dev/null; dbq \"DELETE FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID\" >/dev/null; dbq \"DELETE FROM EmployeeOvertime WHERE id=$OT_ID\" >/dev/null"
ok "overtime request submitted (id=$OT_ID) for $OT_DATE 18:00-20:00"

st=$(ot_status "$OT_ID")
[ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"

chainCount=$(dbq "SELECT COUNT(*) FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID")
[ "${chainCount:-x}" = "0" ] && ok "no approval chain created (single-level)" \
                             || bad "unexpected approval chain rows: $chainCount"

# ------------------------------------------------------------------
echo; echo "[Phase 3] Only the supervisor may decide it"
# ------------------------------------------------------------------
# The owner must not be able to approve their own request. changeStatus authorises via
# the record owner, and "access to your own employee data" is true for the owner — so
# without an explicit self-check the owner passes. Guarding it here because that hole
# is invisible from the UI (the button simply isn't drawn).
resp=$(ca user1 changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"self\"}" "$MODEL" "$MOD")
st=$(ot_status "$OT_ID")
if [ "$st" = "Pending" ]; then
  ok "owner cannot approve their own overtime (still Pending)"
else
  bad "OWNER SELF-APPROVED their own overtime -> status='$st' (response: $(printf '%s' "$resp" | head -c 120))"
fi

# An employee who is neither the owner's supervisor nor an approver must be refused.
login user4 || die "cannot log in as user4"
resp=$(ca user4 changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"outsider\"}" "$MODEL" "$MOD")
st=$(ot_status "$OT_ID")
if [ "$st" = "Pending" ]; then
  ok "unrelated employee cannot approve someone else's overtime"
else
  bad "unrelated employee changed the status -> '$st' (response: $(printf '%s' "$resp" | head -c 120))"
fi

# ------------------------------------------------------------------
echo; echo "[Phase 4] Manager approves -> straight to Approved"
# ------------------------------------------------------------------
login manager || die "cannot log in as manager"
ca manager changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"e2e single-level\"}" "$MODEL" "$MOD" >/dev/null
st=$(ot_status "$OT_ID")
[ "$st" = "Approved" ] && ok "manager approved -> status = Approved (single step)" \
                       || bad "expected Approved, got '$st'"

# ------------------------------------------------------------------
echo; echo "[Phase 5] user1 sees the approved request"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"
st=$(ot_status "$OT_ID")
[ "$st" = "Approved" ] && ok "user1's overtime $OT_ID is Approved" \
                       || bad "user1's overtime is '$st', expected Approved"

logged=$(dbq "SELECT COUNT(*) FROM StatusChangeLogs WHERE type='EmployeeOvertime' AND element=$OT_ID AND status_from='Pending' AND status_to='Approved'")
[ "${logged:-0}" -ge 1 ] && ok "approval logged (Pending -> Approved)" \
                         || bad "no Pending->Approved log entry"

# The owner may read the history of their own request even though they may not decide it.
resp=$(ca user1 getLogs "{\"id\":$OT_ID}" "$MODEL" "$MOD")
printf '%s' "$resp" | grep -q '"SUCCESS"' && ok "owner can read their own approval history" \
                                          || bad "owner denied their own history: $(printf '%s' "$resp" | head -c 120)"

finish "(overtime id=$OT_ID, date=$OT_DATE; restored: multi-level='${ORIG_MLV}', ${ORIG_CHAIN})"
