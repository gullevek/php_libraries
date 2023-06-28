<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Template\HtmlBuilder\StringReplace;

/**
 * Test class for Template\HtmlBuilder\StringReplace
 * @coversDefaultClass \CoreLibs\Template\HtmlBuilder\StringReplace
 * @testdox \CoreLibs\Template\HtmlBuilder\StringReplace method tests
 */
final class CoreLibsTemplateHtmlBuilderStringReplaceTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @covers ::replaceData
	 * @testdox test basic replaceData
	 *
	 * @return void
	 */
	public function testReplaceData(): void
	{
		$html_block = <<<HTML
<div id="{ID}" class="{CSS}">
	{CONTENT}
</div>
HTML;

		$this->assertEquals(
			<<<HTML
<div id="block-id" class="blue,red">
	Some content here<br>with bla bla inside
</div>
HTML,
			StringReplace::replaceData(
				$html_block,
				[
					'ID' => 'block-id',
					'CSS' => join(',', ['blue', 'red']),
					'{CONTENT}' => 'Some content here<br>with bla bla inside',
				]
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox replaceData error
	 *
	 * @return void
	 */
	/* public function testReplaceDataErrors(): void
	{
		$this->expectException(HtmlBuilderExcpetion::class);
		$this->expectExceptionMessage("Replace and content array count differ");
		StringReplace::replaceData('<span>{FOO}</span>', ['{FOO}', '{BAR}'], ['foo']);
	} */
}

// __END__
