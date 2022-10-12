<?php

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
*	 - edit_visible_group.php
* HISTORY:
* 2005/06/30 (cs) remove color settings, they are in CSS File now
* 2005/06/22 (cs) moved load of config array into form class, set lang
*                 and lang is must set var for form class; removed the
*                 page name setting, moved it into the form class,
*                 emove all HTML from main page
* 2004/09/30 (cs) changed layout to fit default layout & changed LIBS, etc
* 2003-06-10: creation of this page
*********************************************************************/

declare(strict_types=1);

$DEBUG_ALL = true;
$PRINT_ALL = true;
$ECHO_ALL = false;
$DB_DEBUG = true;

// TODO: only extract _POST data that is needed
extract($_POST, EXTR_SKIP);

ob_start();
require 'config.php';
// overrride debug flags
if (!DEBUG)	{
	$DEBUG_ALL = false;
	$PRINT_ALL = false;
	$DB_DEBUG = false;
	$ECHO_ALL = false;
}

// should be utf8
header("Content-type: text/html; charset=" . DEFAULT_ENCODING);
// start session
$session = new \CoreLibs\Create\Session(EDIT_SESSION_NAME);
// init logger
$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => LOG_FILE_ID . 'EditBase',
	'print_file_date' => true,
	'per_class' => true,
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL,
	'print_all' => $PRINT_ALL,
]);
// db connection
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
// login page
$login = new CoreLibs\ACL\Login($db, $log, $session);
// lang, path, domain
// pre auto detect language after login
$locale = \CoreLibs\Language\GetLocale::setLocale();
// set lang and pass to smarty/backend
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
);
// flush and start
ob_end_flush();
// turn off set log per class
$log->setLogPer('class', false);
// create form class
$form = new CoreLibs\Output\Form\Generate(DB_CONFIG, $log, $l10n, $locale);
if ($form->mobile_phone) {
	echo "I am sorry, but this page cannot be viewed by a mobile phone";
	exit;
}
// smarty template engine (extended Translation version)
$smarty = new CoreLibs\Template\SmartyExtend($l10n, $locale);

// $form->log->debug('POST', $form->log->prAr($_POST));

if (TARGET == 'live' || TARGET == 'remote') {
	// login
	$login->log->setLogLevelAll('debug', DEBUG ? true : false);
	$login->log->setLogLevelAll('echo', false);
	$login->log->setLogLevelAll('print', DEBUG ? true : false);
	// form
	$form->log->setLogLevelAll('debug', DEBUG ? true : false);
	$form->log->setLogLevelAll('echo', false);
	$form->log->setLogLevelAll('print', DEBUG ? true : false);
}
// space for setting special debug flags
$login->log->setLogLevelAll('debug', true);
// set smarty arrays
$HEADER = [];
$DATA = [];
$DEBUG_DATA = [];
// set the template dir
// WARNING: this has a special check for the mailing tool layout (old layout)
if (defined('LAYOUT')) {
	$smarty->setTemplateDir(BASE . INCLUDES . TEMPLATES . CONTENT_PATH);
	$DATA['css'] = LAYOUT . CSS;
	$DATA['js'] = LAYOUT . JS;
} else {
	$smarty->setTemplateDir(TEMPLATES);
	$DATA['css'] = CSS;
	$DATA['js'] = JS;
}
// set table width
$table_width = '100%';

// define all needed smarty stuff for the general HTML/page building
$HEADER['CSS'] = CSS;
$HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;
$HEADER['STYLESHEET'] = $ADMIN_STYLESHEET ?? ADMIN_STYLESHEET;

if ($form->my_page_name == 'edit_order') {
	// get is for "table_name" and "where" only
	$table_name = $_GET['table_name'] ?? '';
	// $where = $_GET['where'] ?? '';
	// order name is _always_ order_number for the edit interface

	// follwing arrays do exist here:
	// $position ... has the positions of the [0..max], cause in a <select>
	//               I can't put an number into the array field, in this array,
	//               there are the POSITION stored, that should CHANGE there order (up/down)
	// $row_data_id ... has ALL ids from the sorting part
	// $row_data_order ... has ALL order positions from the soirting part
	if (!isset($position)) {
		$position = [];
	}
	$row_data_id = $_POST['row_data_id'] ?? [];
	$original_id = $row_data_id;
	if (count($position)) {
		$row_data_order = $_POST['row_data_order'];

		// FIRST u have to put right sort, then read again ...
		// hast to be >0 or the first one is selected and then there is no move
		if (isset($up) && isset($position[0]) && $position[0] > 0) {
			for ($i = 0; $i < count($position); $i++) {
				// change position order
				// this gets temp, id before that, gets actual (moves one "down")
				// this gets the old before (moves one "up")
				// is done for every element in row
				// echo "A: ".$row_data_id[$position[$i]]
				//	." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]
				//	." (".$row_data_order[$position[$i]-1].")<br>";
				$temp_id = $row_data_id[$position[$i]] ?? null;
				$row_data_id[$position[$i]] = $row_data_id[$position[$i] - 1] ?? null;
				$row_data_id[$position[$i] - 1] = $temp_id;
				// echo "A: ".$row_data_id[$position[$i]]
				//	." (".$row_data_order[$position[$i]].") -- "
				//	.$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
			} // for
		} // if up

		// the last position id from position array is not to be the count - 1 of
		// row_data_id array, or it is the last element
		if (isset($down) && ($position[count($position) - 1] != (count($row_data_id) - 1))) {
			for ($i = count($position) - 1; $i >= 0; $i--) {
				// same as up, just up in other way, starts from bottom (last element) and moves "up"
				// element before actuel gets temp, this element, becomes element after this,
				// element after this, gets this
				$temp_id = $row_data_id[$position[$i] + 1] ?? null;
				$row_data_id[$position[$i] + 1] = $row_data_id[$position[$i]] ?? null;
				$row_data_id[$position[$i]] = $temp_id;
			} // for
		} // if down

		// write data ... (which has to be abstrackt ...)
		if (
			(isset($up) && $position[0] > 0) ||
			(isset($down) && ($position[count($position) - 1] != (count($row_data_id) - 1)))
		) {
			for ($i = 0; $i < count($row_data_id); $i++) {
				if (isset($row_data_order[$i]) && isset($row_data_id[$i])) {
					$q = "UPDATE " . $table_name
						. " SET order_number = " . $row_data_order[$i]
						. " WHERE " . $table_name . "_id = " . $row_data_id[$i];
					$q = $form->dbExec($q);
				}
			} // for all article ids ...
		} // if write
	} // if there is something to move

	// get ...
	$q = "SELECT " . $table_name . "_id, name, order_number FROM " . $table_name . " ";
	if (!empty($where_string)) {
		$q .= "WHERE $where_string ";
	}
	$q .= "ORDER BY order_number";

	// init arrays
	$row_data = [];
	$options_id = [];
	$options_name = [];
	$options_selected = [];
	// DB read data for menu
	while (is_array($res = $form->dbReturn($q))) {
		$row_data[] = [
			"id" => $res[$table_name . "_id"],
			"name" => $res["name"],
			"order" => $res["order_number"]
		];
	} // while read data ...

	// html title
	$HEADER['HTML_TITLE'] = $form->l->__('Edit Order');

	$messages = [];
	// error msg
	if (isset($error)) {
		if (!isset($msg)) {
			$msg = [];
		}
		$messages[] = [
			'msg' => $msg,
			'class' => 'error',
			'width' => '100%'
		];
	}
	$DATA['form_error_msg'] = $messages;

	// all the row data
	for ($i = 0; $i < count($row_data); $i++) {
		$options_id[] = $i;
		$options_name[] = $row_data[$i]['name'];
		// list of points to order
		for ($j = 0; $j < count($position); $j++) {
			// if matches, put into select array
			if (
				isset($original_id[$position[$j]]) && isset($row_data[$i]['id']) &&
				$original_id[$position[$j]] == $row_data[$i]['id']
			) {
				$options_selected[] = $i;
			}
		}
	}
	$DATA['options_id'] = $options_id;
	$DATA['options_name'] = $options_name;
	$DATA['options_selected'] = $options_selected;

	// hidden list for the data (id, order number)
	$row_data_id = [];
	$row_data_order = [];
	for ($i = 0; $i < count($row_data); $i++) {
		$row_data_id[] = $row_data[$i]['id'];
		$row_data_order[] = $row_data[$i]['order'];
	}
	$DATA['row_data_id'] = $row_data_id;
	$DATA['row_data_order'] = $row_data_order;

	// hidden names for the table & where string
	$DATA['table_name'] = $table_name;
	$DATA['where_string'] = $where_string ?? '';

	$EDIT_TEMPLATE = 'edit_order.tpl';
} else {
	// load call only if id is set
	if (isset(${$form->archive_pk_name})) {
		$form->formProcedureLoad(${$form->archive_pk_name});
	}
	$form->formProcedureNew();
	$form->formProcedureSave();
	$form->formProcedureDelete();
	// delete call only if those two are set
	if (isset($element_list) && isset($remove_name)) {
		$form->formProcedureDeleteFromElementList($element_list, $remove_name);
	}

	$DATA['table_width'] = $table_width;

	$messages = [];
	// write out error / status messages
	$messages[] = $form->formPrintMsg();
	$DATA['form_error_msg'] = $messages;

	// MENU START
	// request some session vars
	if (!isset($HEADER_COLOR)) {
		$DATA['HEADER_COLOR'] = '#E0E2FF';
	} else {
		$DATA['HEADER_COLOR'] = $_SESSION['HEADER_COLOR'];
	}
	$DATA['USER_NAME'] = $_SESSION['USER_NAME'];
	$DATA['EUID'] = $_SESSION['EUID'];
	$DATA['GROUP_NAME'] = $_SESSION['GROUP_NAME'];
	$DATA['GROUP_LEVEL'] = $_SESSION['GROUP_ACL_LEVEL'];
	$PAGES = $_SESSION['PAGES'];

	//$form->log->debug('menu', $form->log->prAr($PAGES));

	// build nav from $PAGES ...
	if (!isset($PAGES) || !is_array($PAGES)) {
		$PAGES = [];
	}
	$menuarray = [];
	foreach ($PAGES as $PAGE_CUID => $PAGE_DATA) {
		if ($PAGE_DATA['menu'] && $PAGE_DATA['online']) {
			$menuarray[] = $PAGE_DATA;
		}
	}

	// split point for nav points
	$COUNT_NAV_POINTS = count($menuarray);
	$SPLIT_FACTOR = 3;
	$START_SPLIT_COUNT = 3;
	// WTF ?? I dunno what I am doing here ...
	for ($i = 9; $i < $COUNT_NAV_POINTS; $i += $START_SPLIT_COUNT) {
		if ($COUNT_NAV_POINTS > $i) {
			$SPLIT_FACTOR += 1;
		}
	}

	$position = 0;
	$menu_data = [];
	// for ($i = 1; $i <= count($menuarray); $i ++) {
	foreach ($menuarray as $i => $data) {
		// do that for new array
		$j = $i + 1;
		$menu_data[$i]['pagename'] = htmlentities($data['page_name']);
		$menu_data[$i]['filename'] =
			// prefix folder or host name
			(isset($data['hostname']) && $data['hostname'] ?
				$data['hostname'] :
				''
			)
			// filename
			. ($data['filename'] ?? '')
			// query string
			. (isset($data['query_string']) && $data['query_string'] ?
				$data['query_string'] :
				''
			);
		if ($j == 1 || !($i % $SPLIT_FACTOR)) {
			$menu_data[$i]['splitfactor_in'] = 1;
		} else {
			$menu_data[$i]['splitfactor_in'] = 0;
		}
		// on matching, we also need to check if we are in the same folder
		if (
			isset($data['filename']) &&
			$data['filename'] == \CoreLibs\Get\System::getPageName() &&
			(!isset($data['hostname']) || (
				isset($data['hostname']) &&
					(!$data['hostname'] || strstr($data['hostname'], CONTENT_PATH) !== false)
			))
		) {
			$position = $i;
			$menu_data[$i]['position'] = 1;
			$menu_data[$i]['popup'] = 0;
		} else {
			// add query stuff
			// HAS TO DONE LATER ... set urlencode, etc ...
			// check if popup needed
			if (isset($data['popup']) && $data['popup'] == 1) {
				$menu_data[$i]['popup'] = 1;
				$menu_data[$i]['rand'] = uniqid((string)rand());
				$menu_data[$i]['width'] = $data['popup_x'];
				$menu_data[$i]['height'] = $data['popup_y'];
			} else {
				$menu_data[$i]['popup'] = 0;
			}
			$menu_data[$i]['position'] = 0;
		} // highlight or not
		if (!($j % $SPLIT_FACTOR) || (($j + 1) > count($menuarray))) {
			$menu_data[$i]['splitfactor_out'] = 1;
		} else {
			$menu_data[$i]['splitfactor_out'] = 0;
		}
	} // for
	// $form->log->debug('MENU ARRAY', $form->log->prAr($menu_data));
	$DATA['menu_data'] = $menu_data;
	$DATA['page_name'] = $menuarray[$position]['page_name'] ?? '-Undefined [' . $position . '] -';
	$L_TITLE = $DATA['page_name'];
	// html title
	$HEADER['HTML_TITLE'] = $form->l->__($L_TITLE);
	// END MENU
	// LOAD AND NEW
	$DATA['load'] = $form->formCreateLoad();
	$DATA['new'] = $form->formCreateNew();
	// SHOW DATA PART
	if ($form->yes) {
		$DATA['form_yes'] = $form->yes;
		$DATA['form_my_page_name'] = $form->my_page_name;
		$DATA['filename_exist'] = 0;
		$DATA['drop_down_input'] = 0;
		$elements = [];
		// depending on the "getPageName()" I show different stuff
		switch ($form->my_page_name) {
			case 'edit_users':
				$elements[] = $form->formCreateElement('login_error_count');
				$elements[] = $form->formCreateElement('login_error_date_last');
				$elements[] = $form->formCreateElement('login_error_date_first');
				$elements[] = $form->formCreateElement('enabled');
				$elements[] = $form->formCreateElement('deleted');
				$elements[] = $form->formCreateElement('protected');
				$elements[] = $form->formCreateElement('username');
				$elements[] = $form->formCreateElement('password');
				$elements[] = $form->formCreateElement('password_change_interval');
				$elements[] = $form->formCreateElement('login_user_id');
				$elements[] = $form->formCreateElement('login_user_id_set_date');
				$elements[] = $form->formCreateElement('login_user_id_last_revalidate');
				$elements[] = $form->formCreateElement('login_user_id_locked');
				$elements[] = $form->formCreateElement('login_user_id_revalidate_after');
				$elements[] = $form->formCreateElement('login_user_id_valid_from');
				$elements[] = $form->formCreateElement('login_user_id_valid_until');
				$elements[] = $form->formCreateElement('email');
				$elements[] = $form->formCreateElement('last_name');
				$elements[] = $form->formCreateElement('first_name');
				$elements[] = $form->formCreateElement('edit_group_id');
				$elements[] = $form->formCreateElement('edit_access_right_id');
				$elements[] = $form->formCreateElement('strict');
				$elements[] = $form->formCreateElement('locked');
				$elements[] = $form->formCreateElement('lock_until');
				$elements[] = $form->formCreateElement('lock_after');
				$elements[] = $form->formCreateElement('admin');
				$elements[] = $form->formCreateElement('debug');
				$elements[] = $form->formCreateElement('db_debug');
				$elements[] = $form->formCreateElement('edit_language_id');
				$elements[] = $form->formCreateElement('edit_scheme_id');
				$elements[] = $form->formCreateElementListTable('edit_access_user');
				$elements[] = $form->formCreateElement('additional_acl');
				break;
			case 'edit_schemes':
				$elements[] = $form->formCreateElement('enabled');
				$elements[] = $form->formCreateElement('name');
				$elements[] = $form->formCreateElement('header_color');
				$elements[] = $form->formCreateElement('template');
				break;
			case 'edit_pages':
				if (!isset($form->table_array['edit_page_id']['value'])) {
					$q = "DELETE FROM temp_files";
					$form->dbExec($q);
					// gets all files in the current dir and dirs given ending with .php
					$folders = ['../admin/', '../frontend/'];
					$files = ['*.php'];
					$search_glob = [];
					foreach ($folders as $folder) {
						// make sure this folder actually exists
						if (is_dir(ROOT . $folder)) {
							foreach ($files as $file) {
								$search_glob[] = $folder . $file;
							}
						}
					}
					$crap = exec('ls ' . join(' ', $search_glob), $output, $status);
					// now get all that are NOT in de DB
					$q = "INSERT INTO temp_files (folder, filename) VALUES ";
					$t_q = '';
					foreach ($output as $output_file) {
						// split the ouput into folder and file
						$pathinfo = pathinfo($output_file);
						if (!empty($pathinfo['dirname'])) {
							$pathinfo['dirname'] .= DIRECTORY_SEPARATOR;
						} else {
							$pathinfo['dirname'] = '';
						}
						if ($t_q) {
							$t_q .= ', ';
						}
						$t_q .= "('" . $form->dbEscapeString($pathinfo['dirname']) . "', '"
							. $form->dbEscapeString($pathinfo['basename']) . "')";
					}
					$form->dbExec($q . $t_q, 'NULL');
					$elements[] = $form->formCreateElement('filename');
				} else {
					// show file menu
					// just show name of file ...
					$DATA['filename_exist'] = 1;
					$DATA['filename'] = $form->table_array['filename']['value'];
				} // File Name View IF
				$elements[] = $form->formCreateElement('hostname');
				$elements[] = $form->formCreateElement('name');
				// $elements[] = $form->formCreateElement('tag');
				// $elements[] = $form->formCreateElement('min_acl');
				$elements[] = $form->formCreateElement('order_number');
				$elements[] = $form->formCreateElement('online');
				$elements[] = $form->formCreateElement('menu');
				$elements[] = $form->formCreateElementListTable('edit_query_string');
				$elements[] = $form->formCreateElement('content_alias_edit_page_id');
				$elements[] = $form->formCreateElementListTable('edit_page_content');
				$elements[] = $form->formCreateElement('popup');
				$elements[] = $form->formCreateElement('popup_x');
				$elements[] = $form->formCreateElement('popup_y');
				$elements[] = $form->formCreateElementReferenceTable('edit_visible_group');
				$elements[] = $form->formCreateElementReferenceTable('edit_menu_group');
				break;
			case 'edit_languages':
				$elements[] = $form->formCreateElement('enabled');
				$elements[] = $form->formCreateElement('short_name');
				$elements[] = $form->formCreateElement('long_name');
				$elements[] = $form->formCreateElement('iso_name');
				break;
			case 'edit_groups':
				$elements[] = $form->formCreateElement('enabled');
				$elements[] = $form->formCreateElement('name');
				$elements[] = $form->formCreateElement('edit_access_right_id');
				$elements[] = $form->formCreateElement('edit_scheme_id');
				$elements[] = $form->formCreateElementListTable('edit_page_access');
				$elements[] = $form->formCreateElement('additional_acl');
				break;
			case 'edit_visible_group':
				$elements[] = $form->formCreateElement('name');
				$elements[] = $form->formCreateElement('flag');
				break;
			case 'edit_menu_group':
				$elements[] = $form->formCreateElement('name');
				$elements[] = $form->formCreateElement('flag');
				$elements[] = $form->formCreateElement('order_number');
				break;
			case 'edit_access':
				$elements[] = $form->formCreateElement('name');
				$elements[] = $form->formCreateElement('enabled');
				$elements[] = $form->formCreateElement('protected');
				$elements[] = $form->formCreateElement('color');
				$elements[] = $form->formCreateElement('description');
				// add name/value list here
				$elements[] = $form->formCreateElementListTable('edit_access_data');
				$elements[] = $form->formCreateElement('additional_acl');
				break;
			default:
				print '[No valid page definition given]';
				break;
		}
		// $form->log->debug('edit', "Elements: <pre>".$form->log->prAr($elements));
		$DATA['elements'] = $elements;
		$DATA['hidden'] = $form->formCreateHiddenFields();
		$DATA['save_delete'] = $form->formCreateSaveDelete();
	} else {
		$DATA['form_yes'] = 0;
	}
	$EDIT_TEMPLATE = 'edit_body.tpl';
}

// debug data, if DEBUG flag is on, this data is print out
$DEBUG_DATA['DEBUG'] = $DEBUG_TMPL ?? '';

// create main data array
$CONTENT_DATA = array_merge($HEADER, $DATA, $DEBUG_DATA);
// data is 1:1 mapping (all vars, values, etc)
foreach ($CONTENT_DATA as $key => $value) {
	$smarty->assign($key, $value);
}
if (is_dir(BASE . TEMPLATES_C)) {
	$smarty->setCompileDir(BASE . TEMPLATES_C);
}
if (is_dir(BASE . CACHE)) {
	$smarty->setCacheDir(BASE . CACHE);
}
$smarty->display($EDIT_TEMPLATE, 'editAdmin_' . $smarty->lang, 'editAdmin_' . $smarty->lang);

$form->log->debug('DEBUGEND', '==================================== [Form END]');
// debug output
echo $login->log->printErrorMsg();
echo $form->log->printErrorMsg();

// __END__
