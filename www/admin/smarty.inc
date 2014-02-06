<?
	/********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* CREATED: 2005/07/12
	* SHORT DESCRIPTION:
	* default smarty vars, and create output template for smarty
	* HISTORY:
	*********************************************************************/

	// trigger flags
	$cms->HEADER['USE_PROTOTYPE'] = USE_PROTOTYPE;
	// scriptacolous, can only be used with prototype
	if (USE_PROTOTYPE)
		$cms->HEADER['USE_SCRIPTACULOUS'] = USE_SCRIPTACULOUS;
	// jquery and prototype should not be used together
	$cms->HEADER['USE_JQUERY'] = USE_JQUERY; // don't use either of those two toger

	// set basic template path (tmp)
	// paths are set in the 'set_paths.inc' file
	
	// check if we have an external file with the template name
	if (file_exists($cms->includes.$cms->INC_TEMPLATE_NAME) && is_file($cms->includes.$cms->INC_TEMPLATE_NAME))
	{
		include($cms->includes.$cms->INC_TEMPLATE_NAME);
	}
	// additional per page Javascript include
	if (file_exists($cms->javascript.$cms->JS_TEMPLATE_NAME) && is_file($cms->javascript.$cms->JS_TEMPLATE_NAME))
	{
		$cms->JS_INCLUDE = $cms->javascript.$cms->JS_TEMPLATE_NAME;
	}
	// per page css file
	if (file_exists($cms->css.$cms->CSS_TEMPLATE_NAME) && is_file($cms->css.$cms->CSS_TEMPLATE_NAME))
	{
		$cms->CSS_INCLUDE = $cms->css.$cms->CSS_TEMPLATE_NAME;
	}
	// optional CSS file
	if (file_exists($cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME) && is_file($cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME))
	{
		$cms->CSS_SPECIAL_INCLUDE = $cms->css.$cms->CSS_SPECIAL_TEMPLATE_NAME;
	}
	// optional JS file
	if (file_exists($cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME) && is_file($cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME))
	{
		$cms->JS_SPECIAL_INCLUDE = $cms->javascript.$cms->JS_SPECIAL_TEMPLATE_NAME;
	}

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
	$cms->HEADER['STYLESHEET'] = $EDIT_STYLESHEET;
	$cms->HEADER['JAVASCRIPT'] = $EDIT_JAVASCRIPT;
	// html title
	$cms->HEADER['HTML_TITLE'] = ((!$L_TITLE) ? $cms->l->__($G_TITLE) : $cms->l->__($L_TITLE));
	$cms->DATA['table_width'] = $PAGE_WIDTH ? $PAGE_WIDTH : PAGE_WIDTH;

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
	$cms->DEBUG_DATA['debug_error_msg'] = $cms->running_time();
	$cms->DEBUG_DATA['DEBUG'] = $DEBUG_TMPL;
	$cms->DEBUG_DATA['Id'] = '$Id: smarty.inc 4897 2014-02-06 08:16:56Z gullevek $';

	// create main data array
	$cms->CONTENT_DATA = array_merge($cms->HEADER, $cms->DATA, $cms->DEBUG_DATA);
	// data is 1:1 mapping (all vars, values, etc)
	while (list($key, $value) = each($cms->CONTENT_DATA))
	{
		$smarty->assign($key, $value);
	}
	$smarty->display($MASTER_TEMPLATE_NAME, $TEMPLATE.$lang, $TEMPLATE.$lang);

?>
