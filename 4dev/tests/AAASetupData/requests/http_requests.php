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
 * @param  array<string,mixed> $http_headers
 * @param  ?string $body
 * @return string
 */
function buildContent(array $http_headers, ?string $body): string
{
	if (is_string($body) && !empty($body)) {
		$_body = json_decode($body, true);
		if (!is_array($_body)) {
			$body = [$body];
		} else {
			$body = $_body;
		}
	} elseif (is_string($body)) {
		$body = [];
	}
	return json_encode([
		'HEADERS' => $http_headers,
		"REQUEST_TYPE" => $_SERVER['REQUEST_METHOD'],
		"PARAMS" => $_GET,
		"BODY" => $body,
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
	exit(1);
}

// if server request type is get set file_get to null -> no body
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$file_get = null;
} elseif (($file_get = file_get_contents('php://input')) === false) {
	header("HTTP/1.1 404 Not Found");
	print buildContent($http_headers, '{"code": 404, "content": {"Error": "file_get_contents failed"}}');
	exit(1);
}

print buildContent($http_headers, $file_get);

// __END__
