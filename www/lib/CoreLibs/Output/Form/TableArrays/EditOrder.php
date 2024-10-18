<?php

declare(strict_types=1);

namespace CoreLibs\Output\Form\TableArrays;

class EditOrder implements Interface\TableArraysInterface
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
	 * NOTE: this is a dummy array to just init the Form\Generate class and is not used for anything else
	 *
	 * @return array<mixed>
	 */
	public function setTableArray(): array
	{
		return [
			'table_array' => [
				'-'
			],
			'table_name' => '-',
			'load_query' => '',
			'show_fields' => [],
		];
	}
}

// __END__
