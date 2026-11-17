<?php
/*
    Exercises the xajaxControl / xajaxControlContainer getClass() change
    (xajaxControl::getClass() -> parent::getClass()), which the demo app does
    not cover. Subclasses are needed because both constructors are protected.
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

require dirname(__DIR__) . '/xajax_core/xajaxControl.inc.php';

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

// A concrete, empty container: no children, class stays %block.
class emptyContainer extends xajaxControlContainer
{
    public function __construct()
    {
        parent::__construct('div');
    }
}

// A container holding a child element; a %flow container becomes its content class.
class flowContainer extends xajaxControlContainer
{
    public function __construct($child)
    {
        parent::__construct('span', array('child' => $child));
    }
}

class leaf extends xajaxControl
{
    public function __construct($sClass)
    {
        parent::__construct('em');
        $this->sClass = $sClass;
    }
}

$objEmpty = new emptyContainer();
check('empty container getClass() is %block', $objEmpty->getClass() === '%block', $objEmpty->getClass());

$objLeaf = new leaf('%flow');
check('leaf getClass() returns its class', $objLeaf->getClass() === '%flow', $objLeaf->getClass());

$objFlow = new flowContainer($objLeaf);
// The child is %flow, so the container's adjusted class is the content class.
$sAdjusted = $objFlow->getClass();
check('flow container getClass() delegates to content class', $sAdjusted !== '%flow' && $sAdjusted !== '', $sAdjusted);

// getHTML() must render without error for the container.
$sHtml = $objFlow->getHTML();
check('container getHTML() renders', strpos($sHtml, '<span') === 0 && strpos($sHtml, '</span>') !== false, substr($sHtml, 0, 80));

echo str_repeat('-', 60) . "\n";
if (empty($aIssues)) {
    echo "No deprecations, notices or warnings.\n";
} else {
    echo 'Issues (' . count($aIssues) . "):\n";
    foreach ($aIssues as $sIssue) {
        echo "  $sIssue\n";
    }
}
