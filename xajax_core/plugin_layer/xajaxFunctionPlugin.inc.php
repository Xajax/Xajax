<?php
/*
	File: xajaxFunctionPlugin.inc.php

	Contains the xajaxFunctionPlugin class

	Title: xajaxFunctionPlugin class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxFunctionPlugin.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Constant: XAJAX_FUNCTION
		Specifies that the item being registered via the <xajax->register> function
		is a php function available at global scope, or a specific function from
		an instance of an object.
*/
if (!defined ('XAJAX_FUNCTION')) define ('XAJAX_FUNCTION', 'function');

// require_once is necessary here as the xajaxEvent class will include this also
//SkipAIO
require_once dirname(__FILE__) . '/support/xajaxUserFunction.inc.php';
//EndSkipAIO

/*
	Class: xajaxFunctionPlugin
*/
class xajaxFunctionPlugin extends xajaxRequestPlugin
{
	/*
		Array: aFunctions
		
		An array of <xajaxUserFunction> object that are registered and
		available via a <xajax.request> call.
	*/
	private $aFunctions;

	/*
		String: sXajaxPrefix
		
		A configuration setting that is stored locally and used during
		the client script generation phase.
	*/
	private $sXajaxPrefix;
	
	/*
		String: sDefer
		
		Configuration option that can be used to request that the
		javascript file is loaded after the page has been fully loaded.
	*/
	private $sDefer;
	
	private $bDeferScriptGeneration;

	/*
		String: sRequestedFunction

		This string is used to temporarily hold the name of the function
		that is being requested (during the request processing phase).

		Since canProcessRequest loads this value from the get or post
		data, it is unnecessary to load it again.
	*/
	private $sRequestedFunction;

	/*
		Function: __construct
		
		Constructs and initializes the <xajaxFunctionPlugin>.  The GET and POST
		data is searched for xajax function call parameters.  This will later
		be used to determine if the request is for a registered function in
		<xajaxFunctionPlugin->canProcessRequest>
	*/
	public function __construct()
	{
		$this->aFunctions = array();

		$this->sXajaxPrefix = 'xajax_';
		$this->sDefer = '';
		$this->bDeferScriptGeneration = false;

		$this->sRequestedFunction = NULL;
		
		if (isset($_GET['xjxfun'])) $this->sRequestedFunction = $_GET['xjxfun'];
		if (isset($_POST['xjxfun'])) $this->sRequestedFunction = $_POST['xjxfun'];
	}

	/*
		Function: configure
		
		Sets/stores configuration options used by this plugin.
	*/
	public function configure($sName, $mValue)
	{
		if ('wrapperPrefix' == $sName) {
			$this->sXajaxPrefix = $mValue;
		} else if ('scriptDefferal' == $sName) {
			if (true === $mValue) $this->sDefer = 'defer ';
			else $this->sDefer = '';
		} else if ('deferScriptGeneration' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDeferScriptGeneration = $mValue;
			else if ('deferred' === $mValue)
				$this->bDeferScriptGeneration = $mValue;
		}
	}

	/*
		Function: register
		
		Provides a mechanism for functions to be registered and made available to
		the page via the javascript <xajax.request> call.
	*/
	public function register($aArgs)
	{
		if (1 < count($aArgs))
		{
			$sType = $aArgs[0];

			if (XAJAX_FUNCTION == $sType)
			{
				$xuf = $aArgs[1];

				if (false === ($xuf instanceof xajaxUserFunction))
					$xuf = new xajaxUserFunction($xuf);

				if (2 < count($aArgs))
				{
					if (is_array($aArgs[2]))
					{
						foreach ($aArgs[2] as $sName => $sValue)
						{
							$xuf->configure($sName, $sValue);
						}
					} else {
						$xuf->configure('include', $aArgs[2]);
					}
				}
				$this->aFunctions[] = $xuf;

				return $xuf->generateRequest($this->sXajaxPrefix);
			}
		}

		return false;
	}


	public function generateHash()
	{
		$sHash = '';
		foreach (array_keys($this->aFunctions) as $sKey)
			$sHash .= $this->aFunctions[$sKey]->getName();
		return md5($sHash);
	}

	/*
		Function: generateClientScript
		
		Called by the <xajaxPluginManager> during the client script generation
		phase.  This is used to generate a block of javascript code that will
		contain function declarations that can be used on the browser through
		javascript to initiate xajax requests.
	*/
	public function generateClientScript()
	{
		if (0 < count($this->aFunctions))
		{
			foreach (array_keys($this->aFunctions) as $sKey)
				$this->aFunctions[$sKey]->generateClientScript($this->sXajaxPrefix);
		}
	}

	/*
		Function: canProcessRequest
		
		Determines whether or not the current request can be processed
		by this plugin.
		
		Returns:
		
		boolean - True if the current request can be handled by this plugin;
			false otherwise.
	*/
	public function canProcessRequest()
	{
		if (NULL == $this->sRequestedFunction)
			return false;

		return true;
	}

	/*
		Function: processRequest
		
		Called by the <xajaxPluginManager> when a request needs to be
		processed.
		
		Returns:
		
		mixed - True when the request has been processed successfully.
			An error message when an error has occurred.
	*/
	public function processRequest()
	{
		if (NULL == $this->sRequestedFunction)
			return false;

		$objArgumentManager = xajaxArgumentManager::getInstance();
		$aArgs = $objArgumentManager->process();

		foreach (array_keys($this->aFunctions) as $sKey)
		{
			$xuf = $this->aFunctions[$sKey];

			if ($xuf->getName() == $this->sRequestedFunction)
			{
				$xuf->call($aArgs);
				return true;
			}
		}

		return 'Invalid function request received; no request processor found with this name.';
	}
}

$objPluginManager = xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new xajaxFunctionPlugin(), 100);
