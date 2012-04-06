<?php
/*
	File: thewall.php

	Example which demonstrates a xajax implementation of a graffiti wall.
	
	Title: Graffiti Wall Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <thewall.php>
	- <thewall.common.php>
	- <thewall.server.php>
*/

/*
	@package xajax
	@version $Id: xajax.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

require_once("thewall.common.php");

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>The Graffiti Wall</title>
		<?php $xajax->printJavascript(); ?>
		<script>
		function update()
		{
			xajax_updateWall();
			setTimeout("update()", 30000);
		}
		</script>
		<style type="text/css">
		div.label{
			clear: both;
			float:left;
			width:60px;
			text-align:right;
			font-size: small;
		}
		#handle{
			font-size: x-small;
			width: 100px;
		}
		#words{
			font-size: x-small;
			width: 400px;
		}
		#post{
			font-size: small;
			margin-left: 390px;
		}
		#theWall{
			background-image: url('brick.jpg');
			height: 300px;
			padding: 50px;
			border: 3px outset black;
			overflow: auto;
		}
		.notice{
			font-size: small;
		}
		</style>
	</head>
	<body>
		<form id="scribbleForm" onsubmit="return false;">
			<div class="label">Handle:</div><input id="handle" name="handle" type="text" /><div></div>
			<div class="label">Graffiti:</div><input id="words" name="words"type="text" maxlength="75"/><div></div>
			<input id="post" type="submit" value="scribble" onclick="xajax_scribble(xajax.getFormValues('scribbleForm'));" />
		</form>
		<div class="notice">To see xajax's UTF-8 support, try posting words in other languages.  You can copy and paste from <a href="http://www.unicode.org/iuc/iuc10/x-utf8.html" target="_new">here</a></div>
		<div id="theWall">
		</div>
		<div style="text-align:center;">
		powered by <a href="http://www.xajaxproject.org">xajax</a>
		</div>
		<script>
			update();
		</script>
	</body>
</html>