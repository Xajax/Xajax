<?php
/*
    File: xajax_handlers.inc.php

    All server-side handler functions, callable objects and events used by the
    PHP 8.2 compatibility test app. Kept in one file so the registration in
    index.php stays readable.
*/

// ---------------------------------------------------------------- functions

function greet($sName, $sGreeting = 'Hello')
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', $sGreeting . ', ' . $sName . '!');
    return $objResponse;
}

function addNumbers($nA, $nB)
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', (string)($nA + $nB));
    return $objResponse;
}

// Returns a PHP array; xajax converts it to a JS object literal.
function getArray()
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'array received');
    $objResponse->call('showObject', array(
        'name'   => 'xajax',
        'number' => 82,
        'nested' => array('a' => 1, 'b' => true, 'c' => null),
    ));
    return $objResponse;
}

function showFormData($aForm)
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', '<pre>' . htmlspecialchars(print_r($aForm, true)) . '</pre>');
    return $objResponse;
}

// Fluent interface: getGlobalResponse() returns the shared response object.
function fluentDemo()
{
    global $xajax;
    return $xajax->getGlobalResponse()
        ->assign('out', 'innerHTML', 'fluent <b>interface</b> works')
        ->alert('Fluent interface alert');
}

// Exercises assign/append/prepend/replace/clear/remove/create/insert.
function commandDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', '<span id="demo">AB</span>');
    $objResponse->append('demo', 'innerHTML', 'C');
    $objResponse->prepend('demo', 'innerHTML', 'Z');
    $objResponse->replace('demo', 'innerHTML', 'Z', 'Q');
    $objResponse->assign('out2', 'innerHTML', 'assigned to second target');
    return $objResponse;
}

// Exercises the DOM response commands.
function domDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->domStartResponse();
    $objResponse->domCreateElement('el', 'div');
    $objResponse->domSetAttribute('el', 'id', 'domMade');
    $objResponse->domSetAttribute('el', 'class', 'made');
    $objResponse->domAppendText('el', 'Created via DOM commands');
    $objResponse->domAppendChild('out', 'el');
    $objResponse->domEndResponse();
    return $objResponse;
}

// Exercises includeScript / includeCSS / waitFor / script / call.
function assetsDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->includeCSS('xajax_js/does-not-exist.css', 'screen');
    $objResponse->includeScript('xajax_js/does-not-exist.js', 'text/javascript', 'demoScript');
    $objResponse->waitFor('window.demoFlag === true', 5);
    $objResponse->script('window.demoRan = true;');
    $objResponse->call('jsCallback', 'from', 'xajax');
    $objResponse->assign('out', 'innerHTML', 'asset/script commands queued');
    return $objResponse;
}

// Exercises createInput / insertInput / setEvent / addHandler.
function inputDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('inputHost', 'innerHTML', '');
    $objResponse->createInput('inputHost', 'text', 'madeInput', 'madeInput');
    $objResponse->assign('madeInput', 'value', 'created at ' . date('H:i:s'));
    $objResponse->setEvent('madeInput', 'focus', 'this.select();');
    $objResponse->addHandler('out', 'click', 'clickedOut');
    return $objResponse;
}

// UTF-8 round trip: server echoes back what the client sent.
function echoText($sText)
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'You sent: ' . htmlspecialchars($sText));
    return $objResponse;
}

// Errors and messages.
function errorDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->debug('debug command emitted');
    $objResponse->alert('alert command emitted');
    $objResponse->confirmCommands(1, 'Cancel skips the next command');
    $objResponse->assign('out', 'innerHTML', 'this may be skipped');
    return $objResponse;
}

// Explicit JSON response type.
function jsonDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->setResponseType('JSON');
    $objResponse->assign('out', 'innerHTML', 'json response type');
    $objResponse->alert('json alert');
    return $objResponse;
}

// setReturnValue for synchronous calls.
function returnValueDemo()
{
    $objResponse = new xajaxResponse();
    $objResponse->setReturnValue('the return value');
    $objResponse->assign('out', 'innerHTML', 'return value set');
    return $objResponse;
}

// Custom response class extending xajaxResponse.
class customXajaxResponse extends xajaxResponse
{
    public function setStatus($sText)
    {
        $this->assign('status', 'innerHTML', $sText);
        return $this;
    }
}

function customResponseDemo()
{
    $objResponse = new customXajaxResponse();
    $objResponse->setStatus('status set through a custom response class');
    $objResponse->assign('out', 'innerHTML', 'custom response class works');
    return $objResponse;
}

// A function that raises a PHP warning, to exercise the xajax error handler.
function raisesWarning()
{
    trigger_error('deliberate test warning', E_USER_WARNING);
    $objResponse = new xajaxResponse();
    $objResponse->assign('out', 'innerHTML', 'warning raised');
    return $objResponse;
}

// ----------------------------------------------------------- callable object

class Calculator
{
    public $label = 'calculator';

    public function add($nA, $nB)
    {
        $objResponse = new xajaxResponse();
        $objResponse->assign('out', 'innerHTML', "$nA + $nB = " . ($nA + $nB));
        return $objResponse;
    }

    public function multiply($nA, $nB)
    {
        $objResponse = new xajaxResponse();
        $objResponse->assign('out', 'innerHTML', "$nA * $nB = " . ($nA * $nB));
        return $objResponse;
    }
}

// ------------------------------------------------------------ event handlers

function onEventAlpha($sArg = '')
{
    $objResponse = new xajaxResponse();
    $objResponse->append('eventLog', 'innerHTML', 'alpha handler ran with: ' . htmlspecialchars((string)$sArg) . '<br />');
    return $objResponse;
}

function onEventBeta($sArg = '')
{
    $objResponse = new xajaxResponse();
    $objResponse->append('eventLog', 'innerHTML', 'beta handler ran with: ' . htmlspecialchars((string)$sArg) . '<br />');
    return $objResponse;
}

// --------------------------------------------------------- processing events

/*
    Processing events may return an xajaxResponse, which is appended to the
    request response. Returning null instead would be appended to the response
    manager and reported as an invalid response, so both handlers return one.

    They emit a debug command so the self test can confirm the events fired.
*/
function beforeProcessing(&$bEndRequest)
{
    $objArgumentManager = xajaxArgumentManager::getInstance();
    $aArgs = $objArgumentManager->process();
    $GLOBALS['processingLog'][] = 'beforeProcessing (' . count($aArgs) . ' args)';
    $objResponse = new xajaxResponse();
    $objResponse->debug('beforeProcessing ran with ' . count($aArgs) . ' argument(s)');
    return $objResponse;
}

function afterProcessing($bEndRequest)
{
    $GLOBALS['processingLog'][] = 'afterProcessing';
    $objResponse = new xajaxResponse();
    $objResponse->debug('afterProcessing ran');
    return $objResponse;
}
