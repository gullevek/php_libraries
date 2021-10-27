<?php

/*
 * TEST sets for DB::IO
 */

namespace Test\DB;

use CoreLibs\DB\IO;

class TestDB
{
	/** @var IO */
	private $db;
	/** @var array<mixed> */
	private $config;

	/**
	 * Undocumented function
	 */
	public function __construct()
	{
		$this->config = [
			'db_name' => $_ENV['DB_NAME_TEST'] ?? '',
			'db_user' => $_ENV['DB_USER_TEST'] ?? '',
			'db_pass' => $_ENV['DB_PASS_TEST'] ?? '',
			'db_host' => $_ENV['DB_HOST_TEST'] ?? '',
			'db_port' => 5432,
			'db_schema' => 'public',
			'db_type' => 'pgsql',
			'db_encoding' => '',
			'db_ssl' => 'allow'
		];
		$this->db = new IO($this->config);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	private function testDBa(): void
	{
		$this->db->dbInfo();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function testRunDB(): void
	{
		$this->testDBa();
	}
}

// __ENB__
