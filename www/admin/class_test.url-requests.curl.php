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
$LOG_FILE_ID = 'classTest-urlrequests';
ob_end_flush();

use CoreLibs\UrlRequests\Curl;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$client = new Curl();

$PAGE_NAME = 'TEST CLASS: URL REQUESTS CURL';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "<hr>";
$data = $client->requestGet(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=get_a',
	['test-header: ABC', 'request-type: _GET'],
	['foo' => 'BAR']
);
print "_GET RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->requestPost(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=post_a',
	['payload' => 'data post'],
	[
		'Content-Type: application/json',
		'Accept: application/json',
		'test-header: ABC',
		'info-request-type: _POST'
	],
	['foo' => 'BAR post'],
);
print "_POST RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->requestPut(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=put_a',
	['payload' => 'data put'],
	[
		'Content-Type: application/json',
		'Accept: application/json',
		'test-header: ABC',
		'info-request-type: _PUT'
	],
	['foo' => 'BAR put'],
);
print "_PUT RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->requestPatch(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=patch_a',
	['payload' => 'data patch'],
	[
		'Content-Type: application/json',
		'Accept: application/json',
		'test-header: ABC',
		'info-request-type: _PATCH'
	],
	['foo' => 'BAR patch'],
);
print "_PATCH RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->requestDelete(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=delete_no_body_a',
	null,
	[
		'Content-Type: application/json',
		'Accept: application/json',
		'test-header: ABC',
		'info-request-type: _DELETE'
	],
	['foo' => 'BAR delete'],
);
print "_DELETE RESPONSE: <pre>" . print_r($data, true) . "</pre>";
$data = $client->requestDelete(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlReqeusts.target.php'
	. '?other=delete_body_a',
	['payload' => 'data delete'],
	[
		'Content-Type: application/json',
		'Accept: application/json',
		'test-header: ABC',
		'info-request-type: _DELETE'
	],
	['foo' => 'BAR delete'],
);
print "_DELETE RESPONSE: <pre>" . print_r($data, true) . "</pre>";


print "</body></html>";

// __END__
