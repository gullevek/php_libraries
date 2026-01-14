<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Combined\DateTime
 * @coversDefaultClass \CoreLibs\Combined\DateTime
 * @testdox \CoreLibs\Combined\DateTime method tests
 */
final class CoreLibsCombinedDateTimeTest extends TestCase
{
	/**
	 * timestamps
	 *
	 * @return array
	 */
	public function timestampProvider(): array
	{
		return [
			'valid timestamp no microtime' => [
				1641515890,
				false,
				false,
				'2022-01-07 09:38:10',
			],
			'valid timestamp with microtime' => [
				1641515890,
				true,
				false,
				'2022-01-07 09:38:10',
			],
			'valid timestamp with microtime float' => [
				1641515890,
				true,
				true,
				'2022-01-07 09:38:10',
			],
			'valid micro timestamp with microtime' => [
				1641515890.123456,
				true,
				false,
				'2022-01-07 09:38:10 1235ms',
			],
			'valid micro timestamp with microtime float' => [
				1641515890.123456,
				true,
				true,
				'2022-01-07 09:38:10.1235',
			],
			'valid micro timestamp no microtime' => [
				1641515890.123456,
				false,
				false,
				'2022-01-07 09:38:10',
			],
			'invalid timestamp' => [
				-123123,
				false,
				false,
				'1969-12-30 22:47:57',
			],
		];
	}

	/**
	 * date string convert test
	 *
	 * @covers ::dateStringFormat
	 * @dataProvider timestampProvider
	 * @testdox dateStringFormat $input (microtime $flag) will be $expected [$_dataName]
	 *
	 * @param int|float $input
	 * @param bool      $flag
	 * @param string    $expected
	 * @return void
	 */
	public function testDateStringFormat(
		$input,
		bool $flag_show_micro,
		bool $flag_micro_as_float,
		string $expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::dateStringFormat(
				$input,
				$flag_show_micro,
				$flag_micro_as_float
			)
		);
	}

	/**
	 * interval for both directions
	 *
	 * @return array
	 */
	public function intervalProvider(): array
	{
		return [
			'on hour' => [
				3600,
				false,
				'1h 0m 0s'
			],
			'interval no microtime' => [
				1641515890,
				false,
				'18999d 0h 38m 10s',
			],
			'interval with microtime' => [
				1641515890,
				true,
				'18999d 0h 38m 10s 0ms',
			],
			'micro interval no microtime' => [
				1641515890.123456,
				false,
				'18999d 0h 38m 10s',
			],
			'micro interval with microtime' => [
				1641515890.123456,
				true,
				'18999d 0h 38m 10s 124ms',
			],
			'negative interval no microtime' => [
				-1641515890,
				false,
				'-18999d 0h 38m 10s',
			],
			// short for mini tests
			'microtime only' => [
				0.123456,
				true,
				'0s 123ms',
			],
			'seconds only' => [
				30.123456,
				true,
				'30s 123ms',
			],
			'minutes only' => [
				90.123456,
				true,
				'1m 30s 123ms',
			],
			'hours only' => [
				3690.123456,
				true,
				'1h 1m 30s 123ms',
			],
			'days only' => [
				90090.123456,
				true,
				'1d 1h 1m 30s 123ms',
			],
			'already set' => [
				'1d 1h 1m 30s 1235ms',
				true,
				'1d 1h 1m 30s 1235ms',
			],
			'invalid data' => [
				'xyz',
				true,
				'0s',
			],
			'out of bounds timestamp' => [
				999999999999999,
				false,
				'1s'
			]
		];
	}

	/**
	 * time seconds convert test
	 *
	 * @covers ::timeStringFormat
	 * @dataProvider intervalProvider
	 * @testdox timeStringFormat $input (microtime $flag) will be $expected [$_dataName]
	 *
	 * @param string|int|float $input
	 * @param bool      $flag
	 * @param string    $expected
	 * @return void
	 */
	public function testTimeStringFormat(string|int|float $input, bool $flag, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::timeStringFormat($input, $flag)
		);
	}

	/**
	 * interval seconds convert
	 *
	 * @covers ::intervalStringFormat
	 * @dataProvider intervalProvider
	 * @testdox intervalStringFormat $input (microtime $show_micro) will be $expected [$_dataName]
	 *
	 * @param  string|int|float $input
	 * @param  bool      $show_micro
	 * @param  string    $expected
	 * @return void
	 */
	public function testIntervalStringFormat(string|int|float $input, bool $show_micro, string $expected): void
	{
		// we skip string input, that is not allowed
		if (is_string($input)) {
			$this->assertTrue(true, 'Skip strings');
			return;
		}
		// invalid values throw exception in default
		if ($input == 999999999999999) {
			$this->expectException(\LengthException::class);
		}
		// below is equal to timeStringFormat
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::intervalStringFormat(
				$input,
				show_microseconds: $show_micro,
				show_only_days: true,
				skip_zero: false,
				skip_last_zero: false,
				truncate_nanoseconds: true,
				truncate_zero_seconds_if_microseconds: false
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function intervalExtendedProvider(): array
	{
		return [
			// A
			'(60) default value' => [
				[
					'seconds' => 60,
				],
				'expected' => '1m',
				'exception' => null
			],
			'(60) default value, skip_last_zero:false' => [
				[
					'seconds' => 60,
					'skip_last_zero' => false,
				],
				'expected' => '1m 0s 0ms',
				'exception' => null
			],
			// B
			'(120.1) default value' => [
				[
					'seconds' => 120.1,
				],
				'expected' => '2m 100ms',
				'exception' => null
			],
			'(120.1) default value, skip_zero:false' => [
				[
					'seconds' => 120.1,
					'skip_zero' => false,
				],
				'expected' => '2m 0s 100ms',
				'exception' => null
			],
			'(120.1) default value, skip_last_zero:false' => [
				[
					'seconds' => 120.1,
					'skip_last_zero' => false,
				],
				'expected' => '2m 100ms',
				'exception' => null
			],
			// C
			'(3601) default value' => [
				[
					'seconds' => 3601,
				],
				'expected' => '1h 1s',
				'exception' => null
			],
			'(3601) default value, skip_zero:false' => [
				[
					'seconds' => 3601,
					'skip_zero' => false,
				],
				'expected' => '1h 0m 1s',
				'exception' => null
			],
			'(3601) default value, skip_last_zero:false' => [
				[
					'seconds' => 3601,
					'skip_last_zero' => false,
				],
				'expected' => '1h 1s 0ms',
				'exception' => null
			],
			// TODO create unit tests for ALL edge cases
			// CREATE abort tests, simple, all others are handled in exception tests
			'exception: \UnexpectedValueException:1' => [
				[
					'seconds' => 99999999999999999999999
				],
				'expected' => null,
				'exception' => [
					'class' => \UnexpectedValueException::class,
					'code' => 1,
				],
			]
		];
	}

	/**
	 * test all options for interval conversion
	 *
	 * @covers ::intervalStringFormat
	 * @dataProvider intervalExtendedProvider
	 * @testdox intervalStringFormat $input will be $expected / $exception [$_dataName]
	 *
	 * @param  array<string,null|int|float|bool> $parameter_list
	 * @param  string       $expected
	 * @param  array<string,mixed> $exception
	 * @return void
	 */
	public function testExtendedIntervalStringFormat(
		array $parameter_list,
		?string $expected,
		?array $exception
	): void {
		if ($expected === null && $exception === null) {
			$this->assertFalse(true, 'Cannot have expected and exception null in test data');
		}
		$parameters = [];
		foreach (
			[
				'seconds' => null,
				'truncate_after' => '',
				'natural_seperator' => false,
				'name_space_seperator' => false,
				'show_microseconds' => true,
				'short_time_name' => true,
				'skip_last_zero' => true,
				'skip_zero' => true,
				'show_only_days' => false,
				'auto_fix_microseconds' => false,
				'truncate_nanoseconds' => false,
				'truncate_zero_seconds_if_microseconds' => true,
			] as $param => $default
		) {
			if (empty($parameter_list[$param]) && $default === null) {
				$this->assertFalse(true, 'Parameter ' . $param . ' is mandatory ');
			} elseif (!isset($parameter_list[$param]) || $parameter_list[$param] === null) {
				$parameters[] = $default;
			} else {
				$parameters[] = $parameter_list[$param];
			}
		}
		if ($expected !== null) {
			$this->assertEquals(
				$expected,
				call_user_func_array('CoreLibs\Combined\DateTime::intervalStringFormat', $parameters)
			);
		} else {
			if (empty($exception['class']) || empty($exception['code'])) {
				$this->assertFalse(true, 'Exception tests need Exception name and Code');
			}
			$this->expectException($exception['class']);
			$this->expectExceptionCode($exception['code']);
			// if we have a message, must be regex
			if (!empty($exception['message'])) {
				$this->expectExceptionMessageMatches($exception['message']);
			}
			call_user_func_array('CoreLibs\Combined\DateTime::intervalStringFormat', $parameters);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function exceptionsIntervalProvider(): array
	{
		return [
			'UnexpectedValueException: 1 A' => [
				'seconds' => 99999999999999999999999,
				'params' => [],
				'exception' => \UnexpectedValueException::class,
				'exception_message' => "/^Seconds value is invalid, too large or more than six decimals: /",
				'excpetion_code' => 1,
			],
			'UnexpectedValueException: 1 B' => [
				'seconds' => 123.1234567,
				'params' => [],
				'exception' => \UnexpectedValueException::class,
				'exception_message' => "/^Seconds value is invalid, too large or more than six decimals: /",
				'excpetion_code' => 1,
			],
			// exception 2 is very likely covered by exception 1
			'LengthException: 3' => [
				'seconds' => 999999999999999999,
				'params' => [
					'show_only_days',
				],
				'exception' => \LengthException::class,
				'exception_message' => "/^Input seconds value is too large for days output: /",
				'excpetion_code' => 3,
			],
			'UnexpectedValueException: 4' => [
				'seconds' => 1234567,
				'params' => [
					'truncate_after'
				],
				'exception' => \UnexpectedValueException::class,
				'exception_message' => "/^truncate_after has an invalid value: /",
				'excpetion_code' => 4,
			],
			'UnexpectedValueException: 5' => [
				'seconds' => 1234567,
				'params' => [
					'show_only_days:truncate_after'
				],
				'exception' => \UnexpectedValueException::class,
				'exception_message' =>
					"/^If show_only_days is turned on, the truncate_after cannot be years or months: /",
				'excpetion_code' => 5,
			]
		];
	}

	/**
	 * Test all exceptions
	 *
	 * @covers ::intervalStringFormat
	 * @dataProvider exceptionsIntervalProvider
	 * @testdox intervalStringFormat: test Exceptions
	 *
	 * @param  int|float     $seconds
	 * @param  array<string> $params
	 * @param  string        $exception
	 * @param  string        $exception_message
	 * @param  int           $excpetion_code
	 * @return void
	 */
	public function testExceptionsIntervalStringFormat(
		int|float $seconds,
		array $params,
		string $exception,
		string $exception_message,
		int $excpetion_code,
	): void {
		$this->expectException($exception);
		$this->expectExceptionMessageMatches($exception_message);
		$this->expectExceptionCode($excpetion_code);
		if (empty($params)) {
			\CoreLibs\Combined\DateTime::intervalStringFormat($seconds);
		} else {
			if (in_array('show_only_days', $params)) {
				\CoreLibs\Combined\DateTime::intervalStringFormat($seconds, show_only_days:true);
			} elseif (in_array('truncate_after', $params)) {
				\CoreLibs\Combined\DateTime::intervalStringFormat($seconds, truncate_after: 'v');
			} elseif (in_array('show_only_days:truncate_after', $params)) {
				\CoreLibs\Combined\DateTime::intervalStringFormat($seconds, show_only_days:true, truncate_after: 'y');
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function reverseIntervalProvider(): array
	{
		return [
			'interval no microtime' => [
				'18999d 0h 38m 10s',
				1641515890,
			],
			'micro interval with microtime' => [
				'18999d 0h 38m 10s 1235ms',
				1641515891.235,
			],
			'micro interval with microtime' => [
				'18999d 0h 38m 10s 1234567890ms',
				1642750457.89,
			],
			'negative interval no microtime' => [
				'-18999d 0h 38m 10s',
				-1641515890,
			],
			// short for mini tests
			'microtime only' => [
				'0s 1235ms',
				1.235,
			],
			'seconds only' => [
				'30s 1235ms',
				31.235,
			],
			'minutes only' => [
				'1m 30s 1235ms',
				91.235,
			],
			'hours only' => [
				'1h 1m 30s 1235ms',
				3691.235,
			],
			'days only' => [
				'1d 1h 1m 30s 1235ms',
				90091.235,
			],
			'days only with long name' => [
				'1day 1hour 1min 30second 1235millisecond',
				90091.235,
			],
			// Test day variations
			'day singular' => [
				'5day',
				432000,
			],
			'days plural' => [
				'3days',
				259200,
			],
			'days with space' => [
				'2days 5h',
				190800,
			],
			'day without space' => [
				'1day1h',
				90000,
			],
			// Test hour variations
			'hour singular' => [
				'2hour',
				7200,
			],
			'hours plural' => [
				'4hours',
				14400,
			],
			'hours with space' => [
				'3hours 30m',
				12600,
			],
			'hour without space' => [
				'1hour30m',
				5400,
			],
			// Test minute variations
			'min short' => [
				'45min',
				2700,
			],
			'minute singular' => [
				'1minute',
				60,
			],
			'minutes plural' => [
				'10minutes',
				600,
			],
			'minutes with space' => [
				'5minutes 20s',
				320,
			],
			'min without space' => [
				'2min30s',
				150,
			],
			// Test second variations
			'sec short' => [
				'30sec',
				30,
			],
			'second singular' => [
				'1second',
				1,
			],
			'seconds plural' => [
				'45seconds',
				45,
			],
			'seconds with space' => [
				'15seconds 500ms',
				15.5,
			],
			'sec without space' => [
				'10sec250ms',
				10.25,
			],
			// Test millisecond variations
			'ms short' => [
				'500ms',
				0.5,
			],
			'millis short' => [
				'250millis',
				0.25,
			],
			'millisec medium singular' => [
				'250millisec',
				0.25,
			],
			'millisecs medium plural' => [
				'250millisecs',
				0.25,
			],
			'misec medium singular' => [
				'250millisec',
				0.25,
			],
			'msecs medium plural' => [
				'250millisecs',
				0.25,
			],
			'millisecond long singular' => [
				'1millisecond',
				0.001,
			],
			'milliseconds long plural' => [
				'999milliseconds',
				0.999,
			],
			// Test negative values
			'negative days' => [
				'-5d',
				-432000,
			],
			'negative hours' => [
				'-3h',
				-10800,
			],
			'negative minutes' => [
				'-45m',
				-2700,
			],
			'negative seconds' => [
				'-30s',
				-30,
			],
			'negative milliseconds' => [
				'-500ms',
				-0.5,
			],
			'negative complex' => [
				'-2days 3hours 15minutes 30seconds 250milliseconds',
				-184530.25,
			],
			// Test combined formats
			'all components short' => [
				'1d 2h 3m 4s 5ms',
				93784.005,
			],
			'all components long' => [
				'2days 3hours 4minutes 5seconds 678milliseconds',
				183845.678,
			],
			'mixed short and long' => [
				'1day 2h 3minutes 4sec 100ms',
				93784.1,
			],
			'no spaces between components' => [
				'1d2h3m4s5ms',
				93784.005,
			],
			'only days and milliseconds' => [
				'5d 123ms',
				432000.123,
			],
			'only hours and seconds' => [
				'2h 45s',
				7245,
			],
			'only minutes and milliseconds' => [
				'30m 500ms',
				1800.5,
			],
			// Test zero values
			'zero seconds' => [
				'0s',
				0,
			],
			'zero with milliseconds' => [
				'0s 123ms',
				0.123,
			],
			// Test large values
			'large days' => [
				'365days',
				31536000,
			],
			'large hours' => [
				'48hours',
				172800,
			],
			'large minutes' => [
				'1440minutes',
				86400,
			],
			'large seconds' => [
				'86400seconds',
				86400,
			],
			// Test edge cases with spaces
			'extra spaces' => [
				'1d 2h 3m 4s 5ms',
				93784.005,
			],
			'mixed spaces and no spaces' => [
				'1d 2h3m 4s5ms',
				93784.005,
			],
			// Test single component each
			'only days short' => [
				'7d',
				604800,
			],
			'only hours short' => [
				'12h',
				43200,
			],
			'only minutes short' => [
				'90m',
				5400,
			],
			'only seconds short' => [
				'120s',
				120,
			],
			'only milliseconds short' => [
				'1500ms',
				1.5,
			],
			'already set' => [
				1641515890,
				1641515890,
			],
			'invalid data' => [
				'xyz',
				'xyz',
			],
			'empty data' => [
				' ',
				' ',
			],
			'out of bound data' => [
				'99999999999999999999d',
				8.64E+24
			],
			'spaces inbetween' => [
				'  -       9 d 2h 58minutes 35    seconds     123     ms          ',
				-788315.123,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::stringToTime
	 * @dataProvider reverseIntervalProvider
	 * @testdox stringToTime $input will be $expected [$_dataName]
	 *
	 * @param string|int|float $input
	 * @param string|int|float $expected
	 * @return void
	 */
	public function testStringToTime($input, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::stringToTime($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::stringToTime
	 * @testdox stringToTime invalid input will throw exception if requested
	 *
	 * @return void
	 */
	public function testStringToTimeException(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches("/^Invalid time string format, cannot parse: /");
		\CoreLibs\Combined\DateTime::stringToTime('1x 2y 3z', true);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::stringToTime
	 * @testdox stringToTime empty input will throw exception if requested
	 *
	 * @return void
	 */
	public function testStringToTimeExceptionEmpty(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches("/^Invalid time string format, no interval value found: /");
		\CoreLibs\Combined\DateTime::stringToTime(' ', true);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function dateProvider(): array
	{
		return [
			'valid date with -' => [
				'2021-12-12',
				true,
			],
			'valid date with /' => [
				'2021/12/12',
				true,
			],
			'valid date time with -' => [
				'2021-12-12 12:12:12',
				true,
			],
			'invalid date' => [
				'2021-31-31',
				false,
			],
			'invalid date string' => [
				'xyz',
				false,
			],
			'out of bound date' => [
				'9999-12-31',
				true
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checkDate
	 * @dataProvider dateProvider
	 * @testdox checkDate $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param bool $expected
	 * @return void
	 */
	public function testCheckDate(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::checkDate($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function dateTimeProvider(): array
	{
		return [
			'valid date time with -' => [
				'2021-12-12 12:12:12',
				true,
			],
			'valid date time with /' => [
				'2021/12/12 12:12:12',
				true,
			],
			'vald date time with hour/min' => [
				'2021/12/12 12:12',
				true,
			],
			'valid date missing time' => [
				'2021-12-12',
				false,
			],
			'valid date invalid time string' => [
				'2021-12-12 ab:cd',
				false,
			],
			'invalid hour +' => [
				'2021-12-12 35:12',
				false,
			],
			'invalid hour -' => [
				'2021-12-12 -12:12',
				false,
			],
			'invalid minute +' => [
				'2021-12-12 23:65:12',
				false,
			],
			'invalid minute -' => [
				'2021-12-12 23:-12:12',
				false,
			],
			'invalid seconds +' => [
				'2021-12-12 23:12:99',
				false,
			],
			'invalid seconds -' => [
				'2021-12-12 23:12:-12',
				false,
			],
			'invalid seconds string' => [
				'2021-12-12 23:12:ss',
				false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checkDateTime
	 * @dataProvider dateTimeProvider
	 * @testdox checkDateTime $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param bool $expected
	 * @return void
	 */
	public function testCheckDateTime(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::checkDateTime($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function dateCompareProvider(): array
	{
		return [
			'first date smaller' => [
				'2020-12-12',
				'2021-12-12',
				-1,
				null,
				null,
			],
			'dates equal' => [
				'2020-12-12',
				'2020-12-12',
				0,
				null,
				null,
			],
			'second date smaller' => [
				'2021-12-12',
				'2020-12-12',
				1,
				null,
				null,
			],
			'dates equal with different time' => [
				'2020-12-12 12:12:12',
				'2020-12-12 13:13:13',
				0,
				null,
				null,
			],
			'invalid dates --' => [
				'--',
				'--',
				false,
				'UnexpectedValueException',
				1,
			],
			'empty dates' => [
				'',
				'',
				false,
				'UnexpectedValueException',
				1
			],
			'invalid dates' => [
				'not a date',
				'not a date either',
				false,
				'UnexpectedValueException',
				2
			],
			'invalid end date' => [
				'1990-01-01',
				'not a date either',
				false,
				'UnexpectedValueException',
				3
			],
			'out of bound dates' => [
				'1900-1-1',
				'9999-12-31',
				-1,
				null,
				null,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::compareDate
	 * @dataProvider dateCompareProvider
	 * @testdox compareDate $input_a compared to $input_b will be $expected [$_dataName]
	 *
	 * @param string $input_a
	 * @param string $input_b
	 * @param int|bool $expected
	 * @param string|null $exception
	 * @param int|null $exception_code
	 * @return void
	 */
	public function testCompareDate(
		string $input_a,
		string $input_b,
		int|bool $expected,
		?string $exception,
		?int $exception_code
	): void {
		if ($expected === false) {
			$this->expectException($exception);
			$this->expectExceptionCode($exception_code);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::compareDate($input_a, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function dateTimeCompareProvider(): array
	{
		return [
			'first date smaller no time' => [
				'2020-12-12',
				'2021-12-12',
				-1,
				null,
				null,
			],
			'dates equal no timestamp' => [
				'2020-12-12',
				'2020-12-12',
				0,
				null,
				null,
			],
			'second date smaller no timestamp' => [
				'2021-12-12',
				'2020-12-12',
				1,
				null,
				null,
			],
			'date equal first time smaller' => [
				'2020-12-12 12:12:12',
				'2020-12-12 13:13:13',
				-1,
				null,
				null,
			],
			'date equal time equal' => [
				'2020-12-12 12:12:12',
				'2020-12-12 12:12:12',
				0,
				null,
				null,
			],
			'date equal second time smaller' => [
				'2020-12-12 13:13:13',
				'2020-12-12 12:12:12',
				1,
				null,
				null,
			],
			'valid date invalid time' => [
				'2020-12-12 13:99:13',
				'2020-12-12 12:12:99',
				false,
				'UnexpectedValueException',
				2
			],
			'valid date invalid end time' => [
				'2020-12-12 13:12:13',
				'2020-12-12 12:12:99',
				false,
				'UnexpectedValueException',
				3
			],
			'invalid datetimes --' => [
				'--',
				'--',
				false,
				'UnexpectedValueException',
				1
			],
			'empty datetimess' => [
				'',
				'',
				false,
				'UnexpectedValueException',
				1
			],
			'invalid date times' => [
				'not a date',
				'not a date either',
				false,
				'UnexpectedValueException',
				2
			],
			'invalid end date time' => [
				'1990-01-01 12:12:12',
				'not a date either',
				false,
				'UnexpectedValueException',
				3
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::compareDateTime
	 * @dataProvider dateTimeCompareProvider
	 * @testdox compareDateTime $input_a compared to $input_b will be $expected [$_dataName]
	 *
	 * @param string $input_a
	 * @param string $input_b
	 * @param int|bool $expected
	 * @param string|null $exception
	 * @param int|null $exception_code
	 * @return void
	 */
	public function testCompareDateTime(
		string $input_a,
		string $input_b,
		int|bool $expected,
		?string $exception,
		?int $exception_code
	): void {
		if ($expected === false) {
			$this->expectException($exception);
			$this->expectExceptionCode($exception_code);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::compareDateTime($input_a, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function daysIntervalProvider(): array
	{
		return [
			// normal and format tests
			'valid interval / not named array' => [
				'input_a' => '2020/1/1',
				'input_b' => '2020/1/30',
				'return_named' => false, // return_named
				'include_end_date' => true, // include_end_date
				'exclude_start_date' => false, // exclude_start_date
				'expected' => [30, 22, 8, false],
			],
			'valid interval / named array' => [
				'input_a' => '2020/1/1',
				'input_b' => '2020/1/30',
				'return_named' => true,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => ['overall' => 30, 'weekday' => 22, 'weekend' => 8, 'reverse' => false],
			],
			'valid interval with "-"' => [
				'input_a' => '2020-1-1',
				'input_b' => '2020-1-30',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => [30, 22, 8, false],
			],
			'valid interval with time' => [
				'input_a' => '2020/1/1 12:12:12',
				'input_b' => '2020/1/30 13:13:13',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => [30, 22, 8, false],
			],
			// invalid
			'invalid dates' => [
				'input_a' => 'abc',
				'input_b' => 'xyz',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => [0, 0, 0, false]
			],
			// this test will take a long time
			'out of bound dates' => [
				'input_a' => '1900-1-1',
				'input_b' => '9999-12-31',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => [2958463, 2113189, 845274, false],
			],
			// tests for include/exclude
			'exclude end date' => [
				'input_b' => '2020/1/1',
				'input_a' => '2020/1/30',
				'return_named' => false,
				'include_end_date' => false,
				'exclude_start_date' => false,
				'expected' => [29, 21, 8, false],
			],
			'exclude start date' => [
				'input_b' => '2020/1/1',
				'input_a' => '2020/1/30',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => true,
				'expected' => [29, 21, 8, false],
			],
			'exclude start and end date' => [
				'input_b' => '2020/1/1',
				'input_a' => '2020/1/30',
				'return_named' => false,
				'include_end_date' => false,
				'exclude_start_date' => true,
				'expected' => [28, 20, 8, false],
			],
			// reverse
			'reverse: valid interval' => [
				'input_a' => '2020/1/30',
				'input_b' => '2020/1/1',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => false,
				'expected' => [30, 22, 8, true],
			],
			'reverse: exclude end date' => [
				'input_a' => '2020/1/30',
				'input_b' => '2020/1/1',
				'return_named' => false,
				'include_end_date' => false,
				'exclude_start_date' => false,
				'expected' => [29, 21, 8, true],
			],
			'reverse: exclude start date' => [
				'input_a' => '2020/1/30',
				'input_b' => '2020/1/1',
				'return_named' => false,
				'include_end_date' => true,
				'exclude_start_date' => true,
				'expected' => [29, 21, 8, true],
			],
			'reverse: exclude start and end date' => [
				'input_a' => '2020/1/30',
				'input_b' => '2020/1/1',
				'return_named' => false,
				'include_end_date' => false,
				'exclude_start_date' => true,
				'expected' => [28, 20, 8, true],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::calcDaysInterval
	 * @dataProvider daysIntervalProvider
	 * @testdox calcDaysInterval $input_a compared to $input_b will be $expected [$_dataName]
	 * @medium
	 *
	 * @param string $input_a
	 * @param string $input_b
	 * @param bool   $return_named
	 * @param array  $expected
	 * @return void
	 */
	public function testCalcDaysInterval(
		string $input_a,
		string $input_b,
		bool $return_named,
		bool $include_end_date,
		bool $exclude_start_date,
		$expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::calcDaysInterval(
				$input_a,
				$input_b,
				return_named:$return_named,
				include_end_date:$include_end_date,
				exclude_start_date:$exclude_start_date
			),
			'call calcDaysInterval'
		);
		if ($return_named) {
			$this->assertEquals(
				$expected,
				\CoreLibs\Combined\DateTime::calcDaysIntervalNamedIndex(
					$input_a,
					$input_b,
					include_end_date:$include_end_date,
					exclude_start_date:$exclude_start_date
				),
				'call calcDaysIntervalNamedIndex'
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Combined\DateTime::calcDaysIntervalNumIndex(
					$input_a,
					$input_b,
					include_end_date:$include_end_date,
					exclude_start_date:$exclude_start_date
				),
				'call calcDaysIntervalNamedIndex'
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function weekdayNumberProvider(): array
	{
		return [
			'0 invalid' => [0, null, 'Inv',],
			'0 invalid long' => [0, true, 'Invalid',],
			'1 short' => [1, null, 'Mon',],
			'1 long' => [1, true, 'Monday',],
			'2 short' => [2, null, 'Tue',],
			'2 long' => [2, true, 'Tuesday',],
			'3 short' => [3, null, 'Wed',],
			'3 long' => [3, true, 'Wednesday',],
			'4 short' => [4, null, 'Thu',],
			'4 long' => [4, true, 'Thursday',],
			'5 short' => [5, null, 'Fri',],
			'5 long' => [5, true, 'Friday',],
			'6 short' => [6, null, 'Sat',],
			'6 long' => [6, true, 'Saturday',],
			'7 short' => [7, null, 'Sun',],
			'7 long' => [7, true, 'Sunday',],
			'8 invalid' => [8, null, 'Inv',],
			'8 invalid long' => [8, true, 'Invalid',],
		];
	}

	/**
	 * int weekday number to string weekday
	 *
	 * @covers ::setWeekdayNameFromIsoDow
	 * @dataProvider weekdayNumberProvider
	 * @testdox weekdayListProvider $input (short $flag) will be $expected [$_dataName]
	 *
	 * @param  int       $input
	 * @param  bool|null $flag
	 * @param  string    $expected
	 * @return void
	 */
	public function testSetWeekdayNameFromIsoDow(
		int $input,
		?bool $flag,
		string $expected
	): void {
		if ($flag === null) {
			$output = \CoreLibs\Combined\DateTime::setWeekdayNameFromIsoDow($input);
		} else {
			$output = \CoreLibs\Combined\DateTime::setWeekdayNameFromIsoDow($input, $flag);
		}
		$this->assertEquals(
			$expected,
			$output
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function weekdayDateProvider(): array
	{
		return [
			'invalid date' => ['2022-02-31', -1],
			'1: monday' => ['2022-07-25', 1],
			'2: tuesday' => ['2022-07-26', 2],
			'3: wednesday' => ['2022-07-27', 3],
			'4: thursday' => ['2022-07-28', 4],
			'5: friday' => ['2022-07-29', 5],
			'6: saturday' => ['2022-07-30', 6],
			'7: sunday' => ['2022-07-31', 7],
		];
	}

	/**
	 * date to weekday number
	 *
	 * @covers ::setWeekdayNumberFromDate
	 * @dataProvider weekdayDateProvider
	 * @testdox setWeekdayNumberFromDate $input will be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  int    $expected
	 * @return void
	 */
	public function testSetWeekdayNumberFromDate(
		string $input,
		int $expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::setWeekdayNumberFromDate($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function weekdayDateNameProvider(): array
	{
		return [
			'invalid date short' => ['2022-02-31', null, 'Inv'],
			'invalid date long' => ['2022-02-31', true, 'Invalid'],
			'Mon short' => ['2022-07-25', null, 'Mon'],
			'Monday long' => ['2022-07-25', true, 'Monday'],
			'Tue short' => ['2022-07-26', null, 'Tue'],
			'Tuesday long' => ['2022-07-26', true, 'Tuesday'],
			'Wed short' => ['2022-07-27', null, 'Wed'],
			'Wednesday long' => ['2022-07-27', true, 'Wednesday'],
			'Thu short' => ['2022-07-28', null, 'Thu'],
			'Thursday long' => ['2022-07-28', true, 'Thursday'],
			'Fri short' => ['2022-07-29', null, 'Fri'],
			'Friday long' => ['2022-07-29', true, 'Friday'],
			'Sat short' => ['2022-07-30', null, 'Sat'],
			'Saturday long' => ['2022-07-30', true, 'Saturday'],
			'Sun short' => ['2022-07-31', null, 'Sun'],
			'Sunday long' => ['2022-07-31', true, 'Sunday'],
		];
	}

	/**
	 * date to weekday name
	 *
	 * @covers ::setWeekdayNameFromDate
	 * @dataProvider weekdayDateNameProvider
	 * @testdox setWeekdayNameFromDate $input (short $flag) will be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  bool|null $flag
	 * @param  string $expected
	 * @return void
	 */
	public function testSetWeekdayNameFromDate(
		string $input,
		?bool $flag,
		string $expected
	): void {
		if ($flag === null) {
			$output = \CoreLibs\Combined\DateTime::setWeekdayNameFromDate($input);
		} else {
			$output = \CoreLibs\Combined\DateTime::setWeekdayNameFromDate($input, $flag);
		}
		$this->assertEquals(
			$expected,
			$output
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function dateRangeHasWeekendProvider(): array
	{
		return [
			'no weekend' => [
				'2023-07-03',
				'2023-07-04',
				false
			],
			'start weekend sat' => [
				'2023-07-01',
				'2023-07-04',
				true
			],
			'start weekend sun' => [
				'2023-07-02',
				'2023-07-04',
				true
			],
			'end weekend sat' => [
				'2023-07-03',
				'2023-07-08',
				true
			],
			'end weekend sun' => [
				'2023-07-03',
				'2023-07-09',
				true
			],
			'long period > 6 days' => [
				'2023-07-03',
				'2023-07-27',
				true
			],
			// reverse
			'reverse: no weekend' => [
				'2023-07-04',
				'2023-07-03',
				false
			],
			'reverse: start weekend sat' => [
				'2023-07-04',
				'2023-07-01',
				true
			],
			'reverse: start weekend sun' => [
				'2023-07-04',
				'2023-07-02',
				true
			],
			'reverse: end weekend sat' => [
				'2023-07-08',
				'2023-07-03',
				true
			],
			'reverse: end weekend sun' => [
				'2023-07-09',
				'2023-07-03',
				true
			],
			'reverse: long period > 6 days' => [
				'2023-07-27',
				'2023-07-03',
				true
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dateRangeHasWeekend
	 * @dataProvider dateRangeHasWeekendProvider
	 * @testdox dateRangeHasWeekend $start_date and $end_date are expected weekend $expected [$_dataName]
	 *
	 * @param  string $start_date
	 * @param  string $end_date
	 * @param  bool   $expected
	 * @return void
	 */
	public function testDateRangeHasWeekend(
		string $start_date,
		string $end_date,
		bool $expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::dateRangeHasWeekend($start_date, $end_date)
		);
	}
}

// __END__
