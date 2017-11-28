<?
	$edit_schemes = array (
		"table_array" => array (
			"edit_scheme_id" => array (
				"value" => $GLOBALS["edit_scheme_id"],
				"type" => "hidden",
				"pk" => 1
			),
			"name" => array (
				"value" => $GLOBALS["name"],
				"output_name" => "Scheme Name",
				"mandatory" => 1,
				"type" => "text"
			),
			"header_color" => array (
				"value" => $GLOBALS["header_color"],
				"output_name" => "Header Color",
				"mandatory" => 1,
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
				"int" => 1,
				"type" => "binary",
				"element_list" => array (
					"1" => "Yes",
					"0" => "No"
				)
			),
			"template" => array (
				"value" => $GLOBALS["template"],
				"output_name" => "Template",
				"type" => "text"
			)
		),
		"table_name" => "edit_scheme",
		"load_query" => "SELECT edit_scheme_id, name, enabled FROM edit_scheme ORDER BY name",
		"show_fields" => array (
			array (
				"name" => "name"
			),
			array (
				"name" => "enabled",
				"binary" => array ("Yes", "No"),
				"before_value" => "Enabled: "
			)
		)
	); // main array
	?>
