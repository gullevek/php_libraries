{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/html/mailing_tool/branches/version-4/templates/default/edit_save_delete.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2006-12-21 11:47:45 +0900 (Thu, 21 Dec 2006) $
	= $LastChangedRevision: 1636 $
	= SUBVERSION DATA ===================================================

	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* save / delete part of edit window
	* HISTORY:
	********************************************************************
*}
	<tr>
	{if $save_delete.seclevel_okay}
		<td class="edit_fgcolor_alt" class="normal">
			<input type="submit" name="save" value="{$save_delete.save}">
		{if $save_delete.old_school_hidden}
			<input type="hidden" name="{$save_delete.pk_name}" value="{$save_delete.pk_value}">
		{/if}
		</td>
	{/if}
	{if $save_delete.show_delete}
		<td class="edit_fgcolor_delete">
			{if !$save_delete.hide_delete_checkbox}
			<input type="checkbox" name="really_delete" value="yes">&nbsp;{t}really{/t}&nbsp;
			{else}
			<input type="hidden" name="really_delete" value="yes">
			{/if}
			<input type="submit" name="delete" value="{t}Delete{/t}">
	{else}
		<td class="edit_fgcolor_alt" class="normal">
             &nbsp;
	{/if}
		</td>
	</tr>
{* $Id: edit_save_delete.tpl 1636 2006-12-21 02:47:45Z gullevek $ *}
