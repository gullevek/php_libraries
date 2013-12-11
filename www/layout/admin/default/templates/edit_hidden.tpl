{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/html/mailing_tool/branches/version-4/templates/default/edit_hidden.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2006-12-21 11:47:45 +0900 (Thu, 21 Dec 2006) $
	= $LastChangedRevision: 1636 $
	= SUBVERSION DATA ===================================================

	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2005/06/23
	* DESCRIPTION:
	* show hidden messages
	* HISTORY:
	********************************************************************
*}
{foreach from=$hidden item=element key=key name=loop}
	<input type="hidden" name="{$element.key}" value="{$element.value}">
{/foreach}
{* $Id: edit_hidden.tpl 1636 2006-12-21 02:47:45Z gullevek $ *}
