<?php

/*
 * Internal function for getting locale and encodig settings
 * used for new locale layout
 */

declare(strict_types=1);

namespace CoreLibs\Language;

class GetLocale
{
	/**
	 * returns locale, lang, domain, encoding, path
	 * from either parameter set or from sessions/config variables
	 * NOTE: named constant usage is deprecated and will be removed in future
	 *
	 * @param  string|null $locale   override auto detect
	 * @param  string|null $domain   override domain
	 * @param  string|null $encoding override encoding
	 * @param  string|null $path     override path
	 * @return array<string,string>  locale, domain, encoding, path
	 */
	public static function setLocale(
		?string $locale = null,
		?string $domain = null,
		?string $encoding = null,
		?string $path = null
	): array {
		// locale must match at least basic rules
		if (
			empty($locale) ||
			!preg_match("/^[-A-Za-z0-9_.@]+$/", $locale)
		) {
			if (!empty($_SESSION['DEFAULT_LOCALE'])) {
				// parse from session (logged in)
				$locale = $_SESSION['DEFAULT_LOCALE'];
			} else {
				trigger_error(
					'setLocale: Unset $locale or unset SESSION locale is deprecated',
					E_USER_DEPRECATED
				);
				// else parse from site locale
				$locale = defined('SITE_LOCALE') && !empty(SITE_LOCALE) ?
					SITE_LOCALE :
					// else parse from default, if not 'en'
					/** @phpstan-ignore-next-line DEFAULT_LOCALE could be empty */
					(defined('DEFAULT_LOCALE') && !empty(DEFAULT_LOCALE) ?
						DEFAULT_LOCALE : 'en');
			}
		}
		// if domain is set, must be alphanumeric, if not unset
		if (
			empty($domain) ||
			!preg_match("/^\w+$/", $domain)
		) {
			if (!empty($_SESSION['DEFAULT_DOMAIN'])) {
				$domain = $_SESSION['DEFAULT_DOMAIN'];
			} else {
				trigger_error(
					'setLocale: Unset $domain is deprecated',
					E_USER_DEPRECATED
				);
				// if no domain is set, fall back to content path
				$domain = str_replace(DIRECTORY_SEPARATOR, '', CONTENT_PATH);
			}
		}
		// check that override encoding matches locale encoding
		// if locale encoding is set
		preg_match('/(?:\\.(?P<charset>[-A-Za-z0-9_]+))/', $locale, $matches);
		$locale_encoding = $matches['charset'] ?? null;
		if (
			// empty encoding
			empty($encoding) ||
			// not valid encoding
			!preg_match("/^[-A-Za-z0-9_]+$/", $encoding) ||
			// locale encoding set and not matching to encoding
			(!empty($locale_encoding) && $encoding != $locale_encoding)
		) {
			if (!empty($locale_encoding)) {
				$encoding = strtoupper($locale_encoding);
			} elseif (!empty($_SESSION['DEFAULT_CHARSET'])) {
				// else set from session
				$encoding = $_SESSION['DEFAULT_CHARSET'];
			} else {
				trigger_error(
					'setLocale: Short $locale with unset $encoding or unset SESSION encoding is deprecated',
					E_USER_DEPRECATED
				);
				// else set from site encoding
				$encoding = defined('SITE_ENCODING') && !empty(SITE_ENCODING) ?
					SITE_ENCODING :
					// or default encoding, if not 'UTF-8'
					/** @phpstan-ignore-next-line DEFAULT_LOCALE could be empty */
					(defined('DEFAULT_ENCODING') && !empty(DEFAULT_ENCODING) ?
						DEFAULT_ENCODING : 'UTF-8');
			}
		}
		// path checks if set, if not valid path unset to default BASE path
		if (
			empty($path) ||
			!is_dir($path)
		) {
			if (!empty($_SESSION['LOCALE_PATH'])) {
				$path = $_SESSION['LOCALE_PATH'];
			} else {
				trigger_error(
					'setLocale: Unset $path is deprecated',
					E_USER_DEPRECATED
				);
				$path = BASE . INCLUDES . LOCALE;
			}
		}
		// extract lang & country from locale string, else set to en
		if (
			preg_match(
				// lang
				'/^(?P<lang>[a-z]{2,3})'
				// country code
				. '(?:_(?P<country>[A-Z]{2}))?/',
				$locale,
				$matches
			)
		) {
			$lang = ($matches['lang'] ?? 'en')
				// add country only if set
				. (!empty($matches['country']) ? '_' . $matches['country'] : '');
		} else {
			$lang = 'en';
		}
		return [
			'locale' => $locale,
			'lang' => $lang,
			'domain' => $domain,
			'encoding' => $encoding,
			'path' => $path,
		];
	}
}

// __END__
