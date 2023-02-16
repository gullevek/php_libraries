<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditAccess implements \CoreLibs\Output\Form\TableArraysInterface
{
	/** @var \CoreLibs\Output\Form\Generate */
	private $form;

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
				'edit_access_id' => [
					'value' => $_POST['edit_access_id'] ?? '',
					'type' => 'hidden',
					'pk' => 1
				],
				'name' => [
					'value' => $_POST['name'] ?? '',
					'output_name' => 'Access Group Name',
					'mandatory' => 1,
					'type' => 'text',
					'error_check' => 'alphanumericspace|unique'
				],
				'description' => [
					'value' => $_POST['description'] ?? '',
					'output_name' => 'Description',
					'type' => 'textarea'
				],
				'color' => [
					'value' => $_POST['color'] ?? '',
					'output_name' => 'Color',
					'mandatory' => 0,
					'type' => 'text',
					'size' => 10,
					'length' => 9,
					'error_check' => 'custom',
					// FIXME: update regex check for hex/rgb/hsl with color check class
					'error_regex' => '/^#([\dA-Fa-f]{6}|[\dA-Fa-f]{8})$/',
					'error_example' => '#F6A544'
				],
				'enabled' => [
					'value' => $_POST['enabled'] ?? 0,
					'output_name' => 'Enabled',
					'type' => 'binary',
					'int' => 1, // OR 'bool' => 1
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
				],
				'protected' => [
					'value' => $_POST['protected'] ?? 0,
					'output_name' => 'Protected',
					'type' => 'binary',
					'int' => 1,
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
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
					// is not a sub table read and connect, but only a sub table with data
					// 'type' => 'reference_data',
					// maxium visible if no data is set, if filled add this number to visible
					'max_empty' => 5,
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
							// reference main key from master table above
							'fk_id' => 1
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
	}
}

// __END__
