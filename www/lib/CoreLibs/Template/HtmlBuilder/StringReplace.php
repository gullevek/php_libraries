<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/6/23
 * DESCRIPTION:
 * Simeple string replace calls for elements
*/

declare(strict_types=1);

namespace CoreLibs\Template\HtmlBuilder;

use CoreLibs\Template\HtmlBuilder\General\Error;
use CoreLibs\Template\HtmlBuilder\General\HtmlBuilderExcpetion;

class StringReplace
{
	/** @var array<string,string> */
	private static array $elements = [];
	/** @var array<string,string> */
	private static array $replace = [];

	/**
	 * load html blocks into array for repeated usage
	 * each array group parameter has 0: index, 1: content
	 * There is no content check done.
	 * index must be non empty (but has no fixed format)
	 * if same index is tried twice it will set an error and skip
	 *
	 * @param  array<string,string> ...$element Elements to load
	 * @return void
	 * @throws HtmlBuilderExcpetion
	 */
	public static function loadElements(array ...$element): void
	{
		foreach ($element as $el) {
			$index = $el[0] ?? '';
			if (empty($index)) {
				Error::setError(
					'310',
					'Index cannot be an empty string',
					[
						'element' => $index
					]
				);
				throw new HtmlBuilderExcpetion('Index cannot be an empty string');
			}
			if (isset(self::$elements[$index])) {
				Error::setError(
					'311',
					'Index already exists',
					[
						'element' => $index
					]
				);
				throw new HtmlBuilderExcpetion('Index already exists: ' . $index);
			}
			// content check?
			self::$elements[$index] = $el[1];
		}
	}

	/**
	 * update an element at index
	 * can also be used to reset (empty string)
	 *
	 * @param  string $index
	 * @param  string $element
	 * @return void
	 */
	public static function updateElement(string $index, string $element): void
	{
		if (!isset(self::$elements[$index])) {
			Error::setError(
				'312',
				'Index does not exists',
				[
					'element' => $index
				]
			);
			throw new HtmlBuilderExcpetion('Index does not exists: ' . $index);
		}
		// allow empty reset
		self::$elements[$index] = $element;
	}

	/**
	 * get an element block at index
	 * if not found will return false
	 *
	 * @param  string      $index
	 * @return string
	 * @throws HtmlBuilderExcpetion
	 */
	public static function getElement(string $index): string
	{
		if (!isset(self::$elements[$index])) {
			Error::setError('321', 'Index not found in elements', ['element' => $index]);
			throw new HtmlBuilderExcpetion('Index not found in elements array: ' . $index);
		}
		return self::$elements[$index];
	}

	/**
	 * set a replacement block at index
	 * can be used for setting one block and using it agai
	 *
	 * @param  string $index
	 * @param  string $content
	 * @return void
	 */
	public static function setReplaceBlock(string $index, string $content): void
	{
		self::$replace[$index] = $content;
	}

	/**
	 * get replacement block at index, if not found return empty and set error
	 *
	 * @param  string $index
	 * @return string
	 * @throws HtmlBuilderExcpetion
	 */
	public static function getReplaceBlock(string $index): string
	{
		if (!isset(self::$replace[$index])) {
			Error::setError('331', 'Index not found in replace block', ['replace' => $index]);
			throw new HtmlBuilderExcpetion('Index not found in replace block array: ' . $index);
		}
		return self::$replace[$index];
	}

	/**
	 * build and element on an index and either returns it or also sets it
	 * into the replace block array
	 * if index not found in relement list will return false
	 *
	 * @param  string        $index   index of set element
	 * @param  array<string,string> $replace array of text to search (key) and replace (value) for
	 * @return string|false
	 */
	public static function buildElement(
		string $index,
		array $replace,
		string $replace_index = ''
	): string|bool {
		if (self::getElement($index) === false) {
			return false;
		}
		if ($replace_index) {
			self::setReplaceBlock(
				$replace_index,
				self::replaceData(
					self::$elements[$index],
					array_keys($replace),
					array_values($replace)
				)
			);
			return self::getReplaceBlock($replace_index);
		} else {
			return self::replaceData(
				self::$elements[$index],
				array_keys($replace),
				array_values($replace)
			);
		}
	}

	/**
	 * main replace entries in text string
	 * elements to be replaced are in {} brackets. if they are missing in the
	 * replace array they will be added.
	 * if the replace and content count is not the same then an error will be thrown
	 *
	 * @param  string               $data
	 * @param  array<string,string> $replace
	 * @return string
	 * @throws HtmlBuilderExcpetion
	 */
	public static function replaceData(string $data, array $replace): string
	{
		$replace = array_keys($replace);
		// all replace data must have {} around
		array_walk($replace, function (&$entry) {
			if (!str_starts_with($entry, '{')) {
				$entry = '{' . $entry;
			}
			if (!str_ends_with($entry, '}')) {
				$entry .= '}';
			}
			// do some validation?
		});
		// replace content
		return str_replace($replace, array_values($replace), $data);
	}
}

// __END__
