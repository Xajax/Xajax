<?php
require_once( "../xajax_core/xajax.inc.php" );

function test2ndFunction($formData, $objResponse)
{
    $objResponse->alert( "formData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    return $objResponse;
}

function onInvalidRequest( )
{
    $objArgumentManager = xajaxArgumentManager::getInstance();
    $aArgs = $objArgumentManager->process();

    $objResponse = new xajaxResponse();
    $objResponse->alert( "This is from the invalid request handler" );
    return test2ndFunction( $aArgs[0], $objResponse );
}

function testForm( $formData )
{
    $objResponse=new xajaxResponse();
    $objResponse->alert( "This is from the regular function" );
    return test2ndFunction( $formData, $objResponse );
}

$xajax=new xajax();
$xajax->configure( 'javascript URI', '../' );
$xajax->configure( "errorHandler", true );
$xajax->register( XAJAX_PROCESSING_EVENT, XAJAX_PROCESSING_EVENT_INVALID, "onInvalidRequest" );

if ( isset( $_GET['registerFunction'] ) )
    if ( 1 == $_GET['registerFunction'] )
        $xajax->register(XAJAX_FUNCTION, "testForm");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>onMissingFunction Event (used to be catch-all) Function Test | xajax Tests</title>

        <?php $xajax->printJavascript() ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>invalidRequest Event Handler</h1>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <input type = "text" id = "textBox1" name = "textBox1" value = "This is some text" />
            </p>

            <p>
                <input type = "submit"
                    value = "Submit Normal"
                    onclick = "xajax.request({xjxfun:'testForm'}, { parameters: [xajax.getFormValues('testForm1')] }); return false;" />
            </p>
        </form>

        <?php
        if ( isset( $_GET['registerFunction'] ) && 1 == $_GET['registerFunction'] )
        {
        ?>

            <a href = './catchAllFunctionTest.php'>Disable Normal Handler</a>

        <?php
        }
        else
        {
        ?>

            <a href = './catchAllFunctionTest.php?registerFunction=1'>Enable Normal Handler</a>

        <?php
        }
        ?>

            <div id = "submittedDiv">
            </div>
    </body>
</html>

