<?php
/*
	File: xulServer.php

	Example which demonstrates a XUL application with xajax.  XUL will only
	work in Mozilla based browsers like Firefox.
	
	Title: XUL Example - Server
	
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

/*
	Section: Standard xajax startup
	
	- include <xajax.inc.php>
	- instantiate main <xajax> object
*/
	require_once("../../xajax_core/xajax.inc.php");
	$xajax = new xajax();

	/*
		Function: test
		
		alert 'hallo', then update the testButton's label to 'Success'
	*/
	function test() {
			$objResponse = new xajaxResponse();
			$objResponse->alert("hallo");
			$objResponse->assign('testButton','label','Success!');
			return $objResponse;
	}
	
	/*
		- Register the function <test>
	*/
	$xajax->register(XAJAX_FUNCTION,"test");
	
	/*
		Section: processRequest
	*/
	$xajax->processRequest();
?>