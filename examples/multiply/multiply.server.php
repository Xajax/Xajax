<?php
/*
	File: multiply.server.php

	Example which demonstrates a multiplication using xajax.
	
	Title: Multiplication Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <multiply.php>
	- <multiply.common.php>
	- <multiply.server.php>
*/

/*
	@package xajax
	@version $Id: xajax.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

function multiply($x, $y)
{
	$objResponse = new xajaxResponse();
	$objResponse->assign("z", "value", $x*$y);
	return $objResponse;
}

require("multiply.common.php");
$xajax->processRequest();
?>