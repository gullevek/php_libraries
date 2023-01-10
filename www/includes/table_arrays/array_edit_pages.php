<?php

declare(strict_types=1);

$edit_pages = [
	'table_array' => [
		'edit_page_id' => [
			'value' => $_POST['edit_page_id'] ?? '',
			'type' => 'hidden',
			'pk' => 1
		],
		'filename' => [
			'value' => $_POST['filename'] ?? '',
			'output_name' => 'Add File ...',
			'mandatory' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT DISTINCT temp_files.filename AS id, "
				. "temp_files.folder || temp_files.filename AS name "
				. "FROM temp_files "
				. "LEFT JOIN edit_page ep ON temp_files.filename = ep.filename "
				. "WHERE ep.filename IS NULL"
		],
		'hostname' => [
			'value' => $_POST['hostname'] ?? '',
			'output_name' => 'Hostname or folder',
			'type' => 'text'
		],
		'name' => [
			'value' => $_POST['name'] ?? '',
			'output_name' => 'Page name',
			'mandatory' => 1,
			'type' => 'text'
		],
		'order_number' => [
			'value' => $_POST['order_number'] ?? '',
			'output_name' => 'Page order',
			'type' => 'order',
			'int' => 1,
			'order' => 1
		],
		/* 'flag' => [
			'value' => $_POST['flag']) ?? '',
			'output_name' => 'Page Flag',
			'type' => 'drop_down_array',
			'query' => [
				'0' => '0',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5'
			],
		],*/
		'online' => [
			'value' => $_POST['online'] ?? '',
			'output_name' => 'Online',
			'int' => 1,
			'type' => 'binary',
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'menu' => [
			'value' => $_POST['menu'] ?? '',
			'output_name' => 'Menu',
			'int' => 1,
			'type' => 'binary',
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'popup' => [
			'value' => $_POST['popup'] ?? '',
			'output_name' => 'Popup',
			'int' => 1,
			'type' => 'binary',
			'element_list' => [
				'1' => 'Yes',
				'0' => 'No'
			],
		],
		'popup_x' => [
			'value' => $_POST['popup_x'] ?? '',
			'output_name' => 'Popup Width',
			'int_null' => 1,
			'type' => 'text',
			'size' => 4,
			'length' => 4
		],
		'popup_y' => [
			'value' => $_POST['popup_y'] ?? '',
			'output_name' => 'Popup Height',
			'int_null' =>  1,
			'type' => 'text',
			'size' => 4,
			'length' => 4
		],
		'content_alias_edit_page_id' => [
			'value' => $_POST['content_alias_edit_page_id'] ?? '',
			'output_name' => 'Content Alias Source',
			'int_null' => 1,
			'type' => 'drop_down_db',
			// query creation
			'select_distinct' => 0,
			'pk_name' => 'edit_page_id AS content_alias_edit_page_id',
			'input_name' => 'name',
			'table_name' => 'edit_page',
			'where_not_self' => 1,
			'order_by' => 'order_number'
			// 'query' => "SELECT edit_page_id AS content_alias_edit_page_id, name ".
				// "FROM edit_page ".
				// (!empty($_POST['edit_page_id']) ? " WHERE edit_page_id <> ".$_POST['edit_page_id'] : "")." ".
				// "ORDER BY order_number"
		],
	],
	'load_query' => "SELECT edit_page_id, "
		. "CASE WHEN hostname IS NOT NULL THEN hostname ELSE ''::VARCHAR END || filename AS filename, "
		. "name, online, menu, popup "
		. "FROM edit_page "
		. "ORDER BY order_number",
	'table_name' => 'edit_page',
	'show_fields' => [
		[
			'name' => 'name'
		],
		[
			'name' => 'filename',
			'before_value' => 'Filename: '
		],
		[
			'name' => 'online',
			'binary' => ['Yes', 'No'],
			'before_value' => 'Online: '
		],
		[
			'name' => 'menu',
			'binary' => ['Yes', 'No'],
			'before_value' => 'Menu: '
		],
		[
			'name' => 'popup',
			'binary' => ['Yes', 'No'],
			'before_value' => 'Popup: '
		],
	],
	'reference_arrays' => [
		'edit_visible_group' => [
			'table_name' => 'edit_page_visible_group',
			'other_table_pk' => 'edit_visible_group_id',
			'output_name' => 'Visible Groups (access)',
			'mandatory' => 1,
			'select_size' => 10,
			'selected' => $_POST['edit_visible_group_id'] ?? '',
			'query' => "SELECT edit_visible_group_id, 'Name: ' || name || ', ' || 'Flag: ' || flag "
				. "FROM edit_visible_group ORDER BY name"
		],
		'edit_menu_group' => [
			'table_name' => 'edit_page_menu_group',
			'other_table_pk' => 'edit_menu_group_id',
			'output_name' => 'Menu Groups (grouping)',
			'mandatory' => 1,
			'select_size' => 10,
			'selected' => $_POST['edit_menu_group_id'] ?? '',
			'query' => "SELECT edit_menu_group_id, 'Name: ' || name || ', ' || 'Flag: ' || flag "
				. "FROM edit_menu_group ORDER BY order_number"
		],

	],
	'element_list' => [
		'edit_query_string' => [
			'output_name' => 'Query Strings',
			'delete_name' => 'remove_query_string',
			'prefix' => 'eqs',
			'elements' => [
				'name' => [
					'output_name' => 'Name',
					'type' => 'text',
					'error_check' => 'unique|alphanumeric',
					'mandatory' => 1
				],
				'value' => [
					'output_name' => 'Value',
					'type' => 'text'
				],
				'enabled' => [
					'output_name' => 'Enabled',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => [1],
				],
				'dynamic' => [
					'output_name' => 'Dynamic',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => [1],
				],
				'edit_query_string_id' => [
					'type' => 'hidden',
					'pk_id' => 1
				],
			], // elements
		], // query_string element list
		'edit_page_content' => [
			'output_name' => 'Page Content',
			'delete_name' => 'remove_page_content',
			'prefix' => 'epc',
			'elements' => [
				'name' => [
					'output_name' => 'Content',
					'type' => 'text',
					'error_check' => 'alphanumeric',
					'mandatory' => 1
				],
				'uid' => [
					'output_name' => 'UID',
					'type' => 'text',
					'error_check' => 'unique|alphanumeric',
					'mandatory' => 1
				],
				'order_number' => [
					'output_name' => 'Order',
					'type' => 'text',
					'error_check' => 'int',
					'mandatory' => 1
				],
				'online' => [
					'output_name' => 'Online',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => [1],
				],
				'edit_access_right_id' => [
					'type' => 'drop_down_db',
					'output_name' => 'Access Level',
					'int' => 1,
					'preset' => 1, // first of the select
					'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
				],
				'edit_page_content_id' => [
					'type' => 'hidden',
					'pk_id' => 1
				],
			],
		],
	], // element list
];

// __END__
