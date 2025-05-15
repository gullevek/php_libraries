<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// turn on all error reporting
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-db-convert-placeholder';
ob_end_flush();

use CoreLibs\Debug\Support;
use CoreLibs\DB\Support\ConvertPlaceholder;
use CoreLibs\Convert\Html;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: DB CONVERT PLACEHOLDER';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "LOGFILE NAME: " . $log->getLogFile() . "<br>";
print "LOGFILE ID: " . $log->getLogFileId() . "<br>";

print "Lookup Regex: <pre>" . Html::htmlent(ConvertPlaceholder::REGEX_LOOKUP_PLACEHOLDERS) . "</pre>";
print "Lookup Numbered Regex: <pre>" . Html::htmlent(ConvertPlaceholder::REGEX_LOOKUP_NUMBERED) . "</pre>";
print "Replace Named Regex: <pre>" . Html::htmlent(ConvertPlaceholder::REGEX_REPLACE_NAMED) . "</pre>";
print "Replace Question Mark Regex: <pre>"
	. Html::htmlent(ConvertPlaceholder::REGEX_REPLACE_QUESTION_MARK) . "</pre>";
print "Replace Numbered Regex: <pre>" . Html::htmlent(ConvertPlaceholder::REGEX_REPLACE_NUMBERED) . "</pre>";

$uniqid = \CoreLibs\Create\Uids::uniqIdShort();
// $binary_data = $db->dbEscapeBytea(file_get_contents('class_test.db.php') ?: '');
// $binary_data = file_get_contents('class_test.db.php') ?: '';
$binary_data = '';
$params = [
	$uniqid,
	true,
	'STRING A',
	2,
	2.5,
	1,
	date('H:m:s'),
	date('Y-m-d H:i:s'),
	json_encode(['a' => 'string', 'b' => 1, 'c' => 1.5, 'f' => true, 'g' => ['a', 1, 1.5]]),
	null,
	'{"a", "b"}',
	'{1,2}',
	'{"(array Text A, 5, 8.8)","(array Text B, 10, 15.2)"}',
	'("Text", 4, 6.3)',
	$binary_data
];

$query = <<<SQL
INSERT INTO test_foo (
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_int_1,
	array_composite,
	composite_item,
	some_binary
) VALUES (
	$1, $2, $3, $4, $5, $6,
	$7, $8, $9, $10,
	$11, $12,
	$13,
	$14,
	$15
)
RETURNING
	test_foo_id,
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_int_1,
	array_composite,
	composite_item,
	some_binary
SQL;

print "<b>[ALL] Convert</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = "SELECT foo FROM bar WHERE baz = :baz AND buz = :baz AND biz = :biz AND boz = :bez";
$params = [':baz' => 'SETBAZ', ':bez' => 'SETBEZ', ':biz' => 'SETBIZ'];
print "<b>[NO PARAMS] Convert</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = "SELECT foo FROM bar WHERE baz = :baz AND buz = :baz AND biz = :biz AND boz = :bez";
$params = null;
print "<b>[NO PARAMS] Convert</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = "SELECT row_varchar FROM table_with_primary_key WHERE row_varchar <> :row_varchar";
$params = null;
print "<b>[NO PARAMS] Convert</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = "SELECT row_varchar, row_varchar_literal, row_int, row_date FROM table_with_primary_key";
$params = null;
print "<b>[NO PARAMS] TEST</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = <<<SQL
UPDATE table_with_primary_key SET
	row_int = $1::INT, row_numeric = $1::NUMERIC, row_varchar = $1
WHERE
	row_varchar = $1
SQL;
$params = [1];
print "<b>[All the same params] TEST</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

$query = <<<SQL
SELECT row_varchar, row_varchar_literal, row_int, row_date
FROM table_with_primary_key
WHERE row_varchar = :row_varchar
SQL;
$params = [':row_varchar' => 1];
print "<b>[: param] TEST</b>: "
	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params))
	. "<br>";
echo "<hr>";

print "<b>[P-CONV]</b>: "
	. Support::printAr(
		ConvertPlaceholder::updateParamList([
			'original' => [
				'query' => 'SELECT foo FROM bar WHERE baz = :baz AND buz = :biz AND biz = :biz AND boz = :bez',
				'params' => [':baz' => 'SETBAZ', ':bez' => 'SETBEZ', ':biz' => 'SETBIZ'],
				'empty_params' => false,
			],
			'type' => 'named',
			'found' => 3,
			// 'matches' => [
			// 	':baz'
			// ],
			// 'params_lookup' => [
			// 	':baz' => '$1'
			// ],
			// 'query' => "SELECT foo FROM bar WHERE baz = $1",
			// 'parms' => [
			// 	'SETBAZ'
			// ],
		])
	);

echo "<hr>";

// test connectors: = , <> () for query detection

// convert placeholder tests
// ? -> $n
// :name -> $n

// other way around (just visual)
$test_queries = [
	'skip' => [
		'query' => <<<SQL
SELECT test, string_a, number_a
FROM test_foo
SQL,
		'params' => [],
		'direction' => 'pg',
	],
	'numbers' => [
		'query' => <<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE
	foo = $1 AND bar = $1 AND foobar = $2
SQL,
		'params' => [\CoreLibs\Create\Uids::uniqIdShort(), 'string A-1', 1234],
		'direction' => 'pdo',
	],
	'a?' => [
		'query' => <<<SQL
INSERT INTO test_foo (
	test, string_a, number_a
) VALUES (
	?, ?, ?
)
SQL,
		'params' => [\CoreLibs\Create\Uids::uniqIdShort(), 'string A-1', 1234],
		'direction' => 'pg',
	],
	'b?' => [
		'query' => <<<SQL
SELECT test FROM test_foo = ?
SQL,
		'params' => [1234],
		'direction' => 'pg',
	],
	'b:' => [
		'query' => <<<SQL
INSERT INTO test_foo (
	test, string_a, number_a
) VALUES (
	:test, :string_a, :number_a
)
SQL,
		'params' => [
			':test' => \CoreLibs\Create\Uids::uniqIdShort(),
			':string_a' => 'string B-1',
			':number_a' => 5678
		],
		'direction' => 'pg',
	],
	'select, compare $' => [
		'query' => <<<SQL
		SELECT row_varchar
		FROM table_with_primary_key
		WHERE
			row_int >= $1 OR row_int <= $2 OR
			row_int > $3 OR row_int < $4
			OR row_int = $5 OR row_int <> $6
		SQL,
		'params' => null,
		'direction' => 'pg'
	]
];


foreach ($test_queries as $info => $data) {
	$query = $data['query'];
	$params = $data['params'];
	$direction = $data['direction'];
	print "<b>[$info] Convert</b>: "
		. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params, $direction))
		. "<br>";
	echo "<hr>";
}

print "</body></html>";
$log->debug('DEBUGEND', '==================================== [END]');

// __END__
