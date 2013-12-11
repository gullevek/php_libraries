{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/html/mailing_tool/branches/version-4/templates/default/edit_load.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2006-12-21 11:47:45 +0900 (Thu, 21 Dec 2006) $
	= $LastChangedRevision: 1636 $
	= SUBVERSION DATA ===================================================

	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* load part of template
	* HISTORY:
	********************************************************************
*}

<tr>
	<td class="edit_fgcolor_alt" class="normal">
		Load:
	</td>
	<td class="edit_fgcolor_alt" class="normal">
		<select name="{$load.t_pk_name}">
			<option value="">{t}Please choose{/t}</option>
			{html_options values=$load.pk_ids output=$load.pk_names selected=$load.pk_selected}
		</select>&nbsp;
		<input type="submit" name="archive" value="{t}Load{/t}">
	</td>
</tr>

{* $Id: edit_load.tpl 1636 2006-12-21 02:47:45Z gullevek $ *}
