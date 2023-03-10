<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-check-colors';
ob_end_flush();

use CoreLibs\Check\Colors;
// use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);

$PAGE_NAME = 'TEST CLASS: CHECK COLORS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// list of colors to check
$css_colors = [
	// base hex
	'#ab12cd',
	'#ab12cd12',
	// rgb
	'rgb(255, 10, 20)',
	'rgb(100%, 10%, 20%)',
	'rgba(255, 10, 20)',
	'rgba(100%, 10%, 20%)',
	'rgba(255, 10, 20, 0.5)',
	'rgba(100%, 10%, 20%, 0.5)',
	'rgba(255, 10, 20, 50%)',
	'rgba(100%, 10%, 20%, 50%)',
	// hsl
	'hsl(100, 50%, 60%)',
	'hsl(100, 50.5%, 60.5%)',
	'hsla(100, 50%, 60%)',
	'hsla(100, 50.5%, 60.5%)',
	'hsla(100, 50%, 60%, 0.5)',
	'hsla(100, 50.5%, 60.5%, 0.5)',
	'hsla(100, 50%, 60%, 50%)',
	'hsla(100, 50.5%, 60.5%, 50%)',
	// invalid here
	'invalid string',
	'(hsla(100, 100, 100))',
	'hsla(100, 100, 100',
	// invalid numbers
	'#zzab99',
	'#abcdef0',
	'rgb(255%, 100, 100)',
	'rgb(255%, 100, -10)',
	'rgb(100%, 100, -10)',
	'hsl(370, 100, 10)',
	'hsl(200, 100%, 160%)',
];

foreach ($css_colors as $color) {
	$check = Colors::validateColor($color);
	print "Color check: $color with (" . Colors::ALL . "): ";
	if ($check) {
		print '<span style="color: green;">OK</span>';
	} else {
		print '<span style="color: red;">ERROR</span>';
	}
	print "<br>";
}

echo "<hr>";

// valid rgb/hsl checks
$color = 'hsla(360, 100%, 60%, 0.556)';
$check = Colors::validateColor($color);
print "Color check: $color with (" . Colors::ALL . "): ";
if ($check) {
	print '<span style="color: green;">OK</span>';
} else {
	print '<span style="color: red;">ERROR</span>';
}

// invalid flag
echo "<hr>";
try {
	$check = Colors::validateColor('#ab12cd', 99);
	print "No Exception";
} catch (\Exception $e) {
	print "ERROR: " . $e->getCode() . ": " . $e->getMessage() . "<br>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
