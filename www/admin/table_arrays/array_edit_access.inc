<?
	$edit_access = array (
		"table_array" => array (
			"edit_access_id" => array (
				"value" => $GLOBALS["edit_access_id"],
				"type" => "hidden",
				"pk" => 1
			),
			"name" => array (
				"value" => $GLOBALS["name"],
				"output_name" => $this->l->__("Access Group Name"),
				"mandatory" => 1,
				"type" => "text",
				"error_check" => "alphanumericspace|unique"
			),
			"description" => array (
				"value" => $GLOBALS["description"],
				"output_name" => $this->l->__("Description"),
				"type" => "textarea"
			),
			"color" => array (
				"value" => $GLOBALS["color"],
				"output_name" => $this->l->__("Color"),
				"mandatory" => 0,
				"type" => "text",
				"size" => 6,
				"length" => 6,
				"error_check" => "custom",
				"error_regex" => "/[\dA-Fa-f]{6}/",
				"error_example" => "F6A544"
			)
		),
		"table_name" => "edit_access",
		"load_query" => "SELECT edit_access_id, name FROM edit_access ORDER BY name",
		"show_fields" => array (
			array (
				"name" => "name"
			)
		),
		"element_list" => array (
			"edit_access_data" => array (
				"output_name" => "Edit Access Data",
				"delete" => 0, // set then reference entries are deleted, else the "enable" flag is only set
				"prefix" => "ead",
				"read_data" => array (
					"table_name" => "edit_access_data",
					"pk_id" => "edit_acesss_id",
					"name" => "name"
				),
				"elements" => array (
					"edit_access_data_id" => array (
						"output_name" => "Activate",
						"type" => "hidden",
						"int" => 1,
						"pk_id" => 1
					),
					"name" => array (
						"type" => "text",
						"error_check" => "alphanumeric|unique",
						"output_name" => "Name"
					),
					"value" => array (
						"type" => "text",
						"output_name" => "Value"
					),
					"enabled" => array (
						"type" => "checkbox",
						"output_name" => "Activate",
						"int" => 1,
						"element_list" => array(1)
					)
				)
			)
		)
	);
?>
