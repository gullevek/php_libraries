<?php

declare(strict_types=1);

$edit_menu_group = [
	'table_array' => [
		'edit_menu_group_id' => [
			'value' => $GLOBALS['edit_menu_group_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1
		],
		'name' => [
			'value' => $GLOBALS['name'] ?? '',
			'output_name' => 'Group name',
			'mandatory' => 1,
			'type' => 'text'
		],
		'flag' => [
			'value' => $GLOBALS['flag'] ?? '',
			'output_name' => 'Flag',
			'mandatory' => 1,
			'type' => 'text',
			'error_check' => 'alphanumeric|unique'
		],
		'order_number' => [
			'value' => $GLOBALS['order_number'] ?? '',
			'output_name' => 'Group order',
			'type' => 'order',
			'int' => 1,
			'order' => 1
		],
	],
	'table_name' => 'edit_menu_group',
	'load_query' => "SELECT edit_menu_group_id, name FROM edit_menu_group ORDER BY name",
	'show_fields' => [
		[
			'name' => 'name'
		],
	],
];

// __END__
