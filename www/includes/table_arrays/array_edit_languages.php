<?php

declare(strict_types=1);

$edit_languages = [
	'table_array' => [
		'edit_language_id' => [
			'value' => $GLOBALS['edit_language_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1
		],
		'short_name' => [
			'value' => $GLOBALS['short_name'] ?? '',
			'output_name' => 'Language (short)',
			'mandatory' => 1,
			'type' => 'text',
			'size' => 2,
			'length' => 2
		],
		'long_name' => [
			'value' => $GLOBALS['long_name'] ?? '',
			'output_name' => 'Language (long)',
			'mandatory' => 1,
			'type' => 'text',
			'size' => 40
		],
		'iso_name' => [
			'value' => $GLOBALS['iso_name'] ?? '',
			'output_name' => 'ISO Code',
			'mandatory' => 1,
			'type' => 'text'
		],
		'order_number' => [
			'value' => $GLOBALS['order_number'] ?? '',
			'int' => 1,
			'order' => 1
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
		'lang_default' => [
			'value' => $GLOBALS['lang_default'] ?? '',
			'output_name' => 'Default Language',
			'int' => 1,
			'type' => 'binary',
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
	],
	'load_query' => "SELECT edit_language_id, long_name, iso_name, enabled FROM edit_language ORDER BY long_name",
	'show_fields' => [
		[
			'name' => 'long_name'
		],
		[
			'name' => 'iso_name',
			'before_value' => 'ISO: '
		],
		[
			'name' => 'enabled',
			'before_value' => 'Enabled: ',
			'binary' => ['Yes','No'],
		],
	],
	'table_name' => 'edit_language'
];

// __END__
