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

class StringReplace
{
	/** @var array<string,string> */
	private static array $elements = [];
	/** @var array<string,array<string,string>> */
	private static array $replace = [];

	/**
	 * load html blocks into array for repeated usage
	 * each array group parameter has 0: index, 1: content
	 * There is no content check done.
	 * index must be non empty (but has no fixed format)
	 * if same index is tried twice it will set an error and skip
	 *
	 * @param  array<string,string> ...$element Elements to load
	 * @return bool                             False if double index or other error
	 *                                          True on ok
	 */
	public static function loadElements(array ...$element): bool
	{
		$error = false;
		foreach ($element as $el) {
			$index = $el[0] ?? '';
			if (empty($index)) {
				$error = true;
				Error::setError(
					'310',
					'Index cannot be an empty string',
					[
						'element' => $index
					]
				);
			}
			if (isset(self::$elements[$index])) {
				$error = true;
				Error::setError(
					'311',
					'Index already exists',
					[
						'element' => $index
					]
				);
			}
			// content check?
			self::$elements[$index] = $el[1];
		}
		return $error;
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
			return;
		}
		// allow empty reset
		self::$elements[$index] = $element;
	}

	/**
	 * get an element block at index
	 * if not found will return false
	 *
	 * @param  string      $index
	 * @return string|false
	 */
	public static function getElement(string $index): string|bool
	{
		if (!isset(self::$elements[$index])) {
			Error::setError('321', 'Index not found in elements', ['element' => $index]);
			return false;
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
	 */
	public static function getReplaceBlock(string $index): string
	{
		if (!isset(self::$replace[$index])) {
			Error::setError('331', 'Index not found in replace block', ['replace' => $index]);
			return '';
		}
		return self::$replace[$index];
	}

	/**
	 * build and element on an index and either returns it or also sets it
	 * into the replace block array
	 * if index not found in relement list will return false
	 *
	 * @param  string        $index   index of set element
	 * @param  array<string> $replace array of text to search for
	 * @param  array<string> $content content data to be set for replace
	 * @return string|false
	 */
	public static function buildElement(
		string $index,
		array $replace,
		array $content,
		string $replace_index = ''
	): string|bool {
		if (self::getElement($index) === false) {
			return false;
		}
		if ($replace_index) {
			self::setReplaceBlock(
				$replace_index,
				self::replaceData(self::$elements[$index], $replace, $content)
			);
			return self::getReplaceBlock($replace_index);
		} else {
			return self::replaceData(self::$elements[$index], $replace, $content);
		}
	}

	/**
	 * main replace entries in text string
	 * elements to be replaced are in {} brackets. if they are missing in the
	 * replace array they will be added.
	 * if the replace and content count is not the same then an error will be thrown
	 *
	 * @param  string        $data
	 * @param  array<string> $replace
	 * @param  array<string> $content
	 * @return string|bool
	 */
	public static function replaceData(string $data, array $replace, array $content): string|bool
	{
		if (count($replace) != count($content)) {
			Error::setError('340', 'Replace and content count differ');
			return false;
		}
		// all replace data must have {} around
		array_walk($replace, function (&$entry) {
			if (!str_starts_with($entry, '{')) {
				$entry = '{' . $entry;
			}
			if (!str_ends_with($entry, '}')) {
				$entry .= '}';
			}
		});
		// replace content
		return str_replace($replace, $content, $data);
	}
}

// __END__
