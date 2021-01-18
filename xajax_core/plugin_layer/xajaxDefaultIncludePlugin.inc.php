<?php
/*
	File: xajaxDefaultIncludePlugin.inc.php

	Contains the default script include plugin class.

	Title: xajax default script include plugin class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxDefaultIncludePlugin.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxIncludeClientScript

	Generates the SCRIPT tags necessary to 'include' the xajax javascript
	library on the browser.

	This is called when the page is first loaded.
*/
final class xajaxIncludeClientScriptPlugin extends xajaxRequestPlugin
{


	public function __construct()
	{

	}

	/*
		Function: configure
	*/
	public function configure($sName, $mValue)
	{


	}

	/*
		Function: generateClientScript
	*/
	public function generateClientScript()
	{
		if (false === $this->bDeferScriptGeneration)
		{
			$this->printJavascriptConfig();
			$this->printJavascriptInclude();
		}
		else if (true === $this->bDeferScriptGeneration)
		{
			$this->printJavascriptInclude();
		}
		else if ('deferred' == $this->bDeferScriptGeneration)
		{
			$this->printJavascriptConfig();
		}
	}

	/*
		Function: getJavascriptConfig

		Generates the xajax settings that will be used by the xajax javascript
		library when making requests back to the server.

		Returns:

		string - The javascript code necessary to configure the settings on
			the browser.
	*/
	public function getJavascriptConfig()
	{
		ob_start();
		$this->printJavascriptConfig();
		return ob_get_clean();
	}
	
	/*
		Function: printJavascriptConfig
		
		See <xajaxIncludeClientScriptPlugin::getJavascriptConfig>
	*/
	public function printJavascriptConfig()
	{


	}

	/*
		Function: getJavascriptInclude

		Generates SCRIPT tags necessary to load the javascript libraries on
		the browser.

		sJsURI - (string):  The relative or fully qualified PATH that will be
			used to compose the URI to the specified javascript files.
		aJsFiles - (array):  List of javascript files to include.

		Returns:

		string - The SCRIPT tags that will cause the browser to load the
			specified files.
	*/
	public function getJavascriptInclude()
	{
		ob_start();
		$this->printJavascriptInclude();
		return ob_get_clean();
	}
	
	/*
		Function: printJavascriptInclude
		
		See <xajaxIncludeClientScriptPlugin::getJavascriptInclude>
	*/
	public function printJavascriptInclude()
	{

			

	}
	

}

/*
	Register the xajaxIncludeClientScriptPlugin object with the xajaxPluginManager.
*/
#$objPluginManager = xajaxPluginManager::getInstance();
#$objPluginManager->registerPlugin(new xajaxIncludeClientScriptPlugin(), 99);
