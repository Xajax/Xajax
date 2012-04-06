<?php
if ( false == class_exists( 'xajaxPlugin' ) || false == class_exists( 'xajaxPluginManager' ) )
{
	$sBaseFolder = dirname( dirname( dirname( __FILE__ ) ) );
	$sXajaxCore = $sBaseFolder.'/xajax_core';

	if ( false == class_exists( 'xajaxPlugin' ) )
		require $sXajaxCore.'/xajaxPlugin.inc.php';

	if ( false == class_exists( 'xajaxPluginManager' ) )
		require $sXajaxCore.'/xajaxPluginManager.inc.php';
}

class clsSwfUpload
	extends xajaxResponsePlugin
{

	//--------------------------------------------------------------------------------------------------------------------------------

	private $sCallName = "SWFUpload";
	private $sDefer;
	private $sJavascriptURI;
	private $bInlineScript;
	private $SWFupload_FadeTimeOut = 1500;
	private $sRequestedFunction = NULL;

	private $sXajaxPrefix = "xajax_";
	//--------------------------------------------------------------------------------------------------------------------------------

	public function clsSwfUpload()
	{
		$this->sDefer = '';
		$this->sJavascriptURI = '';
		$this->bInlineScript = false;
	}
	//--------------------------------------------------------------------------------------------------------------------------------

	function getName()
	{
		return get_class( $this );
	}

	//--------------------------------------------------------------------------------------------------------------------------------
	public function configure( $sName, $mValue )
	{
		switch ( $sName )
		{
			case 'scriptDeferral':
				if ( true === $mValue || false === $mValue )
				{
					if ( $mValue )
						$this->sDefer = 'defer ';
					else
						$this->sDefer = '';
				}
				break;

			case 'javascript URI':
				$this->sJavascriptURI = $mValue;
				break;

			case 'inlineScript':
				if ( true === $mValue || false === $mValue )
					$this->bInlineScript = $mValue;
				break;

			case 'SWFupload_FadeTimeOut':
				if ( is_numeric( $mValue ) )
					$this->SWFupload_FadeTimeOut = $mValue;
				break;
		}
	}

	function generateHash()
	{
		return ('SWFuploadPlugin');
	}


	//--------------------------------------------------------------------------------------------------------------------------------

	public function generateClientScript()
	{
		echo "if (undefined == xajax.ext)	xajax.ext = {};\n";
		echo "xajax.ext.SWFupload = {};";
		echo "xajax.ext.SWFupload.config = {};\n";
		echo "xajax.ext.SWFupload.config.javascript_URI='".$this->sJavascriptURI."xajax_plugins/response/swfupload/';\n";
		echo "xajax.ext.SWFupload.config.FadeTimeOut = '".$this->SWFupload_FadeTimeOut."';\n";

		include( dirname( __FILE__ ).'/swfupload.js' );
		include( dirname( __FILE__ ).'/swfupload.xajax.js' );
	}

	//--------------------------------------------------------------------------------------------------------------------------------

	function transForm( $id, $config, $multi = false )
	{
		$command = array
		(
			'cmd' => 'SWFup_tfo',
			'id' => $id
		);

		$this->addCommand( $command, array
		(
			"config" => $config,
			"multi" => $multi
		));
	}

	//--------------------------------------------------------------------------------------------------------------------------------

	function transField( $id, $config, $multi = false )
	{
		$command = array
		(
			'cmd' => 'SWFup_tfi',
			'id' => $id
		);

		$this->addCommand( $command, array
		(
			"config" => $config,
			"multi" => $multi
		));
	}
	//--------------------------------------------------------------------------------------------------------------------------------

	function destroyField( $id )
	{
		$command = array
		(
			'cmd' => 'SWFup_dfi',
			'id' => $id
		);

		$this->addCommand( $command, array ());
	}
	//--------------------------------------------------------------------------------------------------------------------------------

	function destroyForm( $id )
	{
		$command = array
		(
			'cmd' => 'SWFup_dfo',
			'id' => $id
		);

		$this->addCommand( $command, array ());
	}
//--------------------------------------------------------------------------------------------------------------------------------


}

$objPluginManager = &xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin( new clsSwfUpload(), 100 );
?>