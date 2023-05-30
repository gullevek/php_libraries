<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2002/10/22
* VERSION: 3.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTION:
*   ~ 2003/02/26: decided to move away from single class and change this
*   to extend db_array_io which extends db_io. this is much more efficient
*   in use of vars and use of methods of other classes
*
*   ~ 2002/10/20: this class contains a set of functions that helps in creating
*   more or less default forms, or supports u in handling normal
*   form data
*
* description of the variables && arrays that have to be set ...
*  $name_u_choose = [
* # this is the description of ALL fields in the main table
*    'table_array' => [
*      'name_of_col_in_table' => [
*        'value' => $name_of_col_in_table',
*        'pk' => 1/0 - sets the primary key (only one)
*        'fk' => 1/0 - sets the foreign key (do not use at the moment ... buggy ;)
*        'mandatory' => 1/0 - triggers * in output, but nor error check
*        'output_name' => 'text' - text put as label for the element
*        'type' => 'view/text/textarea/date/drop_down_db/drop_down_array/drop_down_db_input/drop_down_db_same_db/radio_array/binary/hidden/file/password'
*					View is special, it just prints out the data as is, will not be saved
*                   1) more will come
*                   2) keep in mind that binary will not be checked, as it is always set to a value (default is 'no')
*        ---- the next four fields are only NECESSARY (!!!) for drop_down_db_input
*        'table_name' => the name of the table for the drop down
*        'pk_name' => the pk_name of the table for the drop down
*        'input_name' => the text field name in the table for the drop down
*        'input_value' => the $name of input_name (must be same)
*        'order_by' => 'order bY' string for drop_down_db(_input) if no query given but fields set
*        'query' => for drop_down_db/array if no outer query given
*        'preset' => value to preset when array is unset (available for all types)
*        'element_list' => ['true', 'false'] - MUST (!) be set for binary
*        'length' => 'nr' - only available for 'text' (maxlength)
*        'size' => 'nr' - only available for 'text' (size of input field)
*        'rows' => 'nr' - only available for 'textarea'
*        'cols' => 'nr' - only available for 'textarea'
*        'error_check' => 'custom/email/date/number/unique' - 1) more will come
*        'error_regex' => 'regex' - if error_check is custom regex here
*        'error_example' => 'text' - example input text for error_check (only custom right now)
*        'empty' => 'value/text' - ONLY for view. If no data found, set this value
*        --- file:
*        'save_dir' => 'directory where it should be saved to
*        'accept_type' => 'mime types accepted (mime/text,mime/jpeg ... etc)'
*      ],
*      ...
*    ],
* # all reference tables (n<->n)
*    'reference_array' => [
*      'name_u_choose' => [
*        'table_name' => 'table_u_choose_for_n_to_n_table',
*        'other_table_pk' => 'primary_key_name_of_reference_table',
*        'output_name' => 'Printed out next to multiple select field',
*        'mandatory' => 1/0 for must be selected,
*        'select_size' => size of multiple select field,
*        'query' => 'the query to load the multiple select field
*                    (select id, concat_ws(' ',name_a, name_b) from reference_table)',
*        'selected' => $var_name for name='xx' in multiple select
*      ],
*      ...
*    ],
* # fields that should be shown from the load_query and with what aditions
*    'show_fields' => [
*      [
*        'name' => 'name_of_col_in_query' - col from the query that should be shown
*        'before_value' => 'text' - if set this text will be put in FRONT of the value from the col
*        'binary' => ['true','false'] - for 1/0 fields in DB changes it int human readable format
*      ],
*      ...
*    ],
* # the laod query
*    'load_query' => 'query', - query for generting the list in 'load' function
* # the name of the main table
*    'table_name' => 'table_name' - the exakt name of the table ...
* # security levels for load ... usefull is delete with a low number and load with a high
*    'security_level' =>
*      'load' => ... for load to appear
*      'new' => 1... - security level minimum required for new part to appear (goes in hadn with save)
*      'save' => ... - should be same level as new [or its a bit useless]
*      'delete' => ... - for delete
*
* example for a page:
*
* $form->form_procedure_load(${$form->archive_pk_name});
* $form->form_procedure_new();
* $form->form_procedure_save();
* $form->form_procedure_delete();
* <HTML start>
* $form->form_create_load();
* $form->form_create_new();
* if ($form->yes)
* {
*   $from->form_create_element('element_name');
*   $from->form_create_hidden_fields();
*   $form->form_creae_save_delete();
* }
* $form->_form();
* <HTML end>
*
* list_of_functions:
*   form_get_col_name_from_key($want_key)
*     returns the value for the key (out of table_array)
*   form_get_col_name_array_from_key($want_key)
*     returns array of values for the searched key ...
*   form_print_msg () [form_error_msg()]
*     returns the HTML formated part with the error msg, if one exists
*   form_procedure_load($id)
*     starts the loading procedure
*   form_procedure_new()
*     starts the new procedure
*   form_procedure_save()
*     starts the save procedure
*   form_procedure_delete()
*     starts the delete procedure
*   form_create_load () [form_load()]
*     returns the HTML part for loading a table row, load_query & field_array have to be set for this!!!!!!
*   form_create_new () [form_new()]
*     returns the HTML part for creating a new table_row
*   form_create_save_delete () [form_delete_save()]
*     returns the HTML part for saveing and deleteing one table_row
*   form_create_element ($element_name, $query='')
*     creates and HTML element based on the description in the table_array array,
*     second parameter is for drop_down fields, either a query for _db or an array for _array
*   form_error_check()
*     checks on errors after submit based on the settings in the table_array array
*   form_set_order()
*     if it finds the order flag set in the table_array sets the order for the current element to MAX+1 from the DB
*   form_unset_table_array()
*     unsets the table_array value fields for new entries
*   form_create_hidden_fields($hidden_array)
*     outputs a string with the HTML hidden fields (array must be $name['hidden_name']=$hidden_value)
*   form_create_element_reference_table($table_name) [form_show_reference_table()]
*     creates and table tr part for the reference table name given
*   form_load_table_array($pk_id=0)
*     loads the table_array and the reference tables for the pk_id set in the class or given via parameter
*   form_save_table_array($addslashes=0)
*     save table array & reference tables
*   form_delete_table_array()
*     deletes table array & reference tables
*
*  // debug methods
*   form_dump_table_array()
*     returns a formatted string with alle table_array vars
*
* HISTORY:
* 2005/07/14 (cs) fixed the insert for reference tables, prepared drop down text insert to be correct [untested]
* 2005/07/08 (cs) added int set for integer insert values
* 2005/07/07 (cs) bug with protected data, error got triggered even if no delete was pressed
* 2005/06/30 (cs) changed color settings, they get set from CSS file now
* 2005/06/29 (cs) finished full support for element_lists
* 2005/06/24 (cs) added full support for a list in a form, a list is written
*                 to an other table and the other table has this forms PK as a FK
* 2005/06/23 (cs) changed all HTML to Smarty Template Type
* 2005/06/22 (cs) you can put more than one error check into the error field;
*                 alphanumeric check and unique in same table are new
* 2005/06/21 (cs) changed the error_msg writings to debug
* 2005/03/31 (cs) fixed the class call with all debug vars
* 2004/11/10 (cs) fix bug with preset: don't check if set, check if variable is set at all
* 2004/09/30 (cs) layout change
*   2003-06-13: error with 'protected' flag, fixed and added error msg,
*               if protected flag is detected during delete
*   2003-06-12: adapted class to register_global_vars off
*   2003-06-10: in procedure_delete function I added 'protected' variable clause,
*               so if this field exists in the DB and is set, you are not able to delete
*               [at the moment used for admin edit user in DB]
*   2003-05-30: _temp for drop_down_db was added always and not only for same_db
*   2003-05-28: added drop_down_db_same_db for drop down/input combinations going into the same DB.
*               WARNING!!! please be careful that input_value var name MUST have the ending _temp
*               This might get change in future
*               added a 'where' field to the field list, this is only used for the drop_down for selecting
*               only a certain field list. If where is filled out and used in combination with insert (not same_db)
*               then this key will be SET when inserted into the DB !!!
*   2003-04-09: added open_dir for download of file (URL), save_dir is only for upload (absolute path)
*               added require once for class_db_array_io.php
*   2003-03-31: added a file upload module (type==file)
*   2003-03-20: added form_procedure_new, etc functions so for default calls it is easier to write
*               also added security levels to all functions where it is needed
*   2003-03-14: changed the static error msgs to dynamic ones
*   2003-03-13: very bad bug with getting key function. fixed it (set first array value always)
*               reason was that in second if I forgot to check if the second method field was really
*               set, so I compared to empty which was always right.
*   2003-03-11: started renaming some functions:
*               form_load, form_new, form_delete_save -> form_create_... (and _save_delete)
*               .._show_reference_table -> create_element_reference_table
*               added language array
*               - kept old var names/function names for backward compatbile
*   2003-03-10: added flag for form_delete_save, first flag hides delete part, second flag
*               hides checkbox for delete, both are set 0 default
*               added drop_down_db_input element type.
*               next to a drop down with elements froma db, there is an input field,
*               if something is input there and not yet in the DB it will be inserted into
*               the db first and then selected in the drop down, if already in db, the element
*               in the drop down will be selected
*   2003-03-07: form_create_hidden_fields() has to be called mandatory
*   2003-03-06: if nothing selected for reference table, do not write
*               a wrong return in form_delete_table_array quit the function to early
*   2003-03-04: drop_down_array value for option was left from array and
*               not right
*   2003-02-27: added another check in unset if reference array exists
*   2003-02-26: change form to extend db_array_io and created load, save,
*               delete functions removed all reference table functions,
*               except show function rewrite config array
*               re-wrote the class info vars into array
*   2003-02-25: added reference table functions
*   2002-10-22: create this class so creating basic and medium form pages
*               can be handled easy.
*               with a given config file the class handles error checks,
*               save data, loads data, etc
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs\Output\Form;

use CoreLibs\Get\System;
use CoreLibs\Debug\Support;

class Generate extends \CoreLibs\DB\Extended\ArrayIO
{
	// for the load statetment describes which elements from
	// the load query should be shown and i which format
	/** @var array<mixed> */
	public $field_array = [];
	/** @var string */
	public $load_query; // the query needed for loading a data set (one row in the table)
	/** @var string */
	public $col_name; // the name of the columen (before _<type>) [used for order button]
	/** @var int */
	public $yes; // the yes flag that triggers the template to show ALL and not only new/load
	/** @var string */
	public $msg; // the error msg
	/** @var int */
	public $error; // the error flag set for printing red error msg
	/** @var int */
	public $warning; // warning flag, for information (saved, loaded, etc)
	/** @var string */
	public $archive_pk_name; // the pk name for the load select form
	/** @var string */
	private $int_pk_name; // primary key, only internal usage
	/** @var array<mixed> */
	public $reference_array = []; // reference arrays -> stored in $this->reference_array[$table_name] => [];
	// NOTE: should be changed to this @var mixed[]
	/** @var array<mixed> */
	public $element_list; // element list for elements next to each other as a special sub group
	/** @var array<mixed> */
	public $table_array = [];
	/** @var string */
	public $my_page_name; // the name of the page without .php extension
	/** @var bool */
	public $mobile_phone = false;
	/** @var string */
	public $email_regex;
	// buttons and checkboxes
	/** @var string */
	public $archive;
	/** @var string */
	public $new;
	/** @var string */
	public $really_new;
	/** @var string */
	public $delete;
	/** @var string */
	public $really_delete;
	/** @var string */
	public $save;
	/** @var string */
	public $remove_button;
	// security values
	/** @var int base acl for current page */
	private $base_acl_level = 0;
	/** @var int admin master flag (1/0) */
	private $acl_admin = 0;
	/** @var array<mixed> */
	public $security_level;
	/** @var array<string,mixed> Login ACL */
	public $login_acl = [];
	// layout publics
	/** @var int */
	public $table_width;
	// internal lang & encoding vars
	/** @var string */
	public $lang_dir = '';
	/** @var string */
	public $lang;
	/** @var string */
	public $lang_short;
	/** @var string */
	public $domain;
	/** @var string */
	public $encoding;
	// language
	/** @var \CoreLibs\Language\L10n */
	public $l;
	// log
	/** @var \CoreLibs\Logging\Logging */
	public $log;

	// now some default error msgs (english)
	/** @var array<mixed> */
	public $language_array = [];

	/**
	 * construct form generator
	 *
	 * @param array<mixed>            $db_config    db config array, mandatory
	 * @param \CoreLibs\Logging\Logging $log          Logging class
	 * @param \CoreLibs\Language\L10n $l10n         l10n language class
	 * @param array<string,mixed>     $login_acl	Login ACL array,
	 *                                              at least base/admin should be set
	 * @param array<mixed>|null       $table_arrays Override table array data
	 *                                              instead of try to load from
	 *                                              include file
	 * @throws \Exception             1: No table_arrays set/no class found for my page name
	 */
	public function __construct(
		array $db_config,
		\CoreLibs\Logging\Logging $log,
		\CoreLibs\Language\L10n $l10n,
		array $login_acl,
		?array $table_arrays = null,
	) {
		// init logger if not set
		$this->log = $log;
		// don't log per class
		$this->log->unsetLogFlag(\CoreLibs\Logging\Logger\Flag::per_class);
		// init the language class
		$this->l = $l10n;
		// parse and read, legacy stuff
		$locale = $this->l->getLocaleAsArray();
		$this->encoding = $locale['encoding'];
		$this->lang = $locale['lang'];
		$this->lang_short = $locale['lang_short'];
		$this->domain = $locale['domain'];
		$this->lang_dir = $locale['path'];
		// load config array
		// get table array definitions for current page name
		$this->login_acl = $login_acl;
		// security settings
		$this->base_acl_level = $this->login_acl['base'] ?? 0;
		$this->acl_admin = $this->login_acl['admin'] ?? 0;

		// replace any non valid variable names and set my page name
		$this->my_page_name = str_replace(
			['.'],
			'_',
			System::getPageName(System::NO_EXTENSION)
		);

		// first check if we have a in page override as $table_arrays[page name]
		if (
			isset($table_arrays[System::getPageName(System::NO_EXTENSION)]) &&
			is_array($table_arrays[System::getPageName(System::NO_EXTENSION)])
		) {
			// $config_array = $GLOBALS['table_arrays'][System::getPageName(1)];
			$config_array = $table_arrays[System::getPageName(1)];
		} else {
			// primary try to load the class
			/** @var TableArrays\Interface\TableArraysInterface|false $content_class */
			$content_class = $this->loadTableArray();
			if (is_object($content_class)) {
				$config_array = $content_class->setTableArray();
			} else {
				// throw an error here as we cannot load the class at all
				throw new \Exception("Cannot load " . $this->my_page_name, 1);
			}
		}
		// $log->debug('CONFIG ARRAY', $log->prAr($config_array));
		// start the array_io class which will start db_io ...
		parent::__construct(
			$db_config,
			$config_array['table_array'],
			$config_array['table_name'],
			$this->log,
			// set the ACL
			$this->base_acl_level,
			$this->acl_admin
		);
		// here should be a check if the config_array is correct ...
		if (isset($config_array['show_fields']) && is_array($config_array['show_fields'])) {
			$this->field_array = $config_array['show_fields'];
		}
		if (isset($config_array['load_query']) && $config_array['load_query']) {
			$this->load_query = $config_array['load_query'];
		}
		if (empty($this->load_query)) {
			$this->log->debug('INIT ERROR', 'Missing Load Query for: ' . $this->my_page_name);
		}
		$this->archive_pk_name = 'a_' . $this->pk_name;
		$this->col_name = str_replace('_id', '', $this->pk_name);
		$this->int_pk_name = $this->pk_name;
		// check if reference_arrays are given and proceed them
		if (isset($config_array['reference_arrays']) && is_array($config_array['reference_arrays'])) {
			foreach ($config_array['reference_arrays'] as $key => $value) {
				$this->reference_array[$key] = $value;
			}
		}
		if (isset($config_array['element_list']) && is_array($config_array['element_list'])) {
			foreach ($config_array['element_list'] as $key => $value) {
				$this->element_list[$key] = $value;
			}
		}

		// set button vars
		$this->archive = $_POST['archive'] ?? '';
		$this->new = $_POST['new'] ?? '';
		$this->really_new = $_POST['really_new'] ?? '';
		$this->delete = $_POST['delete'] ?? '';
		$this->really_delete = $_POST['really_delete'] ?? '';
		$this->save = $_POST['save'] ?? '';
		$this->remove_button = $_POST['remove_button'] ?? '';

		// security levels for buttons/actions
		// if array does not exists create basic
		if (
			!isset($config_array['security_level']) ||
			(isset($config_array['security_level']) &&
				(!is_array($config_array['security_level']) ||
				(is_array($config_array['security_level']) && count($config_array['security_level']) < 4))
			)
		) {
			$this->security_level = [
				'load' => 20,
				'new' => 100,
				'save' => 40,
				'delete' => 100
			];
		} else {
			// write array to class var
			$this->security_level = isset($config_array['security_level']) ?
				$config_array['security_level'] :
				[
					'load' => 20,
					'new' => 100,
					'save' => 40,
					'delete' => 100
				];
		}

		// set email regex
		$this->email_regex = \CoreLibs\Check\Email::getEmailRegex();
	}

	/**
	 * deconstructor
	 * writes out error msg to global var
	 * closes db connectio
	 */
	public function __destruct()
	{
		// close DB connection
		parent::__destruct();
	}

	// PRIVATE METHODS  |=================================================>

	/**
	* load table array class based on my page name converted to camel case
	* class files are in \TableArrays folder in \Output\Form
	* @return TableArrays\Interface\TableArraysInterface|false Return class object or false on failure
	*/
	private function loadTableArray(): TableArrays\Interface\TableArraysInterface|false
	{
		// note: it schould be Schemas but an original type made it to this
		//       this file is kept for the old usage, new one should be EditSchemas
		$table_array_shim = [
			'EditSchemes' => 'EditSchemas'
		];
		// camel case $this->my_page_name from foo_bar_note to FooBarNote
		$page_name_camel_case = '';
		foreach (explode('_', $this->my_page_name) as $part) {
			$page_name_camel_case .= ucfirst($part);
		}
		$class_string = __NAMESPACE__ . "\\TableArrays\\"
			. (
				// shim lookup
				$table_array_shim[$page_name_camel_case] ??
					$page_name_camel_case
			);
		try {
			/** @var TableArrays\Interface\TableArraysInterface|false $class */
			$class = new $class_string($this);
		} catch (\Throwable $t) {
			$this->log->debug('CLASS LOAD', 'Failed loading: ' . $class_string . ' => ' . $t->getMessage());
			return false;
		}
		if (is_object($class)) {
			return $class;
		}
		return false;
	}

	// PUBLIC METHODS |=================================================>

	/**
	 * return current acl admin flag (1/0)
	 *
	 * @return int Admin flag 1 for on or 0 for off
	 */
	public function getAclAdmin(): int
	{
		return $this->acl_admin;
	}

	/**
	 * check if current acl level is match to requested on
	 *
	 * @param  int  $level Requested ACL level
	 * @return bool        if current level equal or larger return tru, else false
	 */
	public function checkBaseACL(int $level): bool
	{
		return $this->base_acl_level >= $level ? true : false;
	}

	/**
	 * dumps all values into output (for error msg)
	 *
	 * @return string full table array data output as string html formatted
	 */
	public function formDumpTableArray()
	{
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		$string = '<b>TABLE ARRAY DUMP:</b> ' . $this->table_name . '<br>';
		foreach ($this->table_array as $key => $value) {
			$string .= '<b>' . $key . '</b>: ' . $value['value'] . '<br>';
		}
		return $string;
	}

	/**
	 * the value of the $want_key array field
	 * works only with fields that appear only ONCE
	 * if multiple gets only FIRST
	 *
	 * @param  string        $want_key  key to search for
	 * @param  string|null   $key_value value to match to (optional)
	 * @return string|null              returns key found or empty string
	 */
	public function formGetColNameFromKey(
		string $want_key,
		?string $key_value = null
	): ?string {
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			if (isset($value[$want_key]) && !$key_value) {
				return $key;
			} elseif (isset($value[$want_key]) && $value[$want_key] == $key_value && $key_value) {
				return $key;
			}
		}
		// return nothing on nothing
		return null;
	}

	/**
	 * array of fields
	 *
	 * @param  string      $want_key  the key where you want the data from
	 * @param  string|null $key_value if set searches for special right value
	 * @return array<mixed>           found key fields
	 */
	public function formGetColNameArrayFromKey(
		string $want_key,
		?string $key_value = null
	): array {
		$key_array = [];
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			if ($value[$want_key] && !$key_value) {
				array_push($key_array, $key);
			}
			if ($value[$want_key] == $key_value) {
				array_push($key_array, $key);
			}
		}
		return $key_array;
	}

	/**
	 * formated output for the error && warning msg
	 *
	 * @return array<string,string|int> error message with msg, width, clas
	 */
	public function formPrintMsg(): array
	{
		$class = '';
		if ($this->error) {
			$class = 'error';
		}
		if ($this->warning) {
			$class = 'warning';
		}
		return [
			'msg' => $this->msg,
			'width' => $this->table_width,
			'class' => $class
		];
	}

	// next for functions are pre_test fkts for easier default new,load, etc handling
	/**
	 * default load procedure
	 *
	 * @param  string $archive_id archive id to load
	 * @return void               has no return
	 */
	public function formProcedureLoad(string $archive_id): void
	{
		if (
			isset($this->security_level['load']) &&
			$this->archive &&
			$archive_id &&
			$this->base_acl_level >= $this->security_level['load']
		) {
			$this->formLoadTableArray($archive_id);
			$this->yes = 1;
		}
	}

	/**
	 * default new procedure
	 *
	 * @return void has no return
	 */
	public function formProcedureNew(): void
	{
		if (
			isset($this->security_level['new']) &&
			$this->new &&
			$this->base_acl_level >= $this->security_level['new']
		) {
			if ($this->really_new == 'yes') {
				$this->formUnsetTableArray();
			} else {
				$this->msg .= $this->l->__('You have to select the <b>Checkbox for New</b>!<br>');
				$this->error = 2;
			}
			$this->yes = 1;
		}
	}

	/**
	 * default save procedure
	 *
	 * @return void has no return
	 */
	public function formProcedureSave(): void
	{
		if (
			isset($this->security_level['save']) &&
			$this->save &&
			$this->base_acl_level >= $this->security_level['save']
		) {
			$this->formErrorCheck();
			if (!$this->error) {
				$this->formSaveTableArray();
			}
			$this->yes = 1;
		}
	}

	/**
	 * default delete procedure
	 *
	 * @return void has no return
	 */
	public function formProcedureDelete(): void
	{
		// delete is also by 'protected'
		if (
			isset($this->security_level['delete']) &&
			$this->delete &&
			$this->base_acl_level >= $this->security_level['delete']
		) {
			if (isset($this->table_array['protected']['value']) && $this->table_array['protected']['value']) {
				$this->msg .= $this->l->__('Cannot delete this Dataset, because it is internaly protected!');
				$this->error = 2;
			}
			if ($this->really_delete == 'yes') {
				$this->formDeleteTableArray();
			} else {
				$this->msg .= $this->l->__('You have to select the <b>Checkbox for Delete</b>!<br>');
				$this->error = 2;
				$this->yes = 1;
			}
		}
	}

	/**
	 * default delete procedure
	 *
	 * @param  array<mixed> $element_list element array that should be removed
	 * @param  array<mixed> $remove_name  key names that should be removed
	 * @return void                       has no return
	 */
	public function formProcedureDeleteFromElementList(
		array $element_list,
		array $remove_name
	): void {
		/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
		$this->log->debug('REMOVE ELEMENT', 'Remove REF ELEMENT: ' . $this->base_acl_level . ' >= '
			. $this->security_level['delete']);
		$this->log->debug('REMOVE ELEMENT', 'Protected Value set: '
			. (string)isset($this->table_array['protected']['value']));
		$this->log->debug('REMOVE ELEMENT', 'Error: ' . $this->error);
		// only do if the user is allowed to delete
		if (
			isset($this->security_level['delete']) &&
			$this->base_acl_level >= $this->security_level['delete'] &&
			empty($this->table_array['protected']['value']) &&
			!$this->error
		) {
			for ($i = 0, $i_max = count($element_list); $i < $i_max; $i++) {
				// $this->log->debug('form_error', 'Array: '
				//	. is_array($this->element_list[$element_list[$i]]['read_data']) . ' | '
				//	. $this->element_list[$element_list[$i]]['delete']);
				// if prefix, set it
				$prfx = !empty($this->element_list[$element_list[$i]]['prefix']) ?
					$this->element_list[$element_list[$i]]['prefix'] . '_' :
					'';
				// get the primary key
				$pk_name = '';
				foreach ($this->element_list[$element_list[$i]]['elements'] as $el_name => $data) {
					if (isset($data['pk_id'])) {
						$pk_name = $el_name;
						break;
					}
				}
				// which key should be deleted
				$id = $remove_name[$i];
				if (
					(!empty($this->element_list[$element_list[$i]]['delete_name']) ||
					!empty($this->element_list[$element_list[$i]]['delete'])) &&
					empty($this->element_list[$element_list[$i]]['enable_name'])
				) {
					// flag var name
					$flag = $remove_name[$i] . '_flag';
					if ($_POST[$flag] == 'true') {
						$q = 'DELETE FROM ' . $element_list[$i] . ' WHERE ' . $pk_name . ' = ' . $_POST[$id];
						$this->dbExec($q);
						$this->msg .= $this->l->__('Removed entry from list<br>');
						$this->warning = 1;
					} // post okay true -> delete
				} elseif (
					isset($this->element_list[$element_list[$i]]['read_data']) &&
					!$this->element_list[$element_list[$i]]['delete']
				) {
					if (!isset($_POST[$id])) {
						$_POST[$id] = [];
					}
					for ($j = 0, $j_max = count($_POST[$id]); $j < $j_max; $j++) {
						// if it is not activated
						if (!$_POST[$remove_name[$i]][$j]) {
							$q = 'UPDATE ' . $element_list[$i]
								. ' WHERE ' . $pk_name . ' = ' . $_POST[$prfx . $pk_name][$j];
							// $this->log->debug('edit_db', 'UP: $q');
							// $this->dbExec($q);
							$this->msg .= $this->l->__('Disabled deselected entries from list<br>');
							$this->warning = 1;
						}
					}
				} elseif (
					isset($this->element_list[$element_list[$i]]['read_data']) &&
					$this->element_list[$element_list[$i]]['delete']
				) {
					// $this->log->debug('form_clean', 'ID [$id] [$prfx . $pk_name]');
					// $this->log->debug('form_clean', 'ID arr: ' . $this->log->prAr($_POST[$id]));
					// $this->log->debug('form_clean', 'PK arr: ' . $this->log->prAr($_POST[$prfx.$pk_name]));
					for ($j = 0, $j_max = count($_POST[$prfx . $pk_name]); $j < $j_max; $j++) {
						if (!$_POST[$remove_name[$i]][$j] && $_POST[$prfx . $pk_name][$j]) {
							$q = 'DELETE FROM ' . $element_list[$i]
								. ' WHERE ' . $pk_name . ' = ' . $_POST[$prfx . $pk_name][$j];
							// $this->log->debug('edit_db', 'DEL: $q');
							$this->dbExec($q);
							$this->msg .= $this->l->__('Deleted deselected entries from list<br>');
							$this->warning = 1;
						}
					}
				}
			} // for each element group
		}
		if ($this->remove_button) {
			$this->yes = 1;
		}
	}

	/**
	 * create the load list and return it as an array
	 *
	 *
	 * @return array<string,mixed> load list array with primary key, name and selected entry
	 */
	public function formCreateLoad(): array
	{
		$pk_selected = '';
		$t_pk_name = '';
		$pk_names = [];
		$pk_ids = [];
		$seclevel_okay = false;
		// for error abort only
		$return_array = [
			't_pk_name' => $t_pk_name,
			'pk_ids' => $pk_ids,
			'pk_names' => $pk_names,
			'pk_selected' => $pk_selected,
			'seclevel_okay' => $seclevel_okay,
		];
		// when security level is okay ...
		if (
			empty($this->security_level['load']) ||
			$this->base_acl_level < $this->security_level['load']
		) {
			return $return_array;
		}
		if (empty($this->load_query)) {
			$this->log->debug('LOAD LIST ERROR', 'Missing load list query');
			return $return_array;
		}

		$t_pk_name = $this->archive_pk_name;

		// load list data
		$this->dbExec($this->load_query);
		while (is_array($res = $this->dbFetchArray())) {
			$pk_ids[] = $res[$this->int_pk_name];
			if (
				isset($this->table_array[$this->int_pk_name]['value']) &&
				$res[$this->int_pk_name] == $this->table_array[$this->int_pk_name]['value']
			) {
				$pk_selected = $res[$this->int_pk_name];
			}
			$t_string = '';
			foreach ($this->field_array as $i => $field_array) {
				if ($t_string) {
					$t_string .= ', ';
				}
				if (isset($field_array['before_value'])) {
					$t_string .= $field_array['before_value'];
				}
				// must have res element set
				if (
					isset($field_array['name']) &&
					isset($res[$field_array['name']])
				) {
					if (isset($field_array['binary'])) {
						if (isset($field_array['binary'][0])) {
							$t_string .= $field_array['binary'][0];
						} elseif (isset($field_array['binary'][1])) {
							$t_string .= $field_array['binary'][1];
						}
					} else {
						$t_string .= $res[$field_array['name']];
					}
				}
			}
			$pk_names[] = $t_string;
		}
		$seclevel_okay = true;
		return [
			't_pk_name' => $t_pk_name,
			'pk_ids' => $pk_ids,
			'pk_names' => $pk_names,
			'pk_selected' => $pk_selected,
			'seclevel_okay' => $seclevel_okay,
		];
	}

	/**
	 * Create new entry element for HTML output
	 *
	 * @param  bool $hide_new_checkbox  show or hide the new checkbox, default is false
	 * @return array<string,string|bool> return the new create array with name & checkbox show flag
	 */
	public function formCreateNew(bool $hide_new_checkbox = false): array
	{
		$show_checkbox = false;
		$new_name = '';
		$seclevel_okay = false;
		// when security level is okay
		if (
			empty($this->security_level['new']) ||
			$this->base_acl_level < $this->security_level['new']
		) {
			return [
				'new_name' => $new_name,
				'show_checkbox' => $show_checkbox,
				'seclevel_okay' => $seclevel_okay,
			];
		}
		if ($this->yes && !$hide_new_checkbox) {
			$show_checkbox = false;
		}
		// set type of new name
		if ($this->yes) {
			$new_name = $this->l->__('Clear all and create new');
		} else {
			$new_name = $this->l->__('New');
		}
		$seclevel_okay = true;
		return [
			'new_name' => $new_name,
			'show_checkbox' => $show_checkbox,
			'seclevel_okay' => $seclevel_okay,
		];
	}

	/**
	 * create the save and delete element html group data
	 * @param  bool  $hide_delete          hide the delete button (default false)
	 * @param  bool  $hide_delete_checkbox hide the delete checkbox (default false)
	 * @return array<string,mixed>         return the hide/show delete framework
	 *                                     for html creation
	 */
	public function formCreateSaveDelete(
		bool $hide_delete = false,
		bool $hide_delete_checkbox = false,
		bool $old_school_hidden = false
	): array {
		$seclevel_okay = false;
		$save = '';
		$pk_name = '';
		$pk_value = '';
		$show_delete = false;
		if (
			(empty($this->security_level['save']) ||
			$this->base_acl_level < $this->security_level['save']) &&
			(empty($this->security_level['delete']) ||
			$this->base_acl_level < $this->security_level['delete'])
		) {
			return [
				'seclevel_okay' => $seclevel_okay,
				'save' => $save,
				'pk_name' => $pk_name,
				'pk_value' => $pk_value,
				'show_delete' => $show_delete,
				'old_school_hidden' => $old_school_hidden,
				'hide_delete_checkbox' => $hide_delete_checkbox
			];
		}
		if (
			!empty($this->security_level['save']) &&
			$this->base_acl_level >= $this->security_level['save']
		) {
			$seclevel_okay = true;
			if (empty($this->table_array[$this->int_pk_name]['value'])) {
				$save = $this->l->__('Save');
			} else {
				$save = $this->l->__('Update');
			}
			// print the old_school hidden if requestet
			if ($old_school_hidden === true) {
				$pk_name = $this->int_pk_name;
				$pk_value = $this->table_array[$this->int_pk_name]['value'];
			}
		} // show save part
		// show delete part only if pk is set && we want to see the delete
		if (
			!empty($this->table_array[$this->int_pk_name]['value']) &&
			!$hide_delete &&
			!empty($this->security_level['delete']) &&
			$this->base_acl_level >= $this->security_level['delete']
		) {
			$show_delete = true;
		}
		return [
			'seclevel_okay' => $seclevel_okay,
			'save' => $save,
			'pk_name' => $pk_name,
			'pk_value' => $pk_value,
			'show_delete' => $show_delete,
			'old_school_hidden' => $old_school_hidden,
			'hide_delete_checkbox' => $hide_delete_checkbox
		];
		// end of function
	}

	/**
	 * create a form element based on the settings in the element array entry
	 *
	 * @param  string      $element_name the name from the array, you want to have build
	 * @param  string|null $query        can overrule internal query data,
	 *                                   for drop down, as data comes from a reference table
	 *                                   for drop_down_text it has to be an
	 *                                   array with $key->$value
	 * @return array<string,mixed>       html settings array
	 */
	public function formCreateElement(string $element_name, ?string $query = null): array
	{
		$data = [];
		// special 2nd color for 'binary' attribut
		if (
			$this->table_array[$element_name]['type'] == 'binary' &&
			!isset($this->table_array[$element_name]['value'])
		) {
			$EDIT_FGCOLOR_T = 'edit_fgcolor_no';
		} else {
			$EDIT_FGCOLOR_T = 'edit_fgcolor';
		}
		$output_name = $this->table_array[$element_name]['output_name'];
		if (
			isset($this->table_array[$element_name]['mandatory']) &&
			$this->table_array[$element_name]['mandatory']
		) {
			$output_name .= ' *';
		}
		// create right side depending on 'definiton' in table_array
		$type = $this->table_array[$element_name]['type'];
		// set default min edit/read to 100 (admin)
		$min_edit_acl = $this->table_array[$element_name]['min_edit_acl'] ?? 100;
		$min_show_acl = $this->table_array[$element_name]['min_show_acl'] ?? 100;
		$show_value = '-';
		// view only output
		if ($this->table_array[$element_name]['type'] == 'view') {
			$data['value'] = empty($this->table_array[$element_name]['value']) ?
				$this->table_array[$element_name]['empty'] :
				$this->table_array[$element_name]['value'];
			$show_value = $data['value'];
		}
		// binary true/false element
		if ($this->table_array[$element_name]['type'] == 'binary') {
			$data['checked'] = 0;
			for ($i = (count($this->table_array[$element_name]['element_list']) - 1); $i >= 0; $i--) {
				$data['value'][] = $i;
				$data['output'][] = $this->table_array[$element_name]['element_list'][$i] ?? null;
				$data['name'] = $element_name;
				if (
					isset($this->table_array[$element_name]['value']) &&
					(($i && $this->table_array[$element_name]['value']) ||
					(!$i && !$this->table_array[$element_name]['value']))
				) {
					$data['checked'] = $this->table_array[$element_name]['value'];
					$show_value = $this->table_array[$element_name]['element_list'][$i] ?? $data['checked'];
				}

				if ($i) {
					$data['separator'] = '';
				}
			}
		}
		// checkbox element
		if ($this->table_array[$element_name]['type'] == 'checkbox') {
			$data['name'] = $element_name;
			$data['value'][] = $this->table_array[$element_name]['element_list'];
			$data['checked'] = $this->table_array[$element_name]['value'];
			// array map element list + value
			// foreach ($data['checked'] as $checked)
			$show_value = join(', ', $data['checked']);
		}
		// normal text element
		if ($this->table_array[$element_name]['type'] == 'text') {
			$data['name'] = $element_name;
			$data['value'] = $this->table_array[$element_name]['value'] ?? '';
			$data['size'] = $this->table_array[$element_name]['size'] ?? '';
			$data['length'] = $this->table_array[$element_name]['length'] ?? '';
			$show_value = $data['value'];
		}
		// password element, does not write back the value
		if ($this->table_array[$element_name]['type'] == 'password') {
			$data['name'] = $element_name;
			$data['HIDDEN_value'] = $this->table_array[$element_name]['HIDDEN_value'];
			$data['size'] = $this->table_array[$element_name]['size'] ?? '';
			$data['length'] = $this->table_array[$element_name]['length'] ?? '';
		}
		// date (YYYY-MM-DD)
		if ($this->table_array[$element_name]['type'] == 'date') {
			$data['name'] = $element_name;
			$data['value'] = $this->table_array[$element_name]['value'] ?? '';
			$show_value = $data['value'];
		}
		// date time (no sec) (YYYY-MM-DD HH:mm)
		if ($this->table_array[$element_name]['type'] == 'datetime') {
			$data['name'] = $element_name;
			$data['value'] = $this->table_array[$element_name]['value'] ?? '';
			$show_value = $data['value'];
		}
		// textarea
		if ($this->table_array[$element_name]['type'] == 'textarea') {
			$data['name'] = $element_name;
			$data['value'] = $this->table_array[$element_name]['value'] ?? '';
			$data['rows'] = $this->table_array[$element_name]['rows'] ?? '';
			$data['cols'] = $this->table_array[$element_name]['cols'] ?? '';
			$show_value = $data['value'];
		}
		// for drop_down_*
		if (preg_match("/^drop_down_/", $this->table_array[$element_name]['type'])) {
			$type = 'drop_down';
			// outer query overrules inner
			if (empty($query) && !empty($this->table_array[$element_name]['query'])) {
				$query = $this->table_array[$element_name]['query'];
			}
		}
		// for drop_down_db*
		$data['drop_down_input'] = 0;
		if (preg_match("/^drop_down_db/", $this->table_array[$element_name]['type'])) {
			// if still NO query
			if (empty($query)) {
				// select pk_name, input_name from table_name (order by order_by)
				$query = "SELECT "
					. (
						(
							isset($this->table_array[$element_name]['select_distinct']) &&
							$this->table_array[$element_name]['select_distinct']
						) ? "DISTINCT" : ''
					) . " "
					. $this->table_array[$element_name]['pk_name'] . ", "
					. $this->table_array[$element_name]['input_name'] . " ";
				if (!empty($this->table_array[$element_name]['order_by'])) {
					$query .= ", " . $this->table_array[$element_name]['order_by'] . " ";
				}
				$query .= "FROM " . $this->table_array[$element_name]['table_name'];
				// possible where statements
				if (!empty($this->table_array[$element_name]['where'])) {
					$query .= " WHERE " . $this->table_array[$element_name]['where'];
				}
				// not self where
				if (
					!empty($this->table_array[$element_name]['where_not_self']) &&
					isset($this->table_array[$this->int_pk_name]['value']) &&
					$this->table_array[$this->int_pk_name]['value']
				) {
					// check if query has where already
					if (strstr($query, 'WHERE') === false) {
						$query .= " WHERE ";
					} else {
						$query .= " AND ";
					}
					$query .= " " . $this->int_pk_name . " <> " . $this->table_array[$this->int_pk_name]['value'];
				}
				// possible order statements
				if (!empty($this->table_array[$element_name]['order_by'])) {
					$query .= " ORDER BY " . $this->table_array[$element_name]['order_by'];
				}
			}
			// set output data
			$data['selected'] = '';
			$data['name'] = $element_name;
			$data['value'][] = '';
			$data['output'][] = $this->l->__('Please choose .. . ');
			while (is_array($res = $this->dbReturn($query))) {
				$data['value'][] = $res[0];
				$data['output'][] = $res[1];
				if (
					isset($this->table_array[$element_name]['value']) &&
					$this->table_array[$element_name]['value'] == $res[0]
				) {
					$data['selected'] = $this->table_array[$element_name]['value'];
					$show_value = $res[1];
				}
			}
			// for _input put additional field next to drop down
			if (preg_match("/^drop_down_db_input/", $this->table_array[$element_name]['type'])) {
				$data['drop_down_input'] = 1;
				// pre fill the temp if empty and other side is selected, only for same_db
				if (
					$this->table_array[$element_name]['type'] == 'drop_down_db_input_same_db' &&
					!$this->table_array[$element_name]['input_value'] &&
					$this->table_array[$element_name]['value']
				) {
					$this->table_array[$element_name]['input_value'] = $this->table_array[$element_name]['value'];
				}
				$data['input_value'] = $this->table_array[$element_name]['input_value'];
				$data['input_name'] = $this->table_array[$element_name]['input_name']
					. (($this->table_array[$element_name]['type'] == 'drop_down_db_input_same_db') ? '_temp' : '');
				$data['input_size'] = $this->table_array[$element_name]['size'];
				$data['input_length'] = $this->table_array[$element_name]['length'];
			}
		}
		// drop down array
		if ($this->table_array[$element_name]['type'] == 'drop_down_array') {
			$data['selected'] = '';
			$data['name'] = $element_name;
			$data['value'][] = '';
			$data['output'][] = $this->l->__('Please choose .. . ');
			// outer query overrules inner
			foreach ($query as $key => $value) {
				$data['value'][] = $key;
				$data['output'][] = $value;
				if ($this->table_array[$element_name]['value'] == $key) {
					$data['selected'] = $this->table_array[$element_name]['value'];
					$show_value = $value;
				}
			}
		}
		// radio array
		if ($this->table_array[$element_name]['type'] == 'radio_array') {
			if (!$query) {
				$query = $this->table_array[$element_name]['query'];
			}
			$data['name'] = $element_name;
			foreach ($query as $key => $value) {
				$data['value'][] = $key;
				$data['output'][] = $value;
				if ($this->table_array[$element_name]['value'] == $key) {
					$data['checked'] = $this->table_array[$element_name]['value'];
					$show_value = $value;
				}
				$data['separator'] = '';
			}
		}
		// for media / not yet implemented
		if ($this->table_array[$element_name]['type'] == 'media') {
			//media::insert_file($element_name,$this->table_array[$element_name]['value'],$query);
		}
		// order button
		if ($this->table_array[$element_name]['type'] == 'order') {
			$data['output_name'] = $this->table_array[$element_name]['output_name'];
			$data['name'] = $element_name;
			$data['value'] = $this->table_array[$element_name]['value'] ?? 0;
			$data['col_name'] = $this->col_name;
			$data['table_name'] = $this->table_name;
			$data['query'] = $query !== null ? urlencode($query) : '';
		}
		// file upload
		if ($this->table_array[$element_name]['type'] == 'file') {
			$data['name'] = $element_name;
			// if file for this exsists, print 'delete, view stuff'
			if ($this->table_array[$element_name]['value']) {
				$data['content'] = 1;
				$data['url'] = $this->table_array[$element_name]['open_dir']
					. $this->table_array[$element_name]['value'];
				$data['output'] = $this->table_array[$element_name]['value'];
				$data['value'] = $this->table_array[$element_name]['value'];
			}
		}
		return [
			'output_name' => $output_name,
			'color' => $EDIT_FGCOLOR_T,
			'type' => $type,
			'data' => $data,
			'show_value' => $show_value,
			'allow_edit' => $this->base_acl_level >= $min_edit_acl ? 1 : 0,
			'allow_show' => $this->base_acl_level >= $min_show_acl ? 1 : 0,
		];
	}

	/**
	 * full error message string for output
	 * checks each filled entry to the table array defined error checks
	 * should be cought like this ...
	 * if ($msg = $form->form_error_check())
	 *   $error=1;
	 *
	 * @return void has no return
	 */
	public function formErrorCheck(): void
	{
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			// skip if we are not allowe to write this anyway
			// $this->log->debug('ERROR CHECK', 'ACL K: ' . $key . ', '
			// 	. ($value['min_edit_acl'] ?? 100) . ' < ' . $this->base_acl_level);
			if ($this->base_acl_level < ($value['min_edit_acl'] ?? 100)) {
				continue;
			}
			//if ($value['mandatory'] && $value['error_check'])
			// if error value set && somethign input, check if input okay
			if (
				isset($value['error_check']) &&
				isset($this->table_array[$key]['value']) &&
				!empty($this->table_array[$key]['value'])
			) {
				$this->log->debug('ERROR CHECK', 'Key: ' . $key . ' => ' . $value['error_check']);
				// each error check can be a piped seperated value, lets split it
				// $this->log->debug('edit', $value['error_check']);
				foreach (explode('|', $value['error_check']) as $error_check) {
					switch ($error_check) {
						case 'number':
							if (!is_numeric($this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a vailid Number for the <b>%s</b> Field!<br>'),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'date': // YYYY-MM-DD
							if (!\CoreLibs\Combined\DateTime::checkDate($this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__(
										'Please enter a valid date (YYYY-MM-DD) for the <b>%s</b> Field!<br>'
									),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'time': // HH:MM[:SS]
							if (!\CoreLibs\Combined\DateTime::checkDateTime($this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__(
										'Please enter a valid time (HH:mm[:SS]) for the <b>%s</b> Field!<br>'
									),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'datetime': // YYYY-MM-DD HH:MM[:SS]
							if (!\CoreLibs\Combined\DateTime::checkDateTime($this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__(
										'Please enter a valid date time (YYYY-MM-DD HH:mm) '
											. 'for the <b>%s</b> Field!<br>'
									),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'intervalshort': // ony interval n [Y/M/D] only
							if (
								!preg_match(
									"/^\d{1,3}\ ?([ymd]{1}|day(s)?|year(s)?|month(s)?)$/i",
									$this->table_array[$key]['value']
								)
							) {
								$this->msg .= sprintf(
									$this->l->__(
										'Please enter a valid time interval in the format '
										. '<length> Y|M|D for the <b>%s</b> Field!<br>'
									),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'email':
							if (!preg_match("/$this->email_regex/", $this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__(
										'Please enter a valid E-Mail Address for the <b>%s</b> Field!<br>'
									),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						// check unique, check if field in table is not yet exist
						case 'unique':
							$q = 'SELECT ' . $key . ' AS unique_row '
								. 'FROM ' . $this->table_name . ' '
								. 'WHERE ' . $key . ' = '
								. "'" . $this->dbEscapeString($this->table_array[$key]['value']) . "'";
							if ($this->table_array[$this->int_pk_name]['value']) {
								$q .= ' AND ' . $this->int_pk_name . ' <> '
									. $this->table_array[$this->int_pk_name]['value'];
							}
							if (
								is_array($s_res = $this->dbReturnRow($q)) &&
								!empty($s_res['unique_row'])
							) {
								$this->msg .= sprintf(
									$this->l->__('The field <b>%s</b> can be used only once!<br>'),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'custom':
							if (
								!preg_match($this->table_array[$key]['error_regex'], $this->table_array[$key]['value'])
							) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a valid (%s) input for the <b>%s</b> Field!<br>'),
									$this->table_array[$key]['error_example'],
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'alphanumericspace':
							// $this->log->debug('edit', 'IN Alphanumericspace');
							if (!preg_match("/^[0-9A-Za-z_\-\ ]+$/", $this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a valid alphanumeric (Numbers and Letters, -, _ '
										. 'and spaces allowed) value for the <b>%s</b> Field!<br>'),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'alphanumeric':
							// $this->log->debug('edit', 'IN Alphanumeric');
							if (!preg_match("/^[0-9A-Za-z_\-]+$/", $this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a valid alphanumeric (Numbers and Letters only '
										. 'also - and _, no spaces) value for the <b>%s</b> Field!<br>'),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						// this one also allows @ and .
						case 'alphanumericextended':
							// $this->log->debug('edit', 'IN Alphanumericextended');
							if (!preg_match("/^[0-9A-Za-z_\-@\.]+$/", $this->table_array[$key]['value'])) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a valid alphanumeric extended (Numbers, Letters, -,  '
										. '_, @ and . only, no spaces) value for the <b>%s</b> Field!<br>'),
									$this->table_array[$key]['output_name']
								);
							}
							break;
						case 'password':
							// password can only be alphanumeric + special chars
							// password and CONFIRM_password need to be the same
							if ($this->table_array[$key]['value'] != $this->table_array[$key]['CONFIRM_value']) {
								// error
							}
							break;
						case 'json':
							// check if valid json
							$json_out = json_decode($this->table_array[$key]['value'], true);
							$this->log->debug('JSON ENCODE', 'LAST ERROR: ' . json_last_error()
								. ' WITH: ' . $this->table_array[$key]['value']);
							if (json_last_error()) {
								$this->msg .= sprintf(
									$this->l->__('Please enter a valid JSON string for the field <b>%s<b>: %s'),
									$this->table_array[$key]['output_name'],
									json_last_error_msg()
								);
							}
							break;
					} // switch
				} // for each error to check
			} elseif (
				isset($value['mandatory']) &&
				$value['mandatory'] &&
				(
					// for all 'normal' fields
					(
						$this->table_array[$key]['type'] != 'password' &&
						$this->table_array[$key]['type'] != 'drop_down_db_input' &&
						!$this->table_array[$key]['value']
					) ||
					// for drop_down_db_input check if one of both fields filled
					(
						$this->table_array[$key]['type'] == 'drop_down_db_input' &&
						!$this->table_array[$key]['input_value'] &&
						!$this->table_array[$key]['value']
					) ||
					// for password
					(
						$this->table_array[$key]['type'] == 'password' &&
						!$this->table_array[$key]['value'] &&
						!$this->table_array[$key]['HIDDEN_value']
					)
				)
				// main if end
			) {
				// if mandatory && no input
				// $this->log->debug('form', 'A: ' . $this->table_array[$key]['type'] . ' -- '
				//	. $this->table_array[$key]['input_value'] . ' -- ' . $this->table_array[$key]['value']);
				if (
					empty($this->table_array[$key]['value']) &&
					$this->table_array[$key]['type'] != 'binary'
				) {
					$this->msg .= sprintf(
						$this->l->__('Please enter something into the <b>%s</b> field!<br>'),
						$this->table_array[$key]['output_name']
					);
				}
			} // mandatory
			// check file upload
			if (
				isset($this->table_array[$key]['type']) &&
				$this->table_array[$key]['type'] == 'file' &&
				$GLOBALS['_FILES'][$key . '_file']['name'] &&
				is_array($this->table_array[$key]['accept_type'])
			) {
				// check against allowed types
				$mime_okay = 0;
				foreach ($this->table_array[$key]['accept_type'] as $mime_type) {
					if ($GLOBALS['_FILES'][$key . '_file']['type'] == $mime_type) {
						$mime_okay = 1;
					}
				}
				if (!$mime_okay) {
					$this->msg .= sprintf(
						$this->l->__('Uploaded File <b>%s</b> has MIME Type <b>%s</b> which is not in '
							. 'theallowed MIME List for Upload Field <b>%s</b>!<br>'),
						$GLOBALS['_FILES'][$key . '_file']['name'],
						$GLOBALS['_FILES'][$key . '_file']['type'],
						$this->table_array[$key]['output_name']
					);
				}
			}
		} // while
		// do check for reference tables
		reset($this->reference_array);
		foreach ($this->reference_array as $key => $value) {
			// skip if not allowed to write
			if (
				$this->base_acl_level < ($this->reference_array[$key]['min_edit_acl'] ?? 100)
			) {
				continue;
			}
			if (
				isset($this->reference_array[$key]['mandatory']) &&
				$this->reference_array[$key]['mandatory'] &&
				!$this->reference_array[$key]['selected'][0]
			) {
				$this->msg .= sprintf(
					$this->l->__('Please select at least one Element from field <b>%s</b>!<br>'),
					$this->reference_array[$key]['output_name']
				);
			}
		}
		// $this->log->debug('edit_error', 'QS: <pre>' . print_r($_POST, true) . '</pre>');
		if (is_array($this->element_list)) {
			// check the mandatory stuff
			// if mandatory, check that at least on pk exists or
			// if at least the mandatory field is filled
			foreach ($this->element_list as $table_name => $reference_array) {
				if (!is_array($reference_array)) {
					$reference_array = [];
				}
				// skip if not allowed to write
				if (
					$this->base_acl_level < ($this->reference_array['min_edit_acl'] ?? 100)
				) {
					continue;
				}
				// set pk/fk id for this
				$_pk_name = '';
				$_fk_name = '';
				foreach ($reference_array['elements'] as $_name => $_data) {
					if (isset($_data['pk_id'])) {
						$_pk_name = $_name;
					}
					if (isset($_data['fk_id'])) {
						$_fk_name = $_name;
					}
				}
				// get the leasy of keys from the elements array
				$keys = array_keys($reference_array['elements']);
				// prefix
				$prfx = $reference_array['prefix'] ? $reference_array['prefix'] . '_' : '';
				// get max elements
				$max = 0;
				foreach ($keys as $key) {
					if (
						isset($_POST[$prfx . $key]) &&
						is_array($_POST[$prfx . $key]) &&
						count($_POST[$prfx . $key]) > $max
					) {
						$max = count($_POST[$prfx . $key]);
					}
					// $this->log->debug('edit_error_chk', 'KEY: ' . $prfx . $key . ' | count: '
					//	. count($_POST[$prfx.$key]) . ' | M: $max');
					// $this->log->debug('edit_error_chk', 'K: ' . $_POST[$prfx.$key] . ' | ' . $_POST[$prfx.$key][0]);
				}
				// $this->log->debug('POST ARRAY', $this->log->prAr($_POST));
				// init variables before inner loop run
				$mand_okay = 0;
				$mand_name = '';
				$row_okay = [];
				$default_wrong = [];
				$error = [];
				$element_set = [];
				# check each row
				for ($i = 0; $i < $max; $i++) {
					// either one of the post pks is set, or the mandatory
					foreach ($reference_array['elements'] as $el_name => $data_array) {
						if (
							isset($data_array['mandatory']) &&
							$data_array['mandatory']
						) {
							$mand_name = $data_array['output_name'];
						}
						// check if there is a primary ket inside, so it is okay
						if (
							isset($data_array['pk_id']) &&
							count($_POST[$prfx . $el_name]) &&
							!empty($reference_array['mandatory'])
						) {
							$mand_okay = 1;
						}
						// we found a mandatory field. check now if one is set to satisfy the main mandatory
						// also check, if this field is mandatory and its not set, but any other, throw an error
						// $this->log->debug('edit_error_chk', 'RG error - Data[' . $prfx . $el_name
						//	. ': ' . $_POST[$prfx . $el_name][$i] . ' | '
						// 	. $_POST[$prfx . $el_name] . ' - ' . $reference_array['enable_name'] . ' - '
						// 	. $_POST[$reference_array['enable_name']][$_POST[$prfx . $el_name][$i]]);
						if (
							!empty($data_array['mandatory']) &&
							!empty($_POST[$prfx . $el_name][$i])
						) {
							$mand_okay = 1;
							$row_okay[$i] = 1;
						} elseif (
							!empty($data_array['type']) &&
							$data_array['type'] == 'radio_group' &&
							!isset($_POST[$prfx . $el_name])
						) {
							// radio group and set where one not active
							// $this->log->debug('edit_error_chk', 'RADIO GROUP');
							$row_okay[$_POST[$prfx . $el_name][$i] ?? 0] = 0;
							$default_wrong[$_POST[$prfx . $el_name][$i] ?? 0] = 1;
							$error[$_POST[$prfx . $el_name][$i] ?? 0] = 1;
						} elseif (
							isset($_POST[$prfx . $el_name][$i]) &&
							!isset($error[$i])
						) {
							// $this->log->debug('edit_error_chk', '[$i]');
							$element_set[$i] = 1;
							$row_okay[$i] = 1;
						} elseif (
							!empty($data_array['mandatory']) &&
							!$_POST[$prfx . $el_name][$i]
						) {
							$row_okay[$i] = 0;
						}
						// do optional error checks like for normal fields
						// currently active: unique/alphanumeric
						if (!empty($data_array['error_check'])) {
							foreach (explode('|', $data_array['error_check']) as $error_check) {
								switch ($error_check) {
									// check unique, check if field is filled and not same in _POST set
									case 'unique':
										// must be set for double check
										if (
											$_POST[$prfx . $el_name][$i] &&
											count(array_keys(
												$_POST[$prfx . $el_name],
												$_POST[$prfx . $el_name][$i]
											)) >= 2
										) {
											$this->msg .= sprintf(
												$this->l->__('The field <b>%s</b> in row <b>%s</b> '
													. 'can be used only once!<br>'),
												$reference_array['output_name'],
												$i
											);
											$_POST['ERROR'][$prfx][$i] = 1;
										}
										break;
									case 'alphanumericspace':
										// only check if set
										if (
											$_POST[$prfx . $el_name][$i] &&
											!preg_match("/^[0-9A-Za-z\ ]+$/", $_POST[$prfx . $el_name][$i])
										) {
											$this->msg .= sprintf(
												$this->l->__('Please enter a valid alphanumeric (Numbers and Letters, '
													. 'spaces allowed) value for the '
													. '<b>%s</b> Field and row <b>%s</b>!<br>'),
												$reference_array['output_name'],
												$i
											);
											$_POST['ERROR'][$prfx][$i] = 1;
										}
										break;
								}
							}
						}
					} // if main mandatory
				}

				// main mandatory is met -> error msg
				if (
					!$mand_okay &&
					!empty($reference_array['mandatory'])
				) {
					$this->msg .= sprintf(
						$this->l->__('You need to enter at least one data set for field <b>%s</b>!<br>'),
						$reference_array['output_name']
					);
				}
				for ($i = 0; $i < $max; $i++) {
					if (!isset($row_okay[$i]) && isset($element_set[$i])) {
						$this->msg .= sprintf(
							$this->l->__('The row <b>%s</b> has <b>%s</b> set as mandatory, '
							. 'please fill at least this field out<br>'),
							$i,
							$mand_name
						);
					}
					if (!isset($row_okay[$i]) && isset($default_wrong[$i])) {
						$this->msg .= sprintf(
							$this->l->__('The row <b>%s</b> would have a default setting, but it would be disabled. '
								. 'Please change the default setting and save again<br>'),
							$i
						);
					}
				}
			} // each element list
		}
		if ($this->msg) {
			$this->error = 1;
		}
	}

	/**
	 * sets the order to the maximum, if order flag is set in array
	 *
	 * @return array<mixed> table array with set order number
	 */
	public function formSetOrder(): array
	{
		// get order name
		$order_name = $this->formGetColNameFromKey('order');
		if (empty($order_name)) {
			return $this->table_array;
		}
		// first check out of order ...
		if (empty($this->table_array[$order_name]['value'])) {
			// set order (read max)
			$q = 'SELECT MAX(' . $order_name . ') + 1 AS max_page_order '
				. 'FROM ' . $this->table_name;
			if (
				is_array($res = $this->dbReturnRow($q)) &&
				!empty($res['max_page_order'])
			) {
				$this->table_array[$order_name]['value'] = $res['max_page_order'];
			}
			// frist element is 0 because NULL gets returned, set to 1
			if (!$this->table_array[$order_name]['value']) {
				$this->table_array[$order_name]['value'] = 1;
			}
		} elseif (!empty($this->table_array[$this->int_pk_name]['value'])) {
			$q = 'SELECT ' . $order_name . ' AS order_name '
				. 'FROM ' . $this->table_name . ' '
				. 'WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
			if (
				is_array($res = $this->dbReturnRow($q)) &&
				!empty($res['order_name'])
			) {
				$this->table_array[$order_name]['value'] = $res['order_name'];
			}
		}
		return $this->table_array;
	}

	/**
	 * resets all values in table_array and in the reference tables
	 *
	 * @return void has no return
	 */
	public function formUnsetTableArray(): void
	{
		$this->pk_id = null;
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			unset($this->table_array[$key]['value']);
			unset($this->table_array[$key]['input_value']);
			// if preset var present preset
			if (isset($this->table_array[$key]['preset'])) {
				$this->table_array[$key]['value'] = $this->table_array[$key]['preset'];
			}
		}
		if (is_array($this->reference_array)) {
			if (!is_array($this->reference_array)) {
				$this->reference_array = [];
			}
			reset($this->reference_array);
			foreach ($this->reference_array as $key => $value) {
				unset($this->reference_array[$key]['selected']);
			}
		}
		$this->warning = 1;
		$this->msg = $this->l->__('Cleared for new Dataset!');
	}

	/**
	 * load a table & reference
	 *
	 * @param  string|null $pk_id overrule pk_id
	 * @return void               has no return
	 */
	public function formLoadTableArray(?string $pk_id = null): void
	{
		if ($pk_id) {
			$this->pk_id = $pk_id;
		}
		$this->table_array = $this->dbRead(true);

		// reset all temp fields
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			unset($this->table_array[$key]['input_value']);
		}

		if (is_array($this->reference_array)) {
			// load each reference_table
			if (!is_array($this->reference_array)) {
				$this->reference_array = [];
			}
			reset($this->reference_array);
			foreach ($this->reference_array as $key => $value) {
				unset($this->reference_array[$key]['selected']);
				$q = 'SELECT ' . $this->reference_array[$key]['other_table_pk']
					. ' FROM ' . $this->reference_array[$key]['table_name']
					. ' WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
				while (is_array($res = $this->dbReturn($q))) {
					$this->reference_array[$key]['selected'][] = $res[$this->reference_array[$key]['other_table_pk']];
				}
			}
		}
		$this->warning = 1;
		$this->msg = $this->l->__('Dataset has been loaded!<br>');
	}

	/**
	 * save a table, reference and all input fields
	 * note that the addslashes flag here is passed on to the dbWrite method
	 * it only does html conversion, add slashes for DB is done automatically
	 *
	 * @param  bool $addslashes override internal addslasahes flag (default false)
	 * @return void             has no return
	 */
	public function formSaveTableArray(bool $addslashes = false)
	{
		// for drop_down_db_input check if text field is filled and if, if not yet in db ...
		// and upload files
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			// drop_down_db with input + reference table
			// $this->log->debug('form', 'A: ' . $this->table_array[$key]['type']
			//	. ' --- ' . $this->table_array[$key]['input_value']);
			if (
				isset($this->table_array[$key]['type']) &&
				$this->table_array[$key]['type'] == 'drop_down_db_input' &&
				$this->table_array[$key]['input_value']
			) {
				// $this->log->debug('form', 'HERE');
				// check if this text name already exists (lowercase compare)
				$q = 'SELECT ' . $this->table_array[$key]['pk_name'] . ' AS pk_name '
					. ' FROM ' . $this->table_array[$key]['table_name']
					. ' WHERE LCASE(' . $this->table_array[$key]['input_name'] . ') = '
					. "'" . $this->dbEscapeString(strtolower($this->table_array[$key]['input_value'])) . "'";
				// if a where was given, add here
				if ($this->table_array[$key]['where']) {
					$q .= ' AND ' . $this->table_array[$key]['where'];
				}
				if (
					is_array($s_res = $this->dbReturnRow($q)) &&
					!empty($s_res['pk_name'])
				) {
					// $this->table_array[$key]['value'] = $pk_name_temp;
					$this->table_array[$key]['value'] = $s_res['pk_name'];
				} else {
					// if a where was given, set this key also [dangerous!]
					// postgreSQL compatible insert
					$q = 'INSERT INTO ' . $this->table_array[$key]['table_name']
						. ' (' . $this->table_array[$key]['input_name'] . ') VALUES ('
						. "'" . $this->dbEscapeString($this->table_array[$key]['input_value']) . "')";
					$this->dbExec($q);
					if (!empty($this->table_array[$key]['where']) && is_numeric($this->dbGetInsertPK())) {
						// make an update on the just inseted data with the where data als update values
						$q = 'UPDATE ' . $this->table_array[$key]['table_name'] . ' SET ';
						$q .= $this->table_array[$key]['where'] . ' ';
						$q .= 'WHERE ' . $this->table_array[$key]['pk_name'] . ' = ' . $this->dbGetInsertPK();
						$this->dbExec($q);
					}
					$this->table_array[$key]['value'] = $this->dbGetInsertPK();
				} // set value from DB through select or insert
				unset($this->table_array[$key]['input_value']);
			} // if it is certain field type && if there is something in the temp field
			// drop_down_db with input and in same table
			if (
				isset($this->table_array[$key]['type']) &&
				$this->table_array[$key]['type'] == 'drop_down_db_input_same_db' &&
				$this->table_array[$key]['input_value']
			) {
				// if drop down & input are different
				if ($this->table_array[$key]['input_value'] != $this->table_array[$key]['value']) {
					// check if 'right input' is in DB
					$q = 'SELECT ' . $this->table_array[$key]['input_name'] . ' AS temp '
						. ' FROM ' . $this->table_array[$key]['table_name']
						. ' WHERE LCASE(' . $this->table_array[$key]['input_name'] . ') = '
						. "'" . strtolower($this->dbEscapeString($this->table_array[$key]['input_value'])) . "'";
					// if a where was given, add here
					if ($this->table_array[$key]['where']) {
						$q .= ' AND ' . $this->table_array[$key]['where'];
					}
					if (
						is_array($s_res = $this->dbReturnRow($q)) &&
						empty($s_res['temp'])
					) {
						$this->table_array[$key]['value'] = $this->table_array[$key]['input_value'];
					} else {
						// found in DB
						$this->table_array[$key]['input_value'] = $this->table_array[$key]['value'];
					}
				} // key difference ?
			} // for same_db drop down

			// upload & save files to locations
			if (isset($this->table_array[$key]['type']) && $this->table_array[$key]['type'] == 'file') {
				// if smth in $$key_file -> save or overwrite
				// if smth in $key && $$key_delete && !$$key_file-> delte
				// if smth in $key, keep as is
				// $_file=$key . '_file';
				// $_delete=$key . '_delete';
				// $this->log->debug('form', 'UF: ' . $GLOBALS['_FILES'][$key . '_file']['name']);
				// $this->log->debug('form', 'delete: ' . $key . '_delete => ' . $GLOBALS[$key . '_delete']);
				if ($GLOBALS['_FILES'][$key . '_file']['name']) {
					// check if dir exists
					if (is_dir($this->table_array[$key]['save_dir'])) {
						//if a slash at the end (if not add slash)
						if (!preg_match("|/$|", $this->table_array[$key]['save_dir'])) {
							$this->table_array[$key]['save_dir'] .= '/';
						}
						if (
							move_uploaded_file(
								$GLOBALS['_FILES'][$key . '_file']['tmp_name'],
								$this->table_array[$key]['save_dir'] . $GLOBALS['_FILES'][$key . '_file']['name']
							)
						) {
							// make it unique with a unique number at the beginning
							$this->table_array[$key]['value'] = uniqid((string)rand(), true)
								. '_' . $GLOBALS['_FILES'][$key . '_file']['name'];
						} else {
							$this->msg .= $this->l->__('File could not be copied to target directory! '
								. 'Perhaps wrong directory permissions . ');
							$this->error = 1;
						} // could not move file (dir permissions?)
					} else {
						$this->msg .= sprintf(
							$this->l->__('Target Directory \'%s\' is not a vaild directory!'),
							$this->table_array[$key]['save_dir']
						);
						$this->error = 1;
					} // could not dir check (dir wrong??)
				}
				if (
					!empty($GLOBALS[$key . '_delete']) &&
					$this->table_array[$key]['value'] && !$GLOBALS['_FILES'][$key . '_file']['name']
				) {
					unlink($this->table_array[$key]['save_dir'] . $this->table_array[$key]['value']);
					unset($this->table_array[$key]['value']);
				}
			}

			// for password crypt it as blowfish, or if not available MD5
			if (isset($this->table_array[$key]['type']) && $this->table_array[$key]['type'] == 'password') {
				if ($this->table_array[$key]['value']) {
					// use the better new passwordSet instead of crypt based
					$this->table_array[$key]['value'] =
						\CoreLibs\Security\Password::passwordSet($this->table_array[$key]['value']);
					$this->table_array[$key]['HIDDEN_value'] = $this->table_array[$key]['value'];
				} else {
					// $this->table_array[$key]['HIDDEN_value'] =
				}
			}
		} // go through each field

		// set object order (if necessary)
		$this->formSetOrder();
		// $this->log->debug('PK NAME SET', "PK NAME: " . $this->pk_name . "/" . $this->int_pk_name . ": "
		//	. $this->table_array[$this->pk_name]['value'] . "/"
		//	. $this->table_array[$this->int_pk_name]['value']);
		// write the object
		$this->dbWrite($addslashes, [], true);
		// write reference array (s) if necessary
		if (is_array($this->reference_array)) {
			if (!is_array($this->reference_array)) {
				$this->reference_array = [];
			}
			reset($this->reference_array);
			foreach ($this->reference_array as $reference_array) {
				$q = 'DELETE FROM ' . $reference_array['table_name']
					. ' WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
				$this->dbExec($q);
				$q = 'INSERT INTO ' . $reference_array['table_name']
					. ' (' . $reference_array['other_table_pk'] . ', ' . $this->int_pk_name . ') VALUES ';
				for ($i = 0, $i_max = count($reference_array['selected']); $i < $i_max; $i++) {
					$t_q = '(' . $reference_array['selected'][$i] . ', '
						. $this->table_array[$this->int_pk_name]['value'] . ')';
					$this->dbExec($q . $t_q);
				}
			} // foreach reference arrays
		} // if reference arrays
		// write element list
		if (is_array($this->element_list)) {
			$type = [];
			reset($this->element_list);
			foreach ($this->element_list as $table_name => $reference_array) {
				// early skip if not enought ACL
				if ($this->base_acl_level < ($reference_array['min_edit_acl'] ?? 100)) {
					continue;
				}
				// init arrays
				$q_begin = [];
				$q_middle = [];
				$q_end = [];
				$q_names = [];
				$q_data = [];
				$q_values = [];
				$no_write = [];
				$block_write = [];
				// get the number of keys from the elements array
				$keys = array_keys($reference_array['elements']);
				// element prefix name
				$prfx = $reference_array['prefix'] ? $reference_array['prefix'] . '_' : '';
				// get max elements
				$max = 0;
				foreach ($keys as $key) {
					if (
						isset($_POST[$prfx . $key]) &&
						is_array($_POST[$prfx . $key]) &&
						count($_POST[$prfx . $key]) > $max
					) {
						$max = count($_POST[$prfx . $key]);
					}
				}
				$this->log->debug('REF ELEMENT', 'RUN FOR TABLE: ' . $table_name);
				// $this->log->debug('edit_error', 'MAX: $max');
				// check if there is a hidden key, update, else insert
				foreach ($reference_array['elements'] as $el_name => $data_array) {
					// $this->log->debug('edit_error_query', 'QUERY: ' . $this->log->prAr($_POST));
					// go through all submitted data
					// for ($i = 0; $i < count($_POST[$el_name]); $i++)
					for ($i = 0; $i < $max; $i++) {
						if (!isset($no_write[$i])) {
							$no_write[$i] = 0;
							// $this->log->debug('REF ELEMENT', 'Init no write for pos: ' . $i);
						}
						if (!isset($block_write[$i])) {
							$block_write[$i] = 0;
							// $this->log->debug('REF ELEMENT', 'Init block write for pos: ' . $i);
						}
						// if we have enable name & delete set,
						// then only insert/update those which are flagged as active
						// check if mandatory field is set, if not set 'do not write flag'
						if (
							isset($data_array['mandatory']) &&
							$data_array['mandatory'] &&
							(
								!isset($_POST[$prfx . $el_name][$i]) ||
								(isset($_POST[$prfx . $el_name][$i]) &&
								empty($_POST[$prfx . $el_name][$i]))
							)
						) {
							$no_write[$i] = 1;
						}
						// $this->log->debug('REF ELEMENT', "[$i] [" . $prfx . $el_name . "]: '
						//	. "MANDATORY: " . isset($data_array['mandatory'])
						//	. " SET: " . isset($_POST[$prfx . $el_name][$i])
						//	. ", EMPTY: " . empty($_POST[$prfx . $el_name][$i])
						//	. " | DO ACTION " . ((!isset($_POST[$prfx . $el_name][$i]) ||
						//		(isset($_POST[$prfx . $el_name][$i]) &&
						//		empty($_POST[$prfx . $el_name][$i]))) ? 'YES' : 'NO'
						//	) . " => NO WRITE: " . $no_write[$i]);
						if (
							!empty($reference_array['enable_name']) &&
							isset($reference_array['delete']) && $reference_array['delete'] &&
							(!isset($_POST[$reference_array['enable_name']][$i]) ||
							empty($_POST[$reference_array['enable_name']][$i]))
						) {
							$no_write[$i] = 1;
						}
						// $this->log->debug('REF ELEMENT', "[$i] [" . $prfx . $el_name . "]: ENABLED NAME: "
						//	. isset($reference_array['enable_name'])
						//	. ", DELETE: " . isset($reference_array['delete']) . ", NOT ENABLED FOR POS: "
						//	. (isset($reference_array['enable_name']) ?
						//		isset($_POST[$reference_array['enable_name']][$i]) : '-'));
						$this->log->debug('REF ELEMENT', "[$i] [" . $prfx . $el_name . "]: WRITE: " . $no_write[$i]);
						// flag if data is in the text field and we are in a reference data set
						if (isset($reference_array['type']) && $reference_array['type'] == 'reference_data') {
							if (
								!empty($data_array['type']) && $data_array['type'] == 'text' &&
								isset($_POST[$prfx . $el_name][$i])
							) {
								$block_write[$i] = 1;
							}
						} else {
							$block_write[$i] = 1;
						}
						// $this->log->debug('REF ELEMENT', "[$i] [" . $prfx . $el_name . "]: "
						//	. "REFERENCE TYPE: " . isset($reference_array['type'])
						//  . ", SET REFERENCE TYPE: "
						//	. (isset($reference_array['type']) ? $reference_array['type'] : '-')
						//	. ", DATA TYPE: " . $data_array['type'] . ", SET: " . isset($_POST[$prfx . $el_name][$i])
						//	. ", => BLOCK WIRTE: " . $block_write[$i]);
						// set type and boundaries for insert/update
						if (
							isset($data_array['pk_id']) &&
							!empty($data_array['pk_id']) &&
							!empty($_POST[$prfx . $el_name][$i])
						) {
							$q_begin[$i] = 'UPDATE ' . $table_name . ' SET ';
							$q_end[$i] = ' WHERE ' . $el_name . ' = ' . $_POST[$prfx . $el_name][$i];
							$type[$i] = 'update';
							$this->log->debug('REF ELEMENT', 'SET UPDATE');
						} elseif (
							isset($data_array['pk_id']) &&
							!empty($data_array['pk_id']) &&
							empty($_POST[$prfx . $el_name][$i])
						) {
							$q_begin[$i] = 'INSERT INTO ' . $table_name . ' (';
							$q_middle[$i] = ') VALUES (';
							$q_end[$i] = ')';
							$type[$i] = 'insert';
							$this->log->debug('REF ELEMENT', 'SET INSERT');
						}
						// $this->log->debug('REF ELEMENT', "[$i] [" . $prfx . $el_name . "] "
						//	. "PK SET: " . isset($data_array['pk_id']) . '/' . empty($data_array['pk_id'])
						//	. ', KEY SET: ' . empty($_POST[$prfx . $el_name][$i])
						//	. " -> TYPE: " . (isset($type[$i]) ? $type[$i] : '-'));
						// write all data (insert/update) because I don't know until
						// all are processed if it is insert or update
						// don't write primary key backup for update
						// for reference_data type, only write if at least one text type field is set
						// $this->log->debug('edit_error', 'I: $i | EL Name: ' . $prfx . $el_name
						//	. ' | Data: ' . $_POST[$prfx . $el_name][$i] . ' | Type: ' . $type[$i]
						//	. ' | PK: ' . $data_array['pk_id'] . ', Block write: ' . $block_write[$i]);
						// only add elements that are not PK or FK flaged
						if (!isset($data_array['pk_id']) && !isset($data_array['fk_id'])) {
							// update data list
							if (isset($q_data[$i])) {
								$q_data[$i] .= ', ';
							} else {
								$q_data[$i] = '';
							}
							// insert name part list
							if (isset($q_names[$i])) {
								$q_names[$i] .= ', ';
							} else {
								$q_names[$i] = '';
							}
							// insert value part list
							if (isset($q_values[$i])) {
								$q_values[$i] .= ', ';
							} else {
								$q_values[$i] = '';
							}
							// insert column name add
							$q_names[$i] .= $el_name;
							// data part, read from where [POST]
							// radio group selections (only one can be active)
							if (isset($data_array['type']) && $data_array['type'] == 'radio_group') {
								if (isset($_POST[$prfx . $el_name]) && $i == $_POST[$prfx . $el_name]) {
									$_value = $i + 1;
								} else {
									$_value = 'NULL';
								}
							} else {
								$_value = $_POST[$prfx . $el_name][$i] ?? '';
							}
							// pre write data set. if int value, unset flagged
							// need to be set null or 0 depending on settings
							if (isset($data_array['int']) || isset($data_array['int_null'])) {
								if (!$_value && isset($data_array['int_null'])) {
									$_value = 'NULL';
								} elseif (!$_value) {
									$_value = 0;
								}
								$q_data[$i] .= $el_name . ' = ' . $_value;
								$q_values[$i] .= $_value;
							} else {
								// normal data gets escaped
								$q_data[$i] .= $el_name . ' = ' . "'" . $this->dbEscapeString($_value) . "'";
								$q_values[$i] .= "'" . $this->dbEscapeString($_value) . "'";
							}
						}
					}
				} // eche table elements
				// finalize the queries, add FK key reference for inserts and run the query
				for ($i = 0, $i_max = count($type); $i < $i_max; $i++) {
					$q = '';
					// skip empty or not fully filled rows
					if (isset($no_write[$i]) && !$no_write[$i]) {
						if (!isset($q_begin[$i])) {
							$q_begin[$i] = '';
						}
						if (!isset($q_end[$i])) {
							$q_end[$i] = '';
						}
						// if tpye is update
						if (isset($type[$i]) && $type[$i] == 'update') {
							$q = $q_begin[$i]
								. ($q_data[$i] ?? '')
								. $q_end[$i];
						// or if we have block write, then it is insert (new)
						} elseif (isset($block_write[$i]) && $block_write[$i]) {
							$q = $q_begin[$i]
								. ($q_names[$i] ?? '') . ', '
								. $this->int_pk_name
								. ($q_middle[$i] ?? '')
								. ($q_values[$i] ?? '') . ', '
								. $this->table_array[$this->int_pk_name]['value']
								. $q_end[$i];
						}
						/** @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset */
						$this->log->debug('edit', 'Pos[' . $i . '] => ' . $type[$i] . ' Q: ' . $q . '<br>');
						// write the dataset
						if ($q) {
							$this->dbExec($q);
						}
					}
				} // for each created query
			} // each element list
		}
		$this->warning = 1;
		$this->msg = $this->l->__('Dataset has been saved!<Br>');
	}

	/**
	 * delete a table and reference fields
	 *
	 * @return void
	 */
	public function formDeleteTableArray()
	{
		// remove any reference arrays
		if (is_array($this->reference_array)) {
			if (!is_array($this->reference_array)) {
				$this->reference_array = [];
			}
			reset($this->reference_array);
			foreach ($this->reference_array as $reference_array) {
				$q = 'DELETE FROM ' . $reference_array['table_name']
					. ' WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
				$this->dbExec($q);
			}
		}
		// remove any element list references
		if (is_array($this->element_list)) {
			if (!is_array($this->element_list)) {
				$this->element_list = [];
			}
			reset($this->element_list);
			foreach ($this->element_list as $table_name => $data_array) {
				$q = 'DELETE FROM ' . $table_name
					. ' WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
				$this->dbExec($q);
			}
		}
		// unlink ALL files
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			if (isset($this->table_array[$key]['type']) && $this->table_array[$key]['type'] == 'file') {
				unlink($this->table_array[$key]['save_dir'] . $this->table_array[$key]['value']);
			}
		}
		$this->dbDelete();
		$this->warning = 1;
		$this->msg = $this->l->__('Dataset has been deleted!');
	}

	/**
	 * creates HTML hidden input fields out of an hash array
	 *
	 * @param  array<mixed> $hidden_array The list of fields to be added as hidden
	 * @return array<mixed>               key -> value list of hidden fileds data
	 */
	public function formCreateHiddenFields(array $hidden_array = []): array
	{
		$hidden = [];
		if (!is_array($this->table_array)) {
			$this->table_array = [];
		}
		reset($this->table_array);
		foreach ($this->table_array as $key => $value) {
			if (
				isset($this->table_array[$key]['type']) &&
				$this->table_array[$key]['type'] == 'hidden'
			) {
				if (array_key_exists($key, $this->table_array)) {
					$hidden_array[$key] = $this->table_array[$key]['value'] ?? '';
				} else {
					$hidden_array[$key] = '';
				}
			}
		}
		reset($hidden_array);
		foreach ($hidden_array as $key => $value) {
			$hidden[] = ['key' => $key, 'value' => $value];
		}
		return $hidden;
	}

	/**
	 * creates the multiple select part for a reference_table
	 *
	 * @param  string $table_name  Table name for reference array lookup
	 * @return array<string,mixed> Reference table output array
	 */
	public function formCreateElementReferenceTable(string $table_name): array
	{
		$data = [];
		//
		$show_value = '';
		// set default min edit/read to 100 (admin)
		$min_edit_acl = $this->reference_array[$table_name]['min_edit_acl'] ?? 100;
		$min_show_acl = $this->reference_array[$table_name]['min_show_acl'] ?? 100;
		// output name
		$output_name = $this->reference_array[$table_name]['output_name'];
		// mandatory flag
		if (
			isset($this->reference_array[$table_name]['mandatory']) &&
			$this->reference_array[$table_name]['mandatory']
		) {
			$output_name .= ' *';
		}
		$data['name'] = $this->reference_array[$table_name]['other_table_pk'];
		$data['size'] = $this->reference_array[$table_name]['select_size'];
		while (is_array($res = $this->dbReturn($this->reference_array[$table_name]['query']))) {
			$data['value'][] = $res[0];
			$data['output'][] = $res[1];
			$selected = (\CoreLibs\Convert\Html::checked(
				$this->reference_array[$table_name]['selected'] ?? '',
				$res[0]
			)) ? $res[0] : '';
			$data['selected'][] = $selected;
			if (!empty($selected)) {
				if (!empty($show_value)) {
					$show_value .= ", ";
				}
				$show_value .= $res[1];
			}
		}
		$type = 'reference_table';
		return [
			'output_name' => $output_name,
			'type' => $type,
			'color' => 'edit_fgcolor',
			'data' => $data,
			'show_value' => empty($show_value) ? '-' : $show_value,
			'allow_edit' => $this->base_acl_level >= $min_edit_acl ? 1 : 0,
			'allow_show' => $this->base_acl_level >= $min_show_acl ? 1 : 0,
		];
	}

	/**
	 * create list of elements next to each other for a group of data in an input field
	 * this currently only works for a list that is filled from a sub table and creates
	 * only a connection to this one new version will allow a sub list with free input
	 * fields to directly fill a sub table to a master table
	 *
	 * @param  string $table_name  Which element entry to create
	 * @return array<string,mixed> Element for html creation
	 */
	public function formCreateElementListTable(string $table_name): array
	{
		// init data rray
		$data = [
			'delete_name' => '',
			'delete' => 0,
			'enable_name' => '',
			'prefix' => '',
			'pk_name' => '',
			'fk_name' => '',
			'type' => [],
			'output_name' => [],
			'preset' => [],
			'element_list' => [],
			'output_data' => [],
			'content' => [],
			'pos' => [],
			'table_name' => $table_name // sub table name
		];
		$show_value = '-';
		// set default min edit/read to 100 (admin)
		$min_edit_acl = $this->element_list[$table_name]['min_edit_acl'] ?? 100;
		$min_show_acl = $this->element_list[$table_name]['min_show_acl'] ?? 100;
		// output name for the viewable left table td box, prefixed with * if mandatory
		$output_name = $this->element_list[$table_name]['output_name'];
		// mandatory flag
		if (
			isset($this->element_list[$table_name]['mandatory']) &&
			$this->element_list[$table_name]['mandatory']
		) {
			$output_name .= ' *';
		}
		// delete button name, if there is one set
		if (isset($this->element_list[$table_name]['delete_name'])) {
			$data['delete_name'] = $this->element_list[$table_name]['delete_name'];
		}
		// set the enable checkbox for delete, if the delete flag is given if there is one
		if (isset($this->element_list[$table_name]['enable_name'])) {
			$data['enable_name'] = $this->element_list[$table_name]['enable_name'];
			if (isset($this->element_list[$table_name]['delete'])) {
				$data['delete'] = 1;
			}
		}
		// prefix for the elements, to not collide with names in the master set
		if (isset($this->element_list[$table_name]['prefix'])) {
			$data['prefix'] = $this->element_list[$table_name]['prefix'] . '_';
		}

		// build the select part
		if (
			!isset($this->element_list[$table_name]['elements']) ||
			!is_array($this->element_list[$table_name]['elements'])
		) {
			$this->element_list[$table_name]['elements'] = [];
		}
		reset($this->element_list[$table_name]['elements']);
		// generic data read in (counts for all rows)
		// visible list data output
		$q_select = [];
		$proto = [];
		foreach ($this->element_list[$table_name]['elements'] as $el_name => $data_array) {
			// $this->log->debug('CFG', 'El: ' . $el_name . ' -> ' . $this->log->prAr($data_array));
			// if the element name matches the read array, then set the table as a name prefix
			// this is for reading the data
			$q_select[] = $el_name;
			// prefix the name for any further data parts
			$el_name = $data['prefix'] . $el_name;
			// this are the output names (if given)
			$data['output_name'][$el_name] = $data_array['output_name'] ?? '';
			// this is the type of the field
			$data['type'][$el_name] = $data_array['type'] ?? '';
			// set the primary key name
			if (isset($data_array['pk_id'])) {
				$data['pk_name'] = $el_name;
			}
			if (isset($data_array['fk_id'])) {
				$data['fk_name'] = $el_name;
			}
			// if drop down db read data for element list from the given sub table as from the query
			// only two elements are allowed: pos 0 is key, pso 1 is visible output name
			if (isset($data_array['type']) && $data_array['type'] == 'drop_down_db') {
				while (is_array($res = $this->dbReturn($data_array['query']))) {
					/** @phan-suppress-next-line PhanTypeInvalidDimOffset */
					$this->log->debug('edit', 'Q[' . $this->dbGetQueryHash($data_array['query']) . '] pos: '
						. $this->dbGetCursorPos($data_array['query'])
						. ' | want: ' . ($data_array['preset'] ?? '-')
						. ' | set: ' . ($data['preset'][$el_name] ?? '-'));
					// first is default for this element
					if (
						isset($data_array['preset']) &&
						(!isset($data['preset'][$el_name]) || empty($data['preset'][$el_name])) &&
						($this->dbGetCursorPos($data_array['query']) == $data_array['preset'])
					) {
						$data['preset'][$el_name] = $res[0];
					}
					// split up data, 0 is id, 1 name
					$data['element_list'][$el_name][] = $res[0];
					$data['output_data'][$el_name][] = $res[1];
				}
			} elseif (isset($data_array['element_list'])) {
				$data['element_list'][$el_name] = $data_array['element_list']; // this is for the checkboxes
			}
			$this->log->debug('CREATE ELEMENT LIST TABLE', 'Table: ' . $table_name . ', Post: ' . $el_name . ' => '
				. ((isset($_POST[$el_name]) && is_array($_POST[$el_name])) ?
					'AS ARRAY'/*.$this->log->prAr($_POST[$el_name])*/ :
					'NOT SET/OR NOT ARRAY')
				. ((isset($_POST[$el_name]) && !is_array($_POST[$el_name])) ? $_POST[$el_name] : ''));
			// if error, check new line addition so we don't lose it
			if ($this->error) {
				if (isset($_POST[$el_name]) && is_array($_POST[$el_name])) {
					// this is for the new line
					$proto[$el_name] = $_POST[$el_name][(count($_POST[$el_name]) - 1)] ?? 0;
				} else {
					$proto[$el_name] = 0;
				}
			} else {
				$proto[$el_name] = '';
			}
			// $proto[$el_name] = $this->error ? $_POST[$el_name][(count($_POST[$el_name]) - 1)] : '';
		}
		// $this->log->debug('CFG DATA', 'Data: ' . $this->log->prAr($data));
		// $this->log->debug('CFG PROTO', 'Proto: ' . $this->log->prAr($proto));
		// $this->log->debug('CFG SELECT', 'Proto: ' . $this->log->prAr($q_select));
		// query for reading in the data
		$this->log->debug('edit_error', 'ERR: ' . $this->error);
		// if we got a read data, build the read select for the read, and read out the 'selected'
		/** @phan-assert array $this->element_list[$table_name]['read_data'] */
		if (isset($this->element_list[$table_name]['read_data'])) {
			// we need a second one for the query build only
			// prefix all elements with the $table name
			$_q_select = [];
			foreach ($q_select as $_pos => $element) {
				$_q_select[$_pos] = $table_name . '.' . $element;
			}
			// set if missing
			if (!isset($this->element_list[$table_name]['read_data']['pk_id'])) {
				$this->element_list[$table_name]['read_data']['pk_id'] = '';
			}
			if (!isset($this->element_list[$table_name]['read_data']['name'])) {
				$this->element_list[$table_name]['read_data']['name'] = '';
			}
			if (!isset($this->element_list[$table_name]['read_data']['table_name'])) {
				$this->element_list[$table_name]['read_data']['table_name'] = '';
			}
			// add the read names in here, prefix them with the table name
			// earch to read part is split by |
			if (!empty($this->element_list[$table_name]['read_data']['name'])) {
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
				foreach (explode('|', $this->element_list[$table_name]['read_data']['name']) as $read_name) {
					array_unshift(
						$_q_select,
						/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
						$this->element_list[$table_name]['read_data']['table_name']
							. '.' . $read_name
					);
					array_unshift($q_select, $read_name);
				}
			}
			// @phan HACK
			$data['prefix'] = $data['prefix'];
			// set the rest of the data so we can print something out
			/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
			$data['type'][$data['prefix'] . $this->element_list[$table_name]['read_data']['name']] = 'string';
			// build the read query
			$q = 'SELECT ';
			// if (!$this->table_array[$this->int_pk_name]['value'])
			// 	$q .= 'DISTINCT ';
			// prefix join key with table name, and implode the query select part
			$q .= str_replace(
				$table_name . '.'
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
					. $this->element_list[$table_name]['read_data']['pk_id'],
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
				$this->element_list[$table_name]['read_data']['table_name']
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
					. '.' . $this->element_list[$table_name]['read_data']['pk_id'],
				implode(', ', $_q_select)
			) . ' ';
			// if (
			//	!$this->table_array[$this->int_pk_name]['value'] &&
			//	$this->element_list[$table_name]['read_data']['order']
			// ) {
			// 	$q .= ', ' . $this->element_list[$table_name]['read_data']['order'] . ' ';
			// read from the read table as main, and left join to the sub table to read the actual data
			// }
			/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
			$q .= 'FROM ' . $this->element_list[$table_name]['read_data']['table_name'] . ' ';
			$q .= 'LEFT JOIN ' . $table_name . ' ';
			$q .= 'ON (';
			/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
			$q .= $this->element_list[$table_name]['read_data']['table_name'] . '.'
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
				. $this->element_list[$table_name]['read_data']['pk_id'] . ' = '
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
				. $table_name . '.' . $this->element_list[$table_name]['read_data']['pk_id'] . ' ';
			// if ($this->table_array[$this->int_pk_name]['value'])
			$q .= 'AND ' . $table_name . '.' . $this->int_pk_name . ' = '
				. (!empty($this->table_array[$this->int_pk_name]['value']) ?
					$this->table_array[$this->int_pk_name]['value'] :
					'NULL') . ' ';
			$q .= ') ';
			if (isset($this->element_list[$table_name]['read_data']['order'])) {
				$q .= ' ORDER BY '
					/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
					. $this->element_list[$table_name]['read_data']['table_name']
					/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
					. '.' . $this->element_list[$table_name]['read_data']['order'];
			}
		} else {
			// only create query if we have a primary key
			// reads directly from the reference table
			if (
				isset($this->table_array[$this->int_pk_name]['value']) &&
				$this->table_array[$this->int_pk_name]['value']
			) {
				$q = 'SELECT ' . implode(', ', $q_select)
					. ' FROM ' . $table_name
					. ' WHERE ' . $this->int_pk_name . ' = ' . $this->table_array[$this->int_pk_name]['value'];
			}
		}
		// $this->log->debug('CFG QUERY', 'Q: ' . $q);
		// only run if we have query strnig
		$written_pos = [];
		if (isset($q)) {
			$prfx = $data['prefix']; // short
			$pos = 0; // position in while for overwrite if needed
			// read out the list and add the selected data if needed
			while (is_array($res = $this->dbReturn($q))) {
				$_data = [];
				// go through each res
				for ($i = 0, $i_max = count($q_select); $i < $i_max; $i++) {
					// query select part, set to the element name
					$el_name = $q_select[$i];
					// $this->log->debug('edit_error', '[$i] ELNAME: $el_name | POS[$prfx$el_name]: '
					//	. $_POST[$prfx . $el_name][$pos] . ' | RES: ' . $res[$el_name]);
					// if we have an error, we take what we have in the vars, if not we take the data from the db
					if ($this->error) {
						// if we have a radio group, set a bit different
						if (
							isset($data['element_list'][$prfx . $el_name]) &&
							$data['element_list'][$prfx . $el_name] == 'radio_group'
						) {
							$_data[$prfx . $el_name] = ($res[$el_name]) ? ($res[$el_name] - 1) : 0;
						} elseif (isset($_POST[$prfx . $el_name][$pos])) {
							$_data[$prfx . $el_name] = $_POST[$prfx . $el_name][$pos];
						} else {
							$_data[$prfx . $el_name] = '';
						}
					} else {
						if (isset($data['preset'][$prfx . $el_name]) && !isset($res[$el_name])) {
							$_data[$prfx . $el_name] = $data['preset'][$prfx . $el_name];
						} else {
							$_data[$prfx . $el_name] = $res[$el_name];
						}
					}
				}
				$data['content'][] = $_data;
				$data['pos'][] = [0 => $pos]; // this is for the checkboxes
				$written_pos[] = $pos;
				$pos++; // move up one
				// reset and unset before next run
				unset($_data);
			}
		}
		// add lost error ones
		$this->log->debug('ERROR', 'P: ' . $data['prefix'] . ', '
			. Support::prAr($_POST['ERROR'][$data['prefix']] ?? []));
		if ($this->error && !empty($_POST['ERROR'][$data['prefix']])) {
			$prfx = $data['prefix']; // short
			$_post_data = [];
			// MAX entries defined via $data['pk_name'] entry (must exist)
			$_max_pos = count($_POST[$data['pk_name']] ?? []);
			// write all excte previous loaded @ $pos
			foreach ($q_select as $_el_name) {
				for ($_pos = 0; $_pos <= $_max_pos; $_pos++) {
					// if not in data pos
					if (!in_array($_pos, $written_pos)) {
						$_post_data[$_pos][$prfx . $_el_name] =
							$_POST[$prfx . $_el_name][$_pos] ?? '';
					}
				}
			}
			// only add if all fields in data are filled, else skip
			// pk_name field is excluded of check
			foreach ($_post_data as $_pos => $_data) {
				$filled = false;
				foreach ($_data as $_name => $_value) {
					if ($_name != $data['pk_name'] && !empty($_value)) {
						$filled = true;
					}
				}
				if ($filled == true) {
					$data['content'][] = $_data;
					$data['pos'][] = [0 => $_pos];
				}
			}
		}
		// if this is normal single reference data check the content on the element count
		// if there is a max_empty is set, then fill up new elements (unfilled)
		// until we reach max empty
		if (
			/*isset($this->element_list[$table_name]['type']) &&
			$this->element_list[$table_name]['type'] == 'reference_data' &&*/
			isset($this->element_list[$table_name]['max_empty']) &&
			is_numeric($this->element_list[$table_name]['max_empty']) &&
			$this->element_list[$table_name]['max_empty'] > 0
		) {
			// if the max empty is bigger than 10, just cut it to ten at the moment
			if ($this->element_list[$table_name]['max_empty'] > 10) {
				$this->element_list[$table_name]['max_empty'] = 10;
			}
			// check if we need to fill fields
			$element_count = count($data['content']);
			$missing_empty_count = $this->element_list[$table_name]['max_empty'] - $element_count;
			$this->log->debug('CFG MAX', 'Max empty: '
				. $this->element_list[$table_name]['max_empty'] . ', Missing: ' . $missing_empty_count
				. ', Has: ' . $element_count);
			// set if we need more open entries or if we do not have any entries yet
			if (
				($missing_empty_count < $this->element_list[$table_name]['max_empty']) ||
				$element_count == 0
			) {
				for (
					$pos = $element_count,
					$pos_max = $this->element_list[$table_name]['max_empty'] + $element_count;
					$pos <= $pos_max;
					$pos++
				) {
					$_data = [];
					// the fields that need to be filled are in data->type array:
					// pk fields are unfilled
					// fk fields are filled with the fk_id 'int_pk_name' value
					foreach ($data['type'] as $el_name => $type) {
						$_data[$el_name] = '';
						if (
							!empty($data['pk_name']) &&
							$el_name == $data['pk_name']
						) {
							// do nothing for pk name
						} elseif (
							!empty($data['fk_name']) &&
							$el_name == $data['fk_name'] &&
							isset($this->table_array[$this->int_pk_name]['value'])
						) {
							$_data[$el_name] = $this->table_array[$this->int_pk_name]['value'];
						}
					}
					$data['content'][] = $_data;
					// this is for the checkboxes
					$data['pos'][] = [
						0 => $pos
					];
					$this->log->debug('CFG ELEMENT LIST FILL', 'Pos: ' . $pos . '/'
						. $pos_max . ', Content: ' . count($data['content']) . ', Pos: ' . count($data['pos']));
				}
			}
		}

		// push in an empty line of this type, but only if we have a delete key that is also filled
		if (!empty($data['delete_name'])) {
			$data['content'][] = $proto;
			// we also need the pos add or we through an error in smarty
			$data['pos'][] = [
				0 => count($data['pos'])
			];
		}
		// $this->log->debug('CFG ELEMENT LIST FILL', 'Data array: ' . $this->log->prAr($data));
		$type = 'element_list';
		return [
			'output_name' => $output_name,
			'type' => $type,
			'color' => 'edit_fgcolor',
			'data' => $data,
			'show_value' => $show_value,
			'allow_edit' => $this->base_acl_level >= $min_edit_acl ? 1 : 0,
			'allow_show' => $this->base_acl_level >= $min_show_acl ? 1 : 0,
		];
	}
	// end of class
}

// __END__
