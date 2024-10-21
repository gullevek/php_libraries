<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/9/20
 * DESCRIPTION:
 * Curl Client for get/post/put/delete requests through the php curl inteface
*/

namespace CoreLibs\UrlRequests;

use RuntimeException;
use CoreLibs\Convert\Json;

class Curl implements Interface\RequestsInterface
{
	/** @var array<string> all the valid request type */
	private const VALID_REQUEST_TYPES = ["get", "post", "put", "delete"];
	/** @var int error bad request */
	public const HTTP_BAD_REQUEST = 400;
	/** @var int error not authorized Request */
	public const HTTP_NOT_AUTHORIZED = 401;
	/** @var int error forbidden */
	public const HTTP_FORBIDDEN = 403;
	/** @var int error not found */
	public const HTTP_NOT_FOUND = 404;
	/** @var int error conflict */
	public const HTTP_CONFLICT = 409;
	/** @var int error unprocessable entity */
	public const HTTP_UNPROCESSABLE_ENTITY = 422;
	/** @var int http ok request */
	public const HTTP_OK = 200;
	/** @var int http ok creted response */
	public const HTTP_CREATED = 201;
	/** @var int http ok no content */
	public const HTTP_NO_CONTENT = 204;

	/** @var string auth ident as "email:api_token */
	private string $auth_ident;
	/** @var bool if flagged to true, will raise an exception on failed authentication */
	private bool $exception_on_not_authorized = false;

	/**
	 * init class with auth ident token
	 *
	 * @param ?string $auth_ident [defaul=null]                    String to send for authentication, optional
	 * @param bool    $exception_on_not_authorized [default=false] If set to true
	 *                                                             will raise excepion on http auth error
	 */
	public function __construct(?string $auth_ident = null, bool $exception_on_not_authorized = false)
	{
		if (is_string($auth_ident)) {
			$this->auth_ident = $auth_ident;
		}
		$this->exception_on_not_authorized = $exception_on_not_authorized;
	}

	// *********************************************************************
	// MARK: PRIVATE METHODS
	// *********************************************************************

	// MARK: query and params convert

	/**
	 * Convert Query params and combine with url
	 *
	 * @param  string                          $url
	 * @param  null|string|array<string,mixed> $query
	 * @return string
	 */
	private function convertQuery(string $url, null|string|array $query = null): string
	{
		// conert to URL encoded query if array
		if (is_array($query)) {
			$query = http_build_query($query);
		}
		// add the params to the url
		if (!empty($query)) {
			// add ? if the string doesn't strt with one
			// check if URL has "?", if yes, add as "&" block
			$param_prefix = '?';
			if (strstr($url, '?') !== false) {
				$param_prefix = '&';
			}
			// if set, strip first character
			if (str_starts_with($query, '?') || str_starts_with($query, '&')) {
				$query = substr($query, 1);
			}
			// build url string
			$url .= $param_prefix . $query;
		}
		return $url;
	}

	/**
	 * Convert array params to json type string
	 *
	 * @param  string|array<string,mixed> $params
	 * @return string
	 */
	private function convertParams(string|array $params): string
	{
		// convert to string as JSON block if it is an array
		if (is_array($params)) {
			$params = Json::jsonConvertArrayTo($params);
		}
		return $params;
	}

	// MARK: main curl request

	/**
	 * Overall reequest call
	 *
	 * @param  string        $type    get, post, put, delete: if not set or invalid throw error
	 * @param  string        $url     The URL being requested,
	 *                                including domain and protocol
	 * @param  array<string> $headers [default=[]] Headers to be used in the request
	 * @param  string|null $params [default=null] Optional url parameters for post/put requests
	 * @return array{code:string,content:string}
	 */
	private function curlRequest(string $type, string $url, array $headers = [], ?string $params = null): array
	{
		if (!in_array($type, self::VALID_REQUEST_TYPES)) {
			throw new RuntimeException(
				json_encode([
					'status' => 'FAILURE',
					'code' => 'C003',
					'type' => 'InvalidRequestType',
					'message' => 'Invalid request type set: ' . $type,
					'context' => [
						'url' => $url,
						'type' => $type,
					],
				]) ?: '',
				0,
			);
		}
		// init curl handle
		$handle = $this->handleCurleInit($url);
		// set the standard curl options
		$this->setCurlOptions($handle, $headers);
		// for post we set POST option
		if ($type == "post") {
			curl_setopt($handle, CURLOPT_POST, true);
		} elseif (in_array($type, ["put", "delete"])) {
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, strtoupper($type));
		}
		if (in_array($type, ["post", "put"]) && !empty($params)) {
			curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
		}
		// run curl execute
		$http_result = $this->handleCurlExec($handle);
		// get response code and bail on not authorized
		$http_response = $this->handleCurlResponse($http_result, $handle);
		// return response and result
		return [
			'code' => (string)$http_response,
			'content' => (string)$http_result
		];
	}

	// MARK: curl request helpers

	/**
	 * Handel curl init and errors
	 *
	 * @param  string            $url
	 * @return \CurlHandle
	 */
	private function handleCurleInit(string $url): \CurlHandle
	{
		$handle = curl_init($url);
		if ($handle !== false) {
			return $handle;
		}
		// throw Error here with all codes
		throw new RuntimeException(
			json_encode([
				'status' => 'FAILURE',
				'code' => 'C001',
				'type' => 'CurlInitError',
				'message' => 'Failed to init curl with url: ' . $url,
				'context' => [
					'url' => $url,
				],
			]) ?: '',
			0,
		);
	}

	/**
	 * set the default curl options
	 *
	 * headers array: do not split into "key" => "value", they must be "key: value"
	 *
	 * @param  \CurlHandle   $handle
	 * @param  array<string> $headers list of options
	 * @return void
	 */
	private function setCurlOptions(\CurlHandle $handle, array $headers): void
	{
		if (!empty($this->auth_ident)) {
			curl_setopt($handle, CURLOPT_USERPWD, $this->auth_ident);
		}
		if ($headers !== []) {
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		}
		// curl_setopt($handle, CURLOPT_FAILONERROR, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		// for debug only
		curl_setopt($handle, CURLINFO_HEADER_OUT, true);
	}

	// MARK: Curl Exception handler

	/**
	 * handles any CURL execute and on error throws a correct error message
	 *
	 * @param  \CurlHandle $handle
	 * @return string
	 */
	private function handleCurlExec(\CurlHandle $handle): string
	{
		// execute query
		$http_result = curl_exec($handle);
		if ($http_result === true) {
			return (string)self::HTTP_OK;
		} elseif ($http_result !== false) {
			return $http_result;
		}
		$url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
		$errno = curl_errno($handle);
		$message = curl_error($handle);
		switch ($errno) {
			case CURLE_COULDNT_CONNECT:
			case CURLE_COULDNT_RESOLVE_HOST:
			case CURLE_OPERATION_TIMEOUTED:
				$message = 'Could not connect to server (' . $url . '). Please check your '
					. 'internet connection and try again. [' . $message . ']';
				break;
			case CURLE_SSL_PEER_CERTIFICATE:
				$message = 'Could not verify SSL certificate. Please make sure '
					. 'that your network is not intercepting certificates. '
					. '(Try going to ' . $url . 'in your browser.) '
					. '[' . $message . ']';
				break;
			case 0:
			default:
				$message = 'Unexpected error communicating with server: ' . $message;
		}

		// throw an error like in the normal reqeust, but set to CURL error
		throw new RuntimeException(
			json_encode([
				'status' => 'FAILURE',
				'code' => 'C002',
				'type' => 'CurlError',
				'message' => $message,
				'context' => [
					'url' => $url,
					'errno' => $errno,
					'message' => $message,
				],
			]) ?: '',
			$errno
		);
	}

	// MARK: curl response hanlder

	/**
	 * Handle curl response and not auth 401 errors
	 *
	 * @param  string      $http_result
	 * @param  \CurlHandle $handle
	 * @return string
	 */
	private function handleCurlResponse(
		string $http_result,
		\CurlHandle $handle
	): string {
		$http_response = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
		if (
			!$this->exception_on_not_authorized ||
			$http_response !== self::HTTP_NOT_AUTHORIZED
		) {
			return (string)$http_response;
		}
		$err = curl_errno($handle);
		// extract all the error codes
		$result_ar = json_decode((string)$http_result, true);

		$url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
		$error_status = 'ERROR';
		$error_code = $http_response;
		$error_type = 'UnauthorizedRequest';
		$message = 'Request could not be finished successfully because of an authorization error';

		// throw Error here with all codes
		throw new RuntimeException(
			json_encode([
				'status' => $error_status,
				'code' => $error_code,
				'type' => $error_type,
				'message' => $message,
				'context' => [
					'url' => $url,
					'result' => $result_ar,
				],
			]) ?: '',
			$err
		);
	}

	// *********************************************************************
	// MARK: PUBLIC METHODS
	// *********************************************************************

	// MARK: request methods

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
	public function requestGet(string $url, array $headers = [], null|string|array $query = null): array
	{
		return $this->curlRequest("get", $this->convertQuery($url, $query), $headers);
	}

	/**
	 * Makes an request to the target url via curl: POST
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  string|array<string,mixed>      $params  String to pass on as POST
	 * @param  array<string>                   $headers Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   Optinal query parameters, array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestPost(
		string $url,
		string|array $params,
		array $headers,
		null|string|array $query = null
	): array {
		return $this->curlRequest(
			"post",
			$this->convertQuery($url, $query),
			$headers,
			$this->convertParams($params)
		);
	}

	/**
	 * Makes an request to the target url via curl: PUT
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  string|array<string,mixed>      $params  String to pass on as POST
	 * @param  array<string>                   $headers Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   Optinal query parameters, array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestPut(
		string $url,
		string|array $params,
		array $headers,
		null|string|array $query = null
	): array {
		return $this->curlRequest(
			"put",
			$this->convertQuery($url, $query),
			$headers,
			$this->convertParams($params)
		);
	}

	/**
	 * Makes an request to the target url via curl: DELETE
	 * Returns result as string (json)
	 *
	 * @param  string                          $url     The URL being requested,
	 *                                                  including domain and protocol
	 * @param  array<string>                   $headers [default=[]] Headers to be used in the request
	 * @param  null|string|array<string,mixed> $query   [default=null] String to pass on as GET,
	 *                                                  if array will be converted
	 * @return array{code:string,content:string} Result code and content as array, content is json
	 */
	public function requestDelete(string $url, array $headers = [], null|string|array $query = null): array
	{
		return $this->curlRequest("delete", $this->convertQuery($url, $query), $headers);
	}
}

// __END__
