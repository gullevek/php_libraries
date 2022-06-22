<?php

declare(strict_types=1);

$edit_users = [
	'table_array' => [
		'edit_user_id' => [
			'value' => $GLOBALS['edit_user_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1,
			'int' => 1
		],
		'username' => [
			'value' => $GLOBALS['username'] ?? '',
			'output_name' => 'Username',
			'mandatory' => 1,
			'error_check' => 'unique|alphanumericextended',
			'type' => 'text'
		],
		'password' => [
			'value' => $GLOBALS['password'] ?? '',
			'HIDDEN_value' => $GLOBALS['HIDDEN_password'] ?? '',
			'CONFIRM_value' => $GLOBALS['CONFIRM_password'] ?? '',
			'output_name' => 'Password',
			'mandatory' => 1,
			'type' => 'password', // later has to be password for encryption in database
			'update' => [ // connected field updates, and update data
				'password_change_date' => [ // db row to update
					'type' => 'date', // type of field (int/text/date/etc)
					'value' => 'NOW()' // value [todo: complex reference
				],
			],
		],
		// password date when first insert and password is set, needs special field with connection to password
		// password reset force interval, if set, user needs to reset password after X time period
		'password_change_interval' => [
			'value' => $GLOBALS['password_change_interval'] ?? '',
			'output_name' => 'Password change interval',
			// can be any date length format. n Y/M/D [not H/M/S], only one set, no combination
			'error_check' => 'intervalshort',
			'type' => 'text',
			'interval' => 1, // interval needs NULL write for empty
			'size' => 5, // make it 5 chars long
			'length' => 5
		],
		'enabled' => [
			'value' => $GLOBALS['enabled'] ?? '',
			'output_name' => 'Enabled',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'deleted' => [
			'value' => $GLOBALS['deleted'] ?? '',
			'output_name' => 'Deleted',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'strict' => [
			'value' => $GLOBALS['strict'] ?? '',
			'output_name' => 'Strict (Lock after errors)',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'locked' => [
			'value' => $GLOBALS['locked'] ?? '',
			'output_name' => 'Locked (auto set if strict with errors)',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'admin' => [
			'value' => $GLOBALS['admin'] ?? '',
			'output_name' => 'Admin',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'debug' => [
			'value' => $GLOBALS['debug'] ?? '',
			'output_name' => 'Debug',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'db_debug' => [
			'value' => $GLOBALS['db_debug'] ?? '',
			'output_name' => 'DB Debug',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'email' => [
			'value' => $GLOBALS['email'] ?? '',
			'output_name' => 'E-Mail',
			'type' => 'text',
			'error_check' => 'email'
		],
		'last_name' => [
			'value' => $GLOBALS['last_name'] ?? '',
			'output_name' => 'Last Name',
			'type' => 'text'
		],
		'first_name' => [
			'value' => $GLOBALS['first_name'] ?? '',
			'output_name' => 'First Name',
			'type' => 'text'
		],
		'lock_until' => [
			'value' => $GLOBALS['lock_until'] ?? '',
			'output_name' => 'Lock account until',
			'type' => 'datetime',
			'error_check' => 'datetime',
			'sql_read' => 'YYYY-MM-DD HH24:MI',
			'datetime' => 1,
		],
		'lock_after' => [
			'value' => $GLOBALS['lock_after'] ?? '',
			'output_name' => 'Lock account after',
			'type' => 'datetime',
			'error_check' => 'datetime',
			'sql_read' => 'YYYY-MM-DD HH24:MI',
			'datetime' => 1,
		],
		'login_user_id' => [
			'value' => $GLOBALS['login_user_id'] ?? '',
			'output_name' => '_GET/_POST loginUserId direct login ID',
			'type' => 'text',
			'error_check' => 'unique|custom',
			'error_regex' => "/^[A-Za-z0-9]+$/",
			'emptynull' => 1,
		],
		'login_user_id_set_date' => [
			'output_name' => 'loginUserId set date',
			'value' => $GLOBALS['login_user_id_set_date'] ?? '',
			'type' => 'view',
			'empty' => '-'
		],
		'login_user_id_last_revalidate' => [
			'output_name' => 'loginUserId last revalidate date',
			'value' => $GLOBALS['login_user_id_last_revalidate'] ?? '',
			'type' => 'view',
			'empty' => '-'
		],
		'login_user_id_locked' => [
			'value' => $GLOBALS['login_user_id_locked'] ?? '',
			'output_name' => 'loginUserId usage locked',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'login_user_id_revalidate_after' => [
			'value' => $GLOBALS['login_user_id_revalidate_after'] ?? '',
			'output_name' => 'loginUserId, User must login after n days',
			'type' => 'text',
			'error_check' => 'intervalshort',
			'interval' => 1, // interval needs NULL write for empty
			'size' => 5, // make it 5 chars long
			'length' => 5
		],
		'login_user_id_valid_from' => [
			'value' => $GLOBALS['login_user_id_valid_from'] ?? '',
			'output_name' => 'loginUserId valid from',
			'type' => 'datetime',
			'error_check' => 'datetime',
			'sql_read' => 'YYYY-MM-DD HH24:MI',
			'datetime' => 1,
		],
		'login_user_id_valid_until' => [
			'value' => $GLOBALS['login_user_id_valid_until'] ?? '',
			'output_name' => 'loginUserId valid until',
			'type' => 'datetime',
			'error_check' => 'datetime',
			'sql_read' => 'YYYY-MM-DD HH24:MI',
			'datetime' => 1,
		],
		'edit_language_id' => [
			'value' => $GLOBALS['edit_language_id'] ?? '',
			'output_name' => 'Language',
			'mandatory' => 1,
			'int' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_language_id, long_name FROM edit_language WHERE enabled = 1 ORDER BY order_number"
		],
		'edit_scheme_id' => [
			'value' => $GLOBALS['edit_scheme_id'] ?? '',
			'output_name' => 'Scheme',
			'int_null' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_scheme_id, name FROM edit_scheme WHERE enabled = 1 ORDER BY name"
		],
		'edit_group_id' => [
			'value' => $GLOBALS['edit_group_id'] ?? '',
			'output_name' => 'Group',
			'int' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_group_id, name FROM edit_group WHERE enabled = 1 ORDER BY name",
			'mandatory' => 1
		],
		'edit_access_right_id' => [
			'value' => $GLOBALS['edit_access_right_id'] ?? '',
			'output_name' => 'User Level',
			'mandatory' => 1,
			'int' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
		],
		'login_error_count' => [
			'output_name' => 'Login error count',
			'value' => $GLOBALS['login_error_count'] ?? '',
			'type' => 'view',
			'empty' => '0'
		],
		'login_error_date_last' => [
			'output_name' => 'Last login error',
			'value' => $GLOBALS['login_error_date_liast'] ?? '',
			'type' => 'view',
			'empty' => '-'
		],
		'login_error_date_first' => [
			'output_name' => 'First login error',
			'value' => $GLOBALS['login_error_date_first'] ?? '',
			'type' => 'view',
			'empty' => '-'
		],
		'protected' => [
			'value' => $GLOBALS['protected'] ?? '',
			'output_name' => 'Protected',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'additional_acl' => [
			'value' => $GLOBALS['additional_acl'] ?? '',
			'output_name' => 'Additional ACL (as JSON)',
			'type' => 'textarea',
			'error_check' => 'json',
			'rows' => 10,
			'cols' => 60
		],
	],
	'load_query' => "SELECT edit_user_id, username, enabled, deleted, "
		. "strict, locked, login_error_count "
		. "FROM edit_user ORDER BY username",
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
			'delete' => 0, // set then reference entries are deleted, else the 'enable' flag is only set
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

// __END__
