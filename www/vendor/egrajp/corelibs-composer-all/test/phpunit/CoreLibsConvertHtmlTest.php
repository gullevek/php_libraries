<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Html
 * @coversDefaultClass \CoreLibs\Convert\Html
 * @testdox \CoreLibs\Convert\Html method tests
 */
final class CoreLibsConvertHtmlTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function htmlentProvider(): array
	{
		return [
			'no conversion' => [
				0 => 'I am some string',
				1 => 'I am some string',
			],
			'conversion' => [
				0 => 'I have special <> inside',
				1 => 'I have special &lt;&gt; inside',
			],
			'skip number' => [
				0 => 1234,
				1 => 1234,
			],
			'utf8' => [
				0 => '日本語 <>',
				1 => '日本語 &lt;&gt;'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function removeLBProvider(): array
	{
		return [
			'nothing replaced, default' => [
				0 => 'I am some string',
				1 => null,
				2 => 'I am some string',
			],
			'string with \n replace -' => [
				0 => "I am\nsome string",
				1 => '-',
				2 => 'I am-some string',
			],
			'string with \r replace _' => [
				0 => "I am\rsome string",
				1 => '_',
				2 => 'I am_some string',
			],
			'string with \n\r, default' => [
				0 => "I am\n\rsome string",
				1 => null,
				2 => 'I am some string',
			],
			'string with \n\r replae ##BR##' => [
				0 => "I am\n\rsome string",
				1 => '##BR##',
				2 => 'I am##BR##some string',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function checkedProvider(): array
	{
		return [
			'haystack is a string and matching selected' => [
				0 => 'string',
				1 => 'string',
				2 => \CoreLibs\Convert\Html::SELECTED,
				3 => 'selected'
			],
			'haystack is a string and matching checked' => [
				0 => 'string',
				1 => 'string',
				2 => \CoreLibs\Convert\Html::CHECKED,
				3 => 'checked'
			],
			'haystack is a string and not matching' => [
				0 => 'string',
				1 => 'not matching',
				2 => \CoreLibs\Convert\Html::CHECKED,
				3 => null
			],
			'haystack is array and matching' => [
				0 => ['a', 'b', 'c'],
				1 => 'a',
				2 => \CoreLibs\Convert\Html::SELECTED,
				3 => 'selected'
			],
			'haystack is array and not matching' => [
				0 => ['a', 'b', 'c'],
				1 => 'not matching',
				2 => \CoreLibs\Convert\Html::SELECTED,
				3 => null
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::htmlent
	 * @dataProvider htmlentProvider
	 * @testdox htmlent $input will be $expected [$_dataName]
	 *
	 * @param mixed $input
	 * @param mixed $expected
	 * @return void
	 */
	public function testHtmlent($input, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Html::htmlent($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::removeLB
	 * @dataProvider removeLBProvider
	 * @testdox removeLB $input with replace $replace will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string|null $replace
	 * @param string $expected
	 * @return void
	 */
	public function testRemoveLB(string $input, ?string $replace, string $expected): void
	{
		if ($replace !== null) {
			$this->assertEquals(
				$expected,
				\CoreLibs\Convert\Html::removeLB($input, $replace)
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Convert\Html::removeLB($input)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checked
	 * @dataProvider checkedProvider
	 * @testdox checked find $needle in $haystack and return $type will be $expected [$_dataName]
	 *
	 * @param array<mixed>|string $haystack
	 * @param string $needle
	 * @param integer $type
	 * @param string|null $expected
	 * @return void
	 */
	public function testChecked($haystack, string $needle, int $type, ?string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Html::checked($haystack, $needle, $type)
		);
	}
}

// __END__
