<?php

namespace Admin\TestLibs;

class AttachOutside
{
	/** @var \CoreLibs\Debug\LoggingLegacy */
	public $log;
	public function __construct(\CoreLibs\Debug\LoggingLegacy $logger_class)
	{
		$this->log = $logger_class;
	}
	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function test(): string
	{
		$this->log->debug('ATTACHOUTSIDE', 'A test');
		return get_class($this);
	}
}

// __END__
