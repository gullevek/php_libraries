<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Language\GetLocale
 *
 * @coversDefaultClass \CoreLibs\Language\GetLocale
 * @testdox \CoreLibs\Language\GetLocale method tests
 */
final class CoreLibsLanguageGetLocaleTest extends TestCase
{
	public const SITE_ENCODING = 'UTF-8';
	public const SITE_LOCALE = 'en_US.UTF-8';
	public const SITE_DOMAIN = 'admin';
	public const LOCALE_PATH = __DIR__ . DIRECTORY_SEPARATOR
		. 'includes' . DIRECTORY_SEPARATOR
		. 'locale' . DIRECTORY_SEPARATOR;

	/**
	 * all the test data
	 *
	 * @return array<mixed>
	 */
	public function setLocaleFromSessionProvider(): array
	{
		return [
			// 0: locale
			// 1: domain
			// 2: encoding
			// 3: path
			// 4: SESSION: DEFAULT_LOCALE
			// 5: SESSION: DEFAULT_CHARSET
			// 5: SESSION: DEFAULT_DOMAIN
			// 6: SESSION: LOCALE_PATH
			// 6: expected array
			// 7: deprecation message
			'all session vars set' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				'ja_JP.UTF-8', 'UTF-8', 'admin', __DIR__ . '/locale_other/',
				// return array
				[
					'locale' => 'ja_JP.UTF-8',
					'lang' => 'ja_JP',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// param lang and domain (no override)
			'no session set, only parameters' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				null, null, null, null,
				// return array
				[
					'locale' => 'en_US.UTF-8',
					'lang' => 'en_US',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// special parse session check for locales
			'all session vars set, short lang' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				'ja', 'UTF-8', 'admin', __DIR__ . '/locale_other/',
				// return array
				[
					'locale' => 'ja',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// lang with modifier
			// param lang and domain (no override)
			'long locale, domain, encoding params, no sessions' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				'de_CH.UTF-8@euro', 'admin', 'UTF-8',  __DIR__ . '/includes/locale/',
				// return array
				[
					'locale' => 'de_CH.UTF-8@euro',
					'lang' => 'de_CH',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// missing session values check
			// special parse session check for locales
			'session missing encoding, set from parameters' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				'ja', null, 'admin', __DIR__ . '/locale_other/',
				// return array
				[
					'locale' => 'ja',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// null return check for invalid entries
			'no session set, only parameters, all invalid' => [
				// lang, domain, encoding, path
				'###', '&&&&', '$$$$', 'foo_bar_path',
				// SESSION SETTINGS: locale, charset, domain, path
				null, null, null, null,
				// return array
				[
					'locale' => null,
					'lang' => null,
					'domain' => null,
					'encoding' => null,
					'path' => null,
				],
			],
			// invalid session names, fall backup
			'all session vars are invalid, fallback' => [
				// lang, domain, encoding, path
				self::SITE_LOCALE, self::SITE_DOMAIN, self::SITE_ENCODING, self::LOCALE_PATH,
				// SESSION SETTINGS: locale, charset, domain, path
				'###', '&&&&', '$$$$', 'foo_bar_path',
				// return array
				[
					'locale' => 'en_US.UTF-8',
					'lang' => 'en_US',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
		];
	}

	/**
	  * Undocumented function
	  *
	  * @covers ::setLocale
	  * @dataProvider setLocaleFromSessionProvider
	  * @testdox lang settings lang $language, domain $domain, encoding $encoding, path $path; session lang: $SESSION_DEFAULT_LOCALE, session char: $SESSION_DEFAULT_CHARSET [$_dataName]
	  *
	  * @param  string|      $language
	  * @param  string|      $domain
	  * @param  string|      $encoding
	  * @param  string|      $path
	  * @param  string|null  $SESSION_DEFAULT_LOCALE
	  * @param  string|null  $SESSION_DEFAULT_CHARSET
	  * @param  string|null  $SESSION_DEFAULT_DOMAIN
	  * @param  string|null  $SESSION_LOCALE_PATH
	  * @param  array<mixed> $expected
	  * @return void
	  */
	public function testsetLocaleFromSession(
		string $language,
		string $domain,
		string $encoding,
		string $path,
		?string $SESSION_DEFAULT_LOCALE,
		?string $SESSION_DEFAULT_CHARSET,
		?string $SESSION_DEFAULT_DOMAIN,
		?string $SESSION_LOCALE_PATH,
		array $expected,
	): void {
		$return_lang_settings = [];
		global $_SESSION;
		// set override
		if ($SESSION_DEFAULT_LOCALE !== null) {
			$_SESSION['DEFAULT_LOCALE'] = $SESSION_DEFAULT_LOCALE;
		}
		if ($SESSION_DEFAULT_CHARSET !== null) {
			$_SESSION['DEFAULT_CHARSET'] = $SESSION_DEFAULT_CHARSET;
		}
		if ($SESSION_DEFAULT_DOMAIN !== null) {
			$_SESSION['DEFAULT_DOMAIN'] = $SESSION_DEFAULT_DOMAIN;
		}
		if ($SESSION_LOCALE_PATH !== null) {
			$_SESSION['LOCALE_PATH'] = $SESSION_LOCALE_PATH;
		}
		$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocaleFromSession(
			$language,
			$domain,
			$encoding,
			$path
		);
		// print "RETURN: " . print_r($return_lang_settings, true) . "\n";
		foreach (
			[
				'locale', 'lang', 'domain', 'encoding', 'path'
			] as $key
		) {
			$value = $expected[$key];
			if (
				!empty($value) &&
				strpos($value, "/") === 0
			) {
				// this is regex
				$this->assertMatchesRegularExpression(
					$value,
					$return_lang_settings[$key] ?? '',
					'assert regex failed for ' . $key
				);
			} else {
				// assert equal
				$this->assertEquals(
					$value,
					$return_lang_settings[$key],
					'assert equal failed for ' . $key
				);
			}
		}
		// unset all vars
		$_SESSION = [];
		unset($GLOBALS['OVERRIDE_LANG']);
	}
}

// __END__
