<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditGroups implements Interface\TableArraysInterface
{
	/** @var \CoreLibs\Output\Form\Generate */
	private \CoreLibs\Output\Form\Generate $form;

	/**
	 * constructor
	 * @param \CoreLibs\Output\Form\Generate $form base form class
	 */
	public function __construct(\CoreLibs\Output\Form\Generate $form)
	{
		$this->form = $form;
		$this->form->log->debug('CLASS LOAD', __NAMESPACE__ . __CLASS__);
	}

	/**
	 * return the table array
	 *
	 * @return array<mixed>
	 */
	public function setTableArray(): array
	{
		return [
			'table_array' => [
				'edit_group_id' => [
					'value' => $_POST['edit_group_id'] ?? '',
					'pk' => 1,
					'type' => 'hidden'
				],
				'enabled' => [
					'value' => $_POST['enabled'] ?? '',
					'output_name' => 'Enabled',
					'int' => 1,
					'type' => 'binary',
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
				],
				'name' => [
					'value' => $_POST['name'] ?? '',
					'output_name' => 'Group Name',
					'type' => 'text',
					'mandatory' => 1
				],
				'edit_access_right_id' => [
					'value' => $_POST['edit_access_right_id'] ?? '',
					'output_name' => 'Group Level',
					'mandatory' => 1,
					'int' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_access_right_id, name "
						. "FROM edit_access_right "
						. "ORDER BY level"
				],
				'edit_scheme_id' => [
					'value' => $_POST['edit_scheme_id'] ?? '',
					'output_name' => 'Group Scheme',
					'int_null' => 1,
					'type' => 'drop_down_db',
					'query' => "SELECT edit_scheme_id, name "
						. "FROM edit_scheme "
						. "WHERE enabled = 1 "
						. "ORDER BY name"
				],
				'additional_acl' => [
					'value' => $_POST['additional_acl'] ?? '',
					'output_name' => 'Additional ACL (as JSON)',
					'type' => 'textarea',
					'error_check' => 'json',
					'rows' => 10,
					'cols' => 60
				],
			],
			'load_query' => "SELECT edit_group_id, name, enabled "
				. "FROM edit_group "
				. "ORDER BY name",
			'table_name' => 'edit_group',
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
			'element_list' => [
				'edit_page_access' => [
					'output_name' => 'Pages',
					'mandatory' => 1,
					'delete' => 0, // set then reference entries are deleted, else the 'enable' flag is only set
					'enable_name' => 'enable_page_access',
					'prefix' => 'epa',
					'read_data' => [
						'table_name' => 'edit_page',
						'pk_id' => 'edit_page_id',
						'name' => 'name',
						'order' => 'order_number'
					],
					'elements' => [
						'edit_page_access_id' => [
							'type' => 'hidden',
							'int' => 1,
							'pk_id' => 1
						],
						'enabled' => [
							'type' => 'checkbox',
							'output_name' => 'Activate',
							'int' => 1,
							'element_list' => [1],
						],
						'edit_access_right_id' => [
							'type' => 'drop_down_db',
							'output_name' => 'Access Level',
							'int' => 1,
							'preset' => 1, // first of the select
							'query' => "SELECT edit_access_right_id, name "
								. "FROM edit_access_right "
								. "ORDER BY level"
						],
						'edit_page_id' => [
							'int' => 1,
							'type' => 'hidden'
						],
						/*,
						'edit_default' => [
							'output_name' => 'Default',
							'type' => 'radio',
							'mandatory' => 1
						],*/
					],
				], // edit pages ggroup
			],
		];
	}
}

// __END__
