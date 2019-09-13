<?php declare(strict_types=1);

$edit_access = array (
	'table_array' => array (
		'edit_access_id' => array (
			'value' => isset($GLOBALS['edit_access_id']) ? $GLOBALS['edit_access_id'] : '',
			'type' => 'hidden',
			'pk' => 1
		),
		'name' => array (
			'value' => isset($GLOBALS['name']) ? $GLOBALS['name'] : '',
			'output_name' => 'Access Group Name',
			'mandatory' => 1,
			'type' => 'text',
			'error_check' => 'alphanumericspace|unique'
		),
		'description' => array (
			'value' => isset($GLOBALS['description']) ? $GLOBALS['description'] : '',
			'output_name' => 'Description',
			'type' => 'textarea'
		),
		'color' => array (
			'value' => isset($GLOBALS['color']) ? $GLOBALS['color'] : '',
			'output_name' => 'Color',
			'mandatory' => 0,
			'type' => 'text',
			'size' => 6,
			'length' => 6,
			'error_check' => 'custom',
			'error_regex' => "/[\dA-Fa-f]{6}/",
			'error_example' => 'F6A544'
		),
		'enabled' => array (
			'value' => isset($GLOBALS['enabled']) ? $GLOBALS['enabled'] : 0,
			'output_name' => 'Enabled',
			'type' => 'binary',
			'int' => 1, // OR 'bool' => 1
			'element_list' => array (
				'1' => 'Yes',
				'0' => 'No'
			)
		),
		'protected' => array (
			'value' => isset($GLOBALS['protected']) ? $GLOBALS['protected'] : 0,
			'output_name' => 'Protected',
			'type' => 'binary',
			'int' => 1,
			'element_list' => array (
				'1' => 'Yes',
				'0' => 'No'
			)
		),
		'additional_acl' => array (
			'value' => isset($GLOBALS['additional_acl']) ? $GLOBALS['additional_acl'] : '',
			'output_name' => 'Additional ACL (as JSON)',
			'type' => 'textarea',
			'error_check' => 'json',
			'rows' => 10,
			'cols' => 60
		),
	),
	'table_name' => 'edit_access',
	"load_query" => "SELECT edit_access_id, name FROM edit_access ORDER BY name",
	'show_fields' => array (
		array (
			'name' => 'name'
		)
	),
	'element_list' => array (
		'edit_access_data' => array (
			'output_name' => 'Edit Access Data',
			'delete_name' => 'remove_edit_access_data',
			// 'type' => 'reference_data', # is not a sub table read and connect, but only a sub table with data
			'max_empty' => 5, # maxium visible if no data is set, if filled add this number to visible
			'prefix' => 'ead',
			'elements' => array (
				'name' => array (
					'type' => 'text',
					'error_check' => 'alphanumeric|unique',
					'output_name' => 'Name',
					'mandatory' => 1
				),
				'value' => array (
					'type' => 'text',
					'output_name' => 'Value'
				),
				'enabled' => array (
					'type' => 'checkbox',
					'output_name' => 'Activate',
					'int' => 1,
					'element_list' => array(1)
				),
				/*'edit_access_id' => array (
					'int' => 1,
					'type' => 'hidden',
					'fk_id' => 1 # reference main key from master table above
				),*/
				'edit_access_data_id' => array (
					'type' => 'hidden',
					'int' => 1,
					'pk_id' => 1
				)
			)
		)
	)
);

// __END__
