<?php

// phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Debug\RunningTime
 * @coversDefaultClass \CoreLibs\Debug\RunningTime
 * @testdox \CoreLibs\Debug\RunningTime method tests
 */
final class CoreLibsDebugRunningTimeTest extends TestCase
{
	public function hrRunningTimeProvider(): array
	{
		return [
			'default time' => [
				0 => null,
				1 => '/^\d{4}\.\d{5,}$/'
			],
			'nanoseconds' => [
				0 => 'ns',
				1 => '/^\d{10}$/'
			],
			'microseconds' => [
				0 => 'ys',
				1 => '/^\d{7}\.\d{3}$/'
			],
			'milliseconds' => [
				0 => 'ms',
				1 => '/^\d{4}\.\d{6}$/'
			],
			'seconds' => [
				0 => 's',
				1 => '/^\d{1}\.\d{9}$/'
			],
			'invalid fallback to ms' => [
				0 => 'invalid',
				1 => '/^\d{4}\.\d{6}$/'
			]
		];
	}

	public function runningTimeProvider(): array
	{
		return [
			'run time test' => [
				0 => '/^\d{1}$/',
				1 => '/^Start: \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} 0\.\d{7,}$/',
				2 => '/^Start:$/'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::hrRunningTime
	 * @dataProvider hrRunningTimeProvider
	 * @testdox hrRunningTime with $out_time matching $expected [$_dataName]
	 *
	 * @param string|null $out_time
	 * @param string $expected
	 * @return void
	 */
	public function testHrRunningTime(?string $out_time, string $expected): void
	{
		$start = \CoreLibs\Debug\RunningTime::hrRunningTime();
		$this->assertEquals(
			0,
			$start
		);
		time_nanosleep(1, 500);
		if ($out_time === null) {
			$end = \CoreLibs\Debug\RunningTime::hrRunningTime();
		} else {
			$end = \CoreLibs\Debug\RunningTime::hrRunningTime($out_time);
		}
		$this->assertMatchesRegularExpression(
			$expected,
			(string)$end
		);
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider runningTimeProvider
	 * @testdox runningTime matching return $expected_number and start $expected_start end $expected_end [$_dataName]
	 *
	 * @param string $expected_number
	 * @param string $expected_start
	 * @param string $expected_end
	 * @return void
	 */
	public function testRunningTime(string $expected_number, string $expected_start, string $expected_end): void
	{
		$start = \CoreLibs\Debug\RunningTime::runningTime(true);
		$this->assertEquals(
			0,
			$start
		);
		$this->assertMatchesRegularExpression(
			$expected_start,
			\CoreLibs\Debug\RunningTime::runningTimeString()
		);
		time_nanosleep(1, 500);
		$end = \CoreLibs\Debug\RunningTime::runningTime(true);
		$this->assertMatchesRegularExpression(
			$expected_number,
			(string)$end
		);
		$this->assertMatchesRegularExpression(
			$expected_end,
			\CoreLibs\Debug\RunningTime::runningTimeString()
		);
	}
}

// __END__
