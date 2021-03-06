<?php declare(strict_types=1);

$edit_pages = array(
	'table_array' => array(
		'edit_page_id' => array(
			'value' => isset($GLOBALS['edit_page_id']) ? $GLOBALS['edit_page_id'] : '',
			'type' => 'hidden',
			'pk' => 1
		),
		'filename' => array(
			'value' => isset($GLOBALS['filename']) ? $GLOBALS['filename'] : '',
			'output_name' => 'Add File ...',
			'mandatory' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT DISTINCT temp_files.filename AS id, temp_files.folder || temp_files.filename AS name ".
				"FROM temp_files ".
				"LEFT JOIN edit_page ep ON temp_files.filename = ep.filename ".
				"WHERE ep.filename IS NULL"
		),
		'hostname' => array(
			'value' => isset($GLOBALS['hostname']) ? $GLOBALS['hostname'] : '',
			'output_name' => 'Hostname or folder',
			'type' => 'text'
		),
		'name' => array(
			'value' => isset($GLOBALS['name']) ? $GLOBALS['name'] : '',
			'output_name' => 'Page name',
			'mandatory' => 1,
			'type' => 'text'
		),
		'order_number' => array(
			'value' => isset($GLOBALS['order_number']) ? $GLOBALS['order_number'] : '',
			'output_name' => 'Page order',
			'type' => 'order',
			'int' => 1,
			'order' => 1
		),
		/* 'flag' => array(
			'value' => isset($GLOBALS['flag']) ? $GLOBALS['flag'] : '',
			'output_name' => 'Page Flag',
			'type' => 'drop_down_array',
			'query' => array(
				'0' => '0',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5'
			)
		),*/
		'online' => array(
			'value' => isset($GLOBALS['online']) ? $GLOBALS['online'] : '',
			'output_name' => 'Online',
			'int' => 1,
			'type' => 'binary',
			'element_list' => array(
				'1' => 'Yes',
				'0' => 'No'
			)
		),
		'menu' => array(
			'value' => isset($GLOBALS['menu']) ? $GLOBALS['menu'] : '',
			'output_name' => 'Menu',
			'int' => 1,
			'type' => 'binary',
			'element_list' => array(
				 '1' => 'Yes',
				 '0' => 'No'
			)
		),
		'popup' => array(
			'value' => isset($GLOBALS['popup']) ? $GLOBALS['popup'] : '',
			'output_name' => 'Popup',
			'int' => 1,
			'type' => 'binary',
			'element_list' => array(
				 '1' => 'Yes',
				 '0' => 'No'
			)
		),
		'popup_x' => array(
			'value' => isset($GLOBALS['popup_x']) ? $GLOBALS['popup_x'] : '',
			'output_name' => 'Popup Width',
			'int_null' => 1,
			'type' => 'text',
			'size' => 4,
			'length' => 4
		),
		'popup_y' => array(
			'value' => isset($GLOBALS['popup_y']) ? $GLOBALS['popup_y'] : '',
			'output_name' => 'Popup Height',
			'int_null' =>  1,
			'type' => 'text',
			'size' => 4,
			'length' => 4
		),
		'content_alias_edit_page_id' => array(
			'value' => isset($GLOBALS['content_alias_edit_page_id']) ? $GLOBALS['content_alias_edit_page_id'] : '',
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
				// (isset($GLOBALS['edit_page_id']) ? " WHERE edit_page_id <> ".$GLOBALS['edit_page_id'] : "")." ".
				// "ORDER BY order_number"
		)
	),
	'load_query' => "SELECT edit_page_id, CASE WHEN hostname IS NOT NULL THEN hostname ELSE ''::VARCHAR END || filename AS filename, name, online, menu, popup FROM edit_page ORDER BY order_number",
	'table_name' => 'edit_page',
	'show_fields' => array(
		array(
			'name' => 'name'
		),
		array(
			'name' => 'filename',
			'before_value' => 'Filename: '
		),
		 array(
		   'name' => 'online',
		   'binary' => array('Yes','No'),
		   'before_value' => 'Online: '
		 ),
		 array(
			'name' => 'menu',
			'binary' => array('Yes','No'),
			'before_value' => 'Menu: '
		),
		array(
			'name' => 'popup',
			'binary' => array('Yes','No'),
			'before_value' => 'Popup: '
		)
	),
	'reference_arrays' => array(
		'edit_visible_group' => array(
			'table_name' => 'edit_page_visible_group',
			'other_table_pk' => 'edit_visible_group_id',
			'output_name' => 'Visible Groups (access)',
			'mandatory' => 1,
			'select_size' => 10,
			'selected' => isset($GLOBALS['edit_visible_group_id']) ? $GLOBALS['edit_visible_group_id'] : '',
			'query' => "SELECT edit_visible_group_id, 'Name: ' || name || ', ' || 'Flag: ' || flag FROM edit_visible_group ORDER BY name"
		),
		'edit_menu_group' => array(
			'table_name' => 'edit_page_menu_group',
			'other_table_pk' => 'edit_menu_group_id',
			'output_name' => 'Menu Groups (grouping)',
			'mandatory' => 1,
			'select_size' => 10,
			'selected' => isset($GLOBALS['edit_menu_group_id']) ? $GLOBALS['edit_menu_group_id'] : '',
			'query' => "SELECT edit_menu_group_id, 'Name: ' || name || ', ' || 'Flag: ' || flag FROM edit_menu_group ORDER BY order_number"
		)

	),
	'element_list' => array(
		'edit_query_string' => array(
			'output_name' => 'Query Strings',
			'delete_name' => 'remove_query_string',
			'prefix' => 'eqs',
			'elements' => array(
				'name' => array(
					'output_name' => 'Name',
					'type' => 'text',
					'error_check' => 'unique|alphanumeric',
					'mandatory' => 1
				),
				'value' => array(
					'output_name' => 'Value',
					'type' => 'text'
				),
				'enabled' => array(
					'output_name' => 'Enabled',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => array(1)
				),
				'dynamic' => array(
					'output_name' => 'Dynamic',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => array(1)
				),
				'edit_query_string_id' => array(
					'type' => 'hidden',
					'pk_id' => 1
				)
			) // elements
		), // query_string element list
		'edit_page_content' => array(
			'output_name' => 'Page Content',
			'delete_name' => 'remove_page_content',
			'prefix' => 'epc',
			'elements' => array(
				'name' => array(
					'output_name' => 'Content',
					'type' => 'text',
					'error_check' => 'alphanumeric',
					'mandatory' => 1
				),
				'uid' => array(
					'output_name' => 'UID',
					'type' => 'text',
					'error_check' => 'unique|alphanumeric',
					'mandatory' => 1
				),
				'order_number' => array(
					'output_name' => 'Order',
					'type' => 'text',
					'error_check' => 'int',
					'mandatory' => 1
				),
				'online' => array(
					'output_name' => 'Online',
					'int' => 1,
					'type' => 'checkbox',
					'element_list' => array(1)
				),
				'edit_access_right_id' => array(
					'type' => 'drop_down_db',
					'output_name' => 'Access Level',
					'int' => 1,
					'preset' => 1, // first of the select
					'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
				),
				'edit_page_content_id' => array(
					'type' => 'hidden',
					'pk_id' => 1
				)
			)
		)
	) // element list
);

// __END__
