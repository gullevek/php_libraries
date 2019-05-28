{*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2007/10/18
	* DESCRIPTION:
	* content main part (buttons, load, etc)
	* HISTORY:
	********************************************************************
*}

<script language="JavaScript">
<!--
var form_name = '{$form_name}';
var load_id = {if $primary_key}{$primary_key}{else}0{/if};
var show_sort = {if $show_sort}{$show_sort}{else}0{/if};
//-->
</script>

{include file=$CONTENT_INCLUDE}
