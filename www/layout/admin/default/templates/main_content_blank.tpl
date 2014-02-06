{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2007/10/18
	* DESCRIPTION:
	* content main part (buttons, load, etc)
	* HISTORY:
	********************************************************************
*}

<script language="JavaScript">
<!--
var form_name = '{$form_name}';
var load_id = '{if $primary_key}{$primary_key}{/if}';
var show_sort = {if $show_sort}{$show_sort}{else}0{/if};
//-->
</script>

<div style="width: {$table_width}px; margin-top: 5px; margin-bottom: 5px;">
	<form method="post" name="{$form_name}" enctype="multipart/form-data">
	{* save, delete commands *}
	<div style="margin-bottom: 2px; padding: 2px;" class="buttongroup">
		{include file=cms_buttons.tpl}
	</div>
	{include file=$CONTENT_INCLUDE}
	{* save, delete commands *}
	<div style="margin-bottom: 2px; padding: 2px;" class="buttongroup">
		{include file=cms_buttons.tpl}
	</div> <!-- button close //-->
	{* hidden group *}
	<input type="hidden" id="primary_key" name="primary_key" value="{$primary_key}">
	{* action var set *}
	<input type="hidden" id="action" name="action" value="">
	<input type="hidden" id="action_flag" name="action_flag" value="">
	<input type="hidden" id="action_yes" name="action_yes" value="">
	<input type="hidden" id="action_id" name="action_id" value="">
	<input type="hidden" id="action_value" name="action_value" value="">
	<input type="hidden" id="action_menu" name="action_menu" value="">
	<input type="hidden" id="action_error" name="action_error" value="">
	<input type="hidden" name="action_loaded" value="true">
	</form>
</div>
