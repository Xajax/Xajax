<?php
/*
    Strict PHP 8.2 run: loads every plugin and drives every demo handler with
    E_ALL, converting any deprecation/notice/warning into a hard failure.

    Usage:
        php php82_app/verify_strict.php
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$aIssues = array();
set_error_handler(function ($nNo, $sMsg, $sFile, $nLine) use (&$aIssues) {
    // Ignore the deliberate warnings/notices raised by the error-handling demos.
    if (strpos($sMsg, 'deliberate') !== false) {
        return true;
    }
    $aIssues[] = "[$nNo] $sMsg ($sFile:$nLine)";
    return true;
});

require_once dirname(__DIR__) . '/xajax_core/xajax.inc.php';

$xajax = new xajax('index.php');
$xajax->configure('javascript URI', '../');
$xajax->configure('deferScriptGeneration', false);

// Loading the plugins is what exercises the constructor renames at startup.
require_once __DIR__ . '/xajax_handlers.inc.php';

$xajax->register(XAJAX_FUNCTION, 'greet');
$xajax->register(XAJAX_FUNCTION, 'addNumbers');
$xajax->register(XAJAX_FUNCTION, 'getArray');
$xajax->register(XAJAX_FUNCTION, 'showFormData');
$xajax->register(XAJAX_FUNCTION, 'commandDemo');
$xajax->register(XAJAX_FUNCTION, 'domDemo');
$xajax->register(XAJAX_FUNCTION, 'assetsDemo');
$xajax->register(XAJAX_FUNCTION, 'inputDemo');
$xajax->register(XAJAX_FUNCTION, 'echoText');
$xajax->register(XAJAX_FUNCTION, 'jsonDemo');
$xajax->register(XAJAX_FUNCTION, 'returnValueDemo');
$xajax->register(XAJAX_FUNCTION, 'customResponseDemo');
$xajax->register(XAJAX_CALLABLE_OBJECT, new Calculator());
$xajax->register(XAJAX_EVENT, 'eventAlpha');
$xajax->register(XAJAX_EVENT_HANDLER, 'eventAlpha', 'onEventAlpha');
$xajax->register(XAJAX_EVENT_HANDLER, 'eventAlpha', 'onEventBeta');

// Generate the client script for every registered object. This drives the
// script generators and the plugin manager's generateHash().
$sScript = $xajax->getJavascript();
echo 'script bytes: ' . strlen($sScript) . "\n";

// Generate deferred scripts too, which exercises the output directory creation.
$xajax->configure('deferScriptGeneration', true);
$sScript = $xajax->getJavascript();
echo 'deferred script bytes: ' . strlen($sScript) . "\n";
$xajax->configure('deferScriptGeneration', false);

// Call each handler through the request dispatch and collect the responses.
$aCalls = array(
    'greet'              => array('World'),
    'addNumbers'         => array('6', '7'),
    'getArray'           => array(),
    'commandDemo'        => array(),
    'domDemo'            => array(),
    'assetsDemo'         => array(),
    'inputDemo'          => array(),
    'echoText'           => array("caf\xC3\xA9 \xE2\x82\xAC"),
    'jsonDemo'           => array(),
    'returnValueDemo'    => array(),
    'customResponseDemo' => array(),
    'Calculator'         => array(),
);

// Directly invoke the handlers (the request dispatch needs a populated $_POST).
foreach ($aCalls as $sFn => $aArgs) {
    $mFn = $sFn === 'Calculator' ? array(new Calculator(), 'add') : $sFn;
    if (!is_callable($mFn)) {
        echo "skip $sFn\n";
        continue;
    }
    $aRealArgs = $sFn === 'Calculator' ? array('6', '7') : $aArgs;
    $objResponse = call_user_func_array($mFn, $aRealArgs);
    if ($objResponse instanceof xajaxResponse) {
        $sOut = $objResponse->getOutput();
        echo str_pad($sFn, 20) . ': ' . strlen($sOut) . " bytes\n";
    } else {
        echo str_pad($sFn, 20) . ": (no response object)\n";
    }
}

// Drive the event handlers.
onEventAlpha('payload');
onEventBeta('payload');

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
    exit(0);
}
echo 'Issues found (' . count($aIssues) . "):\n";
foreach ($aIssues as $sIssue) {
    echo "  $sIssue\n";
}
exit(1);
