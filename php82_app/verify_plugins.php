<?php
/*
    Loads each bundled response plugin under PHP 8.2 to confirm the constructor
    renames took effect and the files do not fatal.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$aIssues = array();
set_error_handler(function ($nNo, $sMsg, $sFile, $nLine) use (&$aIssues) {
    $aIssues[] = "[$nNo] $sMsg ($sFile:$nLine)";
    return true;
});

require dirname(__DIR__) . '/xajax_core/xajax.inc.php';
$objBootstrap = new xajax('index.php');
unset($objBootstrap);

$aPlugins = array(
    'tableUpdater' => dirname(__DIR__) . '/xajax_plugins/response/tableUpdater.inc.php',
    'googleMap'    => dirname(__DIR__) . '/xajax_plugins/response/googleMap.inc.php',
    'swfupload'    => dirname(__DIR__) . '/xajax_plugins/response/swfupload/swfupload.inc.php',
    'comet'        => dirname(__DIR__) . '/xajax_plugins/response/comet/comet.inc.php',
);

foreach ($aPlugins as $sName => $sPath) {
    $nBefore = count($aIssues);
    try {
        require_once $sPath;
        $sStatus = (count($aIssues) === $nBefore) ? 'ok' : 'with notices';
    } catch (\Throwable $e) {
        $sStatus = 'FATAL: ' . $e->getMessage();
    }
    echo str_pad($sName, 14) . ": $sStatus\n";
}

// The classes should now exist and construct.
foreach (array('clsTableUpdater', 'clsGoogleMap', 'clsSwfUpload', 'clsCometStreaming') as $sClass) {
    $bExists = class_exists($sClass);
    $bConstruct = false;
    if ($bExists) {
        try {
            $obj = new $sClass();
            $bConstruct = true;
        } catch (\Throwable $e) {
            $bConstruct = 'FATAL: ' . $e->getMessage();
        }
    }
    echo str_pad($sClass, 20) . ': exists=' . var_export($bExists, true) . ' construct=' . var_export($bConstruct, true) . "\n";
}

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
} else {
    echo 'Issues (' . count($aIssues) . "):\n";
    foreach ($aIssues as $sIssue) {
        echo "  $sIssue\n";
    }
}
