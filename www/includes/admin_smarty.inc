<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2005/07/12
* SHORT DESCRIPTION:
* default smarty vars, and create output template for smarty
* HISTORY:
*********************************************************************/

// trigger flags
$cms->HEADER['USE_PROTOTYPE'] = isset($USE_PROTOTYPE) ? $USE_PROTOTYPE : USE_PROTOTYPE;
// scriptacolous, can only be used with prototype
if ($cms->HEADER['USE_PROTOTYPE']) {
	$cms->HEADER['USE_SCRIPTACULOUS'] = isset($USE_SCRIPTACULOUS) ? $USE_SCRIPTACULOUS : USE_SCRIPTACULOUS;
}
// jquery and prototype should not be used together
$cms->HEADER['USE_JQUERY'] = isset($USE_JQUERY) ? $USE_JQUERY : USE_JQUERY; // don't use either of those two together

// the actual include files for javascript (per page)
$cms->HEADER['JS_INCLUDE'] = $cms->JS_INCLUDE;
$cms->HEADER['CSS_INCLUDE'] = $cms->CSS_INCLUDE;
$cms->HEADER['CSS_SPECIAL_INCLUDE'] = $cms->CSS_SPECIAL_INCLUDE;
$cms->HEADER['JS_SPECIAL_INCLUDE'] = $cms->JS_SPECIAL_INCLUDE;
// paths to the files
$cms->DATA['includes'] = $cms->includes;
$cms->DATA['js'] = $cms->javascript;
$cms->DATA['css'] = $cms->css;
$cms->DATA['pictures'] = $cms->pictures;

// default CMS settings
// define all needed smarty stuff for the general HTML/page building
$cms->HEADER['CSS'] = CSS;
$cms->HEADER['JS'] = JS;
$cms->HEADER['ENCODING'] = $encoding;
$cms->HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;
$cms->HEADER['STYLESHEET'] = isset($ADMIN_STYLESHEET) ? $ADMIN_STYLESHEET : ADMIN_STYLESHEET;
$cms->HEADER['JAVASCRIPT'] = isset($ADMIN_JAVASCRIPT) ? $ADMIN_JAVASCRIPT : ADMIN_JAVASCRIPT;
// html title
$cms->HEADER['HTML_TITLE'] = isset($L_TITLE) ? $cms->l->__($L_TITLE) : $cms->l->__(G_TITLE);
$cms->DATA['table_width'] = isset($PAGE_WIDTH) ? $PAGE_WIDTH : PAGE_WIDTH;

// messages = array('msg' =>, 'class' => 'error/warning/...')
$cms->DATA['messages'] = $cms->messages;

// top menu
$cms->DATA['nav_menu'] = $cms->adbTopMenu();
// the page name
$cms->DATA['page_name'] = $cms->page_name;
// user name
$cms->DATA['USER_NAME'] = $_SESSION['USER_NAME'];
// the template part to include into the body
$cms->DATA['TEMPLATE_NAME'] = $TEMPLATE_NAME;
$cms->DATA['CONTENT_INCLUDE'] = $CONTENT_INCLUDE;
// LANG
$cms->DATA['LANG'] = $lang;
$cms->DATA['TINYMCE_LANG'] = $lang_short;

// debug data, if DEBUG flag is on, this data is print out
$cms->DEBUG_DATA['debug_error_msg'] = $cms->runningTime();
$cms->DEBUG_DATA['DEBUG'] = @$DEBUG_TMPL;

// create main data array
$cms->CONTENT_DATA = array_merge($cms->HEADER, $cms->DATA, $cms->DEBUG_DATA);
// data is 1:1 mapping (all vars, values, etc)
foreach ($cms->CONTENT_DATA as $key => $value) {
	$smarty->assign($key, $value);
}
if (is_dir(BASE.TEMPLATES_C)) {
	$smarty->setCompileDir(BASE.TEMPLATES_C);
}
if (is_dir(BASE.CACHE)) {
	$smarty->setCacheDir(BASE.CACHE);
}
$smarty->display(
	$MASTER_TEMPLATE_NAME,
	$cms->CACHE_ID.($cms->CACHE_ID ? '_' : '').$lang,
	$cms->COMPILE_ID.($cms->COMPILE_ID ? '_' : '').$lang
);

// __END__
