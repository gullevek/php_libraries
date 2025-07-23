<?php

/*
 * array search and transform functions
 */

declare(strict_types=1);

namespace CoreLibs\Combined;

class ArrayHandler
{
	public const string DATA_SEPARATOR = ':';

	/**
	 * searches key = value in an array / array
	 * only returns the first one found
	 *
	 * @param  string|int   $needle         needle (search for)
	 * @param  array<mixed> $haystack       haystack (search in)
	 * @param  string|null  $key_search_for the key to look out for, default empty
	 * @return array<mixed>                 array with the elements where
	 *                                      the needle can be found in the
	 *                                      haystack array
	 */
	public static function arraySearchRecursive(
		string|int $needle,
		array $haystack,
		?string $key_search_for = null
	): array {
		$path = [];
		if (
			$key_search_for != null &&
			array_key_exists($key_search_for, $haystack) &&
			$needle === $haystack[$key_search_for]
		) {
			$path[] = $key_search_for;
		} else {
			foreach ($haystack as $key => $val) {
				if (
					is_scalar($val) &&
					$val === $needle &&
					empty($key_search_for)
				) {
					$path[] = $key;
					break;
				} elseif (
					is_scalar($val) &&
					!empty($key_search_for) &&
					$key === $key_search_for &&
					$val === $needle
				) {
					$path[] = $key;
					break;
				} elseif (
					is_array($val) &&
					$path = self::arraySearchRecursive(
						$needle,
						(array)$val,
						// to avoid PhanTypeMismatchArgumentNullable
						($key_search_for === null ? $key_search_for : (string)$key_search_for)
					)
				) {
					array_unshift($path, $key);
					break;
				}
			}
		}
		return $path;
	}

	/**
	 * recursive array search function, which returns all found not only the first one
	 *
	 * @param  string|int        $needle         needle (search for)
	 * @param  array<mixed>      $haystack       haystack (search in)
	 * @param  string|int|null   $key_search_for the key to look for in
	 * @param  bool              $old            [true], if set to false will
	 *                                           return new flat layout
	 * @param  array<mixed>|null $path           recursive call for previous path
	 * @return array<mixed>|null                 all array elements paths where
	 *                                           the element was found
	 */
	public static function arraySearchRecursiveAll(
		string|int $needle,
		array $haystack,
		string|int|null $key_search_for,
		bool $old = true,
		?array $path = null
	): ?array {
		// init if not set on null
		if ($path === null) {
			$path = [
				'level' => 0,
				'work' => []
			];
		} else {
			// init sub sets if not set
			if (!isset($path['level'])) {
				$path['level'] = 0;
			}
			if (!isset($path['work'])) {
				$path['work'] = [];
			}
		}

		// go through the array,
		foreach ($haystack as $_key => $_value) {
			if (
				is_scalar($_value) &&
				$_value === $needle &&
				empty($key_search_for)
			) {
				// only value matches
				$path['work'][$path['level'] ?? 0] = $_key;
				$path['found'][] = $path['work'];
			} elseif (
				is_scalar($_value) &&
				!empty($key_search_for) &&
				$_key === $key_search_for &&
				$_value === $needle
			) {
				// key and value matches
				$path['work'][$path['level'] ?? 0] = $_key;
				$path['found'][] = $path['work'];
			} elseif (is_array($_value)) {
				// add position to working
				$path['work'][$path['level'] ?? 0] = $_key;
				// we will up a level
				$path['level'] += 1;
				// call recursive
				$path = self::arraySearchRecursiveAll($needle, $_value, $key_search_for, $old, $path);
			}
		}
		// be 100% sure the array elements are set
		$path['level'] = $path['level'] ?? 0;
		$path['work'] = $path['work'] ?? [];
		// cut all that is >= level
		array_splice($path['work'], $path['level']);
		// step back a level
		$path['level'] -= 1;
		if ($old === false && $path['level'] == -1) {
			return $path['found'] ?? [];
		} else {
			return $path;
		}
	}

	/**
	 * array search simple. looks for key, value combination, if found, returns true
	 * on default does not strict check, so string '4' will match int 4 and vica versa
	 *
	 * @param  array<mixed>           $in_array search in as array
	 * @param  string|int             $key   key (key to search in)
	 * @param  string|int|bool|array<string|int|bool> $value  values list (what to find)
	 * @param  bool                   $strict [false], if set to true, will strict check key/value
	 * @return bool                   true on found, false on not found
	 */
	public static function arraySearchSimple(
		array $in_array,
		string|int $key,
		string|int|bool|array $value,
		bool $strict = false
	): bool {
		// convert to array
		if (!is_array($value)) {
			$value = [$value];
		}
		foreach ($in_array as $_key => $_value) {
			// if value is an array, we search
			if (is_array($_value)) {
				// call recursive, and return result if it is true, else continue
				if (($result = self::arraySearchSimple($_value, $key, $value, $strict)) !== false) {
					return $result;
				}
			} elseif ($strict === false && $_key == $key && in_array($_value, $value)) {
				return true;
			} elseif ($strict === true && $_key === $key && in_array($_value, $value, true)) {
				return true;
			}
		}
		// no true returned, not found
		return false;
	}

	/**
	 * search for one or many keys in array and return matching values
	 * If flat is set to true, return flat array with found values only
	 * If prefix is turned on each found group will be prefixed with the
	 * search key
	 *
	 * @param  array<mixed> $in_array   array to search in
	 * @param  array<mixed> $needles keys to find in array
	 * @param  bool         $flat    [false] Turn on flat output
	 * @param  bool         $prefix  [false] Prefix found with needle key
	 * @return array<mixed>          Found values
	 */
	public static function arraySearchKey(
		array $in_array,
		array $needles,
		bool $flat = false,
		bool $prefix = false
	): array {
		$iterator  = new \RecursiveArrayIterator($in_array);
		$recursive = new \RecursiveIteratorIterator(
			$iterator,
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$hit_list = [];
		if ($prefix === true) {
			$hit_list = array_fill_keys($needles, []);
		}
		$key_path = [];
		$prev_depth = 0;
		foreach ($recursive as $key => $value) {
			if ($prev_depth > $recursive->getDepth()) {
				// remove all trailing to ne depth
				$diff = $prev_depth - $recursive->getDepth();
				array_splice($key_path, -$diff, $diff);
			}
			$prev_depth = $recursive->getDepth();
			if ($flat === false) {
				$key_path[$recursive->getDepth()] = $key;
			}
			if (in_array($key, $needles, true)) {
				ksort($key_path);
				if ($flat === true) {
					$hit = $value;
				} else {
					$hit = [
						'value' => $value,
						'path' => $key_path
					];
				}
				if ($prefix === true) {
					$hit_list[$key][] = $hit;
				} else {
					$hit_list[] = $hit;
				}
			}
		}
		return $hit_list;
	}

	/**
	 * Search in an array for value with or without key and
	 * check in the same array block for the required key
	 * If not found return an array with the array block there the required key is missing,
	 * the path as string with seperator block set and the missing key entry
	 *
	 * @param  array<mixed>          $in_array
	 * @param  string|int|float|bool $search_value
	 * @param  string|array<string>  $required_key
	 * @param  ?string               $search_key [null]
	 * @param  string                $path_separator [DATA_SEPARATOR]
	 * @param  string                $current_path
	 * @return array<array{content?:array<mixed>,path?:string,missing_key?:array<string>}>
	 */
	public static function findArraysMissingKey(
		array $in_array,
		string|int|float|bool $search_value,
		string|array $required_key,
		?string $search_key = null,
		string $path_separator = self::DATA_SEPARATOR,
		string $current_path = ''
	): array {
		$results = [];
		foreach ($in_array as $key => $value) {
			$path = $current_path ? $current_path . $path_separator . $key : $key;

			if (is_array($value)) {
				// Check if this array contains the search value
				// either any value match or with key
				if ($search_key === null) {
					$containsValue = in_array($search_value, $value, true);
				} else {
					$containsValue = array_key_exists($search_key, $value) && $value[$search_key] === $search_value;
				}

				// If it contains the value but doesn't have the required key
				if (
					$containsValue &&
					(
						(
							is_string($required_key) &&
							!array_key_exists($required_key, $value)
						) || (
							is_array($required_key) &&
							count(array_intersect($required_key, array_keys($value))) !== count($required_key)
						)
					)
				) {
					$results[] = [
						'content' => $value,
						'path' => $path,
						'missing_key' => is_array($required_key) ?
							array_values(array_diff($required_key, array_keys($value))) :
							[$required_key]
					];
				}

				// Recursively search nested arrays
				$results = array_merge(
					$results,
					self::findArraysMissingKey(
						$value,
						$search_value,
						$required_key,
						$search_key,
						$path_separator,
						$path
					)
				);
			}
		}

		return $results;
	}

	/**
	 * Find key => value entry and return set with key for all matching
	 * Can search recursively through nested arrays if recursive flag is set
	 *
	 * @param  array<mixed> $in_array
	 * @param  string $lookup
	 * @param  int|string|float|bool $search
	 * @param  bool $strict [false]
	 * @param  bool $case_insensitive [false]
	 * @param  bool $recursive [false]
	 * @param  bool $flat_result [true] If set to false and recursive is on the result is a nested array
	 * @param  string $flat_separator [DATA_SEPARATOR] if flat result is true, can be any string
	 * @return array<mixed>
	 */
	public static function selectArrayFromOption(
		array $in_array,
		string $lookup,
		int|string|float|bool $search,
		bool $strict = false,
		bool $case_insensitive = false,
		bool $recursive = false,
		bool $flat_result = true,
		string $flat_separator = self::DATA_SEPARATOR
	): array {
		// skip on empty
		if ($in_array == []) {
			return [];
		}
		// init return result
		$result = [];
		// case sensitive convert if string
		if ($case_insensitive && is_string($search)) {
			$search = strtolower($search);
		}

		foreach ($in_array as $key => $value) {
			// Handle current level search
			if (isset($value[$lookup])) {
				$compareValue = $value[$lookup];

				if ($case_insensitive && is_string($compareValue)) {
					$compareValue = strtolower($compareValue);
				}

				if (
					($strict && $search === $compareValue) ||
					(!$strict && $search == $compareValue)
				) {
					$result[$key] = $value;
				}
			}
			// Handle recursive search if flag is set
			if ($recursive && is_array($value)) {
				$recursiveResults = self::selectArrayFromOption(
					$value,
					$lookup,
					$search,
					$strict,
					$case_insensitive,
					true,
					$flat_result,
					$flat_separator
				);

				// Merge recursive results with current results
				// Preserve keys by using array_merge with string keys or + operator
				foreach ($recursiveResults as $recKey => $recValue) {
					if ($flat_result) {
						$result[$key . $flat_separator . $recKey] = $recValue;
					} else {
						$result[$key][$recKey] = $recValue;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * main wrapper function for next/prev key
	 *
	 * @param  array<mixed>    $in_array array to search in
	 * @param  int|string      $key   key for next/prev
	 * @param  bool            $next  [=true] if to search next or prev
	 * @return int|string|null        Next/prev key or null for end/first
	 */
	private static function arrayGetKey(array $in_array, int|string $key, bool $next = true): int|string|null
	{
		$keys = array_keys($in_array);
		if (($position = array_search($key, $keys, true)) === false) {
			return null;
		}
		$next_position = $next ? $position + 1 : $position - 1;

		if (!isset($keys[$next_position])) {
			return null;
		}
		return $keys[$next_position];
	}

	/**
	 * Get previous array key from an array
	 * null on not found
	 *
	 * @param  array<mixed>    $in_array
	 * @param  int|string      $key
	 * @return int|string|null        Next key, or null for not found
	 */
	public static function arrayGetPrevKey(array $in_array, int|string $key): int|string|null
	{
		return self::arrayGetKey($in_array, $key, false);
	}

	/**
	 * Get next array key from an array
	 * null on not found
	 *
	 * @param  array<mixed>    $in_array
	 * @param  int|string      $key
	 * @return int|string|null        Next key, or null for not found
	 */
	public static function arrayGetNextKey(array $in_array, int|string $key): int|string|null
	{
		return self::arrayGetKey($in_array, $key, true);
	}

	/**
	 * correctly recursive merges as an array as array_merge_recursive
	 * just glues things together
	 *         array first array to merge
	 *         array second array to merge
	 *         ...   etc
	 *         bool  key flag: true: handle keys as string or int
	 *               default false: all keys are string
	 *
	 * @return array<mixed> merged array
	 */
	public static function arrayMergeRecursive(): array
	{
		// croak on not enough arguemnts (we need at least two)
		if (func_num_args() < 2) {
			throw new \ArgumentCountError(__FUNCTION__ . ' needs two or more array arguments');
		}
		// default key is not string
		$key_is_string = false;
		$in_arrays = func_get_args();
		// if last is not array, then assume it is trigger for key is always string
		if (!is_array(end($in_arrays))) {
			if (array_pop($in_arrays)) {
				$key_is_string = true;
			}
		}
		// check that arrays count is at least two, else we don't have enough to do anything
		if (count($in_arrays) < 2) {
			throw new \ArgumentCountError(__FUNCTION__ . ' needs two or more array arguments');
		}
		$merged = [];
		while ($in_arrays) {
			$in_array = array_shift($in_arrays);
			if (!is_array($in_array)) {
				throw new \TypeError(__FUNCTION__ . ' encountered a non array argument');
			}
			if (!$in_array) {
				continue;
			}
			foreach ($in_array as $key => $value) {
				// if string or if key is assumed to be string do key match
				// else add new entry
				if (is_string($key) || $key_is_string === false) {
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
						// $merged[$key] = call_user_func(__METHOD__, $merged[$key], $value, $key_is_string);
						$merged[$key] = self::arrayMergeRecursive($merged[$key], $value, $key_is_string);
					} else {
						$merged[$key] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}
		return $merged;
	}

	/**
	 * correct array_diff that does an actualy difference between two arrays.
	 * array_diff only checks elements from A that are not in B, but not the
	 * other way around.
	 * Note that like array_diff this only checks first level values not keys
	 *
	 * @param  array<mixed>  $a array to compare a
	 * @param  array<mixed>  $b array to compare b
	 * @return array<mixed>     array with missing elements from a & b
	 */
	public static function arrayDiff(array $a, array $b): array
	{
		$intersect = array_intersect($a, $b);
		return array_merge(array_diff($a, $intersect), array_diff($b, $intersect));
	}

	/**
	 * search for the needle array elements in haystack and
	 * return the ones found as an array,
	 * is there nothing found, it returns FALSE (boolean)
	 *
	 * @param  array<mixed> $needle   elements to search for
	 * @param  array<mixed> $haystack array where the $needle elements should
	 *                                be searched int
	 * @return array<mixed>|false     either the found elements or
	 *                                false for nothing found or error
	 */
	public static function inArrayAny(array $needle, array $haystack): array|false
	{
		$found = [];
		foreach ($needle as $element) {
			if (in_array($element, $haystack)) {
				$found[] = $element;
			}
		}
		if (count($found) == 0) {
			return false;
		} else {
			return $found;
		}
	}

	/**
	 * creates out of a normal db_return array an assoc array
	 *
	 * @param  array<mixed>    $db_array return array from the database
	 * @param  string|int|bool $key      key set, false for not set
	 * @param  string|int|bool $value    value set, false for not set
	 * @param  bool            $set_only flag to return all (default), or set only
	 * @return array<mixed>              associative array
	 */
	public static function genAssocArray(
		array $db_array,
		string|int|bool $key,
		string|int|bool $value,
		bool $set_only = false
	): array {
		$ret_array = [];
		// do this to only run count once
		for ($i = 0, $iMax = count($db_array); $i < $iMax; $i++) {
			// if no key then we make an order reference
			if (
				$key !== false &&
				$value !== false &&
				(($set_only && !empty($db_array[$i][$value])) ||
				(!$set_only && isset($db_array[$i][$value]))) &&
				!empty($db_array[$i][$key])
			) {
				$ret_array[$db_array[$i][$key]] = $db_array[$i][$value];
			} elseif (
				$key === false && $value !== false &&
				isset($db_array[$i][$value])
			) {
				$ret_array[] = $db_array[$i][$value];
			} elseif (
				$key !== false && $value === false &&
				!empty($db_array[$i][$key])
			) {
				$ret_array[$db_array[$i][$key]] = $i;
			}
		}
		return $ret_array;
	}

	/**
	 * converts multi dimensional array to a flat array
	 * does NOT preserve keys
	 *
	 * @param  array<mixed> $in_array multi dimensionial array
	 * @return array<mixed>        flattened array
	 */
	public static function flattenArray(array $in_array): array
	{
		$return = [];
		array_walk_recursive(
			$in_array,
			function ($value) use (&$return) {
				$return[] = $value;
			}
		);
		return $return;
	}

	/**
	 * will loop through an array recursivly and write the array keys back
	 *
	 * @param  array<mixed> $in_array  multidemnsional array to flatten
	 * @param  array<mixed> $return recoursive pass on array of keys
	 * @return array<mixed>         flattened keys array
	 */
	public static function flattenArrayKey(array $in_array, array $return = []): array
	{
		foreach ($in_array as $key => $sub) {
			$return[] = $key;
			if (is_array($sub) && count($sub) > 0) {
				$return = self::flattenArrayKey($sub, $return);
			}
		}
		return $return;
	}

	/**
	 * as above will flatten an array, but in this case only the outmost
	 * leave nodes, all other keyswill be skipped
	 *
	 * @param  array<mixed> $in_array multidemnsional array to flatten
	 * @return array<mixed>        flattened keys array
	 */
	public static function flattenArrayKeyLeavesOnly(array $in_array): array
	{
		$return = [];
		array_walk_recursive(
			$in_array,
			function ($value, $key) use (&$return) {
				$return[] = $key;
			}
		);
		return $return;
	}

	/**
	 * searches for key -> value in an array tree and writes the value one level up
	 * this will remove this leaf will all other values
	 *
	 * @param  array<mixed> $in_array  nested array
	 * @param  string|int   $search key to find that has no sub leaf
	 *                              and will be pushed up
	 * @return array<mixed>         modified, flattened array
	 */
	public static function arrayFlatForKey(array $in_array, string|int $search): array
	{
		foreach ($in_array as $key => $value) {
			// if it is not an array do just nothing
			if (!is_array($value)) {
				continue;
			}
			// probe it has search key
			if (isset($value[$search])) {
				// set as current
				$in_array[$key] = $value[$search];
			} else {
				// call up next node down
				// $in_array[$key] = call_user_func(__METHOD__, $value, $search);
				$in_array[$key] = self::arrayFlatForKey($value, $search);
			}
		}
		return $in_array;
	}

	/**
	 * Remove entries from a simple array, will not keep key order
	 *
	 * any array content is allowed
	 *
	 * https://stackoverflow.com/a/369608
	 *
	 * @param  array<mixed> $in_array  Array where elements are located
	 * @param  array<mixed> $remove Elements to remove
	 * @return array<mixed>         Array with $remove elements removed
	 */
	public static function arrayRemoveEntry(array $in_array, array $remove): array
	{
		return array_diff($in_array, $remove);
	}

	/**
	 * From the array with key -> mixed values,
	 * return only the entries where the key matches the key given in the key list parameter
	 *
	 * key list is a list[string]
	 * if key list is empty, return array as is
	 *
	 * @param  array<string,mixed> $in_array
	 * @param  array<string>       $key_list
	 * @return array<string,mixed>
	 */
	public static function arrayReturnMatchingKeyOnly(
		array $in_array,
		array $key_list
	): array {
		// on empty return as is
		if (empty($key_list)) {
			return $in_array;
		}
		return array_filter(
			$in_array,
			fn($key) => in_array($key, $key_list),
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Modifieds the key of an array with a prefix and/or suffix and
	 * returns it with the original value
	 * does not change order in array
	 *
	 * @param  array<string|int,mixed> $in_array
	 * @param  string                  $key_mod_prefix [''] key prefix string
	 * @param  string                  $key_mod_suffix [''] key suffix string
	 * @return array<string|int,mixed>
	 */
	public static function arrayModifyKey(
		array $in_array,
		string $key_mod_prefix = '',
		string $key_mod_suffix = ''
	): array {
		// skip if array is empty or neither prefix or suffix are set
		if (
			$in_array == [] ||
			($key_mod_prefix == '' && $key_mod_suffix == '')
		) {
			return $in_array;
		}
		return array_combine(
			array_map(
				fn($key) => $key_mod_prefix . $key . $key_mod_suffix,
				array_keys($in_array)
			),
			array_values($in_array)
		);
	}

	/**
	 * sort array and return in same call
	 * sort ascending or descending with or without lower case convert
	 * value only, will loose key connections unless preserve_keys is set to true
	 *
	 * @param  array<mixed> $in_array                    Array to sort by values
	 * @param  bool         $case_insensitive [false] Sort case insensitive
	 * @param  bool         $reverse [false]          Reverse sort
	 * @param  bool         $maintain_keys [false]    Maintain keys
	 * @param  int          $flag [SORT_REGULAR]      Sort flags
	 * @return array<mixed>
	 */
	public static function sortArray(
		array $in_array,
		bool $case_insensitive = false,
		bool $reverse = false,
		bool $maintain_keys = false,
		int $flag = SORT_REGULAR
	): array {
		$fk_sort_lower_case = function (string $a, string $b): int {
			return strtolower($a) <=> strtolower($b);
		};
		$fk_sort_lower_case_reverse = function (string $a, string $b): int {
			return strtolower($b) <=> strtolower($a);
		};
		$case_insensitive ? (
			$maintain_keys ?
				(uasort($in_array, $reverse ? $fk_sort_lower_case_reverse : $fk_sort_lower_case)) :
				(usort($in_array, $reverse ? $fk_sort_lower_case_reverse : $fk_sort_lower_case))
		) :
		(
			$maintain_keys ?
				($reverse ? arsort($in_array, $flag) : asort($in_array, $flag)) :
				($reverse ? rsort($in_array, $flag) : sort($in_array, $flag))
		);
		return $in_array;
	}

	/**
	 * sort by key ascending or descending and return
	 *
	 * @param  array<mixed> $in_array                    Array to srt
	 * @param  bool         $case_insensitive [false] Sort keys case insenstive
	 * @param  bool         $reverse [false]          Reverse key sort
	 * @return array<mixed>
	 */
	public static function ksortArray(array $in_array, bool $case_insensitive = false, bool $reverse = false): array
	{
		$fk_sort_lower_case = function (string $a, string $b): int {
			return strtolower($a) <=> strtolower($b);
		};
		$fk_sort_lower_case_reverse = function (string $a, string $b): int {
			return strtolower($b) <=> strtolower($a);
		};
		$fk_sort = function (string $a, string $b): int {
			return $a <=> $b;
		};
		$fk_sort_reverse = function (string $a, string $b): int {
			return $b <=> $a;
		};
		uksort(
			$in_array,
			$case_insensitive ?
				($reverse ? $fk_sort_lower_case_reverse : $fk_sort_lower_case) :
				($reverse ? $fk_sort_reverse : $fk_sort)
		);
		return $in_array;
	}
}

// __END__
