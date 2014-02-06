{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* prints out the elements for the form in the edit interface
	* HISTORY:
	********************************************************************
*}
{foreach from=$elements item=element key=key name=loop}
	<tr>
		<td class="edit_fgcolor" class="normal" valign="top">
			{$element.output_name}
		</td>
		<td class="{$element.color}" class="normal">
		{* here is depending on type the content data *}
		{if $element.type == 'binary' || $element.type == 'radio_array'}
			{html_radios values=$element.data.value output=$element.data.output name=$element.data.name selected=$element.data.checked separator=$element.data.separator}
		{/if}
		{if $element.type == 'checkbox'}
			{html_checkboxes values=$element.data.value output=$element.data.output selected=$element.data.checked}
		{/if}
		{if $element.type == 'text'}
			<input type="text" name="{$element.data.name}" value="{$element.data.value}"{if $element.data.size} size="{$element.data.size}"{/if}{if $element.data.length} maxlength="{$element.data.length}"{/if}>	
		{/if}
		{if $element.type == 'password'}
			Password: <input type="password" name="{$element.data.name}" {if $element.data.size} size="{$element.data.size}"{/if}{if $element.data.length} maxlength="{$element.data.length}"{/if}> {if $element.data.HIDDEN_value}{t}Password set{/t}{/if}<br>
			Confirm: <input type="password" name="CONFIRM_{$element.data.name}" {if $element.data.size} size="{$element.data.size}"{/if}{if $element.data.length} maxlength="{$element.data.length}"{/if}> 
			<input type="hidden" name="HIDDEN_{$element.data.name}" value="{$element.data.HIDDEN_value}">
		{/if}
		{if $element.type == 'date'}
			<input type="text" name="{$element.data.name}" value="{$element.data.value}" size="10" maxlength="10">
		{/if}
		{if $element.type == 'textarea'}
			<textarea name="{$element.data.name}"{if $element.data.rows} rows="{$element.data.rows}"{/if}{if $element.data.cols} cols="{$element.data.cols}"{/if}>{$element.data.value}</textarea>
		{/if}
		{if $element.type == 'drop_down'}
			{html_options name=$element.data.name values=$element.data.value output=$element.data.output selected=$element.data.selected}
			{if $drop_down_input}
				&nbsp;&nbsp;&nbsp;<input type="text" name="{$element.data.input_name}" value="{$element.data.input_value}"{if $element.data.input_size} size="{$element.data.input_size}"{/if}{if $element.data.input_length} maxlength="{$element.data.input_length}"{/if}>
			{/if}
		{/if}
		{if $element.type == 'media'}
			{* not yet implemented *}
		{/if}
		{if $element.type == 'order'}
{*			<input type="button" name="order" value="{$element.data.output_name}" OnClick="pop('order.php?col_name={$element.data.col_name}&table_name={$element.data.table_name}&where={$element.data.query}','Order','status=no,scrollbars=yes,width=700,height=500');"> *}
			<input type="button" name="order" value="{$element.data.output_name}" OnClick="pop('edit_order.php?table_name={$element.data.table_name}&where={$element.data.query}','Order','status=no,scrollbars=yes,width=700,height=500');">
			<input type="hidden" name="{$element.data.name}" value="{$element.data.value}">
		{/if}
		{if $element.type == 'file'}
			<input type="file" name="{$element.data.name}_file">
			{if $element.data.content}
				<br><a href="{$element.data.url}" target="_blank">{$element.data.output}</a>';
				<br><input type="checkbox" name="{$element.data.name}_delete" value="1"> {t}delete this file{/t}
				<input type="hidden" name="{$element.data.name}" value="{$element.data.value}">
			{/if}
		{/if}
		{if $element.type == 'reference_table'}
			<select name="{$element.data.name}[]" size="{$element.data.size}" multiple>
				{html_options values=$element.data.value output=$element.data.output selected=$element.data.selected}
			</select>
		{/if}
		{if $element.type == 'element_list'}
			{* each row of data *}
			<table width="100%" border="0">
			{foreach from=$element.data.content item=line key=key}
			<tr>
				{* now each line of data *}
				<td>
					{$key}: 
				</td>
				{foreach from=$line item=line_item key=line_key}
				<td>
{* {$line_item} - {$line_key} [{$element.data.type.$line_key}] ||<br> *}
{* {$element.data.pos.$key} *}
{* {$element.data.delete_name} *}
					{if $element.data.type.$line_key == 'string'}
						{$line_item}
						<input type="hidden" name="{$line_key}[]" value="{$line_item}">
					{/if}
					{if $element.data.type.$line_key == 'hidden'}
						<input type="hidden" name="{$line_key}[]" value="{$line_item}">
					{/if}
					{if $element.data.type.$line_key == 'checkbox'}
						{html_checkboxes name=$line_key values=$element.data.element_list.$line_key output=$element.data.output_name.$line_key selected=$line_item pos=$element.data.pos.$key}
					{/if}
					{if $element.data.type.$line_key == 'radio'}
						{html_radios name=$line_key values=$element.data.element_list.$line_key output=$element.data.output_name.$line_key selected=$line_item}
					{/if}
					{if $element.data.type.$line_key == 'radio_group'}
						{$element.data.output_name.$line_key} <input type="radio" name="{$line_key}" value="{$key}" {if $line_item}checked{/if}>
					{/if}
					{if $element.data.type.$line_key == 'text'}
						{$element.data.output_name.$line_key}: <input type="text" name="{$line_key}[]" value="{$line_item}">
					{/if}
					{if $element.data.type.$line_key == 'drop_down_db'}
						{assign var="_line_key" value="`$line_key`[]"}
						{$element.data.output_name.$line_key}: {html_options name=$_line_key values=$element.data.element_list.$line_key output=$element.data.output_data.$line_key selected=$line_item}
					{/if}
					{* if there is a hidden key, set delete, but only if we have a delete string *}
					{if $element.data.type.$line_key == 'hidden' && $line_item && $element.data.delete_name}
						<input type="submit" name="remove_button" value="{t}Delete{/t}" onClick="document.edit_form.{$element.data.delete_name}.value={$line_item};document.edit_form.{$element.data.delete_name}_flag.value=confirm('{t}Do you want to remove this entry?{/t}');document.edit_form.submit();">
					{/if}
					{if $element.data.type.$line_key == 'hidden' && $element.data.enable_name && $element.data.delete && $element.data.output_name.$line_key}
						<input type="checkbox" name="{$element.data.enable_name}[{$key}]" value="1" {if $line_item}checked{/if}> {$element.data.output_name.$line_key}
					{/if}
				</td>
				{/foreach}
			</tr>
			{/foreach}
			</table>
			{if $element.data.delete_name}
			<input type="hidden" value="" name="{$element.data.delete_name}">
			<input type="hidden" value="" name="{$element.data.delete_name}_flag">
			<input type="hidden" name="remove_name[]" value="{$element.data.delete_name}">
			{/if}
			{if $element.data.enable_name}
			<input type="hidden" name="remove_name[]" value="{$element.data.enable_name}">
			<input type="hidden" name="primary_key[]" value="{$element.data.enable_name}">
			{/if}
			<input type="hidden" name="element_list[]" value="{$element.data.table_name}">
		{/if}
		</td>
	</tr>
{/foreach}
