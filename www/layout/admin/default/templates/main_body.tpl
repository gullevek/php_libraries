{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* edit body part
	* HISTORY:
	********************************************************************
*}

<html>
<head>
	<title>{$HTML_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$css}{$STYLESHEET}">
	{/if}
	{if $USE_JQUERY}
	<!-- JQuery -->
	<script type="text/javascript" src="{$js}/jquery.js"></script>
	{/if}
	{if $CSS_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_INCLUDE}">
	{/if}
	{if $CSS_SPECIAL_INCLUDE}
	<link rel=stylesheet type="text/css" href="{$CSS_SPECIAL_INCLUDE}">
	{/if}
	<script language="JavaScript" src="{$js}/firebug.js"></script>
	{if $JAVASCRIPT}
	<script language="JavaScript" src="{$js}{$JAVASCRIPT}"></script>
	{/if}
	{* declare prototype everywhere *}
	{if $USE_PROTOTYPE}
	<script src="{$js}/scriptaculous/prototype.js" type="text/javascript"></script>
	{/if}
	{if $USE_SCRIPTACULOUS}
	<script src="{$js}/scriptaculous/scriptaculous.js" type="text/javascript"></script>
	{/if}
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
	{popup_init src="`$js`/overlib/overlib.js"}
</head>
<body>
<div style="margin: 2px; width: {$table_width}px; margin-bottom: 10px;">
	<div style="position: relative; height: 20px;" class="menu">
		<div style="position: absolute; width: 200px;">{t 1=$USER_NAME|upper}Hello %1{/t}</div>
		<div style="position: absolute; text-align: right; right: 0px; width: 120px;">
			<form method="post" name="loginlogout">
				<a href="javascript:document.loginlogout.login_logout.value='Logout';document.loginlogout.submit();">{t}Logout{/t}</a>
				<input type="hidden" name="login_logout" value="">
			</form>
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
	<div class="pagename">
		{$page_name}
	</div>
</div>
{* error/warning *}
{if $messages}
<div style="margin: 2px; width: {$table_width}px;">
	{foreach from=$messages item=item key=key}
	<div class="{$item.class}">{$item.msg}</div>
	{/foreach}
</div>
{/if}
{include file="$TEMPLATE_NAME"}

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
