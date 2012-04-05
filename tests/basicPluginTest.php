<?php

$core = dirname(dirname(__FILE__)) . '/xajax_core';
require_once $core . '/xajax.inc.php';

$xajax = new xajax();

//$xajax->configure('debug', true);
$xajax->configure('javascript URI', '../');

require_once $core . '/xajaxPlugin.inc.php';
require_once $core . '/xajaxPluginManager.inc.php';

class testPlugin extends xajaxResponsePlugin
{
	var $sDefer;
	
	function testPlugin()
	{
		$this->sDefer = '';
	}
	
	function getName()
	{
		return 'testPlugin';
	}
	
	function generateClientScript()
	{
		echo "\n<script type='text/javascript' " . $this->sDefer . "charset='UTF-8'>\n";
		echo "/* <![CDATA[ */\n";

		echo "xajax.command.handler.register('testPlg', function(args) { \n";
		echo "\talert('Test plugin command received: ' + args.data);\n";
		echo "});\n";

		echo "/* ]]> */\n";
		echo "</script>\n";
	}
	
	function testMethod()
	{
		$this->addCommand(array('cmd'=>'testPlg'), 'abcde]]>fg');	
	}
}

$objPluginManager = xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new testPlugin());

function showOutput()
{
	$testResponse = new xajaxResponse();
	$testResponse->alert("Edit this test and uncomment lines in the showOutput() method to test plugin calling");
	// PHP4 & PHP5
	$testResponse->plugin("testPlugin", "testMethod");
	
	// PHP5 ONLY - Uncomment to test
	//$testResponse->plugin("testPlugin")->testMethod();
	
	// PHP5 ONLY - Uncomment to test
	//$testResponse->testPlugin->testMethod();
	
	$testResponseOutput = htmlspecialchars($testResponse->getOutput());
	
	$objResponse = new xajaxResponse();
	$objResponse->assign("submittedDiv", "innerHTML", $testResponseOutput);
	$objResponse->plugin('testPlugin', 'testMethod');
	return $objResponse;
}

$reqShowOutput = $xajax->register(XAJAX_FUNCTION, "showOutput");

$xajax->configure('responseType','XML');
$xajax->processRequest();
$xajax->configure('javascript URI','../');



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Basic Plugin Test | xajax Tests</title>
<?php $xajax->printJavascript() ?>
</head>
<body>

<h2><a href="index.php">xajax Tests</a></h2>
<h1>Basic Plugin Test</h1>

<form id="testForm1" onsubmit="return false;">
<p><input type="button" id="btnShowOutput" value="Show Response" onclick="xajax_showOutput();" /></p>
</form>

<div id="submittedDiv"></div>

</body>
</html>