<?php
/*
	File: signup.common.php

	Example which demonstrates a xajax implementation of a sign-up page.
	
	Title: Sign-up Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <signup.php>
	- <signup.common.php>
	- <signup.server.php>
*/

/*
	@package xajax
	@version $Id: xajax.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/
require_once ("../../xajax_core/xajax.inc.php");

session_start();

$xajax = new xajax("signup.server.php");
$xajax->configure('javascript URI','../../');
$xajax->register(XAJAX_FUNCTION,"processForm");
?>