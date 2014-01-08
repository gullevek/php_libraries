<?
	/********************************************************************
	* $HeadURL: svn://svn/development/core_data/php/www/admin/set_paths.inc $
	* $LastChangedBy: gullevek $
	* $LastChangedDate: 2014-01-08 13:09:19 +0900 (Wed, 08 Jan 2014) $
	* $LastChangedRevision: 4807 $
	*********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* CREATED: 2007/09/03
	* SHORT DESCRIPTION:
	* set paths & language variables
	* HISTORY:
	*********************************************************************/

	// template path
	if ($_SESSION['TEMPLATE'])
		$TEMPLATE = $_SESSION['TEMPLATE'];
	elseif ($LOCAL_TEMPLATE)
		$TEMPLATE = $LOCAL_TEMPLATE;
	else
		$TEMPLATE = DEFAULT_TEMPLATE;
	$TEMPLATE_DIR = $TEMPLATE.'/';
	// master template
	$MASTER_TEMPLATE_NAME = '';
	if (!$MASTER_TEMPLATE_NAME)
		$MASTER_TEMPLATE_NAME = MASTER_TEMPLATE_NAME;

	// just emergency fallback for language
	// set encoding 
	if ($_SESSION['DEFAULT_CHARSET'])
		$encoding = $_SESSION['DEFAULT_CHARSET'];
	elseif (!$encoding)
		$encoding = DEFAULT_ENCODING;
	// just emergency fallback for language
	if ($_SESSION['DEFAULT_LANG'])
		$lang = $_SESSION['DEFAULT_LANG'];
	elseif (!$lang)
		$lang = DEFAULT_LANG;
	// create the char lang encoding
	$lang_short = substr($lang, 0, 2);

	// set include & template names
	$CONTENT_INCLUDE = str_replace(".php", ".tpl", $cms->page_name);
	$FORM_NAME = !$FORM_NAME ? str_replace(".php", "", $cms->page_name) : $FORM_NAME;
	// strip tpl and replace it with inc
	// php include file per page
	$cms->INC_TEMPLATE_NAME = str_replace(".tpl", ".inc", $CONTENT_INCLUDE);
	// javascript include per page
	$cms->JS_TEMPLATE_NAME = str_replace(".tpl", ".js", $CONTENT_INCLUDE);
	// css per page
	$cms->CSS_TEMPLATE_NAME = str_replace(".tpl", ".css", $CONTENT_INCLUDE);
	// special CSS file
	$cms->CSS_SPECIAL_TEMPLATE_NAME = $CSS_NAME;
	// special JS file
	$cms->JS_SPECIAL_TEMPLATE_NAME = $JS_NAME;

	// set basic template path (tmp)
	$smarty->setTemplateDir(LAYOUT.$TEMPLATE_DIR.TEMPLATES.'/');
	$cms->lang_dir = LAYOUT.$TEMPLATE_DIR.LANG."/";
	$cms->includes = LAYOUT.$TEMPLATE_DIR.INCLUDES."/";
	$cms->javascript = LAYOUT.$TEMPLATE_DIR.JS."/";
	$cms->css = LAYOUT.$TEMPLATE_DIR.CSS."/";
	$cms->pictures = LAYOUT.$TEMPLATE_DIR.IMAGES."/";
	$cms->cache_pictures = LAYOUT.$TEMPLATE_DIR.CACHE.IMAGES.'/';
	$cms->cache_pictures_root = ROOT.$cms->cache_pictures;
	if (!is_dir($cms->cache_pictures_root))
		mkdir($cms->cache_pictures_root);
	$template_set = $TEMPLATE;

	// if we don't find the master template, set the template path back AND set the insert template as absolute path
	if (!file_exists(ROOT.$smarty->getTemplateDir()[0]."/".MASTER_TEMPLATE_NAME))
	{
		if (file_exists(ROOT.$smarty->getTemplateDir()[0]."/".$TEMPLATE_NAME))
			$TEMPLATE_NAME = ROOT.$smarty->getTemplateDir()[0]."/".$TEMPLATE_NAME;
		$smarty->setTemplateDir(LAYOUT.DEFAULT_TEMPLATE.TEMPLATES);
		$template_set = DEFAULT_TEMPLATE;
	}
	// check if the template exists in the template path, if not, set path to to default
	elseif (!file_exists(ROOT.$smarty->getTemplateDir()[0]."/".$TEMPLATE_NAME))
	{
		$smarty->setTemplateDir(LAYOUT.DEFAULT_TEMPLATE.TEMPLATES);
		$template_set = DEFAULT_TEMPLATE;
	}
	// loop to check if dir exists
	// check if file exists, if not reset to default path

	// check if the javascript folder exists in the template path, if not fall back to default
	if (!is_dir($cms->javascript))
		$cms->javascript = LAYOUT.DEFAULT_TEMPLATE.JS."/";
	// check if lang_dir folder exists in the template path, if not fall back to default
	if (!is_dir($cms->lang_dir))
		$cms->lang_dir = LAYOUT.DEFAULT_TEMPLATE.LANG."/";
	// check if include folder exists in the template path, if not fall back to default
	if (!is_dir($cms->includes))
		$cms->includes = LAYOUT.DEFAULT_TEMPLATE.INCLUDES."/";
	// check if css folder exists in the template path, if not fall back to default
	if (!is_dir($cms->css))
		$cms->css = LAYOUT.DEFAULT_TEMPLATE.CSS."/";
	if (!is_dir($cms->pictures))
		$cms->pictures = LAYOUT.DEFAULT_TEMPLATE.PICTURES.'/';
	if (!is_dir($cms->cache_pictures))
	   	$cms->cache_pictures = LAYOUT.DEFAULT_TEMPLATE.CACHE.IMAGES.'/';

	// if the template_dir is != DEFAULT_TEMPLATE, then try to make a lang switch
	// if the default lang is not like the lang given, switch lang
	if (false === strstr(LAYOUT.DEFAULT_TEMPLATE.LANG, $cms->lang_dir) || strcasecmp(DEFAULT_LANG, $lang))
	{
		$cms->debug('LANG', 'Orig: '.LAYOUT.DEFAULT_TEMPLATE.LANG.', New: '.$cms->lang_dir.' | Orig Lang: '.DEFAULT_LANG.', New Lang: '.$lang);
		$cms->l->l10nReloadMOfile($lang, $cms->lang_dir);
		$smarty->l10n->l10nReloadMOfile($lang, $cms->lang_dir);
	}

//	$cms->debug("LANGUAGE", "L: $lang | ".$cms->lang_dir." | MO File: ".$cms->l->mofile);
	$cms->debug("LANGUAGE", "SL: ".$_SESSION['DEFAULT_CHARSET']." | ".$_SESSION['LANG']." | ".$_SESSION['DEFAULT_LANG']);
	$cms->debug("TEMPLATE", "P: ".$smarty->getTemplateDir()[0]);

	// $Id: set_paths.inc 4807 2014-01-08 04:09:19Z gullevek $
?>
