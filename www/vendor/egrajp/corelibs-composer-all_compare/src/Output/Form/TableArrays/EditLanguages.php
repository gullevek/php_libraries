<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditLanguages implements Interface\TableArraysInterface
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
				'edit_language_id' => [
					'value' => $_POST['edit_language_id'] ?? '',
					'type' => 'hidden',
					'pk' => 1
				],
				'short_name' => [
					'value' => $_POST['short_name'] ?? '',
					'output_name' => 'Language (short)',
					'mandatory' => 1,
					'type' => 'text',
					'size' => 2,
					'length' => 2
				],
				'long_name' => [
					'value' => $_POST['long_name'] ?? '',
					'output_name' => 'Language (long)',
					'mandatory' => 1,
					'type' => 'text',
					'size' => 40
				],
				'iso_name' => [
					'value' => $_POST['iso_name'] ?? '',
					'output_name' => 'ISO Code',
					'mandatory' => 1,
					'type' => 'text'
				],
				'order_number' => [
					'value' => $_POST['order_number'] ?? '',
					'int' => 1,
					'order' => 1
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
				'lang_default' => [
					'value' => $_POST['lang_default'] ?? '',
					'output_name' => 'Default Language',
					'int' => 1,
					'type' => 'binary',
					'element_list' => [
						'1' => 'Yes',
						'0' => 'No'
					],
				],
			],
			'load_query' => "SELECT edit_language_id, long_name, iso_name, enabled "
				. "FROM edit_language "
				. "ORDER BY long_name",
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
	}
}

// __END__
