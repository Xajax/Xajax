<?php
/*
	File: xajaxResponse.inc.php

	Contains the response class.
	
	Title: xajax response class
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxResponse.inc.php 361 2007-05-24 12:48:14Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxResponse
	
	Collect commands to be sent back to the browser in response to a xajax
	request.  Commands are encoded and packaged in a format that is acceptable
	to the response handler from the javascript library running on the client
	side.
	
	Common commands include:
		- <xajaxResponse->assign>: Assign a value to an elements property.
		- <xajaxResponse->append>: Append a value on to an elements property.
		- <xajaxResponse->script>: Execute a portion of javascript code.
		- <xajaxResponse->call>: Execute an existing javascript function.
		- <xajaxResponse->alert>: Display an alert dialog to the user.
		
	Elements are identified by the value of the HTML id attribute.  If you do 
	not see your updates occuring on the browser side, ensure that you are 
	using the correct id in your response.
*/
class xajaxResponse
{
	/*
		Array: aCommands
		
		Stores the commands that will be sent to the browser in the response.
	*/
	public  $aCommands;
	
	/*
		String: sCharacterEncoding
		
		The name of the encoding method you wish to use when dealing with 
		special characters.  See <xajax->setEncoding> for more information.
	*/
	private $sCharacterEncoding;
	
	/*
		Boolean: bOutputEntities
		
		Convert special characters to the HTML equivellent.  See also
		<xajax->bOutputEntities> and <xajax->configure>.
	*/
	private $bOutputEntities;
	
	/*
		Mixed: returnValue
		
		A string, array or integer value to be returned to the caller when
		using 'synchronous' mode requests.  See <xajax->setMode> for details.
	*/
	private $returnValue;
	
	/*
		Object: objPluginManager
		
		A reference to the global plugin manager.
	*/
	private $objPluginManager;
	
	// sorry but this config is static atm
	private $sContentType = 'application/json'; //'text/xml';
	
	/*
		Constructor: __construct
		
		Create and initialize a xajaxResponse object.
	*/
	public function __construct()
	{
		//SkipDebug
		if (0 < func_num_args()) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
					$objLanguageManager->getText('XJXRSP:EDERR:01')
					, E_USER_ERROR
					);
		}
		//EndSkipDebug
		
		$this->aCommands = array();
		
		$objResponseManager = xajaxResponseManager::getInstance();
		
		$this->sCharacterEncoding = $objResponseManager->getCharacterEncoding();
		$this->bOutputEntities = $objResponseManager->getOutputEntities();
		$this->setResponseType($objResponseManager->getConfiguration('responseType'));
		
		$this->objPluginManager = xajaxPluginManager::getInstance();
	}
	
	public function getResponseType()
	{
		return $this->sResponseType;
	}

	public function setResponseType($sResponseType) {
		
		if (NULL == $sResponseType) return;
		
		$this->sResponseType = $sResponseType ;
		
		if ('XML' == $sResponseType) {
			$this->setContentType('text/xml');
		}
		elseif ('JSON' == $sResponseType) {
			$this->setContentType('application/json');
		}
	}
	
	
	/*
		Function: setCharacterEncoding
		
		Overrides the default character encoding (or the one specified in the
		constructor) to the specified character encoding.
		
		Parameters:
		
		sCharacterEncoding - (string):  The encoding method to use for this response.
		
		See also, <xajaxResponse->xajaxResponse>()
		
		Returns:
		
		object - The xajaxResponse object.
	*/
	public function setCharacterEncoding($sCharacterEncoding)
	{
		$this->sCharacterEncoding = $sCharacterEncoding;
		return $this;
	}

	public function getCharacterEncoding()
	{
		return $this->sCharacterEncoding;
	}
	

	/*
		Function: setOutputEntities
		
		Convert special characters to their HTML equivellent automatically
		(only works if the mb_string extension is available).
		
		Parameters:
		
		bOption - (boolean):  Convert special characters
		
		Returns:
		
		object - The xajaxResponse object.
	*/
	public function setOutputEntities($bOutputEntities)
	{
		$this->bOutputEntities = (boolean)$bOutputEntities;
		return $this;
	}
	
	/*
		Function: plugin
		
		Provides access to registered response plugins.  If you are using PHP
		4 or 5, pass the plugin name as the first argument, the plugin method
		name as the second argument and subsequent arguments (if any) to be 
		passed along to the plugin.
		
		Optionally, if you use PHP 5, you can pass just the plugin name as the
		first argument and the plugin object will be returned.  You can then
		access the methods of the plugin directly.
		
		Parameters:
		
		sName - (string):  Name of the plugin.
		sFunction - (string, optional):  The name of the method to call.
		arg1...argn - (mixed, optional):  Additional arguments to pass on to
			the plugin function.
			
		Returns:
		
		object - The plugin specified by sName.
	*/
	public function plugin()
	{
		$aArgs = func_get_args();
		$nArgs = func_num_args();
		
		//SkipDebug
		if (false == (0 < $nArgs)) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
					$objLanguageManager->getText('XJXRSP:MPERR:01')
					, E_USER_ERROR
					);
		}
		//EndSkipDebug
		
		$sName = array_shift($aArgs);
		
		$objPlugin = $this->objPluginManager->getResponsePlugin($sName);
		
		if (false === $objPlugin)
		{
			$bReturn = false;
			return $bReturn;
		}
		
		$objPlugin->setResponse($this);
		
		if (0 < count($aArgs))
		{
			$sMethod = array_shift($aArgs);
			
			$aFunction = array($objPlugin, $sMethod);
			call_user_func_array($aFunction, $aArgs);
		}
		
		return $objPlugin;
	}
	
	/*
		Function: __get
		
		Magic function for PHP 5.  Used to permit plugins to be called as if they
		where native members of the xajaxResponse instance.
		
		Parameters:
		
		sPluginName - (string):  The name of the plugin.
		
		Returns:
		
		object - The plugin specified by sPluginName.
	*/
	public function __get($sPluginName)
	{
		$objPlugin = $this->plugin($sPluginName);
		return $objPlugin;
	}
	
	/*
		Function: confirmCommands
		
		Response command that prompts user with [ok] [cancel] style
		message box.  If the user clicks cancel, the specified 
		number of response commands following this one, will be
		skipped.
		
		Parameters:
		
		iCmdNumber - (integer):  The number of commands to skip upon cancel.
		sMessage - (string):  The message to display to the user.
		
		Returns:
		
		object : The xajaxResponse object.
	*/
	public function confirmCommands($iCmdNumber, $sMessage)
	{
		return $this->addCommand(
			array(
					'cmd'=>'cc',
					'id'=>$iCmdNumber
					),
			(string)$sMessage
			);
	}
	
	/*
		Function: assign
		
		Response command indicating that the specified value should be 
		assigned to the given element's attribute.
		
		Parameters:
		
		sTarget - (string):  The id of the html element on the browser.
		sAttribute - (string):  The property to be assigned.
		sData - (string):  The value to be assigned to the property.
		
		Returns:
		
		object : The <xajaxResponse> object.
		
	*/
	public function assign($sTarget,$sAttribute,$sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'as',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sAttribute
					),
			(string)$sData
			);
	}
	
	/*
		Function: append
		
		Response command that indicates the specified data should be appended
		to the given element's property.
		
		Parameters:
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The name of the property to be appended to.
		sData - (string):  The data to be appended to the property.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function append($sTarget,$sAttribute,$sData)
	{	
		return $this->addCommand(
			array(
					'cmd'=>'ap',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sAttribute
					),
			(string)$sData
			);
	}
	
	/*
		Function: prepend
		
		Response command to prepend the specified value onto the given
		element's property.
		
		Parameters:
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The property to be updated.
		sData - (string):  The value to be prepended.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function prepend($sTarget,$sAttribute,$sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'pp',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sAttribute
					),
			(string)$sData
			);
	}
	
	/*
		Function: replace
		
		Replace a specified value with another value within the given
		element's property.
		
		Parameters:
		
		sTarget - (string):  The id of the element to update.
		sAttribute - (string):  The property to be updated.
		sSearch - (string):  The needle to search for.
		sData - (string):  The data to use in place of the needle.
	*/
	public function replace($sTarget,$sAttribute,$sSearch,$sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'rp',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sAttribute
					),
			array(
					's' => (string)$sSearch,
					'r' => (string)$sData
					)
			);
	}
	
	/*
		Function: clear
		
		Response command used to clear the specified property of the 
		given element.
		
		Parameters:
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The property to be clared.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	public function clear($sTarget,$sAttribute)
	{
		return $this->assign(
			(string)$sTarget,
			(string)$sAttribute,
			''
			);
	}
	
	/*
		Function: contextAssign
		
		Response command used to assign a value to a member of a
		javascript object (or element) that is specified by the context
		member of the request.  The object is referenced using the 'this' keyword
		in the sAttribute parameter.
		
		Parameters:
		
		sAttribute - (string):  The property to be updated.
		sData - (string):  The value to assign.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function contextAssign($sAttribute, $sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'c:as', 
					'prop'=>(string)$sAttribute
					), 
			(string)$sData
			);
	}
	
	/*
		Function: contextAppend
		
		Response command used to append a value onto the specified member
		of the javascript context object (or element) specified by the context
		member of the request.  The object is referenced using the 'this' keyword
		in the sAttribute parameter.
		
		Parameters:
		
		sAttribute - (string):  The member to be appended to.
		sData - (string):  The value to append.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function contextAppend($sAttribute, $sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'c:ap', 
					'prop'=>(string)$sAttribute
					), 
			(string)$sData
			);
	}	
	
	/*
		Function: contextPrepend
		
		Response command used to prepend the speicified data to the given
		member of the current javascript object specified by context in the
		current request.  The object is access via the 'this' keyword in the
		sAttribute parameter.
		
		Parameters:
		
		sAttribute - (string):  The member to be updated.
		sData - (string):  The value to be prepended.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function contextPrepend($sAttribute, $sData)
	{
		return $this->addCommand(
			array(
					'cmd'=>'c:pp', 
					'prop'=>(string)$sAttribute
					), 
			(string)$sData
			);
	}
	
	/*
		Function: contextClear
		
		Response command used to clear the value of the property specified
		in the sAttribute parameter.  The member is access via the 'this'
		keyword and can be used to update a javascript object specified
		by context in the request parameters.
		
		Parameters:
		
		sAttribute - (string):  The member to be cleared.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function contextClear($sAttribute)
	{
		return $this->contextAssign(
			(string)$sAttribute, 
			''
			);
	}
	
	/*
		Function: alert
		
		Response command that is used to display an alert message to the user.
		
		Parameters:
		
		sMsg - (string):  The message to be displayed.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function alert($sMsg)
	{
		return $this->addCommand(
			array(
					'cmd'=>'al'
					),
			(string)$sMsg
			);
	}
	
	public function debug($sMessage)
	{
		return $this->addCommand(
			array(
					'cmd'=>'dbg'
					),
			(string)$sMessage
			);
	}
	
	/*
		Function: redirect
		
		Response command that causes the browser to navigate to the specified
		URL.
		
		Parameters:
		
		sURL - (string):  The relative or fully qualified URL.
		iDelay - (integer, optional):  Number of seconds to delay before
			the redirect occurs.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function redirect($sURL, $iDelay=0)
	{
		//we need to parse the query part so that the values are rawurlencode()'ed
		//can't just use parse_url() cos we could be dealing with a relative URL which
		//  parse_url() can't deal with.
		$queryStart = strpos($sURL, '?', strrpos($sURL, '/'));
		if ($queryStart !== FALSE)
		{
			$queryStart++;
			$queryEnd = strpos($sURL, '#', $queryStart);
			if ($queryEnd === FALSE)
				$queryEnd = strlen($sURL);
			$queryPart = substr($sURL, $queryStart, $queryEnd-$queryStart);
			parse_str($queryPart, $queryParts);
			$newQueryPart = "";
			if ($queryParts)
			{
				$first = true;
				foreach($queryParts as $key => $value)
				{
					if ($first)
						$first = false;
					else
						$newQueryPart .= '&';
					$newQueryPart .= rawurlencode($key).'='.rawurlencode($value);
				}
			} else if ($_SERVER['QUERY_STRING']) {
					//couldn't break up the query, but there's one there
					//possibly "http://url/page.html?query1234" type of query?
					//just encode it and hope it works
					$newQueryPart = rawurlencode($_SERVER['QUERY_STRING']);
				}
			$sURL = str_replace($queryPart, $newQueryPart, $sURL);
		}
		if ($iDelay)
			$this->script(
					'window.setTimeout("window.location = \''
					. $sURL
					. '\';",'
					. ($iDelay*1000)
					. ');'
					);
		else
			$this->script(
					'window.location = "'
					. $sURL
					. '";'
					);
		return $this;
	}
	
	/*
		Function: script
		
		Response command that is used to execute a portion of javascript on
		the browser.  The script runs in it's own context, so variables declared
		locally, using the 'var' keyword, will no longer be available after the
		call.  To construct a variable that will be accessable globally, even
		after the script has executed, leave off the 'var' keyword.
		
		Parameters:
		
		sJS - (string):  The script to execute.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function script($sJS)
	{
		return $this->addCommand(
			array(
					'cmd'=>'js'
					),
			(string)$sJS
			);
	}
	
	/*
		Function: call
		
		Response command that indicates that the specified javascript
		function should be called with the given (optional) parameters.
		
		Parameters:
		
		arg1 - (string):  The name of the function to call.
		arg2 .. argn : arguments to be passed to the function.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function call() {
		$aArgs = func_get_args();
		$sFunc = array_shift($aArgs);
		return $this->addCommand(
			array(
					'cmd'=>'jc',
					'func'=>$sFunc
					), 
			$aArgs
			);
	}
	
	/*
		Function: remove
		
		Response command used to remove an element from the document.
		
		Parameters:
		
		sTarget - (string):  The id of the element to be removed.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function remove($sTarget)
	{
		return $this->addCommand(
			array(
					'cmd'=>'rm',
					'id'=>(string)$sTarget),
			''
			);
	}
	
	/*
		Function: create
		
		Response command used to create a new element on the browser.
		
		Parameters:
		
		sParent - (string):  The id of the parent element.
		sTag - (string):  The tag name to be used for the new element.
		sId - (string):  The id to assign to the new element.
 
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	
	public function create($sParent, $sTag, $sId)
	{
 
		
		return $this->addCommand(
			array(
					'cmd'=>'ce',
					'id'=>(string)$sParent,
					'prop'=>(string)$sId
					),
			(string)$sTag
			);
	}
	
	/*
		Function: insert
		
		Response command used to insert a new element just prior to the specified
		element.
		
		Parameters:
		
		sBefore - (string):  The element used as a reference point for the 
			insertion.
		sTag - (string):  The tag to be used for the new element.
		sId - (string):  The id to be used for the new element.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function insert($sBefore, $sTag, $sId)
	{
		return $this->addCommand(
			array(
					'cmd'=>'ie',
					'id'=>(string)$sBefore,
					'prop'=>(string)$sId
					),
			(string)$sTag
			);
	}
	
	/*
		Function: insertAfter
		
		Response command used to insert a new element after the specified
		one.
		
		Parameters:
		
		sAfter - (string):  The id of the element that will be used as a reference
			for the insertion.
		sTag - (string):  The tag name to be used for the new element.
		sId - (string):  The id to be used for the new element.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function insertAfter($sAfter, $sTag, $sId)
	{
		return $this->addCommand(
			array(
					'cmd'=>'ia',
					'id'=>(string)$sAfter,
					'prop'=>(string)$sId
					),
			(string)$sTag
			);
	}
	
	/*
		Function: createInput
		
		Response command used to create an input element on the browser.
		
		Parameters:
		
		sParent - (string):  The id of the parent element.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new element.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function createInput($sParent, $sType, $sName, $sId)
	{
		return $this->addCommand(
			array(
					'cmd'=>'ci',
					'id'=>(string)$sParent,
					'prop'=>(string)$sId,
					'type'=>(string)$sType
					),
			(string)$sName
			);
	}
	
	/*
		Function: insertInput
		
		Response command used to insert a new input element preceeding the
		specified element.
		
		Parameters:
		
		sBefore - (string):  The id of the element to be used as the reference
			point for the insertion.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new input element.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function insertInput($sBefore, $sType, $sName, $sId)
	{
		return $this->addCommand(
			array(
				'cmd'=>'ii',
				'id'=>(string)$sBefore,
				'prop'=>(string)$sId,
				'type'=>(string)$sType
				),
			(string)$sName
			);
	}
	
	/*
		Function: insertInputAfter
		
		Response command used to insert a new input element after the 
		specified element.
		
		Parameters:
		
		sAfter - (string):  The id of the element that is to be used
			as the insertion point for the new element.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new input element.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function insertInputAfter($sAfter, $sType, $sName, $sId)
	{
		return $this->addCommand(
			array(
				'cmd'=>'iia',
				'id'=>(string)$sAfter,
				'prop'=>(string)$sId,
				'type'=>(string)$sType
				),
			(string)$sName
			);
	}
	
	/*
		Function: setEvent
		
		Response command used to set an event handler on the browser.
		
		Parameters:
		
		sTarget - (string):  The id of the element that contains the event.
		sEvent - (string):  The name of the event.
		sScript - (string):  The javascript to execute when the event is fired.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function setEvent($sTarget,$sEvent,$sScript)
	{
		return $this->addCommand(
			array(
				'cmd'=>'ev',
				'id'=>(string)$sTarget,
				'prop'=>(string)$sEvent
				),
			(string)$sScript
			);
	}
	

	/*
		Function: addEvent
		
		Response command used to set an event handler on the browser.
		
		Parameters:
		
		sTarget - (string):  The id of the element that contains the event.
		sEvent - (string):  The name of the event.
		sScript - (string):  The javascript to execute when the event is fired.
		
		Returns:
		
		object : The <xajaxResponse> object.
		
		Note:
		
		This function is depreciated and will be removed in a future version. 
		Use <setEvent> instead.
	*/	
	public function addEvent($sTarget,$sEvent,$sScript)
	{
		return $this->setEvent(
			(string)$sTarget,
			(string)$sEvent,
			(string)$sScript
			);
	}
	
	/*
		Function: addHandler
		
		Response command used to install an event handler on the specified element.
		
		Parameters:
		
		sTarget - (string):  The id of the element.
		sEvent - (string):  The name of the event to add the handler to.
		sHandler - (string):  The javascript function to call when the event is fired.
		
		You can add more than one event handler to an element's event using this method.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	public function addHandler($sTarget,$sEvent,$sHandler)
	{
		return $this->addCommand(
			array(
					'cmd'=>'ah',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sEvent
					),
			(string)$sHandler
			);
	}
	
	/*
		Function: removeHandler
		
		Response command used to remove an event handler from an element.
		
		Parameters:
		
		sTarget - (string):  The id of the element.
		sEvent - (string):  The name of the event.
		sHandler - (string):  The javascript function that is called when the 
			event is fired.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function removeHandler($sTarget,$sEvent,$sHandler)
	{
		return $this->addCommand(
			array(
					'cmd'=>'rh',
					'id'=>(string)$sTarget,
					'prop'=>(string)$sEvent
					),
			(string)$sHandler);
	}
	
	/*
		Function: setFunction
		
		Response command used to construct a javascript function on the browser.
		
		Parameters:
		
		sFunction - (string):  The name of the function to construct.
		sArgs - (string):  Comma separated list of parameter names.
		sScript - (string):  The javascript code that will become the body of the
			function.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function setFunction($sFunction, $sArgs, $sScript)
	{
		return $this->addCommand(
			array(
					'cmd'=>'sf',
					'func'=>(string)$sFunction,
					'prop'=>(string)$sArgs
					),
			(string)$sScript
			);
	}
	
	/*
		Function: wrapFunction
		
		Response command used to construct a wrapper function around
		and existing javascript function on the browser.
		
		Parameters:
		
		sFunction - (string):  The name of the existing function to wrap.
		sArgs - (string):  The comma separated list of parameters for the function.
		aScripts - (array):  An array of javascript code snippets that will
			be used to build the body of the function.  The first piece of code
			specified in the array will occur before the call to the original
			function, the second will occur after the original function is called.
		sReturnValueVariable - (string):  The name of the variable that will
			retain the return value from the call to the original function.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function wrapFunction($sFunction, $sArgs, $aScripts, $sReturnValueVariable)
	{
		return $this->addCommand(
			array(
					'cmd'=>'wpf',
					'func'=>(string)$sFunction,
					'prop'=>(string)$sArgs,
					'type'=>(string)$sReturnValueVariable
					),
			$aScripts
			);
	}
	
	/*
		Function: includeScript
		
		Response command used to load a javascript file on the browser.
		
		Parameters:
		
		sFileName - (string):  The relative or fully qualified URI of the 
			javascript file.
	
		sType - (string): Determines the script type . Defaults to 'text/javascript'. 

			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function includeScript($sFileName, $sType = null, $sId = null)
	{
		$command = array('cmd' => 'in');
		
		if (false === (null === $sType))
			$command['type'] = (string)$sType;
		
		if (false === (null === $sId))
			$command['elm_id'] = (string)$sId;

		return $this->addCommand(
			$command,
			(string)$sFileName
			);
	}
	
	/*
		Function: includeScriptOnce
		
		Response command used to include a javascript file on the browser
		if it has not already been loaded.
		
		Parameters:
		
		sFileName - (string):  The relative for fully qualified URI of the
			javascript file.

		sType - (string): Determines the script type . Defaults to 'text/javascript'. 
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function includeScriptOnce($sFileName, $sType = null, $sId = null)
	{
		$command = array('cmd' => 'ino');
		
		if (false === (null === $sType))
			$command['type'] = (string)$sType;
		
		if (false === (null === $sId))
			$command['elm_id'] = (string)$sId;
			
		return $this->addCommand(
			$command,
			(string)$sFileName
			);
	}
	
	/*
		Function: removeScript
		
		Response command used to remove a SCRIPT reference to a javascript
		file on the browser.  Optionally, you can call a javascript function
		just prior to the file being unloaded (for cleanup).
		
		Parameters:
		
		sFileName - (string):  The relative or fully qualified URI of the
			javascript file.
		sUnload - (string):  Name of a javascript function to call prior
			to unlaoding the file.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function removeScript($sFileName, $sUnload = '') {
		$this->addCommand(
				array(
					'cmd'=>'rjs',
					'unld'=>(string)$sUnload
					),
				(string)$sFileName
				);
		return $this;
	}
	
	/*
		Function: includeCSS
		
		Response command used to include a LINK reference to 
		the specified CSS file on the browser.  This will cause the
		browser to load and apply the style sheet.
		
		Parameters:
		
		sFileName - (string):  The relative or fully qualified URI of
			the css file.
	
		sMedia - (string): Determines the media type of the CSS file. Defaults to 'screen'. 
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function includeCSS($sFileName, $sMedia = null)
	{
		$command = array('cmd' => 'css');
		
		if (false === (null === $sMedia))
			$command['media'] = (string)$sMedia;
		
		return $this->addCommand(
			$command,
			(string)$sFileName
			);
	}
	
	/*
		Function: removeCSS
		
		Response command used to remove a LINK reference to 
		a CSS file on the browser.  This causes the browser to
		unload the style sheet, effectively removing the style
		changes it caused.
		
		Parameters:
		
		sFileName - (string):  The relative or fully qualified URI
			of the css file.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function removeCSS($sFileName, $sMedia = null)
	{
		$command = array('cmd'=>'rcss');
		
		if (false === (null === $sMedia))
			$command['media'] = (string)$sMedia;
		
		return $this->addCommand(
			$command,
			(string)$sFileName
			);
	}
	
	/*
		Function: waitForCSS
		
		Response command instructing xajax to pause while the CSS
		files are loaded.  The browser is not typically a multi-threading
		application, with regards to javascript code.  Therefore, the
		CSS files included or removed with <xajaxResponse->includeCSS> and 
		<xajaxResponse->removeCSS> respectively, will not be loaded or 
		removed until the browser regains control from the script.  This
		command returns control back to the browser and pauses the execution
		of the response until the CSS files, included previously, are
		loaded.
		
		Parameters:
		
		iTimeout - (integer):  The number of 1/10ths of a second to pause
			before timing out and continuing with the execution of the
			response commands.
			
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function waitForCSS($iTimeout = 600) {
		$sData = "";
		$this->addCommand(
				array(
					'cmd'=>'wcss', 
					'prop'=>$iTimeout
					),
				$sData
				);
		return $this;
	}
	
	/*
		Function: waitFor
		
		Response command instructing xajax to delay execution of the response
		commands until a specified condition is met.  Note, this returns control
		to the browser, so that other script operations can execute.  xajax
		will continue to monitor the specified condition and, when it evaulates
		to true, will continue processing response commands.
		
		Parameters:
		
		script - (string):  A piece of javascript code that evaulates to true 
			or false.
		tenths - (integer):  The number of 1/10ths of a second to wait before
			timing out and continuing with the execution of the response
			commands.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function waitFor($script, $tenths) {
		return $this->addCommand(
			array(
					'cmd'=>'wf',
					'prop'=>$tenths
					), 
			(string)$script
			);
	}
	
	/*
		Function: sleep
		
		Response command which instructs xajax to pause execution
		of the response commands, returning control to the browser
		so it can perform other commands asynchronously.  After
		the specified delay, xajax will continue execution of the 
		response commands.
		
		Parameters:
		
		tenths - (integer):  The number of 1/10ths of a second to
			sleep.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function sleep($tenths) {
		$this->addCommand(
				array(
					'cmd'=>'s',
					'prop'=>$tenths
					), 
				''
				);
		return $this;
	}
	
	public function domStartResponse()
	{
		$this->script('xjxElm = []');
	}
	
	public function domCreateElement($variable, $tag)
	{
		return $this->addCommand(
			array(
					'cmd' => 'DCE',
					'tgt' => $variable
					),
			$tag
			);
	}
	
	public function domSetAttribute($variable, $key, $value)
	{
		return $this->addCommand(
			array(
					'cmd' => 'DSA',
					'tgt' => $variable,
					'key' => $key
					),
			$value
			);
	}

	public function domRemoveChildren($parent, $skip=null, $remove=null)
	{
		$command = array(
			'cmd' => 'DRC'
			);

		if ($skip != null)
			$command['skip'] = $skip;

		if ($remove != null)
			$command['remove'] = $remove;

		return $this->addCommand(
			$command,
			$parent
			);
	}
	
	public function domAppendChild($parent, $variable)
	{
		return $this->addCommand(
			array(
				'cmd' => 'DAC',
				'par' => $parent
				),
			$variable
			);
	}

	public function domInsertBefore($target, $variable)
	{
		return $this->addCommand(
			array(
				'cmd' => 'DIB',
				'tgt' => $target
				),
			$variable
			);
	}

	public function domInsertAfter($target, $variable)
	{
		return $this->addCommand(
			array(
				'cmd' => 'DIA',
				'tgt' => $target
				),
			$variable
			);
	}
	
	public function domAppendText($parent, $text)
	{
		return $this->addCommand(
			array(
					'cmd' => 'DAT',
					'par' => $parent
					),
			$text
			);
	}
	
	public function domEndResponse()
	{
		$this->script('xjxElm = []');
	}
	
	/*
		Function: setReturnValue
		
		Stores a value that will be passed back as part of the response.
		When making synchronous requests, the calling javascript can
		obtain this value immediately as the return value of the
		<xajax.call> function.
		
		Parameters:
		
		value - (mixed):  Any value.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function setReturnValue($value) {
		

		$this->returnValue = 'JSON' == $this->sResponseType ? $value : $this->_encodeArray($value)  ;
		return $this;
	}
	
	/*
		Function: getContentType
		
		Returns the current content type that will be used for the
		response packet.  (typically: "text/xml")
		
		Returns:
		
		string : The content type.
	*/
	public function getContentType()
	{
		return $this->sContentType;
	}

	public function setContentType($sContentType)
	{
		$this->sContentType = $sContentType ;
	}
	
	/*
		Function: getOutput
	*/
	public function getOutput()
	{
		ob_start();
		if ('text/xml' == $this->getContentType() ) {
			$this->_printHeader_XML();
			$this->_printResponse_XML();
		} elseif ('application/json' == $this->getContentType() ) {
			$this->_printResponse_JSON();
			
		} else {
			//todo: trigger Error
		};
		return ob_get_clean();
	}
	
	/*
		Function: printOutput
		
		Prints the output, generated from the commands added to the response,
		that will be sent to the browser.
		
		Returns:
		
		string : The textual representation of the response commands.
	*/
	public function printOutput()
	{
		$this->_sendHeaders();
		if ('text/xml' == $this->getContentType() ) {
			$this->_printHeader_XML();
			$this->_printResponse_XML();
		} elseif ('application/json' == $this->getContentType() ) {
			$this->_printResponse_JSON();
			
		} else {
			//todo: trigger Error
		}
	}
	
	/*
		Function: _sendHeaders
		
		Used internally to generate the response headers.
	*/
	public function _sendHeaders()
	{
		$objArgumentManager = xajaxArgumentManager::getInstance();
		if (XAJAX_METHOD_GET == $objArgumentManager->getRequestMethod())
		{
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
		}
		
		$sCharacterSet = '';
		if ($this->sCharacterEncoding && 0 < strlen(trim($this->sCharacterEncoding))) {
			$sCharacterSet = '; charset="' . trim($this->sCharacterEncoding) . '"';
		}
		
		$sContentType = $this->getContentType();
		
		header('content-type: ' . $sContentType . ' ' . $sCharacterSet);
	}
	
	/*
		Function: getCommandCount
		
		Returns:
		
		integer : The number of commands in the response.
	*/
	public function getCommandCount()
	{
		return count($this->aCommands);
	}
	
	/*
		Function: appendResponse
		
		Merges the response commands from the specified <xajaxResponse>
		object with the response commands in this <xajaxResponse> object.
		
		Parameters:
		
		mCommands - (object):  <xajaxResponse> object.
		bBefore - (boolean):  Add the new commands to the beginning 
			of the list.
			
	*/
	public function appendResponse($mCommands, $bBefore=false)
	{
		if ( $mCommands instanceof xajaxResponse ) {
			$this->returnValue = $mCommands->returnValue;
			
			if ($bBefore) {
				$this->aCommands = array_merge($mCommands->aCommands, $this->aCommands);
			}
			else {
				$this->aCommands = array_merge($this->aCommands, $mCommands->aCommands);
			}
		}
		else if (is_array($mCommands)) {
				if ($bBefore) {
					$this->aCommands = array_merge($mCommands, $this->aCommands);
				}
				else {
					$this->aCommands = array_merge($this->aCommands, $mCommands);
				}
			}
			else {
				//SkipDebug
				if (!empty($mCommands)) {
					$objLanguageManager = xajaxLanguageManager::getInstance();
					trigger_error(
							$objLanguageManager->getText('XJXRSP:LCERR:01')
							, E_USER_ERROR
							);
				}
				//EndSkipDebug
			}
	}
	
	/*
		Function: addPluginCommand
		
		Adds a response command that is generated by a plugin.
		
		Parameters:
		
		objPlugin - (object):  A reference to a plugin object.
		aAttributes - (array):  Array containing the attributes for this
			response command.
		mData - (mixed):  The data to be sent with this command.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function addPluginCommand($objPlugin, $aAttributes, $mData)
	{
		$aAttributes['plg'] = $objPlugin->getName();
		return $this->addCommand($aAttributes, $mData);
	}
	
	/*
		Function: addCommand
		
		Add a response command to the array of commands that will
		be sent to the browser.
		
		Parameters:
		
		aAttributes - (array):  Associative array of attributes that
			will describe the command.
		mData - (mixed):  The data to be associated with this command.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function addCommand($aAttributes, $mData)
	{
		if ('text/xml' == $this->getContentType()) $mData = $this->_encodeArray($mData);;
		
		/* merge commands if possible */
		if ( in_array($aAttributes['cmd'],array('js','ap') ) )
		{
			if ($aLastCommand = array_pop($this->aCommands))
			{
				if ($aLastCommand['cmd'] == $aAttributes['cmd'])
				{
					if ('js' == $aLastCommand['cmd']) 
					{
						$mData = $aLastCommand['data'].'; '.$mData;
					} 
					elseif('ap' == $aLastCommand['cmd'] && $aLastCommand['id'] == $aAttributes['id'] && $aLastCommand['prop'] == $aAttributes['prop'])
					{
						$mData = $aLastCommand['data'].' '.$mData;
					}
					else
					{
						$this->aCommands[] = $aLastCommand;
					}
				} else {
					$this->aCommands[] = $aLastCommand;
				}
			}
		} 
		$aAttributes['data'] = $mData;
		$this->aCommands[] = $aAttributes;

		return $this;
	}
	
	/*
		Function: clearCommands
		
		Clear all the commands already added to the response.
		
		Returns:
		
		object : The <xajaxResponse> object.
	*/
	public function clearCommands()
	{
		$this->aCommands[] = array();

		return $this;
	}
	
	
	private function _printResponse_JSON()
	{
		$response = array();
		
		if (null !== $this->returnValue)
			$response['xjxrv'] = $this->returnValue;

		$response['xjxobj'] = array();

		foreach(array_keys($this->aCommands) as $sKey)
			$response['xjxobj'][] = $this->aCommands[$sKey];

		print json_encode($response);
	}
	
	/*
		Function: _printHeader_XML
		
		Used internally to print XML start tag.
	*/
	private function _printHeader_XML()
	{
		echo '<';
		echo '?';
		echo 'xml version="1.0"';
		
		$sEncoding = trim($this->sCharacterEncoding);
		if ($this->sCharacterEncoding && 0 < strlen($sEncoding)) {
			echo ' encoding="';
			echo $sEncoding;
			echo '"';
		}
		
		echo ' ?';
		echo '>';
	}
	
	/*
		Function: _printResponse_XML
		
		Used internally to generate the command output.
	*/
	public function _printResponse_XML()
	{
		echo '<';
		echo 'xjx>';
		
		if (null !== $this->returnValue)
		{
			echo '<';
			echo 'xjxrv>';
			
			$this->_printArray_XML($this->returnValue);
			
			echo '<';
			echo '/xjxrv>';
		}
		
		foreach(array_keys($this->aCommands) as $sKey)
			$this->_printCommand_XML($this->aCommands[$sKey]);
		
		echo '<';
		echo '/xjx>';
	}
	
	/*
		Function: _printCommand_XML
		
		Prints an XML representation of the command.
		
		Parameters:
		
		aAttributes - (array):  Associative array of attributes for this
			command.
	*/
	private function _printCommand_XML($aAttributes)
	{
		echo '<';
		echo 'cmd';
		
		$mData = '';
		
		foreach (array_keys($aAttributes) as $sKey) {
			if ($sKey) {
				if ('data' != $sKey) {
					echo ' ';
					echo $sKey;
					echo '="';
					echo $aAttributes[$sKey];
					echo '"';
				} else
					$mData = $aAttributes[$sKey];
			}
		}
		
		echo '>';
		
		$this->_printArray_XML($mData);
		
		echo '<';
		echo '/cmd>';
	}
	
	/*
		Function: _printArray_XML
		
		Prints an XML representation of a php array suitable
		for inclusion in the response to the browser.  Arrays
		sent via this method will be converted into a javascript
		array on the browser.
		
		Parameters:
		
		mArray - (array):  Array to be converted.
	*/
	private function _printArray_XML($mArray) {
		if ('object' == gettype($mArray))
			$mArray = get_object_vars($mArray);
		
		if (false == is_array($mArray)) {
			$this->_printEscapedString_XML($mArray);
			return;
		}
		
		echo '<';
		echo 'xjxobj>';
		
		foreach (array_keys($mArray) as $sKey) {
			if (is_array($mArray[$sKey])) {
				echo '<';
				echo 'e>';
				
				foreach (array_keys($mArray[$sKey]) as $sInnerKey) {
					//SkipDebug
					if (htmlspecialchars($sInnerKey, ENT_COMPAT, 'UTF-8') != $sInnerKey) {
						$objLanguageManager = xajaxLanguageManager::getInstance();
						trigger_error(
								$objLanguageManager->getText('XJXRSP:AKERR:01')
								, E_USER_ERROR
								);
					}
					//EndSkipDebug
					
					if ('k' == $sInnerKey || 'v' == $sInnerKey) {
						echo '<';
						echo $sInnerKey;
						echo '>';
						$this->_printArray_XML($mArray[$sKey][$sInnerKey]);
						echo '<';
						echo '/';
						echo $sInnerKey;
						echo '>';
					} else {
						//SkipDebug
						$objLanguageManager = xajaxLanguageManager::getInstance();
						trigger_error(
								$objLanguageManager->getText('XJXRSP:IEAERR:01')
								, E_USER_ERROR
								);
						//EndSkipDebug
					}
				}
				
				echo '<';
				echo '/e>';
			} else {
				//SkipDebug
				$objLanguageManager = xajaxLanguageManager::getInstance();
				trigger_error(
						$objLanguageManager->getText('XJXRSP:NEAERR:01')
						, E_USER_ERROR
						);
				//EndSkipDebug
			}
		}
		
		echo '<';
		echo '/xjxobj>';
	}
	
	/*
		Function: _printEscapedString_XML
		
		Escape the specified data if necessary, so special characters in the 
		command data does not interfere with the structure of the response.
		
		This could be overridden to allow for transport encodings other than
		XML.
		
		Parameters:
		
		sData - (string):  The data to be escaped.
		
		Returns:
		
		string : The escaped data.
	*/
	private function _printEscapedString_XML($sData)
	{
		if (is_null($sData) || false == isset($sData)) {
			echo '*';
			return;
		}
		
		if ($this->bOutputEntities) {
			//SkipDebug
			if (false === function_exists('mb_convert_encoding')) {
				$objLanguageManager = xajaxLanguageManager::getInstance();
				trigger_error(
						$objLanguageManager->getText('XJXRSP:MBEERR:01')
						, E_USER_NOTICE
						);
			}
			//EndSkipDebug
			
			echo call_user_func_array(
				'mb_convert_encoding', 
				array($sData, 'HTML-ENTITIES', $this->sCharacterEncoding)
				);
			return;
		}
		
		$nCDATA = 0;
		
		$bNoOpenCDATA = (false === strpos($sData, '<'.'![CDATA['));
		if ($bNoOpenCDATA) {
			$bNoCloseCDATA = (false === strpos($sData, ']]>'));
			if ($bNoCloseCDATA) {
				$bSpecialChars = (htmlspecialchars($sData, ENT_COMPAT, 'UTF-8') != $sData);
				if ($bSpecialChars)
					$nCDATA = 1;
			} else
				$nCDATA = 2;
		} else
			$nCDATA = 2;
		
		if (0 < $nCDATA) {
			echo '<';
			echo '![CDATA[';
			
			// PHP defines numeric values as integer or float (double and real are aliases of float)
			if (is_string($sData)) {
				echo 'S';
			} else if (is_int($sData) || is_float($sData)) {
				echo 'N';
			} else if (is_bool($sData)) {
				echo 'B';
			}
			
			if (1 < $nCDATA) {
				$aSegments = explode('<'.'![CDATA[', $sData);
				$aOutput = array();
				$nOutput = 0;
				foreach (array_keys($aSegments) as $keySegment) {
					$aFragments = explode(']]>', $aSegments[$keySegment]);
					$aStack = array();
					$nStack = 0;
					foreach (array_keys($aFragments) as $keyFragment) {
						if (0 < $nStack)
							array_push($aStack, ']]]]><', '![CDATA[>', $aFragments[$keyFragment]);
						else
							$aStack[] = $aFragments[$keyFragment];
						++$nStack;
					}
					if (0 < $nOutput)
						array_push($aOutput, '<', '![]]><', '![CDATA[CDATA[', implode('', $aStack));
					else
						$aOutput[] = implode('', $aStack);
					++$nOutput;
				}
				echo implode('', $aOutput);
			} else
				echo $sData;
			
			echo ']]>';
		} else {
			if (is_string($sData)) {
				echo 'S';
			} else if (is_int($sData) || is_float($sData)) {
				echo 'N';
			} else if (is_bool($sData)) {
				echo 'B';
			}
			echo $sData;
		}
	}
	
	/*
		Function: _encodeArray
		
		Recursively serializes a data structure in an array so that it can
		be sent to the browser.  This can be thought of as the opposite of
		<xajaxRequestProcessorPlugin->_parseObjXml>.
		
		Parameters:
		
		mData - (mixed):  The data to be evaluated.
		
		Returns:
		
		mixed : The object constructed from the data.
	*/
	private function _encodeArray($mData) {
		if ('object' === gettype($mData))
			$mData = get_object_vars($mData);
		
		if (false === is_array($mData))
			return $mData;
		
		$aData = array();
		foreach (array_keys($mData) as $sKey)
			$aData[] = array(
					// key does not need to be encoded
					'k'=>$sKey,
					'v'=>$this->_encodeArray($mData[$sKey])
					);
		return $aData;
	}
	
}// end class xajaxResponse

class xajaxCustomResponse
{
	protected $sOutput;
	protected $sContentType;
	
	protected $sCharacterEncoding;
	protected $bOutputEntities;
	
	public function __construct($sContentType)
	{
		$this->sOutput = '';
		$this->sContentType = $sContentType;
		
		$objResponseManager = xajaxResponseManager::getInstance();
		
		$this->sCharacterEncoding = $objResponseManager->getCharacterEncoding();
		$this->bOutputEntities = $objResponseManager->getOutputEntities();
	}
	
	public function setCharacterEncoding($sCharacterEncoding)
	{
		$this->sCharacterEncoding = $sCharacterEncoding;
	}
	
	public function setOutputEntities($bOutputEntities)
	{
		$this->bOutputEntities = $bOutputEntities;
	}
	
	public function clear()
	{
		$this->sOutput = '';
	}
	
	public function append($sOutput)
	{
		$this->sOutput .= $sOutput;
	}
	
	public function appendResponse($objResponse)
	{
		//SkipDebug
		if (false == ($objResponse instanceof xajaxCustomResponse )) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
				$objLanguageManager->getText('XJXRSP:MXRTERR')
				, E_USER_ERROR
				);
		}
		
		if ($objResponse->getContentType() != $this->getContentType()) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
				$objLanguageManager->getText('XJXRSP:MXCTERR')
				, E_USER_ERROR
				);
		}
		
		if ($objResponse->getCharacterEncoding() != $this->getCharacterEncoding()) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
				$objLanguageManager->getText('XJXRSP:MXCEERR')
				, E_USER_ERROR
				);
		}
		
		if ($objResponse->getOutputEntities() != $this->getOutputEntities()) {
			$objLanguageManager = xajaxLanguageManager::getInstance();
			trigger_error(
				$objLanguageManager->getText('XJXRSP:MXOEERR')
				, E_USER_ERROR
				);
		}
		//EndSkipDebug
		
		$this->sOutput .= $objResponse->getOutput();
	}
	
	public function getContentType()
	{
		return $this->sContentType;
	}
	
	public function getCharacterEncoding()
	{
		return $this->sCharacterEncoding;
	}
	
	public function getOutputEntities()
	{
		return $this->bOutputEntities;
	}
	
	public function getOutput()
	{
		return $this->sOutput;
	}
	
	public function printOutput()
	{
		$sContentType = $this->sContentType;
		$sCharacterSet = $this->sCharacterEncoding;
		
		header("content-type: {$sContentType}; charset={$sCharacterSet}");
		
		echo $this->sOutput;
	}
}
