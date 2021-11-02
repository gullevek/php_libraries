<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Undocumented class
 * @testdox CoreLibs\Check\File method tests
 */
final class CoreLibsCheckFileTest extends TestCase
{
	/** @var array<mixed> */
	private $files = [];

	protected function setUp(): void
	{
		// write a dummy files for testing
	}

	protected function tearDown(): void
	{
		// unlink files
	}

	/**
	 * main file list + data provider
	 *
	 * @return array
	 */
	public function filesList(): array
	{
		return [
			['filename.txt', 'txt', 5]
		];
	}

	public function filesExtensionProvider(): array
	{
		$list = [];
		foreach ($this->filesList as $row) {
			$list[$row[0] . ' must be extension ' . $row[1]] = [$row[0], $row[1]];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @#dataProvider filesExtensionProvider
	 * @#testdox File::getFilenameEnding Input $input must be $expected
	 * //string $input, string $expected
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testGetFilenameEnding(): void
	{
		// getFilenameEnding
		/* $this->assertEquals(
			$expected,
			\CoreLibs\Check\File::getFilenameEnding($input)
		); */
		$this->assertTrue(true, 'This should already work.');
		$this->markTestIncomplete(
			'testGetFilenameEnding has not been implemented yet.'
		);
	}

	/**
	 * Undocumented function
	 * // string $input, string $expected
	 *
	 * @return void
	 */
	public function testGetLinesFromFile(): void
	{
		// getLinesFromFile
		$this->assertTrue(true, 'This should already work.');
		$this->markTestIncomplete(
			'testGetLinesFromFile has not been implemented yet.'
		);
	}
}

// __END__
