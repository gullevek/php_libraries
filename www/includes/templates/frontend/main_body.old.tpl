{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2008/12/24
	* DESCRIPTION:
	* main body
	* HISTORY:
	********************************************************************
*}

<html>
<head>
	<title>{$HTML_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$DEFAULT_ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$CSS}{$STYLESHEET}">
	{/if}
	<script language="JavaScript">
	<!--
	var DEBUG = {$JS_DEBUG};
	//-->
	</script>
	<script language="JavaScript" src="{$js}/firebug.js"></script>
	<script language="JavaScript" src="{$js}/debug.js"></script>
	{if $JAVASCRIPT}
	<script language="JavaScript" src="{$JS}{$JAVASCRIPT}"></script>
	{/if}
	{if $ajax_javascript}
	<script language="JavaScript">
	{$ajax_javascript}
	</script>
	{/if}
	{if $JS_INCLUDE}
	<script language="JavaScript" src="{$JS_INCLUDE}"></script>
	{/if}
	{* for including datepickr *}
	{if $JS_DATEPICKR}
	<link rel=stylesheet type="text/css" href="{$js}/datepickr/datepickr.min.css">
	<script language="JavaScript" src="{$js}/datepickr/datepickr.min.js"></script>
	{/if}
{*	{popup_init src="`$js`/overlib/overlib.js"} *}
</head>
<body>
<form name="product_search" method="get">
<div style="border: 1px solid black; margin: 15px; padding: 5px;">
{include file="top_menu.tpl"}
</div>
<div>
{include file="$INCLUDE_TEMPLATE"}
</div>
</form>
{* debug info *}
{if $DEBUG}
<div style="width:{$table_width}px;" class="debug_message">
{$Id}<br>
	<b>{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}</b><br>
{$debug_error_msg}
</div>
{/if}

</body>
</html>
