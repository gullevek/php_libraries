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

namespace Test;

use Test\DB;

class Test
{
	/** @var DB\TestDB */
	private $test_db;

	public function __construct()
	{
		// calls all tests
		$this->testPrivate();
		$this->testProtected();
		$this->testPublic();

		// call intern
		$this->test_db = new DB\TestDB();
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
}

// __END__
