<?php
/*
	File: signup.php

	Example which demonstrates a xajax implementation of a multi-page sign-up.
	
	Title: Multi-page Sign-up Example
	
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
require_once('signup.common.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php $xajax->printJavascript(); ?>
		<style type="text/css">
		#formWrapper{
			color: rgb(255,255,255);
			background-color: rgb(149,67,97);
			width: 200px;
		}
		#title{
			text-align: center;
			background-color: rgb(0,0,0);
		}
		#formDiv{
			padding: 25px;
		}
		.submitDiv{
			margin-top: 10px;
			text-align: center;
		}
		</style>
		<script type="text/javascript">
		function submitSignup()
		{
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="please wait...";
			xajax_processForm(xajax.getFormValues("signupForm"));
			return false;
		}
		</script>
	</head>
	<body>
		<div id="formWrapper">
		
			<div id="title">Create a New Account</div>
			
			<div id="formDiv">
				<form id="signupForm" action="javascript:void(null);" onsubmit="submitSignup();">
					<div>Username:</div><div><input type="text" name="username" /></div>
					<div>Password:</div><div><input type="password" name="newPass1" /></div>
					<div>Confirm Password:</div><div><input type="password" name="newPass2" /></div>
					<div class="submitDiv"><input id="submitButton" type="submit" value="continue ->"/></div>
				</form>
			</div>
			
		</div>
		
		<div id="outputDiv">
		</div>
	</body>
</html>