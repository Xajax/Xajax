<?php
require_once("../xajax_core/xajax.inc.php");

function testForm($formData)
{
	$objResponse = new xajaxResponse();
	$objResponse->alert("formData: " . print_r($formData, true));
	$objResponse->assign("submittedDiv", "innerHTML", nl2br(print_r($formData, true)));
	return $objResponse;
}

$xajax = new xajax();
//$xajax->configure("debug", true);
$xajax->register(XAJAX_FUNCTION, "testForm");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Disabled Form Elements Test | xajax Tests</title>
<?php $xajax->printJavascript("../") ?>
</head>
<body>

<h2><a href="index.php">xajax Tests</a></h2>
<h1>Disabled Form Elements Test</h1>

<form id="testForm1" onsubmit="return false;">
<p><input type="text" id="textBox1" name="textBox1" value="This is enabled" /></p>
<p><input type="text" id="textBox2" name="textBox2" disabled="true" value="This is disabled" /></p>
<p><input type="submit" value="Submit Normal" onclick="xajax_testForm(xajax.getFormValues('testForm1')); return false;" /></p>
<p><input type="submit" value="Submit Everything" onclick="xajax_testForm(xajax.getFormValues('testForm1', true)); return false;" /></p>
</form>

<div id="submittedDiv"></div>

</body>
</html>