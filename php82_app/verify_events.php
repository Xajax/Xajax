<?php
/*
    Exercises the event system's fire() path and the comet function's call(),
    which had reference-assignment changes (& removed).
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$aIssues = array();
set_error_handler(function ($nNo, $sMsg, $sFile, $nLine) use (&$aIssues) {
    if (strpos($sMsg, 'deliberate') !== false) {
        return true;
    }
    $aIssues[] = "[$nNo] $sMsg ($sFile:$nLine)";
    return true;
});

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';
$objBootstrap = new xajax('index.php');
unset($objBootstrap);

$GLOBALS['log'] = array();

function handlerOne($sArg)
{
    $GLOBALS['log'][] = 'one:' . $sArg;
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'one');
    return $objResponse;
}

function handlerTwo($sArg)
{
    $GLOBALS['log'][] = 'two:' . $sArg;
    return null;
}

// xajaxEvent::fire dispatches to every registered handler.
require_once dirname(__DIR__) . '/xajax_core/plugin_layer/support/xajaxEvent.inc.php';

$objEvent = new xajaxEvent('eventAlpha');
$objEvent->addHandler(new xajaxUserFunction('handlerOne'));
$objEvent->addHandler(new xajaxUserFunction('handlerTwo'));

$objEvent->fire(array('payload'));

$bBoth = in_array('one:payload', $GLOBALS['log'], true) && in_array('two:payload', $GLOBALS['log'], true);
echo ($bBoth ? 'PASS' : 'FAIL') . " event fire() dispatches to all handlers\n";
echo '  log: ' . implode(', ', $GLOBALS['log']) . "\n";

// The comet function shares xajaxUserFunction's structure; its call() must work.
require_once dirname(__DIR__) . '/xajax_plugins/response/comet/xajaxCometFunction.inc.php';

function cometTarget($sArg)
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'comet:' . $sArg);
    return $objResponse;
}

$objComet = new xajaxCometFunction('cometTarget');
$objComet->call(array('x'));
$objResponseManager = xajaxResponseManager::getInstance();
$r = new ReflectionProperty('xajaxResponseManager', 'objResponse');
$r->setAccessible(true);
$objAccumulated = $r->getValue($objResponseManager);
$sOutput = $objAccumulated instanceof xajaxResponse ? $objAccumulated->getOutput() : '';
$bComet = strpos($sOutput, 'comet:x') !== false;
echo ($bComet ? 'PASS' : 'FAIL') . " comet function call()\n";

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
} else {
    echo 'Issues (' . count($aIssues) . "):\n";
    foreach ($aIssues as $sIssue) {
        echo "  $sIssue\n";
    }
}
