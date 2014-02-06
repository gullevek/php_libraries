{*
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
