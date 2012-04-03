<?php
/*
    File: searchReplaceTest.php

    Script to test the response command <xajaxResponse->replace> which
    will replace a specified piece of text with another.
    
    Title: Test the <xajaxResponse> object.
    
    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: searchReplaceTest.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/
require_once( "../xajax_core/xajax.inc.php" );

function replace($aForm)
{
    $objResponse=new xajaxResponse();
    $objResponse->replace('content', "innerHTML", $aForm['search'], $aForm['replace'] );
    return $objResponse;
}

$xajax = new xajax();
$xajax->configure("debug", true);
$xajax->configure("javascript URI", '../');
$xajax->register(XAJAX_FUNCTION, "replace");
$xajax->processRequest();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml" xml:lang = "en" lang = "en">
    <head>
        <title>Search and Replace Test | xajax Tests</title>

        <?php $xajax->printJavascript( "../" ) ?>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Search and Replace Test</h1>

        <div id="content"" style="border: 1px solid gray"> Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
        Morbi fermentum. Phasellus non nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada
        fames ac turpis egestas. Nulla id ligula sit amet purus tristique dictum. Fusce at arcu. Maecenas ipsum leo,
        tincidunt eu, vehicula id, elementum feugiat, enim. Nam fringilla mi ac ligula. Quisque tempus, lacus ut
        molestie dignissim, massa ipsum sodales arcu, eget rhoncus sapien diam at velit. Morbi fermentum, dui vel
        tempus vestibulum, diam metus nonummy ligula, ac ultrices lacus est ac sapien. Pellentesque luctus dictum
        massa. Cras ullamcorper ullamcorper massa. Etiam erat odio, gravida eget, ornare vitae, dapibus nec, nunc.
        Phasellus ligula arcu, rutrum at, pellentesque et, varius feugiat, velit. Etiam erat magna, eleifend vel,
        vulputate eget, dignissim non, lectus. Nam at metus. Aenean mollis ligula viverra ipsum.	</div>

	<form id="testForm1" onsubmit="return false;">
	<div>
	Search:<input id="search" name="search" value="" />
	</div>
	<div>
	Replace:<input id="replace" name="replace" value="" />
	</div>
	<div><input type="submit" value="Search & Replace" onclick="xajax_replace(xajax.getFormValues('testForm1')); return false;" /></div>
	</form>

	<div id="submittedDiv"></div>

</body>
</html>