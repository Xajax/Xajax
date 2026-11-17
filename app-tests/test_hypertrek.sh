#!/bin/bash
set -u
BASE="http://127.0.0.1/hypertrek/index.php"
CJ=$(mktemp); rm -f "$CJ"
pass=0; fail=0
chk(){ if echo "$3" | grep -q "$2"; then echo "  PASS: $1"; pass=$((pass+1)); else echo "  FAIL: $1"; echo "        got: $(echo "$3"|head -c 300)"; fail=$((fail+1)); fi; }
curl -s -c "$CJ" "$BASE" -o /tmp/ht_home.html
chk "home page includes xajax core js" "xajax_core" "$(cat /tmp/ht_home.html)"
chk "home page registers xajax_AJAXmenu" "xajax_AJAXmenu" "$(cat /tmp/ht_home.html)"
chk "home page registers xajax_AJAXcerca" "xajax_AJAXcerca" "$(cat /tmp/ht_home.html)"
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" --data-urlencode "xjxfun=AJAXmenu" --data-urlencode 'xjxargs[]=Stopent')
chk "AJAXmenu returns xajax command payload" '"cmd"' "$R"
chk "AJAXmenu writes indiceintestazione" "indiceintestazione" "$R"
R2=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE" -H "X-Requested-With: XMLHttpRequest" --data-urlencode "xjxfun=AJAXcerca" --data-urlencode 'xjxargs[]={"cerca":"kirk"}')
chk "AJAXcerca returns xajax command payload" '"cmd"' "$R2"
chk "AJAXcerca reports search result" "Risultato della ricerca" "$R2"
echo "TOTAL pass=$pass fail=$fail"
[ "$fail" -eq 0 ]
