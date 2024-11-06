<?php // phpcs:ignore PSR1.Files.SideEffects

declare(strict_types=1);

// url requests target test
require 'config.php';
use CoreLibs\Convert\Json;
$LOG_FILE_ID = 'classTest-urlrequests-target';
$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

/**
 * build return json
 *
 * @param  array<string,mixed> $http_headers
 * @param  string $body
 * @return string
 */
function buildContent(array $http_headers, string $body): string
{
	return Json::jsonConvertArrayTo([
		'HEADERS' => $http_headers,
		"REQUEST_TYPE" => $_SERVER['REQUEST_METHOD'],
		"PARAMS" => $_GET,
		"BODY" => Json::jsonConvertToArray($body),
		// "STRING_BODY" => $body,
	]);
}

$http_headers = array_filter($_SERVER, function ($value, $key) {
	if (str_starts_with($key, 'HTTP_')) {
		return true;
	}
}, ARRAY_FILTER_USE_BOTH);

header("Content-Type: application/json; charset=UTF-8");

// if the header has Authorization and RunAuthTest then exit with 401
if (!empty($http_headers['HTTP_AUTHORIZATION']) && !empty($http_headers['HTTP_RUNAUTHTEST'])) {
	header("HTTP/1.1 401 Unauthorized");
	print buildContent($http_headers, '{"code": 401, "content": {"Error": "Not Authorized"}}');
	exit;
}

if (($file_get = file_get_contents('php://input')) === false) {
	header("HTTP/1.1 404 Not Found");
	print buildContent($http_headers, '{"code": 404, "content": {"Error": "file_get_contents failed"}}');
	exit;
}
// str_replace('\"', '"', trim($file_get, '"'));

$log->debug('SERVER', $log->prAr($_SERVER));
$log->debug('HEADERS', $log->prAr($http_headers));
$log->debug('REQUEST TYPE', $_SERVER['REQUEST_METHOD']);
$log->debug('GET', $log->prAr($_GET));
$log->debug('POST', $log->prAr($_POST));
$log->debug('PHP-INPUT', $log->prAr($file_get));

print buildContent($http_headers, $file_get);

$log->debug('[END]', '=========================================>');

// __END__
