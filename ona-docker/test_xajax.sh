#!/bin/bash
# End-to-end xajax functional test against ONA running the new PHP 8.2 xajax.
BASE="${ONA_URL:-http://localhost:12001/ona}"
CJ=$(mktemp)
rm -f "$CJ"

pass=0; fail=0
check() { # name, expected_substr, actual
  if echo "$3" | grep -q "$2"; then echo "  PASS: $1"; pass=$((pass+1));
  else echo "  FAIL: $1 (expected '$2')"; echo "        got: $(echo "$3" | head -c 200)"; fail=$((fail+1)); fi
}

# get login page + session
curl -s -c "$CJ" "$BASE/login.php" -o /dev/null

# xajax login (window_submit -> tooltips -> logingo)
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE/login.php" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=window_submit" \
  --data-urlencode 'xjxargs[]=Stooltips' \
  --data-urlencode 'xjxargs[]={"onapassword":"admin","standalone":"standalone","onausername":"admin","getpass":"admin"}' \
  --data-urlencode 'xjxargs[]=Slogingo')
check "xajax login returns Success" "Success" "$R"

# main page
M=$(curl -s -b "$CJ" "$BASE/")
check "main page shows logged-in user" "loggedin_user" "$M"
check "main page includes xajax core js" "xajax_core.js" "$M"
check "main page includes webwin plugin js" "webwin.js" "$M"

# xajax: tooltip window (real call from main page)
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE/" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=window_submit" \
  --data-urlencode 'xjxargs[]=Stooltips' \
  --data-urlencode 'xjxargs[]=Stooltip=>sys_alert,id=>tt_sys_alert')
check "xajax tooltip window returns json cmd" '"cmd"' "$R"

# xajax: open the edit_domain editor (real call from main page)
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE/" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=window_submit" \
  --data-urlencode 'xjxargs[]=Sedit_domain' \
  --data-urlencode 'xjxargs[]=Sjs=> ' \
  --data-urlencode 'xjxargs[]=Seditor')
check "xajax edit_domain editor returns json cmd" '"cmd"' "$R"

# xajax: run a subnet search (search_results window)
R=$(curl -s -b "$CJ" -c "$CJ" -X POST "$BASE/" -H "X-Requested-With: XMLHttpRequest" \
  --data-urlencode "xjxfun=window_submit" \
  --data-urlencode 'xjxargs[]=Ssearch_results' \
  --data-urlencode 'xjxargs[]=Ssearch_form_id=>qsearch_form')
check "xajax search_results returns json cmd" '"cmd"' "$R"
echo "--- search result sample ---"; echo "$R" | head -c 300; echo

echo
echo "RESULT: pass=$pass fail=$fail"
rm -f "$CJ"
[ "$fail" -eq 0 ]
