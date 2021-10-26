<?php

/*
 * TEST sets only
 */

declare(strict_types=1);

namespace Test;

class Test
{

	public function __construct()
	{
		// calls all tests
		$this->testPrivate();
		$this->testProtected();
		$this->testPublic();
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
}

// __END__
