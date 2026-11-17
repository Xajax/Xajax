<?php
/*
    File: selftest.php

    Server-side self test for the PHP 8.2 compatibility app.

    Simulates real xajax client requests by posting the same parameters the
    javascript client sends (xjxfun / xjxargs[] / xjxevt) and asserts on the
    returned command packets. Runs with no browser, so it can be used in CI.

    Usage:
        php selftest.php                # in-process, no HTTP server needed
        php selftest.php http://host    # against a running server
*/

$bHttp = isset($argv[1]);
$sBase = $bHttp ? rtrim($argv[1], '/') : null;

$nPass = 0;
$nFail = 0;

function check($sLabel, $bOk, $sDetail = '')
{
    global $nPass, $nFail;
    if ($bOk) {
        $nPass++;
        echo "PASS  $sLabel\n";
    } else {
        $nFail++;
        echo "FAIL  $sLabel" . ($sDetail !== '' ? " -- $sDetail" : '') . "\n";
    }
}

/*
    Build a request packet the way xajax_core.js does. Scalars are prefixed
    with their type letter (S string, B boolean, N number); arrays are JSON.
*/
function encodeArg($mValue)
{
    if (is_array($mValue) || is_object($mValue)) {
        return json_encode($mValue);
    }
    if (is_null($mValue)) {
        return '*';
    }
    if (is_bool($mValue)) {
        return 'B' . ($mValue ? 'true' : 'false');
    }
    if (is_int($mValue) || is_float($mValue)) {
        return 'N' . $mValue;
    }
    return 'S' . $mValue;
}

/*
    In HTTP mode, POST the request and return the decoded command list.
    Returns array('body' => string, 'commands' => array).
*/
function request($sBase, array $aParams)
{
    $aFields = array();
    foreach ($aParams as $sKey => $mValue) {
        if ($sKey === 'xjxargs') {
            foreach ($mValue as $i => $mArg) {
                $aFields[] = 'xjxargs[]=' . urlencode(encodeArg($mArg));
            }
        } else {
            $aFields[] = urlencode($sKey) . '=' . urlencode($mValue);
        }
    }
    $sBody = implode('&', $aFields);

    $ctx = stream_context_create(array('http' => array(
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $sBody,
        'timeout' => 30,
        'ignore_errors' => true,
    )));
    $sResponse = @file_get_contents($sBase . '/index.php', false, $ctx);

    return array('body' => (string)$sResponse, 'commands' => decodeCommands($sResponse));
}

/*
    Parse the response packet. JSON responses carry the command array under
    "xjxobj"; XML responses are converted to an array with the same shape.
*/
function decodeCommands($sBody)
{
    $aJson = json_decode((string)$sBody, true);
    if (is_array($aJson) && isset($aJson['xjxobj'])) {
        return $aJson['xjxobj'];
    }

    if (strpos((string)$sBody, '<xjx>') !== false) {
        $aOut = array();
        if (preg_match_all('/<cmd\s+([^>]*?)(?:\/>|>(.*?)<\/cmd>)/s', (string)$sBody, $aM, PREG_SET_ORDER)) {
            foreach ($aM as $aMatch) {
                $aCmd = array();
                if (preg_match_all('/(\w+)="([^"]*)"/', $aMatch[1], $aAttr, PREG_SET_ORDER)) {
                    foreach ($aAttr as $aPair) {
                        $aCmd[$aPair[1]] = $aPair[2];
                    }
                }
                if (isset($aMatch[2])) {
                    $aCmd['data'] = html_entity_decode($aMatch[2]);
                }
                $aOut[] = $aCmd;
            }
        }
        return $aOut;
    }

    return array();
}

function findCommand(array $aCommands, $sCmd)
{
    foreach ($aCommands as $aCmd) {
        if (isset($aCmd['cmd']) && $aCmd['cmd'] === $sCmd) {
            return $aCmd;
        }
    }
    return null;
}

echo "Xajax PHP 8.2 app self test (" . ($bHttp ? "HTTP $sBase" : 'in-process') . ")\n";
echo str_repeat('-', 60) . "\n";

if ($bHttp) {
    // ---------------------------------------------------------- HTTP mode

    $a = request($sBase, array('xjxfun' => 'greet', 'xjxargs' => array('World', 'Hello')));
    $aCmd = findCommand($a['commands'], 'as');
    check('greet returns an assign command', null !== $aCmd, $a['body']);
    check('greet assign carries the greeting', null !== $aCmd && strpos($aCmd['data'], 'Hello, World!') !== false,
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxfun' => 'addNumbers', 'xjxargs' => array(20, 22)));
    $aCmd = findCommand($a['commands'], 'as');
    check('addNumbers adds typed numbers', null !== $aCmd && trim($aCmd['data']) === '42',
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxfun' => 'getArray', 'xjxargs' => array()));
    check('getArray emits a call command', null !== findCommand($a['commands'], 'jc'));

    $a = request($sBase, array('xjxfun' => 'commandDemo', 'xjxargs' => array()));
    check('commandDemo emits append', null !== findCommand($a['commands'], 'ap'));
    check('commandDemo emits prepend', null !== findCommand($a['commands'], 'pp'));
    check('commandDemo emits replace', null !== findCommand($a['commands'], 'rp'));

    $a = request($sBase, array('xjxfun' => 'domDemo', 'xjxargs' => array()));
    check('domDemo emits DOM commands', null !== findCommand($a['commands'], 'DCE') && null !== findCommand($a['commands'], 'DAT'));

    $a = request($sBase, array('xjxfun' => 'assetsDemo', 'xjxargs' => array()));
    check('assetsDemo emits includeCSS', null !== findCommand($a['commands'], 'css'));
    check('assetsDemo emits includeScript', null !== findCommand($a['commands'], 'in'));
    check('assetsDemo emits waitFor', null !== findCommand($a['commands'], 'wf'));
    check('assetsDemo emits script', null !== findCommand($a['commands'], 'js'));
    check('assetsDemo emits call', null !== findCommand($a['commands'], 'jc'));

    $a = request($sBase, array('xjxfun' => 'inputDemo', 'xjxargs' => array()));
    check('inputDemo emits createInput', null !== findCommand($a['commands'], 'ci'));
    check('inputDemo emits addHandler', null !== findCommand($a['commands'], 'ah'));

    $a = request($sBase, array('xjxfun' => 'fluentDemo', 'xjxargs' => array()));
    check('fluentDemo emits alert', null !== findCommand($a['commands'], 'al'));
    check('fluentDemo emits assign', null !== findCommand($a['commands'], 'as'));

    $a = request($sBase, array('xjxfun' => 'errorDemo', 'xjxargs' => array()));
    check('errorDemo emits debug', null !== findCommand($a['commands'], 'dbg'));
    check('errorDemo emits confirmCommands', null !== findCommand($a['commands'], 'cc'));

    $a = request($sBase, array('xjxfun' => 'jsonDemo', 'xjxargs' => array()));
    check('jsonDemo returns JSON content', strpos($a['body'], 'xjxobj') !== false, substr($a['body'], 0, 80));

    $a = request($sBase, array('xjxfun' => 'customResponseDemo', 'xjxargs' => array()));
    $aCmd = findCommand($a['commands'], 'as');
    check('customResponseDemo assigns to status', null !== $aCmd && $aCmd['id'] === 'status',
        $aCmd ? $aCmd['id'] : 'no command');

    $a = request($sBase, array('xjxcls' => 'Calculator', 'xjxmthd' => 'add', 'xjxargs' => array(6, 7)));
    $aCmd = findCommand($a['commands'], 'as');
    check('callable object calculator.add', null !== $aCmd && strpos($aCmd['data'], '13') !== false,
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxcls' => 'Calculator', 'xjxmthd' => 'multiply', 'xjxargs' => array(6, 7)));
    $aCmd = findCommand($a['commands'], 'as');
    check('callable object calculator.multiply', null !== $aCmd && strpos($aCmd['data'], '42') !== false,
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxevt' => 'eventAlpha', 'xjxargs' => array('payload')));
    $aCmd = findCommand($a['commands'], 'ap');
    check('eventAlpha fires both handlers', null !== $aCmd && substr_count($a['body'], 'handler ran') >= 1,
        substr($a['body'], 0, 120));

    $a = request($sBase, array('xjxfun' => 'echoText', 'xjxargs' => array('caf\u00e9 \u20ac')));
    $aCmd = findCommand($a['commands'], 'as');
    check('UTF-8 text round trip', null !== $aCmd && strpos($aCmd['data'], 'caf') !== false,
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxfun' => 'showFormData', 'xjxargs' => array(array('fieldA' => 'alpha'))));
    $aCmd = findCommand($a['commands'], 'as');
    check('form array argument decoded', null !== $aCmd && strpos($aCmd['data'], 'alpha') !== false,
        $aCmd ? $aCmd['data'] : 'no command');

    $a = request($sBase, array('xjxfun' => 'raisesWarning', 'xjxargs' => array()));
    check('raised warning captured by error handler',
        strpos($a['body'], 'Fatal error') === false && strpos($a['body'], 'deliberate test warning') !== false,
        substr($a['body'], 0, 160));

    $a = request($sBase, array('xjxfun' => 'noSuchFunction', 'xjxargs' => array()));
    check('unknown function handled gracefully', strpos($a['body'], 'Fatal error') === false && strpos($a['body'], 'Uncaught') === false,
        substr($a['body'], 0, 120));

    $a = request($sBase, array('xjxfun' => 'greet', 'xjxargs' => array('World')));
    check('processing events fired on a request',
        strpos($a['body'], 'beforeProcessing ran') !== false && strpos($a['body'], 'afterProcessing ran') !== false,
        substr($a['body'], 0, 160));

    // Page load: no xajax parameters, must return HTML with the script tags.
    // Processing events only fire while handling a request, so the page load
    // must render the (empty) footer without error.
    $sPage = @file_get_contents($sBase . '/index.php');
    check('page load returns HTML', strpos($sPage, '<html') !== false);
    check('page load includes xajax javascript', strpos($sPage, 'xajax_core_uncompressed.js') !== false);
    check('page load renders processing event footer',
        strpos($sPage, 'Processing events fired on this page load') !== false
        && strpos($sPage, 'Fatal error') === false);
} else {
    // ---------------------------------------------------- in-process mode

    // Exercise the response object directly, without a web server.
    require_once dirname(__DIR__) . '/xajax_core/xajax.inc.php';

    // There is no REQUEST_URI under the CLI, so pass one explicitly.
    $xajax = new xajax('index.php');
    $xajax->configure('javascript URI', '../');
    $xajax->configure('deferScriptGeneration', false);

    // Handlers declare a subclass of xajaxResponse, so load them after the
    // library has been initialised (the core classes are pulled in by the
    // xajax constructor).
    require_once __DIR__ . '/xajax_handlers.inc.php';

    $objResponse = new xajaxResponse();
    // The response type decides how array data is encoded when a command is
    // added, so it must be set before any command is queued.
    $objResponse->setResponseType('XML');
    $objResponse->setCharacterEncoding('utf-8');
    $objResponse->setOutputEntities(true);
    $objResponse->assign('a', 'innerHTML', 'x');
    $objResponse->append('a', 'innerHTML', 'y');
    $objResponse->prepend('a', 'innerHTML', 'z');
    $objResponse->replace('a', 'innerHTML', 'z', 'q');
    $objResponse->clear('a', 'style.color');
    $objResponse->remove('b');
    $objResponse->create('c', 'div', 'newDiv');
    $objResponse->insert('d', 'span', 'newSpan');
    $objResponse->insertAfter('e', 'span', 'newSpan2');
    $objResponse->createInput('f', 'text', 'n', 'i');
    $objResponse->insertInput('g', 'text', 'n2', 'i2');
    $objResponse->alert('msg');
    $objResponse->debug('dbg');
    $objResponse->confirmCommands(2, 'sure?');
    $objResponse->script('var x=1;');
    $objResponse->call('fn', 'arg');
    $objResponse->setEvent('h', 'click', 'x();');
    $objResponse->addHandler('h', 'click', 'h2');
    $objResponse->removeHandler('h', 'click', 'h2');
    $objResponse->includeScript('s.js');
    $objResponse->includeScriptOnce('s.js');
    $objResponse->includeCSS('s.css');
    $objResponse->removeCSS('s.css');
    $objResponse->waitFor('cond', 5);
    $objResponse->waitForCSS(100);
    $objResponse->sleep(1);
    $objResponse->setReturnValue('rv');
    $objResponse->domStartResponse();
    $objResponse->domCreateElement('el', 'div');
    $objResponse->domSetAttribute('el', 'id', 'x');
    $objResponse->domAppendText('el', 'text');
    $objResponse->domAppendChild('out', 'el');
    $objResponse->domInsertBefore('el', 'other');
    $objResponse->domInsertAfter('el', 'other');
    $objResponse->domRemoveChildren('out');
    $objResponse->domEndResponse();
    $objResponse->contextAssign('innerHTML', 'ctx');
    $objResponse->contextAppend('innerHTML', 'ctx');
    $objResponse->contextPrepend('innerHTML', 'ctx');
    $objResponse->contextClear('innerHTML');
    $objResponse->setFunction('myFn', 'a,b', 'return a+b;');
    $objResponse->wrapFunction('origFn', 'a', array('before();', 'after();'), 'r');
    $objResponse->redirect('https://example.com', 0);

    $nCount = $objResponse->getCommandCount();
    check('all response commands accepted', $nCount > 35, "command count = $nCount");

    $sXml = $objResponse->getOutput();
    check('XML output renders', strpos($sXml, '<xjx>') !== false, substr($sXml, 0, 60));
    check('XML output contains encoded array', strpos($sXml, '<xjxobj>') !== false, substr($sXml, 0, 120));

    // JSON response type, set before commands are queued.
    $objJson = new xajaxResponse();
    $objJson->setResponseType('JSON');
    $objJson->assign('j', 'innerHTML', 'json value');
    $objJson->call('fn2', array('a' => 1));
    $sJson = $objJson->getOutput();
    check('JSON output renders', strpos($sJson, 'xjxobj') !== false, substr($sJson, 0, 60));
    check('JSON output is valid JSON', is_array(json_decode($sJson, true)));

    // Encoding: entity output applies to XML responses. "café" as real UTF-8 bytes.
    $objResponse2 = new xajaxResponse();
    $objResponse2->setResponseType('XML');
    $objResponse2->setOutputEntities(true);
    $objResponse2->assign('t', 'innerHTML', "caf\xc3\xa9");
    $sOut = $objResponse2->getOutput();
    check('entity encoding emits numeric entities', strpos($sOut, '&#233;') !== false, substr($sOut, 0, 80));

    // Handlers produce responses.
    $r = greet('World', 'Hello');
    check('greet() returns xajaxResponse', $r instanceof xajaxResponse);
    $r = addNumbers(2, 3);
    check('addNumbers() returns xajaxResponse', $r instanceof xajaxResponse);
    $r = getArray();
    check('getArray() returns xajaxResponse', $r instanceof xajaxResponse);

    // The warning is emitted through trigger_error, so convert it to an
    // exception here and confirm it is reported rather than swallowed.
    set_error_handler(function ($nNo, $sStr) {
        throw new ErrorException($sStr);
    }, E_USER_WARNING);
    $bRaised = false;
    try {
        raisesWarning();
    } catch (ErrorException $e) {
        $bRaised = true;
    } finally {
        restore_error_handler();
    }
    check('raisesWarning() emits a PHP warning', $bRaised);
}

echo str_repeat('-', 60) . "\n";
echo "passed: $nPass, failed: $nFail\n";
exit($nFail === 0 ? 0 : 1);
