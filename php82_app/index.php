<?php
/*
    File: index.php

    PHP 8.2 compatibility test app for Xajax.

    Exercises the full server-side feature set of the library on PHP 8.2:
    functions, callable objects, events, processing events, the response
    command set (assign/append/prepend/replace/clear/remove/create/insert),
    DOM commands, script/CSS includes, parameter types, JSON and XML response
    types, custom response classes, UTF-8 round trips and error handling.

    See README.md for run instructions.
*/

require_once dirname(__DIR__) . '/xajax_core/xajax.inc.php';

$GLOBALS['processingLog'] = array();

$xajax = new xajax();

// Enable debugging and use the uncompressed javascript sources.
// The app lives one directory below the library, so the javascript URI is '../'.
// requestURI is set explicitly so the app works behind any proxy or port.
// Debug opens a popup window, so it is opt-in via ?debug=1.
$xajax->configure('debug', isset($_GET['debug']));
$xajax->configure('javascript URI', '../');
$xajax->configure('requestURI', basename(__FILE__));
$xajax->configure('useUncompressedScripts', true);
$xajax->configure('deferScriptGeneration', false);
$xajax->configure('errorHandler', true);
$xajax->configure('cleanBuffer', true);

require_once __DIR__ . '/xajax_handlers.inc.php';

// ------------------------------------------------------------ registration

$xajax->register(XAJAX_FUNCTION, 'greet');
$xajax->register(XAJAX_FUNCTION, 'addNumbers');
$xajax->register(XAJAX_FUNCTION, 'getArray');
$xajax->register(XAJAX_FUNCTION, 'showFormData');
$xajax->register(XAJAX_FUNCTION, 'fluentDemo');
$xajax->register(XAJAX_FUNCTION, 'commandDemo');
$xajax->register(XAJAX_FUNCTION, 'domDemo');
$xajax->register(XAJAX_FUNCTION, 'assetsDemo');
$xajax->register(XAJAX_FUNCTION, 'inputDemo');
$xajax->register(XAJAX_FUNCTION, 'echoText');
$xajax->register(XAJAX_FUNCTION, 'errorDemo');
$xajax->register(XAJAX_FUNCTION, 'jsonDemo');
$xajax->register(XAJAX_FUNCTION, 'returnValueDemo');
$xajax->register(XAJAX_FUNCTION, 'customResponseDemo');
$xajax->register(XAJAX_FUNCTION, 'raisesWarning');

// Parameter types: fixed values, form values and input values.
$reqGreetFixed = $xajax->register(XAJAX_FUNCTION, 'greet');
$reqGreetFixed->setParameter(0, XAJAX_QUOTED_VALUE, 'fixed argument');
$reqGreetFixed->setParameter(1, XAJAX_QUOTED_VALUE, 'Hi');

$reqGreetForm = $xajax->register(XAJAX_FUNCTION, 'greet');
$reqGreetForm->setParameter(0, XAJAX_INPUT_VALUE, 'nameInput');
$reqGreetForm->setParameter(1, XAJAX_QUOTED_VALUE, 'Hello');

$reqAddNumbers = $xajax->register(XAJAX_FUNCTION, 'addNumbers');
$reqAddNumbers->setParameter(0, XAJAX_INPUT_VALUE, 'numA');
$reqAddNumbers->setParameter(1, XAJAX_INPUT_VALUE, 'numB');

$reqEcho = $xajax->register(XAJAX_FUNCTION, 'echoText');
$reqEcho->setParameter(0, XAJAX_INPUT_VALUE, 'textInput');

$reqShowForm = $xajax->register(XAJAX_FUNCTION, 'showFormData');
$reqShowForm->setParameter(0, XAJAX_FORM_VALUES, 'demoForm');

// Callable object.
$objCalculator = new Calculator();
$reqCalculator = $xajax->register(XAJAX_CALLABLE_OBJECT, $objCalculator);

// Events with two handlers each.
$eventAlpha = $xajax->register(XAJAX_EVENT, 'eventAlpha');
$xajax->register(XAJAX_EVENT_HANDLER, 'eventAlpha', 'onEventAlpha');
$xajax->register(XAJAX_EVENT_HANDLER, 'eventAlpha', 'onEventBeta');

$eventBeta = $xajax->register(XAJAX_EVENT, 'eventBeta');
$xajax->register(XAJAX_EVENT_HANDLER, 'eventBeta', 'onEventBeta');

// Processing events.
$xajax->register(XAJAX_PROCESSING_EVENT, XAJAX_PROCESSING_EVENT_BEFORE, 'beforeProcessing');
$xajax->register(XAJAX_PROCESSING_EVENT, XAJAX_PROCESSING_EVENT_AFTER, 'afterProcessing');

// Handle the request and stop for xajax calls; fall through to the page body
// for the initial page load.
$xajax->processRequest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Xajax on PHP <?php echo PHP_VERSION; ?></title>
<?php $xajax->printJavascript(); ?>
<style>
    body { font-family: system-ui, sans-serif; margin: 1.5rem; line-height: 1.4; }
    fieldset { margin-bottom: 1rem; }
    legend { font-weight: bold; }
    button { margin: 0 .3rem .3rem 0; }
    #out, #out2, #status { min-height: 1.4em; padding: .4rem; background: #f4f4f4; border: 1px solid #ddd; margin-top: .4rem; }
    #eventLog { padding: .4rem; background: #f9f9f9; border: 1px solid #eee; min-height: 1.4em; }
    pre { margin: 0; white-space: pre-wrap; }
    .row { margin-bottom: .4rem; }
</style>
<script type="text/javascript">
/* <![CDATA[ */
window.showObject = function (o) {
    document.getElementById('out').innerHTML =
        'object received: ' + o.name + ' / ' + o.number + ' / nested.a=' + o.nested.a + ' / nested.b=' + o.nested.b;
};
window.jsCallback = function (a, b) {
    document.getElementById('out2').innerHTML = 'jsCallback got: ' + a + ' ' + b;
};
window.clickedOut = function () {
    document.getElementById('out2').innerHTML = 'click handler fired';
};
window.demoFlag = false;
/* ]]> */
</script>
</head>
<body>

<h1>Xajax on PHP <?php echo PHP_VERSION; ?></h1>
<p>Server PHP: <b><?php echo PHP_VERSION; ?></b> &middot; Xajax version: <b><?php echo xajax::getVersion(); ?></b></p>
<p>Every button below issues a real xajax request. Results appear in the output boxes.</p>

<div id="out">ready</div>
<div id="out2">second target</div>
<div id="status"></div>
<div id="inputHost"></div>
<div id="eventLog"></div>

<fieldset>
    <legend>Functions and parameters</legend>
    <button onclick='<?php $reqGreetFixed->printScript(); ?>'>greet (quoted values)</button>
    <button onclick='<?php $reqGreetForm->printScript(); ?>'>greet (input value)</button>
    <button onclick='<?php $reqAddNumbers->printScript(); ?>'>addNumbers (input values)</button>
    <button onclick="xajax_greet('direct call', 'Hi');">greet (direct JS call)</button>
    <div class="row">
        name: <input type="text" id="nameInput" value="World" />
        a: <input type="text" id="numA" value="20" size="4" />
        b: <input type="text" id="numB" value="22" size="4" />
    </div>
</fieldset>

<fieldset>
    <legend>Response command set</legend>
    <button onclick="xajax_commandDemo();">assign/append/prepend/replace</button>
    <button onclick="xajax_domDemo();">DOM commands</button>
    <button onclick="xajax_assetsDemo();">script/CSS/waitFor/call</button>
    <button onclick="xajax_inputDemo();">createInput/setEvent/addHandler</button>
    <button onclick="xajax_getArray();">return PHP array as JS object</button>
    <button onclick="xajax_fluentDemo();">fluent interface</button>
    <button onclick="xajax_errorDemo();">debug/alert/confirmCommands</button>
</fieldset>

<fieldset>
    <legend>Response types and classes</legend>
    <button onclick="xajax_jsonDemo();">JSON response type</button>
    <button onclick="xajax_returnValueDemo();">setReturnValue (synchronous)</button>
    <button onclick="xajax_customResponseDemo();">custom response class</button>
</fieldset>

<fieldset>
    <legend>Callable object</legend>
    <button onclick="xajax_Calculator.add(6, 7);">calculator.add(6, 7)</button>
    <button onclick="xajax_Calculator.multiply(6, 7);">calculator.multiply(6, 7)</button>
</fieldset>

<fieldset>
    <legend>Events</legend>
    <button onclick="xajax_event_eventAlpha('browser payload');">fire eventAlpha (2 handlers)</button>
    <button onclick="xajax_event_eventBeta('browser payload');">fire eventBeta (1 handler)</button>
</fieldset>

<fieldset>
    <legend>Forms and text</legend>
    <form id="demoForm" onsubmit="return false;">
        <div class="row"><input type="text" name="fieldA" value="alpha" /></div>
        <div class="row"><input type="text" name="fieldB" value="beta" /></div>
        <div class="row"><input type="checkbox" name="checkMe" value="1" checked="checked" /> checked</div>
    </form>
    <button onclick='<?php $reqShowForm->printScript(); ?>'>submit form values</button>
    <div class="row">
        text: <input type="text" id="textInput" value="caf&eacute; &#8364; &#128512;" size="30" />
        <button onclick='<?php $reqEcho->printScript(); ?>'>echo UTF-8 text</button>
    </div>
</fieldset>

<fieldset>
    <legend>Error handling</legend>
    <button onclick="xajax_raisesWarning();">call a function that raises a warning</button>
</fieldset>

<fieldset>
    <legend>Generated javascript</legend>
    <button onclick="document.getElementById('jsdump').style.display='block';return false;">show generated javascript</button>
    <pre id="jsdump" style="display:none; max-height: 20em; overflow: auto; background:#272822; color:#f8f8f2; padding:.5rem;"><?php echo htmlspecialchars($xajax->getJavascript()); ?></pre>
</fieldset>

<p><small>Processing events fired on this page load:
<?php echo htmlspecialchars(implode(' | ', $GLOBALS['processingLog'])); ?></small></p>

</body>
</html>
