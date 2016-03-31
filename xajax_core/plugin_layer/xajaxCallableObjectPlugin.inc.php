<?php
/*
	File: xajaxCallableObjectPlugin.inc.php

	Contains the xajaxCallableObjectPlugin class

	Title: xajaxCallableObjectPlugin class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxCallableObjectPlugin.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Constant: XAJAX_CALLABLE_OBJECT
		Specifies that the item being registered via the <xajax->register> function is a
		object who's methods will be callable from the browser.
*/
if (!defined ('XAJAX_CALLABLE_OBJECT')) define ('XAJAX_CALLABLE_OBJECT', 'callable object');

//SkipAIO
require dirname(__FILE__) . '/support/xajaxCallableObject.inc.php';
//EndSkipAIO

/*
	Class: xajaxCallableObjectPlugin
*/
final class xajaxCallableObjectPlugin extends xajaxRequestPlugin
{
	/*
		Array: aCallableObjects
	*/
	private $aCallableObjects;

	/*
		Array: aClassPaths
	*/
	private $aClassPaths;

	/*
		String: sXajaxPrefix
	*/
	private $sXajaxPrefix;
	
	/*
		String: sDefer
	*/
	private $sDefer;
	
	private $bDeferScriptGeneration;

	/*
		String: sRequestedClass
	*/
	private $sRequestedClass;
	
	/*
		String: sRequestedMethod
	*/
	private $sRequestedMethod;

	/*
		Function: xajaxCallableObjectPlugin
	*/
	public function __construct()
	{
		$this->aCallableObjects = array();
		$this->aClassPaths = array();

		$this->sXajaxPrefix = 'xajax_';
		$this->sDefer = '';
		$this->bDeferScriptGeneration = false;

		$this->sRequestedClass = NULL;
		$this->sRequestedMethod = NULL;

		if (!empty($_GET['xjxcls'])) $this->sRequestedClass = $_GET['xjxcls'];
		if (!empty($_GET['xjxmthd'])) $this->sRequestedMethod = $_GET['xjxmthd'];
		if (!empty($_POST['xjxcls'])) $this->sRequestedClass = $_POST['xjxcls'];
		if (!empty($_POST['xjxmthd'])) $this->sRequestedMethod = $_POST['xjxmthd'];
	}

	/*
		Function: setRequestedClass
	*/
	public function setRequestedClass($sRequestedClass)
	{
		$this->sRequestedClass = $sRequestedClass;
	}

	/*
		Function: configure
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
	*/
	public function register($aArgs)
	{
		if (1 < count($aArgs))
		{
			$sType = $aArgs[0];

			if (XAJAX_CALLABLE_OBJECT == $sType)
			{
				$xco = $aArgs[1];

//SkipDebug
				if (!is_object($xco))
				{
					trigger_error("To register a callable object, please provide an instance of the desired class.", E_USER_WARNING);
					return false;
				}
//EndSkipDebug

				if (!($xco instanceof xajaxCallableObject))
					$xco = new xajaxCallableObject($xco);

				if (2 < count($aArgs) && is_array($aArgs[2]))
				{
					foreach ($aArgs[2] as $sKey => $aValue)
					{
						foreach ($aValue as $sName => $sValue)
						{
							if($sName == 'classpath' && $sValue != '')
								$this->aClassPaths[] = $sValue;
							$xco->configure($sKey, $sName, $sValue);
						}
					}
				}
				$this->aCallableObjects[$xco->getName()] = $xco;

				return $xco->generateRequests($this->sXajaxPrefix);
			}
		}

		return false;
	}


	public function generateHash()
	{
		$sHash = '';
		foreach($this->aCallableObjects as $xCallableObject)
			$sHash .= $xCallableObject->getName();

		foreach($this->aCallableObjects as $xCallableObject)
			$sHash .= implode('|', $xCallableObject->getMethods());

		return md5($sHash);
	}

	/*
		Function: generateClientScript
	*/
	public function generateClientScript()
	{
		// Generate code for javascript classes declaration
		$classes = array();
		foreach($this->aClassPaths as $sClassPath)
		{
			$offset = 0;
			$sClassPath .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
			while(($dotPosition = strpos($sClassPath, '.', $offset)) !== false)
			{
				$class = substr($sClassPath, 0, $dotPosition);
				// Generate code for this class
				if(!array_key_exists($class, $classes))
				{
					echo "{$this->sXajaxPrefix}$class = {};\n";
					$classes[$class] = $class;
				}
				$offset = $dotPosition + 1;
			}
		}
		$classes = null;

		foreach($this->aCallableObjects as $xCallableObject)
			$xCallableObject->generateClientScript($this->sXajaxPrefix);
	}

	/*
		Function: canProcessRequest
	*/
	public function canProcessRequest()
	{
		if (NULL == $this->sRequestedClass)
			return false;
		if (NULL == $this->sRequestedMethod)
			return false;

		return true;
	}

	/*
		Function: processRequest
	*/
	public function processRequest()
	{
		if (NULL == $this->sRequestedClass)
			return false;
		if (NULL == $this->sRequestedMethod)
			return false;

		$objArgumentManager = xajaxArgumentManager::getInstance();
		$aArgs = $objArgumentManager->process();

		if(array_key_exists($this->sRequestedClass, $this->aCallableObjects))
		{
			$xCallableObject = $this->aCallableObjects[$this->sRequestedClass];
			if ($xCallableObject->hasMethod($this->sRequestedMethod))
			{
				$xCallableObject->call($this->sRequestedMethod, $aArgs);
				return true;
			}
		}

		return 'Invalid request for a callable object.';
	}
}

$objPluginManager = xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new xajaxCallableObjectPlugin(), 102);
