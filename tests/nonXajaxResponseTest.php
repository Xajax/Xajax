<?php
require_once( "../xajax_core/xajax.inc.php" );

function testXajaxResponse( )
{
    // Return a xajax response object
    $objResponse=new xajaxResponse();
    $objResponse->assign( 'DataDiv', 'innerHTML', 'Xajax Response Data' );
    return $objResponse;
}

function testXmlResponse( )
{
    $objResponse=new xajaxCustomResponse( 'text/xml' );
    $objResponse->setCharacterEncoding( 'UTF-8' );

    $objResponse->append( '<?xml version="1.0" encoding="utf-8" ?' . '><root><data>text</data></root>' );

    return $objResponse;
}

function testTextResponse( )
{
    $objResponse=new xajaxCustomResponse( 'text/plain' );
    $objResponse->append( 'text data' );
    return $objResponse;

    // return text data directly to the custom response handler function
    return 'text data';
}

$xajax=new xajax();
$xajax->configure( 'debug', true );
$xajax->configure( 'useUncompressedScripts', true );
$xajax->configure( 'javascript URI', '../' );

// Tell xajax to permit registered functions to return data other than xajaxResponse objects
$xajax->configure( 'allowAllResponseTypes', true );

$callXajaxResponse = $xajax->register(
	XAJAX_FUNCTION,
	'testXajaxResponse'
	);

$callXmlResponse = $xajax->register(
	XAJAX_FUNCTION,
	'testXmlResponse',
	array('responseProcessor' => 'xmlResponse')
	);

$callTextResponse = $xajax->register(
	XAJAX_FUNCTION,
	'testTextResponse',
	array(
		'mode' => '"synchronous"',
		'responseProcessor' => 'textResponse'
		)
	);

$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Non-xajaxResponse XML and Text Responses Test | xajax Tests</title>

        <?php $xajax->printJavascript() ?>

        <script>
            // function to handle ajax response text data
            function textResponse(objRequest)
                {
                xajax.$('DataDiv').innerHTML = objRequest.request.responseText;
                xajax.completeResponse(objRequest);
                }

            // function to handle ajax response data XML
            function xmlResponse(objRequest)
                {
                alert(objRequest.request.responseXML.documentElement.nodeName);
                xajax.$('DataDiv').innerHTML = 'non xajax: XML response';
                xajax.completeResponse(objRequest);
                }
        </script>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Non-xajaxResponse XML and Text Responses Test</h1>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <input type = "button"
                    value = "xajax" onclick = "<?php $callXajaxResponse->printScript(); ?>; return false;" />
                <!-- use xajax.call to call the functions that return data directly and 
                indicate the javascript function that will handle the response -->
                <input type = 'button' value = 'xml' onclick = '<?php $callXmlResponse->printScript(); ?>' />

                <input type = 'button' value = 'text' onclick = '<?php $callTextResponse->printScript(); ?>' />
            </p>
        </form>

        <div id = "DataDiv">
        </div>
    </body>
</html>

