<?
	// $Id: array_edit_users.inc 4381 2013-02-18 07:25:12Z gullevek $

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
				"error_check" => "unique|alphanumeric",
				"type" => "text"
			),
			"password" => array (
				"value" => $GLOBALS["password"], 
				"HIDDEN_value" => $GLOBALS["HIDDEN_password"],
				"CONFIRM_value" => $GLOBALS["CONFIRM_password"],
				"output_name" => "Password", 
				"mandatory" => 1, 
				"type" => "password" // later has to be password for encryption in database
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
			"protected" => array (
				"value" => $GLOBALS["protected"],
				"int" => 1
			)
		),
		"load_query" => "SELECT edit_user_id, username, enabled, debug, db_debug FROM edit_user ORDER BY username",
		"table_name" => "edit_user",
		"show_fields" => array (
			array (
				"name" => "username"
			),
			array (
				"name" => "enabled",
				"binary" => array("Yes", "No"),
				"before_value" => "Enabled: "
			),
			array (
				"name" => "debug",
				"binary" => array("Yes", "No"),
				"before_value" => "Debug: "
			),
			array (
				"name" => "db_debug",
				"binary" => array("Yes", "No"),
				"before_value" => "DB Debug: "
			),
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
						"element_list" => array(1)
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
?>
