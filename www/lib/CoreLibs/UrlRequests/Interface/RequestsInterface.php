<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/9/20
 * DESCRIPTION:
 * URL Requests client interface
*/

namespace CoreLibs\UrlRequests\Interface;

interface RequestsInterface
{
	/**
	 * Makes an request to the target url via curl: GET
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array<string>                   $headers [default=[]] Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   [default=null] String to pass on as GET,
	 *                                                  if array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestGet(string $url, array $headers = [], null|string|array $query = null): array;

	/**
	 * Makes an request to the target url via curl: POST
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  string|array<string,mixed>      $payload Data to pass on as POST
	 * @param  array<string>                   $headers [default=[]] Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   [default=null] Optinal query parameters, array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestPost(
		string $url,
		string|array $payload,
		array $headers = [],
		null|string|array $query = null
	): array;

	/**
	 * Makes an request to the target url via curl: PUT
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  string|array<string,mixed>      $payload Data to pass on as POST
	 * @param  array<string>                   $headers [default=[]] Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   [default=null] Optinal query parameters, array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestPut(
		string $url,
		string|array $payload,
		array $headers = [],
		null|string|array $query = null
	): array;

	/**
	 * Makes an request to the target url via curl: DELETE
	 * Returns result as string (json)
	 * Note that DELETE payload is optional
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  null|string|array<string,mixed> $payload [default=null] Data to pass on as POST
	 * @param  array<string>                   $headers [default=[]] Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   [default=null] String to pass on as GET,
	 *                                                  if array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestDelete(
		string $url,
		null|string|array $payload = null,
		array $headers = [],
		null|string|array $query = null
	): array;
}

// __END__
