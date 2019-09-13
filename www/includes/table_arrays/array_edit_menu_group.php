<?php declare(strict_types=1);

$edit_menu_group = array (
	'table_array' => array (
		'edit_menu_group_id' => array (
			'value' => isset($GLOBALS['edit_menu_group_id']) ? $GLOBALS['edit_menu_group_id'] : '',
			'type' => 'hidden',
			'pk' => 1
		),
		'name' => array (
			'value' => isset($GLOBALS['name']) ? $GLOBALS['name'] : '',
			'output_name' => 'Group name',
			'mandatory' => 1,
			'type' => 'text'
		),
		'flag' => array (
			'value' => isset($GLOBALS['flag']) ? $GLOBALS['flag'] : '',
			'output_name' => 'Flag',
			'mandatory' => 1,
			'type' => 'text',
			'error_check' => 'alphanumeric|unique'
		),
		'order_number' => array (
			'value' => isset($GLOBALS['order_number']) ? $GLOBALS['order_number'] : '',
			'output_name' => 'Group order',
			'type' => 'order',
			'int' => 1,
			'order' => 1
		)
	),
	'table_name' => 'edit_menu_group',
	'load_query' => "SELECT edit_menu_group_id, name FROM edit_menu_group ORDER BY name",
	'show_fields' => array (
		array (
			'name' => 'name'
		)
	)
);

// __END__
