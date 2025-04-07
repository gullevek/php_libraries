<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/10/10
 * DESCRIPTION:
 * Convert placeholders in query from PDO style ? or :named to \PG style $number
 * pr the other way around
*/

declare(strict_types=1);

namespace CoreLibs\DB\Support;

class ConvertPlaceholder
{
	/** @var string text block in SQL, single quited
	 * Note that does not include $$..$$ strings or anything with token name or nested ones
	*/
	private const PATTERN_TEXT_BLOCK_SINGLE_QUOTE = '(?:\'(?:[^\'\\\\]|\\\\.)*\')';
	/** @var string text block in SQL, dollar quoted
	 * NOTE: if this is added everything shifts by one lookup number
	*/
	private const PATTERN_TEXT_BLOCK_DOLLAR = '(?:\$([^$]*)\$.*?\$\1\$)';
	/** @var string comment regex
	 * anything that starts with -- and ends with a line break but any character that is not line break inbetween
	 * this is the FIRST thing in the line and will skip any further lookups */
	private const PATTERN_COMMENT = '(?:\-\-[^\r\n]*?\r?\n)';
	// below are the params lookups
	/** @var string named parameters, must start with single : */
	private const PATTERN_NAMED = '((?<!:):(?:\w+))';
	/** @var string question mark parameters, will catch any */
	private const PATTERN_QUESTION_MARK = '(\?{1})';
	/** @var string numbered parameters, can only start 1 to 9, second and further digits can be 0-9
	 * This ignores the $$ ... $$ escape syntax. If we find something like this will fail
	 * It is recommended to use proper string escape quiting for writing data to the DB
	*/
	private const PATTERN_NUMBERED = '(\$[1-9]{1}(?:[0-9]{1,})?)';
	// below here are full regex that will be used
	/** @var string replace regex for named (:...) entries */
	public const REGEX_REPLACE_NAMED = '/'
		. self::PATTERN_COMMENT . '|'
		. self::PATTERN_TEXT_BLOCK_SINGLE_QUOTE . '|'
		. self::PATTERN_TEXT_BLOCK_DOLLAR . '|'
		. self::PATTERN_NAMED
		. '/s';
	/** @var string replace regex for question mark (?) entries */
	public const REGEX_REPLACE_QUESTION_MARK = '/'
		. self::PATTERN_COMMENT . '|'
		. self::PATTERN_TEXT_BLOCK_SINGLE_QUOTE . '|'
		. self::PATTERN_TEXT_BLOCK_DOLLAR . '|'
		. self::PATTERN_QUESTION_MARK
		. '/s';
	/** @var string replace regex for numbered ($n) entries */
	public const REGEX_REPLACE_NUMBERED = '/'
		. self::PATTERN_COMMENT . '|'
		. self::PATTERN_TEXT_BLOCK_SINGLE_QUOTE . '|'
		. self::PATTERN_TEXT_BLOCK_DOLLAR . '|'
		. self::PATTERN_NUMBERED
		. '/s';
	/** @var string the main lookup query for all placeholders */
	public const REGEX_LOOKUP_PLACEHOLDERS = '/'
		. self::PATTERN_COMMENT . '|'
		. self::PATTERN_TEXT_BLOCK_SINGLE_QUOTE . '|'
		. self::PATTERN_TEXT_BLOCK_DOLLAR . '|'
		// match for replace part
		. '(?:'
		// :name named part (PDO) [1]
		. self::PATTERN_NAMED . '|'
		// ? question mark part (PDO) [2]
		. self::PATTERN_QUESTION_MARK . '|'
		// $n numbered part (\PG php) [3]
		. self::PATTERN_NUMBERED
		// end match
		. ')'
		// single line -> add line break to matches in "."
		. '/s';
	/** @var string lookup for only numbered placeholders */
	public const REGEX_LOOKUP_NUMBERED = '/'
		. self::PATTERN_COMMENT . '|'
		. self::PATTERN_TEXT_BLOCK_SINGLE_QUOTE . '|'
		. self::PATTERN_TEXT_BLOCK_DOLLAR . '|'
		// match for replace part
		. '(?:'
		// $n numbered part (\PG php) [1]
		. self::PATTERN_NUMBERED
		// end match
		. ')'
		. '/s';
	/** @var int position for regex in full placeholder lookup: named */
	public const LOOOKUP_NAMED_POS = 2;
	/** @var int position for regex in full placeholder lookup: question mark */
	public const LOOOKUP_QUESTION_MARK_POS = 3;
	/** @var int position for regex in full placeholder lookup: numbered */
	public const LOOOKUP_NUMBERED_POS = 4;
	/** @var int matches position for replacement and single lookup */
	public const MATCHING_POS = 2;

	/**
	 * Convert PDO type query with placeholders to \PG style and vica versa
	 * For PDO to: ? and :named
	 * For \PG to: $number
	 *
	 * If the query has a mix of ?, :named or $numbrer the \OutOfRangeException exception
	 * will be thrown
	 *
	 * If the convert_to is either pg or pdo, nothing will be changed
	 *
	 * found has -1 if an error occoured in the preg_match_all call
	 *
	 * @param  string       $query      Query with placeholders to convert
	 * @param  ?array<mixed> $params     The parameters that are used for the query, and will be updated
	 * @param  string       $convert_to Either pdo or pg, will be converted to lower case for check
	 * @return array{original:array{query:string,params:array<mixed>,empty_params:bool},type:''|'named'|'numbered'|'question_mark',found:int,matches:array<string>,params_lookup:array<mixed>,query:string,params:array<mixed>}
	 * @throws \OutOfRangeException 200 If mixed placeholder types
	 * @throws \InvalidArgumentException 300 or 301 if wrong convert to with found placeholders
	 */
	public static function convertPlaceholderInQuery(
		string $query,
		?array $params,
		string $convert_to = 'pg'
	): array {
		$convert_to = strtolower($convert_to);
		$matches = [];
		// matches:
		// 1: :named
		// 2: ? question mark
		// 3: $n numbered
		$found = preg_match_all(self::REGEX_LOOKUP_PLACEHOLDERS, $query, $matches, PREG_UNMATCHED_AS_NULL);
		// if false or null set to -1
		//  || $found === null
		if ($found === false) {
			$found = -1;
		}
		/** @var array<string> 1: named */
		$named_matches = array_filter($matches[self::LOOOKUP_NAMED_POS]);
		/** @var array<string> 2: open ? */
		$qmark_matches = array_filter($matches[self::LOOOKUP_QUESTION_MARK_POS]);
		/** @var array<string> 3: $n matches */
		$numbered_matches = array_filter($matches[self::LOOOKUP_NUMBERED_POS]);
		// print "**MATCHES**: <pre>" . print_r($matches, true) . "</pre>";
		// count matches
		$count_named = count(array_unique($named_matches));
		$count_qmark = count($qmark_matches);
		$count_numbered = count(array_unique($numbered_matches));
		// throw exception if mixed found
		if (
			($count_named && $count_qmark) ||
			($count_named && $count_numbered) ||
			($count_qmark && $count_numbered)
		) {
			throw new \OutOfRangeException('Cannot have named, question mark and numbered in the same query', 200);
		}
		// // throw if invalid conversion
		// if (($count_named || $count_qmark) && $convert_to != 'pg') {
		// 	throw new \InvalidArgumentException('Cannot convert from named or question mark placeholders to PDO', 300);
		// }
		// if ($count_numbered && $convert_to != 'pdo') {
		// 	throw new \InvalidArgumentException('Cannot convert from numbered placeholders to Pg', 301);
		// }
		// return array
		$return_placeholders = [
			// original
			'original' => [
					'query' => $query,
					'params' => $params ?? [],
					'empty_params' => $params === null ? true : false,
				],
				// type found, empty if nothing was done
				'type' => '',
				// int: found, not found; -1: problem (set from false)
				'found' => (int)$found,
				'matches' => [],
				// old to new lookup check
				'params_lookup' => [],
				// this must match the count in params in new
				'needed' => 0,
				// new
				'query' => '',
				'params' => [],
		];
		// replace basic regex and name settings
		if ($count_named) {
			$return_placeholders['type'] = 'named';
			$return_placeholders['matches'] = $named_matches;
			$return_placeholders['needed'] = $count_named;
		} elseif ($count_qmark) {
			$return_placeholders['type'] = 'question_mark';
			$return_placeholders['matches'] = $qmark_matches;
			$return_placeholders['needed'] = $count_qmark;
			// for each ?:DTN: -> replace with $1 ... $n, any remaining :DTN: remove
		} elseif ($count_numbered) {
			$return_placeholders['type'] = 'numbered';
			$return_placeholders['matches'] = $numbered_matches;
			$return_placeholders['needed'] = $count_numbered;
		}
		// run convert only if matching type and direction
		if (
			(($count_named || $count_qmark) && $convert_to == 'pg') ||
			($count_numbered && $convert_to == 'pdo')
		) {
			$param_list = self::updateParamList($return_placeholders);
			$return_placeholders['params_lookup'] = $param_list['params_lookup'];
			$return_placeholders['query'] = $param_list['query'];
			$return_placeholders['params'] = $param_list['params'];
		}
		// return data
		return $return_placeholders;
	}

	/**
	 * Updates the params list from one style to the other to match the query output
	 * if original.empty_params is set to true, no params replacement is done
	 * if param replacement has been done in a dbPrepare then this has to be run
	 * with the return palceholders array with params in original filled and empty_params turned off
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param array{original:array{query:string,params:array<mixed>,empty_params:bool},type:''|'named'|'numbered'|'question_mark',found:int,matches?:array<string>,params_lookup?:array<mixed>,query?:string,params?:array<mixed>} $converted_placeholders
	 * phpcs:enable Generic.Files.LineLength
	 * @return array{params_lookup:array<mixed>,query:string,params:array<mixed>}
	 */
	public static function updateParamList(array $converted_placeholders): array
	{
		// skip if nothing set
		if (!$converted_placeholders['found']) {
			return [
				'params_lookup' => [],
				'query' => '',
				'params' => []
			];
		}
		$query_new = '';
		$params_new = [];
		$params_lookup = [];
		// set to null if params is empty
		$params = $converted_placeholders['original']['params'];
		$empty_params = $converted_placeholders['original']['empty_params'];
		switch ($converted_placeholders['type']) {
			case 'named':
				// 1: replace part :named
				$pos = 0;
				$query_new = preg_replace_callback(
					self::REGEX_REPLACE_NAMED,
					function ($matches) use (&$pos, &$params_new, &$params_lookup, $params, $empty_params) {
						if (!isset($matches[self::MATCHING_POS])) {
							throw new \RuntimeException(
								'Cannot lookup ' . self::MATCHING_POS . ' in matches list',
								209
							);
						}
						$match = $matches[self::MATCHING_POS];
						// only count up if $match[1] is not yet in lookup table
						if (empty($params_lookup[$match])) {
							$pos++;
							$params_lookup[$match] = '$' . $pos;
							// skip params setup if param list is empty
							if (!$empty_params) {
								$params_new[] = $params[$match] ??
									throw new \RuntimeException(
										'Cannot lookup ' . $match . ' in params list',
										210
									);
							}
						}
						// add the connectors back (1), and the data sets only if no replacement will be done
						return $params_lookup[$match] ??
							throw new \RuntimeException(
								'Cannot lookup ' . $match . ' in params lookup list',
								211
							);
					},
					$converted_placeholders['original']['query']
				);
				break;
			case 'question_mark':
				if (!$empty_params) {
					// order and data stays the same
					$params_new = $params ?? [];
				}
				// 1: replace part ?
				$pos = 0;
				$query_new = preg_replace_callback(
					self::REGEX_REPLACE_QUESTION_MARK,
					function ($matches) use (&$pos, &$params_lookup) {
						if (!isset($matches[self::MATCHING_POS])) {
							throw new \RuntimeException(
								'Cannot lookup ' . self::MATCHING_POS . ' in matches list',
								229
							);
						}
						$match = $matches[self::MATCHING_POS];
						// only count pos up for actual replacements we will do
						if (!empty($match)) {
							$pos++;
							$params_lookup[] = '$' . $pos;
						}
						// add the connectors back (1), and the data sets only if no replacement will be done
						return '$' . $pos;
					},
					$converted_placeholders['original']['query']
				);
				break;
			case 'numbered':
				// 1: replace part $numbered
				$pos = 0;
				$query_new = preg_replace_callback(
					self::REGEX_REPLACE_NUMBERED,
					function ($matches) use (&$pos, &$params_new, &$params_lookup, $params, $empty_params) {
						if (!isset($matches[self::MATCHING_POS])) {
							throw new \RuntimeException(
								'Cannot lookup ' . self::MATCHING_POS . ' in matches list',
								239
							);
						}
						$match = $matches[self::MATCHING_POS];
						// only count up if $match[1] is not yet in lookup table
						if (empty($params_lookup[$match])) {
							$pos++;
							$params_lookup[$match] = ':' . $pos . '_named';
							// skip params setup if param list is empty
							if (!$empty_params) {
								$params_new[] = $params[($pos - 1)] ??
									throw new \RuntimeException(
										'Cannot lookup ' . ($pos - 1) . ' in params list',
										230
									);
							}
						}
						// add the connectors back (1), and the data sets only if no replacement will be done
						return $params_lookup[$match]  ??
							throw new \RuntimeException(
								'Cannot lookup ' . $match . ' in params lookup list',
								231
							);
					},
					$converted_placeholders['original']['query']
				);
				break;
		}
		return [
			'params_lookup' => $params_lookup,
			'query' => $query_new ?? '',
			'params' => $params_new,
		];
	}
}

// __END__
