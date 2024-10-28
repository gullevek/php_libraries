<?php

// url requests target test
require 'config.php';
use CoreLibs\Convert\Json;
$LOG_FILE_ID = 'classTest-urlrequests-target';
$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$http_headers = array_filter($_SERVER, function ($value, $key) {
	if (str_starts_with($key, 'HTTP_')) {
		return true;
	}
}, ARRAY_FILTER_USE_BOTH);

$file_get = file_get_contents('php://input');
// str_replace('\"', '"', trim($file_get, '"'));

$log->debug('SERVER', $log->prAr($_SERVER));
$log->debug('HEADERS', $log->prAr($http_headers));
$log->debug('POST', $log->prAr($_POST));
$log->debug('PHP-INPUT', $log->prAr($file_get));

print Json::jsonConvertArrayTo([
	'HTTP_HEADERS' => $http_headers,
	"_GET" => $_GET,
	"_POST" => Json::jsonConvertToArray($file_get),
]);

$log->debug('[END]', '=========================================>');

// __END__
