<?php

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
	 * Undocumented function
	 *
	 * @param  string $tag
	 * @param  string $id
	 * @param  string $content
	 * @param  array<string> $css,
	 * @param  array<string,string>  $options
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
	 * @param  array<mixed> $base
	 * @param  array<mixed> $attach
	 * @param  string  $id
	 * @return array<mixed>
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
	 * Undocumented function
	 *
	 * @param  array<mixed> $base
	 * @param  array<mixed> ...$attach
	 * @return array<mixed>
	 */
	public static function aelx(
		array $base,
		array ...$attach
	): array {
		$base = self::addSub($base, ...$attach);
		return $base;
	}

	/**
	 * Undocumented function
	 *
	 * @param  array<mixed> $element
	 * @param  array<mixed> $sub
	 * @return array<mixed>
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
	 * Undocumented function
	 *
	 * @param  array<mixed> $element
	 * @return array<mixed>
	 */
	public static function resetSub(array $elment): array
	{
		$element['sub'] = [];
		return $element;
	}

	// CSS Elements

	/**
	 * Undocumented function
	 *
	 * @param  array<mixed> $element
	 * @param  string ...$css
	 * @return array<mixed>
	 */
	public static function acssel(array $element, string ...$css): array
	{
		$element['css'] = array_unique(array_merge($element['css'] ?? [], $css));
		return $element;
	}

	/**
	 * Undocumented function
	 *
	 * @param  string ...$css
	 * @return array<mixed>
	 */
	public static function rcssel(array $element, string ...$css): array
	{
		$element['css'] = array_diff($element['css'] ?? [], $css);
		return $element;
	}

	/**
	 * Undocumented function
	 * scssel (switch) is not supported
	 * use rcssel -> acssel
	 *
	 * @param  array $element
	 * @param  array $rcss
	 * @param  array $acss
	 * @return array
	 */
	public static function scssel(array $element, array $rcss, array $acss): array
	{
		return self::acssel(
			self::rcssel($element, ...$rcss),
			...$acss
		);
	}

	/**
	 * Undocumented function
	 * alias phfo
	 *
	 * @param  array<mixed> $tree
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
	 * Undocumented function
	 * alias phfa
	 *
	 * @param  array<mixed> $list
	 * @return string
	 */
	public static function buildHtmlFromList(array $list): string
	{
		$output = '';
		foreach ($list as $el) {
			$output .= self::buildHtml($el);
		}
		return $output;
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
