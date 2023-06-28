<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/6/1
 * DESCRIPTION:
 * html builder: element
 * nested and connected objects
*/

declare(strict_types=1);

namespace CoreLibs\Template\HtmlBuilder;

use CoreLibs\Template\HtmlBuilder\General\Settings;
use CoreLibs\Template\HtmlBuilder\General\Error;
use CoreLibs\Template\HtmlBuilder\General\HtmlBuilderExcpetion;

class Element
{
	/** @var string */
	private string $tag = '';
	/** @var string */
	private string $id = '';
	/** @var string */
	private string $name = '';
	/** @var string */
	private string $content = '';
	/** @var array<string> */
	private array $css = [];
	/** @var array<string,mixed> */
	private array $options = [];
	/** @var array<Element> list of elements */
	private array $sub = [];

	/**
	 * create new html element
	 *
	 * @param  string $tag                   html tag (eg div, button, etc)
	 * @param  string $id                    html tag id, used also for name if name
	 *                                       not set in $options
	 * @param  string $content               content text inside, eg <div>Content</div>
	 *                                       if sub elements exist, they are added after content
	 * @param  array<string> $css            array of css names, put style in $options
	 * @param  array<string,string> $options Additional element options in
	 *                                       key = value format
	 *                                       eg: onClick => 'something();'
	 *                                       id, css are skipped
	 *                                       name only set on input/button
	 * @throws HtmlBuilderExcpetion
	 */
	public function __construct(
		string $tag,
		string $id = '',
		string $content = '',
		array $css = [],
		array $options = []
	) {
		// exit if not valid tag
		try {
			$this->setTag($tag);
		} catch (HtmlBuilderExcpetion $e) {
			throw new HtmlBuilderExcpetion('Could not create Element', 0, $e);
		}
		$this->setId($id);
		$this->setName($options['name'] ?? '');
		$this->setContent($content);
		$this->addCss(...$css);
		$this->setOptions($options);
	}

	/**
	 * set tag
	 *
	 * @param  string $tag
	 * @return void
	 * @throws HtmlBuilderExcpetion
	 */
	public function setTag(string $tag): void
	{
		// tag must be letters only
		if (!preg_match("/^[A-Za-z]+$/", $tag)) {
			Error::setError(
				'201',
				'invalid or empty tag',
				['tag' => $tag]
			);
			throw new HtmlBuilderExcpetion('Invalid or empty tag: ' . $tag);
		}
		$this->tag = $tag;
	}

	/**
	 * get the tag name
	 *
	 * @return string HTML element tag
	 */
	public function getTag(): string
	{
		return $this->tag;
	}

	/**
	 * set the element id
	 *
	 * @param  string $id
	 * @return void
	 */
	public function setId(string $id): void
	{
		// invalid id and name check too
		// be strict: [a-zA-Z0-9], -, _
		// cannot start with digit, two hyphens or a hyphen with a digit:
		// 0abc
		// __abc
		// _0abc
		if (
			!empty($id) &&
			!preg_match("/^[A-Za-z][\w-]*$/", $id)
		) {
			Error::setWarning(
				'202',
				'possible invalid id',
				['id' => $id, 'tag' => $this->getTag()]
			);
			// TODO: shoud throw error
		}
		$this->id = $id;
	}

	/**
	 * get the html tag id
	 *
	 * @return string HTML element id
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Set name for elements
	 * only for elements that need it (input/button/form)
	 *
	 * @param  string $name
	 * @return void
	 */
	public function setName(string $name): void
	{
		if (
			!empty($name) &&
			!preg_match("/^[A-Za-z][\w-]*$/", $name)
		) {
			Error::setWarning(
				'203',
				'possible invalid name',
				['name' => $name, 'id' => $this->getId(), 'tag' => $this->getTag()]
			);
			// TODO: shoud throw error
		}
		$this->name = $name;
	}

	/**
	 * get the name if set
	 *
	 * @return string Optional HTML name (eg for input)
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Set new content for element
	 *
	 * @param  string $content
	 * @return void
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	/**
	 * get the elment text content (not sub elements)
	 *
	 * @return string HTML content text as is
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * set or update options
	 *
	 * @param  array<string,mixed> $options
	 * @return void
	 */
	public function setOptions(array $options): void
	{
		foreach ($options as $key => $value) {
			if (empty($key)) {
				Error::setError(
					'110',
					'Cannot set option with empty key',
					['id' => $this->getId(), 'tag' => $this->getTag()]
				);
				// TODO: shoud throw error
				continue;
			}
			// if data is null
			if ($value === null) {
				if (isset($this->options[$key])) {
					unset($this->options[$key]);
				} else {
					Error::setError(
						'210',
						'Cannot set option with null value',
						['id' => $this->getId(), 'tag' => $this->getTag()]
					);
				}
				// TODO: shoud throw error
				continue;
			}
			$this->options[$key] = $value;
		}
	}

	/**
	 * get the options array
	 * also holds "name" option
	 * anything like: style, javascript, value or any other html tag option
	 * right side can be empty but not null
	 *
	 * @return array<string,string> get options as list html option name and value
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	// Sub Elements

	/**
	 * get the sub elements (array of Elements)
	 *
	 * @return array<Element> Array of Elements (that can have sub elements)
	 */
	public function getSub(): array
	{
		return $this->sub;
	}

	/**
	 * add one or many sub elements (add at the end)
	 *
	 * @param  Element $sub One or many elements to add
	 * @return void
	 * @throws HtmlBuilderExcpetion
	 */
	public function addSub(Element ...$sub): void
	{
		foreach ($sub as $_sub) {
			// if one of the elements is the same as this class, ignore it
			// with this we avoid self reference loop
			if ($_sub == $this) {
				Error::setError(
					'100',
					'Cannot assign Element to itself, this would create an infinite loop',
					['id' => $this->getId(), 'tag' => $this->getTag()]
				);
				throw new HtmlBuilderExcpetion('Cannot assign Element to itself, this would create an infinite loop');
			}
			array_push($this->sub, $_sub);
		}
	}

	/**
	 * Remove an element from the sub array
	 * By pos in array or id set on first level
	 *
	 * @param  int|string $id String id name or int pos number in array
	 * @return void
	 */
	public function removeSub(int|string $id): void
	{
		// find element with id and remove it
		// or when number find pos in sub and remove it
		if (is_int($id)) {
			if (!isset($this->sub[$id])) {
				return;
			}
			unset($this->sub[$id]);
			return;
		}
		// only on first level
		foreach ($this->sub as $pos => $el) {
			if (
				$el->getId() === $id
			) {
				unset($this->sub[$pos]);
				return;
			}
		}
	}

	/**
	 * remove all sub elements
	 *
	 * @return void
	 */
	public function resetSub(): void
	{
		$this->sub = [];
	}

	// CSS Elements

	/**
	 * get the current set css elements
	 *
	 * @return array<string> list of css element entries
	 */
	public function getCss(): array
	{
		return $this->css;
	}

	/**
	 * add one or many new css elements
	 * Note that we can chain: add/remove/reset
	 *
	 * @param  string ...$css one or more css strings to add
	 * @return Element        Current element for chaining
	 */
	public function addCss(string ...$css): Element
	{
		// should do check for empty/invalid css
		$_set_css = [];
		foreach ($css as $_css) {
			if (empty($_css)) {
				Error::setError(
					'204',
					'cannot have empty css string',
				);
				// TODO: shoud throw error
				continue;
			}
			// -?[_A-Za-z][_A-Za-z0-9-]*
			if (!preg_match("/^-?[_A-Za-z][_A-Za-z0-9-]*$/", $_css)) {
				Error::setWarning(
					'205',
					'possible invalid css string',
					['css' => $_css, 'id' => $this->id, 'tag' => $this->tag]
				);
				// TODO: shoud throw error
			}
			$_set_css[] = $_css;
		}
		$this->css = array_unique(array_merge($this->css, $_set_css));
		return $this;
	}

	/**
	 * remove one or more css elements
	 * Note that we can chain: add/remove/reset
	 *
	 * @param  string ...$css one or more css strings to remove
	 * @return Element        Current element for chaining
	 */
	public function removeCss(string ...$css): Element
	{
		$this->css = array_diff($this->css, $css);
		return $this;
	}

	/**
	 * unset all css elements
	 * Note that we can chain: add/remove/reset
	 *
	 * @return Element
	 */
	public function resetCss(): Element
	{
		$this->css = [];
		return $this;
	}

	// build output from tree

	/**
	 * build html string from the current element tree (self)
	 * or from the Element tree given as parameter
	 * if $add_nl is set then new lines are added before each sub element added
	 * no indet is done (tab or other)
	 *
	 * @param  Element|null $tree                   Different Element tree to build
	 *                                              if not set (null), self is used
	 * @param  bool         $add_nl [default=false] Optional output string line breaks
	 * @return string                               HTML as string
	 */
	public function buildHtml(Element $tree = null, bool $add_nl = false): string
	{
		// print "D01: " . microtime(true) . "<br>";
		if ($tree === null) {
			$tree = $this;
		}
		$line = '<' . $tree->getTag();

		if ($tree->getId()) {
			$line .= ' id="' . $tree->getId() . '"';
			if (in_array($tree->getTag(), Settings::NAME_ELEMENTS)) {
				$line .= ' name="'
					. (!empty($tree->getName()) ? $tree->getName() : $tree->getId())
					. '"';
			}
		}
		if (count($tree->getCss())) {
			$line .= ' class="' . join(' ', $tree->getCss()) . '"';
		}
		foreach ($tree->getOptions() as $key => $item) {
			// skip
			if (in_array($key, Settings::SKIP_OPTIONS)) {
				continue;
			}
			$line .= ' ' . $key . '="' . $item . '"';
		}
		$line .= '>';
		if (strlen($tree->getContent()) > 0) {
			$line .= $tree->getContent();
		}
		// sub nodes
		foreach ($tree->getSub() as $sub) {
			if ($add_nl === true) {
				$line .= "\n";
			}
			$line .= $tree->buildHtml($sub, $add_nl);
			if ($add_nl === true) {
				$line .= "\n";
			}
		}

		// close line if needed
		if (!in_array($tree->getTag(), Settings::NO_CLOSE)) {
			$line .= '</' . $tree->getTag() . '>';
		}

		return $line;
	}

	// this is static

	/**
	 * Builds a single string from an array of elements
	 * a new line can be added before each new element if $add_nl is set to true
	 *
	 * @param  array<Element> $list                   array of Elements, uses buildHtml internal
	 * @param  bool           $add_nl [default=false] Optional output string line breaks
	 * @return string                                 HTML as string
	 */
	public static function buildHtmlFromList(array $list, bool $add_nl = false): string
	{
		$output = '';
		foreach ($list as $el) {
			if (!empty($output) && $add_nl === true) {
				$output .= "\n";
			}
			$output .= $el->buildHtml();
		}
		return $output;
	}

	// so we can call builder statically

	/**
	 * Search element tree for id and add
	 * if id is empty add at element given in parameter $base
	 *
	 * @param  Element $base   Element to attach to
	 * @param  Element $attach Element to attach (single)
	 * @param  string  $id     Optional id, if empty then attached at the end
	 *                         If set will loop through ALL sub elements until
	 *                         matching id found. if not found, not added
	 * @return Element         Element with attached sub element
	 */
	public static function addElementWithId(
		Element $base,
		Element $attach,
		string $id = ''
	): Element {
		// no id or matching id
		if (
			empty($id) ||
			$base->getId() == $id
		) {
			$base->addSub($attach);
			return $base;
		}
		// find id in 'id' in all 'sub'
		foreach ($base->getSub() as $el) {
			self::addElementWithId($el, $attach, $id);
		}

		return $base;
	}

	/**
	 * add one or more elemens to $base
	 *
	 * @param  Element $base      Element to attach to
	 * @param  Element ...$attach Element or Elements to attach
	 * @return Element            Element with attached sub elements
	 */
	public static function addElement(
		Element $base,
		Element ...$attach
	): Element {
		// we must make sure we do not self attach
		$base->addSub(...$attach);
		return $base;
	}

	/**
	 * Static call version for building
	 * not recommended to be used, rather use "Element->buildHtml()"
	 * wrapper for buildHtml
	 *
	 * @param  ?Element $tree                   Element tree to build
	 *                                          if not set returns empty string
	 * @param  bool     $add_nl [default=false] Optional output string line break
	 * @return string                           build html as string
	 * @deprecated Do not use, use Element->buildHtml() instead
	 */
	public static function printHtmlFromObject(Element $tree = null, bool $add_nl = false): string
	{
		// nothing ->bad
		if ($tree === null) {
			return '';
		}
		return $tree->buildHtml(add_nl: $add_nl);
	}

	/**
	 * Undocumented function
	 * wrapper for buildHtmlFromList
	 *
	 * @param  array<Element> $list                   array of Elements to build string from
	 * @param  bool           $add_nl [default=false] Optional output string line break
	 * @return string         build html as string
	 */
	public static function printHtmlFromArray(array $list, bool $add_nl = false): string
	{
		return self::buildHtmlFromList($list, $add_nl);
	}
}

// __END__
