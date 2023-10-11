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
	 * @param  string       $query      Query with placeholders to convert
	 * @param  array<mixed> $params     The parameters that are used for the query, and will be updated
	 * @param  string       $convert_to Either pdo or pg, will be converted to lower case for check
	 * @return array{original:array{query:string,params:array<mixed>},type:''|'named'|'numbered'|'question_mark',found:int|false,matches:array<string>,params_lookup:array<mixed>,query:string,params:array<mixed>}
	 * @throws \OutOfRangeException 200
	 */
	public static function convertPlaceholderInQuery(
		string $query,
		array $params,
		string $convert_to = 'pg'
	): array {
		$convert_to = strtolower($convert_to);
		$matches = [];
		$pattern = '/'
			// prefix string part, must match towards
			. '(?:\'.*?\')?\s*(?:\?\?|[(=,])\s*'
			// match for replace part
			. '(?:'
			// digit -> ignore
			. '\d+|'
			// other string -> ignore
			. '(?:\'.*?\')|'
			// :name named part (PDO)
			. '(:\w+)|'
			// ? question mark part (PDO)
			. '(?:(?:\?\?)?\s*(\?{1}))|'
			// $n numbered part (\PG php)
			. '(\$[1-9]{1}(?:[0-9]{1,})?)'
			// end match
			. ')'
			// single line -> add line break to matches in "."
			. '/s';
		// matches:
		// 1: :named
		// 2: ? question mark
		// 3: $n numbered
		$found = preg_match_all($pattern, $query, $matches, PREG_UNMATCHED_AS_NULL);
		/** @var array<string> 1: named */
		$named_matches = array_filter($matches[1]);
		/** @var array<string> 2: open ? */
		$qmark_matches = array_filter($matches[2]);
		/** @var array<string> 3: $n matches */
		$numbered_matches = array_filter($matches[3]);
		// throw if mixed
		if (count($named_matches) && count($qmark_matches) && count($numbered_matches)) {
			throw new \OutOfRangeException('Cannot have named, question mark and numbered in the same query', 200);
		}
		// rebuild
		$matches_return = [];
		$type = '';
		$query_new = '';
		$params_new = [];
		$params_lookup = [];
		if (count($named_matches) && $convert_to == 'pg') {
			$type = 'named';
			$matches_return = $named_matches;
			// only check for :named
			$pattern_replace = '/((?:\'.*?\')?\s*(?:\?\?|[(=,])\s*)(\d+|(?:\'.*?\')|(:\w+))/s';
			// 0: full
			// 1: pre part
			// 2: keep part UNLESS '3' is set
			// 3: replace part :named
			$pos = 0;
			$query_new = preg_replace_callback(
				$pattern_replace,
				function ($matches) use (&$pos, &$params_new, &$params_lookup, $params) {
					// only count up if $match[3] is not yet in lookup table
					if (!empty($matches[3]) && empty($params_lookup[$matches[3]])) {
						$pos++;
						$params_lookup[$matches[3]] = '$' . $pos;
						$params_new[] = $params[$matches[3]] ??
							throw new \RuntimeException(
								'Cannot lookup ' . $matches[3] . ' in params list',
								210
							);
					}
					// add the connectors back (1), and the data sets only if no replacement will be done
					return $matches[1] . (
						empty($matches[3]) ?
							$matches[2] :
							$params_lookup[$matches[3]] ??
								throw new \RuntimeException(
									'Cannot lookup ' . $matches[3] . ' in params lookup list',
									211
								)
					);
				},
				$query
			);
		} elseif (count($qmark_matches) && $convert_to == 'pg') {
			$type = 'question_mark';
			$matches_return = $qmark_matches;
			// order and data stays the same
			$params_new = $params;
			// only check for ?
			$pattern_replace = '/((?:\'.*?\')?\s*(?:\?\?|[(=,])\s*)(\d+|(?:\'.*?\')|(?:(?:\?\?)?\s*(\?{1})))/s';
			// 0: full
			// 1: pre part
			// 2: keep part UNLESS '3' is set
			// 3: replace part ?
			$pos = 0;
			$query_new = preg_replace_callback(
				$pattern_replace,
				function ($matches) use (&$pos, &$params_lookup) {
					// only count pos up for actual replacements we will do
					if (!empty($matches[3])) {
						$pos++;
						$params_lookup[] = '$' . $pos;
					}
					// add the connectors back (1), and the data sets only if no replacement will be done
					return $matches[1] . (
						empty($matches[3]) ?
							$matches[2] :
							'$' . $pos
					);
				},
				$query
			);
			// for each ?:DTN: -> replace with $1 ... $n, any remaining :DTN: remove
		} elseif (count($numbered_matches) && $convert_to == 'pdo') {
			// convert numbered to named
			$type = 'numbered';
			$matches_return = $numbered_matches;
			// only check for $n
			$pattern_replace = '/((?:\'.*?\')?\s*(?:\?\?|[(=,])\s*)(\d+|(?:\'.*?\')|(\$[1-9]{1}(?:[0-9]{1,})?))/s';
			// 0: full
			// 1: pre part
			// 2: keep part UNLESS '3' is set
			// 3: replace part $numbered
			$pos = 0;
			$query_new = preg_replace_callback(
				$pattern_replace,
				function ($matches) use (&$pos, &$params_new, &$params_lookup, $params) {
					// only count up if $match[3] is not yet in lookup table
					if (!empty($matches[3]) && empty($params_lookup[$matches[3]])) {
						$pos++;
						$params_lookup[$matches[3]] = ':' . $pos . '_named';
						$params_new[] = $params[($pos - 1)] ??
							throw new \RuntimeException(
								'Cannot lookup ' . ($pos - 1) . ' in params list',
								220
							);
					}
					// add the connectors back (1), and the data sets only if no replacement will be done
					return $matches[1] . (
						empty($matches[3]) ?
							$matches[2] :
							$params_lookup[$matches[3]] ??
							throw new \RuntimeException(
								'Cannot lookup ' . $matches[3] . ' in params lookup list',
								221
							)
					);
				},
				$query
			);
		}
		// return, old query is always set
		return [
			// original
			'original' => [
				'query' => $query,
				'params' => $params,
			],
			// type found, empty if nothing was done
			'type' => $type,
			// int|null: found, not found; false: problem
			'found' => $found,
			'matches' => $matches_return,
			// old to new lookup check
			'params_lookup' => $params_lookup,
			// new
			'query' => $query_new ?? '',
			'params' => $params_new,
		];
	}
}

// __END__
