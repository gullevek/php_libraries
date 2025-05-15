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
$LOG_FILE_ID = 'classTest-phpv';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$_phpv = new CoreLibs\Check\PhpVersion();
$phpv_class = 'CoreLibs\Check\PhpVersion';

$PAGE_NAME = 'TEST CLASS: PHP VERSION';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// fputcsv
print "<h3>\CoreLibs\DeprecatedHelper\Deprecated84::fputcsv()</h3>";
$test_csv = BASE . TMP . 'DeprecatedHelper.test.csv';
print "File: $test_csv<br>";

$fp = fopen($test_csv, "w");
if (!is_resource($fp)) {
	die("Cannot open file: $test_csv");
}
\CoreLibs\DeprecatedHelper\Deprecated84::fputcsv($fp, ["A", "B", "C"]);
fclose($fp);

$fp = fopen($test_csv, "r");
if (!is_resource($fp)) {
	die("Cannot open file: $test_csv");
}
while ($entry = \CoreLibs\DeprecatedHelper\Deprecated84::fgetcsv($fp)) {
	print "fgetcsv: <pre>" . print_r($entry, true) . "</pre>";
}
fclose($fp);

$out = \CoreLibs\DeprecatedHelper\Deprecated84::str_getcsv("A,B,C");
print "str_getcsv: <pre>" . print_r($out, true) . "</pre>";

/**
 * temporary different CSV function, because fgetcsv seems to be broken on some systems
 * (does not read out japanese text)
 *
 * @param  string $string    full line for csv split
 * @param  string $encoding  optional, if given, converts string to the internal encoding
 *                           before we do anything
 * @param  string $delimiter sepperate character, default ','
 * @param  string $enclosure string line marker, default '"'
 * @param  string $flag      INTERN | EXTERN. if INTERN uses the PHP function, else uses explode
 * @return array<int,string|null> array with split data from input line
 */
function mtParseCSV(
	string $string,
	string $encoding = '',
	string $delimiter = ',',
	string $enclosure = '"',
	string $flag = 'INTERN'
): array {
	$lines = [];
	if ($encoding) {
		$string = \CoreLibs\Convert\Encoding::convertEncoding(
			$string,
			'UTF-8',
			$encoding
		);
	}
	if ($flag == 'INTERN') {
		// split with PHP function
		$lines = str_getcsv($string, $delimiter, $enclosure);
	} else {
		// split up with delimiter
		$lines = explode(',', $string) ?: [];
	}
	// strip " from beginning and end of line
	for ($i = 0; $i < count($lines); $i++) {
		// remove line breaks
		$lines[$i] = preg_replace("/\r\n?/", '', (string)$lines[$i]) ?? '';
		// lingering " at the beginning and end of the line
		$lines[$i] = preg_replace("/^\"/", '', (string)$lines[$i]) ?? '';
		$lines[$i] = preg_replace("/\"$/", '', (string)$lines[$i]) ?? '';
	}
	return $lines;
}

print "</body></html>";

// __END__
