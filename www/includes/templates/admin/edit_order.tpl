{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/07/11
	* DESCRIPTION:
	* order page
	* HISTORY:
	********************************************************************
*}

<html>
<head>
	<title>{$HTML_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$DEFAULT_ENCODING}">
	{if $STYLESHEET}
	<link rel=stylesheet type="text/css" href="{$css}{$STYLESHEET}">
	{/if}
</head>
<body>
<table width="100%" border="0" cellpadding="0" cellspacing="1">
<!-- ERROR MSG START //-->
{foreach from=$form_error_msg item=element key=key name=loop}
    {include file="edit_error_msg.tpl"}
{/foreach}
<!-- ERROR MSG END //-->
<!-- BODY START //-->
<tr>
	<td class="edit_bgcolor">
	<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<!-- ANFANG Neu //-->
	<form method="post" enctype="multipart/form-data">
	<tr>
		<td class="edit_fgcolor_alt" class="normal" colspan="2">
			Order
		</td>
	</tr>
	<tr>
		<td class="edit_fgcolor" class="normal" width="80%">
			<select name="position[]" size="20" multiple>
				{html_options values=$options_id output=$options_name selected=$options_selected}
			</select>
			<!-- verschiedene hiddens //-->
			{foreach item=item from=$row_data_id key=key}
				<input type="hidden" name="row_data_id[{$key}]" value="{$item}">
			{/foreach}
			{foreach item=item from=$row_data_order key=key}
				<input type="hidden" name="row_data_order[{$key}]" value="{$item}">
			{/foreach}
			<!-- verschiedene hiddens //-->
		</td>
		<td class="edit_fgcolor" class="normal" width="20%">
			<input type="submit" name="up" value="Up">
			<p>
			<hr>
			<p>
			<input type="submit" name="down" value="Down">
			<!-- single hiddens //-->
			<input type="hidden" name="table_name" value="{$table_name}">
			<input type="hidden" name="where_string" value="{$where_string}">
			<!-- single hiddens //-->
		</td>
	</tr>
	<tr>
		<td class="edit_fgcolor_alt" class="normal" colspan="2">
			<input type="button" name="close" value="Close" OnClick="self.close();">
		</td>
	</tr>
	</form>
<!-- ENDE FOOTER //-->
	</table>
	</td>
</tr>
<!-- BODY END //-->
</table>
</body>
</html>
