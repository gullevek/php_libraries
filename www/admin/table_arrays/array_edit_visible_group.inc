<?
	$edit_visible_group = array (
		"table_array" => array (
			"edit_visible_group_id" => array (
				"value" => $GLOBALS["edit_visible_group_id"],
				"type" => "hidden",
				"pk" => 1
			),
			"name" => array (
				"value" => $GLOBALS["name"],
				"output_name" => $this->l->__("Group name"),
				"mandatory" => 1,
				"type" => "text"
			),
			"flag" => array (
				"value" => $GLOBALS["flag"],
				"output_name" => $this->l->__("Flag"),
				"mandatory" => 1,
				"type" => "text",
				"error_check" => "alphanumeric|unique"
			)
		),
		"table_name" => "edit_visible_group",
		"load_query" => "SELECT edit_visible_group_id, name FROM edit_visible_group ORDER BY name",
		"show_fields" => array (
			array (
				"name" => "name"
			)
		)
	);
