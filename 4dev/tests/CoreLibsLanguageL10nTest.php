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
		define('DEFAULT_LANG', 'en_US');
		// default web page encoding setting
		define('DEFAULT_ENCODING', 'UTF-8');
		// default lang + encoding
		define('DEFAULT_LOCALE', 'en_US.UTF-8');
		// site
		define('SITE_LANG', DEFAULT_LANG);
		// just set
		define('BASE', str_replace('/configs', '', __DIR__) . DIRECTORY_SEPARATOR);
		define('INCLUDES', 'includes' . DIRECTORY_SEPARATOR);
		define('LANG', 'lang' . DIRECTORY_SEPARATOR);
		define('LOCALE', 'locale' . DIRECTORY_SEPARATOR);
		define('CONTENT_PATH', 'frontend' . DIRECTORY_SEPARATOR);
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
			// 1: path
			// 2: domain
			// 3: legacy load (Default true)
			// 4: locale expected
			// 5: domain exepcted
			// 6: context (null for none)
			// 7: test string in
			// 8: test translated
			'legacy load en' => [
				'en_utf8',
				null,
				null,
				null,
				//
				'en_utf8',
				'',
				//
				null,
				'Original',
				'Translated frontend en_US',
			],
			'legacy load ja' => [
				'ja_utf8',
				null,
				null,
				null,
				'ja_utf8',
				'',
				null,
				'Original',
				'Translated frontend ja_JP',
			],
			// new style load
			'gettext load en' => [
				'en_US.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				false,
				'en_US.UTF-8',
				'frontend',
				null,
				'Original',
				'Translated frontend en_US',
			],
			'gettext load en' => [
				'en_US.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				false,
				'en_US.UTF-8',
				'frontend',
				'context',
				'Original',
				'Original context frontend en_US',
			],
			'gettext load ja' => [
				'ja_JP.UTF-8',
				__DIR__ . 'includes/locale/',
				'admin',
				false,
				'ja_JP.UTF-8',
				'admin',
				null,
				'Original',
				'Translated admin ja_JP',
			],
			// null set locale legacy
			'empty load legacy' => [
				null,
				null,
				null,
				null,
				'',
				'',
				null,
				'Original',
				'Original',
			],
			'empty load new ' => [
				'',
				'',
				'',
				false,
				'',
				'',
				null,
				'Original',
				'Original',
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
	 * @param  string|null $path
	 * @param  string|null $domain
	 * @param  bool|null   $legacy
	 * @param  string      $locale_expected
	 * @param  string      $domain_expected
	 * @param  ?string     $context
	 * @param  string      $original
	 * @param  string      $translated
	 * @return void
	 */
	public function testL10nObject(
		?string $locale,
		?string $path,
		?string $domain,
		?bool $legacy,
		string $locale_expected,
		string $domain_expected,
		?string $context,
		string $original,
		string $translated,
	): void {
		if ($locale === null) {
			$l10n = new \CoreLibs\Language\L10n();
		} elseif ($path === null) {
			$l10n = new \CoreLibs\Language\L10n($locale);
		} elseif ($domain === null) {
			$l10n = new \CoreLibs\Language\L10n($locale, $path);
		} elseif ($legacy === null) {
			$l10n = new \CoreLibs\Language\L10n($locale, $path, $domain);
		} else {
			$l10n = new \CoreLibs\Language\L10n($locale, $path, $domain, $legacy);
		}
		// print "LOC: " . $locale . ", " . $l10n->getLocale() . ", " . $locale_expected . "\n";
		// print "MO: " . $l10n->getMoFile() . "\n";
		$this->assertEquals(
			$locale_expected,
			$l10n->getLocale(),
			'Locale assert failed'
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
			// 1: path
			// 2: domain
			// 3: legacy flag
			// 4: input string to translated
			// 5: expected locale
			// 6: expected domain
			// 7: expected translation
			// 8: change locale
			// 9: change path
			// 10: change domain
			// 11: legacy flag
			// 12: expected locale
			// 13: expected domain
			// 14: expected translation
			'legacy load and change (en->ja)' => [
				// set 0-3
				'en_utf8',
				null,
				null,
				null,
				// status 4
				false,
				// to translate 5
				'Original',
				// check setter 6-8
				'en_utf8',
				'',
				'Translated frontend en_US',
				// set new 9-12
				'ja_utf8',
				null,
				null,
				null,
				// status new 13
				false,
				// check new setter 14-16
				'ja_utf8',
				'',
				'Translated frontend ja_JP',
			],
			'load and change (en->ja)' => [
				// set 0-3
				'en_US.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				false,
				// status 4
				false,
				// to translate 5
				'Original',
				// check setter 6-8
				'en_US.UTF-8',
				'frontend',
				'Translated frontend en_US',
				// set new 9-12
				'ja_JP.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				false,
				// status new 13
				false,
				// check new setter 14-16
				'ja_JP.UTF-8',
				'frontend',
				'Translated frontend ja_JP',
			],
			'empty load and change to en' => [
				// set 0-3
				'',
				'',
				'',
				false,
				// status 4
				false,
				// to translate 5
				'Original',
				// check setter 6-8
				'',
				'',
				'Original',
				// set new 9-12
				'en_US.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				false,
				// status new 13
				false,
				// check new setter 14-16
				'en_US.UTF-8',
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
	 * @param  string|null $path
	 * @param  string|null $domain
	 * @param  bool|null   $legacy
	 * @param  bool        $load_error
	 * @param  string      $original
	 * @param  string      $locale_expected_a
	 * @param  string      $domain_expected_a
	 * @param  string      $translated_a
	 * @param  string|null $locale_new
	 * @param  string|null $path_new
	 * @param  string|null $domain_new
	 * @param  bool|null   $legacy_new
	 * @param  bool        $load_error_new
	 * @param  string      $locale_expected_b
	 * @param  string      $domain_expected_b
	 * @param  string      $translated_b
	 * @return void
	 */
	public function testGetTranslator(
		// 0-3
		?string $locale,
		?string $path,
		?string $domain,
		?bool $legacy,
		// 4
		bool $load_error,
		// 5
		string $original,
		// 6-8
		string $locale_expected_a,
		string $domain_expected_a,
		string $translated_a,
		// 9-12
		?string $locale_new,
		?string $path_new,
		?string $domain_new,
		?bool $legacy_new,
		// 13
		bool $load_error_new,
		// 14-16
		string $locale_expected_b,
		string $domain_expected_b,
		string $translated_b,
	): void {
		if ($locale === null) {
			$l10n = new \CoreLibs\Language\L10n();
		} elseif ($path === null) {
			$l10n = new \CoreLibs\Language\L10n($locale);
		} elseif ($domain === null) {
			$l10n = new \CoreLibs\Language\L10n($locale, $path);
		} elseif ($legacy === null) {
			$l10n = new \CoreLibs\Language\L10n($locale, $path, $domain);
		} else {
			$l10n = new \CoreLibs\Language\L10n($locale, $path, $domain, $legacy);
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

		// do reload with legacy l10nReloadMOfile IF legacy is null or true
		// use getTranslator if legacy is false
		if ($legacy === null || $legacy === true) {
			// there is no null/empty locale allowed directly
			// if empty will set previous one
			if ($path_new === null) {
				$ret_status = $l10n->l10nReloadMOfile($locale_new);
			} elseif ($domain_new === null) {
				$ret_status = $l10n->l10nReloadMOfile($locale_new, $path_new);
			} elseif ($legacy_new === null) {
				$ret_status = $l10n->l10nReloadMOfile($locale_new, $path_new, $domain_new);
			} else {
				$ret_status = $l10n->l10nReloadMOfile($locale_new, $path_new, $domain_new, $legacy_new);
			}
			// status check
			$this->assertEquals(
				$load_error_new,
				$l10n->getLoadError(),
				'Legacy method load error change check'
			);
			// retun status check is inverted to load error check
			$this->assertEquals(
				$load_error_new ? false : true,
				$ret_status,
				'Legacy return load error change check'
			);
		} else {
			if ($locale_new === null) {
				$translator = $l10n->getTranslator();
			} elseif ($path_new === null) {
				$translator = $l10n->getTranslator($locale_new);
			} elseif ($domain_new === null) {
				$translator = $l10n->getTranslator($locale_new, $path_new);
			} elseif ($legacy_new === null) {
				$translator = $l10n->getTranslator($locale_new, $path_new, $domain_new);
			} else {
				$translator = $l10n->getTranslator($locale_new, $path_new, $domain_new, $legacy_new);
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
		}
		// new set check
		$this->assertEquals(
			$locale_expected_b,
			$l10n->getLocale(),
			'Locale change assert failed'
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

	// TODO: test all translation types
	// __/gettext
	// __n/ngettext
	// ->dgettext
	// ->dngettext
	// ->dpgettext
	// ->dpngettext

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function ngettextProvider(): array
	{
		return [
			// 0: locale
			// 1: path
			// 2: domain
			// 3: context (null for none)
			// 4: single string
			// 5: plural string
			// 6: array for each n value expected string
			'plural text en' => [
				'en_US',
				__DIR__ . 'includes/locale/',
				'admin',
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
				__DIR__ . 'includes/locale/',
				'admin',
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
	 * Undocumented function
	 *
	 * @covers ::__n
	 * @dataProvider ngettextProvider
	 * @testdox plural string test for locale $locale and domain $domain with $context [$_dataName]
	 *
	 * @param  string  $locale
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  ?string $context
	 * @param  string  $original_single
	 * @param  string  $original_plural
	 * @param  array   $expected_strings
	 * @return void
	 */
	public function testNgettext(
		// config 0-3
		string $locale,
		string $path,
		string $domain,
		// context string
		?string $context,
		// input strings
		string $original_single,
		string $original_plural,
		// expected
		array $expected_strings
	): void {
		$l10n = new \CoreLibs\Language\L10n($locale, $path, $domain, false);

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
					$l10n->__pn($context, $original_single, $original_plural, $n),
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
				]
			],
			'en.UTF-8' => [
				'en.UTF-8',
				[
					'en.UTF-8',
					'en',
				]
			],
			'en_US' => [
				'en_US',
				[
					'en_US',
					'en',
				]
			],
			'en_US.UTF-8' => [
				'en_US.UTF-8',
				[
					'en_US.UTF-8',
					'en_US',
					'en',
				]
			],
			'en_US@subtext' => [
				'en_US@subtext',
				[
					'en_US@subtext',
					'en@subtext',
					'en_US',
					'en',
				]
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
				]
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
	 * @param array $expected
	 * @return void
	 */
	public function testListLocales(string $locale, array $expected): void
	{
		$locale_list = \CoreLibs\Language\L10n::listLocales($locale);
		// print "LOCALES: " . print_r($locale_list, true) . "\n";
		$this->assertEquals(
			$expected,
			$locale_list
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
		string $expected,
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
			// 1: path
			// 2: domain
			// 3: encoding
			// 4: string
			// 5: translated string
			'standard en' => [
				'en_US.UTF-8',
				__DIR__ . 'includes/locale/',
				'frontend',
				'UTF-8',
				'Original',
				'Translated frontend en_US',
			],
			'standard ja' => [
				'ja_JP.UTF-8',
				__DIR__ . 'includes/locale/',
				'admin',
				'UTF-8',
				'Original',
				'Translated admin ja_JP',
			]
		];
	}

	/**
	 * fuctions check
	 * TODO: others d/dn/dp/dpn gettext functions
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
	 * @param  string $path
	 * @param  string $domain
	 * @param  string $encoding
	 * @param  string $original
	 * @param  string $translated
	 * @return void
	 */
	public function testFunctions(
		string $locale,
		string $path,
		string $domain,
		string $encoding,
		string $original,
		string $translated
	): void {
		\CoreLibs\Language\L10n::loadFunctions();
		__setlocale(LC_MESSAGES, $locale);
		__textdomain($domain);
		__bindtextdomain($domain, $path);
		__bind_textdomain_codeset($domain, $encoding);
		$this->assertEquals(
			$translated,
			__($original),
			'function __ assert failed'
		);
		$this->assertEquals(
			$translated,
			__gettext($original),
			'function gettext assert failed'
		);
	}
}

// __END__
