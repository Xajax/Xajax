<?php
/*
	File: xulApplication.php

	Example which demonstrates a XUL application with xajax.  XUL will only
	work in Mozilla based browsers like Firefox.
	
	Title: XUL Example - Application
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: XUL example
	
	- <xulServer.php> (this file)
	- <xulClient.xul>
	- <xulApplication.php>
*/

/*
	@package xajax
	@version $Id: xajax.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

require_once("../../xajax_core/xajax.inc.php");
$xajax = new xajax();

function test() {
        $objResponse = new xajaxResponse();
        $objResponse->alert("hallo");
        $objResponse->assign('testButton','label','Success!');
        return $objResponse;
}

$xajax->registerFunction("test");
$xajax->processRequest();

header("Content-Type: application/vnd.mozilla.xul+xml");
require_once("./xulClient.xul");
?>
