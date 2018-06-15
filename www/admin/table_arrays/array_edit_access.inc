<?php
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
		// planned delete lock flag
		"protected" => array (
			"value" => $GLOBALS["protected"],
			"int" => 1
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
			"type" => "reference_data", # is not a sub table read and connect, but only a sub table with data
			"max_empty" => 5, # maxium visible if no data is set, if filled add this number to visible
			"prefix" => "ead",
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
				),
				"edit_access_id" => array (
					"int" => 1,
					"type" => "hidden",
					"fk_id" => 1 # reference main key from master table above
				)
			)
		)
	)
);

# __END__
