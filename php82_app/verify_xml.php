<?php
/*
    Exercises the XML response type end to end, including the entity-encoding
    change (mb_encode_numericentity) and the new $sResponseType property.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$aIssues = array();
set_error_handler(function ($nNo, $sMsg) use (&$aIssues) {
    $aIssues[] = "[$nNo] $sMsg";
    return true;
});

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';

$objBootstrap = new xajax('index.php');
unset($objBootstrap);

$objResponse = new xajaxResponse();
$objResponse->setResponseType('XML');
$objResponse->setCharacterEncoding('UTF-8');
$objResponse->setOutputEntities(true);

$objResponse->assign('out', 'innerHTML', "caf\xC3\xA9 \xE2\x82\xAC \xF0\x9F\x98\x80");
$objResponse->append('out', 'innerHTML', 'tail');
$objResponse->call('fn', array('k' => "caf\xC3\xA9"));
$objResponse->setReturnValue(array('a' => 1, 'b' => "caf\xC3\xA9"));

$sXml = $objResponse->getOutput();

echo substr($sXml, 0, 400) . "\n\n";

$bOk = true;
$bOk = $bOk && strpos($sXml, '<xjx>') !== false;
$bOk = $bOk && strpos($sXml, '&#233;') !== false;      // é
$bOk = $bOk && strpos($sXml, '&#8364;') !== false;     // €
$bOk = $bOk && strpos($sXml, '&#128512;') !== false;   // 😀
$bOk = $bOk && strpos($sXml, '<xjxobj>') !== false;

// The XML must be well formed.
$objXml = @simplexml_load_string($sXml);
$bOk = $bOk && $objXml !== false;

echo ($bOk ? 'PASS' : 'FAIL') . " XML response type + numeric entities\n";
if ($objXml === false) {
    echo "XML parse error: " . libxml_get_last_error()->message . "\n";
}

// CDATA path: bOutputEntities false keeps markup as CDATA.
$objResponse = new xajaxResponse();
$objResponse->setResponseType('XML');
$objResponse->setCharacterEncoding('UTF-8');
$objResponse->setOutputEntities(false);
$objResponse->assign('out', 'innerHTML', '<b>bold</b> caf' . "\xC3\xA9");
$sCdata = $objResponse->getOutput();
$bCdata = strpos($sCdata, '![CDATA[') !== false;
$objCdataXml = @simplexml_load_string($sCdata);
echo ($bCdata && $objCdataXml !== false ? 'PASS' : 'FAIL') . " XML CDATA path\n";

// Special characters: xajax embeds markup as raw element text by design (both
// before and after the PHP 8.2 change), so '<', '>', '&' pass through as-is.
// Only non-ASCII is entity-encoded. Assert that documented behaviour.
$objResponse = new xajaxResponse();
$objResponse->setResponseType('XML');
$objResponse->setCharacterEncoding('UTF-8');
$objResponse->setOutputEntities(true);
$objResponse->assign('out', 'innerHTML', 'a<b>&"c caf' . "\xC3\xA9");
$sSpecial = $objResponse->getOutput();
$bPassthrough = strpos($sSpecial, 'a<b>&"c') !== false && strpos($sSpecial, '&#233;') !== false;
echo ($bPassthrough ? 'PASS' : 'FAIL') . " XML: markup passes through, non-ASCII encoded\n";
echo '  output: ' . $sSpecial . "\n";

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
} else {
    echo 'Issues (' . count($aIssues) . "):\n";
    foreach ($aIssues as $sIssue) {
        echo "  $sIssue\n";
    }
}
