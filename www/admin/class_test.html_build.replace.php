<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-html_build-string-replace';
ob_end_flush();

use CoreLibs\Template\HtmlBuilder\StringReplace;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HTML BUILD: STRING REPLACE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$html_block = <<<HTML
<div id="{ID}" class="{CSS}">
	{CONTENT}
</div>
HTML;

print "<pre>" . htmlentities(StringReplace::replaceData(
	$html_block,
	[
		'ID' => 'block-id',
		'CSS' => join(',', ['blue', 'red']),
		'{CONTENT}' => 'Some content here<br>with bla bla inside'
	]
)) . "</pre>";

StringReplace::loadElements(
	['foo', $html_block],
	['bar', <<<HTML
<span id="{ID}">{CONTENT}</span>
HTML]
);

print "Get: <pre>" . htmlentities(StringReplace::getElement('bar') ?: '') . '</pre>';

print "Build element: <pre>" . htmlentities(StringReplace::buildElement(
	'bar',
	[
		'ID}' => 'new-id',
		'{CONTENT' => 'Test cow 日本語'
	]
)) . '</pre>' ;

print "Build element as replace: <pre>" . htmlentities(StringReplace::buildElement(
	'bar',
	['
		ID}' => 'diff-id',
		'{CONTENT' => 'Test cow 日本語. More text plus'
	],
	'rpl-1'
)) . '</pre>' ;

print "Get replacement: <pre>" . htmlentities(StringReplace::getReplaceBlock('rpl-1')) . "</pre>";

print "</body></html>";

// __END__
