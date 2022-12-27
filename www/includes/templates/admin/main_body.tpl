{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* edit body part
	* HISTORY:
	********************************************************************
*}

<!doctype html>
<html>
<head>
	<title>{$HTML_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$css}{$STYLESHEET}">
	{/if}
	{if $CSS_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_INCLUDE}">
	{/if}
	{if $CSS_SPECIAL_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_SPECIAL_INCLUDE}">
	{/if}
	<script language="JavaScript">
	<!--
	var DEBUG = {$JS_DEBUG};
	//-->
	</script>
	<script language="JavaScript" src="{$js}/firebug.js"></script>
	<script language="JavaScript" src="{$js}/debug.js"></script>
	{if $USE_JQUERY}
	<!-- JQuery -->
	<script type="text/javascript" src="{$js}/jquery.min.js"></script>
	{/if}
	{if $USE_PROTOTYPE}
	<script src="{$js}/scriptaculous/prototype.js" type="text/javascript"></script>
		{if $USE_SCRIPTACULOUS}
	<script src="{$js}/scriptaculous/scriptaculous.js" type="text/javascript"></script>
		{/if}
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
	{if $JAVASCRIPT}
	<script language="JavaScript" src="{$js}{$JAVASCRIPT}"></script>
	{/if}
	{* declare prototype everywhere *}
	{if $JS_INCLUDE}
	<script language="JavaScript" src="{$JS_INCLUDE}"></script>
	{/if}
	{if $JS_SPECIAL_INCLUDE}
	<script language="JavaScript" src="{$JS_SPECIAL_INCLUDE}"></script>
	{/if}
	{if $USE_TINY_MCE}
	<!-- TinyMCE -->
	<script type="text/javascript" src="{$js}/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript">
	{literal}
		tinyMCE.init({
	{/literal}
			mode : "specific_textareas",
			language : "{$LANG_SHORT}",
			theme : "advanced",
			editor_selector : "mceEditor",
			theme_advanced_toolbar_location : "top",
			theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,undo,redo,|,cleanup,|,bullist,outdent,indent",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true
	{literal}
		});
	{/literal}

	{literal}
		tinyMCE.init({
	{/literal}
			mode : "specific_textareas",
			language : "{$LANG_SHORT}",
			theme : "advanced",
			editor_selector : "mceTable",
			plugins : "table",
			theme_advanced_toolbar_location : "top",
			theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,undo,redo,|,cleanup,|,tablecontrols",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true
	{literal}
		});
	{/literal}
	</script>
	<!-- /TinyMCE -->
	{/if}
</head>
<body>
{popup_init src="`$js`/overlib/overlib.js"}
<div style="margin: 2px; width: {$table_width}; margin-bottom: 10px;">
	<div style="position: relative; height: 20px;" class="menu">
		<div style="position: absolute; width: 200px;">{t 1=$USER_NAME|upper}Hello %1{/t}</div>
		<div style="position: absolute; text-align: right; right: 0px; width: 120px;">
			<a href="#" OnClick="loginLogout(); return false;">{t}Logout{/t}</a>
		</div>
	</div>
	<div style="margin-bottom: 5px;" class="menu">
{* menu *}
{foreach key=key item=item from=$nav_menu}
	{if $key != 0}
		&middot;
	{/if}
	{if !$item.enabled}
		{$item.name}
	{elseif $item.selected}
		<a href="{$item.url}" class="highlight">{$item.name}</a>
	{elseif $item.popup}

	{elseif $item.enabled}
		<a href="{$item.url}">{$item.name}</a>
	{/if}
{/foreach}
	</div>
	<div id="pagename" class="pagename">
		{$page_name}
	</div>
</div>
{* error/warning *}
{if $messages}
<div style="margin: 2px; width: {$table_width};">
	{foreach from=$messages item=item key=key}
	<div class="{$item.class}">{$item.msg}</div>
	{/foreach}
</div>
{/if}
{include file="$TEMPLATE_NAME"}

{* debug info *}
{if $DEBUG}
<div style="width:{$table_width};" class="debug_message">
{$Id}<br>
	<b>{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}</b><br>
{$debug_error_msg}
</div>
{/if}

</body>
</html>
