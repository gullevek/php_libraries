{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* edit body part
	* HISTORY:
	********************************************************************
*}

<div style="position:relative; width: {$table_width}px; margin-top: 5px; margin-bottom: 5px; top: 0px; left: 0px;">
{*	<div style="position: absolute; top: 0px; left: 0px; width: 240px; height: 100%; z-index: 1; overflow: auto; border: 1px solid blue;">

left

	</div> *}
	<div style="position: relative; top: 0px; left: 0px; width: 790px; z-index: 2; margin: 2px; padding: 2px;">
		<div style="margin-bottom: 2px; padding: 2px; position: relative; border: 1px solid #e5ddba;">
			<div style="margin-bottom: 2px; padding: 2px; font-size: 12px; font-weight: bold;">
				Status
			</div>
			<div id="index_issues_data" style="margin-bottom: 2px; padding: 2px; border: 1px solid gray;">
				<b>Shops</b><br>
				Shops Flagged Delete: {$shop_delete_flagged}<br>
				Shops Flagged Offline: {$shop_offline_flagged}<br>
				Shops Flagged Online: {$shop_online_flagged}<br>
				Shops without any categories: {$shop_no_category}<br>
				<br>
				<b>Products</b><br>
				Products Flagged no image: {$products_no_image_flagged}<br>
				Products Flagged offline: {$products_offline_flagged}<br>
				Products Flagged online: {$products_online_flagged}<br>
				Products without any color tags: {$products_no_color_tags}<br>
				<br>
				<b>Categories</b><br>
				Categories without Products: {$categories_no_products}<br>
			</div>
		</div>
		{ * shows shops to be uploaded *}
		{if $queued_items}
		<form method="post" name="{$form_name}">
		<div style="margin-bottom: 2px; padding: 2px; position: relative; border: 2px solid #8a1113;">
			<div style="margin-bottom: 2px; padding: 2px; position: relative; height: 30px;">
				<div style="position: absolute; top: 2px; left: 2px; height: 20px; width: 140px; padding: 2px; font-size: 12px; font-weight: bold;">
					Items to push live
				</div>
				<div style="position: absolute; top: 2px; right: 2px; height: 25px; width: 450px; padding: 2px; text-align: right;">
					<input type="button" id="cancel_push_live" name="cancel_push_live" value="Delete from live Queue" OnClick="document.{$form_name}.action.value='cancel_push_live';document.{$form_name}.action_yes.value=confirm('Do you want to want to delete the selected items from the live queue?');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $page_acl < 90}disabled{/if}>
					<input type="button" id="push_live" name="push_live" value="Push data to live server" OnClick="document.{$form_name}.action.value='push_live';document.{$form_name}.action_yes.value=confirm('Do you want to push all the changes to the live server?');if (document.{$form_name}.action_yes.value == 'true') document.{$form_name}.submit();" {if $locked || $page_acl < 90}disabled{/if}>
				</div>
			</div>
			<div id="index_issues_data" style="margin-bottom: 2px; padding: 2px; border: 1px solid gray;">
				<div style="clear: both;"></div>
				<div style="float: left; width: 40px; text-align: right; font-weight: bold; padding-right: 2px; border-right: 1px solid gray; border-bottom: 1px solid gray;">del</div>
				<div style="float: left; width: 30px; font-weight: bold; border-bottom: 1px solid gray; padding-left: 2px;">GK</div>
				<div style="float: left; width: 140px; font-weight: bold; border-bottom: 1px solid gray;">Date</div>
				<div style="float: left; width: 70px; font-weight: bold; border-bottom: 1px solid gray;">Type</div>
				<div style="float: left; width: 50px; font-weight: bold; border-bottom: 1px solid gray;">Action</div>
				<div style="float: left; width: 80px; font-weight: bold; border-bottom: 1px solid gray;">Target</div>
				<div style="float: left; width: 80px; font-weight: bold; border-bottom: 1px solid gray;">Key</div>
				<div style="float: left; width: 170px; font-weight: bold; border-bottom: 1px solid gray;">Key ID</div>
				<div style="float: left; width: 30px; font-weight: bold; border-bottom: 1px solid gray;">Asc</div>
				<div style="float: left; width: 50px; font-weight: bold; border-bottom: 1px solid gray;">Lock</div>
				<div style="clear: both;"></div>
				{foreach from=$queued_items key=key item=item}
					<div style="float: left; width: 40px; text-align: right; padding-right: 2px; margin-right: 2px; border-right: 1px solid gray; background-color: {$item.color};">{if $item.checkbox}<input type="checkbox" name="group_key[]" value="{$item.group_key}" style="height: 9px;" {if $item.associate != '-'}disabled{/if}>{else}&nbsp;{/if}</div>
					<div style="float: left; width: 30px; background-color: {$item.color};" {popup width="30" caption="ID" text="`$key`"}>{$item.group_key}</div>
					<div style="float: left; width: 140px; background-color: {$item.color};">{$item.date_created}</div>
					<div style="float: left; width: 70px; background-color: {$item.color}; font-weight: bold; color: {$item.type_color};" {popup width="450" caption="Data" text="`$item.data`"}>{$item.type}</div>
					<div style="float: left; width: 50px; background-color: {$item.color};">{$item.action}</div>
					<div style="float: left; width: 80px; background-color: {$item.color};">{$item.target}</div>
					<div style="float: left; width: 80px; background-color: {$item.color};">{$item.key_name}</div>
					<div style="float: left; width: 170px; background-color: {$item.color};" {popup width="250" caption="Key ID" text="`$item.key_value`"}>{$item.key_value}</div>
					<div style="float: left; width: 30px; background-color: {$item.color};">{$item.associate}</div>
					<div style="float: left; width: 50px; background-color: {$item.color}; font-weight: bold; color: red;">{$item.locked}</div>
					<div style="clear: both;"></div>
				{/foreach}
			</div>
		</div>
		<input type="hidden" id="action" name="action" value="">
		<input type="hidden" id="action_yes" name="action_yes" value="">
		<input type="hidden" name="action_loaded" value="true">
		</form>
		{/if}
		<div style="margin-bottom: 2px; padding: 2px; position: relative; border: 1px solid #e5ddba;">
			<div style="margin-bottom: 2px; padding: 2px; font-size: 12px; font-weight: bold;">
				Issues
			</div>
			<div id="index_issues_data" style="margin-bottom: 2px; padding: 2px; border: 1px solid gray;">
				{$issue_data}
			</div>
		</div>

		<div style="margin-bottom: 2px; padding: 2px; position: relative; border: 1px solid #e5ddba;">
			<div style="margin-bottom: 2px; padding: 2px; position: relative; height: 26px;">
				<div style="position: absolute; top: 2px; left: 2px; height: 20px; width: 250px; padding: 2px; font-size: 12px; font-weight: bold;">
					Quick Search
				</div>
				<div style="position: absolute; top: 2px; right: 2px; height: 20px; width: 350px; padding: 2px; text-align: right;" {popup width="100" text="Search in Key, Name, Prefecture, Category"}>
					<span id="search_status">Search for Key / String:</span> <input type="text" id="quick_search_shop" name="quick_search_shop" value="" onKeyup="QuickSearchInput(); return false;">
				</div>
			</div>
			<div id="quick_search_results" style="margin-bottom: 2px; padding: 2px; position: relative; border: 1px solid gray;">
				Enter something to search into the search field
			</div>
		</div>
	</div>
</div>
