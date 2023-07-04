<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Template\HtmlBuilder\Block;

/**
 * Test class for Template\HtmlBuilder\Block
 * @coversDefaultClass \CoreLibs\Template\HtmlBuilder\Block
 * @testdox \CoreLibs\Template\HtmlBuilder\Block method tests
 */
final class CoreLibsTemplateHtmlBuilderBlockTest extends TestCase
{
	public function testCreateBlock(): void
	{
		$el = Block::cel('div', 'id', 'content', ['css'], ['onclick' => 'foo();']);
		$this->assertEquals(
			'<div id="id" class="css" onclick="foo();">content</div>',
			Block::buildHtml($el)
		);
	}

	// ael
	// aelx|addSub
	// resetSub
	// acssel/rcssel/scssel
	// buildHtml
	// buildHtmlFromList|printHtmlFromArray
}

// __END__
