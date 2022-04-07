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
	 * If language is set it must be in the format of:
	 * <lang>.<encoding>
	 * <lang>_<country>.<encoding>
	 * <lang>_<country>@<subset>.<encoding>
	 *
	 * @param  string|null $language
	 * @param  string|null $path
	 * @return array
	 */
	public static function setLangEncoding(
		?string $language = null,
		?string $domain = null,
		?string $path = null
	): array {
		$lang = '';
		$lang_short = '';
		$encoding = '';
		// if language is set, extract
		if (!empty($language)) {
			preg_match(
				"/^(([a-z]{2,})(_[A-Z]{2,})?(@[a-z]{1,})?)(\.([\w-])+)?$/",
				$language,
				$matches
			);
			// 1: lang (always)
			$lang = $matches[1] ?? '';
			// 2: lang short part
			$lang_short = $matches[2] ?? '';
			// 3: [ignore] sub part, if set, combined with lang
			// 4: [ignore] possible sub part, combined with lang in 1
			// 6: encoding if set
			$encoding = $matches[5] ?? '';
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
