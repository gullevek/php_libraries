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

$PAGE_NAME = 'TEST CLASS: URL REQUESTS CURL';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$client = new Curl();

print "<hr>";
$data = $client->get(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
		. '?other=get_a',
	[
		'headers' => $client->prepareHeaders([
			'test-header: ABC',
			'info-request-type: _GET',
			'Funk-pop' => 'Semlly god'
		]),
		'query' => ['foo' => 'BAR']
	]
);
print "_GET RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->request(
	'get',
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=get_a',
);
print "_GET RESPONSE, nothing set: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
try {
	$data = $client->request(
		'get',
		'soba54.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
		. '?other=get_a',
	);
	print "_GET RESPONSE, nothing set, invalid URL: <pre>" . print_r($data, true) . "</pre>";
} catch (Exception $e) {
	print "Exception: <pre>" . print_r($e, true) . "</pre><br>";
}


print "<hr>";
$data = $client->request(
	"get",
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/'
		. 'trunk/www/admin/UrlRequests.target.php'
		. '?other=get_a',
	[
		"headers" => $client->prepareHeaders([
			'test-header: ABC',
			'info-request-type: _GET',
			'Funk-pop' => 'Semlly god'
		]),
		"query" => ['foo' => 'BAR'],
	],
);
print "[request] _GET RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->post(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=post_a',
	[
		'body' => ['payload' => 'data post'],
		'headers' => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _POST'
		]),
		'query' => ['foo' => 'BAR post'],
	]
);
print "_POST RESPONSE: <pre>" . print_r($data, true) . "</pre>";
print "<hr>";
$data = $client->request(
	"post",
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=post_a',
	[
		"body" => ['payload' => 'data post', 'request' => 'I am the request body'],
		"headers" => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _POST'
		]),
		"query" => ['foo' => 'BAR post'],
	]
);
print "[request] _POST RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->put(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=put_a',
	[
		"body" => ['payload' => 'data put'],
		"headers" => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _PUT'
		]),
		'query' => ['foo' => 'BAR put'],
	]
);
print "_PUT RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->patch(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=patch_a',
	[
		"body" => ['payload' => 'data patch'],
		"headers" => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _PATCH'
		]),
		'query' => ['foo' => 'BAR patch'],
	]
);
print "_PATCH RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->delete(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=delete_no_body_a',
	[
		"body" => null,
		"headers" => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _DELETE'
		]),
		"query" => ['foo' => 'BAR delete'],
	]
);
print "_DELETE RESPONSE: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";
$data = $client->delete(
	'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/UrlRequests.target.php'
	. '?other=delete_body_a',
	[
		"body" => ['payload' => 'data delete'],
		"headers" => $client->prepareHeaders([
			'Content-Type: application/json',
			'Accept: application/json',
			'test-header: ABC',
			'info-request-type: _DELETE'
		]),
		"query" => ['foo' => 'BAR delete'],
	]
);
print "_DELETE RESPONSE BODY: <pre>" . print_r($data, true) . "</pre>";

print "<hr>";

try {
	$uc = new Curl([
		"base_uri" => 'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/www/admin/foo',
		"headers" =>  [
			'DEFAULT-master' => 'master-header',
			'default-header' => 'uc-get',
			'default-remove' => 'will be removed',
			'default-remove-array' => ['a', 'b'],
			'default-remove-array-part' => ['c', 'd'],
			'default-remove-array-part-alt' => ['c', 'd', 'e'],
			'default-overwrite' => 'will be overwritten',
			'default-add' => 'will be added',
		]
	]);
	print "CONFIG: <pre>" . print_r($uc->getConfig(), true) . "</pre>";
	$uc->removeHeaders(['default-remove' => '']);
	$uc->removeHeaders(['default-remove-array' => ['a', 'b']]);
	$uc->removeHeaders(['default-remove-array-part' => 'c']);
	$uc->removeHeaders(['default-remove-array-part-alt' => ['c', 'd']]);
	$uc->setHeaders(['default-new' => 'Something new']);
	$uc->setHeaders(['default-overwrite' => 'Something Overwritten']);
	$uc->setHeaders(['default-add' => 'Something Added'], true);
	print "CONFIG: <pre>" . print_r($uc->getConfig(), true) . "</pre>";
	$data = $uc->request(
		'get',
		'UrlRequests.target.php?other=get_a',
		[
			'headers' => [
				'call-header' => 'call-get',
				'default-header' => 'overwrite-uc-get',
				'X-Foo' => ['bar', 'baz'],
			]
		]
	);
	print "[uc] _GET RESPONSE, nothing set: <pre>" . print_r($data, true) . "</pre>";
	print "[uc] SENT URL: " . $uc->getUrlSent() . "<br>";
	print "[uc] SENT URL PARSED: <pre>" . print_r($uc->getUrlParsedSent(), true) . "</pre>";
	print "[uc] SENT HEADERS: <pre>" . print_r($uc->getHeadersSent(), true) . "</pre>";
} catch (Exception $e) {
	print "Exception: <pre>" . print_r(json_decode($e->getMessage(), true), true) . "</pre><br>";
}


print "</body></html>";

// __END__
