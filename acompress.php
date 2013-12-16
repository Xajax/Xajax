<?php
/**
 * 2012-01-23
 * xajax js compress tool
 */
//Setup the xajax framework.
include_once("xajax_core/xajax.inc.php");
$xajax = new xajax();
$xajax->autoCompressJavascript(NULL, true);