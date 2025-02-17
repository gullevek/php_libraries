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
$LOG_FILE_ID = 'classTest-html';
ob_end_flush();

use CoreLibs\Convert\Html;
use CoreLibs\Output\Form\Elements;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$_html = new CoreLibs\Convert\Html();
$_elements = new CoreLibs\Output\Form\Elements();
$html_class = 'CoreLibs\Convert\Html';
$elements_class = 'CoreLibs\Output\Form\Elements';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HTML/ELEMENTS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$string = "Something < = > Other <br> Next line and Quotes '\"";
echo "String: <pre>$string</pre><br>";
$log->debug('HTMLENT', Html::htmlent($string));
print "HTMLENT: " . Html::htmlent($string) . ": " . $_html->htmlent($string) . " (" . htmlentities($string) . ")<br>";
print "REMOVELB: " . Html::htmlent($string) . ": " . $_html->removeLB($string) . "<br>";
$date_str = [2021, 5, 1, 11, 10];
print "PRINTDATETIME: "
	. $_elements->printDateTime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4]) . "<br>";
// STATIC
// $string = "Something < = > Other <br> Next line and Quotes '\"";
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
	print "CHECKED(0): " . $check[0] . " -> " . print_r($check[1], true) . ": "
		. Html::checked($check[1], $check[0]) . "<br>";
	print "CHECKED(1): " . $check[0] . " -> " . print_r($check[1], true) . ": "
		. Html::checked($check[1], $check[0], Html::CHECKED) . "<br>";
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

$text = 'I am some text
with some
line breaks
in there. Theis 
is sucky';

print "LB remove: " . \CoreLibs\Convert\Html::removeLB($text) . "<br>";
print "LB remove: " . \CoreLibs\Convert\Html::removeLB($text, '##BR##') . "<br>";

print "</body></html>";

// __END__
