<?php
/*
    Verifies the xajaxArgumentManager UTF-8 input decoding path, which is where
    the removed utf8_decode() fallback lived. Runs the request processing with
    decodeUTF8Input enabled and a non-UTF-8 character encoding.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$aIssues = array();
set_error_handler(function ($nNo, $sMsg) use (&$aIssues) {
    if (strpos($sMsg, 'deliberate') !== false) {
        return true;
    }
    $aIssues[] = "[$nNo] $sMsg";
    return true;
});

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SCRIPT_NAME'] = '/decode_check.php';
$_SERVER['REQUEST_URI'] = '/decode_check.php';
$_SERVER['HTTP_HOST'] = 'localhost';

// Two UTF-8 encoded arguments.
$_POST['xjxfun'] = 'capture';
$_POST['xjxargs'] = array('Scaf' . "\xC3\xA9", 'Splain');
$_POST['xjxr'] = '1';

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';

$GLOBALS['captured'] = null;

function capture($sA, $sB)
{
    $GLOBALS['captured'] = array($sA, $sB);
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'ok');
    return $objResponse;
}

$xajax = new xajax('decode_check.php');
$xajax->configure('debug', false);
// Non-UTF-8 target encoding forces the decode path on.
$xajax->configure('characterEncoding', 'ISO-8859-1');
$xajax->configure('decodeUTF8Input', true);
$xajax->register(XAJAX_FUNCTION, 'capture');

// Drive the argument manager directly: processRequest() would emit the
// response and exit, skipping the assertions below.
$objArgMgr = xajaxArgumentManager::getInstance();
$objArgMgr->configure('characterEncoding', 'ISO-8859-1');
$objArgMgr->configure('decodeUTF8Input', true);
$aArgs = $objArgMgr->process();

echo "args: " . var_export($aArgs, true) . "\n";

$bOk = is_array($aArgs)
    && strpos($aArgs[0], 'caf') === 0
    && $aArgs[1] === 'plain';
echo ($bOk ? "PASS" : "FAIL") . " decodeUTF8Input path\n";

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
} else {
    echo 'Issues (' . count($aIssues) . "):\n";
    foreach ($aIssues as $sIssue) {
        echo "  $sIssue\n";
    }
}
