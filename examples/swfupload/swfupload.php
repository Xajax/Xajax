<?php
	if (isset($_POST['PHPSESSID'])) {
		session_id($_POST['PHPSESSID']);
	}
	session_start(); 

	ini_set("display_errors",1);
	error_reporting(E_ALL ^E_NOTICE);
	
	if (!isset($_SESSION['foo'])) $_SESSION['foo'] = 0;
	
	$core = '../../xajax_core';
	require_once $core . '/xajax.inc.php';
	
	$xajax = new xajax();
	require_once '../../xajax_plugins/response/swfupload/swfupload.inc.php';
	$xajax->configure("javascript URI","../../");
	$xajax->configure("responseType","XML");

	$xajax->register(XAJAX_FUNCTION,"uploader",array("mode" => "'SWFupload'","SWFform" => "'upload_form'"));
	$xajax->register(XAJAX_FUNCTION,"transform");

	$xajax->register(XAJAX_FUNCTION,"transfield");
	$xajax->register(XAJAX_FUNCTION,"destroyfield");


	$xajax->register(XAJAX_FUNCTION,"sync_test",array('mode' => "'synchronous'"));
	$xajax->register(XAJAX_FUNCTION,"destroyform");
	
	$_SESSION['foo']++;
	$xajax->processRequest();
	
	// This is dev code, you don't need these lines for using the SWFupload plugin.
	$xajax->autoCompressJavaScript(null,true);
	$xajax->autoCompressJavaScript('../../xajax_plugins/response/swfupload/swfupload.xajax.js',true);
	$xajax->autoCompressJavaScript('../../xajax_plugins/response/swfupload/swfupload.js',true);


	function transfield() {

		$objResponse = new xajaxResponse();
		
		$foo= "
			<input type='text' name='testfieldtext' value='foo' /><br />
			<input type='file' name='filetest' id='filetest' />
		";
		
		$objResponse->assign('fieldtest','innerHTML',$foo);

		$objResponse->clsSwfUpload->transField('filetest'
			,array(
				"file_types" => "*.jpg;*.gif;*.png;"
				,"file_types_description" => "Image Files or mp3"
				,"file_size_limit" => "5 MB"
				,"upload_complete_handler" => "function () {
																				}"
				,"post_params" => array(
					"PHPSESSID" => session_id()
				)
			)
			, true
		);
	
	
		return $objResponse;
	}

	function destroyField() {
		$objResponse = new xajaxResponse();
		$objResponse->clsSwfUpload->destroyField('filetest');		
		$objResponse->assign('fieldtest','innerHTML','');
		return $objResponse;
	
	}

	function transform() {
	
		$objResponse = new xajaxResponse();
		$objResponse->clsSwfUpload->transForm('upload_form'
			,array(
				"file_types" => "*.jpg;*.gif;*.png;"
				,"file_types_description" => "Image Files or mp3"
				,"file_size_limit" => "5 MB"
				,"upload_complete_handler" => "function () {
																				}"
				,"post_params" => array(
					"PHPSESSID" => session_id()
				)

			)
			, true
		);
	
	
		return $objResponse;
	}

	function sync_test() {
		$objResponse = new xajaxResponse();

		$objResponse->setReturnValue('hello world');
		return $objResponse;
	}	
	
	function destroyform() {
	
		$objResponse = new xajaxResponse();
		$objResponse->clsSwfUpload->destroyForm('upload_form');
	
		return $objResponse;
	}

	function uploader($aFormValues=array()) {
		
		$objResponse = new xajaxResponse();
		
		$html="";
		foreach ($_FILES as $key => $file) {
			$html .="
				<div style=\"border:1px solid #f0f0f0;background:#fff;padding:4px;margin-bottom:4px;\">
					<div style=\"float:left;width:100px;\">Filename:</div>
					<div style=\"float:left;\">".$_FILES[$key]['name']."</div>
					<br style=\"clear:both;\" />
					<div style=\"float:left;width:100px;\">Size:</div>
					<div style=\"float:left;\">".$_FILES[$key]['size']."</div>
					<br style=\"clear:both;\" />
					<div style=\"float:left;\">\$_SESSION request counter: ".$_SESSION['foo']."</div>
					<br style=\"clear:both;\" />
				</div>
				"	;
		}
		if ("" == $html) $html="empty queue";
		$objResponse->append("results","innerHTML",$html);
	
		return $objResponse;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>XAJAX SWFupload Plugin</title>
	<?php $xajax->printJavaScript();?>
	<link rel="stylesheet" type="text/css" href="demo.css" />

	<style type="text/css">
		/* <![CDATA[ */
		
.swf_queued_file {
	background:#f0f0f0;
	margin-bottom:2px;
	border:1px solid #c0c0c0;
	padding:4px;
	clear:both;
}

.swf_queued_file_removed {
	background:#FFDFDF;
	margin-bottom:2px;
	border:1px solid #c0c0c0;
	padding:4px;
	clear:both;
}

.swf_queued_file_finished {
	background:#DFFFE6;
	margin-bottom:2px;
	border:1px solid #c0c0c0;
	padding:4px;
	clear:both;
}


.swf_queued_file_remove {
	font-size:11px;
	float:left;
	width:20px;
	height:20px;
	background:url(img/bin_closed.png) no-repeat top left;	
	overflow:hidden;
}
.swf_queued_filename{
	font-size:11px;
	display:inline;
	float:left;
	width:180px;
	overflow:hidden;
}

.swf_queued_file_progress_container {
	float:left;
	display:inline;
	background:#d0d0d0;
	margin-top:4px;
	height:10px;
	width:220px;
	margin-right:8px;
}
.swf_queued_file_progress_bar {
	height:8px;
	background:#333;	
	width:1px;
}

.swf_queued_filesize{
	font-size:11px;
	float:left;
	overflow:hidden;
}

		 /* ]]> */
	</style>
	
</head>
<body onload="xajax_transform();">

	<h1>XAJAX SWFupload Plugin</h1>

	<div id="example">
		<h2>demo</h2>
		<form enctype="multipart/form-data" id="upload_form" action="upload.php" onsubmit="return false;" method="post" >


			<div style="font-size:11px;">
				The file size is limited to 5MB. Don't even think about hijacking the form, all your uploads will safely be stored in /dev/null. :)
			</div>
			
			<div class="formLabel">test field:</div>
			<div class="formField">
				<input type="text" id="foofield" name="foo" value="bar" />
			</div>
			<div style="clear:both;" ></div>

			<div class="formLabel">Normal field 1:</div>
			<div class="formField">
				<input type="file" id="upFile_standard" name="upFile_standard" value="" />			</div>
			<div style="clear:both;" ></div>

			<div class="formLabel">Normal field 2:</div>
			<div class="formField">
					<input type="file" id="upFile_standard2" name="upFile_standard2" value="" />
			</div>
			<div style="clear:both;" ></div>
			<div class="formLabel">&nbsp;</div>
			<div class="formField"><input id="uploadBtn2" type="button" onclick="xajax_uploader(xajax.getFormValues('upload_form'));" value="upload file"/></div>
			<div style="clear:both;" ></div>

			<div style="clear:both;" ></div>
			<div class="formLabel">&nbsp;</div>
			<div class="formField"><input id="uploadBtn3" type="button" onclick="xajax_transform();" value="create swf upload"/></div>
			<div style="clear:both;" ></div>


			<div style="clear:both;" ></div>
			<div class="formLabel">&nbsp;</div>
			<div class="formField"><input id="uploadBtn4" type="button" onclick="xajax_destroyform('upload_form');" value="destroy form"/></div>
			<div style="clear:both;" ></div>





		</form>
		<hr />
			<div id="fieldtest">
				
			</div>

			<div style="clear:both;" ></div>
			<div class="formLabel">&nbsp;</div>
			<div class="formField"><input id="uploadBtn5" type="button" onclick="xajax_transfield();" value="create swf upload field"/></div>
			<div style="clear:both;" ></div>


			<div style="clear:both;" ></div>
			<div class="formLabel">&nbsp;</div>
			<div class="formField"><input id="uploadBtn6" type="button" onclick="xajax_destroyfield();" value="destroy field"/></div>
			<div style="clear:both;" ></div>			
			<input type="button" value="sync test" onclick="alert(xajax_sync_test());" />

	</div>


	<div id="response">
		<h2>response</h2>
		<div id="results">
		</div>
	</div>
	<div style="clear:both;" ></div>

	

</body>
</html>

