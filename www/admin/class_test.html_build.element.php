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
$LOG_FILE_ID = 'classTest-html_build';
ob_end_flush();

use CoreLibs\Template\HtmlBuilder\Element;
use CoreLibs\Template\HtmlBuilder\General\Error;
use CoreLibs\Template\HtmlBuilder\General\HtmlBuilderExcpetion;
use CoreLibs\Debug\Support;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HTML BUILD';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$el = new Element('div', 'el-1', 'Content', ['red'], ['onClick' => 'javascript:alert(\'JS alert\');']);

print "<pre>" . htmlentities($el->buildHtml()) . "</pre>";

$el_o = new Element('div', 'u-id', '', ['base', 'cool']);
$el_o_1 = new Element('span', 's-id-1', 'Span A', ['bold']);
$el_o_2 = new Element('span', 's-id-2', 'Span B');
$el_o_3 = new Element('a', 'link-a', 'Title', ['l-highlight'], ['OnClick' => 'Foo();']);
$el_o_2->addSub($el_o_3);
$el_o->addSub($el_o_1, $el_o_2);

$el_o_1->addCss('italic', 'green', 'italic', 'font-large')
	->removeCss('green');

$el_o_2->addCss('wrong-css')
	->removeCss('wrong-css', 'correct-css');
$el_o_2->addCss('a', 'b')->removeCss('correct-css');

$el_o_1->addSub($el_o_2);

// var_dump($el2);

$el_o_list = [];
$el_o_list[] = new Element('foo', 'foo-A');
$el_o_list[] = new Element('bar', 'foo-B');
$el_o_list[] = new Element('baz', 'foo-C');
$el_o_list[] = new Element('br');
$el_o_list[] = new Element('input', 'tag', '', [], ['name' => 'foo', 'value' => 'ABC']);

// $el2->resetSub();
// var_dump($el2);
echo "<hr>";
print "EL_O: <pre>" . print_r($el_o, true) . "</pre>";

echo "<hr>";
print "buildHtml(): <pre>" . htmlentities($el_o->buildHtml()) . "</pre>";
/* echo "<hr>";
print "phfo(\$el_o): <pre>" . htmlentities($el_o::printHtmlFromObject($el_o, true)) . "</pre>"; */
echo "<hr>";
print "phfa(\$el_list): <pre>" . htmlentities($el_o::buildHtmlFromList($el_o_list, true)) . "</pre>";

echo "<hr>";

// self loop

$el_s = new Element('div', 'id-s', 'Self');
try {
	$el_s->addSub($el_s, new Element('span', '', 'Span'));
} catch (HtmlBuilderExcpetion $e) {
	print "E: " . $e->getMessage() . " | " . $e->getTraceAsString() . "<br>";
}

// var_dump($el_s);
print "el_s, buildHtml(): <pre>" . htmlentities($el_s->buildHtml()) . "</pre>";

$el_s_2 = new Element('div', 'id-s', 'Self', []);
$el_s_2->addSub(
	new Element('span', 's-1', 's 1'),
	new Element('span', 's-2', 's 2'),
);

$el_s_3 = new Element('div', 'id-3', 'ID 3');
try {
	$el_s_3->addSub($el_s_2);
	$el_s_2->addSub($el_s_2);
} catch (HtmlBuilderExcpetion $e) {
	print "E: " . $e->getMessage() . " | " . $e->getTraceAsString() . "<br>";
}

// print "<pre>" . var_export($el_s_3, true) . "</pre>";

print "el_s_3, buildHtml(): <pre>" . htmlentities($el_s_3->buildHtml()) . "</pre>";

echo "<hr>";
Error::resetMessages();
try {
	$el_er = new Element('');
} catch (HtmlBuilderExcpetion $e) {
	print "E: " . $e->getMessage() . " | " . $e->getTraceAsString() . "<br>";
	if ($e->getPrevious() !== null) {
		print "E: " . $e->getPrevious()->getMessage() . " | " . $e->getPrevious()->getTraceAsString() . "<br>";
	}
}
print "Errors: <pre>" . print_r(Error::getMessages(), true) . "</pre>";
print "Warning: " . Support::printToString(Error::hasWarning()) . "<br>";
print "Error: " . Support::printToString(Error::hasError()) . "<br>";
Error::resetMessages();
try {
	$el_er = new Element('123123');
} catch (HtmlBuilderExcpetion $e) {
	print "E: " . $e->getMessage() . " | " . $e->getTraceAsString() . "<br>";
	if ($e->getPrevious() !== null) {
		print "E: " . $e->getPrevious()->getMessage() . " | " . $e->getPrevious()->getTraceAsString() . "<br>";
	}
}
print "Errors: <pre>" . print_r(Error::getMessages(), true) . "</pre>";
print "Warning: " . Support::printToString(Error::hasWarning()) . "<br>";
print "Error: " . Support::printToString(Error::hasError()) . "<br>";

print "</body></html>";

// __END__
