<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for UrlRequests\Curl
 * @coversDefaultClass \CoreLibs\UrlRequests\Curl
 * @testdox \CoreLibs\UrlRequests\Curl method tests
 */
final class CoreLibsUrlRequestsCurlTest extends TestCase
{
	// we must launch some small test web server for the response tests

	// public static function setUpBeforeClass(): voidx

	private string $url_basic = '';
	private string $url_basic_start = '';
	private string $url_basic_end = '';
	private array $default_config = [
		'http_errors' => true,
		'base_uri' => '',
		'query' => [],
		'headers' => [],
		'timeout' => 0,
		'connection_timeout' => 300,
	];

	/**
	 * check if we have some backend for testing
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		// check if local http servers
		// or special started:
		// php -S localhost:30999 \
		// -t /storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/4dev/tests/AAASetupData/requests/
		foreach (
			[
				// main dev
				'https://soba.egplusww.jp/developers/clemens/core_data/php_libraries/trunk/'
					. '4dev/tests/AAASetupData/requests/http_requests.php',
				// composer package
				'https://soba.egplusww.jp/developers/clemens/core_data/composer-packages/'
					. 'CoreLibs-Composer-All/test/phpunit/AAASetupData/requests/http_requests.php',
				// if we run php -S localhost:30999 -t [see below]
				// dev: /storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/4dev/tests/AAASetupData/requests/
				// composer: /storage/var/www/html/developers/clemens/core_data/composer-packages/CoreLibs-Composer-All/test/phpunit/AAASetupData
				'localhost:30999/http_requests.php',
			] as $url
		) {
			$handle = curl_init($url);
			if ($handle === false) {
				continue;
			}
			$this->url_basic = $url;
			// split out the last / part for url set test
			curl_close($handle);
			// print "Open: $url\n";
			break;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function tearDown(): void
	{
		// end some httpserver
	}

	/**
	 * Undocumented function
	 *
	 * @param  string $url
	 * @return array
	 */
	private function splitUrl(string $url): array
	{

		if (($lastSlashPos = strrpos($url, '/')) !== false) {
			return [
				substr($url, 0, $lastSlashPos + 1),
				substr($url, $lastSlashPos + 1, $lastSlashPos + 1)
			];
		} else {
			return [0 => '', 1 => ''];
		}
	}

	// MARK: class setup tests

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerUrlRequestsCurlSetup(): array
	{
		return [
			// MARK: base config
			'no config' => [
				'config' => null,
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => null,
				'expected_change' => null,
			],
			'setup all possible configs' => [
				'config' => [
					'auth' => ['user', 'passowrd', 'Basic'],
					'http_errors' => false,
					'base_uri' => 'http://foo.bar.com',
					'headers' => [
						'something' => 'other',
					],
					'query' => [
						'foo' => 'bar',
					],
					'timeout' => 5,
					'connection_timeout' => 10,
				],
				'expected_set' => [
					'auth' => ['user', 'passowrd', 'Basic'],
					'http_errors' => false,
					'base_uri' => 'http://foo.bar.com',
					'headers' => [
						'something' => 'other',
					],
					'query' => [
						'foo' => 'bar',
					],
					'timeout' => 5,
					'connection_timeout' => 10,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => null,
				'expected_change' => null,
			],
			// MARK: base url
			'setup base_uri only' => [
				'config' => [
					'base_uri' => 'http://bar.foo.com'
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => 'http://bar.foo.com',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => null,
				'expected_change' => null,
			],
			'replace base_uri' => [
				'config' => [
					'base_uri' => 'http://bar.foo.com'
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => 'http://bar.foo.com',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => 'http://bar.baz.com',
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => null,
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => 'http://bar.baz.com',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			// MARK: set headers
			'set header new' =>  [
				'config' => null,
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => [
					'new-header' => 'abc'
				],
				'set_header_add' => false,
				'remove_header' => null,
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'new-header' => 'abc',
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'set header overwrite' => [
				'config' => [
					'headers' => [
						'existing-entry' => 'foo'
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'existing-entry' => 'foo'
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => [
					'existing-entry' => 'bar'
				],
				'set_header_add' => false,
				'remove_header' => null,
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'existing-entry' => 'bar'
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'set header add' => [
				'config' => [
					'headers' => [
						'existing-entry' => 'foo'
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'existing-entry' => 'foo'
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => [
					'existing-entry' => 'bar'
				],
				'set_header_add' => true,
				'remove_header' => null,
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'existing-entry' => ['foo', 'bar']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			// MARK: test remove header
			'remove header string, full match' => [
				'config' => [
					'headers' => [
						'remove-entry' => 'foo'
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => 'foo'
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => [
					'remove-entry' => 'foo'
				],
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'remove header string, key match only' => [
				'config' => [
					'headers' => [
						'remove-entry' => 'foo'
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => 'foo'
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => [
					'remove-entry' => null
				],
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'remove header array, key match' => [
				'config' => [
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => [
					'remove-entry' => null
				],
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'remove header array, string match' => [
				'config' => [
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => [
					'remove-entry' => 'foo'
				],
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => ['bar', 'baz']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
			'remove header array, array match' => [
				'config' => [
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
				],
				'expected_set' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => ['foo', 'bar', 'baz']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
				'new_base_uri' => null,
				'set_header' => null,
				'set_header_add' => null,
				'remove_header' => [
					'remove-entry' => ['foo', 'bar',]
				],
				'expected_change' => [
					'http_errors' => true,
					'base_uri' => '',
					'query' => [],
					'headers' => [
						'remove-entry' => ['baz']
					],
					'timeout' => 0,
					'connection_timeout' => 300,
				],
			],
		];
	}

	// MARK: setup/config

	/**
	 * set setup + header, base uri change
	 *
	 * @covers ::Curl
	 * @covers ::setBaseUri
	 * @covers ::addHeader
	 * @covers ::removeHEader
	 * @dataProvider providerUrlRequestsCurlSetup
	 * @testdox UrlRequests\Curl Class setup tasks [$_dataName]
	 *
	 * @param  null|array  $config
	 * @param  array       $expected
	 * @param  null|string $new_base_uri
	 * @param  null|array  $set_header
	 * @param  null|bool   $set_header_add
	 * @param  null|array  $remove_header
	 * @param  null|array  $expected_change
	 * @return void
	 */
	public function testUrlRequestsCurlSetupConfig(
		null|array $config,
		array $expected_set,
		null|string $new_base_uri,
		null|array $set_header,
		null|bool $set_header_add,
		null|array $remove_header,
		null|array $expected_change
	): void {
		// empty new
		if ($config === null) {
			$curl = new \CoreLibs\UrlRequests\Curl();
		} else {
			$curl = new \CoreLibs\UrlRequests\Curl($config);
		};
		// if ($new_base_uri === null && $set_header === null && $remove_header === null) {
		// }
		$this->assertEquals($expected_set, $curl->getConfig(), 'Class setup config mismatch');
		if ($new_base_uri !== null) {
			$curl->setBaseUri($new_base_uri);
			$this->assertEquals($expected_change, $curl->getConfig(), 'new base_uri not matching');
		}
		if ($set_header !== null) {
			if ($set_header_add !== null) {
				$curl->setHeaders($set_header, $set_header_add);
			} else {
				$curl->setHeaders($set_header);
			}
			$this->assertEquals($expected_change, $curl->getConfig(), 'new headers not matching');
		}
		if ($remove_header !== null) {
			$curl->removeHeaders($remove_header);
			$this->assertEquals($expected_change, $curl->getConfig(), 'removed headers not matching');
		}
	}

	// MARK: request call tests

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerUrlRequestsCurlRequestBuild(): array
	{
		return [
			// MARK: config overwrite
			// this would be:
			// - base url + add url
			// - base url + replace url
			'base url + add url' => [
				'type' => 'get',
				'config' => [
					"base_uri" => "URL_START"
				],
				'url' => "URL_END",
				'options' => null,
				'sent_url' => "URL_FULL",
				'sent_url_parsed' => null,
				'sent_headers' => [
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
			'base url + replace url' => [
				'type' => 'get',
				'config' => [
					"base_uri" => "URL_START"
				],
				'url' => "URL_FULL",
				'options' => null,
				'sent_url' => "URL_FULL",
				'sent_url_parsed' => null,
				'sent_headers' => [
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
			// - base header + add header
			// - base header + reset header (null)
			// - base query + add query
			'base header + add header' => [
				'type' => 'get',
				'config' => [
					"headers" => [
						"header-one" => "one",
					]
				],
				'url' => null,
				'options' => [
					"headers" => [
						"header-two" => "two",
					]
				],
				'sent_url' => null,
				'sent_url_parsed' => null,
				'sent_headers' => [
					"header-two:two",
					"header-one:one",
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
			'base header + reset header' => [
				'type' => 'get',
				'config' => [
					"headers" => [
						"header-one" => "one",
					]
				],
				'url' => null,
				'options' => [
					"headers" => null
				],
				'sent_url' => null,
				'sent_url_parsed' => null,
				'sent_headers' => [
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
			'base header + add header (same)' => [
				'type' => 'get',
				'config' => [
					"headers" => [
						"header-one" => "one",
					]
				],
				'url' => null,
				'options' => [
					"headers" => [
						"header-one" => "one",
						"header-two" => "two",
					]
				],
				'sent_url' => null,
				'sent_url_parsed' => null,
				'sent_headers' => [
					"header-two:two",
					"header-one:one",
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
			'base header + add header (replace)' => [
				'type' => 'get',
				'config' => [
					"headers" => [
						"header-one" => "one",
					]
				],
				'url' => null,
				'options' => [
					"headers" => [
						"header-one" => "three",
						"header-two" => "two",
					]
				],
				'sent_url' => null,
				'sent_url_parsed' => null,
				'sent_headers' => [
					"header-two:two",
					"header-one:three",
					"User-Agent:CoreLibsUrlRequestCurl/1",
				],
				'return_code' => "200",
				'return_content' => ''
			],
		];
	}

	// MARK: test call overwrite

	/**
	 * request build tests
	 *
	 * @covers ::request
	 * @dataProvider providerUrlRequestsCurlRequestBuild
	 * @testdox UrlRequests\Curl call with data merge [$_dataName]
	 *
	 * @param  string      $type
	 * @param  array|null  $config
	 * @param  string|null $url
	 * @param  array|null  $options
	 * @param  string|null $sent_url
	 * @param  array|null  $sent_url_parsed
	 * @param  array       $sent_headers
	 * @param  string      $return_code
	 * @param  string      $return_content
	 * @return void
	 */
	public function testUrlRequestsCurlRequestBuild(
		string $type,
		?array $config,
		?string $url,
		?array $options,
		?string $sent_url,
		?array $sent_url_parsed,
		array $sent_headers,
		string $return_code,
		string $return_content
	) {
		if (!$this->url_basic) {
			$this->markTestSkipped('No backend interface setup for testing: GET');
		}
		if ($url) {
			list($url_start, $url_end) = $this->splitUrl($this->url_basic);
			$config['base_uri'] = str_replace('URL_START', $url_start, $config['base_uri']);
			$url = str_replace('URL_END', $url_end, $url);
			$url = str_replace('URL_FULL', $this->url_basic, $url);
			$sent_url = str_replace('URL_FULL', $this->url_basic, $sent_url);
		}
		// init without or with config
		if ($config === null) {
			$curl = new \CoreLibs\UrlRequests\Curl();
		} else {
			$curl = new \CoreLibs\UrlRequests\Curl($config);
		};
		// set url
		if ($url === null) {
			$url = $this->url_basic;
		}
		// options
		if (is_array($options)) {
			$respone = $curl->request($type, $url, $options);
		} else {
			$respone = $curl->request($type, $url);
		}
		// headers
		$this->assertEqualsCanonicalizing(
			$sent_headers,
			$curl->getHeadersSent(),
			'Headers do not metch'
		);
		// url
		if ($sent_url) {
			$this->assertEquals(
				$sent_url,
				$curl->getUrlSent(),
				'Sent URL does not match'
			);
		}
		// check return code
		$this->assertEquals(
			$return_code,
			$respone['code'],
			'Return code not matching'
		);
	}

	// MARK: test basic call provider

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerUrlRequestsCurlRequest(): array
	{
		// phpcs:disable Generic.Files.LineLength
		// get and delete can have null body, but only get will never have a body
		$provider = [];
		// MARK: get
		foreach (['get'] as $type) {
			$provider["basic " . $type . ", no options"] = [
				'type' => $type,
				'options' => null,
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":[],"BODY":null}'
			];
			$provider["basic " . $type . ", query options"] = [
				'type' => $type,
				'options' => [
					"query" => ["foo" => "bar"],
				],
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":{"foo":"bar"},"BODY":null}'
			];
		}
		// MARK: delete
		foreach (['delete'] as $type) {
			// MARK: post
			$provider["basic " . $type . ", no options"] = [
				'type' => $type,
				'options' => null,
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":[],"BODY":null}'
			];
			$provider["basic " . $type . ", query options"] = [
				'type' => $type,
				'options' => [
					"query" => ["foo" => "bar"],
				],
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":{"foo":"bar"},"BODY":null}'
			];
			$provider["basic " . $type . ", query/body options"] = [
				'type' => $type,
				'options' => [
					"query" => ["foo" => "bar"],
					"body" => ["foobar" => "barbaz"],
				],
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":{"foo":"bar"},"BODY":{"foobar":"barbaz"}}'
			];
		}
		// MARK: post/put/patch
		foreach (['post', 'put', 'patch'] as $type) {
			// MARK: post
			$provider["basic " . $type . ", no options"] = [
				'type' => $type,
				'options' => null,
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":[],"BODY":[]}'
			];
			$provider["basic " . $type . ", query options"] = [
				'type' => $type,
				'options' => [
					"query" => ["foo" => "bar"],
				],
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":{"foo":"bar"},"BODY":[]}'
			];
			$provider["basic " . $type . ", query/body options"] = [
				'type' => $type,
				'options' => [
					"query" => ["foo" => "bar"],
					"body" => ["foobar" => "barbaz"],
				],
				'return_code' => "200",
				'return_content' => '{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1","HTTP_ACCEPT":"*\/*","HTTP_HOST":"soba.egplusww.jp"},"REQUEST_TYPE":"' . strtoupper($type) . '","PARAMS":{"foo":"bar"},"BODY":{"foobar":"barbaz"}}'
			];
		}
		return $provider;
		// phpcs:enable Generic.Files.LineLength
	}

	// MARK: test basic get/post/put/patch/delete

	/**
	 * requests tests
	 *
	 * @covers ::request
	 * @covers ::get
	 * @covers ::post
	 * @covers ::put
	 * @covers ::patch
	 * @covers ::delete
	 * @dataProvider providerUrlRequestsCurlRequest
	 * @testdox UrlRequests\Curl request calls [$_dataName]
	 *
	 * @param  string            $type
	 * @param  null|array        $options
	 * @param  string            $return_code
	 * @param  string            $return_content
	 * @return void
	 */
	public function testUrlRequestsCurlRequest(
		string $type,
		null|array $options,
		string $return_code,
		string $return_content
	) {
		if (!$this->url_basic) {
			$this->markTestSkipped('No backend interface setup for testing: GET');
		}
		$curl = new \CoreLibs\UrlRequests\Curl();
		// options
		if (is_array($options)) {
			$respone = $curl->request($type, $this->url_basic, $options);
		} else {
			$respone = $curl->request($type, $this->url_basic);
		}
		// print "REP: " . print_r($respone, true) . "\n";
		// check return code
		$this->assertEquals(
			$return_code,
			$respone['code'],
			'request: Return code not matching'
		);
		$this->assertEqualsCanonicalizing(
			json_decode($return_content, true),
			json_decode($respone['content'], true),
			'direct call Return content not matching'
		);
		switch ($type) {
			case 'get':
				if (is_array($options)) {
					$respone = $curl->get($this->url_basic, $options);
				} else {
					$respone = $curl->get($this->url_basic);
				}
				break;
			case 'post':
				if (is_array($options)) {
					$respone = $curl->post($this->url_basic, $options);
				} else {
					$respone = $curl->post($this->url_basic, []);
				}
				break;
			case 'put':
				if (is_array($options)) {
					$respone = $curl->put($this->url_basic, $options);
				} else {
					$respone = $curl->put($this->url_basic, []);
				}
				break;
			case 'patch':
				if (is_array($options)) {
					$respone = $curl->patch($this->url_basic, $options);
				} else {
					$respone = $curl->patch($this->url_basic, []);
				}
				break;
			case 'delete':
				if (is_array($options)) {
					$respone = $curl->delete($this->url_basic, $options);
				} else {
					$respone = $curl->delete($this->url_basic);
				}
				break;
		}
		// check return code
		$this->assertEquals(
			$return_code,
			$respone['code'],
			'direct call Return code not matching'
		);
		$this->assertEqualsCanonicalizing(
			json_decode($return_content, true),
			json_decode($respone['content'], true),
			'direct call Return content not matching'
		);
	}

	// TODO: multi requests with same base connection

	/**
	 * Undocumented function
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl multiple calls
	 *
	 * @return void
	 */
	public function testUrlRequestsCurlRequestMultiple()
	{
		$curl = new \CoreLibs\UrlRequests\Curl();
		// get
		$response = $curl->get($this->url_basic, [
			"headers" => ["first-call" => "get"],
			"query" => ["foo-get" => "bar"]
		]);
		$this->assertEquals("200", $response["code"], "multi call: get response code not matching");
		$this->assertEquals(
			'{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1",'
			. '"HTTP_FIRST_CALL":"get","HTTP_ACCEPT":"*\/*",'
			. '"HTTP_HOST":"soba.egplusww.jp"},'
			. '"REQUEST_TYPE":"GET",'
			. '"PARAMS":{"foo-get":"bar"},"BODY":null}',
			$response['content'],
			'multi call: get content not matching'
		);
		// post
		$response = $curl->post($this->url_basic, [
			"headers" => ["second-call" => "post"],
			"body" => ["foo-post" => "baz"]
		]);
		$this->assertEquals("200", $response["code"], "multi call: post response code not matching");
		$this->assertEquals(
			'{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1",'
			. '"HTTP_SECOND_CALL":"post","HTTP_ACCEPT":"*\/*",'
			. '"HTTP_HOST":"soba.egplusww.jp"},'
			. '"REQUEST_TYPE":"POST",'
			. '"PARAMS":[],"BODY":{"foo-post":"baz"}}',
			$response['content'],
			'multi call: post content not matching'
		);
		// delete
		$response = $curl->delete($this->url_basic, [
			"headers" => ["third-call" => "delete"],
		]);
		$this->assertEquals("200", $response["code"], "multi call: delete response code not matching");
		$this->assertEquals(
			'{"HEADERS":{"HTTP_USER_AGENT":"CoreLibsUrlRequestCurl\/1",'
			. '"HTTP_THIRD_CALL":"delete","HTTP_ACCEPT":"*\/*",'
			. '"HTTP_HOST":"soba.egplusww.jp"},'
			. '"REQUEST_TYPE":"DELETE",'
			. '"PARAMS":[],"BODY":null}',
			$response['content'],
			'multi call: delete content not matching'
		);
	}

	// MARK: auth header set via config

	/**
	 * Test auth settings and auth override
	 *
	 * @testdox UrlRequests\Curl auth test call
	 *
	 * @return void
	 */
	public function testUrlRequestsCurlAuthHeader()
	{
		$curl = new \CoreLibs\UrlRequests\Curl([
			"auth" => ["user", "pass", "basic"],
			"http_errors" => false,
		]);
		$curl->request('get', $this->url_basic);
		// check that the auth header matches
		$this->assertContains(
			"Authorization:Basic dXNlcjpwYXNz",
			$curl->getHeadersSent()
		);
		// if we sent new request with auth header, this one should not be used
		$curl->request('get', $this->url_basic, [
			"headers" => ["Authorization" => "Failed"]
		]);
		// check that the auth header matches
		$this->assertContains(
			"Authorization:Basic dXNlcjpwYXNz",
			$curl->getHeadersSent()
		);
		// override auth: reset
		$curl->request('get', $this->url_basic, [
			"auth" => null
		]);
		$this->assertNotContains(
			"Authorization:Basic dXNlcjpwYXNz",
			$curl->getHeadersSent()
		);
		// override auth: different auth
		$curl->request('get', $this->url_basic, [
			"auth" => ["user2", "pass2", "basic"]
		]);
		// check that the auth header matches
		$this->assertContains(
			"Authorization:Basic dXNlcjI6cGFzczI=",
			$curl->getHeadersSent()
		);
	}

	// MARK: test exceptions

	/**
	 * Exception:InvalidRequestType
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:InvalidRequestType
	 *
	 * @return void
	 */
	public function testExceptionInvalidRequestType(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/InvalidRequestType/");
		$curl->request('wrong', 'http://foo.bar.com');
	}

	/**
	 * Exception:InvalidHeaderKey
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:InvalidHeaderKey
	 *
	 * @return void
	 */
	public function testExceptionInvalidHeaderKey(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/InvalidHeaderKey/");
		$curl->request('get', $this->url_basic, [
			"headers" => [
				"(invalid-key)" => "key"
			]
		]);
	}

	/**
	 * Exception:InvalidHeaderValue
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:InvalidHeaderValue
	 *
	 * @return void
	 */
	public function testExceptionInvalidHeaderValue(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/InvalidHeaderValue/");
		$curl->request('get', $this->url_basic, [
			"headers" => [
				"invalid-value" => "\x19\x10"
			]
		]);
	}

	/**
	 * TODO: Exception:CurlInitError
	 *
	 * @testdox UrlRequests\Curl Exception:CurlInitError
	 *
	 * @return void
	 */
	// public function testExceptionCurlInitError(): void
	// {
	// 	$this->markTestSkipped('Test Exception CurlInitError not implemented');
	// }

	/**
	 * Exception:CurlExecError
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:CurlExecError
	 *
	 * @return void
	 */
	public function testExceptionCurlError(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/CurlExecError/");
		// invalid yrl
		$curl->request('get', 'as-4939345!#$%');
	}

	/**
	 * Exception:ClientError
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:ClientError
	 *
	 * @return void
	 */
	public function testExceptionBadRequest(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl(["http_errors" => true]);
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/ClientError/");
		$curl->request('get', $this->url_basic, [
			"headers" => [
				"Authorization" => "schmalztiegel",
				"RunAuthTest" => "yes",
			]
		]);
	}

	/**
	 * Exception:ClientError
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:ClientError on call enable
	 *
	 * @return void
	 */
	public function testExceptionBadRequestEnable(): void
	{
		$curl = new \CoreLibs\UrlRequests\Curl(["http_errors" => false]);
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/ClientError/");
		$curl->request('get', $this->url_basic, [
			"headers" => [
				"Authorization" => "schmalztiegel",
				"RunAuthTest" => "yes",
			],
			"http_errors" => true
		]);
	}

	/**
	 * Exception:ClientError
	 *
	 * @covers ::request
	 * @testdox UrlRequests\Curl Exception:ClientError unset on call
	 *
	 * @return void
	 */
	public function testExceptionBadRequestUnset(): void
	{
		// if true, with false it has to be off
		$curl = new \CoreLibs\UrlRequests\Curl(["http_errors" => true]);
		$response = $curl->request('get', $this->url_basic, [
			"headers" => [
				"Authorization" => "schmalztiegel",
				"RunAuthTest" => "yes",
			],
			"http_errors" => false,
		]);
		$this->assertEquals(
			"401",
			$response['code'],
			'Unset Exception failed with false'
		);
		// if false, null should not change it
		$curl = new \CoreLibs\UrlRequests\Curl(["http_errors" => false]);
		$response = $curl->request('get', $this->url_basic, [
			"headers" => [
				"Authorization" => "schmalztiegel",
				"RunAuthTest" => "yes",
			],
			"http_errors" => null,
		]);
		$this->assertEquals(
			"401",
			$response['code'],
			'Unset Exception failed with null'
		);
	}
}

// __END__
