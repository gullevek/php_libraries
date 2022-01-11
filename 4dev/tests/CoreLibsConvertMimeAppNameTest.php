<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\MimeAppName
 * @testdox CoreLibs\Convert\MimeAppName method tests
 */
final class CoreLibsConvertMimeAppNameTest extends TestCase
{

	public function mimeProvider(): array
	{
		return [
			'find matching app' => [
				0 => 'foo/bar',
				1 => 'FooBar Application',
				2 => 'FooBar Application',
			],
			'try to set empty mime type' => [
				0 => '',
				1 => 'Some app',
				2 => 'Other file'
			],
			'try to set empty app name' => [
				0 => 'some/app',
				1 => '',
				2 => 'Other file'
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider mimeProvider
	 * @testdox mimeSetAppName set $mime with $app and will be $expected [$_dataName]
	 *
	 * @param string $mime
	 * @param string $app
	 * @return void
	 */
	public function testMimeSetAppName(string $mime, string $app, string $expected): void
	{
		\CoreLibs\Convert\MimeAppName::mimeSetAppName($mime, $app);
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\MimeAppName::mimeGetAppName($mime)
		);
	}
}

// __END__
