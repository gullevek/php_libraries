<?php // phpcs:ignore PSR1.Files.SideEffects

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: Ymd
 * DESCRIPTION:
 * DescriptionHere
*/

declare(strict_types=1);

/**
 * build return json
 *
 * @param  array  $http_headers
 * @param  string $body
 * @return string
 */
function buildContent(array $http_headers, string $body): string
{
	return json_encode([
		'HEADERS' => $http_headers,
		"REQUEST_TYPE" => $_SERVER['REQUEST_METHOD'],
		"PARAMS" => $_GET,
		"BODY" => json_decode($body, true)
	]);
}

$http_headers = array_filter($_SERVER, function ($value, $key) {
	if (str_starts_with($key, 'HTTP_')) {
		return true;
	}
}, ARRAY_FILTER_USE_BOTH);

header("Content-Type: application/json; charset=UTF-8");

if (!empty($http_headers['HTTP_AUTHORIZATION']) && !empty($http_headers['HTTP_RUNAUTHTEST'])) {
	header("HTTP/1.1 401 Unauthorized");
	print buildContent($http_headers, '["code": 401, "content": {"Error" => "Not Authorized"}]');
	exit;
}

print buildContent(
	$http_headers,
	file_get_contents('php://input') ?: '["code": 500, "content": {"Error" => "file_get_contents failed"}]'
);

// __END__
