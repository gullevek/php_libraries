<?php // phpcs:disable Generic.Files.LineLength

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/6/1
 * DESCRIPTION:
 * html builder: array
 * static build for array lists (not objects)
 *
 * Recommended to use the Object one or for speed the String Replace
*/

namespace CoreLibs\Template\HtmlBuilder;

use CoreLibs\Template\HtmlBuilder\General\Settings;
use CoreLibs\Template\HtmlBuilder\General\Error;
use CoreLibs\Template\HtmlBuilder\General\HtmlBuilderExcpetion;

class Block
{
	/**
	 * Create Element
	 *
	 * @param  string $tag
	 * @param  string $id
	 * @param  string $content
	 * @param  array<string> $css,
	 * @param  array<string,string>  $options
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 * @throws HtmlBuilderExcpetion
	 */
	public static function cel(
		string $tag,
		string $id = '',
		string $content = '',
		array $css = [],
		array $options = []
	): array {
		if (!preg_match("/^[A-Za-z]+$/", $tag)) {
			Error::setError(
				'201',
				'invalid or empty tag',
				['tag' => $tag]
			);
			throw new HtmlBuilderExcpetion('Invalid or empty tag');
		}
		return [
			'tag' => $tag,
			'id' => $id,
			'name' => $options['name'] ?? '',
			'content' => $content,
			'css' => $css,
			'options' => $options,
			'sub' => [],
		];
	}

	/**
	 * Search element tree for id and add
	 * if id is empty add at current
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $base
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $attach
	 * @param  string  $id
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function ael(
		array $base,
		array $attach,
		string $id = ''
	): array {
		// no id or matching id
		if (
			empty($id) ||
			$base['id'] == $id
		) {
			self::addSub($base, $attach);
			return $base;
		}
		// find id in 'id' in all 'sub'
		foreach ($base['sub'] as $el) {
			$el = self::ael($el, $attach, $id);
		}

		return $base;
	}

	/**
	 * Add multiple elements to the base element
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $base
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} ...$attach
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function aelx(
		array $base,
		array ...$attach
	): array {
		$base = self::addSub($base, ...$attach);
		return $base;
	}

	/**
	 * Add multiple sub elements to the base element
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $element
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $sub
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function addSub(array $element, array ...$sub): array
	{
		if (!isset($element['sub'])) {
			$element['sub'] = [];
		}
		array_push($element['sub'], ...$sub);
		return $element;
	}

	/**
	 * Remove all sub element entries
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $element
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function resetSub(array $element): array
	{
		$element['sub'] = [];
		return $element;
	}

	// CSS Elements

	/**
	 * Add css entry to the css entries
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $element
	 * @param  string ...$css
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function acssel(array $element, string ...$css): array
	{
		$element['css'] = array_unique(array_merge($element['css'] ?? [], $css));
		return $element;
	}

	/**
	 * Remove a css entry entry from the css array
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $element
	 * @param  string ...$css
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function rcssel(array $element, string ...$css): array
	{
		$element['css'] = array_diff($element['css'] ?? [], $css);
		return $element;
	}

	/**
	 * Switch CSS entries
	 * scssel (switch) is not supported
	 * use rcssel -> acssel
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $element
	 * @param  array<string> $rcss
	 * @param  array<string> $acss
	 * @return array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}
	 */
	public static function scssel(array $element, array $rcss, array $acss): array
	{
		return self::acssel(
			self::rcssel($element, ...$rcss),
			...$acss
		);
	}

	/**
	 * Build HTML from the content tree
	 * alias phfo
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $tree
	 * @param  bool         $add_nl [default=false]
	 * @return string
	 */
	public static function buildHtml(array $tree, bool $add_nl = false): string
	{
		if (empty($tree['tag'])) {
			return '';
		}
		// print "D01: " . microtime(true) . "<br>";
		$line = '<' . $tree['tag'];

		if (!empty($tree['id'])) {
			$line .= ' id="' . $tree['id'] . '"';
			if (in_array($tree['tag'], Settings::NAME_ELEMENTS)) {
				$line .= ' name="'
					. (!empty($tree['name']) ? $tree['name'] : $tree['id'])
					. '"';
			}
		}
		if (count($tree['css'])) {
			$line .= ' class="' . join(' ', $tree['css']) . '"';
		}
		foreach ($tree['options'] ?? [] as $key => $item) {
			if (in_array($key, Settings::SKIP_OPTIONS)) {
				continue;
			}
			$line .= ' ' . $key . '="' . $item . '"';
		}
		$line .= '>';
		if (!empty($tree['content'])) {
			$line .= $tree['content'];
		}
		// sub nodes
		foreach ($tree['sub'] ?? [] as $sub) {
			if ($add_nl === true) {
				$line .= "\n";
			}
			$line .= self::buildHtml($sub, $add_nl);
			if ($add_nl === true) {
				$line .= "\n";
			}
		}

		// close line if needed
		if (!in_array($tree['tag'], Settings::NO_CLOSE)) {
			$line .= '</' . $tree['tag'] . '>';
		}

		return $line;
	}

	/**
	 * Alias for phfo
	 *
	 * @param  array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>} $tree
	 * @param  bool         $add_nl [default=false]
	 * @return string
	 *
	 * @param  array  $tree
	 * @param  bool   $add_nl
	 * @return string
	 */
	public static function phfo(array $tree, bool $add_nl = false): string
	{
		return self::buildHtml($tree, $add_nl);
	}

	/**
	 * Build HTML elements from an array of elements
	 * alias phfa
	 *
	 * @param  array<array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}> $list
	 * @param  bool         $add_nl [default=false]
	 * @return string
	 */
	public static function buildHtmlFromList(array $list, bool $add_nl = false): string
	{
		$output = '';
		foreach ($list as $el) {
			$output .= self::buildHtml($el, $add_nl);
		}
		return $output;
	}

	/**
	 * alias for buildHtmlFromList
	 *
	 * @param  array<array{tag:string,id:string,name:string,content:string,css:array<string>,options:array<string,string>,sub:array<mixed>}> $list                   array of Elements to build string from
	 * @param  bool           $add_nl [default=false] Optional output string line break
	 * @return string         build html as string
	 */
	public static function printHtmlFromArray(array $list, bool $add_nl = false): string
	{
		return self::buildHtmlFromList($list, $add_nl);
	}
}

// __END__
