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
	 * @deprecated use GetLocale::setLocaleSession(...) instead
	 */
	public static function setLocale(
		?string $locale = null,
		?string $domain = null,
		?string $encoding = null,
		?string $path = null
	): array {
		trigger_error(
			'Use \CoreLibs\Language\GetLocale::setLocaleSession(...) instead',
			E_USER_DEPRECATED
		);
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
					(defined('DEFAULT_ENCODING') ?
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
			$lang = $matches['lang']
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

	/**
	 * NOTE: For getting the login info via login class use ->loginGetLocale()
	 *
	 * Set locale from session or from override parameters
	 * This is the prefered version to setLocale
	 * It usese the following SESSION VARIABLES
	 * DEFAULT_LOCALE
	 * DEFAULT_DOMAIN
	 * DEFAULT_CHARSET (should be set from DEFAULT_LOCALE)
	 * LOCALE_PATH
	 * in the return array, null set invalid information
	 *
	 * @param  string $locale   override locale
	 * @param  string $domain   override domain
	 * @param  string $encoding override encoding
	 * @param  string $path     override path
	 * @return array<string,string>  locale, domain, encoding, path
	 * @return array<string,string|null> Return list of set locale information
	 * @deprecated This version will be removed in a future version use ACL\Login->loginGetLocale() instead
	 */
	public static function setLocaleFromSession(
		string $locale,
		string $domain,
		string $encoding,
		string $path
	): array {
		// locale must match at least basic rules
		if (
			!empty($_SESSION['DEFAULT_LOCALE']) &&
			preg_match("/^[-A-Za-z0-9_.@]+$/", $_SESSION['DEFAULT_LOCALE'])
		) {
			// parse from session (logged in)
			$locale = $_SESSION['DEFAULT_LOCALE'];
		} elseif (
			empty($locale) ||
			!preg_match("/^[-A-Za-z0-9_.@]+$/", $locale)
		) {
			$locale = null;
		}
		// if domain is set, must be alphanumeric, if not unset
		if (
			!empty($_SESSION['DEFAULT_DOMAIN']) &&
			preg_match("/^\w+$/", $_SESSION['DEFAULT_DOMAIN'])
		) {
			$domain = $_SESSION['DEFAULT_DOMAIN'];
		} elseif (
			empty($domain) ||
			!preg_match("/^\w+$/", $domain)
		) {
			$domain = null;
		}
		// check that override encoding matches locale encoding
		// if locale encoding is set
		preg_match('/(?:\\.(?P<charset>[-A-Za-z0-9_]+))/', $locale ?? '', $matches);
		$locale_encoding = $matches['charset'] ?? null;
		if (!empty($locale_encoding)) {
			$encoding = strtoupper($locale_encoding);
		} elseif (
			!empty($_SESSION['DEFAULT_CHARSET']) &&
			preg_match("/^[-A-Za-z0-9_]+$/", $_SESSION['DEFAULT_CHARSET'])
		) {
			$encoding = $_SESSION['DEFAULT_CHARSET'];
		} elseif (
			empty($encoding) ||
			// not valid encoding
			!preg_match("/^[-A-Za-z0-9_]+$/", $encoding)
		) {
			$encoding = null;
		}
		// path checks if set, if not valid path unset to default BASE path
		if (
			!empty($_SESSION['LOCALE_PATH']) &&
			is_dir($_SESSION['LOCALE_PATH'])
		) {
			$path = $_SESSION['LOCALE_PATH'];
		} elseif (
			empty($path) ||
			!is_dir($path)
		) {
			$path = null;
		}
		// extract lang & country from locale string, else set to en
		if (
			preg_match(
				// lang
				'/^(?P<lang>[a-z]{2,3})'
				// country code
				. '(?:_(?P<country>[A-Z]{2}))?/',
				$locale ?? '',
				$matches
			)
		) {
			$lang = $matches['lang']
				// add country only if set
				. (!empty($matches['country']) ? '_' . $matches['country'] : '');
		} else {
			$lang = null;
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
