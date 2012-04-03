<?php
require_once( "../xajax_core/xajax.inc.php" );

function saySomething( )
{
    $objResponse=new xajaxResponse();
    $objResponse->alert( "Hello world!" );
    $objResponse->assign( "submittedDiv", "style.visibility", "inherit" );
    return $objResponse;
}

function testForm( $formData, $doDelay=false )
{
    if ( $doDelay )
    {
        sleep( 5 );
    }
    $objResponse=new xajaxResponse();
    $objResponse->alert( "POST\nformData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    return $objResponse;
}

function testForm2( $formData )
{
    $objResponse=new xajaxResponse();
    $objResponse->alert( "GET\nformData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    $objResponse->assign( "submittedDiv", "style.visibility", "hidden" );
    return $objResponse;
}

function testFormFail( $formData )
{
    sleep( 2 );
    header( "HTTP/1.1 500 Internal Server Error" );
    header( "Status: 500" );

    echo " \n";
    exit;
}

function testFormExpire( $formData )
{
    sleep( 15 );
    $objResponse=new xajaxResponse();
    $objResponse->alert( "POST\nformData: " . print_r( $formData, true ) );
    $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
    return $objResponse;
}

$xajax=new xajax();
// SEE file list below!
//$xajax->configure("debug", true);
$xajax->register(XAJAX_FUNCTION, "saySomething");
$xajax->register(XAJAX_FUNCTION, "testForm");
$xajax->register(XAJAX_FUNCTION, "testForm2");
$xajax->register(XAJAX_FUNCTION, "testFormFail");
$xajax->register(XAJAX_FUNCTION, "testFormExpire");
$xajax->processRequest();
$xajax->configure('javascript URI','../');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Call Techniques Test | xajax Tests</title>

        <?php
        $xajax_files=array();
        $xajax_files[]=array
            (
            "xajax_js/xajax_core.js",
            "xajax"
            );

        //	$xajax_files[] = array("xajax_js/xajax_debug.js", "xajax.debug");
        $xajax->printJavascript( "../", $xajax_files )
        ?>

        <script type = "text/javascript">
            function setupCallback()
                {
                xajax.callback.global.onRequest = function()
                    {
                    alert('In global.onRequest');
                    };
                xajax.callback.global.onFailure = function(args)
                    {
                    alert("In global.onFailure...HTTP status code: " + args.request.status);
                    }
                xajax.callback.global.onComplete = function()
                    {
                    alert('In global.onComplete');
                    };
                var cb = xajax.callback.create();
                cb.onRequest = function()
                    {
                    alert('Original onRequest');
                    };
                cb.onResponseDelay = function()
                    {
                    alert('Original onRequestDelay');
                    };
                cb.timers.onResponseDelay.delay = 2600;
                return cb;
                }
        </script>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Call Techniques Test</h1>

        <p>
            <a href = "#" onclick = "xajax.request({xjxfun:'saySomething'});return false;">Say Something</a>

            <form id = "testForm1" onsubmit = "return false;">
                <p>
                    <input type = "text" id = "textBox1" name = "textBox1" value = "Here is some text." />
                </p>

                <p>
                    <input type = "submit"
                        value = "Simple Form Call"
                        onclick = "xajax.request({xjxfun:'testForm'}, {parameters:[xajax.getFormValues('testForm1')]}); return false;" />
                </p>

                <p>
                    <input type = "submit"
                        value = "Form Call via get"
                        onclick = "xajax.request({xjxfun:'testForm2'}, {method: 'get', parameters:[xajax.getFormValues('testForm1')]}); return false;" />
                </p>

                <p>
                    <input type = "submit"
                        value = "Form Call with Callback Object"
                        onclick = "var cb = setupCallback(); xajax.request({xjxfun:'testForm'}, { parameters:[xajax.getFormValues('testForm1'), true], callback: cb }); return false;" />
                </p>

                <p>
                    <input type = "submit"
                        value = "Form Call with Overridden Callback"
                        onclick = "var cb = setupCallback(); xajax.request({xjxfun:'testForm'}, { parameters:[xajax.getFormValues('testForm1'), true], callback: cb, onRequest: function() { alert('Overridden onRequest'); } }); return false;" />
                </p>

                <p>
                    <input type = "submit"
                        value = "Form Call with Inline Callback (also onFailure test)"
                        onclick = "var cb = setupCallback(); xajax.request({xjxfun:'testFormFail'}, { parameters:[xajax.getFormValues('testForm1'), true], onRequest: function() { alert('In inline onRequest'); }, onFailure: function(args) { alert('In inline onFailure -- status is: ' + args.request.status); } }); return false;" />
                </p>

                <p>
                    <input type = "submit"
                        value = "Test onExpiration"
                        onclick = "xjx.$('waiting').style.visibility = 'visible'; xajax.request({xjxfun:'testFormExpire'}, { parameters:[xajax.getFormValues('testForm1'), true], onExpiration: function(args) { alert('In inline onExpiration'); xajax.abortRequest(args) }, onComplete: function() { xjx.$('waiting').style.visibility = 'hidden'; } }); return false;" />

                    <span id = "waiting" style = "visibility: hidden">waiting...</span>
                </p>
            </form>

            <div id = "submittedDiv">
            </div>
    </body>
</html>

