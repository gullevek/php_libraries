{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* new part
	* HISTORY:
	********************************************************************
*}

     <tr>
         <td class="edit_fgcolor_alt" class="normal">
             {t}Create new media:{/t}
         </td>
         <td class="edit_fgcolor_alt" class="normal">
	{if $new.show_checkbox}
             <input type="checkbox" name="really_new" value="yes">&nbsp;{t}really{/t}&nbsp;
	{else}
             <input type="hidden" name="really_new" value="yes">
	{/if}
             <input type="submit" name="new" value="{$new.new_name}">
         </td>
     </tr>
