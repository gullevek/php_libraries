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
	<meta http-equiv="Content-Type" content="text/html; charset={$DEFAULT_ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$css}{$STYLESHEET}">
	{/if}
	<script language="JavaScript">
<!--
function pop(theURL, winName, features) {
	winName = window.open(theURL, winName, features);
	winName.focus();
}
//-->
	</script>
</head>
<body>
<table width="{$table_width}" border="0" cellpadding="0" cellspacing="1">
<!-- ERROR MSG START //-->
{foreach from=$form_error_msg item=element key=key name=loop}
	{include file="edit_error_msg.tpl"}
{/foreach}
<!-- ERROR MSG END //-->
<!-- TOP MENU START //-->
<tr>
	<td width="{$table_width}" class="menu_bgcolor" valign="top">
		<table width="100%" border="0" cellpadding="2" cellspacing="1">
		<form method="post">
		<tr>
		<td bgcolor="{$HEADER_COLOR}" class="normal">
			Hello <b>{$USER_NAME|upper}</b> [{$EUID}] from the group <b>{$GROUP_NAME}</b> with Access Level <b>{$GROUP_LEVEL}</b>
		</td>
		<td bgcolor="{$HEADER_COLOR}" class="normal" align="right">
			<input type="submit" name="login_logout" value="Logout">
		</td>
		</tr>
		</form>
		</table>
		<table width="100%" border="0" cellpadding="2" cellspacing="1">
		<tr>
	{* foreach menu *}
	{foreach from=$menu_data item=menu_element}
	{* if split factor is reached *}
		{if $menu_element.splitfactor_in}
			<td class="menu_fgcolor" class="small" valign="top">
		{/if}
		{if $menu_element.position}
			<b><a href="{$menu_element.filename}">{$menu_element.pagename}</a></b><br>
		{else}
			{if !$menu_element.popup}
			<a href="{$menu_element.filename}">{$menu_element.pagename}</a><br>
			{else}
			<a href="javascript:pop('{$menu_element.filename}','{$menu_element.rand}','status=no,scrollbars=yes,width={$menu_element.width},height={$menu_element.height}');">{$menu_element.pagename}</a><br>
			{/if}
		{/if}
		{if $menu_element.splitfactor_out}
		</td>
		{/if}
	{/foreach}
		</tr>
		</table>
		<table width="100%" border="0" cellpadding="10" cellspacing="1">
		<tr>
			<td class="edit_fgcolor_alt" class="headline" align="center">
				{$page_name}
			</td>
		</tr>
		</table>
	</td>
	</tr>
<!-- TOP MENU END //-->
	<tr>
	<td width="{$table_width}" class="edit_bgcolor">
		<form method="post" name="edit_form" style="margin-block-end: 0em;">
		<table width="100%" border="0" cellpadding="2" cellspacing="1">
	{include file="edit_load.tpl"}
	{include file="edit_new.tpl"}
	{if $form_yes}
		{include file="edit_save_delete.tpl"}
		{if $form_my_page_name == "edit_pages" && $filename_exist}
		<tr>
			<td class="edit_fgcolor" class="normal">
				Filename:
			</td>
			<td class="edit_fgcolor" class="normal">
				{$filename}
				<input type="hidden" name="filename" value="{$filename}">
			</td>
		</tr>
		{/if}
		{include file="edit_elements.tpl"}
		{include file="edit_hidden.tpl"}
		{include file="edit_save_delete.tpl"}
	{/if}
		</table>
		</form>
	</td>
</tr>
</table>
</body>
</html>
