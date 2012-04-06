<?php
/*
	File: multiply.php

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

require("multiply.common.php");

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>xajax Multiplier</title> 
		<?php $xajax->printJavascript(); ?> 
	</head> 
	<body> 
		<input type="text" name="x" id="x" value="2" size="3" /> * 
		<input type="text" name="y" id="y" value="3" size="3" /> = 
		<input type="text" name="z" id="z" value="" size="3" /> 
		<input type="button" value="Calculate" onclick="xajax_multiply(document.getElementById('x').value,document.getElementById('y').value);return false;" />
	</body> 
</html>