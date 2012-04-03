<?php
/*
    File: xajaxResponseTest.php

    Script to test various aspects of the <xajaxResponse> object.
    
    Title: Test the <xajaxResponse> object.
    
    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: xajaxResponseTest.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/
require_once( "../xajax_core/xajax.inc.php" );

function showOutput( )
{
    $testResponse=new xajaxResponse();
    $testResponse->alert( "Hello" );

    $testResponse2=new xajaxResponse();
    $testResponse2->appendResponse( $testResponse );
    $testResponse2->replace( "this", "is", "a", "replacement]]>" );
    $testResponseOutput=htmlspecialchars( $testResponse2->getOutput() );

    $objResponse=new xajaxResponse();
    $objResponse->assign( "submittedDiv", "innerHTML", $testResponseOutput );
    $aValues=array();
    $aValues[]="Yippie";
    $objResponse->setReturnValue( $aValues );
    return $objResponse;
}
$xajax=new xajax();
$xajax->configure("debug", true);
$xajax->configure("javascript URI", '../');
$xajax->register(XAJAX_FUNCTION, "showOutput");
//$xajax->configure("responseType", 'XML');

$xajax->processRequest();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml" xml:lang = "en" lang = "en">
    <head>
        <title>xajaxResponse Test | xajax Tests</title>

        <?php $xajax->printJavascript( "../" ) ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>xajaxResponse Test</h1>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <input type = "submit" value = "Show Response"
                    onclick = "alert(xajax.request({xjxfun:'showOutput'}, {mode:'synchronous'})); return false;" />
            </p>
        </form>

        <div id = "submittedDiv">
        </div>
    </body>
</html>

