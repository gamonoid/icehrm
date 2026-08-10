#!/usr/bin/env bash
#
# E2E: Multi-level leave approval (Leave module)
# ----------------------------------------------
# Covers the full multi-level approval chain for a leave applied by user1:
#
#     user1 applies (Annual leave, future working day)
#        -> status Pending, approval chain (level 1/2/3) initialized
#     manager  (supervisor 143) approves  -> Processing  (activates level 1)
#     user2    (approver1  148) approves  -> Processing  (activates level 2)
#     user3    (approver2  149) approves  -> Processing  (activates level 3)
#     user4    (approver3  166) approves  -> Approved     (chain complete)
#     user1 sees the leave Approved.
#
# The SETUP (preconditions) is arranged as admin: the multi-level-leave setting is
# enabled and user1's supervisor + 3 approvers are set (only if not already correct).
# The APPROVAL FLOW itself is driven through the real app endpoints (service.php a=ca),
# authenticated as each user via the normal login, so it exercises the production paths.
#
# Usage:  bash e2e/smoke/leave-multilevel-approval.sh
# Requires: the local docker stack running (app on :9080, mysql container).

set -uo pipefail

# The app URL comes from e2e/.env (shared by every test under e2e/), never hardcoded.
. "$(dirname "$0")/../helpers/env.sh"
BASE="$E2E_APP"
JARDIR=$(mktemp -d)

# Remove the leave this run created. Without it every run permanently consumes one of
# the working days in the window Phase 2 scans, and after a dozen runs the window is
# full and the test dies with "could not find a free working day" — which reads like a
# product failure but is just accumulated test residue. It also leaks Annual balance.
LEAVE_ID=""
cleanup() {
  if [ -n "${LEAVE_ID:-}" ]; then
    dbq "DELETE FROM EmployeeLeaveDays WHERE employee_leave=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeLeaveLog WHERE employee_leave=$LEAVE_ID" >/dev/null
    dbq "DELETE FROM EmployeeLeaves WHERE id=$LEAVE_ID" >/dev/null
  fi
  rm -rf "$JARDIR"
}
trap cleanup EXIT

# --- employee ids ---
# Resolved from the database further down, once dbq() exists. They must NOT be
# hardcoded: they used to be dev literals (147/143/148/149/166) while the app URL
# came from e2e/.env, so on any other environment this configured approval chains
# for unrelated employees and failed later with a misleading error.
ANNUAL=1          # Annual leave type id

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

# custom action: ca <username> <action> <req-json>
ca(){
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
echo " E2E: Multi-level leave approval"
echo "=================================================================="

# ------------------------------------------------------------------
echo; echo "[Phase 1] Setup preconditions (as admin)"
# ------------------------------------------------------------------
login admin || die

# 1a. Multi-level leave approvals enabled + manager-approves-first
for kv in "Leave: Enable Multi Level Approvals=1" "Leave: Manager Needs to Approve=1"; do
  name="${kv%=*}"; val="${kv#*=}"
  cur=$(dbq "SELECT value FROM Settings WHERE name='$name'")
  if [ "$cur" != "$val" ]; then
    dbq "UPDATE Settings SET value='$val' WHERE name='$name'" >/dev/null
    info "set '$name' = $val (was '$cur')"
  fi
done
mlv=$(dbq "SELECT value FROM Settings WHERE name='Leave: Enable Multi Level Approvals'")
[ "$mlv" = "1" ] && ok "Multi-level leave approvals enabled" || bad "Multi-level leave approvals NOT enabled"

# 1b. user1: supervisor=manager, approver1=user2, approver2=user3, approver3=user4
want="$EMP_MANAGER|$EMP_USER2|$EMP_USER3|$EMP_USER4"
got=$(dbq "SELECT CONCAT_WS('|',supervisor,approver1,approver2,approver3) FROM Employees WHERE id=$EMP_USER1")
if [ "$got" != "$want" ]; then
  info "approvers were '$got' — setting to '$want'"
  dbq "UPDATE Employees SET supervisor=$EMP_MANAGER, approver1=$EMP_USER2, approver2=$EMP_USER3, approver3=$EMP_USER4 WHERE id=$EMP_USER1" >/dev/null
  got=$(dbq "SELECT CONCAT_WS('|',supervisor,approver1,approver2,approver3) FROM Employees WHERE id=$EMP_USER1")
fi
[ "$got" = "$want" ] && ok "user1 chain: manager=$EMP_MANAGER, approvers=$EMP_USER2/$EMP_USER3/$EMP_USER4" \
                     || bad "user1 chain wrong: $got"

# ------------------------------------------------------------------
echo; echo "[Phase 2] Pick a future working day (as user1)"
# ------------------------------------------------------------------
login user1 || die
# pick a future working day (weekday, confirmed by getLeaveDays, no overlap)
LEAVE_DATE=""
for off in 14 15 16 17 18 21 22 23 24 25 28 29 30 31 32; do
  cand=$(date -v+"${off}"d +%Y-%m-%d 2>/dev/null || date -d "+${off} days" +%Y-%m-%d)
  resp=$(ca user1 getLeaveDays "{\"start_date\":\"$cand\",\"end_date\":\"$cand\",\"leave_type\":$ANNUAL}")
  # working day if the returned day-map has the date with a count, and no existing leave overlaps
  if printf '%s' "$resp" | grep -q "\"$cand\":1"; then
    overlap=$(dbq "SELECT COUNT(*) FROM EmployeeLeaveDays d JOIN EmployeeLeaves l ON l.id=d.employee_leave WHERE l.employee=$EMP_USER1 AND d.leave_date='$cand' AND l.status IN ('Pending','Processing','Approved')")
    if [ "${overlap:-0}" = "0" ]; then LEAVE_DATE="$cand"; break; fi
  fi
done
[ -n "$LEAVE_DATE" ] && ok "chosen future working day: $LEAVE_DATE" || { bad "could not find a free working day"; echo "PASS=$PASS FAIL=$FAIL"; exit 1; }

# ------------------------------------------------------------------
echo; echo "[Phase 3] user1 applies for the leave (freeing Annual balance if needed)"
# ------------------------------------------------------------------
applyReq="{\"leave_type\":$ANNUAL,\"date_start\":\"$LEAVE_DATE\",\"date_end\":\"$LEAVE_DATE\",\"days\":\"{\\\"$LEAVE_DATE\\\":1}\",\"details\":\"E2E multi-level approval test\"}"
apply_leave(){
  ca user1 addLeave "$applyReq" >/tmp/e2e_apply.json
  dbq "SELECT id FROM EmployeeLeaves WHERE employee=$EMP_USER1 AND leave_type=$ANNUAL AND date_start='$LEAVE_DATE' AND status='Pending' ORDER BY id DESC LIMIT 1"
}
LEAVE_ID=$(apply_leave)
if [ -z "$LEAVE_ID" ]; then
  # As instructed: if there isn't enough Annual balance, delete user1's last approved
  # Annual leave to free a day, then re-apply.
  last=$(dbq "SELECT id FROM EmployeeLeaves WHERE employee=$EMP_USER1 AND leave_type=$ANNUAL AND status='Approved' ORDER BY id DESC LIMIT 1")
  info "apply failed (likely insufficient balance): $(grep -oE '\"[^\"]*not have[^\"]*\"|\"[^\"]*balance[^\"]*\"' /tmp/e2e_apply.json | head -1)"
  if [ -n "$last" ]; then
    info "deleting last approved Annual leave (id=$last) to free balance, then re-applying"
    dbq "DELETE FROM EmployeeLeaveDays WHERE employee_leave=$last" >/dev/null
    dbq "DELETE FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$last" >/dev/null
    dbq "DELETE FROM EmployeeLeaveLog WHERE employee_leave=$last" >/dev/null
    dbq "DELETE FROM EmployeeLeaves WHERE id=$last" >/dev/null
    LEAVE_ID=$(apply_leave)
  fi
fi
if [ -n "$LEAVE_ID" ]; then ok "leave applied (id=$LEAVE_ID) on $LEAVE_DATE"; else bad "apply failed: $(head -c 200 /tmp/e2e_apply.json)"; echo "PASS=$PASS FAIL=$FAIL"; exit 1; fi

st=$(leave_status "$LEAVE_ID"); [ "$st" = "Pending" ] && ok "initial status = Pending" || bad "expected Pending, got '$st'"
chain=$(dbq "SELECT GROUP_CONCAT(CONCAT('L',level,':',approver) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$LEAVE_ID")
[ "$chain" = "L1:$EMP_USER2,L2:$EMP_USER3,L3:$EMP_USER4" ] && ok "approval chain initialized: $chain" || bad "chain wrong: $chain"

# ------------------------------------------------------------------
echo; echo "[Phase 4] Approval chain"
# ------------------------------------------------------------------
approve(){ # $1 username  $2 emp-label  $3 expected-status-after
  login "$1" || return 1
  ca "$1" changeLeaveStatus "{\"id\":$LEAVE_ID,\"status\":\"Approved\",\"reason\":\"\"}" >/dev/null
  local st; st=$(leave_status "$LEAVE_ID")
  [ "$st" = "$3" ] && ok "$2 approved -> status = $st" || bad "$2 approved -> expected $3, got '$st'"
}
approve manager "manager (supervisor)" "Processing"
approve user2   "user2 (approver 1)"   "Processing"
approve user3   "user3 (approver 2)"   "Processing"
approve user4   "user4 (approver 3)"   "Approved"

# ------------------------------------------------------------------
echo; echo "[Phase 5] user1 sees the approved leave"
# ------------------------------------------------------------------
login user1 || die
st=$(leave_status "$LEAVE_ID")
[ "$st" = "Approved" ] && ok "user1's leave $LEAVE_ID is Approved" || bad "user1's leave is '$st', expected Approved"
# all approval levels recorded as approved (status=1)
lv=$(dbq "SELECT GROUP_CONCAT(CONCAT('L',level,'=',status) ORDER BY level) FROM EmployeeApprovals WHERE type='EmployeeLeave' AND element=$LEAVE_ID")
[ "$lv" = "L1=1,L2=1,L3=1" ] && ok "all 3 approval levels recorded approved ($lv)" || bad "approval levels: $lv"

echo; echo "=================================================================="
echo " RESULT: $PASS passed, $FAIL failed   (leave id=$LEAVE_ID, date=$LEAVE_DATE)"
echo "=================================================================="
[ "$FAIL" -eq 0 ]
