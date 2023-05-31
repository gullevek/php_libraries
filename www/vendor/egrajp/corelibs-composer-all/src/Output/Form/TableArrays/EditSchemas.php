<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditSchemas implements Interface\TableArraysInterface
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
				'edit_scheme_id' => [
					'value' => $_POST['edit_scheme_id'] ?? '',
					'type' => 'hidden',
					'pk' => 1
				],
				'name' => [
					'value' => $_POST['name'] ?? '',
					'output_name' => 'Scheme Name',
					'mandatory' => 1,
					'type' => 'text'
				],
				'header_color' => [
					'value' => $_POST['header_color'] ?? '',
					'output_name' => 'Header Color',
					'mandatory' => 1,
					'type' => 'text',
					'size' => 10,
					'length' => 9,
					'error_check' => 'custom',
					// FIXME: update regex check for hex/rgb/hsl with color check class
					'error_regex' => '/^#([\dA-Fa-f]{6}|[\dA-Fa-f]{8})$/',
					'error_example' => '#F6A544'
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
				'template' => [
					'value' => $_POST['template'] ?? '',
					'output_name' => 'Template',
					'type' => 'text'
				],
			],
			'table_name' => 'edit_scheme',
			'load_query' => "SELECT edit_scheme_id, name, enabled "
				. "FROM edit_scheme "
				. "ORDER BY name",
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
		];
	}
}

// __END__
