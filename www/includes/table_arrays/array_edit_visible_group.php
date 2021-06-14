<?php declare(strict_types=1);

$edit_visible_group = [
	'table_array' => [
		'edit_visible_group_id' => [
			'value' => $GLOBALS['edit_visible_group_id'] ?? '',
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
	],
	'table_name' => 'edit_visible_group',
	'load_query' => "SELECT edit_visible_group_id, name FROM edit_visible_group ORDER BY name",
	'show_fields' => [
		[
			'name' => 'name'
		],
	],
];

// __END__
