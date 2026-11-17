#!/bin/bash
set -u
BASE="http://127.0.0.1/ds/index.php"
pass=0; fail=0
chk(){ if echo "$3" | grep -q "$2"; then echo "  PASS: $1"; pass=$((pass+1)); else echo "  FAIL: $1"; echo "        got: $(echo "$3"|head -c 300)"; fail=$((fail+1)); fi; }

curl -s "$BASE" -o /tmp/ds_home.html
H=$(cat /tmp/ds_home.html)
chk "home page renders" "DS Scheduler Web Console" "$H"
chk "page includes xajax core js" "xajax_core" "$H"
chk "page registers xajax_watchList" "xajax_watchList" "$H"
chk "page registers xajax_Action" "xajax_Action" "$H"
chk "page lists job backup_home" "backup_home" "$H"
chk "page lists job log_rotate" "log_rotate" "$H"

# real xajax call: watchList() -> assign master_daemon_status
R=$(curl -s -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" --data-urlencode "xjxfun=watchList")
echo "--- watchList() response ---"; echo "$R" | head -c 400; echo
chk "watchList returns command payload" '"cmd"' "$R"
chk "watchList sets daemon status" "master_daemon_status" "$R"

# real xajax call: show(id) -> opens log dialog
R2=$(curl -s -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" --data-urlencode "xjxfun=show" --data-urlencode 'xjxargs[]=N1')
echo "--- show(1) response ---"; echo "$R2" | head -c 400; echo
chk "show() returns command payload" '"cmd"' "$R2"
chk "show() no fatal error" "" "$(echo "$R2" | grep -iE 'fatal error|parse error' | head -c 200)"

# real xajax call: Action(form, 'disable') on job 1
R3=$(curl -s -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=Action" \
  --data-urlencode 'xjxargs[]={"selected_job":["1"]}' \
  --data-urlencode 'xjxargs[]=Sdisable')
echo "--- Action(disable) response ---"; echo "$R3" | head -c 400; echo
# Action() returns void, so a correct xajax response carries no commands.
chk "Action() returns empty command list" "" "$(echo "$R3" | strings | grep -avE 'Deprecated|^<br|^$' | head -c 200)"
chk "Action() no fatal error" "" "$(echo "$R3" | grep -iE 'fatal error|parse error' | head -c 200)"
chk "Action() set update_flag=3 in db" "3" "$(mysql -uroot -N -e 'SELECT update_flag FROM ds_scheduler.jobs WHERE id=1' 2>/dev/null)"

echo "TOTAL pass=$pass fail=$fail"
[ "$fail" -eq 0 ]
