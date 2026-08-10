#!/usr/bin/env bash
#
# E2E: Single-level leave approval (multi-level DISABLED)
# -------------------------------------------------------
# Covers the leave approval flow when "Leave: Enable Multi Level Approvals" is OFF:
#
#     user1 applies (Annual leave, future working day)  -> status Pending
#                                                        -> NO approval chain created
#     manager (supervisor 143) approves                 -> Approved  (single step,
#                                                           straight to Approved, not
#                                                           "Processing")
#     user1 sees the leave Approved.
#
# The multi-level setting is toggled OFF for the test and RESTORED to its original
# value at the end, so the test is non-destructive to app configuration. The apply +
# approval are driven through the real app endpoints (service.php a=ca) as each user.
#
# Usage:  bash e2e/smoke/leave-single-level-approval.sh
# Requires: the local docker stack running (app on :9080, mysql container).

set -uo pipefail

# The app URL comes from e2e/.env (shared by every test under e2e/), never hardcoded.
. "$(dirname "$0")/../helpers/env.sh"
BASE="$E2E_APP"
JARDIR=$(mktemp -d)

EMP_USER1=147     # user1 (Employee, applicant)
EMP_MANAGER=143   # manager (supervisor of user1)
ANNUAL=1          # Annual leave type id
SETTING='Leave: Enable Multi Level Approvals'

PASS=0; FAIL=0
ok()  { echo "  [PASS] $1"; PASS=$((PASS+1)); }
bad() { echo "  [FAIL] $1"; FAIL=$((FAIL+1)); }
info(){ echo "  ...   $1"; }

dbq(){ docker exec "$E2E_DB_CONTAINER" mysql -u "$E2E_DB_USER" -p"$E2E_DB_PASS" "$E2E_DB_NAME" -N -e "$1" 2>/dev/null; }

emp_of(){ dbq "SELECT employee FROM Users WHERE username='$1' LIMIT 1"; }
EMP_USER1=$(emp_of user1)
EMP_MANAGER=$(emp_of manager)
EMP_USER2=$(emp_of user2)
EMP_USER3=$(emp_of user3)
EMP_USER4=$(emp_of user4)
for _v in EMP_USER1 EMP_MANAGER EMP_USER2 EMP_USER3 EMP_USER4; do
  eval "_id=\$$_v"
  [ -n "${_id:-}" ] || { echo "  [FAIL] could not resolve $_v from ${E2E_DB_CONTAINER} — are the e2e accounts seeded?" >&2; exit 1; }
done

# Restore the original multi-level setting on exit (whatever it was before the test).
ORIG_MLV=$(dbq "SELECT value FROM Settings WHERE name='$SETTING'")
# Also remove the leave this run created. Without it every run permanently consumes a
# working day from the window Phase 2 scans, and after enough runs the window fills up
# and the test dies with "could not find a free working day" — which reads like a
# product failure but is just accumulated test residue. It also leaks Annual balance.
LEAVE_ID=""
cleanup(){
  dbq "UPDATE Settings SET value='${ORIG_MLV:-1}' WHERE name='$SETTING'" >/dev/null
  if [ -n "${LEAVE_ID:-}" ]; then
    dbq "DELETE FROM EmployeeLeaveDays WHERE employee_leave=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeLeaveLog WHERE employee_leave=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeLeaves WHERE id=$LEAVE_ID" >/dev/null
  fi
  rm -rf "$JARDIR"
}
trap cleanup EXIT

# login as $1 -> writes cookie jar $JARDIR/$1.jar ; returns non-zero if login failed
login(){
  local u="$1" jar="$JARDIR/$1.jar" html csrf landing
  dbq "UPDATE Users SET wrong_password_count=0, last_wrong_attempt_at=NULL WHERE username='$u'" >/dev/null
  html=$(curl -s -c "$jar" "$BASE/login.php")
  # Read the token out of the csrf field itself — do NOT match a fixed-width hex string.
  # The token length is not stable (it moved from 40 to 64 hex chars), and a stale
  # width silently yields an empty token, which fails login and then surfaces much
  # later as confusing "no working day" / NO_USER_FOUND errors.
  csrf=$(printf '%s' "$html" | grep -oE '<input[^>]*id="csrf"[^>]*>' | head -1 \
         | grep -oE 'value="[^"]*"' | sed 's/value="//;s/"$//')
  if [ -z "$csrf" ]; then bad "login $u: could not read csrf token from login.php"; return 1; fi
  landing=$(curl -s -o /dev/null -b "$jar" -c "$jar" -L -w '%{url_effective}' \
    --data-urlencode "username=$u" --data-urlencode 'password=Admin123$' \
    --data-urlencode "csrf=$csrf" "$BASE/login.php")
  # A successful login redirects into the app; a failed one stays on login.php.
  case "$landing" in
    *login.php*) bad "login $u failed (landed on $landing)"; return 1 ;;
  esac
  return 0
}
die(){ echo "PASS=$PASS FAIL=$FAIL"; exit 1; }
ca(){ # ca <username> <action> <req-json>
  local jar="$JARDIR/$1.jar"
  # The Referer is required, not cosmetic: service.php gates state-changing actions
  # (a=ca among them) on the request being same-origin — see
  # BaseService::isSameOriginRequest(). A browser always sends one; curl does not, so
  # without this every call comes back {"code":"CSRF_ORIGIN_DENIED"} and the failure
  # surfaces much later as a confusing "could not find a free working day".
  curl -s -b "$jar" -H "Referer: $BASE/ui/" -G "$BASE/service.php" \
    --data-urlencode "t=EmployeeLeave" --data-urlencode "a=ca" \
    --data-urlencode "sa=$2" --data-urlencode "mod=modules=leaves" \
    --data-urlencode "req=$3"
}
leave_status(){ dbq "SELECT status FROM EmployeeLeaves WHERE id=$1"; }

echo "=================================================================="
echo " E2E: Single-level leave approval (multi-level DISABLED)"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Setup preconditions (as admin)"
# ------------------------------------------------------------------
login admin || die
# 1a. disable multi-level leave approvals; manager still approves
dbq "UPDATE Settings SET value='0' WHERE name='$SETTING'" >/dev/null
dbq "UPDATE Settings SET value='1' WHERE name='Leave: Manager Needs to Approve'" >/dev/null
mlv=$(dbq "SELECT value FROM Settings WHERE name='$SETTING'")
[ "$mlv" = "0" ] && ok "multi-level leave approvals DISABLED (was '${ORIG_MLV}')" || bad "could not disable multi-level (value='$mlv')"

# 1b. user1's supervisor must be the manager (single approver)
sup=$(dbq "SELECT supervisor FROM Employees WHERE id=$EMP_USER1")
if [ "$sup" != "$EMP_MANAGER" ]; then
  dbq "UPDATE Employees SET supervisor=$EMP_MANAGER WHERE id=$EMP_USER1" >/dev/null
  sup=$(dbq "SELECT supervisor FROM Employees WHERE id=$EMP_USER1")
fi
[ "$sup" = "$EMP_MANAGER" ] && ok "user1's supervisor = manager ($EMP_MANAGER)" || bad "supervisor wrong: $sup"

# ------------------------------------------------------------------
echo; echo "[Phase 2] Pick a future working day (as user1)"
# ------------------------------------------------------------------
login user1 || die
LEAVE_DATE=""
for off in 14 15 16 17 18 21 22 23 24 25 28 29 30 31 32 35 36 37 38 39; do
  cand=$(date -v+"${off}"d +%Y-%m-%d 2>/dev/null || date -d "+${off} days" +%Y-%m-%d)
  resp=$(ca user1 getLeaveDays "{\"start_date\":\"$cand\",\"end_date\":\"$cand\",\"leave_type\":$ANNUAL}")
  if printf '%s' "$resp" | grep -q "\"$cand\":1"; then
    overlap=$(dbq "SELECT COUNT(*) FROM EmployeeLeaveDays d JOIN EmployeeLeaves l ON l.id=d.employee_leave WHERE l.employee=$EMP_USER1 AND d.leave_date='$cand' AND l.status IN ('Pending','Processing','Approved')")
    if [ "${overlap:-0}" = "0" ]; then LEAVE_DATE="$cand"; break; fi
  fi
done
[ -n "$LEAVE_DATE" ] && ok "chosen future working day: $LEAVE_DATE" || { bad "could not find a free working day"; echo "PASS=$PASS FAIL=$FAIL"; exit 1; }

# ------------------------------------------------------------------
echo; echo "[Phase 3] user1 applies for the leave (freeing Annual balance if needed)"
# ------------------------------------------------------------------
applyReq="{\"leave_type\":$ANNUAL,\"date_start\":\"$LEAVE_DATE\",\"date_end\":\"$LEAVE_DATE\",\"days\":\"{\\\"$LEAVE_DATE\\\":1}\",\"details\":\"E2E single-level approval test\"}"
apply_leave(){
  ca user1 addLeave "$applyReq" >/tmp/e2e_apply2.json
  dbq "SELECT id FROM EmployeeLeaves WHERE employee=$EMP_USER1 AND leave_type=$ANNUAL AND date_start='$LEAVE_DATE' AND status='Pending' ORDER BY id DESC LIMIT 1"
}
LEAVE_ID=$(apply_leave)
if [ -z "$LEAVE_ID" ]; then
  last=$(dbq "SELECT id FROM EmployeeLeaves WHERE employee=$EMP_USER1 AND leave_type=$ANNUAL AND status='Approved' ORDER BY id DESC LIMIT 1")
  info "apply failed (likely insufficient balance): $(grep -oE '\"[^\"]*not have[^\"]*\"|\"[^\"]*balance[^\"]*\"' /tmp/e2e_apply2.json | head -1)"
  if [ -n "$last" ]; then
    info "deleting last approved Annual leave (id=$last) to free balance, then re-applying"
    dbq "DELETE FROM EmployeeLeaveDays WHERE employee_leave=$last" >/dev/null
    dbq "DELETE FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$last" >/dev/null
    dbq "DELETE FROM EmployeeLeaveLog WHERE employee_leave=$last" >/dev/null
    dbq "DELETE FROM EmployeeLeaves WHERE id=$last" >/dev/null
    LEAVE_ID=$(apply_leave)
  fi
fi
if [ -n "$LEAVE_ID" ]; then ok "leave applied (id=$LEAVE_ID) on $LEAVE_DATE"; else bad "apply failed: $(head -c 200 /tmp/e2e_apply2.json)"; echo "PASS=$PASS FAIL=$FAIL"; exit 1; fi

st=$(leave_status "$LEAVE_ID"); [ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"
# With multi-level OFF, no approval chain rows should be created for this leave
chainCount=$(dbq "SELECT COUNT(*) FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$LEAVE_ID")
[ "${chainCount:-x}" = "0" ] && ok "no approval chain created (single-level)" || bad "unexpected approval chain rows: $chainCount"

# ------------------------------------------------------------------
echo; echo "[Phase 4] Manager approves -> straight to Approved"
# ------------------------------------------------------------------
login manager || die
ca manager changeLeaveStatus "{\"id\":$LEAVE_ID,\"status\":\"Approved\",\"reason\":\"\"}" >/dev/null
st=$(leave_status "$LEAVE_ID")
[ "$st" = "Approved" ] && ok "manager approved -> status = Approved (single step)" || bad "expected Approved, got '$st'"

# ------------------------------------------------------------------
echo; echo "[Phase 5] user1 sees the approved leave"
# ------------------------------------------------------------------
login user1 || die
st=$(leave_status "$LEAVE_ID")
[ "$st" = "Approved" ] && ok "user1's leave $LEAVE_ID is Approved" || bad "user1's leave is '$st', expected Approved"
# a status-change log entry Pending -> Approved was recorded
logged=$(dbq "SELECT COUNT(*) FROM EmployeeLeaveLog WHERE employee_leave=$LEAVE_ID AND status_from='Pending' AND status_to='Approved'")
[ "${logged:-0}" -ge 1 ] && ok "approval logged (Pending -> Approved)" || bad "no Pending->Approved log entry"

echo; echo "=================================================================="
echo " RESULT: $PASS passed, $FAIL failed   (leave id=$LEAVE_ID, date=$LEAVE_DATE)"
echo " (multi-level setting restored to '${ORIG_MLV}')"
echo "=================================================================="
[ "$FAIL" -eq 0 ]
