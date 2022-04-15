<?php

/*
 * Internal function for getting languange and encodig settings
 */

declare(strict_types=1);

namespace CoreLibs\Language;

class GetSettings
{
	/**
	 * Sets encoding and language
	 * Can be overridden with language + path to mo file
	 * If locale is set it must be in the format of:
	 * <lang>.<encoding>
	 * <lang>_<country>.<encoding>
	 * <lang>_<country>.<encoding>@<subset>
	 * If no encoding is set in the is, UTF-8 is assumed
	 *
	 * Returned is an array with array indes and dictionary index
	 * 0~4 array are
	 * encoding: 0
	 * lang: 1
	 * lang_short: 2
	 * domain: 3
	 * path: 4
	 *
	 * @param  string|null $locale A valid locale name
	 * @param  string|null $path   A valid path where the mo files will be based
	 * @return array<int|string,string> Settings as array/dictionary
	 * @deprecated Use CoreLibs\Language\GetLocale::setLocale()
	 */
	public static function setLangEncoding(
		?string $locale = null,
		?string $domain = null,
		?string $path = null
	): array {
		$lang = '';
		$lang_short = '';
		$encoding = '';
		// if is is set, extract
		if (!empty($locale)) {
			preg_match(
				// language code
				'/^(?P<lang>[a-z]{2,3})'
				// _ country code
				. '(?:_(?P<country>[A-Z]{2}))?'
				// . charset
				. '(?:\\.(?P<charset>[-A-Za-z0-9_]+))?'
				// @ modifier
				. '(?:@(?P<modifier>[-A-Za-z0-9_]+))?$/',
				$locale,
				$matches
			);
			// lang short part
			$lang_short = $matches['lang'] ?? '';
			$lang = $lang_short;
			// lang + country if country is set
			if (!empty($matches['country'])) {
				$lang = sprintf('%s_%s', $lang_short, $matches['country']);
			}
			// encoding if set
			$encoding = strtoupper($matches['charset'] ?? 'UTF-8');
		}
		// if domain is set, must be alphanumeric, if not unset
		if (
			!empty($domain) &&
			!preg_match("/^\w+$/", $domain)
		) {
			$domain = '';
		}
		// path checks if set, if not valid path unset
		if (
			!empty($path) &&
			!is_dir($path)
		) {
			$path = '';
		}

		// just emergency fallback for language
		// set encoding
		if (empty($encoding)) {
			if (!empty($_SESSION['DEFAULT_CHARSET'])) {
				$encoding = $_SESSION['DEFAULT_CHARSET'];
			} else {
				$encoding = DEFAULT_ENCODING;
			}
		}
		// gobal override
		if (empty($lang)) {
			if (!empty($GLOBALS['OVERRIDE_LANG'])) {
				$lang = $GLOBALS['OVERRIDE_LANG'];
			} elseif (!empty($_SESSION['DEFAULT_LANG'])) {
				// session (login)
				$lang = $_SESSION['DEFAULT_LANG'];
			} else {
				// mostly default SITE LANG or DEFAULT LANG
				$lang = defined('SITE_LANG') && !empty(SITE_LANG) ?
					SITE_LANG :
					DEFAULT_LANG;
			}
		}
		// create the char lang encoding
		if (empty($lang_short)) {
			$lang_short = substr($lang, 0, 2);
		}
		// set the language folder
		if (empty($path)) {
			// LEGACY
			$path = BASE . INCLUDES . LANG . CONTENT_PATH;
			// will be BASE . INCLUDES . LANG . $language . /LC_MESSAGES/
			// so CONTENT_PATH has to be removed
		}
		// if no domain is set, fall back to content path
		if (empty($domain)) {
			$domain = str_replace('/', '', CONTENT_PATH);
		}
		// return
		return [
			// as array
			0 => $encoding,
			1 => $lang,
			2 => $lang_short,
			3 => $domain,
			4 => $path,
			// with index name
			// encoding
			'encoding' => $encoding,
			// language full string, eg en_US
			'lang' => $lang,
			// lang short, if eg en_US only en
			'lang_short' => $lang_short,
			// translation domain (CONTENT_PATH)
			'domain' => $domain,
			// folder BASE ONLY
			'path' => $path,
		];
	}
}
