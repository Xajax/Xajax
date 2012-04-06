<?php
/*
	File: signup.server.php

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

require_once ("signup.common.php");

function processForm($aFormValues)
{
	if (array_key_exists("username",$aFormValues))
	{
		return processAccountData($aFormValues);
	}
	else if (array_key_exists("firstName",$aFormValues))
	{
		return processPersonalData($aFormValues);
	}
}

function processAccountData($aFormValues)
{
	$objResponse = new xajaxResponse();
	
	$bError = false;
	
	if (trim($aFormValues['username']) == "")
	{
		$objResponse->alert("Please enter a username.");
		$bError = true;
	}
	if (trim($aFormValues['newPass1']) == "")
	{
		$objResponse->alert("You may not have a blank password.");
		$bError = true;
	}
	if ($aFormValues['newPass1'] != $aFormValues['newPass2'])
	{
		$objResponse->alert("Passwords do not match.  Try again.");
		$bError = true;
	}

	if (!$bError)
	{
		$_SESSION = array();
		$_SESSION['newaccount']['username'] = trim($aFormValues['username']);
		$_SESSION['newaccount']['password'] = trim($aFormValues['newPass1']);
		
		$sForm = "<form id=\"signupForm\" action=\"javascript:void(null);\" onsubmit=\"submitSignup();\">";
		$sForm .="<div>First Name:</div><div><input type=\"text\" name=\"firstName\" /></div>";
		$sForm .="<div>Last Name:</div><div><input type=\"text\" name=\"lastName\" /></div>";
		$sForm .="<div>Email:</div><div><input type=\"text\" name=\"email\" /></div>";
		$sForm .="<div class=\"submitDiv\"><input id=\"submitButton\" type=\"submit\" value=\"done\"/></div>";
		$sForm .="</form>";
		$objResponse->assign("formDiv","innerHTML",$sForm);
		$objResponse->assign("formWrapper","style.backgroundColor", "rgb(67,149,97)");
		$objResponse->assign("outputDiv","innerHTML","\$_SESSION:<pre>".var_export($_SESSION,true)."</pre>");
	}
	else
	{
		$objResponse->assign("submitButton","value","continue ->");
		$objResponse->assign("submitButton","disabled",false);
	}
	
	return $objResponse;
}

function processPersonalData($aFormValues)
{
	$objResponse = new xajaxResponse();
	
	$bError = false;
	if (trim($aFormValues['firstName']) == "")
	{
		$objResponse->alert("Please enter your first name.");
		$bError = true;
	}
	if (trim($aFormValues['lastName']) == "")
	{
		$objResponse->alert("Please enter your last name.");
		$bError = true;
	}
	if (!eregi("^[a-zA-Z0-9]+[_a-zA-Z0-9-]*(\.[_a-z0-9-]+)*@[a-z??????0-9]+(-[a-z??????0-9]+)*(\.[a-z??????0-9-]+)*(\.[a-z]{2,4})$", $aFormValues['email']))
	{
		$objResponse->alert("Please enter a valid email address.");
		$bError = true;
	}

	if (!$bError)
	{
		$_SESSION['newaccount']['firstname'] = $aFormValues['firstName'];
		$_SESSION['newaccount']['lastname'] = $aFormValues['lastName'];
		$_SESSION['newaccount']['email'] = $aFormValues['email'];
		
		$objResponse->assign("formDiv","style.textAlign","center");
		$sForm = "Account created.<br />Thank you.";
		$objResponse->assign("formDiv","innerHTML",$sForm);
		$objResponse->assign("formWrapper","style.backgroundColor", "rgb(67,97,149)");
		$objResponse->assign("outputDiv","innerHTML","\$_SESSION:<pre>".var_export($_SESSION,true)."</pre>");
	}
	else
	{
		$objResponse->assign("submitButton","value","done");
		$objResponse->assign("submitButton","disabled",false);
	}
	
	return $objResponse;
}

$xajax->processRequest();
?>