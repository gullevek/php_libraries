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
$LOG_FILE_ID = 'classTest-html_build-block';
ob_end_flush();

use CoreLibs\Template\HtmlBuilder\Block;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HTML BUILD: BLOCK';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$el = Block::cel('div', 'el-1', 'Content', ['red'], ['onClick' => 'javascript:alert(\'JS alert\');']);

print "<pre>" . htmlentities(Block::buildHtml($el)) . "</pre>";

$el_a = Block::cel('div', 'u-id', '', ['base', 'cool']);
$el_a_1 = Block::cel('span', 's-id-1', 'Span A', ['bold']);
$el_a_2 = Block::cel('span', 's-id-2', 'Span B');
$el_a_3 = Block::cel('a', 'link-a', 'Title', ['l-highlight'], ['OnClick' => 'Foo();']);
$el_a_2 = Block::aelx($el_a_2, $el_a_3);
// css changes before added to array
$el_a_1 = Block::acssel($el_a_1, 'italic', 'green', 'italic', 'font-large');
$el_a_1 = Block::rcssel($el_a_1, 'green');
// switch
$el_a_1 = Block::scssel($el_a_1, ['one', 'two', 'three'], ['three']);
// this will add el_a_2 to the el_a block
$el_a_1 = Block::aelx($el_a_1, $el_a_2);
$el_a = Block::aelx($el_a, $el_a_1, $el_a_2);

// this will not update el_a
// $el_a_1 = Block::aelx($el_a_1, $el_a_2);

$el_a_list = [];
$el_a_list[] = Block::cel('foo', 'foo-A');
$el_a_list[] = Block::cel('bar', 'foo-B');
$el_a_list[] = Block::cel('baz', 'foo-C');
$el_a_list[] = Block::cel('br');
$el_a_list[] = Block::cel('input');

echo "<hr>";
print "EL_A: <pre>" . print_r($el_a, true) . "</pre>";

echo "<hr>";
print "phfo(\$el_o): <pre>" . htmlentities(Block::buildHtml($el_a, true)) . "</pre>";
echo "<hr>";
print "phfa(\$el_list): <pre>" . htmlentities(Block::buildHtmlFromList($el_a_list, true)) . "</pre>";

echo "<hr>";
// self loop test (will not trigger, are arrays)
$el_s = Block::cel('div', 'id-s', 'Self', []);
$el_s = Block::aelx($el_s, $el_s);

print "phfo(\$el_): <pre>" . htmlentities(Block::buildHtml($el_s, true)) . "</pre>";

print "</body></html>";

// __END__
