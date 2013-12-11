{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/html/mailing_tool/branches/version-4/templates/default/edit_new.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2006-12-21 11:47:45 +0900 (Thu, 21 Dec 2006) $
	= $LastChangedRevision: 1636 $
	= SUBVERSION DATA ===================================================

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

{* $Id: edit_new.tpl 1636 2006-12-21 02:47:45Z gullevek $ *}
