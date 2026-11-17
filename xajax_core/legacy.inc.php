<?php
/**
 * Backwards-compatibility shim for xajax 0.5.
 *
 * Applications written against xajax 0.5 could extend the legacyXajax and
 * legacyXajaxResponse classes instead of the base xajax and xajaxResponse
 * classes.  Those classes were provided by this file in the 0.5 distribution.
 *
 * The PHP 8 compatible core already exposes the deprecated 0.5 methods on the
 * base classes themselves (see xajax.inc.php and xajaxResponse.inc.php), so the
 * legacy classes are now thin subclasses that exist only so that existing
 * `extends legacyXajax` / `extends legacyXajaxResponse` declarations, and
 * `require_once 'legacy.inc.php'` statements, keep working unchanged.
 */

// The legacy classes subclass the current core classes, so make sure those are
// loaded even when the caller only did require_once 'legacy.inc.php'.
if (!class_exists('xajax', false))
	require_once dirname(__FILE__) . '/xajax.inc.php';
if (!class_exists('xajaxResponse', false))
	require_once dirname(__FILE__) . '/xajaxResponse.inc.php';

if (!class_exists('legacyXajaxResponse', false)) {

	class legacyXajaxResponse extends xajaxResponse
	{
	}

}

if (!class_exists('legacyXajax', false)) {

	class legacyXajax extends xajax
	{
		/*
			Constructor retained with the 0.5 signature.  PHP 4 style
			constructors are no longer supported by PHP 8, so this is declared
			as __construct while the legacy name is preserved as a callable
			alias for code that invokes it directly.
		*/
		public function __construct($sRequestURI='', $sWrapperPrefix='xajax_', $sEncoding=XAJAX_DEFAULT_CHAR_ENCODING, $bDebug=false)
		{
			parent::__construct();
			$this->configure('requestURI', $sRequestURI);
			$this->configure('wrapperPrefix', $sWrapperPrefix);
			$this->configure('characterEncoding', $sEncoding);
			$this->configure('debug', $bDebug);
		}

	}

}
