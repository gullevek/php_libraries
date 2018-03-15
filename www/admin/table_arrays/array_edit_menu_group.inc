<?
$edit_menu_group = array (
	"table_array" => array (
		"edit_menu_group_id" => array (
			"value" => $GLOBALS["edit_menu_group_id"],
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
		),
		"order_number" => array (
			"value" => $GLOBALS["order_number"],
			"output_name" => "Group order",
			"type" => "order",
			"int" => 1,
			"order" => 1
		)
	),
	"table_name" => "edit_menu_group",
	"load_query" => "SELECT edit_menu_group_id, name FROM edit_menu_group ORDER BY name",
	"show_fields" => array (
		array (
			"name" => "name"
		)
	)
);
