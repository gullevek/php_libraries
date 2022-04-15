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
	/**
	 * set all constant variables that must be set before call
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		// default web page encoding setting
		define('DEFAULT_ENCODING', 'UTF-8');
		// default lang + encoding
		define('DEFAULT_LOCALE', 'en_US.UTF-8');
		// site
		define('SITE_ENCODING', DEFAULT_ENCODING);
		define('SITE_LOCALE', DEFAULT_LOCALE);
		// just set
		define('BASE', str_replace('/configs', '', __DIR__) . DIRECTORY_SEPARATOR);
		define('INCLUDES', 'includes' . DIRECTORY_SEPARATOR);
		define('LOCALE', 'locale' . DIRECTORY_SEPARATOR);
		define('CONTENT_PATH', 'frontend' . DIRECTORY_SEPARATOR);
		// array session
		$_SESSION = [];
		global $_SESSION;
	}

	/**
	 * all the test data
	 *
	 * @return array
	 */
	public function setLocaleProvider(): array
	{
		return [
			// 0: locale
			// 1: domain
			// 2: encoding
			// 3: path
			// 4: SESSION: DEFAULT_LOCALE
			// 5: SESSION: DEFAULT_CHARSET
			// 6: expected array
			'no params, all default constants' => [
				// lang, domain, encoding, path
				null, null, null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'en_US.UTF-8',
					'lang' => 'en_US',
					'domain' => 'frontend',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			'no params, session charset and lang' => [
				// lang, domain, encoding, path
				null, null, null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				'ja_JP', 'UTF-8',
				// return array
				[
					'locale' => 'ja_JP',
					'lang' => 'ja_JP',
					'domain' => 'frontend',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			'no params, session charset and lang short' => [
				// lang, domain, encoding, path
				null, null, null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				'ja', 'UTF-8',
				// return array
				[
					'locale' => 'ja',
					'lang' => 'ja',
					'domain' => 'frontend',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// param lang (no sessions)
			'locale param only, no sessions' => [
				// lang, domain, encoding, path
				'ja.UTF-8', null, null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja.UTF-8',
					'lang' => 'ja',
					'domain' => 'frontend',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// different locale setting
			'locale complex param only, no sessions' => [
				// lang, domain, encoding, path
				'ja_JP.SJIS', null, null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja_JP.SJIS',
					'lang' => 'ja_JP',
					'domain' => 'frontend',
					'encoding' => 'SJIS',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// param lang and domain (no override)
			'locale, domain params, no sessions' => [
				// lang, domain, encoding, path
				'ja.UTF-8', 'admin', null, null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja.UTF-8',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// param lang and domain (no override)
			'locale, domain, encoding params, no sessions' => [
				// lang, domain, encoding, path
				'ja.UTF-8', 'admin', 'UTF-8', null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja.UTF-8',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// lang, domain, path (no override)
			'locale, domain and path, no sessions' => [
				// lang, domain, encoding, path
				'ja.UTF-8', 'admin', '', __DIR__ . '/locale_other/',
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja.UTF-8',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// all params set (no override)
			'all parameter, no sessions' => [
				// lang, domain, encoding, path
				'ja', 'admin', 'UTF-8', __DIR__ . '/locale_other/',
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'ja',
					'lang' => 'ja',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// param lang and domain (no override)
			'long locale, domain, encoding params, no sessions' => [
				// lang, domain, encoding, path
				'de_CH.UTF-8@euro', 'admin', 'UTF-8', null,
				// SESSION DEFAULT_LOCALE, SESSION: DEFAULT_CHARSET
				null, null,
				// return array
				[
					'locale' => 'de_CH.UTF-8@euro',
					'lang' => 'de_CH',
					'domain' => 'admin',
					'encoding' => 'UTF-8',
					'path' => "/^\/(.*\/)?includes\/locale\/$/",
				],
			],
			// TODO invalid params (bad path) (no override)
			// TODO param calls, but with override set
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setLocale
	 * @dataProvider setLocaleProvider
	 * @testdox lang settings lang $language, domain $domain, encoding $encoding, path $path; session lang: $SESSION_DEFAULT_LOCALE, session char: $SESSION_DEFAULT_CHARSET [$_dataName]
	 *
	 * @return void
	 */
	public function testsetLocale(
		?string $language,
		?string $domain,
		?string $encoding,
		?string $path,
		?string $SESSION_DEFAULT_LOCALE,
		?string $SESSION_DEFAULT_CHARSET,
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
		// function call
		if ($language === null && $domain === null && $encoding === null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocale();
		} elseif ($language !== null && $domain === null && $encoding === null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocale(
				$language
			);
		} elseif ($language !== null && $domain !== null && $encoding === null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocale(
				$language,
				$domain
			);
		} elseif ($language !== null && $domain !== null && $encoding !== null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocale(
				$language,
				$domain,
				$encoding
			);
		} else {
			$return_lang_settings  = \CoreLibs\Language\GetLocale::setLocale(
				$language,
				$domain,
				$encoding,
				$path
			);
		}
		// print "RETURN: " . print_r($return_lang_settings, true) . "\n";

		foreach (
			[
				'locale', 'lang', 'domain', 'encoding', 'path'
			] as $key
		) {
			$value = $expected[$key];
			if (strpos($value, "/") === 0) {
				// this is regex
				$this->assertMatchesRegularExpression(
					$value,
					$return_lang_settings[$key],
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
