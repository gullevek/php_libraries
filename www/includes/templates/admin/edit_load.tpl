{*
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
