<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test base setup
 * @testdox AAASetupData\AAASetupDataTest just setup BASE
 */
final class CoreLibsAAASetupDataTest extends TestCase
{
	public const TIME_ZONE = 'Asia/Tokyo';

	/**
	 * Covers nothing
	 *
	 * @testdox Just setup BASE
	 *
	 * @return void
	 */
	public function testSetupData(): void
	{
		if (!defined('BASE')) {
			define(
				'BASE',
				str_replace('/configs', '', __DIR__)
					. DIRECTORY_SEPARATOR
			);
		}
		$this->assertEquals(
			str_replace('/configs', '', __DIR__)
				. DIRECTORY_SEPARATOR,
			BASE,
			'BASE Path set check'
		);

		// if time zone is not set, errors can happen
		ini_set('date.timezone', self::TIME_ZONE);
	}
}

// __END__
