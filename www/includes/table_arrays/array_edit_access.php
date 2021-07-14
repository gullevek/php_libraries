<?php

declare(strict_types=1);

$edit_access = [
	'table_array' => [
		'edit_access_id' => [
			'value' => $GLOBALS['edit_access_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1
		],
		'name' => [
			'value' => $GLOBALS['name'] ?? '',
			'output_name' => 'Access Group Name',
			'mandatory' => 1,
			'type' => 'text',
			'error_check' => 'alphanumericspace|unique'
		],
		'description' => [
			'value' => $GLOBALS['description'] ?? '',
			'output_name' => 'Description',
			'type' => 'textarea'
		],
		'color' => [
			'value' => $GLOBALS['color'] ?? '',
			'output_name' => 'Color',
			'mandatory' => 0,
			'type' => 'text',
			'size' => 6,
			'length' => 6,
			'error_check' => 'custom',
			'error_regex' => "/[\dA-Fa-f]{6}/",
			'error_example' => 'F6A544'
		],
		'enabled' => [
			'value' => $GLOBALS['enabled'] ?? 0,
			'output_name' => 'Enabled',
			'type' => 'binary',
			'int' => 1, // OR 'bool' => 1
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'protected' => [
			'value' => $GLOBALS['protected'] ?? 0,
			'output_name' => 'Protected',
			'type' => 'binary',
			'int' => 1,
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'additional_acl' => [
			'value' => $GLOBALS['additional_acl'] ?? '',
			'output_name' => 'Additional ACL (as JSON)',
			'type' => 'textarea',
			'error_check' => 'json',
			'rows' => 10,
			'cols' => 60
		],
	],
	'table_name' => 'edit_access',
	"load_query" => "SELECT edit_access_id, name FROM edit_access ORDER BY name",
	'show_fields' => [
		[
			'name' => 'name'
		],
	],
	'element_list' => [
		'edit_access_data' => [
			'output_name' => 'Edit Access Data',
			'delete_name' => 'remove_edit_access_data',
			// 'type' => 'reference_data', // is not a sub table read and connect, but only a sub table with data
			'max_empty' => 5, // maxium visible if no data is set, if filled add this number to visible
			'prefix' => 'ead',
			'elements' => [
				'name' => [
					'type' => 'text',
					'error_check' => 'alphanumeric|unique',
					'output_name' => 'Name',
					'mandatory' => 1
				],
				'value' => [
					'type' => 'text',
					'output_name' => 'Value'
				],
				'enabled' => [
					'type' => 'checkbox',
					'output_name' => 'Activate',
					'int' => 1,
					'element_list' => [1]
				],
				/*'edit_access_id' => [
					'int' => 1,
					'type' => 'hidden',
					'fk_id' => 1 // reference main key from master table above
				],*/
				'edit_access_data_id' => [
					'type' => 'hidden',
					'int' => 1,
					'pk_id' => 1
				],
			],
		],
	],
];

// __END__
