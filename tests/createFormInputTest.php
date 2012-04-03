<?php
require_once( "../xajax_core/xajax.inc.php" );

// tests the select form
function testForm( $formData )
{
    $objResponse=new xajaxResponse();
    $objResponse->alert( "formData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    return $objResponse;
}

// adds an option to the select
function addInput( $aInputData )
{
    $sId=$aInputData['inputId'];
    $sName=$aInputData['inputName'];
    $sType=$aInputData['inputType'];
    $sValue=$aInputData['inputValue'];

    $objResponse=new xajaxResponse();

    $sParentId="testForm1";

    if ( isset( $aInputData['inputWrapper'] ) )
    {
        $sDivId=$sId . '_div';
        $objResponse->append( $sParentId, "innerHTML", '<div id="' . $sDivId . '"></div>' );
        $sParentId=$sDivId;
    }

    $objResponse->alert( "inputData: " . print_r( $aInputData, true ) );
    $objResponse->createInput( $sParentId, $sType, $sName, $sId );
    $objResponse->assign( $sId, "value", $sValue );
    return $objResponse;
}

// adds an option to the select
function insertInput( $aInputData )
{
    $sId=$aInputData['inputId'];
    $sName=$aInputData['inputName'];
    $sType=$aInputData['inputType'];
    $sValue=$aInputData['inputValue'];
    $sBefore=$aInputData['inputBefore'];

    $objResponse=new xajaxResponse();
    $objResponse->alert( "inputData: " . print_r( $aInputData, true ) );
    $objResponse->insertInput( $sBefore, $sType, $sName, $sId );
    $objResponse->assign( $sId, "value", $sValue );
    return $objResponse;
}

function removeInput( $aInputData )
{
    $sId=$aInputData['inputId'];

    $objResponse=new xajaxResponse();

    $objResponse->remove( $sId );

    return $objResponse;
}

$xajax=new xajax();
//$xajax->configure("debug", true);
$xajax->register(XAJAX_FUNCTION, "testForm");
$xajax->register(XAJAX_FUNCTION, "addInput");
$xajax->register(XAJAX_FUNCTION, "insertInput");
$xajax->register(XAJAX_FUNCTION, "removeInput");

$xajax->processRequest();
$xajax->configure('javascript URI','../');
$xajax->configure('responseType','XML');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Create Form Input Test| xajax Tests</title>

        <?php $xajax->printJavascript( "../" ) ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Create Form Input Test</h1>

        <div>
            <form id = "testForm1" onsubmit = "return false;">
                <div>
                    <input type = "submit"
                        value = "submit" onclick = "xajax_testForm(xajax.getFormValues('testForm1')); return false;" />
                </div>
            </form>
        </div>

        <div style = "margin-top: 20px;">
            <form id = "testForm2" onsubmit = "return false;">
                <div>
                    type:
                </div>

                <select id = "inputType" name = "inputType">
                    <option value = "text" selected = "selected">text</option>

                    <option value = "password">password</option>

                    <option value = "hidden">hidden</option>

                    <option value = "radio">radio</option>

                    <option value = "checkbox">checkbox</option>
                </select>

                <div>
                    Id:
                </div>

                <input type = "text" id = "inputId" name = "inputId" value = "input1" />

                <div>
                    Name:
                </div>

                <input type = "text" id = "inputName" name = "inputName" value = "input1" />

                <div>
                    Value:
                </div>

                <input type = "text" id = "inputValue" name = "inputValue" value = "1" />

                <div>
                    Place inside DIV
                </div>

                <input type = "checkbox" id = "inputWrapper" name = "inputWrapper" value = "1" />

                <div>
                    <input type = "submit"
                        value = "Add" onclick = "xajax_addInput(xajax.getFormValues('testForm2')); return false;" />

                    <input type = "submit" value = "Remove"
                        onclick = "xajax_removeInput(xajax.getFormValues('testForm2')); return false;" />

                    <input type = "submit" value = "Insert Before:"
                        onclick = "xajax_insertInput(xajax.getFormValues('testForm2')); return false;" />

                    <input type = "text" id = "inputBefore" name = "inputBefore" value = "" />
                </div>
            </form>
        </div>

        <div id = "submittedDiv" style = "margin: 3px;">
        </div>
    </body>
</html>

