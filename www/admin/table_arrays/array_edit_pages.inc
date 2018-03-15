<?
$edit_pages = array (
	"table_array" => array (
		"edit_page_id" => array (
			"value" => $GLOBALS["edit_page_id"],
			"type" => "hidden",
			"pk" => 1
		),
		"filename" => array (
			"value" => $GLOBALS["filename"],
			"output_name" => "Add File ...",
			"mandatory" => 1,
			"type" => "drop_down_db",
			"query" => "SELECT DISTINCT temp_files.filename AS id, temp_files.filename AS name FROM temp_files LEFT JOIN edit_page ep ON temp_files.filename = ep.filename WHERE ep.filename IS NULL"
		),
		"name" => array (
			"value" => $GLOBALS["name"],
			"output_name" => "Page name",
			"mandatory" => 1,
			"type" => "text"
		),
		"order_number" => array (
			"value" => $GLOBALS["order_number"],
			"output_name" => "Page order",
			"type" => "order",
			"int" => 1,
			"order" => 1
		),
/*			"flag" => array (
			"value" => $GLOBALS["flag"],
			"output_name" => "Page Flag",
			"type" => "drop_down_array",
			"query" => array (
				"0" => "0",
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5"
			)
		),*/
		"online" => array (
			"value" => $GLOBALS["online"],
			"output_name" => "Online",
			"int" => 1,
			"type" => "binary",
			"element_list" => array (
				"1" => "Yes",
				"0" => "No"
			)
		),
		"menu" => array (
			"value" => $GLOBALS["menu"],
			"output_name" => "Menu",
			"int" => 1,
			"type" => "binary",
			"element_list" => array (
				 "1" => "Yes",
				 "0" => "No"
			)
		),
		"popup" => array (
			"value" => $GLOBALS["popup"],
			"output_name" => "Popup",
			"int" => 1,
			"type" => "binary",
			"element_list" => array (
				 "1" => "Yes",
				 "0" => "No"
			)
		),
		"popup_x" => array (
			"value" => $GLOBALS["popup_x"],
			"output_name" => "Popup Width",
			"int_null" => 1,
			"type" => "text",
			"size" => 4,
			"length" => 4
		),
		"popup_y" => array (
			"value" => $GLOBALS["popup_y"],
			"output_name" => "Popup Height",
			"int_null" => 1,
			"type" => "text",
			"size" => 4,
			"length" => 4
		)/*,
		"query_string" => array (
			"value" => $GLOBALS["query_string"],
			"output_name" => "Query String for Link",
			"type" => "text",
			"size" => "50"
		)*/
	),
	"load_query" => "SELECT edit_page_id, filename, name, online, menu, popup FROM edit_page ORDER BY order_number",
	"table_name" => "edit_page",
	"show_fields" => array (
		array (
			"name" => "name"
		),
		array (
			"name" => "filename",
			"before_value" => "Filename: "
		),
		 array (
		   "name" => "online",
		   "binary" => array ("Yes","No"),
		   "before_value" => "Online: "
		 ),
		 array (
			"name" => "menu",
			"binary" => array ("Yes","No"),
			"before_value" => "Menu: "
		),
		array (
			"name" => "popup",
			"binary" => array ("Yes","No"),
			"before_value" => "Popup: "
		)
	),
	"reference_arrays" => array (
		"edit_visible_group" => array (
			"table_name" => "edit_page_visible_group",
			"other_table_pk" => "edit_visible_group_id",
			"output_name" => "Visible Groups (access)",
			"mandatory" => 1,
			"select_size" => 10,
			"selected" => $GLOBALS["edit_visible_group_id"],
			"query" => 'SELECT edit_visible_group_id, \'Name: \' || name || \', \' || \'Flag: \' || flag FROM edit_visible_group ORDER BY name'
		),
		"edit_menu_group" => array (
			"table_name" => "edit_page_menu_group",
			"other_table_pk" => "edit_menu_group_id",
			"output_name" => "Menu Groups (grouping)",
			"mandatory" => 1,
			"select_size" => 10,
			"selected" => $GLOBALS["edit_menu_group_id"],
			"query" => 'SELECT edit_menu_group_id, \'Name: \' || name || \', \' || \'Flag: \' || flag FROM edit_menu_group ORDER BY order_number'
		)

	),
	"element_list" => array (
		"edit_query_string" => array (
			"output_name" => "Query Strings",
			"delete_name" => "remove_query_string",
			"prefix" => "eqs",
			"elements" => array (
				"name" => array (
					"output_name" => "Name",
					"type" => "text",
					"error_check" => "unique|alphanumeric",
					"mandatory" => 1
				),
				"value" => array (
					"output_name" => "Value",
					"type" => "text"
				),
				"enabled" => array (
					"output_name" => "Enabled",
					"int" => 1,
					"type" => "checkbox",
					"element_list" => array (1)
				),
				"dynamic" => array (
					"output_name" => "Dynamic",
					"int" => 1,
					"type" => "checkbox",
					"element_list" => array (1)
				),
				"edit_query_string_id" => array (
					"type" => "hidden",
					"pk_id" => 1
				)
			) // elements
		) // query_string element list
	) // element list
);
