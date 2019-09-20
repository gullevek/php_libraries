<?php declare(strict_types=1);

$edit_groups = array(
	'table_array' => array(
		'edit_group_id' => array(
			'value' => isset($GLOBALS['edit_group_id']) ? $GLOBALS['edit_group_id'] : '',
			'pk' => 1,
			'type' => 'hidden'
		),
		'enabled' => array(
			'value' => isset($GLOBALS['enabled']) ? $GLOBALS['enabled'] : '',
			'output_name' => 'Enabled',
			'int' => 1,
			'type' => 'binary',
			'element_list' => array(
				'1' => 'Yes',
				'0' => 'No'
			)
		),
		'name' => array(
			'value' => isset($GLOBALS['name']) ? $GLOBALS['name'] : '',
			'output_name' => 'Group Name',
			'type' => 'text',
			'mandatory' => 1
		),
		'edit_access_right_id' => array(
			'value' => isset($GLOBALS['edit_access_right_id']) ? $GLOBALS['edit_access_right_id'] : '',
			'output_name' => 'Group Level',
			'mandatory' => 1,
			'int' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
		),
		'edit_scheme_id' => array(
			'value' => isset($GLOBALS['edit_scheme_id']) ? $GLOBALS['edit_scheme_id'] : '',
			'output_name' => 'Group Scheme',
			'int_null' => 1,
			'type' => 'drop_down_db',
			'query' => "SELECT edit_scheme_id, name FROM edit_scheme WHERE enabled = 1 ORDER BY name"
		),
		'additional_acl' => array(
			'value' => isset($GLOBALS['additional_acl']) ? $GLOBALS['additional_acl'] : '',
			'output_name' => 'Additional ACL (as JSON)',
			'type' => 'textarea',
			'error_check' => 'json',
			'rows' => 10,
			'cols' => 60
		),
	),
	'load_query' => "SELECT edit_group_id, name, enabled FROM edit_group ORDER BY name",
	'table_name' => 'edit_group',
	'show_fields' => array(
		array(
			'name' => 'name'
		),
		array(
			'name' => 'enabled',
			'binary' => array('Yes', 'No'),
			'before_value' => 'Enabled: '
		)
	),
	'element_list' => array(
		'edit_page_access' => array(
			'output_name' => 'Pages',
			'mandatory' => 1,
			'delete' => 0, // set then reference entries are deleted, else the 'enable' flag is only set
			'enable_name' => 'enable_page_access',
			'prefix' => 'epa',
			'read_data' => array(
				'table_name' => 'edit_page',
				'pk_id' => 'edit_page_id',
				'name' => 'name',
				'order' => 'order_number'
			),
			'elements' => array(
				'edit_page_access_id' => array(
					'type' => 'hidden',
					'int' => 1,
					'pk_id' => 1
				),
				'enabled' => array(
					'type' => 'checkbox',
					'output_name' => 'Activate',
					'int' => 1,
					'element_list' => array(1)
				),
				'edit_access_right_id' => array(
					'type' => 'drop_down_db',
					'output_name' => 'Access Level',
					'int' => 1,
					'preset' => 1, // first of the select
					'query' => "SELECT edit_access_right_id, name FROM edit_access_right ORDER BY level"
				),
				'edit_page_id' => array(
					'int' => 1,
					'type' => 'hidden'
				)
				/*,
				'edit_default' => array(
					'output_name' => 'Default',
					'type' => 'radio',
					'mandatory' => 1
				)*/
			)
		) // edit pages ggroup
	)
);

// __END__
