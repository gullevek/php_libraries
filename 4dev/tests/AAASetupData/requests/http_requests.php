<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: Ymd
 * DESCRIPTION:
 * DescriptionHere
*/

declare(strict_types=1);

$http_headers = array_filter($_SERVER, function ($value, $key) {
	if (str_starts_with($key, 'HTTP_')) {
		return true;
	}
}, ARRAY_FILTER_USE_BOTH);

$file_get = file_get_contents('php://input') ?: '["code": 500, "content": {"Error" => "file_get_contents failed"}]';

header("Content-Type: application/json; charset=UTF-8");

print json_encode([
	'HEADERS' => $http_headers,
	"PARAMS" => $_GET,
	"BODY" => json_decode($file_get, true),
]);

// __END__
