<?php

declare(strict_types=1);

$edit_schemes = [
	'table_array' => [
		'edit_scheme_id' => [
			'value' => $GLOBALS['edit_scheme_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1
		],
		'name' => [
			'value' => $GLOBALS['name'] ?? '',
			'output_name' => 'Scheme Name',
			'mandatory' => 1,
			'type' => 'text'
		],
		'header_color' => [
			'value' => $GLOBALS['header_color'] ?? '',
			'output_name' => 'Header Color',
			'mandatory' => 1,
			'type' => 'text',
			'size' => 6,
			'length' => 6,
			'error_check' => 'custom',
			'error_regex' => '/[\dA-Fa-f]{6}/',
			'error_example' => 'F6A544'
		],
		'enabled' => [
			'value' => $GLOBALS['enabled'] ?? '',
			'output_name' => 'Enabled',
			'int' => 1,
			'type' => 'binary',
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'template' => [
			'value' => $GLOBALS['template'] ?? '',
			'output_name' => 'Template',
			'type' => 'text'
		],
	],
	'table_name' => 'edit_scheme',
	'load_query' => "SELECT edit_scheme_id, name, enabled FROM edit_scheme ORDER BY name",
	'show_fields' => [
		[
			'name' => 'name'
		],
		[
			'name' => 'enabled',
			'binary' => ['Yes', 'No'],
			'before_value' => 'Enabled: '
		],
	],
]; // main array

// __END__
