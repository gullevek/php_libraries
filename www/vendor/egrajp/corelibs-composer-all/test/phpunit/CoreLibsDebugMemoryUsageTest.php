<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Debug\MemoryUsage;

/**
 * Test class for Debug\MemoryUsage
 * @coversDefaultClass \CoreLibs\Debug\MemoryUsage
 * @testdox \CoreLibs\Debug\MemoryUsage method tests
 */
final class CoreLibsDebugMemoryUsageTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function memoryUsageProvider(): array
	{
		$regex_raw_off = '/^\[[\w\s_-]+\] Peak\/Curr\/Change: \d+(\.\d+)? ?\w{1,2}\/'
			. '\d+(\.\d+)? ?\w{1,2}\/'
			. 'Since Start: \d+(\.\d+)? ?\w{1,2} \| '
			. 'Since Last: \d+(\.\d+)? ?\w{1,2} \| '
			. 'Since Set: \d+(\.\d+)? ?\w{1,2}$/';
		$regex_raw_on = '/^\[[\w\s_-]+\] Peak\/Curr\/'
			// . 'Change: \d+(\.\d+)? ?\w{1,2}\/\d+(\.\d+)? ?\w{1,2} \[\d+\]\/'
			. 'Change: \d+(\.\d+)? ?\w{1,2}\/\d+(\.\d+)? ?\w{1,2}/'
			. 'Since Start: \d+(\.\d+)? ?\w{1,2} \[\d+\] \| '
			. 'Since Last: \d+(\.\d+)? ?\w{1,2} \[\d+\] \| '
			. 'Since Set: \d+(\.\d+)? ?\w{1,2} \[\d+\]$/';
		$regex_array = [
			'prefix' => '/^[\w\s_-]+$/',
			'peak' => '/^\d+$/',
			'usage' => '/^\d+$/',
			'start' => '/^\d+$/',
			'last' => '/^\d+$/',
			'set' => '/^\d+$/',
		];
		// 0: prefix
		// 1: raw flag
		// 2: set flags array
		// 3: array output expected (as regex)
		// 4: string output expected (as regex)
		return [
			'test normal' => [
				'test',
				null,
				[],
				$regex_array,
				$regex_raw_off,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::resetMemory
	 * @cover ::debugMemoryFlag
	 * @cover ::setStartMemory
	 * @cover ::setMemory
	 * @cover ::memoryUsage
	 * @cover ::printMemoryUsage
	 * @dataProvider memoryUsageProvider
	 * @testdox memoryUsage with $prefix, raw memory $raw [$_dataName]
	 *
	 * @param  string    $prefix
	 * @param  bool|null $raw
	 * @param  array     $set_flags
	 * @param  array     $expected_array
	 * @param  string    $expected_string
	 * @return void
	 */
	public function testMemoryUsage(
		string $prefix,
		?bool $raw,
		array $settings,
		array $expected_array,
		string $expected_string
	): void {
		// always reeset to null
		MemoryUsage::resetMemory();
		MemoryUsage::debugMemoryFlag(true);
		MemoryUsage::setStartMemory();
		MemoryUsage::setMemory();
		// run collector
		$memory = MemoryUsage::memoryUsage($prefix);
		if ($raw === null) {
			$string = MemoryUsage::printMemoryUsage($memory);
		} else {
			$string = MemoryUsage::printMemoryUsage($memory, $raw);
		}

		// expected_array for each
		foreach ($expected_array as $name => $regex) {
			$this->assertMatchesRegularExpression(
				$regex,
				(string)$memory[$name],
				'assert memory usage array ' . $name
			);
		}

		// regex match string
		$this->assertMatchesRegularExpression(
			$expected_string,
			$string,
			'assert memory usage string as regex'
		);

		// TODO additional tests with use more memory and check diff matching
		// TODO reset memory usage test
	}
}

// __END__
