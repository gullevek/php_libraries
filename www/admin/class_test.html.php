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
$LOG_FILE_ID = 'classTest-html';
ob_end_flush();

use CoreLibs\Convert\Html;
use CoreLibs\Output\Form\Elements;

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
$_html = new CoreLibs\Convert\Html();
$_elements = new CoreLibs\Output\Form\Elements();
$html_class = 'CoreLibs\Convert\Html';
$elements_class = 'CoreLibs\Output\Form\Elements';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: HTML/ELEMENTS</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$string = "Something < = > Other <br> Next line";
print "HTMLENT: " . Html::htmlent($string) . ": " . $_html->htmlent($string) . "<br>";
print "REMOVELB: " . Html::htmlent($string) . ": " . $_html->removeLB($string) . "<br>";
$date_str = [2021, 5, 1, 11, 10];
print "PRINTDATETIME: "
	. $_elements->printDateTime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4]) . "<br>";
// STATIC
$string = "Something < = > Other <br> Next line";
print "S::HTMLENT: " . Html::htmlent($string) . ": " . $html_class::htmlent($string) . "<br>";
print "S::REMOVELB: " . Html::htmlent($string) . ": " . $html_class::removeLB($string) . "<br>";
$date_str = [2021, 5, 1, 11, 10];
print "S::PRINTDATETIME: "
	. $elements_class::printDateTime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4]) . "<br>";

// STATIC use
echo "U-S::HTML ENT INT: " . Html::htmlent(5) . "<br>";
echo "U-S::HTML ENT STRING: " . Html::htmlent('5<<>') . "<br>";
echo "U-S::HTML ENT NULL: " . Html::htmlent(null) . "<br>";

// check convert
$checked_list = [
	['foo', 'foo'],
	['foo', 'bar'],
	['foo', ['foo', 'bar']],
	['foo', ['bar']],
];
foreach ($checked_list as $check) {
	print "CHECKED(0): $check[0]: " . Html::checked($check[1], $check[0]) . "<br>";
	print "CHECKED(1): $check[0]: " . Html::checked($check[1], $check[0], Html::CHECKED) . "<br>";
}

// magic link creation test
$magic_links = [
	'mailto:user@bubu.at',
	'user@bubu.at',
	'user@bubu.at|Send me email|',
	'http://www.somelink.com/?with=1234',
	'http://www.somelink.com/?with=1234|Some Title|',
	'http://www.somelink.com/?with=1234 <br>Some Title',
];
foreach ($magic_links as $magic_link) {
	print "MAGICLINK: " . Html::htmlent($magic_link) . ": " . Html::htmlent(Elements::magicLinks($magic_link)) . "<br>";
}

// DEPREACTED
/* $string = "Deprecated Something < = > Other <br> Deprecated Next line";
print "D/HTMLENT: $string: ".$basic->htmlent($string)."<br>";
print "D/REMOVELB: $string: ".$basic->removeLB($string)."<br>";
$date_str = [2021, 5, 1, 11, 10];
print "D/PRINTDATETIME: "
	. $basic->printDateTime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4])."<br>";
$magic_link = 'http://www.somelink.com/?with=1234|Some Title|';
print "D/MAGICLINK: ".Html::htmlent($basic->magicLinks($magic_link))."<Br>";
*/

$text = 'I am some text
with some
line breaks
in there. Theis 
is sucky';

print "LB remove: " . \CoreLibs\Convert\Html::removeLB($text) . "<br>";
print "LB remove: " . \CoreLibs\Convert\Html::removeLB($text, '##BR##') . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
