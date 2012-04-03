<?php
/*
    File: errorHandlingTest.php

    Unit test script for validating proper functioning of the error
    handler mechanism on the xajax server side.
    
    Title: Error Handling Test
    
    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: errorHandlingTest.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
    Section: Standard xajax startup
    
    - include <xajax.inc.php>
    - instantiate the <xajax> object
*/

require_once( "../xajax_core/xajax.inc.php" );

$xajax=new xajax();

$xajax->configure( 'javascript URI', '../' );

/*
    - enable deubgging if desired
*/
$xajax->configure( 'debug', true );

/*
    Section: Enable Error Handler
    
    - set <xajax->bErrorHandler> using <xajax->configure> or <xajax->configure>
    - set the log file using <xajax->setLogFile>
*/
$xajax->configure( 'errorHandler', true );
$xajax->configure( 'logFile', 'xajax_error_log.log' );

/*
    Section: Define error ridden function
    
    - syntax is correct, but logic errors will be generated at runtime.
*/
function myErrorRiddenFunction( )
{
    $value=$silly['nuts'];
    $objResponse=new xajaxResponse();
    $objResponse->alert( "Bad array value: $value" );
    include( "file_doesnt_exist.php" );
    return $objResponse;
}

/*
    - register the error ridden function
*/
$xajax->register( XAJAX_FUNCTION, "myErrorRiddenFunction" );

/*
    Section: Process the request or generate the initial page
    
    - standard call to <xajax->processRequest>
*/
$xajax->processRequest();
$xajax->configure('javascript URI','../');


/*
    - if this is xajax request, it is handled and the script is exited
    - else, generate the html for the page
*/
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml" xml:lang = "en" lang = "en">
    <head>
        <title>Error Handling Test | xajax Tests</title>

        <?php
        /*
            - output javascript configuration and reference to xajax 
                javascript library
        */
        $xajax->printJavascript()
        ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Error Handling Test</h1>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <!--
                    Section: Initiate the request
                    
                    - use <xajax.request> to send request back to server
                -->
                <input type = "submit"
                    value = "Call Error Ridden Function" onclick = "xajax_myErrorRiddenFunction(); return false;" />
            </p>
        </form>

        <div id = "submittedDiv">
        </div>
    </body>
</html>

