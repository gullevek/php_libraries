<?php

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

// admin class tests
require 'config.inc' ;
DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);
$base = new CoreLibs\Basic();

// $test = array (
// 	'A' => array (
// 		'B' => array (),
// 		'C' => array (
// 			'D' => array (),
// 			'E' => array (
// 				'F' => array ()
// 			)
// 		)
// 	),
// 	'1' => array (),
// 	'2' => array (),
// 	'3' => array (
// 		'G' => array ()
// 	)
// );

$base->debug('ARRAY', $base->printAr($test));

function rec($pre, $cur, $node = array ())
{
	print "<div style='color: green;'>#### PRE: ".$pre.", CUR: ".$cur.", N-c: ".count($node)." [".join('|', array_keys($node))."]</div>";
	if (!$pre) {
		print "** <span style='color: red;'>NEW</span><br>";
		$node[$cur] = array ();
	} else {
		if (array_key_exists($pre, $node)) {
			print "+ <span style='color: orange;'>KEY FOUND:</span> ".$pre.", add: ".$cur."<br>";
			$node[$pre][$cur] = array ();
		} else {
			print "- NOT FOUND: loop<br>";
			foreach ($node as $_pre => $_cur) {
				print "> TRY: ".$_pre." => ".count($_cur)." [".join('|', array_keys($_cur))."]<br>";
				if (count($_cur) > 0) {
					$node[$_pre] = rec($pre, $cur, $_cur);
				}
			}
		}
	}
	return $node;
}

function flattenArrayKey(array $array, array $return = array ())
{
	foreach ($array as $key => $sub) {
		$return[] = $key;
		if (count($sub) > 0) {
			$return = flattenArrayKey($sub, $return);
		}
	}
	return $return;
}

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
$base->debug('REC', $base->printAr($test));
print "FLATTEN: ".$base->printAr(flattenArrayKey($test))."<br>";

print $base->printErrorMsg();

// __END__
