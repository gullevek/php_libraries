<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditMenuGroup implements \CoreLibs\Output\Form\TableArraysInterface
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
				'edit_menu_group_id' => [
					'value' => $_POST['edit_menu_group_id'] ?? '',
					'type' => 'hidden',
					'pk' => 1
				],
				'name' => [
					'value' => $_POST['name'] ?? '',
					'output_name' => 'Group name',
					'mandatory' => 1,
					'type' => 'text'
				],
				'flag' => [
					'value' => $_POST['flag'] ?? '',
					'output_name' => 'Flag',
					'mandatory' => 1,
					'type' => 'text',
					'error_check' => 'alphanumeric|unique'
				],
				'order_number' => [
					'value' => $_POST['order_number'] ?? '',
					'output_name' => 'Group order',
					'type' => 'order',
					'int' => 1,
					'order' => 1
				],
			],
			'table_name' => 'edit_menu_group',
			'load_query' => "SELECT edit_menu_group_id, name FROM edit_menu_group ORDER BY name",
			'show_fields' => [
				[
					'name' => 'name'
				],
			],
		];
	}
}

// __END__
