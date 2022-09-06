{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* save / delete part of edit window
	* HISTORY:
	********************************************************************
*}
	{if $save_delete.seclevel_okay}
	<tr>
		<!-- SAVE START //-->
		<td class="edit_fgcolor_alt" class="normal">
			<input type="submit" name="save" value="{$save_delete.save}">
		{if $save_delete.old_school_hidden}
			<input type="hidden" name="{$save_delete.pk_name}" value="{$save_delete.pk_value}">
		{/if}
		</td>
		<!-- SAVE END //-->
	{if $save_delete.show_delete}
		<!-- DELETE START //-->
		<td class="edit_fgcolor_delete">
			{if !$save_delete.hide_delete_checkbox}
			<input type="checkbox" name="really_delete" value="yes">&nbsp;{t}really{/t}&nbsp;
			{else}
			<input type="hidden" name="really_delete" value="yes">
			{/if}
			<input type="submit" name="delete" value="{t}Delete{/t}">
			<!-- DELETE END //-->
	{else}
		<td class="edit_fgcolor_alt" class="normal">
            &nbsp;
	{/if}
		</td>
	</tr>
	{/if}
