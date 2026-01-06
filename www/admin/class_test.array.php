<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-array';
ob_end_flush();

use CoreLibs\Combined\ArrayHandler;
use CoreLibs\Debug\Support as DgS;
use CoreLibs\Convert\SetVarType;
// use PHPUnit\Framework\Constraint\ArrayHasKey;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// $_array = new CoreLibs\Combined\ArrayHandler();
// $array_class = 'CoreLibs\Combination\ArrayHandler';

$PAGE_NAME = 'TEST CLASS: ARRAY HANDLER';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// recursive array search
$test_array = [
	'foo' => 'bar',
	'input' => [
		'element_a' => [
			'type' => 'text'
		],
		'element_b' => [
			'type' => 'email'
		],
		'element_c' => [
			'type' => 'email'
		],
		'element_d' => [
			'type' => 'butter'
		],
	],
];

echo "SOURCE ARRAY: " . DgS::printAr($test_array) . "<br>";
// frist return
echo "ARRAYSEARCHRECURSIVE(email, [array], type): "
	. DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array, 'type')) . "<br>";
echo "ARRAYSEARCHRECURSIVE(email, [array]['input'], type): "
	. DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array['input'], 'type')) . "<br>";
echo "ARRAYSEARCHRECURSIVE(email, [array]['input'], wrong): "
	. DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array['input'], 'wrong')) . "<br>";
// all return
echo "ARRAYSEARCHRECURSIVEALL(email, [array], type): "
	. Dgs::printAr((array)ArrayHandler::arraySearchRecursiveAll('email', $test_array, 'type')) . "<br>";
	echo "ARRAYSEARCHRECURSIVEALL(email, [array], type, false): "
	. Dgs::printAr((array)ArrayHandler::arraySearchRecursiveAll('email', $test_array, 'type', false)) . "<br>";

// simple search
echo "ARRAYSEARCHSIMPLE([array], type, email): "
	. Dgs::prBl(ArrayHandler::arraySearchSimple($test_array, 'type', 'email')) . "<br>";
echo "ARRAYSEARCHSIMPLE([array], type, not): "
	. Dgs::prBl(ArrayHandler::arraySearchSimple($test_array, 'type', 'not')) . "<br>";
echo "ARRAYSEARCHSIMPLE([array], type, [email,butter]): "
	. Dgs::prBl(ArrayHandler::arraySearchSimple($test_array, 'type', ['email', 'butter'])) . "<br>";
echo "ARRAYSEARCHSIMPLE([array], type, [email,not]): "
	. Dgs::prBl(ArrayHandler::arraySearchSimple($test_array, 'type', ['email', 'not'])) . "<br>";
echo "ARRAYSEARCHSIMPLE([array], type, [never,not]): "
	. Dgs::prBl(ArrayHandler::arraySearchSimple($test_array, 'type', ['never', 'not'])) . "<br>";

$array_1 = [
	'foo' => 'bar'
];
$array_2 = [
	1, 2, 3
];
$array_3 = [
	'alpha' => [
		'beta' => 4
	]
];
// recusrice merge
print "ARRAYMERGERECURSIVE: " . DgS::printAr(SetVarType::setArray(
	ArrayHandler::arrayMergeRecursive($array_1, $array_2, $array_3)
)) . "<br>";
// array difference
$array_left = [
	'same' => 'data',
	'left' => 'Has L'
];
$array_right = [
	'same' => 'data',
	'right' => 'has R'
];
print "ARRAYDIFF: " . DgS::printAr(ArrayHandler::arrayDiff($array_left, $array_right)) . "<br>";
// in array check
print "INARRAYANY([1,3], [array]): " . DgS::printAr(SetVarType::setArray(
	ArrayHandler::inArrayAny([1, 3], $array_2)
)) . "<br>";
// flatten array
print "FLATTENARRAY: " . DgS::printAr(ArrayHandler::flattenArray($test_array)) . "<br>";
print "FLATTENARRAYKEY: " . DgS::printAr(ArrayHandler::flattenArrayKey($test_array)) . "<br>";
// flatten for key set
print "ARRAYFLATFORKEY: " . DgS::printAr(ArrayHandler::arrayFlatForKey($test_array, 'type')) . "<br>";

/**
 * attach key/value to an array so it becomes nested
 *
 * @param string       $pre  Attach to new (empty for new root node)
 * @param string       $cur  New node
 * @param array<mixed> $node Previous created array
 * @return array<mixed>      Updated array
 */
function rec(string $pre, string $cur, array $node = [])
{
	print "<div style='color: green;'>#### PRE: " . $pre . ", CUR: " . $cur . ", N-c: "
		. count($node) . " [" . join('|', array_keys($node)) . "]</div>";
	if (!$pre) {
		print "** <span style='color: red;'>NEW</span><br>";
		$node[$cur] = [];
	} else {
		if (array_key_exists($pre, $node)) {
			print "+ <span style='color: orange;'>KEY FOUND:</span> " . $pre . ", add: " . $cur . "<br>";
			$node[$pre][$cur] = [];
		} else {
			print "- NOT FOUND: loop<br>";
			foreach ($node as $_pre => $_cur) {
				print "> TRY: " . $_pre . " => " . count($_cur) . " [" . join('|', array_keys($_cur)) . "]<br>";
				if (count($_cur) > 0) {
					$node[$_pre] = rec($pre, $cur, $_cur);
				}
			}
		}
	}
	return $node;
}

$data = [
	'image' => 'foo',
	'element' => 'w-1',
	'rotate' => 360,
	'html' => [
		'image' => 'bar',
		'result_image' => 'baz',
		'rule' => 'wrong'
	],
	[
		'image' => 'large'
	],
	[
		'nothing' => 'wrong'
	],
	'nest' => [
		'nust' => [
			'nist' =>  [
				'foo' => 'bar',
				'image' => 'long, long'
			]
		]
	],
	's' => [
		'image' => 'path?'
	],
];

$search = ['image', 'result_image', 'nothing', 'EMPTY'];
$result = ArrayHandler::arraySearchKey($data, $search);
print "ARRAYSEARCHKEY: Search: " . DgS::printAr($search) . ", Found: " . DgS::printAr($result) . "<br>";
$result = ArrayHandler::arraySearchKey($data, $search, true);
print "ARRAYSEARCHKEY: FLAT: Search: " . DgS::printAr($search) . ", Found: " . DgS::printAr($result) . "<br>";
$result = ArrayHandler::arraySearchKey($data, $search, true, true);
print "ARRAYSEARCHKEY: FLAT:PREFIX: Search: " . DgS::printAr($search) . ", Found: " . DgS::printAr($result) . "<br>";
$result = ArrayHandler::arraySearchKey($data, ["EMPTY"], true);
print "ARRAYSEARCHKEY: FLAT:PREFIX: Search: " . DgS::printAr(["EMPTY"]) . ", Found: " . DgS::printAr($result) . "<br>";

// $data = [
// 	[
// 		[name] => qrc_apcd,
// 		[value] => 5834367225,
// 	],
// 	[
// 		[name] => qrc_other,
// 		[value] => test,
// 	],
// 	[
// 		[name] => qrc_car_type,
// 		[value] => T33P17,
// 	],
// 	[
// 		[name] => qrc_deaer_store,
// 		[value] => 9990:001,
// 	]
// ]

// $test = [
// 	'A' => [
// 		'B' => [],
// 		'C' => [
// 			'D' => [],
// 			'E' => [
// 				'F' => []
// 			]
// 		]
// 	],
// 	'1' => [],
// 	'2' => [],
// 	'3' => [
// 		'G' => []
// 	]
// ];

// build a tested array for flatten
$test = [];
// core
$test = rec('', 'A', $test);
$test = rec('', '1', $test);
$test = rec('', '2', $test);
$test = rec('', '3', $test);
$test = rec('3', 'G', $test);
$test = rec('A', 'B', $test);
$test = rec('A', 'C', $test);
$test = rec('C', 'D', $test);
$test = rec('C', 'E', $test);
$test = rec('E', 'F', $test);
// new
$test = rec('C', 'U', $test);
$test = rec('F', 'U', $test);
$test = rec('', 'Al', $test);
$test = rec('B', 'B1', $test);
print "ORIGINAL: " . \CoreLibs\Debug\Support::printAr($test) . "<br>";
print "FLATTEN-c: " . \CoreLibs\Debug\Support::printAr(ArrayHandler::flattenArrayKey($test)) . "<br>";

$test = [
	'a' => ['a1' => 'a1foo', 'a2' => 'a1bar'],
	1 => 'bar',
	'c' => [2, 3, 4],
	'd' => [
		'e' => [
			'de1' => 'subfoo', 'de2' => 'subbar', 'a2' => 'a1bar'
		]
	]
];
print "ORIGINAL: " . \CoreLibs\Debug\Support::printAr($test) . "<br>";
print "FLATTEN: " . \CoreLibs\Debug\Support::printAr(ArrayHandler::flattenArrayKey($test)) . "<br>";

// genAssocArray
$db_array = [
	0 => ['a' => 'a1', 'b' => 2],
	1 => ['a' => 'a2', 'b' => 3],
	2 => ['a' => '', 'b' => ''],
];
// $key = false;
$key = 'a';
// $value = false;
$value = 'b';
$flag = false;
$output = \CoreLibs\Combined\ArrayHandler::genAssocArray($db_array, $key, $value, $flag);
print "OUTPUT: " . \CoreLibs\Debug\Support::printAr($output) . "<br>";


print "<hr>";
$array = [
	'a' => 'First',
	'b' => 'Second',
	'c' => 'Third',
];

foreach (array_keys($array) as $search) {
	print "Result[" . $search . "]: "
		. "next: " . DgS::printAr(ArrayHandler::arrayGetNextKey($array, $search)) . ", "
		. "prev: " . DgS::printAr(ArrayHandler::arrayGetPrevKey($array, $search))
		. "<br>";
}
print "Key not exists: " . DgS::printAr(ArrayHandler::arrayGetNextKey($array, 'z')) . "<br>";

print "<hr>";
$keys = ['b', 'c', 'f'];
print "Return only: " . DgS::printAr($keys) . ": "
	. DgS::printAr(ArrayHandler::arrayReturnMatchingKeyOnly($array, $keys)) . "<br>";

$out = array_filter($array, fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
print "array filter: " . DgS::printAr($keys) . ": " . DgS::printAr($out) . "<br>";
$out = array_intersect_key(
	$array,
	array_flip($keys)
);
print "array intersect key: " . DgS::printAr($keys) . ": " . DgS::printAr($out) . "<br>";

print "array + suffix: " . DgS::printAr(ArrayHandler::arrayModifyKey($array, key_mod_suffix:'_attached')) . "<br>";

print "<hr>";
$unsorted = [9, 5, 'A', 4, 'B', 6, 'c', 'C', 'a'];
$unsorted_keys = [
	'A' => 9, 'B' => 5, 'C' => 'A', 'D' => 4, 'E' => 'B', 'F' => 6, 'G' => 'c',
	'H1' => 'D', 'B1' => 'd', 'H' => 'C', 'I' => 'a'
];
print "Unsorted: " . DgS::printAr($unsorted) . "<br>";
print "(sort): " . DgS::printAr(ArrayHandler::sortArray($unsorted)) . "<br>";
print "(sort, lower): " . DgS::printAr(ArrayHandler::sortArray($unsorted, case_insensitive:true)) . "<br>";
print "(sort, reverse): " . DgS::printAr(ArrayHandler::sortArray($unsorted, reverse:true)) . "<br>";
print "(sort, lower, reverse): "
	. DgS::printAr(ArrayHandler::sortArray($unsorted, case_insensitive:true, reverse:true)) . "<br>";
print "(sort, keys): " . DgS::printAr(ArrayHandler::sortArray($unsorted_keys, maintain_keys:true)) . "<br>";
print "(sort, keys, lower): "
	. DgS::printAr(ArrayHandler::sortArray($unsorted_keys, maintain_keys:true, case_insensitive:true)) . "<br>";

print "<hr>";
$unsorted = [9 => 'A', 5 => 'B', 'A' => 'C', 4 => 'D', 'B' => 'E', 6 => 'F', 'c' => 'G', 'C' => 'H', 'a' => 'I'];
print "Unsorted Keys: " . DgS::printAr($unsorted) . "<br>";
print "(sort): " . DgS::printAr(ArrayHandler::sortArray($unsorted)) . "<br>";
print "(sort, keys): " . DgS::printAr(ArrayHandler::sortArray($unsorted, maintain_keys:true)) . "<br>";
print "(kosrt): " . DgS::printAr(ArrayHandler::ksortArray($unsorted)) . "<br>";
print "(kosrt, reverse): " . DgS::printAr(ArrayHandler::ksortArray($unsorted, reverse:true)) . "<br>";
print "(kosrt, lower case, reverse): "
	. DgS::printAr(ArrayHandler::ksortArray($unsorted, case_insensitive:true, reverse:true)) . "<br>";


print "<hr>";
$nested = [
	'B' => 'foo', 'a', '0', 9, /** @phpstan-ignore-line This is a test for wrong index */
	'1' => ['z', 'b', 'a'],
	'd' => ['zaip', 'bar', 'baz']
];
print "Nested: " . DgS::printAr($nested) . "<br>";
print "(sort): " . DgS::printAr(ArrayHandler::sortArray($nested)) . "<br>";
print "(ksort): " . DgS::printAr(ArrayHandler::ksortArray($nested)) . "<br>";

print "<hr>";

$search_array = [
	'table_lookup' => [
		'match' => [
			['param' => 'access_d_cd', 'data' => 'a_cd', 'time_validation' => 'on_load',],
			['param' => 'other_block', 'data' => 'b_cd'],
			['pflaume' => 'other_block', 'data' => 'c_cd'],
			['param' => 'third_block', 'data' => 'd_cd', 'time_validation' => 'cool'],
			['special' => 'other_block', 'data' => 'e_cd', 'time_validation' => 'other'],
		]
	]
];
print "Search: " . DgS::printAr($search_array) . "<br>";
print "Result (all): " . Dgs::printAr(ArrayHandler::findArraysMissingKey(
	$search_array,
	'other_block',
	'time_validation'
)) . "<br>";
print "Result (key): " . Dgs::printAr(ArrayHandler::findArraysMissingKey(
	$search_array,
	'other_block',
	'time_validation',
	'pflaume'
)) . "<br>";
print "Result (key): " . Dgs::printAr(ArrayHandler::findArraysMissingKey(
	$search_array,
	'other_block',
	['data', 'time_validation'],
	'pflaume'
)) . "<br>";

print "<hr>";

$search_array = [
	'a' => [
		'lookup' => 1,
		'value' => 'Foo',
		'other' => 'Bar',
	],
	'b' => [
		'lookup' => 1,
		'value' => 'AAA',
		'other' => 'Other',
	],
	'c' => [
		'lookup' => 0,
		'value' => 'CCC',
		'other' => 'OTHER',
	],
	'd' => [
		'd-1' => [
			'lookup' => 1,
			'value' => 'D SUB 1',
			'other' => 'Other B',
		],
		'd-2' => [
			'lookup' => 0,
			'value' => 'D SUB 2',
			'other' => 'Other C',
		],
		'more' => [
			'lookup' => 1,
			'd-more-1' => [
				'lookup' => 1,
				'value' => 'D MORE SUB 1',
				'other' => 'Other C',
			],
			'd-more-2' => [
				'lookup' => 0,
				'value' => 'D MORE SUB 0',
				'other' => 'Other C',
			],
		]
	]
];

print "Search: " . DgS::printAr($search_array) . "<br>";
print "Result: " . DgS::printAr(ArrayHandler::selectArrayFromOption(
	$search_array,
	'lookup',
	1,
)) . "<br>";
print "Result: " . DgS::printAr(ArrayHandler::selectArrayFromOption(
	$search_array,
	'lookup',
	1,
	recursive:true
)) . "<br>";
print "Result: " . DgS::printAr(ArrayHandler::selectArrayFromOption(
	$search_array,
	'lookup',
	1,
	recursive:true,
	flat_separator:'-=-'
)) . "<br>";
print "Result: " . DgS::printAr(ArrayHandler::selectArrayFromOption(
	$search_array,
	'lookup',
	1,
	recursive:true,
	flat_result:false
)) . "<br>";
print "Result: " . DgS::printAr(ArrayHandler::selectArrayFromOption(
	$search_array,
	'other',
	'Other',
	case_insensitive:false,
)) . "<br>";

$nestedTestData = [
	'level1_a' => [
		'name' => 'Level1A',
		'type' => 'parent',
		'children' => [
			'child1' => [
				'name' => 'Child1',
				'type' => 'child',
				'active' => true
			],
			'child2' => [
				'name' => 'Child2',
				'type' => 'child',
				'active' => false
			]
		]
	],
	'level1_b' => [
		'name' => 'Level1B',
		'type' => 'parent',
		'children' => [
			'child3' => [
				'name' => 'Child3',
				'type' => 'child',
				'active' => true,
				'nested' => [
					'deep1' => [
						'name' => 'Deep1',
						'type' => 'deep',
						'active' => true
					]
				]
			]
		]
	],
	'item5' => [
		'name' => 'Direct',
		'type' => 'child',
		'active' => false
	]
];

$result = ArrayHandler::selectArrayFromOption(
	$nestedTestData,
	'type',
	'child',
	false,
	false,
	true,
	true,
	':*'
);
print "*1*Result: " . DgS::printAr($result) . "<br>";
$data = [
	'parent1' => [
		'name' => 'Parent1',
		'status' => 'ACTIVE',
		'children' => [
			'child1' => [
				'name' => 'Child1',
				'status' => 'active'
			]
		]
	]
];

$result = ArrayHandler::selectArrayFromOption(
	$data,
	'status',
	'active',
	false,      // not strict
	true,       // case insensitive
	true,       // recursive
	true,       // flat result
	'|'         // custom separator
);
print "*2*Result: " . DgS::printAr($result) . "<br>";

print "</body></html>";

// __END__
