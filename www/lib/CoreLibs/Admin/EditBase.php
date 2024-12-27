<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2023/1/6
* DESCRIPTION:
* Original created: 2003/06/10
* This is the edit_base.php data as is moved into a class so we can
* more easy update this and also move to a different AJAX style more
* easy
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs\Admin;

use Exception;
use SmartyException;

class EditBase
{
	/** @var array<mixed> */
	private array $HEADER = [];
	/** @var array<mixed> */
	private array $DATA = [];
	/** @var array<mixed> */
	private array $DEBUG_DATA = [];

	/** @var string the template name */
	private string $EDIT_TEMPLATE = '';

	/** @var \CoreLibs\Template\SmartyExtend smarty system */
	private \CoreLibs\Template\SmartyExtend $smarty;
	/** @var \CoreLibs\Output\Form\Generate form generate system */
	private \CoreLibs\Output\Form\Generate $form;
	/** @var \CoreLibs\Logging\Logging */
	public \CoreLibs\Logging\Logging $log;
	/** @var \CoreLibs\Language\L10n */
	public \CoreLibs\Language\L10n $l;
	/** @var \CoreLibs\ACL\Login */
	public \CoreLibs\ACL\Login $login;

	/**
	 * construct form generator
	 *
	 * phpcs:ignore
	 * @param array{db_name:string,db_user:string,db_pass:string,db_host:string,db_port:int,db_schema:string,db_encoding:string,db_type:string,db_ssl:string,db_convert_type?:string[],db_convert_placeholder?:bool,db_convert_placeholder_target?:string,db_debug_replace_placeholder?:bool} $db_config db config array, mandatory
	 * @param \CoreLibs\Logging\Logging $log       Logging class, null auto set
	 * @param \CoreLibs\Language\L10n $l10n      l10n language class, null auto set
	 * @param \CoreLibs\ACL\Login     $login     login class for ACL settings
	 * @param array<string,mixed>     $options   Various settings options
	 */
	public function __construct(
		array $db_config,
		\CoreLibs\Logging\Logging $log,
		\CoreLibs\Language\L10n $l10n,
		\CoreLibs\ACL\Login $login,
		array $options
	) {
		$this->log = $log;
		$this->login = $login;
		$this->l = $l10n;
		// smarty template engine (extended Translation version)
		$this->smarty = new \CoreLibs\Template\SmartyExtend(
			$l10n,
			$options['cache_id'] ?? '',
			$options['compile_id'] ?? '',
		);
		// turn off set log per class
		$log->unsetLogFlag(\CoreLibs\Logging\Logger\Flag::per_class);

		// create form class
		$this->form = new \CoreLibs\Output\Form\Generate(
			$db_config,
			$log,
			$l10n,
			$this->login->loginGetAcl()
		);
		if ($this->form->mobile_phone) {
			echo "I am sorry, but this page cannot be viewed by a mobile phone";
			exit;
		}
		// $this->log->debug('POST', $this->log->prAr($_POST));
	}

	/**
	 * edit order page
	 *
	 * @return void
	 */
	private function editOrderPage(): void
	{
		// get is for "table_name" and "where" only
		$table_name = $_GET['table_name'] ?? $_POST['table_name'] ?? '';
		// not in use
		// $where_string = $_GET['where'] ?? $_POST['where'] ?? '';
		// order name is _always_ order_number for the edit interface

		// follwing arrays do exist here:
		// $position ... has the positions of the [0..max], cause in a <select>
		//               I can't put an number into the array field, in this array,
		//               there are the POSITION stored,
		//               that should CHANGE there order (up/down)
		// $row_data_id ... has ALL ids from the sorting part
		// $row_data_order ... has ALL order positions from the soirting part
		$position = $_POST['position'] ?? [];
		$row_data_id = $_POST['row_data_id'] ?? [];
		$original_id = $row_data_id;
		$row_data_order = $_POST['row_data_order'] ?? [];
		// direction
		$up = $_POST['up'] ?? '';
		$down = $_POST['down'] ?? '';
		if (count($position)) {
			// FIRST u have to put right sort, then read again ...
			// hast to be >0 or the first one is selected and then there is no move
			if (!empty($up) && isset($position[0]) && $position[0] > 0) {
				for ($i = 0; $i < count($position); $i++) {
					// change position order
					// this gets temp, id before that, gets actual (moves one "down")
					// this gets the old before (moves one "up")
					// is done for every element in row
					// echo "A: ".$row_data_id[$position[$i]]
					//	." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]
					//	." (".$row_data_order[$position[$i]-1].")<br>";
					$temp_id = $row_data_id[$position[$i]] ?? null;
					$row_data_id[$position[$i]] = $row_data_id[(int)$position[$i] - 1] ?? null;
					$row_data_id[(int)$position[$i] - 1] = $temp_id;
					// echo "A: ".$row_data_id[$position[$i]]
					//	." (".$row_data_order[$position[$i]].") -- "
					//	.$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
				} // for
			} // if up

			// the last position id from position array is not to be the count - 1 of
			// row_data_id array, or it is the last element
			if (!empty($down) && ($position[count($position) - 1] != (count($row_data_id) - 1))) {
				for ($i = count($position) - 1; $i >= 0; $i--) {
					// same as up, just up in other way, starts from bottom (last element) and moves "up"
					// element before actuel gets temp, this element, becomes element after this,
					// element after this, gets this
					$temp_id = $row_data_id[(int)$position[$i] + 1] ?? null;
					$row_data_id[(int)$position[$i] + 1] = $row_data_id[$position[$i]] ?? null;
					$row_data_id[$position[$i]] = $temp_id;
				} // for
			} // if down

			// write data ... (which has to be abstrackt ...)
			if (
				(!empty($up) && $position[0] > 0) ||
				(!empty($down) && ($position[count($position) - 1] != (count($row_data_id) - 1)))
			) {
				for ($i = 0; $i < count($row_data_id); $i++) {
					if (isset($row_data_order[$i]) && isset($row_data_id[$i])) {
						$q = "UPDATE " . $table_name
							. " SET order_number = " . $row_data_order[$i]
							. " WHERE " . $table_name . "_id = " . $row_data_id[$i];
						$q = $this->form->dba->dbExec($q);
					}
				} // for all article ids ...
			} // if write
		} // if there is something to move

		// get ...
		$q = "SELECT " . $table_name . "_id, name, order_number FROM " . $table_name . " ";
		// /* if (!empty($where_string)) {
		// 	$q .= "WHERE $where_string ";
		// } */
		$q .= "ORDER BY order_number";

		// init arrays
		$row_data = [];
		$options_id = [];
		$options_name = [];
		$options_selected = [];
		// DB read data for menu
		while (is_array($res = $this->form->dba->dbReturn($q))) {
			$row_data[] = [
				"id" => $res[$table_name . "_id"],
				"name" => $res["name"],
				"order" => $res["order_number"]
			];
		} // while read data ...

		// html title
		$this->HEADER['HTML_TITLE'] = $this->l->__('Edit Order');

		$messages = [];
		$error = $_POST['error'] ?? 0;
		// error msg
		if (!empty($error)) {
			$msg = $_POST['msg'] ?? [];
			if (!is_array($msg)) {
				$msg = [];
			}
			$messages[] = [
				'msg' => $msg,
				'class' => 'error',
				'width' => '100%'
			];
		}
		$this->DATA['form_error_msg'] = $messages;

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
		$this->DATA['options_id'] = $options_id;
		$this->DATA['options_name'] = $options_name;
		$this->DATA['options_selected'] = $options_selected;

		// hidden list for the data (id, order number)
		$row_data_id = [];
		$row_data_order = [];
		for ($i = 0; $i < count($row_data); $i++) {
			$row_data_id[] = $row_data[$i]['id'];
			$row_data_order[] = $row_data[$i]['order'];
		}
		$this->DATA['row_data_id'] = $row_data_id;
		$this->DATA['row_data_order'] = $row_data_order;

		// hidden names for the table & where string
		$this->DATA['table_name'] = $table_name;
		$this->DATA['where_string'] = '';
		// $this->DATA['where_string'] = $where_string ?? '';

		$this->EDIT_TEMPLATE = 'edit_order.tpl';
	}

	/**
	 * all edit pages
	 *
	 * @param string $set_root
	 * @param string $set_content_path
	 * @return void
	 */
	private function editPageFlow(
		string $set_root,
		string $set_content_path,
	): void {
		// set table width
		$table_width = '100%';
		// load call only if id is set
		if (!empty($_POST[$this->form->archive_pk_name])) {
			$this->form->formProcedureLoad($_POST[$this->form->archive_pk_name]);
		}
		$this->form->formProcedureNew();
		$this->form->formProcedureSave();
		$this->form->formProcedureDelete();
		// delete call only if those two are set
		// and we are not in new/save/master delete
		if (
			!$this->form->new &&
			!$this->form->save &&
			!$this->form->delete &&
			!empty($_POST['element_list']) &&
			!empty($_POST['remove_name'])
		) {
			$this->form->formProcedureDeleteFromElementList(
				$_POST['element_list'],
				$_POST['remove_name']
			);
			// run a load post element delete to not end up with empty page
			$this->form->formLoadTableArray($_POST[$this->form->archive_pk_name]);
			$this->form->yes = 1;
		}

		$this->DATA['table_width'] = $table_width;

		$messages = [];
		// write out error / status messages
		$messages[] = $this->form->formPrintMsg();
		$this->DATA['form_error_msg'] = $messages;

		// MENU START
		// request some session vars
		$this->DATA['HEADER_COLOR'] = $this->login->loginGetHeaderColor() ?? '#E0E2FF';
		$this->DATA['USER_NAME'] = $this->login->loginGetAcl()['user_name'] ?? '';
		$this->DATA['EUID'] = $this->login->loginGetEuid();
		$this->DATA['GROUP_NAME'] = $this->login->loginGetAcl()['group_name'] ?? '';
		$this->DATA['ACCESS_LEVEL'] = $this->login->loginGetAcl()['base'] ?? '';
		// below is old and to removed when edit_body.tpl is updates
		$this->DATA['GROUP_LEVEL'] = $this->DATA['ACCESS_LEVEL'];
		$PAGES = $this->login->loginGetPages();

		//$this->form->log->debug('menu', $this->form->log->prAr($PAGES));
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
		foreach ($menuarray as $i => $menu_element) {
			// do that for new array
			$j = $i + 1;
			$menu_data[$i]['pagename'] = htmlentities($menu_element['page_name']);
			$menu_data[$i]['filename'] =
				// prefix folder or host name
				(isset($menu_element['hostname']) && $menu_element['hostname'] ?
					$menu_element['hostname'] :
					''
				)
				// filename
				. ($menu_element['filename'] ?? '')
				// query string
				. (isset($menu_element['query_string']) && $menu_element['query_string'] ?
					$menu_element['query_string'] :
					''
				);
			if ($j == 1 || !($i % $SPLIT_FACTOR)) {
				$menu_data[$i]['splitfactor_in'] = 1;
			} else {
				$menu_data[$i]['splitfactor_in'] = 0;
			}
			// on matching, we also need to check if we are in the same folder
			if (
				isset($menu_element['filename']) &&
				$menu_element['filename'] == \CoreLibs\Get\System::getPageName() &&
				(!isset($menu_element['hostname']) || (
					isset($menu_element['hostname']) &&
						(!$menu_element['hostname'] || strstr($menu_element['hostname'], $set_content_path) !== false)
				))
			) {
				$position = $i;
				$menu_data[$i]['position'] = 1;
				$menu_data[$i]['popup'] = 0;
			} else {
				// add query stuff
				// HAS TO DONE LATER ... set urlencode, etc ...
				// check if popup needed
				if (isset($menu_element['popup']) && $menu_element['popup'] == 1) {
					$menu_data[$i]['popup'] = 1;
					$menu_data[$i]['rand'] = uniqid((string)rand());
					$menu_data[$i]['width'] = $menu_element['popup_x'];
					$menu_data[$i]['height'] = $menu_element['popup_y'];
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
		// $this->form->log->debug('MENU ARRAY', $this->form->log->prAr($menu_data));
		$this->DATA['menu_data'] = $menu_data;
		$this->DATA['page_name'] = $menuarray[$position]['page_name'] ?? '-Undefined [' . $position . '] -';
		$L_TITLE = $this->DATA['page_name'];
		// html title
		$this->HEADER['HTML_TITLE'] = $this->form->l->__($L_TITLE);
		// END MENU
		// LOAD AND NEW
		$this->DATA['load'] = $this->form->formCreateLoad();
		$this->DATA['new'] = $this->form->formCreateNew();
		// SHOW DATA PART
		if ($this->form->yes) {
			$this->DATA['form_yes'] = $this->form->yes;
			$this->DATA['form_my_page_name'] = $this->form->my_page_name;
			$this->DATA['filename_exist'] = 0;
			$this->DATA['drop_down_input'] = 0;
			$elements = [];
			// depending on the "getPageName()" I show different stuff
			switch ($this->form->my_page_name) {
				case 'edit_users':
					$elements[] = $this->form->formCreateElement('login_error_count');
					$elements[] = $this->form->formCreateElement('login_error_date_last');
					$elements[] = $this->form->formCreateElement('login_error_date_first');
					$elements[] = $this->form->formCreateElement('enabled');
					$elements[] = $this->form->formCreateElement('deleted');
					$elements[] = $this->form->formCreateElement('protected');
					$elements[] = $this->form->formCreateElement('username');
					$elements[] = $this->form->formCreateElement('password');
					$elements[] = $this->form->formCreateElement('password_change_interval');
					$elements[] = $this->form->formCreateElement('login_user_id');
					$elements[] = $this->form->formCreateElement('login_user_id_set_date');
					$elements[] = $this->form->formCreateElement('login_user_id_last_revalidate');
					$elements[] = $this->form->formCreateElement('login_user_id_locked');
					$elements[] = $this->form->formCreateElement('login_user_id_revalidate_after');
					$elements[] = $this->form->formCreateElement('login_user_id_valid_from');
					$elements[] = $this->form->formCreateElement('login_user_id_valid_until');
					$elements[] = $this->form->formCreateElement('email');
					$elements[] = $this->form->formCreateElement('last_name');
					$elements[] = $this->form->formCreateElement('first_name');
					$elements[] = $this->form->formCreateElement('edit_group_id');
					$elements[] = $this->form->formCreateElement('edit_access_right_id');
					$elements[] = $this->form->formCreateElement('strict');
					$elements[] = $this->form->formCreateElement('locked');
					$elements[] = $this->form->formCreateElement('lock_until');
					$elements[] = $this->form->formCreateElement('lock_after');
					$elements[] = $this->form->formCreateElement('admin');
					$elements[] = $this->form->formCreateElement('edit_language_id');
					$elements[] = $this->form->formCreateElement('edit_scheme_id');
					$elements[] = $this->form->formCreateElementListTable('edit_access_user');
					$elements[] = $this->form->formCreateElement('additional_acl');
					break;
				case 'edit_schemes':
					// @deprecated Will be removed
				case 'edit_schemas':
					$elements[] = $this->form->formCreateElement('enabled');
					$elements[] = $this->form->formCreateElement('name');
					$elements[] = $this->form->formCreateElement('header_color');
					$elements[] = $this->form->formCreateElement('template');
					break;
				case 'edit_pages':
					if (!isset($this->form->dba->getTableArray()['edit_page_id']['value'])) {
						$q = "DELETE FROM temp_files";
						$this->form->dba->dbExec($q);
						// gets all files in the current dir and dirs given ending with .php
						$folders = ['../admin/', '../frontend/'];
						$files = ['*.php'];
						$search_glob = [];
						foreach ($folders as $folder) {
							// make sure this folder actually exists
							if (is_dir($set_root . $folder)) {
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
							$t_q .= "('" . $this->form->dba->dbEscapeString($pathinfo['dirname']) . "', '"
								. $this->form->dba->dbEscapeString($pathinfo['basename']) . "')";
						}
						$this->form->dba->dbExec($q . $t_q, 'NULL');
						$elements[] = $this->form->formCreateElement('filename');
					} else {
						// show file menu
						// just show name of file ...
						$this->DATA['filename_exist'] = 1;
						$this->DATA['filename'] = $this->form->dba->getTableArray()['filename']['value'];
					} // File Name View IF
					$elements[] = $this->form->formCreateElement('hostname');
					$elements[] = $this->form->formCreateElement('name');
					// $elements[] = $this->form->formCreateElement('tag');
					// $elements[] = $this->form->formCreateElement('min_acl');
					$elements[] = $this->form->formCreateElement('order_number');
					$elements[] = $this->form->formCreateElement('online');
					$elements[] = $this->form->formCreateElement('menu');
					$elements[] = $this->form->formCreateElementListTable('edit_query_string');
					$elements[] = $this->form->formCreateElement('content_alias_edit_page_id');
					$elements[] = $this->form->formCreateElementListTable('edit_page_content');
					$elements[] = $this->form->formCreateElement('popup');
					$elements[] = $this->form->formCreateElement('popup_x');
					$elements[] = $this->form->formCreateElement('popup_y');
					$elements[] = $this->form->formCreateElementReferenceTable('edit_visible_group');
					$elements[] = $this->form->formCreateElementReferenceTable('edit_menu_group');
					break;
				case 'edit_languages':
					$elements[] = $this->form->formCreateElement('enabled');
					$elements[] = $this->form->formCreateElement('short_name');
					$elements[] = $this->form->formCreateElement('long_name');
					$elements[] = $this->form->formCreateElement('iso_name');
					break;
				case 'edit_groups':
					$elements[] = $this->form->formCreateElement('enabled');
					$elements[] = $this->form->formCreateElement('name');
					$elements[] = $this->form->formCreateElement('edit_access_right_id');
					$elements[] = $this->form->formCreateElement('edit_scheme_id');
					$elements[] = $this->form->formCreateElementListTable('edit_page_access');
					$elements[] = $this->form->formCreateElement('additional_acl');
					break;
				case 'edit_visible_group':
					$elements[] = $this->form->formCreateElement('name');
					$elements[] = $this->form->formCreateElement('flag');
					break;
				case 'edit_menu_group':
					$elements[] = $this->form->formCreateElement('name');
					$elements[] = $this->form->formCreateElement('flag');
					$elements[] = $this->form->formCreateElement('order_number');
					break;
				case 'edit_access':
					$elements[] = $this->form->formCreateElement('name');
					$elements[] = $this->form->formCreateElement('enabled');
					$elements[] = $this->form->formCreateElement('protected');
					$elements[] = $this->form->formCreateElement('color');
					$elements[] = $this->form->formCreateElement('description');
					// add name/value list here
					$elements[] = $this->form->formCreateElementListTable('edit_access_data');
					$elements[] = $this->form->formCreateElement('additional_acl');
					break;
				default:
					print '[No valid page definition given]';
					break;
			}
			// $this->form->log->debug('edit', "Elements: <pre>".$this->form->log->prAr($elements));
			$this->DATA['elements'] = $elements;
			$this->DATA['hidden'] = $this->form->formCreateHiddenFields();
			$this->DATA['save_delete'] = $this->form->formCreateSaveDelete();
		} else {
			$this->DATA['form_yes'] = 0;
		}
		$this->EDIT_TEMPLATE = 'edit_body.tpl';
	}

	/**
	 * main method that either calls edit order page method or general page
	 * builds the smarty content and runs smarty display output
	 *
	 * @return void
	 * @throws \Smarty\Exception
	 */
	public function editBaseRun(
		?string $template_dir = null,
		?string $compile_dir = null,
		?string $cache_dir = null,
		?string $set_admin_stylesheet = null,
		?string $set_default_encoding = null,
		?string $set_css = null,
		?string $set_js = null,
		?string $set_root = null,
		?string $set_content_path = null
	): void {
		// trigger deprecated warning
		if (
			$template_dir === null ||
			$compile_dir === null ||
			$cache_dir === null ||
			$set_admin_stylesheet === null ||
			$set_default_encoding === null ||
			$set_css === null ||
			$set_js === null ||
			$set_root === null ||
			$set_content_path === null
		) {
			/** @deprecated editBaseRun call without parameters */
			trigger_error(
				'Calling editBaseRun without paramters is deprecated',
				E_USER_DEPRECATED
			);
		}
		// set vars (to be deprecated)
		$template_dir = $template_dir ?? BASE . INCLUDES . TEMPLATES . CONTENT_PATH;
		$compile_dir = $compile_dir ?? BASE . TEMPLATES_C;
		$cache_dir = $cache_dir ?? BASE . CACHE;
		$set_admin_stylesheet = $set_admin_stylesheet ?? ADMIN_STYLESHEET;
		$set_default_encoding = $set_default_encoding ?? DEFAULT_ENCODING;
		$set_css = $set_css ?? LAYOUT . CSS;
		$set_js = $set_js ?? LAYOUT . JS;
		$set_root = $set_root ?? ROOT;
		$set_content_path = $set_content_path ?? CONTENT_PATH;

		// set the template dir
		// WARNING: this has a special check for the mailing tool layout (old no layout folder)
		if (!defined('LAYOUT')) {
			trigger_error(
				'EditBase with unset LAYOUT is deprecated',
				E_USER_DEPRECATED
			);
			$this->smarty->setTemplateDir(TEMPLATES);
			$this->DATA['css'] = CSS;
			$this->DATA['js'] = JS;
		} else {
			$this->smarty->setTemplateDir($template_dir);
			$this->DATA['css'] = $set_css;
			$this->DATA['js'] = $set_js;
		}
		// define all needed smarty stuff for the general HTML/page building
		$this->HEADER['CSS'] = $set_css;
		$this->HEADER['DEFAULT_ENCODING'] = $set_default_encoding;
		$this->HEADER['STYLESHEET'] = $set_admin_stylesheet;

		// main run
		if ($this->form->my_page_name == 'edit_order') {
			$this->editOrderPage();
		} else {
			$this->editPageFlow(
				$set_root,
				$set_content_path
			);
		}

		// debug data, if DEBUG flag is on, this data is print out
		// $this->DEBUG_DATA['DEBUG'] = $DEBUG_TMPL ?? '';
		$this->DEBUG_DATA['DEBUG'] = '';

		// create main data array
		$CONTENT_DATA = array_merge($this->HEADER, $this->DATA, $this->DEBUG_DATA);
		// data is 1:1 mapping (all vars, values, etc)
		foreach ($CONTENT_DATA as $key => $value) {
			$this->smarty->assign($key, $value);
		}
		if (is_dir($compile_dir)) {
			$this->smarty->setCompileDir($compile_dir);
		}
		if (is_dir($cache_dir)) {
			$this->smarty->setCacheDir($cache_dir);
		}
		$this->smarty->display(
			$this->EDIT_TEMPLATE,
			'editAdmin_' . $this->smarty->lang,
			'editAdmin_' . $this->smarty->lang
		);

		$this->log->debug('DEBUGEND', '==================================== [Form END]');
	}
}

// __END__
