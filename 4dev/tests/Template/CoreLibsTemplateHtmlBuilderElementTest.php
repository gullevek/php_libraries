<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Template\HtmlBuilder\Element;
use CoreLibs\Template\HtmlBuilder\General\Error;
use CoreLibs\Template\HtmlBuilder\General\HtmlBuilderExcpetion;

/**
 * Test class for Template\HtmlBuilder\Element
 * @coversDefaultClass \CoreLibs\Template\HtmlBuilder\Element
 * @testdox \CoreLibs\Template\HtmlBuilder\Element method tests
 */
final class CoreLibsTemplateHtmlBuilderElementTest extends TestCase
{
	public function providerCreateElements(): array
	{
		return [
			'simple div' => [
				'tag' => 'div',
				'id' => 'id',
				'content' => 'content',
				'css' => ['css'],
				'options' => ['onclick' => 'foo();'],
				'expected' => '<div id="id" class="css" onclick="foo();">content</div>'
			],
			'simple input' => [
				'tag' => 'input',
				'id' => 'id',
				'content' => null,
				'css' => ['css'],
				'options' => ['name' => 'name', 'onclick' => 'foo();'],
				'expected' => '<input id="id" name="name" class="css" onclick="foo();">'
			]
		];
	}


	/**
	 * Undocumented function
	 *
	 * @covers ::Element
	 * @covers ::buildHtml
	 * @covers ::getTag
	 * @covers ::getId
	 * @covers ::getContent
	 * @covers ::getOptions
	 * @covers ::getCss
	 * @dataProvider providerCreateElements
	 * @testdox create single new Element test [$_dataName]
	 *
	 * @param  string      $tag
	 * @param  string|null $id
	 * @param  string|null $content
	 * @param  array|null  $css
	 * @param  array|null  $options
	 * @param  string      $expected
	 * @return void
	 */
	public function testCreateElement(
		string $tag,
		?string $id,
		?string $content,
		?array $css,
		?array $options,
		string $expected
	): void {
		$el = new Element($tag, $id ?? '', $content ?? '', $css ?? [], $options ?? []);
		$this->assertEquals(
			$expected,
			$el->buildHtml(),
			'element creation failed'
		);

		$this->assertEquals(
			$tag,
			$el->getTag(),
			'get tag failed'
		);

		if ($id !== null) {
			$this->assertEquals(
				$id,
				$el->getId(),
				'get id failed'
			);
		}
		if ($content !== null) {
			$this->assertEquals(
				$content,
				$el->getContent(),
				'get content failed'
			);
		}
		if ($css !== null) {
			$this->assertEquals(
				$css,
				$el->getCss(),
				'get css failed'
			);
		}
		if ($options !== null) {
			$this->assertEquals(
				$options,
				$el->getOptions(),
				'get options failed'
			);
		}
		if (!empty($options['name'])) {
			$this->assertEquals(
				$options['name'],
				$el->getName(),
				'get name failed'
			);
		}
	}

	/**
	 * css add/remove
	 *
	 * @cover ::getCss
	 * @cover ::addCss
	 * @cover ::removeCss
	 * @testdox test handling of adding and removing css classes
	 *
	 * @return void
	 */
	public function testCssHandling(): void
	{
		$el = new Element('div', 'css-test', 'CSS content');
		$this->assertEqualsCanonicalizing(
			[],
			$el->getCss(),
			'check empty css'
		);
		$el->addCss('foo');
		$this->assertEqualsCanonicalizing(
			['foo'],
			$el->getCss(),
			'check added one css'
		);
		$el->removeCss('foo');
		$this->assertEqualsCanonicalizing(
			[],
			$el->getCss(),
			'check remove added css'
		);
		// add serveral
		// remove some of them
		$el->addCss('a', 'b', 'c');
		$this->assertEqualsCanonicalizing(
			['a', 'b', 'c'],
			$el->getCss(),
			'check added some css'
		);
		$el->removeCss('a', 'c');
		// $this->assertArray
		$this->assertEqualsCanonicalizing(
			['b'],
			$el->getCss(),
			'check remove some css'
		);
		// chained add and remove
		$el->addCss('a', 'b', 'c', 'd')->removeCss('b', 'd');
		$this->assertEqualsCanonicalizing(
			['a', 'c'],
			$el->getCss(),
			'check chain add remove some css'
		);
		$el->resetCss();
		$this->assertEqualsCanonicalizing(
			[],
			$el->getCss(),
			'check reset css'
		);
		// remove something that does not eixst
		$el->addCss('exists');
		$el->removeCss('not');
		$this->assertEqualsCanonicalizing(
			['exists'],
			$el->getCss(),
			'check remove not exitsing'
		);
	}

	/**
	 * nested test
	 *
	 * @testdox nested test and loop assign detection
	 *
	 * @return void
	 */
	public function testBuildNested(): void
	{
		Error::resetMessages();
		$el = new Element('div', 'build-test');
		$el_sub = new Element('div', 'sub-1');
		$el->addSub($el_sub);
		$this->assertEquals(
			'<div id="build-test"><div id="sub-1"></div></div>',
			$el->buildHtml(),
			'nested build failed'
		);
		// this would create a loop, throws error
		$this->expectException(HtmlBuilderExcpetion::class);
		$this->expectExceptionMessage("Cannot assign Element to itself, this would create an infinite loop");
		$el_sub->addSub($el_sub);
		$this->assertEquals(
			'<div id="sub-1"></div>',
			$el_sub->buildHtml(),
			'loop detection failed'
		);
		$this->assertTrue(
			Error::hasError(),
			'failed to throw error post loop detection'
		);
		$this->assertEquals(
			[[
				'level' => 'Error',
				'id' => '100',
				'message' => 'Cannot assign Element to itself, this would create an infinite loop',
				'context' => ['tag' => 'div', 'id' => 'sub-1']
			]],
			Error::getMessages(),
			'check error is 100 failed'
		);
		// get sub
		$this->assertEquals(
			[$el_sub],
			$el->getSub(),
			'get sub failed'
		);
		// reset sub
		$el->resetSub();
		$this->assertEquals(
			[],
			$el->getSub(),
			'reset sub failed'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox updated nested connection
	 *
	 * @return void
	 */
	public function testNestedChangeContent(): void
	{
		$el = new Element('div', 'build-test');
		$el_s_1 = new Element('div', 'sub-1');
		$el_s_2 = new Element('div', 'sub-2');
		$el_s_3 = new Element('div', 'sub-3');
		$el_s_4 = new Element('div', 'sub-4');

		$el->addSub($el_s_1, $el_s_2);
		// only sub -1, -2
		$this->assertEquals(
			'<div id="build-test"><div id="sub-1"></div><div id="sub-2"></div></div>',
			$el->buildHtml(),
			'check basic nested'
		);

		// now add -3, -4 to both -1 and -2
		$el_s_1->addSub($el_s_3, $el_s_4);
		$el_s_2->addSub($el_s_3, $el_s_4);
		$this->assertEquals(
			'<div id="build-test"><div id="sub-1"><div id="sub-3"></div><div id="sub-4">'
			. '</div></div><div id="sub-2"><div id="sub-3"></div><div id="sub-4"></div>'
			. '</div></div>',
			$el->buildHtml(),
			'check nested added'
		);

		// now add some css to el_s_3, will update in both sets
		$el_s_3->addCss('red');
		$this->assertEquals(
			'<div id="build-test"><div id="sub-1"><div id="sub-3" class="red"></div><div id="sub-4">'
			. '</div></div><div id="sub-2"><div id="sub-3" class="red"></div><div id="sub-4"></div>'
			. '</div></div>',
			$el->buildHtml(),
			'check nested u@dated'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox test change tag/id/content
	 *
	 * @return void
	 */
	public function testChangeElementData(): void
	{
		$el = new Element('div', 'id', 'Content');
		// content change
		$this->assertEquals(
			'Content',
			$el->getContent(),
			'set content'
		);
		$el->setContent('New Content');
		$this->assertEquals(
			'New Content',
			$el->getContent(),
			'changed content'
		);

		$this->assertEquals(
			'div',
			$el->getTag(),
			'set tag'
		);
		$el->setTag('span');
		$this->assertEquals(
			'span',
			$el->getTag(),
			'changed tag'
		);

		$this->assertEquals(
			'id',
			$el->getId(),
			'set id'
		);
		$el->setId('id-2');
		$this->assertEquals(
			'id-2',
			$el->getId(),
			'changed id'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox test change options
	 *
	 * @return void
	 */
	public function testChangeOptions(): void
	{
		$el = new Element('button', 'id', 'Action', ['css'], ['value' => '3']);
		$this->assertEquals(
			['value' => '3'],
			$el->getOptions(),
			'set option'
		);
		$el->setOptions([
			'value' => '2'
		]);
		$this->assertEquals(
			['value' => '2'],
			$el->getOptions(),
			'changed option'
		);
		// add a new one
		$el->setOptions([
			'Foo' => 'bar',
			'Moo' => 'cow'
		]);
		$this->assertEquals(
			[
				'value' => '2',
				'Foo' => 'bar',
				'Moo' => 'cow'
			],
			$el->getOptions(),
			'changed option'
		);
	}

	// build output
	// build from array list

	/**
	 * Undocumented function
	 *
	 * @testdox build element tree from object
	 *
	 * @return void
	 */
	public function testBuildHtmlObject(): void
	{
		// build a simple block
		// div -> div -> button
		//     -> div -> span
		//     -> div -> input
		$el = new Element('div', 'master', '', ['master']);
		$el->addSub(
			Element::addElement(
				new Element('div', 'div-button', '', ['dv-bt']),
				new Element('button', 'button-id', 'Click me', ['bt-red'], [
					'OnClick' => 'action();',
					'value' => 'click',
					'type' => 'button'
				]),
			),
			Element::addElement(
				new Element('div', 'div-span', '', ['dv-sp']),
				new Element('span', 'span-id', 'Big important message<br>other', ['red']),
			),
			Element::addElement(
				new Element('div', 'div-input', '', ['dv-in']),
				new Element('input', 'input-id', '', ['in-blue'], [
					'OnClick' => 'otherAction();',
					'value' => 'Touch',
					'type' => 'button'
				]),
			),
		);
		$this->assertEquals(
			'<div id="master" class="master">'
			. '<div id="div-button" class="dv-bt">'
			. '<button id="button-id" name="button-id" class="bt-red" OnClick="action();" '
			. 'value="click" type="button">Click me</button>'
			. '</div>'
			. '<div id="div-span" class="dv-sp">'
			. '<span id="span-id" class="red">Big important message<br>other</span>'
			. '</div>'
			. '<div id="div-input" class="dv-in">'
			. '<input id="input-id" name="input-id" '
			. 'class="in-blue" OnClick="otherAction();" value="Touch" type="button">'
			. '</div>'
			. '</div>',
			$el->buildHtml()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox build elements from array list
	 *
	 * @return void
	 */
	public function testbuildHtmlArray(): void
	{
		$this->assertEquals(
			'<div id="id-1">A</div>'
			. '<div id="id-2">B</div>'
			. '<div id="id-3">C</div>',
			Element::buildHtmlFromList([
				new Element('div', 'id-1', 'A'),
				new Element('div', 'id-2', 'B'),
				new Element('div', 'id-3', 'C'),
			])
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox check for invalid tag detection, possible invalid id, possible invalid css
	 *
	 * @return void
	 */
	public function testInvalidElement(): void
	{
		Error::resetMessages();
		$this->expectException(HtmlBuilderExcpetion::class);
		$this->expectExceptionMessage("Could not create Element");
		$el = new Element('');
		$this->assertTrue(
			Error::hasError(),
			'failed to set error invalid tag detection'
		);
		$this->assertEquals(
			[[
				'level' => 'Error',
				'id' => '201',
				'message' => 'invalid or empty tag',
				'context' => ['tag' => '']
			]],
			Error::getMessages(),
			'check error message failed'
		);

		// if we set invalid tag
		$el = new Element('div');
		$this->expectException(HtmlBuilderExcpetion::class);
		$this->expectExceptionMessageMatches("/^Invalid or empty tag: /");
		$this->expectExceptionMessage("Invalid or empty tag: 123123");
		$el->setTag('123123');
		$this->assertTrue(
			Error::hasError(),
			'failed to set error invalid tag detection'
		);
		$this->assertEquals(
			[[
				'level' => 'Error',
				'id' => '201',
				'message' => 'invalid or empty tag',
				'context' => ['tag' => '']
			]],
			Error::getMessages(),
			'check error message failed'
		);


		// invalid id (warning)
		Error::resetMessages();
		$el = new Element('div', '-$a15');
		$this->assertTrue(
			Error::hasWarning(),
			'failed to set warning invalid id detection'
		);
		$this->assertEquals(
			[[
				'level' => 'Warning',
				'id' => '202',
				'message' => 'possible invalid id',
				'context' => ['id' => '-$a15', 'tag' => 'div']
			]],
			Error::getMessages(),
			'check error message failed'
		);

		// invalid name
		Error::resetMessages();
		$el = new Element('div', 'valid', '', [], ['name' => '-$asdf&']);
		$this->assertTrue(
			Error::hasWarning(),
			'failed to set warning invalid name detection'
		);
		$this->assertEquals(
			[[
				'level' => 'Warning',
				'id' => '203',
				'message' => 'possible invalid name',
				'context' => ['name' => '-$asdf&', 'id' => 'valid', 'tag' => 'div']
			]],
			Error::getMessages(),
			'check error message failed'
		);
	}

	// static add element
	// print object/array
}

// __END__
