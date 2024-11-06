<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/10/29
 * DESCRIPTION:
 * Curl Client Trait for get/post/put/delete requests through the php curl inteface
 *
 * For anything more complex use guzzlehttp/http
 * https://docs.guzzlephp.org/en/stable/index.html
*/

// phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace CoreLibs\UrlRequests;

trait CurlTrait
{
	/**
	 * Set the array block that is sent to the request call
	 * Make sure that if headers is set as key but null it stays null and set to empty array
	 * if headers key is missing
	 * "get" calls do not set any body
	 *
	 * @param  string $type if set as get do not add body, else add body
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>} $options Request options
	 * @return array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}
	 */
	private function setOptions(string $type, array $options): array
	{
		if ($type == "get") {
			return [
				"headers" => !array_key_exists('headers', $options) ? [] : $options['headers'],
				"query" => $options['query'] ?? null,
			];
		} else {
			return [
				"headers" => !array_key_exists('headers', $options) ? [] : $options['headers'],
				"query" => $options['query'] ?? null,
				"body" => $options['body'] ?? null,
			];
		}
	}

	/**
	 * combined set call for any type of request with options type parameters
	 * The following options can be set:
	 * header: as array string:string
	 * query as string or array string:string
	 * body as string or array of any type
	 *
	 * @param  string $type What type of request we send, will throw exception if not a valid one
	 * @param  string $url  The url to send
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|string|array<string,mixed>,body?:null|string|array<string,mixed>} $options Request options
	 * @return array{code:string,headers:array<string,array<string>>,content:string} [default=[]] Result code, headers and content as array, content is json
	 * @throws \UnexpectedValueException on missing body data when body data is needed
	 */
	abstract public function request(string $type, string $url, array $options = []): array;

	/**
	 * Makes an request to the target url via curl: GET
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}                   $options Options to set
	 * @return array{code:string,headers:array<string,array<string>>,content:string} [default=[]] Result code, headers and content as array, content is json
	 */
	public function get(string $url, array $options = []): array
	{
		return $this->request(
			"get",
			$url,
			$this->setOptions('get', $options),
		);

		// array{headers?: array<string, array<string>|string>|null, query?: array<string, string>|null, body?: array<string, mixed>|string|null},
		// array{headers?: array<string, array<string>|string>|null, query?: array<string, mixed>|string|null, body?: array<string, mixed>|string|null}
	}

	/**
	 * Makes an request to the target url via curl: POST
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}                   $options Options to set
	 * @return array{code:string,headers:array<string,array<string>>,content:string} Result code, headers and content as array, content is json
	 */
	public function post(string $url, array $options): array
	{
		return $this->request(
			"post",
			$url,
			$this->setOptions('post', $options),
		);
	}

	/**
	 * Makes an request to the target url via curl: PUT
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}                   $options Options to set
	 * @return array{code:string,headers:array<string,array<string>>,content:string} Result code, headers and content as array, content is json
	 */
	public function put(string $url, array $options): array
	{
		return $this->request(
			"put",
			$url,
			$this->setOptions('put', $options),
		);
	}

	/**
	 * Makes an request to the target url via curl: PATCH
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}                   $options Options to set
	 * @return array{code:string,headers:array<string,array<string>>,content:string} Result code, headers and content as array, content is json
	 */
	public function patch(string $url, array $options): array
	{
		return $this->request(
			"patch",
			$url,
			$this->setOptions('patch', $options),
		);
	}

	/**
	 * Makes an request to the target url via curl: DELETE
	 * Returns result as string (json)
	 * Note that DELETE body is optional
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>}                   $options Options to set
	 * @return array{code:string,headers:array<string,array<string>>,content:string} [default=[]] Result code, headers and content as array, content is json
	 */
	public function delete(string $url, array $options = []): array
	{
		return $this->request(
			"delete",
			$url,
			$this->setOptions('delete', $options),
		);
	}
}

// __END__
