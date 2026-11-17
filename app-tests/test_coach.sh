#!/bin/bash
set -u
BASE="http://127.0.0.1/coach/index.php"
CJ=$(mktemp); rm -f "$CJ"
pass=0; fail=0
chk(){ if echo "$3" | grep -q "$2"; then echo "  PASS: $1"; pass=$((pass+1)); else echo "  FAIL: $1"; echo "        got: $(echo "$3"|head -c 300)"; fail=$((fail+1)); fi; }

curl -s -c "$CJ" "$BASE" -o /tmp/coach_home.html
chk "home page renders" "Coach Challenges" "$(cat /tmp/coach_home.html)"
chk "home page includes xajax core js" "xajax_core" "$(cat /tmp/coach_home.html)"
chk "home page registers xajax_test" "xajax_test" "$(cat /tmp/coach_home.html)"
chk "home page registers xajax_login" "xajax_login" "$(cat /tmp/coach_home.html)"

# real xajax call: test() returns alert("hello")
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=test")
echo "--- xajax test() response ---"; echo "$R" | head -c 400; echo
chk "test() returns xajax command payload" '"cmd"' "$R"
chk "test() emits alert(hello)" "hello" "$R"

# real xajax call: login() (no-op, must not error)
R2=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=login" --data-urlencode 'xjxargs[]=nwollmann' --data-urlencode 'xjxargs[]=password')
echo "--- xajax login() response ---"; echo "$R2" | head -c 400; echo
chk "login() no fatal error" "" "$(echo "$R2" | grep -iE 'fatal error|parse error' | head -c 200)"

echo "TOTAL pass=$pass fail=$fail"
[ "$fail" -eq 0 ]
