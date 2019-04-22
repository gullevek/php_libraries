<?php

$edit_users = array (
	"table_array" => array (
		"edit_user_id" => array (
			"value" => $GLOBALS["edit_user_id"],
			"type" => "hidden",
			"pk" => 1,
			"int" => 1
		),
		"username" => array (
			"value" => $GLOBALS["username"],
			"output_name" => "Username",
			"mandatory" => 1,
			"error_check" => "unique|alphanumericextended",
			"type" => "text"
		),
		"password" => array (
			"value" => $GLOBALS["password"],
			"HIDDEN_value" => $GLOBALS["HIDDEN_password"],
			"CONFIRM_value" => $GLOBALS["CONFIRM_password"],
			"output_name" => "Password",
			"mandatory" => 1,
			"type" => "password", // later has to be password for encryption in database
			'update' => array ( // connected field updates, and update data
				'password_change_date' => array ( // db row to update
					'type' => 'date', // type of field (int/text/date/etc)
					'value' => 'NOW()' // value [todo: complex reference
				)
			)
		),
		// password date when first insert and password is set, needs special field with connection to password
		// password reset force interval, if set, user needs to reset password after X time period
		'password_change_interval' => array (
			'value' => $GLOBALS['password_change_interval'],
			'output_name' => 'Password change interval',
			'error_check' => 'intervalshort', // can be any date length format. n Y/M/D [not H/M/S], only one set, no combination
			'type' => 'text',
			'interval' => 1, // interval needs NULL write for empty
			'size' => 5, // make it 5 chars long
			'length' => 5
		),
		"enabled" => array (
			"value" => $GLOBALS["enabled"],
			"output_name" => "Enabled",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"strict" => array (
			"value" => $GLOBALS["strict"],
			"output_name" => "Strict (Lock after errors)",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"locked" => array (
			"value" => $GLOBALS["locked"],
			"output_name" => "Locked (auto set if strict with errors)",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"admin" => array (
			"value" => $GLOBALS["admin"],
			"output_name" => "Admin",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"debug" => array (
			"value" => $GLOBALS["debug"],
			"output_name" => "Debug",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"db_debug" => array (
			"value" => $GLOBALS["db_debug"],
			"output_name" => "DB Debug",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"email" => array (
			"value" => $GLOBALS["email"],
			"output_name" => "E-Mail",
			"type" => "text"
		),
		"last_name" => array (
			"value" => $GLOBALS["last_name"],
			"output_name" => "Last Name",
			"type" => "text"
		),
		"first_name" => array (
			"value" => $GLOBALS["first_name"],
			"output_name" => "First Name",
			"type" => "text"
		),
		"edit_language_id" => array (
			"value" => $GLOBALS["edit_language_id"],
			"output_name" => "Language",
			"mandatory" => 1,
			"int" => 1,
			"type" => "drop_down_db",
			"query" => "SELECT edit_language_id, long_name FROM edit_language WHERE enabled = 1 ORDER BY order_number"
		),
		"edit_scheme_id" => array (
			"value" => $GLOBALS["edit_scheme_id"],
			"output_name" => "Scheme",
			"int_null" => 1,
			"type" => "drop_down_db",
			"query" => "SELECT edit_scheme_id, name FROM edit_scheme WHERE enabled = 1 ORDER BY name"
		),
		"edit_group_id" => array (
			"value" => $GLOBALS["edit_group_id"],
			"output_name" => "Group",
			"int" => 1,
			"type" => "drop_down_db",
			"query" => "SELECT edit_group_id, name FROM edit_group WHERE enabled = 1 ORDER BY name",
			"mandatory" => 1
		),
		"edit_access_right_id" => array (
			"value" => $GLOBALS["edit_access_right_id"],
			"output_name" => "User Level",
			"mandatory" => 1,
			"int" => 1,
			"type" => "drop_down_db",
			"query" => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
		),
		"login_error_count" => array (
			"output_name" => "Login error count",
			"value" => $GLOBALS['login_error_count'],
			"type" => "view",
			"empty" => "0"
		),
		"login_error_date_last" => array (
			"output_name" => "Last login error",
			"value" => $GLOBALS['login_error_date_liast'],
			"type" => "view",
			"empty" => "-"
		),
		"login_error_date_first" => array (
			"output_name" => "First login error",
			"value" => $GLOBALS['login_error_date_first'],
			"type" => "view",
			"empty" => "-"
		),
		"protected" => array (
			"value" => $GLOBALS["protected"],
			"output_name" => "Protected",
			"type" => "binary",
			"int" => 1,
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		)
	),
	"load_query" => "SELECT edit_user_id, username, enabled, debug, db_debug, strict, locked, login_error_count FROM edit_user ORDER BY username",
	"table_name" => "edit_user",
	"show_fields" => array (
		array (
			"name" => "username"
		),
		array (
			"name" => "enabled",
			"binary" => array ("Yes", "No"),
			"before_value" => "Enabled: "
		),
		array (
			"name" => "debug",
			"binary" => array ("Yes", "No"),
			"before_value" => "Debug: "
		),
		array (
			"name" => "db_debug",
			"binary" => array ("Yes", "No"),
			"before_value" => "DB Debug: "
		),
		array (
			"name" => "strict",
			"binary" => array ("Yes", "No"),
			"before_value" => "Strict: "
		),
		array (
			"name" => "locked",
			"binary" => array ("Yes", "No"),
			"before_value" => "Locked: "
		),
		array (
			"name" => "login_error_count",
			"before_value" => "Errors: "
		)
	),
	"element_list" => array (
		"edit_access_user" => array (
			"output_name" => "Accounts",
			"mandatory" => 1,
			"delete" => 0, // set then reference entries are deleted, else the "enable" flag is only set
			"prefix" => "ecu",
			"read_data" => array (
				"table_name" => "edit_access",
				"pk_id" => "edit_access_id",
				"name" => "name",
				"order" => "name"
			),
			"elements" => array (
				"edit_access_user_id" => array (
					"output_name" => "Activate",
					"type" => "hidden",
					"int" => 1,
					"pk_id" => 1
				),
				"enabled" => array (
					"type" => "checkbox",
					"output_name" => "Activate",
					"int" => 1,
					"element_list" => array (1)
				),
				"edit_access_right_id" => array (
					"type" => "drop_down_db",
					"output_name" => "Access Level",
					"preset" => 1, // first of the select
					"int" => 1,
					"query" => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
				),
				"edit_default" => array (
					"type" => "radio_group",
					"output_name" => "Default",
					"int" => 1,
					"element_list" => "radio_group"
				),
				"edit_access_id" => array (
					"type" => "hidden",
					"int" => 1
				)
			)
		) // edit pages ggroup
	)
);

// __END__
