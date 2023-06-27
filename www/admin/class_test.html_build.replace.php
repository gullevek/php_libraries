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
$LOG_FILE_ID = 'classTest-html_build-replace';
ob_end_flush();

use CoreLibs\Template\HtmlBuilder\Replace;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HTML BUILD: REPLACE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$html_block = <<<HTML
<div id="{ID}" class="{CSS}">
	{CONTENT}
</div>
HTML;

print "<pre>" . htmlentities(Replace::replaceData(
	$html_block,
	[
		'ID', 'CSS', '{CONTENT}'
	],
	[
		'block-id', join(',', ['blue', 'red']),
		'Some content here<br>with bla bla inside'
	]
)) . "</pre>";

Replace::loadElements(
	['foo', $html_block],
	['bar', <<<HTML
<span id="{ID}">{CONTENT}</span>
HTML]
);

print "Get: <pre>" . htmlentities(Replace::getElement('bar') ?: '') . '</pre>';

print "Build element: <pre>" . htmlentities(Replace::buildElement(
	'bar',
	['ID}', '{CONTENT'],
	['new-id', 'Test cow 日本語']
)) . '</pre>' ;

print "Build element as replace: <pre>" . htmlentities(Replace::buildElement(
	'bar',
	['ID}', '{CONTENT'],
	['diff-id', 'Test cow 日本語. More text plus'],
	'rpl-1'
)) . '</pre>' ;

print "Get replacement: <pre>" . htmlentities(Replace::getReplaceBlock('rpl-1')) . "</pre>";

print "</body></html>";

// __END__
