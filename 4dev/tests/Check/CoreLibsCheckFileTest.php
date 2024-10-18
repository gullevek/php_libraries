<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\File
 * @coversDefaultClass \CoreLibs\Check\File
 * @testdox \CoreLibs\Check\File method tests
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
			['filename.txt', 'txt', 5, 'text/plain'],
			['filename.csv', 'csv', 15, 'text/csv'],
			['filename.tsv', 'tsv', 0, 'text/plain'],
			['file_does_not_exits', '', -1, ''],
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

	public function mimeTypeProvider(): array
	{
		$list = [];
		foreach ($this->filesList() as $row) {
			$list[$row[0] . ' must be mime type ' . $row[3]] = [$row[0], $row[3]];
		}
		return $list;
	}

	/**
	 * Tests if file extension matches
	 *
	 * @covers ::getFilenameEnding
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
	 * @covers ::getLinesFromFile
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

	/**
	 * Undocumented function
	 *
	 * @covers ::getMimeType
	 * @dataProvider mimeTypeProvider
	 * @testdox getMimeType $input must be mime type $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testGetMimeType(string $input, string $expected): void
	{
		if (!empty($expected)) {
			$file = $this->base_folder . $input;
			$fp = fopen($file, 'w');
			switch ($expected) {
				case 'text/csv':
					for ($i = 1; $i <= 10; $i++) {
						fwrite($fp, '"This is row","' . $expected . '",' . $i . PHP_EOL);
					}
					break;
				case 'text/tsv':
					for ($i = 1; $i <= 10; $i++) {
						fwrite($fp, "\"This is row\"\t\"" . $expected . "\"\t\"" . $i . PHP_EOL);
					}
					break;
				case 'text/plain':
					fwrite($fp, 'This is mime type: ' . $expected . PHP_EOL);
					break;
			}
			fclose($fp);
		} else {
			$this->expectException(\UnexpectedValueException::class);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\File::getMimeType($this->base_folder . $input)
		);
		// unlink file
		if (is_file($this->base_folder . $input)) {
			unlink($this->base_folder . $input);
		}
	}
}

// __END__
