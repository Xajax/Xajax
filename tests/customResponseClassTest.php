<?php
$xajax_core=dirname( dirname( __FILE__ ) ) . '/xajax_core';

require_once( $xajax_core . '/xajax.inc.php' );

$xajax=new xajax();

$xajax->configure( 'javascript URI', '../' );

//$xajax->configure('debug', true);

// Custom Response Class extends xajaxResponse
class customXajaxResponse
    extends xajaxResponse
{
    function createOption( $sSelectId, $sOptionId, $sOptionText, $sOptionValue )
    {
        $this->script( "addOption('" . $sSelectId . "','" . $sOptionId . "','" . $sOptionText . "','" . $sOptionValue
            . "');" );
    }
}

// tests the select form
function testForm( $formData )
{
    $objResponse=new customXajaxResponse();
    $objResponse->alert( "formData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    return $objResponse;
}

// adds an option to the select
function addOption( $selectId, $optionData )
{
    $objResponse=new customXajaxResponse();
    $objResponse->createOption( $selectId, $optionData['optionId'], $optionData['optionText'],
        $optionData['optionValue'] );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $optionData, true ) ) );
    return $objResponse;
}

$xajax->register(XAJAX_FUNCTION, "testForm");
$xajax->register(XAJAX_FUNCTION, "addOption");

$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Custom Response Class Test| xajax Tests</title>

        <script type = "text/javascript">
            //javascript function to add an option to a select box
            function addOption(selectId, optionId, txt, val)
                {
                var objOption = new Option(txt, val);
                objOption.id = optionId;
                document.getElementById(selectId).options.add(objOption);
                }
        </script>

        <?php $xajax->printJavascript() ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Custom Response Class Test</h1>

        <div>
            <form id = "testForm1" onsubmit = "return false;">
                <div>
                    Select
                </div>

                <select id = "selectBox" name = "selectBox">
                </select>

                <div>
                    <input type = "submit"
                        value = "submit" onclick = "xajax_testForm(xajax.getFormValues('testForm1')); return false;" />
                </div>
            </form>
        </div>

        <div style = "margin-top: 20px;">
            <form id = "testForm2" onsubmit = "return false;">
                <fieldset style = "display:inline; background-color: rgb(230,230,230);">
                    <legend>
                        New Option
                    </legend>

                    <div>
                        Id:
                    </div>

                    <input type = "text" id = "optionId" name = "optionId" value = "option1" />

                    <div>
                        Text:
                    </div>

                    <input type = "text" id = "optionText" name = "optionText" value = "One" />

                    <div>
                        Value:
                    </div>

                    <input type = "text" id = "optionValue" name = "optionValue" value = "1" />

                    <div>
                        <input type = "submit" value = "Add"
                            onclick = "xajax_addOption('selectBox',xajax.getFormValues('testForm2')); return false;" />
                    </div>
                </fieldset>
            </form>
        </div>

        <div id = "submittedDiv" style = "margin: 3px;">
        </div>
    </body>
</html>

