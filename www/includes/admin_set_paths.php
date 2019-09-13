<?php declare(strict_types=1);
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
$PAGE_FILE_NAME = str_replace(".php", "", $cms->page_name);
// set include & template names
if (!isset($CONTENT_INCLUDE)) {
	$CONTENT_INCLUDE = $PAGE_FILE_NAME.'.tpl';
}
$FORM_NAME = !isset($FORM_NAME) || !$FORM_NAME ? str_replace(".php", "", $cms->page_name) : $FORM_NAME;
// set local page title
$L_TITLE = ucfirst(str_replace('_', ' ', $cms->getPageName(1))).(defined(G_TITLE) ? ' - '.G_TITLE : '');
// strip tpl and replace it with inc
// php include file per page
$cms->INC_TEMPLATE_NAME = str_replace(".tpl", ".php", $CONTENT_INCLUDE);
// javascript include per page
$cms->JS_TEMPLATE_NAME = str_replace(".tpl", ".js", $CONTENT_INCLUDE);
// css per page
$cms->CSS_TEMPLATE_NAME = str_replace(".tpl", ".css", $CONTENT_INCLUDE);
// special CSS file
$cms->CSS_SPECIAL_TEMPLATE_NAME = $CSS_NAME;
// special JS file
$cms->JS_SPECIAL_TEMPLATE_NAME = $JS_NAME;
// compile & cache id
$cms->CACHE_ID = isset($CACHE_ID) ? $CACHE_ID : CACHE_ID;
$cms->COMPILE_ID = isset($COMPILE_ID) ? $COMPILE_ID : CACHE_ID;

// set basic template path (tmp)
$cms->includes = BASE.INCLUDES; // no longer in templates, only global
$cms->template_path = BASE.INCLUDES.TEMPLATES.CONTENT_PATH;
if ($smarty) {
	$smarty->setTemplateDir($cms->template_path);
}
if (isset($LANGUAGE_FOLDER)) {
	$cms->lang_dir = $LANGUAGE_FOLDER;
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
// check if we have an external file with the template name
if (file_exists($cms->includes.$cms->INC_TEMPLATE_NAME) &&
	is_file($cms->includes.$cms->INC_TEMPLATE_NAME)
) {
	include($cms->includes.$cms->INC_TEMPLATE_NAME);
}
// only CSS/JS/etc include stuff if we have non AJAX page
if (isset($AJAX_PAGE) && !$AJAX_PAGE) {
	// check for template include
	if (isset($USE_INCLUDE_TEMPLATE) && $USE_INCLUDE_TEMPLATE === true && !isset($TEMPLATE_NAME)) {
		$TEMPLATE_NAME = $CONTENT_INCLUDE;
		// add to cache & compile id
		$cms->COMPILE_ID .= '_'.$TEMPLATE_NAME;
		$cms->CACHE_ID .= '_'.$TEMPLATE_NAME;
	}
	// additional per page Javascript include
	$cms->JS_INCLUDE = '';
	if (file_exists($cms->javascript.$cms->JS_TEMPLATE_NAME) && is_file($cms->javascript.$cms->JS_TEMPLATE_NAME)) {
		$cms->JS_INCLUDE = $cms->javascript.$cms->JS_TEMPLATE_NAME;
	}
	// per page css file
	$cms->CSS_INCLUDE = '';
	if (file_exists($cms->css.$cms->CSS_TEMPLATE_NAME) && is_file($cms->css.$cms->CSS_TEMPLATE_NAME)) {
		$cms->CSS_INCLUDE = $cms->css.$cms->CSS_TEMPLATE_NAME;
	}
	// optional CSS file
	$cms->CSS_SPECIAL_INCLUDE = '';
	if (file_exists($cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME) && is_file($cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME)) {
		$cms->CSS_SPECIAL_INCLUDE = $cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME;
	}
	// optional JS file
	$cms->JS_SPECIAL_INCLUDE = '';
	if (file_exists($cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME) && is_file($cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME)) {
		$cms->JS_SPECIAL_INCLUDE = $cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME;
	}
	if ($smarty) {
		// check if template names exist
		if (!file_exists($smarty->getTemplateDir()[0].DS.$MASTER_TEMPLATE_NAME)) {
			// abort if master template could not be found
			exit('MASTER TEMPLATE: '.$MASTER_TEMPLATE_NAME.' could not be found');
		}
		if (isset($TEMPLATE_NAME) && !file_exists($smarty->getTemplateDir()[0].DS.$TEMPLATE_NAME)) {
			exit('INCLUDE TEMPLATE: '.$TEMPLATE_NAME.' could not be found');
		}
	}
}

// if the lang folder is different to the default one
// if the default lang is not like the lang given, switch lang
if (false === strstr(BASE.INCLUDES.LANG.CONTENT_PATH, $cms->lang_dir) ||
	strcasecmp(defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG, $lang)
) {
	$cms->debug('LANG', 'Orig: '.BASE.INCLUDES.LANG.CONTENT_PATH.', New: '.$cms->lang_dir.' | Orig Lang: '.(defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG).', New Lang: '.$lang);
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

if (isset($AJAX_PAGE) && !$AJAX_PAGE) {
	// javascript translate data as template for auto translate
	if (empty($TEMPLATE_TRANSLATE)) {
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
	if ($smarty && !file_exists($smarty->getTemplateDir()[0].DS.$TEMPLATE_TRANSLATE)) {
		unset($TEMPLATE_TRANSLATE);
	}
}

//	$cms->debug("LANGUAGE", "L: $lang | ".$cms->lang_dir." | MO File: ".$cms->l->mofile);
$cms->debug("LANGUAGE", "SL: ".$_SESSION['DEFAULT_CHARSET']." | ".$_SESSION['LANG']." | ".$_SESSION['DEFAULT_LANG']);
if ($smarty) {
	$cms->debug("TEMPLATE", "P: ".$smarty->getTemplateDir()[0]);
}

// __END__
