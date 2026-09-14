#!/usr/bin/env bash
#
# E2E: Multi-level overtime approval
# -----------------------------------
# Covers the full multi-level approval chain for an overtime request by user1:
#
#     user1 submits                          -> Pending, chain L1/L2/L3 initialised
#     manager (supervisor) approves          -> Processing  (activates level 1)
#     user4 (level 3) tries to jump the queue -> refused, still Processing
#     user2 (approver1, Manager)  approves   -> Processing  (activates level 2)
#     user3 (approver2, Employee) approves   -> Processing  (activates level 3)
#     user4 (approver3, Employee) approves   -> Approved     (chain complete)
#     user1 sees it Approved with all three levels recorded.
#
# The approver mix is deliberate. approver1 is a Manager and approver2/3 are Employees,
# because the two authorisation layers involved fail for different roles:
#
#   * changeStatus authorises by the record owner's employee data, which a designated
#     approver does NOT have — approver1..3 are set per employee and are usually not
#     that employee's supervisor. A Manager approver exercises that path.
#   * the a=ca dispatcher enforces the module's user_levels, and the SPA routes
#     changeStatus to admin=overtime (Admin/Manager only) even from the user module's
#     Approvals tab, which modules/overtime shows to Employees. An Employee approver
#     exercises that path.
#
# Both were broken at once at one point, leaving requests stuck at Processing with
# nobody able to advance them, so the chain is walked with both kinds of approver.
#
# Settings and user1's approver chain are captured on entry and restored on exit.
#
# Usage:  bash e2e/smoke/overtime-multilevel-approval.sh
# Requires: the testing stack running (URL from e2e/.env) and the e2e accounts seeded.

. "$(dirname "$0")/../helpers/smoke-lib.sh"

SETTING='Overtime: Enable Multi Level Approvals'
MOD='admin=overtime'
MODEL='EmployeeOvertime'
NOTES="E2E multi-level overtime $$"

resolve_employees

ot_status() { dbq "SELECT status FROM EmployeeOvertime WHERE id=$1"; }
ot_levels() { dbq "SELECT GROUP_CONCAT(CONCAT('L',level,'=',status) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$1"; }

echo "=================================================================="
echo " E2E: Multi-level overtime approval"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Setup preconditions (as admin)"
# ------------------------------------------------------------------
login admin || die "cannot log in as admin"

ORIG_MLV=$(setting_get "$SETTING")
add_cleanup "dbq \"UPDATE Settings SET value='${ORIG_MLV:-0}' WHERE name='$SETTING'\" >/dev/null"
capture_chain "$EMP_USER1"

setting_set "$SETTING" '1'
mlv=$(setting_get "$SETTING")
[ "$mlv" = "1" ] && ok "multi-level overtime approvals ENABLED (was '${ORIG_MLV}')" \
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
echo; echo "[Phase 2] user1 submits an overtime request"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"

OT_DATE=$(future_date 7)
CATEGORY=$(dbq "SELECT id FROM OvertimeCategories ORDER BY id LIMIT 1")
[ -n "$CATEGORY" ] || die "no overtime category exists — is the database seeded?"

add_record user1 "$MODEL" modules overtime \
  --data-urlencode "start_time=$OT_DATE 18:00:00" \
  --data-urlencode "end_time=$OT_DATE 21:00:00" \
  -d "category=$CATEGORY" \
  --data-urlencode "notes=$NOTES" >/dev/null

OT_ID=$(dbq "SELECT id FROM EmployeeOvertime WHERE notes='$NOTES' ORDER BY id DESC LIMIT 1")
[ -n "$OT_ID" ] || die "overtime request was not created"
add_cleanup "dbq \"DELETE FROM StatusChangeLogs WHERE type='EmployeeOvertime' AND element=$OT_ID\" >/dev/null; dbq \"DELETE FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID\" >/dev/null; dbq \"DELETE FROM EmployeeOvertime WHERE id=$OT_ID\" >/dev/null"
ok "overtime request submitted (id=$OT_ID) for $OT_DATE 18:00-21:00"

st=$(ot_status "$OT_ID")
[ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"

built=$(dbq "SELECT GROUP_CONCAT(CONCAT('L',level,':',approver) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID")
[ "$built" = "L1:$EMP_USER2,L2:$EMP_USER3,L3:$EMP_USER4" ] \
  && ok "approval chain initialised: $built" \
  || bad "chain wrong: '$built'"

# ------------------------------------------------------------------
echo; echo "[Phase 3] Supervisor approves -> Processing"
# ------------------------------------------------------------------
login manager || die "cannot log in as manager"
ca manager changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"e2e supervisor\"}" "$MODEL" "$MOD" >/dev/null
st=$(ot_status "$OT_ID")
[ "$st" = "Processing" ] && ok "manager (supervisor) approved -> status = Processing" \
                        || bad "expected Processing after supervisor, got '$st'"

active=$(dbq "SELECT level FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID AND active=1")
[ "$active" = "1" ] && ok "approval level 1 is now active" || bad "expected level 1 active, got '$active'"

# ------------------------------------------------------------------
echo; echo "[Phase 4] Levels must be approved in order"
# ------------------------------------------------------------------
# The level-3 approver may not decide while level 1 is the active one — otherwise the
# chain could be short-circuited by whoever happens to act first.
login user4 || die "cannot log in as user4"
resp=$(ca user4 changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"jump\"}" "$MODEL" "$MOD")
lv=$(ot_levels "$OT_ID")
if [ "$lv" = "L1=-1,L2=-1,L3=-1" ]; then
  ok "level 3 approver cannot approve out of turn (chain untouched: $lv)"
else
  bad "out-of-turn approval was accepted -> $lv (response: $(printf '%s' "$resp" | head -c 120))"
fi

# ------------------------------------------------------------------
echo; echo "[Phase 5] Approval chain, level by level"
# ------------------------------------------------------------------
approve_level() { # $1 username  $2 level  $3 label  $4 expected record status after
  login "$1" || { bad "cannot log in as $1"; return 1; }
  local resp st recorded
  resp=$(ca "$1" changeStatus "{\"id\":$OT_ID,\"status\":\"Approved\",\"reason\":\"e2e $3\"}" "$MODEL" "$MOD")
  st=$(ot_status "$OT_ID")
  # Assert the LEVEL was recorded, not just the record status. For every step but the
  # last the record stays "Processing" whether the level was approved or the call was
  # refused, so a status-only assertion passes even when nothing happened — which is
  # exactly how a refused Manager approver once slipped through green.
  recorded=$(dbq "SELECT status FROM EmployeeApprovals WHERE type='EmployeeOvertime' AND element=$OT_ID AND level=$2")
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
echo; echo "[Phase 6] user1 sees the fully approved request"
# ------------------------------------------------------------------
login user1 || die "cannot log in as user1"
st=$(ot_status "$OT_ID")
[ "$st" = "Approved" ] && ok "user1's overtime $OT_ID is Approved" \
                       || bad "user1's overtime is '$st', expected Approved"

lv=$(ot_levels "$OT_ID")
[ "$lv" = "L1=1,L2=1,L3=1" ] && ok "all 3 approval levels recorded approved ($lv)" \
                            || bad "approval levels: $lv"

finish "(overtime id=$OT_ID, date=$OT_DATE; restored: multi-level='${ORIG_MLV}', ${ORIG_CHAIN})"
