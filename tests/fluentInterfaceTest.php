<?php
require_once( "../xajax_core/xajax.inc.php" );

$xajax=new xajax();

function testFluentInterface( )
{
    global $xajax;
    return $xajax->getGlobalResponse()->alert( "Here is an alert." )->assign( "submittedDiv", "innerHTML",
        "Here is some <b>HTML text</b>." );
}

$xajax->register(XAJAX_FUNCTION, "testFluentInterface");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Fluent Interface Test | xajax Tests</title>

        <?php $xajax->printJavascript( "../" ) ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Fluent Interface Test (PHP 5+ only)</h1>

        <p>
            <a href = "#" onclick = "xajax_testFluentInterface();return false;">Perform Test</a>

            <div id = "submittedDiv">
            </div>
    </body>
</html>

