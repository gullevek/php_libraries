{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* edit body part
	* HISTORY:
	********************************************************************
*}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{$HTML_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$DEFAULT_ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$css}{$STYLESHEET}">
	{/if}
	{if $CSS_CORE_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_CORE_INCLUDE}">
	{/if}
	{if $CSS_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_INCLUDE}">
	{/if}
	{if $CSS_SPECIAL_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_SPECIAL_INCLUDE}">
	{/if}
	<script language="JavaScript" src="{$js}/firebug.js"></script>
	{if $USE_JQUERY}
	{* JQuery *}
	<script type="text/javascript" src="{$js}/jquery.min.js"></script>
	{/if}
	{if $USE_PROTOTYPE}
	{* declare prototype everywhere *}
	<script src="{$js}/scriptaculous/prototype.js" type="text/javascript"></script>
		{if $USE_SCRIPTACULOUS}
	<script src="{$js}/scriptaculous/scriptaculous.js" type="text/javascript"></script>
		{/if}
	{/if}
	{if $JAVASCRIPT}
	<script language="JavaScript" src="{$js}{$JAVASCRIPT}"></script>
	{/if}
	{if $JS_CORE_INCLUDE}
	<script language="JavaScript" src="{$JS_CORE_INCLUDE}"></script>
	{/if}
	{if $JS_INCLUDE}
	<script language="JavaScript" src="{$JS_INCLUDE}"></script>
	{/if}
	{if $JS_SPECIAL_INCLUDE}
	<script language="JavaScript" src="{$JS_SPECIAL_INCLUDE}"></script>
	{/if}
	{* for including datepickr or flatpickr *}
	{if $JS_DATEPICKR}
	<link rel=stylesheet type="text/css" href="{$js}/datepickr/datepickr.min.css">
	<script language="JavaScript" src="{$js}/datepickr/datepickr.min.js"></script>
	<script language="JavaScript" src="{$js}/datepickr/datepickr.init.js"></script>
	{/if}
	{if $JS_FLATPICKR}
	<link rel=stylesheet type="text/css" href="{$js}/flatpickr/flatpickr.min.css">
	<script language="JavaScript" src="{$js}/flatpickr/flatpickr.min.js"></script>
	<script language="JavaScript" src="{$js}/flatpickr/flatpickr.ja.js"></script>
	{/if}
	{if $USE_OVERLIB}
	{popup_init src="`$js`/overlib/overlib.js"}
	{/if}
</head>
<body>
{include file="$TEMPLATE_NAME"}
</body>
</html>
