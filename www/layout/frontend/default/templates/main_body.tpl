{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/development/core_data/php/www/layout/frontend/default/templates/main_body.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2010-09-02 11:58:10 +0900 (Thu, 02 Sep 2010) $
	= $LastChangedRevision: 3159 $
	= SUBVERSION DATA ===================================================

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

{* $Id: main_body.tpl 3159 2010-09-02 02:58:10Z gullevek $ *}
