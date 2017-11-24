{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2008/04/11
	* DESCRIPTION:
	* special content main part (buttons, load, etc)
	* HISTORY:
	********************************************************************
*}

<script language="JavaScript">
<!--
var form_name = '{$form_name}';
var load_id = {if $primary_key}{$primary_key}{else}0{/if};
//-->
</script>

<div style="position:relative; width: {$table_width}px; margin-top: 5px; margin-bottom: 5px; top: 0px; left: 0px;">
	<div style="position: relative; top: 0px; left: 0px; width: 790px; z-index: 0; margin: 2px; padding: 2px;">
		<form method="post" name="{$form_name}">
		{* save, delete commands *}
		<div style="margin: 2px; padding: 2px; position: relative;" class="buttongroup">
			<div style="padding: 2px; position: relative; height: 20px;">
				<input type="button" name="new" value="{t}New{/t}" OnClick="document.{$form_name}.action.value='new';document.{$form_name}.action_yes.value=confirm('{t}Do you want to create a new entry entry?{/t}');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				<input type="button" name="save" value="{t}Save{/t}" OnClick="document.{$form_name}.action.value='save';document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				{if $show_delete_button && !$show_undelete_button}
				<input type="button" name="delete" value="{t}Delete{/t}" OnClick="document.{$form_name}.action.value='delete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to delete this entry?{/t}');document.{$form_name}.action_id.value={$primary_key};if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
				{if $show_delete_button && $show_undelete_button}
				<input type="button" name="un_delete" value="{t}Un-Delete{/t}" OnClick="document.{$form_name}.action.value='undelete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to undelete this entry?{/t}');document.{$form_name}.action_id.value={$primary_key};if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}

			</div>
		</div>
		{* status messages *}
		<div id="status_message" style="margin-bottom: 2px; padding: 2px; text-align: center; position: relative; visibility: hidden; display: none;">
		</div>
		{* main grouping *}
		<div>
			<div class="spacer"></div>
			<div id="main_menu" style="float: left; position: relative; width: 200px; height: 450px; z-index: 5; overflow: auto; padding: 2px; margin: 2px;" class="sidemenu">
				<div style="position: absolute; top: 2px; left: 2px;">
					{if $primary_key}<a href="#{$primary_key}">&darr;</a>{/if}&nbsp;<input type="text" id="search" name="search" size="15" value="{$search}" OnKeyup="QuickSearch(); return false;" {popup text="Search" width="150"}> <span id="search_status">{t}Search{/t}</span>
					<div id="search_found">{t}Found:{/t} {$menu_elements}</div>
				</div>
				<div id="search_results" style="position: relative; top: 32px; left: 2px; padding: 2px; margin: 2px; width: 95%; height: 90%; overflow: auto; visibility: hidden; display: none;">
				</div>
				<div id="element_list" style="position: relative; top: 32px; left: 2px; padding: 2px; margin: 2px; width: 95%; height: 90%; overflow: auto;">
				{foreach from=$table_menu key=key item=item}
					<a name="{$item.id}"></a>
					{if $item.key}[{$item.key}] {/if}{if $item.status}[{$item.status}] {/if}<a href="javascript:le({$item.id});" class="{if $item.deleted == 't' && $primary_key == $item.id}item_loaded_deleted{elseif $item.deleted == 't'}item_deleted{elseif $primary_key == $item.id}item_loaded{/if}">{$item.name}</a><br>
				{/foreach}
				</div>
			</div>

			<div style="float: left; margin: 2px; padding: 2px; position: relative; width: 570px; min-height: 450px;" class="{$status_color}">
				{* START CONTENT *}
				{include file="$CONTENT_INCLUDE"}
				{* END CONTENT *}
			</div>
			<div class="spacer"></div>
			{* END MENU / CONTENT BLOCK *}
		</div>

		{* save, delete commands *}
		<div style="margin: 2px; padding: 2px; position: relative;" class="buttongroup">
			<div style="padding: 2px; position: relative; height: 20px;">
				<input type="button" name="new" value="{t}New{/t}" OnClick="document.{$form_name}.action.value='new';document.{$form_name}.action_yes.value=confirm('{t}Do you want to create a new entry entry?{/t}');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				<input type="button" name="save" value="{t}Save{/t}" OnClick="document.{$form_name}.action.value='save';document.{$form_name}.submit();" {if $page_acl < 60}disabled{/if}>
				{if $show_delete_button && !$show_undelete_button}
				<input type="button" name="delete" value="{t}Delete{/t}" OnClick="document.{$form_name}.action.value='delete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to delete this entry?{/t}');document.{$form_name}.action_id.value={$primary_key};if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
				{/if}
				{if $show_delete_button && $show_undelete_button}
				<input type="button" name="un_delete" value="{t}Un-Delete{/t}" OnClick="document.{$form_name}.action.value='undelete';document.{$form_name}.action_yes.value=confirm('{t}Do you want to undelete this entry?{/t}');document.{$form_name}.action_id.value={$primary_key};if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 80}disabled{/if}>
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
