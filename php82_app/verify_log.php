<?php
/*
    Exercises the error-log file path, which is where the strftime() -> date()
    replacement lives (strftime is removed in PHP 8.2).
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';

$sLog = sys_get_temp_dir() . '/xajax_php82_error.log';
@unlink($sLog);

$GLOBALS['xajaxErrorHandlerText'] = '';

// The function plugin reads xjxfun when it is constructed, so seed the request
// data before the xajax object is created.
$_POST['xjxfun'] = 'boom';
$_POST['xjxargs'] = array();
$_POST['xjxr'] = '1';
$_SERVER['REQUEST_METHOD'] = 'POST';

$xajax = new xajax('index.php');
$xajax->configure('javascript URI', '../');
$xajax->configure('deferScriptGeneration', false);
$xajax->configure('errorHandler', true);
$xajax->configure('logFile', $sLog);
$xajax->configure('debug', false);
// Keep processRequest() from exiting so the assertions below still run.
$xajax->configure('exitAllowed', false);

$xajax->register(XAJAX_FUNCTION, 'boom');

function boom()
{
    trigger_error('a deliberate error for the log test', E_USER_WARNING);
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'ok');
    return $objResponse;
}

// processRequest() sends output; capture it (exitAllowed is off, so it returns).
ob_start();
$xajax->processRequest();
$sBody = ob_get_clean();

echo 'body: ' . substr($sBody, 0, 120) . "\n";

$bLogged = is_file($sLog) && filesize($sLog) > 0;
echo ($bLogged ? 'PASS' : 'FAIL') . " error log file written\n";
if ($bLogged) {
    $sContents = file_get_contents($sLog);
    echo '  log: ' . trim($sContents) . "\n";
    // The timestamp must not be empty (date() must have produced something).
    $bStamp = preg_match('/[A-Z][a-z]{2} \d+ \d{4} \d{2}:\d{2}:\d{2} [AP]M/', $sContents);
    echo ($bStamp ? 'PASS' : 'FAIL') . " log timestamp formatted\n";
}
@unlink($sLog);
