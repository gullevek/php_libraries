<?
	/********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2003/06/10
	* SHORT DESCRIPTION:
	*	central include for all edit_pages
	*	 - edit_groups.php
	*	 - edit_languages.php
	*	 - edit_pages.php
	*	 - edit_schemes.php
	*	 - edit_users.php
	*    - edit_visible_group.php
	* HISTORY:
	* 2005/06/30 (cs) remove color settings, they are in CSS File now
	* 2005/06/22 (cs) moved load of config array into form class, set lang and lang is must set var for form class; removed the page name setting, moved it into the form class, remove all HTML from main page
	* 2004/09/30 (cs) changed layout to fit default layout & changed LIBS, etc
	* 2003-06-10: creation of this page
	*********************************************************************/

	$DEBUG_ALL = 1;
	$DB_DEBUG = 1;

	extract($_POST, EXTR_SKIP);

	$table_width = 750;
	// this is for certain CMS modules that set a relative path
	define(REL_PATH, '');

	ob_start();
	include("config.inc");
	// overrride debug flags
	if (!DEBUG)
	{
		$DEBUG_ALL = 0;
		$PRINT_ALL = 0;
		$DB_DEBUG = 0;
		$ECHO_ALL = 0;
//		$DEBUG_TMPL = 0;
	}
	// set session name here
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
	// login class
	require(LIBS."Class.Login.inc");
	// form generate for edit interface
	require(LIBS."Class.Form.Generate.inc");
	// Smarty: and the small extend for l10n calls
	require(LIBS.'Class.Smarty.Extend.inc');

	// set default lang if not set otherwise
	if (!$lang)
		$lang = DEFAULT_LANG;
	// should be utf8
	header("Content-type: text/html; charset=".DEFAULT_ENCODING);
	ob_end_flush();
	$login = new login($DB_CONFIG[LOGIN_DB], $lang);

	// create form class
	$form = new form($DB_CONFIG[MAIN_DB], $lang);
	$form->db_exec("SET search_path TO ".LOGIN_DB_SCHEMA);
	if ($form->mobile_phone)
	{
		echo "I am sorry, but this page cannot be viewed by a mobile phone";
		exit;
	}
	// smarty template engine (extended Translation version)
	$smarty = new SmartyML($lang);
	
	if (TARGET == 'live' || TARGET == 'remote')
	{
		// login
		$login->debug_output_all = DEBUG ? 1 : 0;
		$login->echo_output_all = 0;
		$login->print_output_all = DEBUG ? 1 : 0;
		// form
		$form->debug_output_all = DEBUG ? 1 : 0;
		$form->echo_output_all = 0;
		$form->print_output_all = DEBUG ? 1 : 0;
	}
	// set the template dir
	// WARNING: this has a special check for the mailing tool layout (old layout)
	if (defined('LAYOUT'))
	{
		$smarty->setTemplateDir(LAYOUT.DEFAULT_TEMPLATE.TEMPLATES);
		$DATA['css'] = LAYOUT.DEFAULT_TEMPLATE.CSS;
		$DATA['js'] = LAYOUT.DEFAULT_TEMPLATE.JS;
	}
	else
	{
		$smarty->setTemplateDir(TEMPLATES.DEFAULT_TEMPLATE);
		$DATA['css'] = CSS.DEFAULT_TEMPLATE;
		$DATA['js'] = JS.DEFAULT_TEMPLATE;
	}

	// space for setting special debug flags
	$login->debug_output_all = 1;

	// define edit logging function. should be in a special edit interface class later
	// METHOD: EditLog()
	// PARAMS: event -> any kind of event description, data -> any kind of data related to that event
	// RETURN: none
	// DESC:   writes all action vars plus other info into edit_log table
	function EditLog($event = '', $data = '')
	{
		$q = "INSERT INTO edit_log ";
		$q .= "(euid, event_date, ip, event, data, page) ";
		$q .= "VALUES (".$_SESSION['EUID'].", NOW(), '".$_SERVER["REMOTE_ADDR"]."', '".$GLOBALS['form']->db_escape_string($event)."', '".$GLOBALS['form']->db_escape_string($data)."', '".$GLOBALS['form']->get_page_name()."')";
	}

	// log backend data
    // data part creation
    $data = array (
        '_SESSION' => $_SESSION,
        '_GET' => $_GET,
        '_POST' => $_POST,
        '_FILES' => $_FILES
    );
    // log action
    EditLog('Edit Submit', serialize($data));

	$form->form_procedure_load(${$form->archive_pk_name});
	$form->form_procedure_new();
	$form->form_procedure_save();
	$form->form_procedure_delete();
	$form->form_procedure_delete_from_element_list($element_list, $remove_name);

	// define all needed smarty stuff for the general HTML/page building
	$HEADER['CSS'] = CSS;
	$HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;
	$HEADER['JS'] = JS;
	$HEADER['STYLESHEET'] = $EDIT_STYLESHEET;
	$HEADER['JAVASCRIPT'] = $EDIT_JAVASCRIPT;

	$DATA['table_width'] = $table_width;

	// write out error / status messages
	$messages[] = $form->form_error_msg();
	$DATA['form_error_msg'] = $messages;

	// MENU START
	// request some session vars
	if (!$HEADER_COLOR)
		$DATA['HEADER_COLOR'] = "#E0E2FF";
	else
		$DATA['HEADER_COLOR'] = $_SESSION['HEADER_COLOR'];
	$DATA['USER_NAME'] = $_SESSION["USER_NAME"];
	$DATA['EUID'] = $_SESSION["EUID"];
	$DATA['GROUP_NAME'] = $_SESSION["GROUP_NAME"];
	$DATA['GROUP_LEVEL'] = $_SESSION["GROUP_LEVEL"];
	$PAGES = $_SESSION["PAGES"];

//$form->debug('menu', $form->print_ar($PAGES));

	// baue nav aus $PAGES ...
	for ($i = 0; $i < count($PAGES); $i ++)
	{
		if ($PAGES[$i]["menu"] && $PAGES[$i]["online"])
		{
			$menuarray[] = $PAGES[$i];
		} 
	}

	// split point for nav points
	$COUNT_NAV_POINTS = count($menuarray);
	$SPLIT_FACTOR = 3;
	$START_SPLIT_COUNT = 3;
	// WTF ?? I dunno what I am doing here ...
	for ($i = 9; $i < $COUNT_NAV_POINTS; $i += $START_SPLIT_COUNT)
	{
		if ($COUNT_NAV_POINTS > $i)
			$SPLIT_FACTOR += 1;
	}

	for ($i = 1; $i <= count($menuarray); $i ++)
	{
		// do that for new array
		$j = $i - 1;
		$menu_data[$j]['pagename'] = htmlentities($menuarray[($i-1)]["page_name"]);
		$menu_data[$j]['filename'] = $menuarray[($i-1)]["filename"].$menuarray[($i-1)]["query_string"];
		if ($i == 1 || !(($i - 1) % $SPLIT_FACTOR))
		{
			$menu_data[$j]['splitfactor_in'] = 1;
		}
		if ($menuarray[($i - 1)]["filename"] == $form->get_page_name())
		{
			$position = $i - 1; 
			$menu_data[$j]['position'] = 1;
		}
		else
		{
			// add query stuff
			// HAS TO DONE LATER ... set urlencode, etc ...
			// check if popup needed
			if ($menuarray[($i - 1)]["popup"])
			{
				$menu_data[$j]['popup'] = 1;
				$menu_data[$j]['rand'] = uniqid(rand());
				$menu_data[$j]['width'] = $menuarray[($i-1)]["popup_x"];
				$menu_data[$j]['height'] = $menuarray[($i-1)]["popup_y"];
			} // popup or not
		} // highlight or not
		if (!($i % $SPLIT_FACTOR) || (($i + 1) > count($menuarray)))
		{
			$menu_data[$j]['splitfactor_out'] = 1;
		} // split
	} // for
	$DATA['menu_data'] = $menu_data;
	$DATA['page_name'] = $menuarray[$position]["page_name"];
	$L_TITLE = $DATA['page_name'];
	// html title
	$HEADER['HTML_TITLE'] = ((!$L_TITLE) ? $form->l->__($G_TITLE) : $form->l->__($L_TITLE));
	// END MENU
	// LOAD AND NEW
	$DATA['load'] = $form->form_create_load();
	$DATA['new'] = $form->form_create_new();
	// SHOW DATA PART
	if ($form->yes)
	{
		$DATA['form_yes'] = $form->yes;
		$DATA['form_my_page_name'] = $form->my_page_name;

		// depending on the "get_page_name()" I show different stuff
		switch ($form->my_page_name)
		{
			case "edit_users":
				$elements[] = $form->form_create_element("login_error_count");
				$elements[] = $form->form_create_element("login_error_date_last");
				$elements[] = $form->form_create_element("login_error_date_first");
				$elements[] = $form->form_create_element("enabled");
				$elements[] = $form->form_create_element("username");
				$elements[] = $form->form_create_element("password");
				$elements[] = $form->form_create_element("password_change_interval");
				$elements[] = $form->form_create_element("email");
				$elements[] = $form->form_create_element("edit_group_id");
				$elements[] = $form->form_create_element("edit_access_right_id");
				$elements[] = $form->form_create_element("strict");
				$elements[] = $form->form_create_element("locked");
				$elements[] = $form->form_create_element("admin");
				$elements[] = $form->form_create_element("debug");
				$elements[] = $form->form_create_element("db_debug");
				$elements[] = $form->form_create_element("edit_language_id");
				$elements[] = $form->form_create_element("edit_scheme_id");
				$elements[] = $form->form_show_list_table("edit_access_user");
			break;
			case "edit_schemes":
				$elements[] = $form->form_create_element("enabled");
				$elements[] = $form->form_create_element("name");
				$elements[] = $form->form_create_element("header_color");
				$elements[] = $form->form_create_element("template");
			break;
			case "edit_pages":
				if (!$form->table_array["edit_page_id"]["value"])
				{
					$q = "DELETE FROM temp_files";
					$form->db_exec($q);
					// gets all files in the current dir ending with .php
					$crap = exec("ls *.php", $output, $status);
					// now get all that are NOT in de DB
					$q = "INSERT INTO temp_files VALUES ";
					for ($i = 0; $i < count($output); $i ++)
					{
						$t_q = "('".$form->db_escape_string($output[$i])."')";
						$form->db_exec($q.$t_q, 'NULL');
					}
					$elements[] = $form->form_create_element("filename");
				}
				else // show file menu
				{
					// just show name of file ...
					$DATA['filename_exist'] = 1;
					$DATA['filename'] = $form->table_array["filename"]["value"];
				} // File Name View IF
				$elements[] = $form->form_create_element("name");
//				$elements[] = $form->form_create_element("tag");
//				$elements[] = $form->form_create_element("min_acl");
				$elements[] = $form->form_create_element("order_number");
				$elements[] = $form->form_create_element("online");
				$elements[] = $form->form_create_element("menu");
				$elements[] = $form->form_show_list_table("edit_query_string");
				$elements[] = $form->form_create_element("popup");
				$elements[] = $form->form_create_element("popup_x");
				$elements[] = $form->form_create_element("popup_y");
				$elements[] = $form->form_show_reference_table("edit_visible_group");
				$elements[] = $form->form_show_reference_table("edit_menu_group");
			break;
			case "edit_languages":
				$elements[] = $form->form_create_element("enabled");
				$elements[] = $form->form_create_element("short_name");
				$elements[] = $form->form_create_element("long_name");
				$elements[] = $form->form_create_element("iso_name");
			break;
			case "edit_groups":
				$elements[] = $form->form_create_element("enabled");
				$elements[] = $form->form_create_element("name");
				$elements[] = $form->form_create_element("edit_access_right_id");
				$elements[] = $form->form_create_element("edit_scheme_id");
				$elements[] = $form->form_show_list_table("edit_page_access");
			break;
			case "edit_visible_group":
				$elements[] = $form->form_create_element("name");
				$elements[] = $form->form_create_element("flag");
			break;
			case "edit_menu_group":
				$elements[] = $form->form_create_element("name");
				$elements[] = $form->form_create_element("flag");
				$elements[] = $form->form_create_element("order_number");
			break;
			case "edit_access":
				$elements[] = $form->form_create_element("name");
				$elements[] = $form->form_create_element("color");
				$elements[] = $form->form_create_element("description");
			break;
			default:
				print "NO NO NOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO!";
			break;
		}
//$form->debug('edit', "Elements: <pre>".$form->print_ar($elements));
		$DATA['elements'] = $elements;
		$DATA['hidden'] = $form->form_create_hidden_fields();
		$DATA['save_delete'] = $form->form_create_save_delete();
	} 

	// debug data, if DEBUG flag is on, this data is print out
	$DEBUG_DATA['DEBUG'] = $DEBUG_TMPL;

	// create main data array
	$CONTENT_DATA = array_merge($HEADER, $DATA, $DEBUG_DATA);
	// data is 1:1 mapping (all vars, values, etc)
	while (list($key, $value) = each($CONTENT_DATA))
	{
		$smarty->assign($key, $value);
	}

//	$smarty->assign('popup_page', 'mt_popup_'.$body_part.'.tpl');

	$smarty->display('edit_body.tpl');

	// debug output
	echo $login->print_error_msg();
	echo $form->print_error_msg();
?>
