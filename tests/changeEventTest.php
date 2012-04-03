<?php
require_once("../xajax_core/xajax.inc.php");

function addEvent($sId,$sCode)
{
	$objResponse = new xajaxResponse();
	$objResponse->addEvent($sId, "onclick", $sCode);
	return $objResponse;
}

$xajax = new xajax();
//$xajax->configure("debug", true);
$xajax->register(XAJAX_FUNCTION, "addEvent");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Change Event Test | xajax Tests</title>
<?php $xajax->printJavascript("../") ?>
</head>
<body>

<h2><a href="index.php">xajax Tests</a></h2>
<h1>Change Event Test </h1>

<div id="myDiv"" style="padding: 3px; display: table; border: 1px outset black; font-size: large; margin-bottom: 10px;">Click Me</div>

<form id="testForm1" onsubmit="return false;">
<div><input type="submit" value="Set onclick to something" onclick="xajax_addEvent('myDiv','alert(\'Something\');'); return false;" /></div>
<div><input type="submit" value="Set onclick to something else" onclick="xajax_addEvent('myDiv','alert(\'Something Else\');'); return false;" /></div>
</form>

<div id="submittedDiv"></div>

</body>
</html>