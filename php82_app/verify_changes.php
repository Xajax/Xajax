<?php
/*
    Targeted verification of the PHP 8.2 compatibility changes made to the
    library. Exercises the exact code paths that were modified, beyond what the
    demo app covers.

    Usage:
        php php82_app/verify_changes.php
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/xajax_core/xajax.inc.php';

// The argument manager reads xjxargs once, when its singleton is first created
// (during the xajax constructor below), so seed the request data first.
$_POST['xjxargs'] = array('Shello', 'N42', 'Btrue', '*', '{"a":1,"b":[2,3]}');

// Instantiating xajax pulls in the core classes (they are required from the
// constructor), so the response/argument/plugin classes are available after.
$objBootstrap = new xajax('index.php');
$objBootstrap->configure('javascript URI', '../');
$objBootstrap->configure('deferScriptGeneration', false);
unset($objBootstrap);

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
        echo "FAIL  $sLabel" . ($sDetail !== '' ? "  -- $sDetail" : '') . "\n";
    }
}

echo "Targeted PHP 8.2 change verification\n";
echo str_repeat('-', 60) . "\n";

/* ------------------------------------------------------------------
   1. xajaxResponse::_printEscapedString_XML via mb_encode_numericentity
      (replaced removed mb_convert_encoding 'HTML-ENTITIES').
      Probe the private method through the public XML render path.
   ------------------------------------------------------------------ */

function renderXml($mValue, $sEncoding = 'UTF-8', $bEntities = true)
{
    $objResponse = new xajaxResponse();
    $objResponse->setResponseType('XML');
    $objResponse->setCharacterEncoding($sEncoding);
    $objResponse->setOutputEntities($bEntities);
    $objResponse->assign('out', 'innerHTML', $mValue);
    return $objResponse->getOutput();
}

// é = U+00E9 (2-byte UTF-8)
$sXml = renderXml("caf\xC3\xA9");
check('XML entity encoding: 2-byte char -> &#233;', strpos($sXml, '&#233;') !== false, substr($sXml, 0, 120));

// € = U+20AC (3-byte UTF-8)
$sXml = renderXml("price \xE2\x82\xAC");
check('XML entity encoding: 3-byte char (euro) -> &#8364;', strpos($sXml, '&#8364;') !== false, substr($sXml, 0, 120));

// 😀 = U+1F600 (4-byte UTF-8) -- above the BMP, tests the 0x10FFFF range
$sXml = renderXml("smile \xF0\x9F\x98\x80");
check('XML entity encoding: 4-byte char (emoji) -> &#128512;', strpos($sXml, '&#128512;') !== false, substr($sXml, 0, 160));

// ASCII must pass through untouched
$sXml = renderXml('plain ascii');
check('XML entity encoding: ASCII unchanged', strpos($sXml, 'plain ascii') !== false && strpos($sXml, '&#') === false, substr($sXml, 0, 120));

/* ------------------------------------------------------------------
   2. xajaxResponse with ISO-8859-1 character encoding.
   ------------------------------------------------------------------ */
$sXml = renderXml("caf\xE9", 'ISO-8859-1');
check('XML entity encoding: ISO-8859-1 input', strpos($sXml, '&#233;') !== false, substr($sXml, 0, 120));

/* ------------------------------------------------------------------
   3. Response type property: default JSON, switch to XML and back.
   ------------------------------------------------------------------ */
$objResponse = new xajaxResponse();
check('response type defaults to JSON', $objResponse->getResponseType() === 'JSON', $objResponse->getResponseType());
$objResponse->setResponseType('XML');
check('response type switches to XML', $objResponse->getResponseType() === 'XML');
$objResponse->setResponseType('JSON');
check('response type switches back to JSON', $objResponse->getResponseType() === 'JSON');

// setReturnValue must pick encoding based on the response type.
$objResponse = new xajaxResponse();
$objResponse->setResponseType('JSON');
$objResponse->setReturnValue(array('a' => 1));
$r = new ReflectionProperty('xajaxResponse', 'returnValue');
$r->setAccessible(true);
check('setReturnValue JSON stores raw value', is_array($r->getValue($objResponse)), gettype($r->getValue($objResponse)));

/* ------------------------------------------------------------------
   4. xajaxArgumentManager: JSON decoding and callable array syntax
      (array($this, '__argumentDecode') replaced array(&$this, ...)).
   ------------------------------------------------------------------ */
$objArgMgr = xajaxArgumentManager::getInstance();
$r = new ReflectionProperty('xajaxArgumentManager', 'aArgs');
$r->setAccessible(true);
$aArgs = $r->getValue($objArgMgr);
check('argument manager: string arg decoded', $aArgs[0] === 'hello', var_export($aArgs[0], true));
check('argument manager: int arg decoded', $aArgs[1] === 42, var_export($aArgs[1], true));
check('argument manager: bool arg decoded', $aArgs[2] === true, var_export($aArgs[2], true));
check('argument manager: null arg decoded', $aArgs[3] === null, var_export($aArgs[3], true));
check('argument manager: JSON object arg decoded', is_array($aArgs[4]) && $aArgs[4]['a'] === 1 && $aArgs[4]['b'][1] === 3, var_export($aArgs[4], true));

/* ------------------------------------------------------------------
   5. xajaxPluginManager::generateHash guard for plugins without
      generateHash(), and the deferred script output directory creation.
   ------------------------------------------------------------------ */
$objPluginManager = xajaxPluginManager::getInstance();
$r = new ReflectionProperty('xajaxPluginManager', 'aClientScriptGenerators');
$r->setAccessible(true);

// A plugin that does NOT implement generateHash().
class pluginWithoutHash
{
}
$saved = $r->getValue($objPluginManager);
$r->setValue($objPluginManager, array('nohash' => new pluginWithoutHash(), 'withhash' => new xajaxFunctionPlugin()));
$mHash = new ReflectionMethod('xajaxPluginManager', 'generateHash');
$mHash->setAccessible(true);
$sHash = $mHash->invoke($objPluginManager);
check('plugin manager: generateHash tolerates plugin without generateHash()', is_string($sHash) && strlen($sHash) === 32, $sHash);
$r->setValue($objPluginManager, $saved);

/* ------------------------------------------------------------------
   6. Constructor renames: instantiate the renamed classes directly.
   ------------------------------------------------------------------ */
$objEventPlugin = new xajaxEventPlugin();
check('xajaxEventPlugin constructs', $objEventPlugin instanceof xajaxEventPlugin);

$objScriptPlugin = new xajaxScriptPlugin();
check('xajaxScriptPlugin constructs', $objScriptPlugin instanceof xajaxScriptPlugin);

$objFunctionPlugin = new xajaxFunctionPlugin();
check('xajaxFunctionPlugin constructs', $objFunctionPlugin instanceof xajaxFunctionPlugin);

$objIncludePlugin = new xajaxIncludeClientScriptPlugin();
check('xajaxIncludeClientScriptPlugin constructs', $objIncludePlugin instanceof xajaxIncludeClientScriptPlugin);

$uf = new xajaxUserFunction('strlen');
check('xajaxUserFunction constructs via __construct', $uf instanceof xajaxUserFunction);

$cr = new xajaxCustomResponse('text/plain');
check('xajaxCustomResponse constructs via __construct', $cr instanceof xajaxCustomResponse);

$rsm = xajaxResponseManager::getInstance();
check('xajaxResponseManager constructs', $rsm instanceof xajaxResponseManager);
$r = new ReflectionProperty('xajaxResponseManager', 'aSettings');
$r->setAccessible(true);
check('xajaxResponseManager declares aSettings', is_array($r->getValue($rsm)));

/* ------------------------------------------------------------------
   7. Static factory must return by reference-compatible usage (no & needed).
   ------------------------------------------------------------------ */
$o1 = xajaxResponseManager::getInstance();
$o2 = xajaxResponseManager::getInstance();
check('getInstance returns singleton', $o1 === $o2);

echo str_repeat('-', 60) . "\n";
echo "passed: $nPass, failed: $nFail\n";
exit($nFail === 0 ? 0 : 1);
