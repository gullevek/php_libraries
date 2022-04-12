<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Language\GetSettings
 *
 * @coversDefaultClass \CoreLibs\Language\GetSettings
 * @testdox \CoreLibs\Language\GetSettings method tests
 */
final class CoreLibsLanguageGetSettingsTest extends TestCase
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
	public function setLangEncodingProvider(): array
	{
		return [
			// 0: locale/lang
			// 1: domain
			// 2: path
			// 3: SESSION DEFAULT_CHARSE
			// 4: GLOBALS: OVERRIDE_LANG
			// 5: SESSION: DEFAULT_LANG
			// 6: expected array
			'no params, all default constants' => [
				// lang, domain, path
				null, null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				null, null, null,
				// return array
				[
					0 => 'UTF-8',
					1 => 'en_US',
					2 => 'en',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'en_US',
					'lang_short' => 'en',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			'no params, session charset and lang' => [
				// lang, domain, path
				null, null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				'UTF-8', null, 'ja_JP',
				// return array
				[
					0 => 'UTF-8',
					1 => 'ja_JP',
					2 => 'ja',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'ja_JP',
					'lang_short' => 'ja',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			'no params, session charset and lang short' => [
				// lang, domain, path
				null, null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				'UTF-8', null, 'ja',
				// return array
				[
					0 => 'UTF-8',
					1 => 'ja',
					2 => 'ja',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'ja',
					'lang_short' => 'ja',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// globals override lang
			'no params, session charset and lang, default lang override' => [
				// lang, domain, path
				null, null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				'UTF-8', 'en_US', 'ja_JP',
				// return array
				[
					0 => 'UTF-8',
					1 => 'en_US',
					2 => 'en',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'en_US',
					'lang_short' => 'en',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// globals override lang short
			'no params, session charset and lang, default lang short override' => [
				// lang, domain, path
				null, null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				'UTF-8', 'en', 'ja_JP',
				// return array
				[
					0 => 'UTF-8',
					1 => 'en',
					2 => 'en',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'en',
					'lang_short' => 'en',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// param lang (no override)
			'locale param only, no override' => [
				// lang, domain, path
				'ja.UTF-8', null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				null, null, null,
				// return array
				[
					0 => 'UTF-8',
					1 => 'ja',
					2 => 'ja',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'ja',
					'lang_short' => 'ja',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// different locale setting
			'locale complex param only, no override' => [
				// lang, domain, path
				'ja_JP.SJIS', null, null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				null, null, null,
				// return array
				[
					0 => 'SJIS',
					1 => 'ja_JP',
					2 => 'ja',
					3 => 'frontend',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'SJIS',
					'lang' => 'ja_JP',
					'lang_short' => 'ja',
					'domain' => 'frontend',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// param lang and domain (no override)
			'locale, domain params, no override' => [
				// lang, domain, path
				'ja.UTF-8', 'admin', null,
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				null, null, null,
				// return array
				[
					0 => 'UTF-8',
					1 => 'ja',
					2 => 'ja',
					3 => 'admin',
					4 => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'ja',
					'lang_short' => 'ja',
					'domain' => 'admin',
					'path' => "/^\/(.*\/)?includes\/lang\/frontend\/$/",
				],
			],
			// all params set (no override)
			'all params, no override' => [
				// lang, domain, path
				'ja.UTF-8', 'admin', __DIR__ . '/locale_other/',
				// global set no session
				// SESSION: DEFAULT_CHARSET, GLOBALS: OVERRIDE_LANG, SESSION: DEFAULT_LANG
				null, null, null,
				// return array
				[
					0 => 'UTF-8',
					1 => 'ja',
					2 => 'ja',
					3 => 'admin',
					4 => "/^\/(.*\/)?locale_other\/$/",
					'encoding' => 'UTF-8',
					'lang' => 'ja',
					'lang_short' => 'ja',
					'domain' => 'admin',
					'path' => "/^\/(.*\/)?locale_other\/$/",
				],
			],
			// TODO invalid params (bad path) (no override)
			// TODO param calls, but with override set
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setLangEncoding
	 * @dataProvider setLangEncodingProvider
	 * @testdox lang settings lang $language, domain $domain, path $path; null session char: $SESSION_DEFAULT_CHARSET, null global lang: $GLOBAL_OVERRIDE_LANG, null session lang: $SESSION_DEFAULT_LANG [$_dataName]
	 *
	 * @return void
	 */
	public function testSetLangEncoding(
		?string $language,
		?string $domain,
		?string $path,
		?string $SESSION_DEFAULT_CHARSET,
		?string $GLOBAL_OVERRIDE_LANG,
		?string $SESSION_DEFAULT_LANG,
		array $expected,
	): void {
		$return_lang_settings = [];
		global $_SESSION;
		// set override
		if ($SESSION_DEFAULT_CHARSET !== null) {
			$_SESSION['DEFAULT_CHARSET'] = $SESSION_DEFAULT_CHARSET;
		}
		if ($GLOBAL_OVERRIDE_LANG !== null) {
			$GLOBALS['OVERRIDE_LANG'] = $GLOBAL_OVERRIDE_LANG;
		}
		if ($SESSION_DEFAULT_LANG !== null) {
			$_SESSION['DEFAULT_LANG'] = $SESSION_DEFAULT_LANG;
		}
		// function call
		if ($language === null && $domain === null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetSettings::setLangEncoding();
		} elseif ($language !== null && $domain === null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetSettings::setLangEncoding(
				$language
			);
		} elseif ($language !== null && $domain !== null && $path === null) {
			$return_lang_settings  = \CoreLibs\Language\GetSettings::setLangEncoding(
				$language,
				$domain
			);
		} else {
			$return_lang_settings  = \CoreLibs\Language\GetSettings::setLangEncoding(
				$language,
				$domain,
				$path
			);
		}
		// print "RETURN: " . print_r($return_lang_settings, true) . "\n";

		foreach (
			[
				0, 1, 2, 3, 4,
				'encoding', 'lang', 'lang_short', 'domain', 'path'
			] as $key
		) {
			$value = $expected[$key];
			if (strpos($value, "/") === 0) {
				// this is regex
				$this->assertMatchesRegularExpression(
					$value,
					$return_lang_settings[$key]
				);
			} else {
				// assert equal
				$this->assertEquals(
					$value,
					$return_lang_settings[$key],
				);
			}
		}
		// unset all vars
		$_SESSION = [];
		unset($GLOBALS['OVERRIDE_LANG']);
	}
}

// __END__
