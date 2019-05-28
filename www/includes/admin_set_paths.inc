<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2007/09/03
* SHORT DESCRIPTION:
* set paths & language variables
* HISTORY:
*********************************************************************/

// master template
if (!isset($MASTER_TEMPLATE_NAME)) {
	$MASTER_TEMPLATE_NAME = MASTER_TEMPLATE_NAME;
}

// just emergency fallback for language
// set encoding
if (isset($_SESSION['DEFAULT_CHARSET'])) {
	$encoding = $_SESSION['DEFAULT_CHARSET'];
} elseif (!isset($encoding)) {
	$encoding = DEFAULT_ENCODING;
}
// just emergency fallback for language
if (isset($_SESSION['DEFAULT_LANG'])) {
	$lang = $_SESSION['DEFAULT_LANG'];
} elseif (!isset($lang)) {
	$lang = defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG;
}
// create the char lang encoding
$lang_short = substr($lang, 0, 2);

// set include & template names
$CONTENT_INCLUDE = str_replace(".php", ".tpl", $cms->page_name);
$FORM_NAME = !isset($FORM_NAME) || !$FORM_NAME ? str_replace(".php", "", $cms->page_name) : $FORM_NAME;
// set local page title
$L_TITLE = ucfirst(str_replace('_', ' ', $cms->getPageName(1))).' - '.$G_TITLE;
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
$cms->includes = BASE.INCLUDES; // no longer in templates, only global
$cms->template_path = BASE.INCLUDES.TEMPLATES.CONTENT_PATH;
if ($smarty) {
	$smarty->setTemplateDir($cms->template_path);
}
if (isset($LANGUAGE_FOLDER)) {
	$cms->kang_dir = $LANGUAGE_FOLDER;
} else {
	$cms->lang_dir = BASE.INCLUDES.LANG.CONTENT_PATH; // no outside
}
$cms->javascript = LAYOUT.JS;
$cms->css = LAYOUT.CSS;
$cms->pictures = LAYOUT.IMAGES;
$cms->cache_pictures = LAYOUT.CACHE;
$cms->cache_pictures_root = ROOT.$cms->cache_pictures;
if (!is_dir($cms->cache_pictures_root)) {
	mkdir($cms->cache_pictures_root);
}

// check if template names exist
if (!file_exists($smarty->getTemplateDir()[0].DS.$MASTER_TEMPLATE_NAME)) {
	// abort if master template could not be found
	exit('MASTER TEMPLATE: '.$MASTER_TEMPLATE_NAME.' could not be found');
}
if (isset($TEMPLATE_NAME) && !file_exists($smarty->getTemplateDir()[0].DS.$TEMPLATE_NAME)) {
	exit('INCLUDE TEMPLATE: '.$TEMPLATE_NAME.' could not be found');
}

// if the lang folder is different to the default one
// if the default lang is not like the lang given, switch lang
if (false === strstr(BASE.INCLUDES.LANG.CONTENT_PATH, $cms->lang_dir) ||
	strcasecmp(defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG, $lang)
) {
	$cms->debug('LANG', 'Orig Lang: '.(defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG).', New Lang: '.$lang);
	$cms->l->l10nReloadMOfile($lang, $cms->lang_dir);
		// if we have login class
	if ($login) {
		$login->l->l10nReloadMOfile($lang, $cms->lang_dir);
	}
	// if we have smarty template class
	if ($smarty) {
		$smarty->l10n->l10nReloadMOfile($lang, $cms->lang_dir);
	}
}

// javascrip translate data as template for auto translate
if (!$TEMPLATE_TRANSLATE) {
	$TEMPLATE_TRANSLATE = 'jsTranslate_'.$lang.'.tpl';
	$cms->debug('LANG', 'Load lang: '.$lang.', for page file '.$TEMPLATE_TRANSLATE);
} else {
	// we assume we have some fixed set
	// we must add _<$lang>
	// if .tpl, put before .tpl
	// if not .tpl, add _<$lang>.tpl
	if (strpos($TEMPLATE_TRANSLATE, '.tpl')) {
		$TEMPLATE_TRANSLATE = str_replace('.tpl', '_'.$lang.'.tpl', $TEMPLATE_TRANSLATE);
	} else {
		$TEMPLATE_TRANSLATE .= '_'.$lang.'.tpl';
	}
}
// if we can't find it, dump it
if (!file_exists($smarty->getTemplateDir()[0].DS.$TEMPLATE_TRANSLATE)) {
	unset($TEMPLATE_TRANSLATE);
}

//	$cms->debug("LANGUAGE", "L: $lang | ".$cms->lang_dir." | MO File: ".$cms->l->mofile);
$cms->debug("LANGUAGE", "SL: ".$_SESSION['DEFAULT_CHARSET']." | ".$_SESSION['LANG']." | ".$_SESSION['DEFAULT_LANG']);
$cms->debug("TEMPLATE", "P: ".$smarty->getTemplateDir()[0]);

// __END__
