<?php

/*
 * array search and transform functions
 */

declare(strict_types=1);

namespace CoreLibs\Combined;

class ArrayHandler
{
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
	 * @param  array<mixed>    $array search in as array
	 * @param  string|int      $key    key (key to search in)
	 * @param  string|int|bool $value  value (what to find)
	 * @param  bool            $strict [false], if set to true, will strict check key/value
	 * @return bool            true on found, false on not found
	 */
	public static function arraySearchSimple(
		array $array,
		string|int $key,
		string|int|bool $value,
		bool $strict = false
	): bool {
		foreach ($array as $_key => $_value) {
			// if value is an array, we search
			if (is_array($_value)) {
				// call recursive, and return result if it is true, else continue
				if (($result = self::arraySearchSimple($_value, $key, $value, $strict)) !== false) {
					return $result;
				}
			} elseif ($strict === false && $_key == $key && $_value == $value) {
				return true;
			} elseif ($strict === true && $_key === $key && $_value === $value) {
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
	 * @param  array<mixed> $array   array to search in
	 * @param  array<mixed> $needles keys to find in array
	 * @param  bool         $flat    [false] Turn on flat output
	 * @param  bool         $prefix  [false] Prefix found with needle key
	 * @return array<mixed>          Found values
	 */
	public static function arraySearchKey(
		array $array,
		array $needles,
		bool $flat = false,
		bool $prefix = false
	): array {
		$iterator  = new \RecursiveArrayIterator($array);
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
		$arrays = func_get_args();
		// if last is not array, then assume it is trigger for key is always string
		if (!is_array(end($arrays))) {
			if (array_pop($arrays)) {
				$key_is_string = true;
			}
		}
		// check that arrays count is at least two, else we don't have enough to do anything
		if (count($arrays) < 2) {
			throw new \ArgumentCountError(__FUNCTION__ . ' needs two or more array arguments');
		}
		$merged = [];
		while ($arrays) {
			$array = array_shift($arrays);
			if (!is_array($array)) {
				throw new \TypeError(__FUNCTION__ . ' encountered a non array argument');
			}
			if (!$array) {
				continue;
			}
			foreach ($array as $key => $value) {
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
	 * @param  array<mixed> $array multi dimensionial array
	 * @return array<mixed>        flattened array
	 */
	public static function flattenArray(array $array): array
	{
		$return = [];
		array_walk_recursive(
			$array,
			function ($value) use (&$return) {
				$return[] = $value;
			}
		);
		return $return;
	}

	/**
	 * will loop through an array recursivly and write the array keys back
	 *
	 * @param  array<mixed> $array  multidemnsional array to flatten
	 * @param  array<mixed> $return recoursive pass on array of keys
	 * @return array<mixed>         flattened keys array
	 */
	public static function flattenArrayKey(array $array, array $return = []): array
	{
		foreach ($array as $key => $sub) {
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
	 * @param  array<mixed> $array multidemnsional array to flatten
	 * @return array<mixed>        flattened keys array
	 */
	public static function flattenArrayKeyLeavesOnly(array $array): array
	{
		$return = [];
		array_walk_recursive(
			$array,
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
	 * @param  array<mixed> $array  nested array
	 * @param  string|int   $search key to find that has no sub leaf
	 *                              and will be pushed up
	 * @return array<mixed>         modified, flattened array
	 */
	public static function arrayFlatForKey(array $array, string|int $search): array
	{
		foreach ($array as $key => $value) {
			// if it is not an array do just nothing
			if (!is_array($value)) {
				continue;
			}
			// probe it has search key
			if (isset($value[$search])) {
				// set as current
				$array[$key] = $value[$search];
			} else {
				// call up next node down
				// $array[$key] = call_user_func(__METHOD__, $value, $search);
				$array[$key] = self::arrayFlatForKey($value, $search);
			}
		}
		return $array;
	}
}

// __END__
