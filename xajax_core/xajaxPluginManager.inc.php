<?php
/*
	File: xajaxPluginManager.inc.php

	Contains the xajax plugin manager.
	
	Title: xajax plugin manager
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxPluginManager.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

//SkipAIO
require(dirname(__FILE__) . '/xajaxPlugin.inc.php');
//EndSkipAIO

/*
	Class: xajaxPluginManager
*/
final class xajaxPluginManager
{
	/*
		Array: aRequestPlugins
	*/
	private $aRequestPlugins;
	
	/*
		Array: aResponsePlugins
	*/
	private $aResponsePlugins;
	
	/*
		Array: aConfigurable
	*/
	private $aConfigurable;
	
	/*
		Array: aRegistrars
	*/
	private $aRegistrars;
	
	/*
		Array: aProcessors
	*/
	private $aProcessors;
	
	/*
		Array: aClientScriptGenerators
	*/
	private $aClientScriptGenerators;
	
	/*
		Function: xajaxPluginManager
		
		Construct and initialize the one and only xajax plugin manager.
	*/

	private $sJsURI;
	private $sJsDir;
	public  $aJsFiles = array();
	private $sDefer;
	private $sDeferDir;
	private $sRequestURI;
	private $sStatusMessages;
	private $sWaitCursor;
	private $sVersion;
	private $sDefaultMode;
	private $sDefaultMethod;
	private $bDebug;
	private $bVerboseDebug;
	private $nScriptLoadTimeout;
	private $bUseUncompressedScripts;
	private $bDeferScriptGeneration;
	private $sLanguage;
	private $nResponseQueueSize;
	private $sDebugOutputID;
	private $sResponseType;

	private function __construct()
	{
		$this->aRequestPlugins = array();
		$this->aResponsePlugins = array();
		
		$this->aConfigurable = array();
		$this->aRegistrars = array();
		$this->aProcessors = array();
		$this->aClientScriptGenerators = array();

		$this->sJsURI = '';
		$this->sJsDir = dirname(dirname(__FILE__)) . '/xajax_js';
		$this->aJsFiles = array();
		$this->sDefer = '';
		$this->sDeferDir = 'deferred';
		$this->sRequestURI = '';
		$this->sStatusMessages = 'false';
		$this->sWaitCursor = 'true';
		$this->sVersion = 'unknown';
		$this->sDefaultMode = 'asynchronous';
		$this->sDefaultMethod = 'POST';	// W3C: Method is case sensitive
		$this->bDebug = false;
		$this->bVerboseDebug = false;
		$this->nScriptLoadTimeout = 2000;
		$this->bUseUncompressedScripts = false;
		$this->bDeferScriptGeneration = false;
		$this->sLanguage = null;
		$this->nResponseQueueSize = null;
		$this->sDebugOutputID = null;
	}
	
	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the 
		xajax plugin manager.
		
		Returns:
		
		object : a reference to the one and only instance of the
			plugin manager.
	*/
	public static function getInstance()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxPluginManager();    
		}
		return $obj;
	}
	
	/*
		Function: loadPlugins
		
		Loads plugins from the folders specified.
		
		Parameters:
			$aFolders - (array): Array of folders to check for plugins
	*/
	public function loadPlugins($aFolders)
	{
		foreach ($aFolders as $sFolder) {
			if (is_dir($sFolder))
			if ($handle = opendir($sFolder)) {
				while (!(false === ($sName = readdir($handle)))) {
					$nLength = strlen($sName);
					if (8 < $nLength) {
						$sFileName = substr($sName, 0, $nLength - 8);
						$sExtension = substr($sName, $nLength - 8, 8);
						if ('.inc.php' == $sExtension) {
							require $sFolder . '/' . $sFileName . $sExtension;
						}
					}
				}
				
				closedir($handle);
			}
		}
	}
	
	/*
		Function: _insertIntoArray
		
		Inserts an entry into an array given the specified priority number. 
		If a plugin already exists with the given priority, the priority is
		automatically incremented until a free spot is found.  The plugin
		is then inserted into the empty spot in the array.
		
		Parameters:
		
		$aPlugins - (array): Plugins array
		$objPlugin - (object): A reference to an instance of a plugin.
		$nPriority - (number): The desired priority, used to order
			the plugins.
		
	*/
	private function _insertIntoArray(&$aPlugins, $objPlugin, $nPriority)
	{
		while (isset($aPlugins[$nPriority]))
			$nPriority++;
		
		$aPlugins[$nPriority] = $objPlugin;
	}
	
	/*
		Function: registerPlugin
		
		Registers a plugin.
		
		Parameters:
		
		objPlugin - (object):  A reference to an instance of a plugin.
		
		Note:
		Below is a table for priorities and their description:
		0 thru 999: Plugins that are part of or extensions to the xajax core
		1000 thru 8999: User created plugins, typically, these plugins don't care about order
		9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
	*/
	public function registerPlugin($objPlugin, $nPriority=1000)
	{
		if ($objPlugin instanceof xajaxRequestPlugin)
		{
			$this->_insertIntoArray($this->aRequestPlugins, $objPlugin, $nPriority);
			
			if (method_exists($objPlugin, 'register'))
				$this->_insertIntoArray($this->aRegistrars, $objPlugin, $nPriority);
			
			if (method_exists($objPlugin, 'canProcessRequest'))
				if (method_exists($objPlugin, 'processRequest'))
					$this->_insertIntoArray($this->aProcessors, $objPlugin, $nPriority);
		}
		else if ( $objPlugin instanceof xajaxResponsePlugin)
		{
			// The defined name of a response plugin is used as key in the plugin table
			$this->aResponsePlugins[$objPlugin->getName()] = $objPlugin;
		}
		else
		{
//SkipDebug
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
				$objLanguageManager->getText('XJXPM:IPLGERR:01') 
				. get_class($objPlugin) 
				. $objLanguageManager->getText('XJXPM:IPLGERR:02')
				, E_USER_ERROR
				);
//EndSkipDebug
		}
		
		if (method_exists($objPlugin, 'configure'))
			$this->_insertIntoArray($this->aConfigurable, $objPlugin, $nPriority);

		if (method_exists($objPlugin, 'generateClientScript'))
			$this->_insertIntoArray($this->aClientScriptGenerators, $objPlugin, $nPriority);
	}

	/*
		Function: canProcessRequest
		
		Calls each of the request plugins and determines if the
		current request can be processed by one of them.  If no processor identifies
		the current request, then the request must be for the initial page load.
		
		See <xajax->canProcessRequest> for more information.
	*/
	public function canProcessRequest()
	{

		$aKeys = array_keys($this->aProcessors);
		sort($aKeys);
		foreach ($aKeys as $sKey) {
			$mResult = $this->aProcessors[$sKey]->canProcessRequest();
			if (true === $mResult)
				return true;
			else if (is_string($mResult))
				return $mResult;
		}
		return false;
	}

	/*
		Function: processRequest

		Calls each of the request plugins to request that they process the
		current request.  If the plugin processes the request, it will
		return true.
	*/
	public function processRequest()
	{
		$bHandled = false;
		
		$aKeys = array_keys($this->aProcessors);
		sort($aKeys);
		foreach ($aKeys as $sKey) {
			$mResult = $this->aProcessors[$sKey]->processRequest();
			if (true === $mResult)
				$bHandled = true;
			else if (is_string($mResult))
				return $mResult;
		}

		return $bHandled;
	}
	
	/*
		Function: configure
		
		Call each of the request plugins passing along the configuration
		setting specified.
		
		Parameters:
		
		sName - (string):  The name of the configuration setting to set.
		mValue - (mixed):  The value to be set.
	*/
	public function configure($sName, $mValue)
	{


		$aKeys = array_keys($this->aConfigurable);
		sort($aKeys);
		foreach ($aKeys as $sKey)
			$this->aConfigurable[$sKey]->configure($sName, $mValue);

		if ('javascript URI' == $sName) {
			$this->sJsURI = $mValue;
		} else if ('javascript Dir' == $sName) {
			$this->sJsDir = $mValue;
		} else if ("javascript files" == $sName) {
			$this->aJsFiles = array_merge($this->aJsFiles,$mValue);
		} else if ("scriptDefferal" == $sName) {
			if (true === $mValue) $this->sDefer = "defer ";
			else $this->sDefer = "";
		} else if ("requestURI" == $sName) {
			$this->sRequestURI = $mValue;
		} else if ("statusMessages" == $sName) {
			if (true === $mValue) $this->sStatusMessages = "true";
			else $this->sStatusMessages = "false";
		} else if ("waitCursor" == $sName) {
			if (true === $mValue) $this->sWaitCursor = "true";
			else $this->sWaitCursor = "false";
		} else if ("version" == $sName) {
			$this->sVersion = $mValue;
		} else if ("defaultMode" == $sName) {
			if ("asynchronous" == $mValue || "synchronous" == $mValue)
				$this->sDefaultMode = $mValue;
		} else if ("defaultMethod" == $sName) {
			if ("POST" == $mValue || "GET" == $mValue)	// W3C: Method is case sensitive
				$this->sDefaultMethod = $mValue;
		} else if ("debug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDebug = $mValue;
		} else if ("verboseDebug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bVerboseDebug = $mValue;
		} else if ("scriptLoadTimeout" == $sName) {
			$this->nScriptLoadTimeout = $mValue;
		} else if ("useUncompressedScripts" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bUseUncompressedScripts = $mValue;
		} else if ('deferScriptGeneration' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDeferScriptGeneration = $mValue;
			else if ('deferred' == $mValue)
				$this->bDeferScriptGeneration = $mValue;
		} else if ('deferDirectory' == $sName) {
			$this->sDeferDir = $mValue;
		} else if ('language' == $sName) {
			$this->sLanguage = $mValue;
		} else if ('responseQueueSize' == $sName) {
			$this->nResponseQueueSize = $mValue;
		} else if ('debugOutputID' == $sName) {
			$this->sDebugOutputID = $mValue;
		} else if ('responseType' == $sName) {
			$this->sResponseType = $mValue;
		}

	}
	
	/*
		Function: register
		
		Call each of the request plugins and give them the opportunity to 
		handle the registration of the specified function, event or callable object.
		
		Parameters:
		 $aArgs - (array) :
	*/
	public function register($aArgs)
	{
		$aKeys = array_keys($this->aRegistrars);
		sort($aKeys);
		foreach ($aKeys as $sKey)
		{
			$objPlugin = $this->aRegistrars[$sKey];
			$mResult = $objPlugin->register($aArgs);
			if ( $mResult instanceof xajaxRequest )
				return $mResult;
			if (is_array($mResult))
				return $mResult;
			if (is_bool($mResult))
				if (true === $mResult)
					return true;
		}
//SkipDebug
		$objLanguageManager = xajaxLanguageManager::getInstance();
		trigger_error(
			$objLanguageManager->getText('XJXPM:MRMERR:01') 
			. print_r($aArgs, true)
			, E_USER_ERROR
			);
//EndSkipDebug
	}

	/*
		Function: _getScriptFilename

		Returns the name of the script file, based on the current settings.

		sFilename - (string):  The base filename.

		Returns:

		string - The filename as it should be specified in the script tags
		on the browser.
	*/
	private function _getScriptFilename($sFilename)
	{
		if ($this->bUseUncompressedScripts) {
			return str_replace('.js', '_uncompressed.js', $sFilename);
		}
		return $sFilename;
	}

	/*
		Function: generateClientScript
		
		Call each of the request and response plugins giving them the
		opportunity to output some javascript to the page being generated.  This
		is called only when the page is being loaded initially.  This is not 
		called when processing a request.
	*/
	public function generateClientScript()
	{

		$sJsURI = $this->sJsURI;

		$aJsFiles = $this->aJsFiles;

		if ($sJsURI == '' || substr($sJsURI, -1) != '/')
			$sJsURI .= '/';
		// $sJsURI .= 'xajax/js/';

		$aJsFiles[] = array($this->_getScriptFilename('xajax_core.js'), 'xajax');

		if (true === $this->bDebug)
			$aJsFiles[] = array($this->_getScriptFilename('xajax_debug.js'), 'xajax.debug');

		if (true === $this->bVerboseDebug)
			$aJsFiles[] = array($this->_getScriptFilename('xajax_verbose.js'), 'xajax.debug.verbose');

		if (null !== $this->sLanguage)
			$aJsFiles[] = array($this->_getScriptFilename('xajax_lang_' . $this->sLanguage . '.js'), 'xajax');

		$sCrLf = "\n";
		echo $sCrLf;
		echo '<';
		echo 'script type="text/javascript" ';
		echo $this->sDefer;
		echo 'charset="UTF-8">';
		echo $sCrLf;
		echo '/* <';
		echo '![CDATA[ */';
		echo $sCrLf;
		echo 'try { if (undefined == typeof xajax.config) xajax.config = {};  } catch (e) { xajax = {}; xajax.config = {};  };';
		echo $sCrLf;
		echo 'xajax.config.requestURI = "';
		echo $this->sRequestURI;
		echo '";';
		echo $sCrLf;
		echo 'xajax.config.statusMessages = ';
		echo $this->sStatusMessages;
		echo ';';
		echo $sCrLf;
		echo 'xajax.config.waitCursor = ';
		echo $this->sWaitCursor;
		echo ';';
		echo $sCrLf;
		echo 'xajax.config.version = "';
		echo $this->sVersion;
		echo '";';
		echo $sCrLf;
		echo 'xajax.config.defaultMode = "';
		echo $this->sDefaultMode;
		echo '";';
		echo $sCrLf;
		echo 'xajax.config.defaultMethod = "';
		echo $this->sDefaultMethod;
		echo '";';
		echo $sCrLf;
		echo 'xajax.config.JavaScriptURI = "';
		echo $this->sJsURI;
		echo '";';
		echo $sCrLf;
		echo 'xajax.config.responseType = "';
		echo $this->sResponseType;
		echo '";';

		if (false === (null === $this->nResponseQueueSize))
		{
			echo $sCrLf;
			echo 'xajax.config.responseQueueSize = ';
			echo $this->nResponseQueueSize;
			echo ';';
		}

		if (true === $this->bDebug)
		{
			if (false === (null === $this->sDebugOutputID))
			{
				echo $sCrLf;
				echo 'xajax.debug = {};';
				echo $sCrLf;
				echo 'xajax.debug.outputID = "';
				echo $this->sDebugOutputID;
				echo '";';
			}
		}
		if (0 < $this->nScriptLoadTimeout) {
			foreach ($aJsFiles as $aJsFile) {
				//				echo '<';
				//				echo 'script type="text/javascript" ';
				//				echo $this->sDefer;
				//				echo 'charset="UTF-8">';
				echo $sCrLf;
				echo '/* <';
				echo '![CDATA[ */';
				echo $sCrLf;
				echo 'window.setTimeout(';
				echo $sCrLf;
				echo ' function() {';
				echo $sCrLf;
				echo '  var scriptExists = false;';
				echo $sCrLf;
				echo '  try { if (';
				echo $aJsFile[1];
				echo '.isLoaded) scriptExists = true; }';
				echo $sCrLf;
				echo '  catch (e) {}';
				echo $sCrLf;
				echo '  if (!scriptExists) {';
				echo $sCrLf;
				echo '   alert("Error: the ';
				echo $aJsFile[1];
				echo ' Javascript component could not be included. Perhaps the URL is incorrect?\nURL: ';
				echo $sJsURI;
				echo $aJsFile[0];
				echo '");';
				echo $sCrLf;
				echo '  }';
				echo $sCrLf;
				echo ' }, ';
				echo $this->nScriptLoadTimeout;
				echo ');';
				echo $sCrLf;
				//				echo '/* ]]> */';
				//				echo $sCrLf;
				//				echo '<';
				//				echo '/script>';
				//				echo $sCrLf;
			}
		}

		echo $sCrLf;
		echo '/* ]]> */';
		echo $sCrLf;
		echo '<';
		echo '/script>';
		echo $sCrLf;


		if (true === $this->bDeferScriptGeneration)
		{

			$sHash = $this->generateHash();

			$sOutFile = $sHash.'.js';
			// $sOutPath = dirname(dirname(__FILE__)).'/xajax_js/deferred/';
			$sOutPath = $this->sJsDir . '/' . $this->sDeferDir . '/';

			if (!is_file($sOutPath.$sOutFile) )
			{
				ob_start();


				// $sInPath = dirname(dirname(__FILE__)).'/xajax_js/';
				$sInPath = $this->sJsDir . '/';

				foreach ($aJsFiles as $aJsFile) {

					print file_get_contents($sInPath.$aJsFile[0]);
				}
				print $sCrLf;

				print $this->printPluginScripts();

				$sScriptCode = stripslashes(ob_get_clean());

				require_once(dirname(__FILE__) . '/xajaxCompress.inc.php');
				$sScriptCode = xajaxCompressFile( $sScriptCode );

				file_put_contents($sOutPath.$sOutFile,$sScriptCode);
			}



			echo '<';
			echo 'script type="text/javascript" src="';
			echo $sJsURI;
			echo $this->sDeferDir.'/';
			echo $sOutFile;
			echo '" ';
			echo $this->sDefer;
			echo 'charset="UTF-8"><';
			echo '/script>';
			echo $sCrLf;



		} else {


			foreach ($aJsFiles as $aJsFile) {
				echo '<';
				echo 'script type="text/javascript" src="';
				echo $sJsURI;
				echo $aJsFile[0];
				echo '" ';
				echo $this->sDefer;
				echo 'charset="UTF-8"><';
				echo '/script>';
				echo $sCrLf;
			}

			echo $sCrLf;
			echo '<';
			echo 'script type="text/javascript" ';
			echo $this->sDefer;
			echo 'charset="UTF-8">';
			echo $sCrLf;
			echo '/* <';
			echo '![CDATA[ */';
			echo $sCrLf;

			$this->printPluginScripts();

			echo $sCrLf;
			echo '/* ]]> */';
			echo $sCrLf;
			echo '<';
			echo '/script>';
			echo $sCrLf;
		}
	}

	private function generateHash()
	{
		$aKeys = array_keys($this->aClientScriptGenerators);
		sort($aKeys);
		$sHash = '';
		foreach ($aKeys as $sKey)
		{
			$sHash .= $this->aClientScriptGenerators[$sKey]->generateHash();
		}
		return md5($sHash);
	}

	private function printPluginScripts()
	{
		$aKeys = array_keys($this->aClientScriptGenerators);
		sort($aKeys);
		foreach ($aKeys as $sKey)
		{
			$this->aClientScriptGenerators[$sKey]->generateClientScript();
		}
	}

	/*
		Function: getResponsePlugin
		
		Locate the specified response plugin by name and return
		a reference to it if one exists.
		
		Parameters:
			$sName - (string): Name of the plugin.
			
		Returns:
			mixed : Returns plugin or false if not found.
	*/
	public function getResponsePlugin($sName)
	{
		// Since the defined name of a response plugin is used as key in the plugin table,
		// the plugin instance in retrieved using the given name as key.
		/*
		$aKeys = array_keys($this->aResponsePlugins);
		sort($aKeys);
		foreach ($aKeys as $sKey)
			if ( $this->aResponsePlugins[$sKey] instanceof  $sName )
				return $this->aResponsePlugins[$sKey];
		$bFailure = false;
		return $bFailure;
		*/
		if(array_key_exists($sName, $this->aResponsePlugins))
			return $this->aResponsePlugins[$sName];
		return false;
	}

	/*
		Function: getRequestPlugin
		
		Locate the specified response plugin by name and return
		a reference to it if one exists.
		
		Parameters:
			$sName - (string): Name of the plugin.
			
		Returns:
			mixed : Returns plugin or false if not found.
	*/
	public function getRequestPlugin($sName)
	{
		$aKeys = array_keys($this->aRequestPlugins);
		sort($aKeys);
		foreach ($aKeys as $sKey) {
			if ( get_class($this->aRequestPlugins[$sKey]) ==  $sName ) {
				return $this->aRequestPlugins[$sKey];
			} 
		}	


		$bFailure = false;
		return $bFailure;
	}
}
