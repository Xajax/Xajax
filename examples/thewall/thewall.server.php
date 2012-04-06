<?php
/*
	File: thewall.server.php

	Example which demonstrates a xajax implementation of a graffiti wall.
	
	Title: Graffiti Wall Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <thewall.php>
	- <thewall.common.php>
	- <thewall.server.php>
*/

/*
	@package xajax
	@version $Id: xajax.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Section: Define constants
	
	Integer: MAX_SCRIBBLES
	
	The number of scribbles that will be retained.  The default is 5.
*/
if (!defined ('MAX_SCRIBBLES'))
{
	define ('MAX_SCRIBBLES', 5);
}

/*
	String: DATA_FILE
	
	The file that will be used to store the messages on the server.
*/
if (!defined ('DATA_FILE'))
{
	define ('DATA_FILE', "thewall.dta");
}

/*
	Class: graffiti
*/
class graffiti
{
	/*
		String: html
		
		Stores the html that is being generated for the current request.
	*/
	var $html;
	
	/*
		Boolean: isValid
		
		Indicates that the html generated for this request is valid and
		can be added to the wall <DATA_FILE>.
	*/
	var $isValid = false;
	
	/*
		Constructor: graffiti
		
		Builds the <html> output and sets the <isValid> indicator.
	*/
	function graffiti($sHandle, $sWords)
	{
		if (trim($sHandle) == "" || trim($sWords) == "")
		{
			return;
		}
		$this->html  = "\n<div style=\"font-weight: bold;text-align:".$this->getRandomAlignment();
		$this->html .= ";color:".$this->getRandomColor().";\">";
		$this->html .= "<span style=\"font-size:".$this->getRandomFontSize()."%;\">";
		$this->html .= strip_tags(stripslashes($sWords));
		$this->html .= "</span><br/><span style=\"font-size: small;\">";
		$this->html .= " ~ ".strip_tags(stripslashes($sHandle))." ".date("m/d/Y H:i:s")."</span></div>";
		
		$this->isValid = true;
	}
	
	/*
		Function: getRandomFontSize
		
		Generate a font size based off a random number.
	*/
	function getRandomFontSize()
	{
		srand((double)microtime()*1000003);
		return rand(100,300);
	}
	
	/*
		Function: getRandomColor
		
		Generate a browser safe color based on a random number.
	*/
	function getRandomColor()
	{
		$sColor = "rgb(";
		srand((double)microtime()*1000003);
		$sColor .= rand(0,255).",";
		srand((double)microtime()*1000003);
		$sColor .= rand(0,255).",";
		$sColor .= rand(0,255).")";
		
		return $sColor;
	}
	
	/*
		Function: getRandomAlignment
		
		Generates a text-alignment value based on a random number.
	*/
	function getRandomAlignment()
	{
		$sAlign = "";
		srand((double)microtime()*1000003);
		$textAlign = rand(0,2);
		switch($textAlign)
		{
			case 0: $sAlign = "left"; break;
			case 1: $sAlign = "right"; break;
			case 2: $sAlign = "center"; break;
			
		}
		return $sAlign;
	}
	
	/*
		Function: save
		
		Writes the current <graffiti->html> to the <DATA_FILE> when <graffiti->isValid>
		or returns an error message.
	*/
	function save()
	{
		if ($this->isValid)
		{
			$rFile = @fopen(DATA_FILE,"a+");
			if (!$rFile) {
				return "ERROR: the graffiti data file could not be written to the " . dirname(realpath(DATA_FILE)) . " folder.";
			}
			fwrite($rFile, $this->html);
			fclose($rFile);
			return null;
		}
		else
		{
			return "Please supply both a handle and some graffiti to scribble on the wall.";
		}
	}
}

/*
	Section: xajax request handlers
	
	Function: scribble
	
	Processes the users form input and passes the values to an instance
	of the <graffiti> class.
	
	If the graffiti class generates and error, it is returned to the browser
	using <xajax->alert>, otherwise, <xajax->script> is used to instruct the
	browser to make a request to <updateWall> and <xajax->clear> is used to
	clear the form input.
*/
function scribble($aFormValues)
{
	$sHandle = $aFormValues['handle'];
	$sWords = $aFormValues['words'];
	$objResponse = new xajaxResponse();
	
	$objGraffiti = new graffiti($sHandle,$sWords);
	$sErrMsg = $objGraffiti->save();
	if (!$sErrMsg)
	{
		$objResponse->script("xajax_updateWall();");
		$objResponse->clear("words","value");
	}
	else
		$objResponse->alert($sErrMsg);
	
	return $objResponse;
}

/*
	Function: updateWall
	
	Processes the data previously written to the <DATA_FILE> by <graffiti->save>.
*/
function updateWall()
{
	$objResponse = new xajaxResponse();
	
	if (file_exists(DATA_FILE)) {
		$aFile = @file(DATA_FILE);
		if (!$aFile) {
			$objResponse->addAlert("ERROR: the graffiti data file could not be written to the " . dirname(realpath(DATA_FILE)) . " folder.");
			return $objResponse;
		}
		
		$sHtmlSave = implode("\n",array_slice($aFile, -MAX_SCRIBBLES));
		$sHtmlSave=str_replace("\n\n","\n",$sHtmlSave);
	}
	else {
		$sHtmlSave = "";
		$aFile = array();
	}
	$rFile = @fopen(DATA_FILE,"w+");
	if (!$rFile) {
		$objResponse->alert("ERROR: the graffiti data file could not be written to the " . dirname(realpath(DATA_FILE)) . " folder.");
		return $objResponse;
	}
	fwrite($rFile, $sHtmlSave);
	fclose($rFile);
	
	$sHtml = implode("\n",array_reverse(array_slice($aFile, -MAX_SCRIBBLES)));
	
	$objResponse->assign("theWall","innerHTML",$sHtml);

	return $objResponse;
}

require("thewall.common.php");
$xajax->processRequest();
?>