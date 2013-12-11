{*
	= SUBVERSION DATA ===================================================
	= $HeadURL: svn://svn/development/core_data/php/www/layout/admin/default/templates/main_content_index.tpl $
	= $LastChangedBy: gullevek $
	= $LastChangedDate: 2010-09-02 11:58:10 +0900 (Thu, 02 Sep 2010) $
	= $LastChangedRevision: 3159 $
	= SUBVERSION DATA ===================================================

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

{* $Id: main_content_index.tpl 3159 2010-09-02 02:58:10Z gullevek $ *}
