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
var load_id = '{if $primary_key}{$primary_key}{else}0{/if}';
var show_sort = {$show_sort};
//-->
</script>
<div style="position:relative; width: {$table_width}px; margin-top: 5px; margin-bottom: 5px; top: 0px; left: 0px;">
	<div id="main_menu" style="position: absolute; top: 0px; left: 0px; width: 750px; height: 550px; z-index: 5; overflow: auto; padding: 2px; visibility: hidden;" class="sidemenu">
		<div style="position: absolute; top: 2px; left: 2px;">
			<input type="button" id="show_menu" name="show_menu" value="{t}Close Menu{/t}" OnClick="ShowHideMenu('hide', 'main_menu');"> {if $primary_key}<a href="#{$primary_key}">&darr;</a>{/if}&nbsp;<input type="text" id="search" name="search" value="{$search}" OnKeyup="QuickSearch(); return false;" {popup text="Search" width="150"}> <span id="search_status">{t}Enter Value{/t}</span>
			<div id="search_found">{t 1=$menu_elements}Found: %1{/t}</div>
		</div>
		<div id="search_results" style="position: relative; top: 32px; left: 2px; padding: 2px; margin: 2px; width: 95%; height: 90%; overflow: auto; visibility: hidden; display: none; z-index: 3;">
		</div>
		<div id="element_list" style="position: relative; top: 32px; left: 2px; padding: 2px; margin: 2px; width: 95%; height: 90%; overflow: auto; z-index: 3;">
		{foreach from=$table_menu key=key item=item}
			{if $s_title != $item.title && $item.title}
				{assign var='s_title' value=$item.title}
			<div style="font-weight: bold;">{$item.title}</div>
			{/if}
			{if $show_sort}
			{strip}
			<span style="font-family: monospace;">[
				{if $item.order_move == '-' || !$item.order_move}
			&nbsp;
				{/if}
				{if $item.order_move == '+' || $item.order_move == '*'}
			<a href="javascript:me({$item.id}, '+');" {popup text="Up" width="20"}>&uarr;</a>
				{/if}
				{if $item.order_move == '-' || $item.order_move == '*'}
			<a href="javascript:me({$item.id}, '-');" {popup text="Down" width="20"}>&darr;</a>
				{/if}
				{if $item.order_move == '+' || !$item.order_move}
			&nbsp;
				{/if}
			]</span>
			{/strip}
			{/if}
			<a name="{$item.id}"></a>
			{if $item.key}[{$item.key}] {/if}{if $item.status}[{$item.status}] {/if}<a href="javascript:le('{$item.id}');" class="{if $item.deleted == 't' && $primary_key == $item.id}item_loaded_deleted{elseif $item.deleted == 't'}item_deleted{elseif $primary_key == $item.id}item_loaded{/if}" {popup text="`$item.desc`" caption="Info" width="350"}>{$item.name}</a><br>
		{/foreach}
		</div>
		<div style="position: absolute; bottom: 2px; left: 2px; z-index: 5;">
			<input type="button" id="show_menu" name="show_menu" value="{t}Close Menu{/t}" OnClick="ShowHideMenu('hide', 'main_menu');">
		</div>
	</div>
	<div style="position: relative; top: 0px; left: 0px; width: 790px; z-index: 0; margin: 2px; padding: 2px;">
		<form method="post" name="{$form_name}" enctype="multipart/form-data">
		{* menu button *}
		{if !$hide_menu}
		<div style="margin-bottom: 2px; padding: 2px; position: relative;">
			<input type="button" id="show_menu" name="show_menu" value="{t}Show Menu{/t}" OnClick="ShowHideMenu('show', 'main_menu');" {if $page_acl < 20}disabled{/if}>
		</div>
		{/if}
		{* save, delete commands *}
		<div style="margin-bottom: 2px; padding: 2px; position: relative;" class="buttongroup">
			<div style="margin-bottom: 2px; padding: 2px; position: relative; height: 20px;">
				<input type="button" name="new" value="{t}New{/t}" OnClick="document.{$form_name}.action.value='new';document.{$form_name}.action_yes.value=confirm('{t}Do you want to create a new entry entry?{/t}');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				<input type="button" name="save" value="{t}Save{/t}" OnClick="document.{$form_name}.action.value='save';document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				{if $show_delete_button && !$show_undelete_button}
				<input type="button" name="delete" value="{t}Delete{/t}" OnClick="document.{$form_name}.action.value='delete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to delete this entry?{/t}');document.{$form_name}.action_id.value='{$primary_key}';if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
				{if $show_delete_button && $show_undelete_button}
				<input type="button" name="un_delete" value="{t}Un-Delete{/t}" OnClick="document.{$form_name}.action.value='undelete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to undelete this entry?{/t}');document.{$form_name}.action_id.value='{$primary_key}';if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
			</div>
		</div>
		{* status messages *}
		<div id="status_message" style="margin-bottom: 2px; padding: 2px; text-align: center; position: relative; visibility: hidden;">
		</div>
		{* main grouping *}
		<div style="margin-bottom: 5px; padding: 2px; position: relative; min-height: 400px;" class="{$status_color}">
			{include file=$CONTENT_INCLUDE}
		</div>

		{* save, delete commands *}
		<div style="margin-bottom: 2px; padding: 2px; position: relative;" class="buttongroup">
			<div style="margin-bottom: 2px; padding: 2px; position: relative; height: 20px;">
				<input type="button" name="new" value="{t}New{/t}" OnClick="document.{$form_name}.action.value='new';document.{$form_name}.action_yes.value=confirm('{t}Do you want to create a new entry entry?{/t}');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				<input type="button" name="save" value="{t}Save{/t}" OnClick="document.{$form_name}.action.value='save';document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				{if $show_delete_button && !$show_undelete_button}
				<input type="button" name="delete" value="{t}Delete{/t}" OnClick="document.{$form_name}.action.value='delete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to delete this entry?{/t}');document.{$form_name}.action_id.value='{$primary_key}';if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
				{if $show_delete_button && $show_undelete_button}
				<input type="button" name="un_delete" value="{t}Un-Delete{/t}" OnClick="document.{$form_name}.action.value='undelete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to undelete this entry?{/t}');document.{$form_name}.action_id.value='{$primary_key}';if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
			</div>
		</div>
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
</div>
