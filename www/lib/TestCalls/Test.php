<?php

/*
 * TEST sets only
 *
 * composer.json
 * autoloader: {
 * 		...
 *      "psr-4": {
 *           "gullevek\\CoreLibs\\": "src/"
 *      }
 * }
 */

declare(strict_types=1);

namespace TestCalls;

use TestCalls\DB;

class Test
{
	/** @var DB\TestDB */
	private $test_db;

	/** @var \CoreLibs\DB\IO */
	public \CoreLibs\DB\IO $db;
	/** @var \CoreLibs\Logging\Logging */
	public \CoreLibs\Logging\Logging $log;

	public function __construct(
		\CoreLibs\DB\IO $db,
		\CoreLibs\Logging\Logging $log
	) {
		$this->db = $db;
		$this->log = $log;
		// calls all tests
		$this->testPrivate();
		$this->testProtected();
		$this->testPublic();

		// call intern
		$this->test_db = new DB\TestDB($this);
	}

	public function __destruct()
	{
		// calls all close tests
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function testPrivate(): string
	{
		$string = 'TEST Private';
		return $string;
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function testProtected(): string
	{
		$string = 'TEST Protected';
		return $string;
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function testPublic(): string
	{
		$string = 'TEST Public';
		return $string;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function testClasses(): void
	{
		$this->test_db->testRunDB();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function testDbCall(): void
	{
		$q = <<<SQL
		SELECT
			type, sdate, integer
		FROM
			foobar
		LIMIT
			1;
		SQL;
		if (is_array($res = $this->db->dbReturnRow($q))) {
			print "OUTPUT: " . $this->log->prAr($res);
		} else {
			$this->log->error('Failure to run query');
		}
	}
}

// __END__
