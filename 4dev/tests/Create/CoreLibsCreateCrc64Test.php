<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\Crc64
 * @coversDefaultClass \CoreLibs\Create\Crc64
 * @testdox \CoreLibs\Create\Crc64 method tests
 */
final class CoreLibsCreateCrc64Test extends TestCase
{
	public function providerFiles(): array
	{
		return [
			'10kb' => [
				'file' => __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'random_10KB.bin',
				'crc64_base64' => 'oMgkUkGrDME=',
				'crc64_hex' => 'a0c8245241ab0cc1',
			],
			'100kb' => [
				'file' => __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'random_100KB.bin',
				'crc64_base64' => 'i9Lpf3Y3D3M=',
				'crc64_hex' => '8bd2e97f76370f73',
			],
			'1mb' => [
				'file' => __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'random_1MB.bin',
				'crc64_base64' => 'WqYAirRJ0q8=',
				'crc64_hex' => '5aa6008ab449d2af',
			],
			'5mb' => [
				'file' => __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'random_5MB.bin',
				'crc64_base64' => '9hS5kUxbgR0=',
				'crc64_hex' => 'f614b9914c5b811d',
			],
			'10mb' => [
				'file' => __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'random_10MB.bin',
				'crc64_base64' => 'xDmtkk1JhjM=',
				'crc64_hex' => 'c439ad924d498633',
			],
			// everything above 10MB is too slow, sow do not check this
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::crc64
	 * @covers ::crc64hex
	 * @dataProvider providerFiles
	 * @testdox generate crc64 for $file and expecting $crc64_base64 and $crc64_hex [$_dataName]
	 *
	 * @return void
	 */
	public function testCrc64(string $file, string $crc64_base64, string $crc64_hex): void
	{
		$this->assertSame(
			$crc64_base64,
			\CoreLibs\Create\Crc64::crc64($file),
			"Failed asserting base64 CRC64 for $file"
		);
		$this->assertSame(
			$crc64_hex,
			\CoreLibs\Create\Crc64::crc64hex($file),
			"Failed asserting hex CRC64 for $file"
		);
	}
}

// __END__
