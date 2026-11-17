<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';
$objBootstrap = new xajax('index.php');
unset($objBootstrap);

require dirname(__DIR__) . '/xajax_core/xajaxCompress.inc.php';

$sSrc = file_get_contents(dirname(__DIR__) . '/xajax_js/xajax_core_uncompressed.js');
$sOut = xajaxCompressFile($sSrc);
echo 'in=' . strlen($sSrc) . ' out=' . strlen($sOut) . "\n";
echo (strlen($sOut) > 0 ? "compress ok\n" : "compress EMPTY\n");

// A JSON-looking literal inside a string must survive compression.
$sSample = 'function f(){ var s = "a//b/*c*/d"; return s; }';
$sComp = xajaxCompressFile($sSample);
echo "sample out: $sComp\n";
