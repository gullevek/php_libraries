<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\File
 * @testdox CoreLibs\Check\File method tests
 */
final class CoreLibsCheckFileTest extends TestCase
{
	/** @var array<mixed> */
	// private $files_list = [];
	/** @var string */
	private $base_folder = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

	/**
	 * main file list + data provider
	 *
	 * filename, file extension matching, lines in file, -1 for nothing
	 *
	 * @return array
	 */
	public function filesList(): array
	{
		return [
			['filename.txt', 'txt', 5],
			['filename.csv', 'csv', 15],
			['filename.tsv', 'tsv', 0],
			['file_does_not_exits', '', -1],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function filesExtensionProvider(): array
	{
		$list = [];
		foreach ($this->filesList() as $row) {
			$list[$row[0] . ' must be extension ' . $row[1]] = [$row[0], $row[1]];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function filesLinesProvider(): array
	{
		$list = [];
		foreach ($this->filesList() as $row) {
			$list[$row[0] . ' must have ' . $row[2] . ' lines'] = [$row[0], $row[2]];
		}
		return $list;
	}

	/**
	 * Tests if file extension matches
	 *
	 * @dataProvider filesExtensionProvider
	 * @testdox getFilenameEnding $input must be extension $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testGetFilenameEnding(string $input, string $expected): void
	{
		// getFilenameEnding
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\File::getFilenameEnding($input)
		);
	}

	/**
	 * Tests the file line read
	 *
	 * @dataProvider filesLinesProvider
	 * @testdox getLinesFromFile $input must have $expected lines [$_dataName]
	 *
	 * @param  string $input    file name
	 * @param  int    $expected lines in file
	 * @return void
	 */
	public function testGetLinesFromFile(string $input, int $expected): void
	{
		// create file
		if ($expected > -1) {
			$file = $this->base_folder . $input;
			$fp = fopen($file, 'w');
			for ($i = 0; $i < $expected; $i++) {
				fwrite($fp, 'This is row ' . ($i + 1) . PHP_EOL);
			}
			fclose($fp);
		}
		// test
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\File::getLinesFromFile($this->base_folder . $input)
		);
		// unlink file
		if (is_file($this->base_folder . $input)) {
			unlink($this->base_folder . $input);
		}
	}
}

// __END__
