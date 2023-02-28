<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditUsers implements Interface\TableArraysInterface
{
	/** @var \CoreLibs\Output\Form\Generate */
	private $form;

	/**
	 * constructor
	 * @param \CoreLibs\Output\Form\Generate $form base form class
	 */
	public function __construct(\CoreLibs\Output\Form\Generate $form)
	{
		$this->form = $form;
		$this->form->log->debug('CLASS LOAD', __NAMESPACE__ . __CLASS__);
	}

	/**
	 * return the table array
	 *
	 * @return array<mixed>
	 */
	public function setTableArray(): array
	{
		return [
			'table_array' => [
				'edit_user_id' => [
					'value' => $_POST['edit_user_id'] ?? '',
					'type' => 'hidden',
					'pk' => 1,
					'int' => 1
				],
				'username' => [
					'value' => $_POST['username'] ?? '',
					'output_name' => 'Username',
					'mandatory' => 1,
					'error_check' => 'unique|alphanumericextended',
					'type' => 'text',
					// if not min_edit_acl only read
					// if not min_show_acl not visible
					'min_edit_acl' => '100',
					'min_show_acl' => '-1',
				],
				'password' => [
					'value' => $_POST['password'] ?? '',
					'HIDDEN_value' => $_POST['HIDDEN_password'] ?? '',
					'CONFIRM_value' => $_POST['CONFIRM_password'] ?? '',
					'output_name' => 'Password',
					'mandatory' => 1,
					'type' => 'password', // later has to be password for encryption in database
					'update' => [ // connected field updates, and update data
						'password_change_date' => [ // db row to update
							'type' => 'date', // type of field (int/text/date/etc)
							'value' => 'NOW()' // value [todo: complex reference
						],
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				// password date when first insert and password is set, needs special field with connection to password
				// password reset force interval, if set, user needs to reset password after X time period
				'password_change_interval' => [
					'value' => $_POST['password_change_interval'] ?? '',
					'output_name' => 'Password change interval',
					// can be any date length format. n Y/M/D [not H/M/S], only one set, no combination
					'error_check' => 'intervalshort',
					'type' => 'text',
					'interval' => 1, // interval needs NULL write for empty
					'size' => 5, // make it 5 chars long
					'length' => 5,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'enabled' => [
					'value' => $_POST['enabled'] ?? '',
					'output_name' => 'Enabled',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '-1',
				],
				'deleted' => [
					'value' => $_POST['deleted'] ?? '',
					'output_name' => 'Deleted',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'strict' => [
					'value' => $_POST['strict'] ?? '',
					'output_name' => 'Strict (Lock after errors)',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'locked' => [
					'value' => $_POST['locked'] ?? '',
					'output_name' => 'Locked (auto set if strict with errors)',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'admin' => [
					'value' => $_POST['admin'] ?? '',
					'output_name' => 'Admin',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'debug' => [
					'value' => $_POST['debug'] ?? '',
					'output_name' => 'Debug',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'db_debug' => [
					'value' => $_POST['db_debug'] ?? '',
					'output_name' => 'DB Debug',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'email' => [
					'value' => $_POST['email'] ?? '',
					'output_name' => 'E-Mail',
					'type' => 'text',
					'error_check' => 'email',
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'last_name' => [
					'value' => $_POST['last_name'] ?? '',
					'output_name' => 'Last Name',
					'type' => 'text',
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'first_name' => [
					'value' => $_POST['first_name'] ?? '',
					'output_name' => 'First Name',
					'type' => 'text',
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'lock_until' => [
					'value' => $_POST['lock_until'] ?? '',
					'output_name' => 'Lock account until',
					'type' => 'datetime',
					'error_check' => 'datetime',
					'sql_read' => 'YYYY-MM-DD HH24:MI',
					'datetime' => 1,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'lock_after' => [
					'value' => $_POST['lock_after'] ?? '',
					'output_name' => 'Lock account after',
					'type' => 'datetime',
					'error_check' => 'datetime',
					'sql_read' => 'YYYY-MM-DD HH24:MI',
					'datetime' => 1,'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_user_id' => [
					'value' => $_POST['login_user_id'] ?? '',
					'output_name' => '_GET/_POST loginUserId direct login ID',
					'type' => 'text',
					'error_check' => 'unique|custom',
					'error_regex' => "/^[A-Za-z0-9]+$/",
					'emptynull' => 1,'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_user_id_set_date' => [
					'output_name' => 'loginUserId set date',
					'value' => $_POST['login_user_id_set_date'] ?? '',
					'type' => 'view',
					'empty' => '-',
					'min_show_acl' => '100',
				],
				'login_user_id_last_revalidate' => [
					'output_name' => 'loginUserId last revalidate date',
					'value' => $_POST['login_user_id_last_revalidate'] ?? '',
					'type' => 'view',
					'empty' => '-',
					'min_show_acl' => '100',
				],
				'login_user_id_locked' => [
					'value' => $_POST['login_user_id_locked'] ?? '',
					'output_name' => 'loginUserId usage locked',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_user_id_revalidate_after' => [
					'value' => $_POST['login_user_id_revalidate_after'] ?? '',
					'output_name' => 'loginUserId, User must login after n days',
					'type' => 'text',
					'error_check' => 'intervalshort',
					'interval' => 1, // interval needs NULL write for empty
					'size' => 5, // make it 5 chars long
					'length' => 5,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_user_id_valid_from' => [
					'value' => $_POST['login_user_id_valid_from'] ?? '',
					'output_name' => 'loginUserId valid from',
					'type' => 'datetime',
					'error_check' => 'datetime',
					'sql_read' => 'YYYY-MM-DD HH24:MI',
					'datetime' => 1,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_user_id_valid_until' => [
					'value' => $_POST['login_user_id_valid_until'] ?? '',
					'output_name' => 'loginUserId valid until',
					'type' => 'datetime',
					'error_check' => 'datetime',
					'sql_read' => 'YYYY-MM-DD HH24:MI',
					'datetime' => 1,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'edit_language_id' => [
					'value' => $_POST['edit_language_id'] ?? '',
					'output_name' => 'Language',
					'mandatory' => 1,
					'int' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_language_id, long_name "
						. "FROM edit_language "
						. "WHERE enabled = 1"
						. "ORDER BY order_number",
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'edit_scheme_id' => [
					'value' => $_POST['edit_scheme_id'] ?? '',
					'output_name' => 'Scheme',
					'int_null' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_scheme_id, name FROM edit_scheme WHERE enabled = 1 ORDER BY name",
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'edit_group_id' => [
					'value' => $_POST['edit_group_id'] ?? '',
					'output_name' => 'Group',
					'int' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_group_id, name FROM edit_group WHERE enabled = 1 ORDER BY name",
					'mandatory' => 1,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'edit_access_right_id' => [
					'value' => $_POST['edit_access_right_id'] ?? '',
					'output_name' => 'User Level',
					'mandatory' => 1,
					'int' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level",
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'login_error_count' => [
					'output_name' => 'Login error count',
					'value' => $_POST['login_error_count'] ?? '',
					'type' => 'view',
					'empty' => '0',
					'min_show_acl' => '100',
				],
				'login_error_date_last' => [
					'output_name' => 'Last login error',
					'value' => $_POST['login_error_date_liast'] ?? '',
					'type' => 'view',
					'empty' => '-',
					'min_show_acl' => '100',
				],
				'login_error_date_first' => [
					'output_name' => 'First login error',
					'value' => $_POST['login_error_date_first'] ?? '',
					'type' => 'view',
					'empty' => '-',
					'min_show_acl' => '100',
				],
				'protected' => [
					'value' => $_POST['protected'] ?? '',
					'output_name' => 'Protected',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
				'additional_acl' => [
					'value' => $_POST['additional_acl'] ?? '',
					'output_name' => 'Additional ACL (as JSON)',
					'type' => 'textarea',
					'error_check' => 'json',
					'rows' => 10,
					'cols' => 60,
					'min_edit_acl' => '100',
					'min_show_acl' => '100',
				],
			],
			'load_query' => "SELECT edit_user_id, username, enabled, deleted, "
				. "strict, locked, login_error_count "
				. "FROM edit_user "
				// if base acl is not 90 only list enabled
				// if not admin flag, do not list admin flagged
				. (
					!$this->form->getAclAdmin() ?
						"WHERE admin = 0 "
						. (
							!$this->form->checkBaseACL(90) ?
							// $_POST['base_acl_level'] < 90 ?
								"AND enabled = 1 " :
								""
						)
					: ''
				)
				. "ORDER BY username",
			'table_name' => 'edit_user',
			'show_fields' => [
				[
					'name' => 'username'
				],
				[
					'name' => 'enabled',
					'binary' => ['Yes', 'No'],
					'before_value' => 'ENBL: '
				],
				[
					'name' => 'deleted',
					'binary' => ['Yes', 'No'],
					'before_value' => 'DEL: '
				],
				[
					'name' => 'strict',
					'binary' => ['Yes', 'No'],
					'before_value' => 'STRC: '
				],
				[
					'name' => 'locked',
					'binary' => ['Yes', 'No'],
					'before_value' => 'LCK: '
				],
				[
					'name' => 'login_error_count',
					'before_value' => 'ERR: '
				],
			],
			'element_list' => [
				'edit_access_user' => [
					'output_name' => 'Accounts',
					'mandatory' => 1,
					// set then reference entries are deleted, else the 'enable' flag is only set
					'delete' => 0,
					// acl
					'min_edit_acl' => '40',
					'min_show_acl' => '20',
					// table read prefix
					'prefix' => 'ecu',
					'read_data' => [
						'table_name' => 'edit_access',
						'pk_id' => 'edit_access_id',
						'name' => 'name',
						'order' => 'name'
					],
					'elements' => [
						'edit_access_user_id' => [
							'output_name' => 'Activate',
							'type' => 'hidden',
							'int' => 1,
							'pk_id' => 1
						],
						'enabled' => [
							'type' => 'checkbox',
							'output_name' => 'Activate',
							'int' => 1,
							'element_list' => [1],
						],
						'edit_access_right_id' => [
							'type' => 'drop_down_db',
							'output_name' => 'Access Level',
							'preset' => 1, // first of the select
							'int' => 1,
							'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
						],
						'edit_default' => [
							'type' => 'radio_group',
							'output_name' => 'Default',
							'int' => 1,
							'element_list' => 'radio_group'
						],
						'edit_access_id' => [
							'type' => 'hidden',
							'int' => 1
						],
					],
				], // edit pages ggroup
			],
		];
	}
}

// __END__
