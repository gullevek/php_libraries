<?
	$edit_languages=array (
		"table_array" => array (
			"edit_language_id" => array (
				"value" => $GLOBALS["edit_language_id"],
				"type" => "hidden",
				"pk" => 1
			),
			"short_name" => array (
				"value" => $GLOBALS["short_name"],
				"output_name" => "Language (short)",
				"mandatory" => 1,
				"type" => "text",
				"size" => 2,
				"length" => 2       
			),
			"long_name" => array (
				"value" => $GLOBALS["long_name"],
				"output_name" => "Language (long)",
				"mandatory" => 1,
				"type" => "text",
				"size" => 40
			),
			"iso_name" => array (
				"value" => $GLOBALS["iso_name"],
				"output_name" => "ISO Code",
				"mandatory" => 1,
				"type" => "text"
			), 
			"order_number" => array (
				"value" => $GLOBALS["order_number"],
				"int" => 1,
				"order" => 1
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
			"lang_default" => array (
				"value" => $GLOBALS["lang_default"], 
				"output_name" => "Default Language", 
				"int" => 1,
				"type" => "binary", 
				"element_list" => array (
					"1" => "Yes", 
					"0" => "No"
				)
			)
		),
		"load_query" => "SELECT edit_language_id, long_name, iso_name, enabled FROM edit_language ORDER BY long_name",
		"show_fields" => array (
			array (
				"name" => "long_name"
			),
			array (
				"name" => "iso_name",
				"before_value" => "ISO: "
			),
			array (
				"name" => "enabled",
				"before_value" => "Enabled: ",
				"binary" => array ("Yes","No")
			)
		),
		"table_name" => "edit_language"
	);
?>
