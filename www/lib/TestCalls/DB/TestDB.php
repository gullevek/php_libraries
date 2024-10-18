<?php

/*
 * TEST sets for DB::IO
 */

namespace TestCalls\DB;

class TestDB
{
	/** @var \CoreLibs\DB\IO */
	private \CoreLibs\DB\IO $db;
	/** @var \CoreLibs\Logging\Logging */
	private \CoreLibs\Logging\Logging $log;

	/** @var \TestCalls\Test */
	public $main;

	/**
	 * Undocumented function
	 *
	 * @param \TestCalls\Test $main
	 */
	public function __construct(
		\TestCalls\Test $main
	) {
		$this->db = $main->db;
		$this->log = $main->log;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	private function testDBa(): void
	{
		$this->log->debug('TEST DB', 'Call in testDBa');
		$this->db->dbInfo();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function testRunDB(): void
	{
		$this->log->debug('TEST DB', 'Call in testRunDB');
		$this->testDBa();
	}
}

// __ENB__
