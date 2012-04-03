<?php
require_once("../xajax_core/xajax.inc.php");

function addHandler($sId,$sHandler)
{
	$objResponse = new xajaxResponse();
	$objResponse->addHandler($sId, "click", $sHandler);
	$objResponse->append('log', 'innerHTML', "{$sHandler} enabled.<br />");
	return $objResponse;
}

function removeHandler($sId,$sHandler)
{
	$objResponse = new xajaxResponse();
	$objResponse->removeHandler($sId, "click", $sHandler);
	$objResponse->append('log', 'innerHTML', "{$sHandler} disabled.<br />");
	return $objResponse;
}

$xajax = new xajax();
//$xajax->configure("debug", true);
$xajax->register(XAJAX_FUNCTION, "addHandler");
$xajax->register(XAJAX_FUNCTION, "removeHandler");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Event Handler Test | xajax Tests</title>
<?php $xajax->printJavascript("../") ?>
<script type="text/javascript">
function clickHandler1()
{
	xajax.$('log').innerHTML += 'Click handler 1 called.<br />';
}
function clickHandler2()
{
	xajax.$('log').innerHTML += 'Click handler 2 called.<br />';
}
</script>
</head>
<body>

<h2><a href="index.php">xajax Tests</a></h2>
<h1>Event Handler Test</h1>


<div id="myDiv" style="padding: 3px; display: table; border: 1px outset black; font-size: large; margin-bottom: 10px;">Click Me</div>

<form id="testForm1" onsubmit="return false;">
<div>
Click handler 1: 
<input type='radio' name='handler1[]' value='add' onclick="xajax_addHandler('myDiv','clickHandler1'); return true;" />Enabled
<input type='radio' name='handler1[]' value='remove' onclick="xajax_removeHandler('myDiv','clickHandler1'); return true;" checked='checked' />Disabled
</div>
<div>
Click handler 2:
<input type='radio' name='handler2[]' value='add' onclick="xajax_addHandler('myDiv','clickHandler2'); return true;" />Enabled
<input type='radio' name='handler2[]' value='remove' onclick="xajax_removeHandler('myDiv','clickHandler2'); return true;" checked='checked' />Disabled
</div>

<div style='border: 1px solid #cccccc'>
<center>
Log (<a href='#' onclick='xajax.$("log").innerHTML = ""; return false;'>clear</a>)
</center>
<div id='log'>
</div>
</div>

</form>

<div id="submittedDiv"></div>

</body>
</html>