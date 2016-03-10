<?php
/*
	File: xajaxScriptPlugin.inc.php

	Contains the xajaxScriptPlugin class declaration.

	Title: xajaxScriptPlugin class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxScriptPlugin.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxScriptPlugin
	
	Contains the code that can produce script and style data during deferred script
	generation.  This allows the xajax generated javascript and style sheet information
	to be loaded via an external file reference instead of inlined into the page
	source.
*/
class xajaxScriptPlugin extends xajaxRequestPlugin
{
	/*
		String: sRequest
	*/
	private $sRequest;
	
	/*
		String: sHash
	*/
	private $sHash;
	
	/*
		String: sRequestURI
	*/
	private $sRequestURI;
	
	/*
		Boolean: bDeferScriptGeneration
	*/
	private $bDeferScriptGeneration;
	
	/*
		Boolean: bValidateHash
	*/
	private $bValidateHash;
	
	/*
		Boolean: bWorking
	*/
	private $bWorking;

	private $sJavaScriptURI;

	/*
		Function: __construct
		
		Construct and initialize the xajax script plugin object.  During
		initialization, this plugin will look for hash codes in the
		GET data (parameters passed on the request URI) and store them
		for later use.
	*/
	public function __construct()
	{
		$this->sRequestURI = '';
		$this->bDeferScriptGeneration = false;
		$this->bValidateHash = true;
		
		$this->bWorking = false;

		$this->sRequest = '';
		$this->sHash = null;
		
/*		if (isset($_GET['xjxGenerateJavascript'])) {
			$this->sRequest = 'script';
			$this->sHash = $_GET['xjxGenerateJavascript'];
		}
		
		if (isset($_GET['xjxGenerateStyle'])) {
			$this->sRequest = 'style';
			$this->sHash = $_GET['xjxGenerateStyle'];
		}
		*/
	}

	/*
		Function: configure
		
		Sets/stores configuration options used by this plugin.  See also:
		<xajax::configure>.  This plugin will watch for and store the current
		setting for the following configuration options:
		
		- <requestURI> (string): The requestURI of the current script file.
		- <deferScriptGeneration> (boolean): A flag that indicates whether
			script deferral is in effect or not.
		- <deferScriptValidateHash> (boolean): A flag that indicates whether
			or not the script hash should be validated.
	*/
	public function configure($sName, $mValue)
	{
		if ('requestURI' == $sName) {
			$this->sRequestURI = $mValue;
		} else if ('deferScriptGeneration' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDeferScriptGeneration = $mValue;
		} else if ('deferScriptValidateHash' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bValidateHash = $mValue;
		} else if ('javascript URI' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->sJavaScriptURI = $mValue;
		}
	}
	
	/*
		Function: generateClientScript
		
		Called by the <xajaxPluginManager> when the text of the client script
		(or style) declarations are needed.
		
		This function will only output script or style information if the 
		request URI contained an appropriate hash code and script deferral 
		is in effect.
	*/
	public function generateClientScript()
	{
	}
	
	/*
		Function: canProcessRequest
		
		Called by the <xajaxPluginManager> to determine if this plugin can
		process the current request.  This will return true when the
		requestURI contains an appropriate hash code.
	*/
	public function canProcessRequest()
	{
		return false;
	}
	//todo: clean
	public function _getSections($sType)
	{
	/*	$objPluginManager = xajaxPluginManager::getInstance();
		
		$objPluginManager->configure('deferScriptGeneration', 'deferred');
		
		$aSections = array();
		
		// buffer output
		
		ob_start();
		$objPluginManager->generateClientScript();
		$sScript = ob_get_clean();
		
		// parse out blocks
		
		$aParts = explode('</' . $sType . '>', $sScript);
		foreach ($aParts as $sPart)
		{
			$aValues = explode('<' . $sType, $sPart, 2);
			if (2 == count($aValues))
			{
				list($sJunk, $sPart) = $aValues;
				
				$aValues = explode('>', $sPart, 2);
				if (2 == count($aValues))
				{
					list($sJunk, $sPart) = $aValues;
			
					if (0 < strlen($sPart))
						$aSections[] = $sPart;
				}
			}
		}
		var_dump($aSections);
		$objPluginManager->configure('deferScriptGeneration', $this->bDeferScriptGeneration);
		
		return $aSections;*/
	}
	
	/*
		Function: processRequest
		
		Called by the <xajaxPluginManager> when the current request should be 
		processed.  This plugin will generate the javascript or style sheet information
		that would normally be output by the other xajax plugin objects, when script 
		deferral is in effect.  If script deferral is disabled, this function returns 
		without performing any functions.
	*/
	public function processRequest()
	{
		if ($this->canProcessRequest())
		{
			$aSections = $this->_getSections($this->sRequest);
			
//			echo "<!--" . print_r($aSections, true) . "-->";
			
			// validate the hash
			$sHash = md5(implode($aSections));
			if (false == $this->bValidateHash || $sHash == $this->sHash)
			{
				$sType = 'text/javascript';
				if ('style' == $this->sRequest)
					$sType = 'text/css';
					
				$objResponse = new xajaxCustomResponse($sType);
				
				foreach ($aSections as $sSection)
					$objResponse->append($sSection . "\n");
				
				$objResponseManager = xajaxResponseManager::getInstance();
				$objResponseManager->append($objResponse);
				
				header ('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24)) . ' GMT');

				return true;
			}
			
			return 'Invalid script or style request.';
			trigger_error('Hash mismatch: ' . $this->sRequest . ': ' . $sHash . ' <==> ' . $this->sHash, E_USER_ERROR);
		}
	}
}

/*
	Register the plugin with the xajax plugin manager.
*/
$objPluginManager = xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new xajaxScriptPlugin(), 9999);
