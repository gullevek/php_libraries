<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/9/20
 * DESCRIPTION:
 * Curl Client for get/post/put/delete requests through the php curl inteface
 *
 * For anything more complex use guzzlehttp/http
 * https://docs.guzzlephp.org/en/stable/index.html
 *
 * Requests are guzzleHttp compatible
 * Config for setup is guzzleHttp compatible (except the exception_on_not_authorized)
 * Any setters and getters are only for this class
*/

declare(strict_types=1);

namespace CoreLibs\UrlRequests;

use RuntimeException;
use CoreLibs\Convert\Json;

/** @package CoreLibs\UrlRequests */
class Curl implements Interface\RequestsInterface
{
	// all general calls: get/post/put/patch/delete
	use CurlTrait;

	/** @var array<string> all the valid request type */
	private const VALID_REQUEST_TYPES = ["get", "post", "put", "patch", "delete"];
	/** @var array<string> list of requests type that are set as custom in the curl options */
	private const CUSTOM_REQUESTS = ["put", "patch", "delete"];
	/** @var array<string> list of requests types that have _POST type fields */
	private const HAVE_POST_FIELDS = ["post", "put", "patch", "delete"];
	/** @var array<string> list of requests that must have a body */
	private const MANDATORY_POST_FIELDS = ["post", "put", "patch"];
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
	/** @var int major version for user agent */
	public const MAJOR_VERSION = 1;

	// the config is set to be as much compatible to guzzelHttp as possible
	// phpcs:disable Generic.Files.LineLength
	/** @var array{auth?:array{0:string,1:string,2:string},exception_on_not_authorized:bool,base_uri:string,headers:array<string,string|array<string>>,query:array<string,string>,timeout:float,connection_timeout:float} config settings as
	 *phpcs:enable Generic.Files.LineLength
	 * auth: [0: user, 1: password, 2: auth type]
	 * base_uri: base url to set, will prefix all urls given in calls
	 * headers: (array) base headers, can be overwritten by headers set in call
	 * timeout: default 0, in seconds (CURLOPT_TIMEOUT_MS)
	 * connect_timeout: default 300, in seconds (CURLOPT_CONNECTTIMEOUT_MS)
	 * : below is not a guzzleHttp config
	 * exception_on_not_authorized: bool true/false for throwing exception on auth error
	 */
	private array $config = [
		'exception_on_not_authorized' => false,
		'base_uri' => '',
		'query' => [],
		'headers' => [],
		'timeout' => 0,
		'connection_timeout' => 300,
	];
	/** @var array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string} parsed base_uri */
	private array $parsed_base_uri = [];
	/** @var array<string,string> lower key header name matches to given header name */
	private array $headers_named = [];
	/** @var int auth type from auth array in config */
	private int $auth_type = 0;
	/** @var string username and password string from auth array in config */
	private string $auth_userpwd = '';
	/** @var string set if auth type basic is given, will be set as "Authorization: ..." */
	private string $auth_basic_header = '';

	/** @var array<string,array<string>> received headers per header name, with sub array if there are redirects */
	private array $received_headers = [];

	/** @var string the current url sent */
	private string $url = '';
	/** @var array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string} parsed url to sent */
	private array $parsed_url = [];
	/** @var array<string,string> the current headers sent */
	private array $headers = [];

	/**
	 * see config allowe entries above
	 *
	 * @param array<string,mixed> $config config settings to be set
	 */
	public function __construct(array $config = [])
	{
		$this->setConfiguration($config);
	}

	// *********************************************************************
	// MARK: PRIVATE METHODS
	// *********************************************************************

	/**
	 * Set the main configuration
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param  array{auth?:array{0:string,1:string,2:string},exception_on_not_authorized?:bool,base_uri?:string,headers?:array<string,string|array<string>>,query?:array<string,string>,timeout?:float,connection_timeout?:float}  $config
	 * @return void
	 * phpcs:enable Generic.Files.LineLength
	 */
	private function setConfiguration(array $config)
	{
		$default_config = [
			'exception_on_not_authorized' => false,
			'base_uri' => '',
			'query' => [],
			'headers' => [],
			'timeout' => 0,
			'connection_timeout' => 300,
		];
		// auth string is array of 0: user, 1: password, 2: auth type
		if (!empty($config['auth']) && is_array($config['auth'])) {
			// base auth sets the header actually
			$type = isset($config['auth'][2]) ? strtolower($config['auth'][2]) : 'basic';
			$userpwd = $config['auth'][0] . ':' . $config['auth'][1];
			switch ($type) {
				case 'basic':
					$this->auth_basic_header = 'Basic ' . base64_encode(
						$userpwd
					);
					// if (!isset($config['headers']['Authorization'])) {
					// 	$config['headers']['Authorization'] = 'Basic ' . base64_encode(
					// 		$userpwd
					// 	);
					// }
					break;
				case 'digest':
					$this->auth_type = CURLAUTH_DIGEST;
					$this->auth_userpwd = $userpwd;
					break;
				case 'ntlm':
					$this->auth_type = CURLAUTH_NTLM;
					$this->auth_userpwd = $userpwd;
					break;
			}
		}
		// only set if bool
		if (
			!isset($config['exception_on_not_authorized']) ||
			!is_bool($config['exception_on_not_authorized'])
		) {
			$config['exception_on_not_authorized'] = false;
		}
		if (!empty($config['base_uri'])) {
			if (($parsed_base_uri = $this->parseUrl($config['base_uri'])) !== false) {
				$this->parsed_base_uri = $parsed_base_uri;
				$config['base_uri'] = $config['base_uri'];
			}
		}
		// general headers
		if (!empty($config['headers'])) {
			// seat the key lookup with lower keys
			foreach (array_keys($config['headers']) as $key) {
				if (isset($this->headers_named[strtolower((string)$key)])) {
					continue;
				}
				$this->headers_named[strtolower((string)$key)] = (string)$key;
			}
		}
		// timeout (must be numeric)
		if (!empty($config['timeout']) && !is_numeric($config['timeout'])) {
			$config['timeout'] = 0;
		}
		if (!empty($config['connection_timeout']) && !is_numeric($config['connection_timeout'])) {
			$config['connection_timeout'] = 300;
		}

		$this->config = array_merge($default_config, $config);
	}

	// MARK: parse and build url

	/**
	 * From: https://github.com/guzzle/psr7/blob/a70f5c95fb43bc83f07c9c948baa0dc1829bf201/src/Uri.php#L106C5-L132C6
	 * guzzle/psr7::parse
	 *
	 * convert the url to valid sets
	 *
	 * @param  string $url
	 * @return array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string}|false
	 */
	private function parseUrl(string $url): array|false
	{
		// If IPv6
		$prefix = '';
		if (preg_match('%^(.*://\[[0-9:a-f]+\])(.*?)$%', $url, $matches)) {
			/** @var array{0:string, 1:string, 2:string} $matches */
			$prefix = $matches[1];
			$url = $matches[2];
		}

		/** @var string $encodedUrl */
		$encodedUrl = preg_replace_callback(
			'%[^:/@?&=#]+%usD',
			static function ($matches) {
				return urlencode($matches[0]);
			},
			$url
		);

		$result = parse_url($prefix . $encodedUrl);

		if ($result === false) {
			return false;
		}

		/** @var callable $caller */
		$caller = 'urldecode';
		return array_map($caller, $result);
	}

	/**
	 * build back the URL based on the parsed URL scheme
	 * NOTE: this is only a sub implementation
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param  array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string} $parsed_url
	 * @param  bool $remove_until_slash [default=false]
	 * @param  bool $add_query [default=false]
	 * @param  bool $add_fragment [default=false]
	 * @return string
	 * phpcs:enable Generic.Files.LineLength
	 */
	private function buildUrl(
		array $parsed_url,
		bool $remove_until_slash = false,
		bool $add_query = false,
		bool $add_fragment = false
	): string {
		$url = '';
		// scheme has :
		if (!empty($parsed_url['scheme'])) {
			$url .= $parsed_url['scheme'] . ':';
		}
		// host + port = authority
		if (!empty($parsed_url['host'])) {
			$url .= '//';
			$url .= $parsed_url['host'] ?? '';
			if (!empty($parsed_url['port'])) {
				$url .= ':' . $parsed_url['port'];
			}
		}
		// remove the last part "/.." because we do not end with "/"
		if ($remove_until_slash) {
			$url_path = $parsed_url['path'] ?? '';
			if (($lastSlashPos = strrpos($url_path, '/')) !== false) {
				$url .=  substr($url_path, 0, $lastSlashPos + 1);
			}
		} else {
			$url .= $parsed_url['path'] ?? '';
		}
		// only on demand
		if ($add_query && !empty($parsed_url['query'])) {
			$url .= '?' . $parsed_url['query'];
		}
		if ($add_fragment && !empty($parsed_url['fragment'])) {
			$url .= '#' . $parsed_url['fragment'];
		}
		return $url;
	}

	// MARK: query, params and headers convert

	/**
	 * Build URL with base url and parameters
	 *
	 * @param  string                    $url_req to send
	 * @param  null|array<string,string> $query   any optional parameters to send
	 * @return string                             the fully build URL
	 */
	private function buildQuery(string $url_req, null|array $query = null): string
	{
		if (($parsed_url = $this->parseUrl($url_req)) !== false) {
			$this->parsed_url = $parsed_url;
		}
		$url = $url_req;
		if (
			!empty($this->config['base_uri']) &&
			empty($this->parsed_url['scheme'])
		) {
			if (str_ends_with($this->config['base_uri'], '/')) {
				$url = $this->config['base_uri'] . $url_req;
			} else {
				// remove until last / and add url, strip leading / if set
				// remove last "/" part until we are at the domain
				// if we do not start with http(s):// then assume blank
				// NOTE any fragments or params will get dropped, only path will remain
				$url = $this->buildUrl($this->parsed_base_uri, remove_until_slash: true) . $url_req;
			}
			if (($parsed_url = $this->parseUrl($url)) !== false) {
				$this->parsed_url = $parsed_url;
			}
		}
		// build query with global query
		// any query set in the base_url or url_req will be overwritten
		if (!empty($this->config['query'])) {
			// add current query if set
			// for params: if foo[0] then we ADD as php array type
			// note that this has to be done on the user side, we just merge and local overrides global
			$query = array_merge($this->config['query'], $query ?? []);
		}
		if (is_array($query)) {
			$query = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
		}
		// add the params to the url
		if (!empty($query)) {
			// if the url_url has a query or a a fragment,
			// we need to build that url new
			// $parsed_url = false;
			if (!empty($this->parsed_url['query']) || !empty($this->parsed_url['framgent'])) {
				$url = $this->buildUrl($this->parsed_url);
			}
			$url .= '?' . $query;
			// fragments are ignored
			// if (!empty($this->parsed_url['fragment'])) {
			// 	$url .= '#' . $parsed_url['fragment'];
			// }
		}
		// parse again with current url
		if ($url != $url_req) {
			if (($parsed_url = $this->parseUrl($url)) !== false) {
				$this->parsed_url = $parsed_url;
			}
		}

		return $url;
	}

	/**
	 * Convert array body data to json type string
	 *
	 * @param  string|array<string,mixed> $body
	 * @return string
	 */
	private function convertPayloadData(string|array $body): string
	{
		// convert to string as JSON block if it is an array
		if (is_array($body)) {
			$params = Json::jsonConvertArrayTo($body);
		}
		return $params ?? '';
	}

	/**
	 * header convert from array key -> value to string list
	 * if the key value is numeric, it is assumed this is an array string list only
	 * Note: this should not be the case
	 *
	 * @param  array<string,string|array<string>> $headers
	 * @return array<string>
	 */
	private function convertHeaders(array $headers): array
	{
		$return_headers = [];
		foreach ($headers as $key => $value) {
			if (!is_string($key)) {
				// TODO: throw error
				continue;
			}
			// bad if not valid header key
			if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/D', $key)) {
				// TODO throw error
				continue;
			}
			// if value is array, join to string
			if (is_array($value)) {
				$value = join(', ', $value);
			}
			$value = trim((string)$value, " \t");
			// header values must be valid
			if (!preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/D', $value)) {
				// TODO throw error
				continue;
			}
			$return_headers[] = (string)$key . ':' .  $value;
		}
		// remove empty entries
		return $return_headers;
	}

	/**
	 * default headers that are always set
	 * Authorization
	 * User-Agent
	 *
	 * @return array<string,string>
	 */
	private function buildDefaultHeaders(): array
	{
		$headers = [];
		// add auth header if set
		if (!empty($this->auth_basic_header)) {
			$headers['Authorization'] = $this->auth_basic_header;
		}
		// always add HTTP_HOST and HTTP_USER_AGENT
		if (!isset($headers[strtolower('User-Agent')])) {
			$headers['User-Agent'] = 'CoreLibsUrlRequestCurl/' . self::MAJOR_VERSION;
		}
		return $headers;
	}

	/**
	 * Build headers, combine with global headers of they are set
	 *
	 * @param  null|array<string,string|array<string>> $headers
	 * @return array<string,string|array<string>>
	 */
	private function buildHeaders(null|array $headers): array
	{
		// if headers is null, return empty headers, do not set config default headers
		// but the automatic set User-Agent and Authorization headers are always set
		if ($headers === null) {
			return $this->buildDefaultHeaders();
		}
		// merge master headers with sub headers, sub headers overwrite master headers
		if (!empty($this->config['headers'])) {
			// we need to build the current headers as a lookup table
			$headers_lookup = [];
			foreach (array_keys($headers) as $key) {
				$headers_lookup[strtolower((string)$key)] = (string)$key;
			}
			// add config headers if not set in local header
			foreach ($this->headers_named as $header_key => $key) {
				// is set local, use this, else use global
				if (isset($headers_lookup[$header_key])) {
					continue;
				}
				$headers[$key] = $this->config['headers'][$key];
			}
		}
		$headers = array_merge($headers, $this->buildDefaultHeaders());
		return $headers;
	}

	// MARK: main curl request

	/**
	 * Overall request call
	 *
	 * @param  string                                  $type    get, post, pathc, put, delete:
	 *                                                          if not set or invalid throw error
	 * @param  string                                  $url     The URL being requested,
	 *                                                          including domain and protocol
	 * @param  null|array<string,string|array<string>> $headers [default=[]] Headers to be used in the request
	 * @param  null|array<string,string>               $query   [default=null] Optinal query parameters
	 * @param  null|string|array<string,mixed>         $body    [default=null] Data body, converted to JSON
	 * @return array{code:string,headers:array<string,array<string>>,content:string}
	 * @throws \RuntimeException if type param is not valid
	 */
	private function curlRequest(
		string $type,
		string $url,
		null|array $headers = [],
		null|array $query = null,
		null|string|array $body = null
	): array {
		$this->url = $this->buildQuery($url, $query);
		$this->headers = $this->convertHeaders($this->buildHeaders($headers));
		if (!in_array($type, self::VALID_REQUEST_TYPES)) {
			throw new RuntimeException(
				json_encode([
					'status' => 'FAILURE',
					'code' => 'C003',
					'type' => 'InvalidRequestType',
					'message' => 'Invalid request type set: ' . $type,
					'context' => [
						'type' => $type,
						'url' => $this->url,
						'headers' => $this->headers,
					],
				]) ?: '',
				0,
			);
		}
		// init curl handle
		$handle = $this->handleCurleInit($this->url);
		// set the standard curl options
		$this->setCurlOptions($handle, $this->headers);
		// for post we set POST option
		if ($type == "post") {
			curl_setopt($handle, CURLOPT_POST, true);
		} elseif (in_array($type, self::CUSTOM_REQUESTS)) {
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, strtoupper($type));
		}
		// set body data if not null, will send empty [] for empty data
		if (in_array($type, self::HAVE_POST_FIELDS) && $body !== null) {
			curl_setopt($handle, CURLOPT_POSTFIELDS, $this->convertPayloadData($body));
		}
		// reset all headers before we start the call
		$this->received_headers = [];
		// run curl execute
		$http_result = $this->handleCurlExec($handle);
		// for debug
		// print "CURLINFO_HEADER_OUT: <pre>" . curl_getinfo($handle, CURLINFO_HEADER_OUT) . "</pre>";
		// get response code and bail on not authorized
		$http_response = $this->handleCurlResponse($http_result, $handle);
		// close handler
		$this->handleCurlClose($handle);
		// return response and result
		return [
			'code' => (string)$http_response,
			'headers' => $this->received_headers,
			'content' => (string)$http_result
		];
	}

	// MARK: curl init

	/**
	 * Handel curl init and errors
	 *
	 * @param  string            $url
	 * @return \CurlHandle
	 * @throws \RuntimeException if curl could not be initialized
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

	// MARK: set curl options and header collector

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
		// for not Basic auth, basic auth sets its own header
		if (!empty($this->auth_type) && !empty($this->auth_userpwd)) {
			curl_setopt($handle, CURLOPT_HTTPAUTH, $this->auth_type);
			curl_setopt($handle, CURLOPT_USERPWD, $this->auth_userpwd);
		}
		if ($headers !== []) {
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		}
		// curl_setopt($handle, CURLOPT_FAILONERROR, true);
		// return response as string and not just HTTP_OK
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		// for debug only
		curl_setopt($handle, CURLINFO_HEADER_OUT, true);
		// curl_setopt($handle, CURLOPT_HEADER, true);
		// collect the current request headers
		curl_setopt($handle, CURLOPT_HEADERFUNCTION, [$this, 'collectCurlHttpHeaders']);
		// if any timeout <1
		$timeout_requires_no_signal = false;
		// if we have a timeout signal
		if (!empty($this->config['timeout'])) {
			$timeout_requires_no_signal |= $this->config['timeout'] < 1;
			curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->config['timeout'] * 1000);
		}
		if (!empty($this->config['connection_timeout'])) {
			$timeout_requires_no_signal |= $this->config['connection_timeout'] < 1;
			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $this->config['connection_timeout'] * 1000);
		}
		if ($timeout_requires_no_signal && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			curl_setopt($handle, CURLOPT_NOSIGNAL, true);
		}
	}

	/**
	 * Collect HTTP headers
	 * They will be reset before each call
	 *
	 * @param  \CurlHandle $curl   current curl handle
	 * @param  string      $header header string to parse
	 * @return int                 size of current line of header
	 */
	private function collectCurlHttpHeaders(\CurlHandle $curl, string $header): int
	{
		$len = strlen($header);
		$header = explode(':', $header, 2);
		if (count($header) < 2) {
			// ignore invalid headers
			return $len;
		}
		$this->received_headers[strtolower(trim($header[0]))][] = trim($header[1]);
		return $len;
	}

	// MARK: Curl Exception handler

	/**
	 * handles any CURL execute and on error throws a correct error message
	 *
	 * @param  \CurlHandle $handle Curl handler
	 * @return string              Return content as string, if False will throw exception
	 *                             will only return HTTP_OK if CURLOPT_RETURNTRANSFER is turned off
	 * @throws \RuntimeException if the connection had an error
	 */
	private function handleCurlExec(\CurlHandle $handle): string
	{
		// execute query
		$http_result = curl_exec($handle);
		if ($http_result === true) {
			// only if CURLOPT_RETURNTRANSFER
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

	// MARK: curl response handler

	/**
	 * Handle curl response and not auth 401 errors
	 *
	 * @param  string      $http_result
	 * @param  \CurlHandle $handle
	 * @return string                   http response code
	 * @throws \RuntimeException Auth error
	 */
	private function handleCurlResponse(
		string $http_result,
		\CurlHandle $handle
	): string {
		$http_response = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
		if (
			empty($this->config['exception_on_not_authorized']) ||
			$http_response !== self::HTTP_NOT_AUTHORIZED
		) {
			return (string)$http_response;
		}
		$err = curl_errno($handle);
		// extract all the error codes
		$result_ar = json_decode((string)$http_result, true);

		$url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);

		// throw Error here with all codes
		throw new RuntimeException(
			json_encode([
				'status' => 'ERROR',
				'code' => $http_response,
				'type' => 'UnauthorizedRequest',
				'message' => 'Request could not be finished successfully because of an authorization error',
				'context' => [
					'url' => $url,
					'result' => $result_ar,
				],
			]) ?: '',
			$err
		);
	}

	/**
	 * close the current curl handle
	 *
	 * @param  \CurlHandle $handle
	 * @return void
	 */
	private function handleCurlClose(\CurlHandle $handle): void
	{
		curl_close($handle);
	}

	// *********************************************************************
	// MARK: PUBLIC METHODS
	// *********************************************************************

	/**
	 * Convert an array with header strings like "foo: bar" to the interface
	 * needed "foo" => "bar" type
	 * Skips entries that are already in key => value type, by checking if the
	 * key is a not a number
	 *
	 * @param  array<int|string,string> $headers
	 * @return array<string,string>
	 * @throws \UnexpectedValueException on duplicate header key
	 */
	public function prepareHeaders(array $headers): array
	{
		$return_headers = [];
		foreach ($headers as $header_key => $header) {
			// skip if header key is not numeric
			if (!is_numeric($header_key)) {
				$return_headers[$header_key] = $header;
				continue;
			}
			list($_key, $_value) = explode(':', $header);
			if (array_key_exists($_key, $return_headers)) {
				// raise exception if key already exists
				throw new \UnexpectedValueException(
					json_encode([
						'status' => 'ERROR',
						'code' => 'C004',
						'type' => 'DuplicatedArrayKey',
						'message' => 'Key already exists in the headers',
						'context' => [
							'key' => $_key,
							'headers' => $headers,
							'return_headers' => $return_headers,
						],
					]) ?: '',
					1
				);
			}
			$return_headers[$_key] = $_value;
		}
		return $return_headers;
	}

	// MARK: get class vars

	/**
	 * get the config array with all settings
	 *
	 * @return array<string,mixed> all current config settings
	 */
	public function getConfig(): array
	{
		return $this->config;
	}

	/**
	 * Return the full url as it was sent
	 *
	 * @return string url sent
	 */
	public function getUrlSent(): string
	{
		return $this->url;
	}

	/**
	 * get the parsed url
	 *
	 * @return array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string}
	 */
	public function getUrlParsedSent(): array
	{
		return $this->parsed_url;
	}

	/**
	 * Return the full headers as they where sent
	 *
	 * @return array<string,string>
	 */
	public function getHeadersSent(): array
	{
		return $this->headers;
	}

	// MARK: set/remove for global headers

	/**
	 * set, add or overwrite header
	 * On default this will overwrite header, and not set
	 *
	 * @param  array<string,string|array<string>> $header
	 * @param  bool                               $add [default=false] if set will add header to existing value
	 * @return void
	 */
	public function setHeaders(array $header, bool $add = false): void
	{
		foreach ($header as $key => $value) {
			// check header previously set
			if (isset($this->headers_named[strtolower($key)])) {
				$header_key = $this->headers_named[strtolower($key)];
				if ($add) {
					// for this add we always add array on the right side
					if (!is_array($value)) {
						$value = (array)$value;
					}
					// if not array, rewrite entry to array
					if (!is_array($this->config['headers'][$header_key])) {
						$this->config['headers'][$header_key] = [
							$this->config['headers'][$header_key]
						];
					}
					$this->config['headers'][$header_key] = array_merge(
						$this->config['headers'][$header_key],
						$value
					);
				} else {
					$this->config['headers'][$header_key] = $value;
				}
			} else {
				$this->headers_named[strtolower($key)] = $key;
				$this->config['headers'][$key] = $value;
			}
		}
	}

	/**
	 * remove header entry
	 * if key is only set then match only key, if both are set both sides must match
	 *
	 * @param  array<string,null|string|array<string>> $remove_headers
	 * @return void
	 */
	public function removeHeaders(array $remove_headers): void
	{
		foreach ($remove_headers as $key => $value) {
			if (!isset($this->headers_named[strtolower($key)])) {
				continue;
			}
			$header_key = $this->headers_named[strtolower($key)];
			if (!isset($this->config['headers'][$header_key])) {
				continue;
			}
			// full remove
			if (
				empty($value) ||
				(
					(
						// array both sides = equal
						// string both sides = equal
						(is_array($value) && is_array($this->config['headers'][$header_key])) ||
						(is_string($value) && is_string($this->config['headers'][$header_key]))
					) &&
					$value == $this->config['headers'][$header_key]
				)
			) {
				unset($this->config['headers'][$header_key]);
				unset($this->headers_named[$header_key]);
			} elseif (
				// string value, array keys = in
				// or both array and not a full match in the one before
				(is_string($value) || is_array($value)) &&
				is_array($this->config['headers'][$header_key])
			) {
				// part remove of key, value must be array
				if (!is_array($value)) {
					$value = [$value];
				}
				// array values so we rewrite the key pos
				$this->config['headers'][$header_key] = array_values(array_diff(
					$this->config['headers'][$header_key],
					$value
				));
			}
		}
	}

	// MARK: update/set base url

	/**
	 * Update or set the base url set
	 * if empty will unset the base url
	 *
	 * @param  string $base_uri
	 * @return void
	 */
	public function setBaseUri(string $base_uri): void
	{
		$this->config['base_uri'] = $base_uri;
		$this->parsed_base_uri = [];
		if (!empty($base_uri)) {
			if (($parsed_base_uri = $this->parseUrl($base_uri)) !== false) {
				$this->parsed_base_uri = $parsed_base_uri;
			}
		}
	}

	// MARK: main public call interface

	/**
	 * combined set call for any type of request with options type parameters
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param  string $type
	 * @param  string $url
	 * @param  array{headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<string,mixed>} $options
	 * @return array{code:string,headers:array<string,array<string>>,content:string} Result code, headers and content as array, content is json
	 * @throws \UnexpectedValueException on missing body data when body data is needed
	 * phpcs:enable Generic.Files.LineLength
	 */
	public function request(string $type, string $url, array $options = []): array
	{
		// can have
		// - headers
		// - query
		// depending on type, must have (post/put/patch), optional for (delete)
		// - body
		$type = strtolower($type);
		// check if we need a payload data set, set empty on not set
		if (in_array($type, self::MANDATORY_POST_FIELDS) && !isset($options['body'])) {
			$options['body'] = [];
		}
		return $this->curlRequest(
			$type,
			$url,
			!array_key_exists('headers', $options) ? [] : $options['headers'],
			$options['query'] ?? null,
			$options['body'] ?? null
		);
	}
}

// __END__
