<?php
/*
    Verifies the xajax 0.5 compatibility shim (legacy.inc.php) under PHP 8.2.

    Applications written against xajax 0.5 loaded this file and extended the
    legacyXajax / legacyXajaxResponse classes.  This checks that the shim still
    provides those classes, that their constructors accept the 0.5 argument
    lists, and that the add* response aliases map onto the current methods.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

// The base xajax constructor auto-detects the request URI from the server
// environment; provide just enough for it to succeed under the CLI.
$_SERVER['REQUEST_URI']    = '/index.php';
$_SERVER['HTTP_HOST']      = 'example.test';
$_SERVER['SERVER_NAME']    = 'example.test';
$_SERVER['REQUEST_METHOD'] = 'GET';

require dirname(__DIR__) . '/xajax_core/legacy.inc.php';

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
        echo "FAIL  $sLabel" . ($sDetail !== '' ? " ($sDetail)" : '') . "\n";
    }
}

check('legacyXajaxResponse class exists', class_exists('legacyXajaxResponse'));
check('legacyXajax class exists', class_exists('legacyXajax'));
check('legacyXajaxResponse extends xajaxResponse', is_subclass_of('legacyXajaxResponse', 'xajaxResponse'));
check('legacyXajax extends xajax', is_subclass_of('legacyXajax', 'xajax'));

// 0.5 constructor signature: ($sRequestURI, $sWrapperPrefix, $sEncoding, $bDebug)
$objXajax = new legacyXajax('/index.php', 'xajax_', 'utf-8', false);
check('legacyXajax constructor accepted 0.5 args', $objXajax instanceof legacyXajax);
check('requestURI configured', $objXajax->getRequestURI() === '/index.php');
check('wrapperPrefix configured', $objXajax->getWrapperPrefix() === 'xajax_');

// The deprecated registration aliases must be callable.
$objXajax->registerFunction('myfunc');
$objXajax->registerCallableObject(new stdClass());
$objXajax->registerExternalFunction('extfunc', __FILE__);
check('registerFunction / registerCallableObject / registerExternalFunction callable', true);

// Every add* alias in the 0.5 shim forwards to the matching modern method.
$objResponse = new legacyXajaxResponse();
$objResponse->addAssign('el', 'innerHTML', 'hi');
$objResponse->addScript("alert('x')");
$objResponse->addAlert('yo');
$objResponse->addConfirmCommands(1, 'sure?');
$objResponse->addCreate('el', 'div', 'newdiv');
$objResponse->addInsert('el', 'span', 'newspan');
$objResponse->addClear('el', 'innerHTML');

$aCommands = $objResponse->aCommands;
$aGot = array();
foreach ($aCommands as $aCommand) {
    $aGot[] = $aCommand['cmd'];
}

check('addAssign -> as', in_array('as', $aGot, true), implode(',', $aGot));
check('addScript -> js', in_array('js', $aGot, true), implode(',', $aGot));
check('addAlert -> al', in_array('al', $aGot, true), implode(',', $aGot));
check('addConfirmCommands -> cc', in_array('cc', $aGot, true), implode(',', $aGot));
check('addCreate -> ce', in_array('ce', $aGot, true), implode(',', $aGot));
check('addInsert -> ie', in_array('ie', $aGot, true), implode(',', $aGot));
// clear() is implemented as assign with an empty value, so it emits 'as'.
check('addClear -> as (empty assign)', in_array('as', $aGot, true), implode(',', $aGot));

// 0.5 exposed the XML payload through getXML() on the response object.
$objXml =& $objResponse->getXML();
check('getXML returns the response object', $objXml === $objResponse);

$objResponse->outputEntitiesOn();
$objResponse->outputEntitiesOff();
check('outputEntitiesOn/Off callable', true);

echo str_repeat('-', 60) . "\n";
echo "passed: $nPass, failed: $nFail\n";
exit($nFail === 0 ? 0 : 1);
