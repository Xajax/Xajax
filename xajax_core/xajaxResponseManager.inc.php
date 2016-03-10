<?php
/*
	File: xajaxResponseManager.inc.php

	Contains the xajaxResponseManager class

	Title: xajaxResponseManager class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxResponseManager.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxResponseManager

	This class stores and tracks the response that will be returned after
	processing a request.  The response manager represents a single point
	of contact for working with <xajaxResponse> objects as well as 
	<xajaxCustomResponse> objects.
*/
final class xajaxResponseManager
{
	/*
		Object: objResponse
	
		The current response object that will be sent back to the browser
		once the request processing phase is complete.
	*/
	private $objResponse;
	
	/*
		String: sCharacterEncoding
	*/
	private $sCharacterEncoding;
	
	/*
		Boolean: bOutputEntities
	*/
	private $bOutputEntities;
	
	/*
		Array: aDebugMessages
	*/
	private $aDebugMessages;
	
	/*
		Function: xajaxResponseManager
		
		Construct and initialize the one and only xajaxResponseManager object.
	*/
	private function __construct()
	{
		$this->objResponse = NULL;
		$this->aDebugMessages = array();
	}
	
	/*
		Function: getInstance
		
		Implementation of the singleton pattern: provide a single instance of the <xajaxResponseManager>
		to all who request it.
	*/
	public static function getInstance()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxResponseManager();
		}
		return $obj;
	}
	
	/*
		Function: configure
		
		Called by the xajax object when configuration options are set in the main script.  Option
		values are passed to each of the main xajax components and stored locally as needed.  The
		<xajaxResponseManager> will track the characterEncoding and outputEntities settings.
		
		Parameters:
		$sName - (string): Setting name
		$mValue - (mixed): Value
	*/
	public function configure($sName, $mValue)
	{
		if ('characterEncoding' == $sName)
		{
			$this->sCharacterEncoding = $mValue;
			
			if (isset($this->objResponse))
				$this->objResponse->setCharacterEncoding($this->sCharacterEncoding);
		}
		else if ('contentType' == $sName)
		{
			if (isset($this->objResponse))
				$this->objResponse->setContentType($mValue);
		}
		else if ('outputEntities' == $sName)
		{
			if (true === $mValue || false === $mValue)
			{
				$this->bOutputEntities = $mValue;
				
				if (isset($this->objResponse))
					$this->objResponse->setOutputEntities($this->bOutputEntities);
			}
		}
		$this->aSettings[$sName] = $mValue;
	
	}



	/*
		Function: getConfiguration
		
		Get the current value of a configuration setting that was previously set
		via <xajax->configure> or <xajax->configureMany>

		Parameters:
		
		$sName - (string): The name of the configuration setting
				
		Returns:
		
		$mValue : (mixed):  The value of the setting if set, null otherwise.
	*/
	
	public function getConfiguration($sName)
	{
		if (isset($this->aSettings[$sName]))
			return $this->aSettings[$sName];
		return NULL;
	}
	
	/*
		Function: clear
		
		Clear the current response.  A new response will need to be appended
		before the request processing is complete.
	*/
	public function clear()
	{
		$this->objResponse = NULL;
	}
	
	/*
		Function: append
		
		Used, primarily internally, to append one response object onto the end of another.  You can
		append one xajaxResponse to the end of another, or append a xajaxCustomResponse onto the end of 
		another xajaxCustomResponse.  However, you cannot append a standard response object onto the end
		of a custom response and likewise, you cannot append a custom response onto the end of a standard
		response.
		
		Parameters:
		
		$mResponse - (object):  The new response object to be added to the current response object.
		
		If no prior response has been appended, this response becomes the main response object to which other
		response objects will be appended.
	*/
	public function append($mResponse)
	{
		if ( $mResponse instanceof xajaxResponse ) {
			if (NULL == $this->objResponse) {
				$this->objResponse = $mResponse;
			} else if ( $this->objResponse instanceof xajaxResponse ) {
				if ($this->objResponse != $mResponse)
					$this->objResponse->appendResponse($mResponse);
			} else {
				$objLanguageManager = xajaxLanguageManager::getInstance();
				$this->debug(
					$objLanguageManager->getText('XJXRM:MXRTERR') 
					. get_class($this->objResponse) 
					. ')'
					);
			}
		} else if ( $mResponse instanceof xajaxCustomResponse ) {
			if (NULL == $this->objResponse) {
				$this->objResponse = $mResponse;
			} else if ( $this->objResponse instanceof xajaxCustomResponse ) {
				if ($this->objResponse != $mResponse)
					$this->objResponse->appendResponse($mResponse);
			} else {
				$objLanguageManager = xajaxLanguageManager::getInstance();
				$this->debug(
					$objLanguageManager->getText('XJXRM:MXRTERR') 
					. get_class($this->objResponse) 
					. ')'
					);
			}
		} else {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			$this->debug($objLanguageManager->getText('XJXRM:IRERR'));
		}
	}
	
	/*
		Function: debug
		
		Appends a debug message on the end of the debug message queue.  Debug messages
		will be sent to the client with the normal response (if the response object supports
		the sending of debug messages, see: <xajaxResponse>)
		
		Parameters:
		
		$sMessage - (string):  The text of the debug message to be sent.
	*/
	public function debug($sMessage)
	{
		$this->aDebugMessages[] = $sMessage;
	}
	
	/*
		Function: send
		
		Prints the response object to the output stream, thus sending the response to the client.
	*/
	public function send()
	{
		if (NULL != $this->objResponse) {
			foreach ($this->aDebugMessages as $sMessage)
				$this->objResponse->debug($sMessage);
			$this->aDebugMessages = array();
			$this->objResponse->printOutput();
		}
	}
	
	/*
		Function: getCharacterEncoding
		
		Called automatically by new response objects as they are constructed to obtain the
		current character encoding setting.  As the character encoding is changed, the <xajaxResponseManager>
		will automatically notify the current response object since it would have been constructed
		prior to the setting change, see <xajaxResponseManager::configure>.
	*/
	public function getCharacterEncoding()
	{
		return $this->sCharacterEncoding;
	}
	
	/*
		Function: getOutputEntities
		
		Called automatically by new response objects as they are constructed to obtain the
		current output entities setting.  As the output entities setting is changed, the
		<xajaxResponseManager> will automatically notify the current response object since it would
		have been constructed prior to the setting change, see <xajaxResponseManager::configure>.
	*/
	public function getOutputEntities()
	{
		return $this->bOutputEntities;
	}
}