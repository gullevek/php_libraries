<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Template\HtmlBuilder\Replace;

/**
 * Test class for Template\HtmlBuilder\Replace
 * @coversDefaultClass \CoreLibs\Template\HtmlBuilder\Replace
 * @testdox \CoreLibs\Template\HtmlBuilder\Replace method tests
 */
final class CoreLibsTemplateHtmlBuilderReplaceTest extends TestCase
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
			Replace::replaceData(
				$html_block,
				[
					'ID', 'CSS', '{CONTENT}'
				],
				[
					'block-id', join(',', ['blue', 'red']),
					'Some content here<br>with bla bla inside'
				]
			)
		);
	}
}

// __END__
