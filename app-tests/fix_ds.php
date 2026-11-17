<?php
$f = $argv[1];
$s = file_get_contents($f);
// PHP 8 rejects unquoted array keys as undefined constants, both in plain
// accesses ($jobs[id]) and inside string interpolation ("{$jobs[id]}").  Quote
// them.  Keys in this app are all plain lowercase identifiers.
$s = preg_replace_callback('/(\{\$)(\w+)\[([a-z_]+)\]/', function ($m) {
    return $m[1] . $m[2] . "['" . $m[3] . "']";
}, $s);
$s = preg_replace_callback('/(\$)(\w+)\[([a-z_]+)\]/', function ($m) {
    return $m[1] . $m[2] . "['" . $m[3] . "']";
}, $s);
file_put_contents($f, $s);
echo "[setup] quoted array keys in functions.php (app-side)\n";
