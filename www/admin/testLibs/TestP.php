<?php

namespace Admin\TestLibs;

class TestP
{
	/** @var \CoreLibs\Logging\Logging */
	public $log;
	public function __construct(
		\CoreLibs\Logging\Logging $log
	) {
		$this->log = $log;
	}

	public function test(): void
	{
		$this->log->info('TestL::test call');
		$this->subCall();
	}

	public function subCall(): void
	{
		$this->log->info('TestL::sub_call call');
	}
}

// __END__
