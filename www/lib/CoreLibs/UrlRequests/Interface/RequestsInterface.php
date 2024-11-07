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
	 * get the config array with all settings
	 *
	 * @return array<string,mixed> all current config settings
	 */
	public function getConfig(): array;

	/**
	 * Return the full url as it was sent
	 *
	 * @return string url sent
	 */
	public function getUrlSent(): string;

	/**
	 * get the parsed url
	 *
	 * @return array{scheme?:string,user?:string,host?:string,port?:string,path?:string,query?:string,fragment?:string,pass?:string}
	 */
	public function getUrlParsedSent(): array;

	/**
	 * Return the full headers as they where sent
	 *
	 * @return array<string,string>
	 */
	public function getHeadersSent(): array;

	/**
	 * set, add or overwrite header
	 * On default this will overwrite header, and not set
	 *
	 * @param  array<string,string|array<string>> $header
	 * @param  bool                               $add [default=false] if set will add header to existing value
	 * @return void
	 */
	public function setHeaders(array $header, bool $add = false): void;

	/**
	 * remove header entry
	 * if key is only set then match only key, if both are set both sides must match
	 *
	 * @param  array<string,string> $remove_headers
	 * @return void
	 */
	public function removeHeaders(array $remove_headers): void;

	/**
	 * Update the base url set, if empty will unset the base url
	 *
	 * @param  string $base_uri
	 * @return void
	 */
	public function setBaseUri(string $base_uri): void;

	/**
	 * combined set call for any type of request with options type parameters
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param  string $type
	 * @param  string $url
	 * @param  array{auth?:null|array{0:string,1:string,2:string},headers?:null|array<string,string|array<string>>,query?:null|array<string,string>,body?:null|string|array<mixed>,http_errors?:null|bool} $options
	 * @return array{code:string,headers:array<string,array<string>>,content:string} Result code, headers and content as array, content is json
	 * @throws \UnexpectedValueException on missing body data when body data is needed
	 * phpcs:enable Generic.Files.LineLength
	 */
	public function request(string $type, string $url, array $options = []): array;
}

// __END__
