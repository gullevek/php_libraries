<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Language\L10n
 * Included are all Language\Core methods too if they are needed
 *
 * @coversDefaultClass \CoreLibs\Language\L10n
 * @testdox \CoreLibs\Language\L10n method tests
 */
final class CoreLibsLanguageL10nTest extends TestCase
{
	/**
	 * set all constant variables that must be set before call
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		// for deprecation test only, will be removed
		if (!defined('BASE')) {
			define('BASE', str_replace(DIRECTORY_SEPARATOR . 'configs', '', __DIR__) . DIRECTORY_SEPARATOR);
		}
		if (!defined('INCLUDES')) {
			define('INCLUDES', 'includes' . DIRECTORY_SEPARATOR);
		}
		if (!defined('LOCALE')) {
			define('LOCALE', 'locale' . DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * get instance self type check
	 *
	 * @covers ::getInstance
	 * @testdox check that getInstance() returns valid instance
	 *
	 * @return void
	 */
	public function testGetInstance(): void
	{
		$l10n_obj = \CoreLibs\Language\L10n::getInstance();
		$this->assertIsObject(
			$l10n_obj
		);
		$this->assertInstanceOf(
			'\CoreLibs\Language\L10n',
			$l10n_obj
		);
	}

	/**
	 * get current translator class type check
	 *
	 * @covers ::getTranslatorClass
	 * @testdox check that getTranslatorClass() returns valid instance
	 *
	 * @return void
	 */
	public function testGetTranslatorClass(): void
	{
		$l10n = new \CoreLibs\Language\L10n();
		$translator = $l10n->getTranslatorClass();
		$this->assertIsObject(
			$translator
		);
		$this->assertInstanceOf(
			'\CoreLibs\Language\Core\GetTextReader',
			$translator
		);
	}

	/**
	 * provider for class load parameters
	 *
	 * @return array
	 */
	public function l10nObjectProvider(): array
	{
		return [
			// 0: locale
			// 1: domain
			// 2: encoding
			// 3: path
			// 4: locale expected
			// 5: locale set expected
			// 6: lang expected
			// 7: encoding expected
			// 8: domain exepcted
			// 9: context (null for none)
			// 10: test string in
			// 11: test translated
			// 12: deprecation message (until removed)
			// new style load
			'gettext load en' => [
				'en_US.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				//
				'en_US.UTF-8',
				'en_US',
				'en_US',
				'UTF-8',
				'frontend',
				null,
				'Original',
				'Translated frontend en_US',
				null,
			],
			'gettext load en' => [
				'en_US.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				//
				'en_US.UTF-8',
				'en_US',
				'en_US',
				'UTF-8',
				'frontend',
				'context',
				'Original',
				'Original context frontend en_US',
				null,
			],
			'gettext load ja' => [
				'ja_JP.UTF-8',
				'admin',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				//
				'ja_JP.UTF-8',
				'ja_JP',
				'ja_JP',
				'UTF-8',
				'admin',
				null,
				'Original',
				'Translated admin ja_JP',
				null,
			],
			// mixed path and domain [DEPRECATED]
			'mixed path and domain [DEPRECATED]' => [
				'en_US.UTF-8',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				'frontend',
				//
				'en_US.UTF-8',
				'en_US',
				'en_US',
				'UTF-8',
				'frontend',
				'context',
				'Original',
				'Original context frontend en_US',
				'L10n constructor parameter switch is no longer supported. domain is 2nd, path is 3rd parameter'
			],
			// unset path
			'unset path with locale and domain [DEPRECATED]' => [
				'ja_JP.UTF-8',
				'admin',
				null,
				//
				'ja_JP.UTF-8',
				'ja_JP',
				'ja_JP',
				'UTF-8',
				'admin',
				null,
				'Original',
				'Translated admin ja_JP',
				'Empty path parameter is no longer allowed if locale and domain are set',
			],
			// null set
			'empty load new ' => [
				'',
				'',
				'',
				//
				'',
				'',
				'',
				'',
				'',
				null,
				'Original',
				'Original',
				null,
			]
		];
	}

	/**
	 * new class load test (basic test)
	 *
	 * @covers ::__construct
	 * @dataProvider l10nObjectProvider
	 * @testdox check l10n init with Locale $locale, Path $path, Domain $domain, Legacy: $legacy with $context [$_dataName]
	 *
	 * @param  string|null $locale
	 * @param  string|null $domain
	 * @param  string|null $path
	 * @param  string      $locale_expected
	 * @param  string      $locale_set_expected
	 * @param  string      $lang_expected
	 * @param  string      $encoding_expected
	 * @param  string      $domain_expected
	 * @param  string|null $context
	 * @param  string      $original
	 * @param  string      $translated
	 * @param  string|null $deprecation_message
	 * @return void
	 */
	public function testL10nObject(
		?string $locale,
		?string $domain,
		?string $path,
		string $locale_expected,
		string $locale_set_expected,
		string $lang_expected,
		string $encoding_expected,
		string $domain_expected,
		?string $context,
		string $original,
		string $translated,
		?string $deprecation_message
	): void {
		if ($deprecation_message !== null) {
			set_error_handler(
				static function (int $errno, string $errstr): never {
					throw new \Exception($errstr, $errno);
				},
				E_USER_DEPRECATED
			);
			// catch this with the message
			$this->expectExceptionMessage($deprecation_message);
		}
		if ($locale === null) {
			$l10n = new \CoreLibs\Language\L10n();
		} elseif ($domain === null) {
			// same as if locale is null
			$l10n = new \CoreLibs\Language\L10n($locale);
		} elseif ($path === null) {
			// deprecated, path must be set
			$l10n = new \CoreLibs\Language\L10n($locale, $domain);
		} else {
			$l10n = new \CoreLibs\Language\L10n($locale, $domain, $path);
		}
		restore_error_handler();
		// print "LOC: " . $locale . ", " . $l10n->getLocale() . ", " . $locale_expected . "\n";
		// print "MO: " . $l10n->getMoFile() . "\n";
		$this->assertEquals(
			$locale_expected,
			$l10n->getLocale(),
			'Locale assert failed'
		);
		$this->assertEquals(
			$locale_set_expected,
			$l10n->getLocaleSet(),
			'Locale set assert failed'
		);
		$this->assertEquals(
			$domain_expected,
			$l10n->getDomain(),
			'Domain assert failed'
		);
		if (empty($context)) {
			$this->assertEquals(
				$translated,
				$l10n->__($original),
				'Translated string assert failed'
			);
		} else {
			$this->assertEquals(
				$translated,
				$l10n->__p($context, $original),
				'Translated string assert failed in context: ' . $context
			);
		}
		// test get locel as array
		$locale = $l10n->getLocaleAsArray();
		$this->assertEquals(
			[
				'locale' => $locale_expected,
				'lang' => $lang_expected,
				'domain' => $domain_expected,
				'encoding' => $encoding_expected,
				'path' => $path
			],
			$locale,
			'getLocaleAsArray mismatch'
		);
	}

	// l10nReloadMOfile and getTranslator
	// null init with loader
	// loader with reload (change type)

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getTranslatorProvider(): array
	{
		return [
			// 0: locale
			// 1: domain
			// 2: path
			// 3: load error
			// 4: input string to translated
			// 5: expected locale
			// 6: expected locale set
			// 7: expected domain
			// 8: expected translation
			// 9: change locale
			// 10: change domain
			// 11: change path
			// 12: change load error
			// 13: expected locale
			// 14: expected locale set
			// 15: expected domain
			// 16: expected translation
			'load and change (en->ja)' => [
				// set 0-2
				'en_US.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				// status 3
				false,
				// to translate 4
				'Original',
				// check setter 5-7
				'en_US.UTF-8',
				'en_US',
				'frontend',
				'Translated frontend en_US',
				// set new 8-10
				'ja_JP.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				// status new 11
				false,
				// check new setter 12-14
				'ja_JP.UTF-8',
				'ja_JP',
				'frontend',
				'Translated frontend ja_JP',
			],
			'empty load and change to en' => [
				// set 0-2
				'',
				'',
				'',
				// status 3
				false,
				// to translate 4
				'Original',
				// check setter 5-7
				'',
				'',
				'',
				'Original',
				// set new 8-10
				'en_US.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				// status new 11
				false,
				// check new setter 12-14
				'en_US.UTF-8',
				'en_US',
				'frontend',
				'Translated frontend en_US',
			]
		];
	}

	/**
	 * init check and connected change translation
	 *
	 * @covers ::getTranslator
	 * @covers ::l10nReloadMOfile
	 * @dataProvider getTranslatorProvider
	 * @testdox change locale from $locale and domain $domain to locale $locale_new and domain $domain_new [$_dataName]
	 *
	 * @param  string|null $locale
	 * @param  string|null $domain
	 * @param  string|null $path
	 * @param  bool        $load_error
	 * @param  string      $original
	 * @param  string      $locale_expected_a
	 * @param  string      $locale_set_expected_a
	 * @param  string      $domain_expected_a
	 * @param  string      $translated_a
	 * @param  string|null $locale_new
	 * @param  string|null $domain_new
	 * @param  string|null $path_new
	 * @param  bool        $load_error_new
	 * @param  string      $locale_set_expected_b
	 * @param  string      $locale_expected_b
	 * @param  string      $domain_expected_b
	 * @param  string      $translated_b
	 * @return void
	 */
	public function testGetTranslator(
		// 0-2
		?string $locale,
		?string $domain,
		?string $path,
		// 3
		bool $load_error,
		// 4
		string $original,
		// 5-7
		string $locale_expected_a,
		string $locale_set_expected_a,
		string $domain_expected_a,
		string $translated_a,
		// 8-10
		?string $locale_new,
		?string $domain_new,
		?string $path_new,
		// 11
		bool $load_error_new,
		// 12-14
		string $locale_expected_b,
		string $locale_set_expected_b,
		string $domain_expected_b,
		string $translated_b
	): void {
		if ($locale === null || $domain === null || $path === null) {
			$l10n = new \CoreLibs\Language\L10n();
		} else {
			$l10n = new \CoreLibs\Language\L10n($locale, $domain, $path);
		}
		// print "LOC: " . $locale . ", " . $l10n->getLocale() . ", " . $locale_expected . "\n";
		// status check
		$this->assertEquals(
			$load_error,
			$l10n->getLoadError(),
			'Legacy method load error init check'
		);
		$this->assertEquals(
			$locale_expected_a,
			$l10n->getLocale(),
			'Locale init assert failed'
		);$this->assertEquals(
			$locale_set_expected_a,
			$l10n->getLocaleSet(),
			'Locale Set init assert failed'
		);
		$this->assertEquals(
			$domain_expected_a,
			$l10n->getDomain(),
			'Domain init assert failed'
		);
		$this->assertEquals(
			$translated_a,
			$l10n->__($original),
			'Translated string init assert failed'
		);

		// switch
		if ($locale_new === null) {
			$translator = $l10n->getTranslator();
		} elseif ($domain_new === null) {
			$translator = $l10n->getTranslator($locale_new);
		} elseif ($path_new === null) {
			$translator = $l10n->getTranslator($locale_new, $domain_new);
		} else {
			$translator = $l10n->getTranslator($locale_new, $domain_new, $path_new);
		}
		// status check
		$this->assertEquals(
			$load_error_new,
			$l10n->getLoadError(),
			'Translate method load error change check'
		);
		// check that returned is class GetTextReader and object
		$this->assertIsObject(
			$translator,
			'translater class is object assert failed'
		);
		$this->assertInstanceOf(
			'\CoreLibs\Language\Core\GetTextReader',
			$translator,
			'translator class is correct instance assert failed'
		);

		// translator class
		$this->assertEquals(
			$translated_b,
			$translator->gettext($original),
			'Translated string change assert failed from returned class'
		);
		// new set check
		$this->assertEquals(
			$locale_expected_b,
			$l10n->getLocale(),
			'Locale change assert failed'
		);
		$this->assertEquals(
			$locale_set_expected_b,
			$l10n->getLocaleSet(),
			'Locale Set change assert failed'
		);
		$this->assertEquals(
			$domain_expected_b,
			$l10n->getDomain(),
			'Domain change assert failed'
		);
		$this->assertEquals(
			$translated_b,
			$l10n->__($original),
			'Translated string change assert failed'
		);
	}

	// TODO: domain based
	// ->dgettext
	// ->dngettext
	// ->dpgettext
	// ->dpngettext

	/**
	 * for plural and plural context
	 *
	 * @return array
	 */
	public function ngettextProvider(): array
	{
		return [
			// 0: locale
			// 1: domain
			// 2: path
			// 3: context (null for none)
			// 4: single string
			// 5: plural string
			// 6: array for each n value expected string
			'plural text en' => [
				'en_US',
				'admin',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				// context
				null,
				// text single/multi in
				'single',
				'multi',
				// confirm translation, pos in array equal n
				[
					0 => 'Multi admin en_US 1',
					1 => 'Multi admin en_US 0',
					2 => 'Multi admin en_US 1',
				]
			],
			'plural text context en' => [
				'en_US',
				'admin',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				// context
				'context',
				// text single/multi in
				'single',
				'multi',
				// confirm translation, pos in array equal n
				[
					0 => 'Multi context admin en_US 1',
					1 => 'Multi context admin en_US 0',
					2 => 'Multi context admin en_US 1',
				]
				],
		];
	}

	/**
	 * plural and plural context
	 *
	 * @covers ::__n
	 * @covers ::__pn
	 * @dataProvider ngettextProvider
	 * @testdox plural string test for locale $locale and domain $domain with $context [$_dataName]
	 *
	 * @param  string  $locale
	 * @param  string  $domain
	 * @param  string  $path
	 * @param  ?string $context
	 * @param  string  $original_single
	 * @param  string  $original_plural
	 * @param  array   $expected_strings
	 * @return void
	 */
	public function testNgettext(
		// config 0-3
		string $locale,
		string $domain,
		string $path,
		// context string
		?string $context,
		// input strings
		string $original_single,
		string $original_plural,
		// expected
		array $expected_strings
	): void {
		$l10n = new \CoreLibs\Language\L10n($locale, $domain, $path);

		foreach ($expected_strings as $n => $expected) {
			if (empty($context)) {
				$this->assertEquals(
					$expected,
					$l10n->__n($original_single, $original_plural, $n),
					'assert failed for plural: ' . $n
				);
			} else {
				$this->assertEquals(
					$expected,
					$l10n->__np($context, $original_single, $original_plural, $n),
					'assert failed for plural: ' . $n . ' in context: ' . $context
				);
			}
		}
	}

	/**
	 * locales list for testing locale folder lookup
	 *
	 * @return array
	 */
	public function localesProvider(): array
	{
		return [
			// 0: locale
			// 1: return array
			'en' => [
				'en',
				[
					'en',
				],
				[
					'lang' => 'en',
					'country' => null,
					'charset' => null,
					'modifier' => null,
				],
			],
			'en.UTF-8' => [
				'en.UTF-8',
				[
					'en.UTF-8',
					'en',
				],
				[
					'lang' => 'en',
					'country' => null,
					'charset' => 'UTF-8',
					'modifier' => null,
				],
			],
			'en_US' => [
				'en_US',
				[
					'en_US',
					'en',
				],
				[
					'lang' => 'en',
					'country' => 'US',
					'charset' => null,
					'modifier' => null,
				],
			],
			'en_US.UTF-8' => [
				'en_US.UTF-8',
				[
					'en_US.UTF-8',
					'en_US',
					'en',
				],
				[
					'lang' => 'en',
					'country' => 'US',
					'charset' => 'UTF-8',
					'modifier' => null,
				],
			],
			'en_US@subtext' => [
				'en_US@subtext',
				[
					'en_US@subtext',
					'en@subtext',
					'en_US',
					'en',
				],
				[
					'lang' => 'en',
					'country' => 'US',
					'charset' => null,
					'modifier' => 'subtext',
				],
			],
			'en_US.UTF-8@subtext' => [
				'en_US.UTF-8@subtext',
				[
					'en_US.UTF-8@subtext',
					'en_US@subtext',
					'en@subtext',
					'en_US.UTF-8',
					'en_US',
					'en',
				],
				[
					'lang' => 'en',
					'country' => 'US',
					'charset' => 'UTF-8',
					'modifier' => 'subtext',
				],
			]
		];
	}

	/**
	 * test locales array return
	 *
	 * @covers ::listLocales
	 * @dataProvider localesProvider
	 * @testdox check $locale [$_dataName]
	 *
	 * @param string $locale
	 * @param array $expected_list
	 * @param array $expected_detail
	 * @return void
	 */
	public function testListLocales(string $locale, array $expected_list, array $expected_detail): void
	{
		$locale_detail = \CoreLibs\Language\L10n::parseLocale($locale);
		$this->assertEquals(
			$expected_detail,
			$locale_detail,
			'Parse local assert failed'
		);
		$locale_list = \CoreLibs\Language\L10n::listLocales($locale);
		// print "LOCALES: " . print_r($locale_list, true) . "\n";
		$this->assertEquals(
			$expected_list,
			$locale_list,
			'List locale assert failed'
		);
	}

	// @covers ::detectLocale

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function detectLocaleProvider(): array
	{
		return [
			// 0: type: global | env
			// 1: global variable name or enviroment var
			// 2: value to set
			// 3: value to expect back
			'global locale' => [
				'global',
				'LOCALE',
				'ja_JP.UTF-8',
				'ja_JP.UTF-8',
			],
			'env LC_ALL' => [
				'env',
				'LC_ALL',
				'ja_JP.UTF-8',
				'ja_JP.UTF-8',
			],
			'env LANG' => [
				'env',
				'LANG',
				'ja_JP.UTF-8',
				'ja_JP.UTF-8',
			],
			'default return' => [
				'env',
				'LC_ALL',
				'',
				'en',
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::detectLocale
	 * @dataProvider detectLocaleProvider
	 * @testdox check detectLocale for $type with $var and $value is $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testDetectLocale(
		string $type,
		string $var,
		string $value,
		string $expected
	): void {
		switch ($type) {
			case 'global':
				$GLOBALS[$var] = $value;
				break;
			case 'env':
				$old_value = getenv("$var");
				putenv("$var=$value");
				// unset all other env vars
				foreach (['LC_ALL', 'LC_MESSAGES', 'LANG'] as $env) {
					if ($env != $var) {
						putenv("$env=");
					}
				}
				break;
		}
		$locale = \CoreLibs\Language\L10n::detectLocale();
		$this->assertEquals(
			$expected,
			$locale
		);
		// reset post run
		switch ($type) {
			case 'global':
				unset($GLOBALS[$var]);
				break;
			case 'env':
				putenv("$var=$old_value");
				break;
		}
	}

	// set/get text domain, domain, locale

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function textDomainProvider(): array
	{
		return [
			// 0: set domain
			// 1: set path
			// 2: get domain
			// 3: expected path
			'valid set and get' => [
				'foo',
				'foo/bar',
				'foo',
				'foo/bar',
			],
			'invalid set and get' => [
				'foo',
				'foo/bar',
				'iamnotset',
				false
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setTextDomain
	 * @covers ::getTextDomain
	 * @dataProvider textDomainProvider
	 * @testdox set $domain with $path and get $get_domain and expect $expected [$_dataName]
	 *
	 * @param  string $domain
	 * @param  string $path
	 * @param  string $get_domain
	 * @param  string|bool $expected
	 * @return void
	 */
	public function testSetGetTextDomain(string $domain, string $path, string $get_domain, $expected): void
	{
		$l10n = new \CoreLibs\Language\L10n();
		$l10n->setTextDomain($domain, $path);
		$this->assertEquals(
			$expected,
			$l10n->getTextDomain($get_domain)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function domainProvider(): array
	{
		return [
			// 0: set domain
			// 1: expected domain from get
			'valid domain' => [
				'foo',
				'foo',
			],
			'empty domain' => [
				'',
				'',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setDomain
	 * @covers ::getDomain
	 * @dataProvider domainProvider
	 * @testdox set $domain and expect $expected [$_dataName]
	 *
	 * @param  string $domain
	 * @param  string $expected
	 * @return void
	 */
	public function testSetGetDomain(string $domain, string $expected): void
	{
		$l10n = new \CoreLibs\Language\L10n();
		$l10n->setDomain($domain);
		$this->assertEquals(
			$expected,
			$l10n->getDomain()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function localeProvider(): array
	{
		return [
			// 0: set locale
			// 1: pre set if not null or not empty
			// 2: expected return from set
			// 3: expected from get
			'valid locale' => [
				'foo',
				null,
				'foo',
				'foo',
			],
			'empty locale' => [
				'',
				null,
				'',
				'',
			],
			'empty locale, pre set' => [
				'',
				'foo',
				'foo',
				'foo',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setLocale
	 * @covers ::getLocale
	 * @dataProvider localeProvider
	 * @testdox set $locale with $expected_return and expect $expected [$_dataName]
	 *
	 * @param  string $locale
	 * @param  string $pre_locale
	 * @param  string $expected_return
	 * @param  string $expected
	 * @return void
	 */
	public function testSetGetLocale(
		string $locale,
		?string $pre_locale,
		string $expected_return,
		string $expected
	): void {
		$l10n = new \CoreLibs\Language\L10n();
		if (!empty($pre_locale)) {
			$l10n->setLocale($pre_locale);
		}
		$returned = $l10n->setLocale($locale);
		$this->assertEquals(
			$expected_return,
			$returned,
			'Set locale return assert failed'
		);
		$this->assertEquals(
			$expected,
			$l10n->getLocale(),
			'Get locale aszert failed'
		);
	}

	// static load

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function functionsProvider(): array
	{
		return [
			// 0: lang/locale
			// 1: domain
			// 2: path
			// 3: encoding
			// 4: string
			// 5: translated string
			'standard en' => [
				'en_US.UTF-8',
				'frontend',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				'UTF-8',
				'Original',
				'Translated frontend en_US',
			],
			'standard ja' => [
				'ja_JP.UTF-8',
				'admin',
				__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR,
				'UTF-8',
				'Original',
				'Translated admin ja_JP',
			]
		];
	}

	/**
	 * fuctions check
	 * TODO: others d/dn/dp/dnp gettext functions
	 *
	 * @covers __setlocale
	 * @covers __bindtextdomain
	 * @covers __bind_textdomain_codeset
	 * @covers __textdomain
	 * @covers __gettext
	 * @covers __
	 * @dataProvider functionsProvider
	 * @testdox check functions with locale $locale and domain $domain [$_dataName]
	 * @param  string $locale
	 * @param  string $domain
	 * @param  string $path
	 * @param  string $encoding
	 * @param  string $original
	 * @param  string $translated
	 * @return void
	 */
	public function testFunctions(
		string $locale,
		string $domain,
		string $path,
		string $encoding,
		string $original,
		string $translated
	): void {
		\CoreLibs\Language\L10n::loadFunctions();
		_setlocale(LC_MESSAGES, $locale);
		_textdomain($domain);
		_bindtextdomain($domain, $path);
		_bind_textdomain_codeset($domain, $encoding);

		$this->assertEquals(
			$translated,
			__($original),
			'function __ assert failed'
		);
		$this->assertEquals(
			$translated,
			_gettext($original),
			'function gettext assert failed'
		);
	}
}

// __END__
