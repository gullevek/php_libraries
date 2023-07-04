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
	 * interval for both directions
	 *
	 * @return array
	 */
	public function intervalProvider(): array
	{
		return [
			'interval no microtime' => [
				1641515890,
				false,
				'18999d 0h 38m 10s',
			],
			'interval with microtime' => [
				1641515890,
				true,
				'18999d 0h 38m 10s',
			],
			'micro interval no microtime' => [
				1641515890.123456,
				false,
				'18999d 0h 38m 10s',
			],
			'micro interval with microtime' => [
				1641515890.123456,
				true,
				'18999d 0h 38m 10s 1235ms',
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
				'0s 1235ms',
			],
			'seconds only' => [
				30.123456,
				true,
				'30s 1235ms',
			],
			'minutes only' => [
				90.123456,
				true,
				'1m 30s 1235ms',
			],
			'hours only' => [
				3690.123456,
				true,
				'1h 1m 30s 1235ms',
			],
			'days only' => [
				90090.123456,
				true,
				'1d 1h 1m 30s 1235ms',
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
				1641515890.1235,
			],
			'micro interval with microtime' => [
				'18999d 0h 38m 10s 1234567890ms',
				1641515890.1234567,
			],
			'negative interval no microtime' => [
				'-18999d 0h 38m 10s',
				-1641515890,
			],
			// short for mini tests
			'microtime only' => [
				'0s 1235ms',
				0.1235,
			],
			'seconds only' => [
				'30s 1235ms',
				30.1235,
			],
			'minutes only' => [
				'1m 30s 1235ms',
				90.1235,
			],
			'hours only' => [
				'1h 1m 30s 1235ms',
				3690.1235,
			],
			'days only' => [
				'1d 1h 1m 30s 1235ms',
				90090.1235,
			],
			'already set' => [
				1641515890,
				1641515890,
			],
			'invalid data' => [
				'xyz',
				'xyz',
			],
			'out of bound data' => [
				'99999999999999999999d',
				8.64E+24
			],
		];
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
	 * @return array
	 */
	public function dateCompareProvider(): array
	{
		return [
			'first date smaller' => [
				'2020-12-12',
				'2021-12-12',
				-1,
			],
			'dates equal' => [
				'2020-12-12',
				'2020-12-12',
				0,
			],
			'second date smaller' => [
				'2021-12-12',
				'2020-12-12',
				1
			],
			'dates equal with different time' => [
				'2020-12-12 12:12:12',
				'2020-12-12 13:13:13',
				0,
			],
			'invalid dates --' => [
				'--',
				'--',
				false
			],
			'empty dates' => [
				'',
				'',
				false
			],
			'invalid dates' => [
				'not a date',
				'not a date either',
				false,
			],
			'out of bound dates' => [
				'1900-1-1',
				'9999-12-31',
				-1
			]
		];
	}

	public function dateTimeCompareProvider(): array
	{
		return [
			'first date smaller no time' => [
				'2020-12-12',
				'2021-12-12',
				-1,
			],
			'dates equal no timestamp' => [
				'2020-12-12',
				'2020-12-12',
				0,
			],
			'second date smaller no timestamp' => [
				'2021-12-12',
				'2020-12-12',
				1
			],
			'date equal first time smaller' => [
				'2020-12-12 12:12:12',
				'2020-12-12 13:13:13',
				-1,
			],
			'date equal time equal' => [
				'2020-12-12 12:12:12',
				'2020-12-12 12:12:12',
				0,
			],
			'date equal second time smaller' => [
				'2020-12-12 13:13:13',
				'2020-12-12 12:12:12',
				1,
			],
			'valid date invalid time' => [
				'2020-12-12 13:99:13',
				'2020-12-12 12:12:99',
				false,
			],
			'invalid datetimes --' => [
				'--',
				'--',
				false,
			],
			'empty datetimess' => [
				'',
				'',
				false,
			],
			'invalid datetimes' => [
				'not a date',
				'not a date either',
				false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function daysIntervalProvider(): array
	{
		return [
			'valid interval /, not named array' => [
				'2020/1/1',
				'2020/1/30',
				false,
				[29, 22, 8],
			],
			'valid interval /, named array' => [
				'2020/1/1',
				'2020/1/30',
				true,
				['overall' => 29, 'weekday' => 22, 'weekend' => 8],
			],
			'valid interval -' => [
				'2020-1-1',
				'2020-1-30',
				false,
				[29, 22, 8],
			],
			'valid interval switched' => [
				'2020/1/30',
				'2020/1/1',
				false,
				[28, 0, 0],
			],
			'valid interval with time' => [
				'2020/1/1 12:12:12',
				'2020/1/30 13:13:13',
				false,
				[28, 21, 8],
			],
			'invalid dates' => [
				'abc',
				'xyz',
				false,
				[0, 0, 0]
			],
			// this test will take a long imte
			'out of bound dates' => [
				'1900-1-1',
				'9999-12-31',
				false,
				[2958463,2113189,845274],
			],
		];
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
			]
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
	 * interval convert test
	 *
	 * @covers ::timeStringFormat
	 * @dataProvider intervalProvider
	 * @testdox timeStringFormat $input (microtime $flag) will be $expected [$_dataName]
	 *
	 * @param int|float $input
	 * @param bool      $flag
	 * @param string    $expected
	 * @return void
	 */
	public function testTimeStringFormat($input, bool $flag, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::timeStringFormat($input, $flag)
		);
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
	 * @covers ::compareDate
	 * @dataProvider dateCompareProvider
	 * @testdox compareDate $input_a compared to $input_b will be $expected [$_dataName]
	 *
	 * @param string $input_a
	 * @param string $input_b
	 * @param int|bool $expected
	 * @return void
	 */
	public function testCompareDate(string $input_a, string $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::compareDate($input_a, $input_b)
		);
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
	 * @return void
	 */
	public function testCompareDateTime(string $input_a, string $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::compareDateTime($input_a, $input_b)
		);
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
	 * @param bool   $flag
	 * @param array $expected
	 * @return void
	 */
	public function testCalcDaysInterval(
		string $input_a,
		string $input_b,
		bool $flag,
		$expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\DateTime::calcDaysInterval($input_a, $input_b, $flag)
		);
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
