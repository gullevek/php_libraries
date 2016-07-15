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
		)
	);
?>
