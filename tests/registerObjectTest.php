<?php
/*
    File: registerObjectTest.php

    Script to test callable objects.
    
    Title: Call methods of registered objects.
    
    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: registerObjectTest.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/
require_once( "../xajax_core/xajax.inc.php" );

class myObjectTest
{
    var $myValue='default';

    function testInstanceMethod( $formData )
    {
        $objResponse=new xajaxResponse();
        $objResponse->alert( "My value is: {$this->myValue}" );
        $objResponse->alert( "formData: " . print_r( $formData, true ) );
        $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
        return $objResponse;
    }

    function testClassMethod( $formData )
    {
        $objResponse=new xajaxResponse();
        $objResponse->alert( "This is a class method." );
        $objResponse->alert( "formData: " . print_r( $formData, true ) );
        $objResponse->assign( "submittedDiv", "innerHTML", nl2br( print_r( $formData, true ) ) );
        return $objResponse;
    }
}

class objectMethodsTest
{
    var $myValue='default';

    function firstMethod( )
    {
        $objResponse=new xajaxResponse();
        $objResponse->alert( "In firstMethod. My value is: {$this->myValue}" );
        return $objResponse;
    }

    function second_method( )
    {
        $objResponse=new xajaxResponse();
        $objResponse->alert( "In second_method. My value is: {$this->myValue}" );
        return $objResponse;
    }
}

class objectMethodsTest2
    extends objectMethodsTest
{
    function thirdMethod( $arg1 )
    {
        $objResponse=new xajaxResponse();
        $objResponse->alert( "In thirdMethod. My value is: {$this->myValue} and arg1: $arg1" );
        return $objResponse;
    }
}

$xajax = new xajax();

//$xajax->configure("debug", true);

$myObj2 = new objectMethodsTest();

if ( 0 <= version_compare( '5.0', PHP_VERSION ) )
    // for PHP4
    eval( '$aMethodsTest = $xajax->register(XAJAX_CALLABLE_OBJECT, &$myObj2);' );
else
    $aMethodsTest = $xajax->register( XAJAX_CALLABLE_OBJECT, $myObj2 );

$myObj2->myValue='right:2';

$myObj3 = new objectMethodsTest2();

if ( 0 <= version_compare( '5.0', PHP_VERSION ) )
    // for PHP4
    eval( '$aMethodsTest2 = $xajax->register(XAJAX_CALLABLE_OBJECT, &$myObj3);' );
else
    $aMethodsTest2 = $xajax->register( XAJAX_CALLABLE_OBJECT, $myObj3 );

$aMethodsTest2['thirdmethod']->setParameter( 0, XAJAX_QUOTED_VALUE, 'howdy' );
$myObj3->myValue='right:3';

$myObj = new myObjectTest();
$myObj->myValue='wrong';
$requestInstanceMethod = $xajax->register(
	XAJAX_FUNCTION,  
	array(
	    "testForm",
		$myObj,
		"testInstanceMethod"
		)
	);

$requestInstanceMethod->setParameter( 0, XAJAX_FORM_VALUES, 'testForm1' );
$requestClassMethod = $xajax->register(
	XAJAX_FUNCTION, 
	array(
		"testForm2",
		"myObjectTest",
		"testClassMethod"
		)
	);

$requestClassMethod->setParameter( 0, XAJAX_FORM_VALUES, 'testForm1' );
$myObj->myValue='right';

$xajax->processRequest();
$xajax->configure('javascript URI','../');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml" xml:lang = "en" lang = "en">
    <head>
        <title>Register Object Test | xajax Tests</title>

        <?php $xajax->printJavascript( "../" ) ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Register Object Test</h1>

        <p>
            <a href = '#' onclick = '<?php $aMethodsTest['firstmethod']->printScript(); ?>; return false;'>Test
            Callable Object:2</a>

            <br />

            <a href = '#' onclick = '<?php $aMethodsTest['second_method']->printScript(); ?>; return false;'>Test
            Callable Object:2</a>

            <br />

            <a href = '#' onclick = '<?php $aMethodsTest2['thirdmethod']->printScript(); ?>; return false;'>Test
            Callable Object:3</a>

            <br />

            <a href = '#' onclick = '<?php $aMethodsTest2['firstmethod']->printScript(); ?>; return false;'>Test
            Callable Object:3</a>
        </p>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <input type = "text" id = "textBox1" name = "textBox1" value = "This is some text" />
            </p>

            <p>
                <input type = 'submit' value = 'Submit to Instance Method'
                    onclick = '<?php $requestInstanceMethod->printScript(); ?>; return false;' />
            </p>

            <p>
                <input type = 'submit' value = 'Submit to Class Method'
                    onclick = '<?php $requestClassMethod->printScript(); ?>; return false;' />
            </p>
        </form>

        <div id = "submittedDiv">
        </div>
    </body>
</html>

