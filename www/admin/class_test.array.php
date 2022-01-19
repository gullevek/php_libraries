<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-array';
ob_end_flush();

use CoreLibs\Combined\ArrayHandler;
use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$basic = new CoreLibs\Basic($log);
// $_array = new CoreLibs\Combined\ArrayHandler();
// $array_class = 'CoreLibs\Combination\ArrayHandler';

print "<html><head><title>TEST CLASS: ARRAY HANDLER</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

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
	],
];

echo "SOURCE ARRAY: " . DgS::printAr($test_array) . "<br>";
// frist return
echo "ARRAYSEARCHRECURSIVE(email, [array], type): "
	. DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array, 'type')) . "<br>";
echo "ARRAYSEARCHRECURSIVE(email, [array]['input'], type): "
	. DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array['input'], 'type')) . "<br>";
// all return
echo "ARRAYSEARCHRECURSIVEALL(email, [array], type): "
	. Dgs::printAr((array)ArrayHandler::arraySearchRecursiveAll('email', $test_array, 'type')) . "<br>";
	echo "ARRAYSEARCHRECURSIVEALL(email, [array], type, false): "
	. Dgs::printAr((array)ArrayHandler::arraySearchRecursiveAll('email', $test_array, 'type', false)) . "<br>";

// simple search
echo "ARRAYSEARCHSIMPLE([array], type, email): "
	. (string)ArrayHandler::arraySearchSimple($test_array, 'type', 'email') . "<br>";

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
print "ARRAYMERGERECURSIVE: " . DgS::printAr(ArrayHandler::arrayMergeRecursive($array_1, $array_2, $array_3)) . "<br>";
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
print "INARRAYANY([1,3], [array]): " . DgS::printAr(ArrayHandler::inArrayAny([1, 3], $array_2)) . "<br>";
// flatten array
print "FLATTENARRAY: " . DgS::printAr(ArrayHandler::flattenArray($test_array)) . "<br>";
print "FLATTENARRAYKEY: " . DgS::printAr(ArrayHandler::flattenArrayKey($test_array)) . "<br>";
// flatten for key set
print "ARRAYFLATFORKEY: " . DgS::printAr(ArrayHandler::arrayFlatForKey($test_array, 'type')) . "<br>";

// DEPRECATED
// print "ARRAYMERGERECURSIVE: ".DgS::printAr($basic->arrayMergeRecursive($array_1, $array_2, $array_3))."<br>";

/**
 * attach key/value to an array so it becomes nested
 *
 * @param string $pre  Attach to new (empty for new root node)
 * @param string $cur  New node
 * @param array  $node Previous created array
 * @return array       Updated array
 */
function rec(string $pre, string $cur, array $node = [])
{
	if (!is_array($node)) {
		$node = [];
	}
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

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__