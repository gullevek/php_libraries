<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2004/11/18
* VERSION: 1.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTION:
* 	init class for gettext. Original was just a function & var setting include for wordpress.
*	I changed that to a class to be more portable with my style of coding
*
* PUBLIC VARIABLES
*
* PRIVATE VARIABLES
*
* PUBLIC METHODS
*	__:	returns string (translated or original if not found)
*	__e: echos out string (translated or original if not found)
*	__n:	should return plural. never tested this.
*
*   PRIVATE METHODS
*
* HISTORY:
* 2005/10/17 (cs) made an on the fly switch method (reload of lang)
*********************************************************************/

// TODO: default path change to <base>/lang/LC_MESSAGES/domain.encoding.mo
// for example: lang: ja_JP.UTF-8, domain: admin
// <base>/ja_JP/LC_MESSAGES/admin.UTF-8.mo
// OLD: includes/lang/admin/ja_utf8.mo
// NEW: includes/lang/ja_JP/LC_MESSAGES/admin.UTF-8.mo
// or fallback: includes/lang/ja/LC_MESSAGES/admin.UTF-8.mo

declare(strict_types=1);

namespace CoreLibs\Language;

use CoreLibs\Language\Core\FileReader;
use CoreLibs\Language\Core\GetTextReader;

class L10n
{
	/** @var string the current locale */
	private $locale = '';
	/** @var string the default selected/active domain */
	private $domain = '';
	/** @var array<string,array<string,GetTextReader>> locale > domain = translator */
	private $domains = [];
	/** @var array<string,string> bound paths for domains */
	private $paths = ['' => './'];
	/** @var string the full path to the mo file to loaded */
	private $mofile = '';
	/** @var string base path to search level */
	private $base_locale_path = '';
	/** @var string dynamic set path to where the mo file is actually */
	private $base_content_path = '';
	/** @var bool if load of mo file was unsuccessful */
	private $load_failure = false;

	/** @var FileReader|bool reader class for file reading, false for short circuit */
	private $input = false;
	/** @var GetTextReader reader class for MO data */
	private $l10n;
	/**
	 * @static
	 * @var L10n self class
	 */
	private static $instance;

	/**
	 * class constructor call for language getstring
	 * if locale is not empty will load translation
	 * else getTranslator needs to be called
	 *
	 * @param string $locale language name, default empty string
	 *                       will return self instance
	 * @param string $path   path, if empty fallback on default internal path
	 * @param string $domain override CONTENT_PATH . $encoding name for mo file
	 * @param bool   $legacy default true, if set to true, will look in the old
	 *                       folder format lang/ CONTENT_PATH / $lang . mo
	 */
	public function __construct(
		string $locale = '',
		string $path = '',
		string $domain = '',
		bool $legacy = true
	) {
		// load the mo file if locale is not empty
		if (!empty($locale)) {
			$this->getTranslator($locale, $path, $domain, $legacy);
		}
	}

	/**
	 * Returns the singleton L10n object.
	 * For function wrapper use
	 *
	 * @return L10n object
	 */
	public static function getInstance(): L10n
	{
		/** @phpstan-ignore-next-line */
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Loads global localization functions.
	 * prefixed with double underscore
	 * eg: gettext -> __gettext
	 */
	public static function loadFunctions(): void
	{
		require_once __DIR__ . '/l10n_functions.php';
	}

	/**
	 * legacy loader name for getTranslator
	 * instead of returning the GetTextReader object it returns
	 * true or false for successful load.
	 * NOTE: some time down the road this will be deprecated
	 *
	 * @param string $locale
	 * @param string $path
	 * @param string $domain
	 * @param bool   $legacy
	 * @return bool          Returns true for successfull load, false for error
	 */
	public function l10nReloadMOfile(
		string $locale,
		string $path = '',
		string $domain = '',
		bool $legacy = true
	): bool {
		$this->getTranslator($locale, $path, $domain, $legacy);
		return $this->load_failure ? false : true;
	}

	/**
	 * loads the mo file base on path, locale and domain set
	 *
	 * @param string $locale language name (optional), fallback is en
	 * @param string $path   path, if empty fallback on default internal path
	 * @param string $domain override CONTENT_PATH . $encoding name for mo file
	 * @param bool   $legacy default true, if set to true, will look in the old
	 *                       folder format lang/ CONTENT_PATH / $lang . mo
	 * @return GetTextReader the main gettext reader object
	 */
	public function getTranslator(
		string $locale = '',
		string $path = '',
		string $domain = '',
		bool $legacy = false
	): GetTextReader {
		// set local if not from parameter
		if (empty($locale)) {
			$locale = $this->locale;
		}
		// set domain if not given
		if (empty($domain)) {
			$domain = $this->domain;
		}
		// store old settings
		$old_mofile = $this->mofile;
		$old_lang = $this->locale;
		$old_domain = $this->domain;
		$old_base_locale_path = $this->base_locale_path;
		$old_base_content_path = $this->base_content_path;

		// legacy or new type
		// legacy will use the old lang/content/file.mo type as default
		// if path is not set, also locale is the file name
		// for new type it follows the gettext spec and path is just the
		// base folder where the mo files will be searched
		if ($legacy === true) {
			if (!is_dir($path)) {
				$this->base_locale_path = BASE . INCLUDES . LANG;
				$this->base_content_path = CONTENT_PATH;
				$path = $this->base_locale_path . $this->base_content_path;
			}
			$this->mofile = $path . $locale . ".mo";
		} else {
			// if new path is a dir
			// 1) from a previous set domain
			// 2) from method option as is
			// 3) fallback if BASE/INCLUDES/LOCALE set
			// 4) current dir
			if (!empty($this->paths[$domain]) && is_dir($this->paths[$domain])) {
				$this->base_locale_path = $this->paths[$domain];
			} elseif (is_dir($path)) {
				$this->base_locale_path = $path;
			} elseif (
				defined('BASE') && defined('INCLUDES') && defined('LOCALE')
			) {
				// set fallback base path if constant set
				$this->base_locale_path = BASE . INCLUDES . LOCALE;
			} else {
				$this->base_locale_path = './';
			}
			// now we loop over lang compositions to get the base path
			// then we check
			$locales = $this->listLocales($locale);
			foreach ($locales as $_locale) {
				$this->base_content_path = $_locale . DIRECTORY_SEPARATOR
					. 'LC_MESSAGES' . DIRECTORY_SEPARATOR;
				$this->mofile = $this->base_locale_path
					. $this->base_content_path
					. $domain . '.mo';
				if (file_exists($this->mofile)) {
					break;
				}
			}
		}

		// check if get a readable mofile
		if (is_readable($this->mofile)) {
			// locale and domain current wanted
			$this->locale = $locale;
			$this->domain = $domain;
			// set empty domains path with current locale
			if (empty($this->domains[$locale])) {
				$this->domains[$locale] = [];
			}
			// store current base path (without locale, etc)
			if (empty($this->paths[$domain])) {
				$this->paths[$domain] = $this->base_locale_path;
			}
			// file reader and mo reader
			$this->input = new FileReader($this->mofile);
			$this->l10n = new GetTextReader($this->input);
			// if short circuit is true, we failed to have a translator loaded
			$this->load_failure = $this->l10n->getShortCircuit();
			// below is not used at the moment, but can be to avoid reloading
			$this->domains[$this->locale][$domain] = $this->l10n;
		} elseif (!empty($old_mofile)) {
			// mo file not readable
			$this->load_failure = true;
			// else fall back to the old ones
			$this->mofile = $old_mofile;
			$this->locale = $old_lang;
			$this->domain = $old_domain;
			$this->base_locale_path = $old_base_locale_path;
			$this->base_content_path = $old_base_content_path;
		} else {
			// mo file not readable, no previous mo file set, set short circuit
			$this->load_failure = true;
			// dummy
			$this->l10n = new GetTextReader($this->input);
		}
		return $this->l10n;
	}

	/**
	 * return current set GetTextReader or return the one for given
	 * domain name if set
	 * This can be used to access all the public methods from the
	 * GetTextReader
	 *
	 * @param  string        $domain optional domain name
	 * @return GetTextReader
	 */
	public function getTranslatorClass(string $domain = ''): GetTextReader
	{
		if (!empty($domain) && !empty($this->domains[$this->locale][$domain])) {
			return $this->domains[$this->locale][$domain];
		}
		// if null return short circuit version
		if ($this->l10n === null) {
			return new GetTextReader($this->input);
		}
		return $this->l10n;
	}

	/**
	 * original:
	 * vendor/phpmyadmin/motranslator/src/Loader.php
	 *
	 * Returns array with all possible locale combinations based on the
	 * given locale name
	 *
	 * I.e. for sr_CS.UTF-8@latin, look through all of
	 * sr_CS.UTF-8@latin, sr_CS@latin, sr@latin, sr_CS.UTF-8, sr_CS, sr.
	 *
	 * @param  string $locale Locale string
	 * @return array<string>  List of locale path parts that can be possible
	 */
	public static function listLocales(string $locale): array
	{
		$locale_list = [];

		if (empty($locale)) {
			return $locale_list;
		}
		// is matching regex
		if (
			!preg_match(
				// language code
				'/^(?P<lang>[a-z]{2,3})'
				// country code
				. '(?:_(?P<country>[A-Z]{2}))?'
				// charset
				. '(?:\\.(?P<charset>[-A-Za-z0-9_]+))?'
				// @ modifier
				. '(?:@(?P<modifier>[-A-Za-z0-9_]+))?$/',
				$locale,
				$matches
			)
		) {
			// not matching, return as is
			return [$locale];
		}
		// do matching run
		$lang = $matches['lang'] ?? null;
		$country = $matches['country'] ?? null;
		$charset = $matches['charset'] ?? null;
		$modifier = $matches['modifier'] ?? null;
		// we need to add all possible cominations from not null set
		// entries to the list, from longest to shortest
		// %s_%s.%s@%s　(lang _ country . encoding @ suffix)
		// %s_%s@%s　(lang _ country @ suffix)
		// %s@%s　(lang @ suffix)
		// %s_%s.%s　(lang _ country . encoding)
		// %s_%s (lang _ country)
		// %s (lang)

		// if lang is set
		if ($lang) {
			// modifier group
			if ($modifier) {
				if ($country) {
					if ($charset) {
						array_push(
							$locale_list,
							sprintf('%s_%s.%s@%s', $lang, $country, $charset, $modifier)
						);
					}

					array_push(
						$locale_list,
						sprintf('%s_%s@%s', $lang, $country, $modifier)
					);
				} elseif ($charset) {
					array_push(
						$locale_list,
						sprintf('%s.%s@%s', $lang, $charset, $modifier)
					);
				}

				array_push(
					$locale_list,
					sprintf('%s@%s', $lang, $modifier)
				);
			}
			// country group
			if ($country) {
				if ($charset) {
					array_push(
						$locale_list,
						sprintf('%s_%s.%s', $lang, $country, $charset)
					);
				}

				array_push(
					$locale_list,
					sprintf('%s_%s', $lang, $country)
				);
			} elseif ($charset) {
				array_push(
					$locale_list,
					sprintf('%s.%s', $lang, $charset)
				);
			}
			// lang only
			array_push($locale_list, $lang);
		}

		// If the locale name doesn't match POSIX style, just include it as-is.
		if (!in_array($locale, $locale_list)) {
			array_push($locale_list, $locale);
		}

		return $locale_list;
	}

	/**
	 * tries to detect the locale set in the following order:
	 * - globals: LOCALE
	 * - globals: LANG
	 * - env: LC_ALL
	 * - env: LC_MESSAGES
	 * - env: LANG
	 * if nothing set, returns 'en' as default
	 *
	 * @return string
	 */
	public static function detectLocale(): string
	{
		// globals
		foreach (['LOCALE', 'LANG'] as $global) {
			if (!empty($GLOBALS[$global])) {
				return $GLOBALS[$global];
			}
		}
		// enviroment
		foreach (['LC_ALL', 'LC_MESSAGES', 'LANG'] as $env) {
			$locale = getenv($env);
			if ($locale !== false && !empty($locale)) {
				return $locale;
			}
		}
		return 'en';
	}

	/************
	 * INTERNAL VAR SET/GET
	 */

	/**
	 * Sets the path for a domain.
	 * must be set before running l10nReloadMOfile
	 *
	 * @param string $domain Domain name
	 * @param string $path   Path where to find locales
	 */
	public function setTextDomain(string $domain, string $path): void
	{
		$this->paths[$domain] = $path;
	}

	/**
	 * return set path for given domain
	 * if not found return false
	 *
	 * @param  string $domain
	 * @return string|bool
	 */
	public function getTextDomain(string $domain)
	{
		return $this->paths[$domain] ?? false;
	}

	/**
	 * sets the default domain.
	 *
	 * @param string $domain Domain name
	 */
	public function setDomain(string $domain): void
	{
		$this->domain = $domain;
	}

	/**
	 * return current set domain name
	 *
	 * @return string
	 */
	public function getDomain(): string
	{
		return $this->domain;
	}

	/**
	 * sets a requested locale.
	 *
	 * @param string $locale Locale name
	 * @return string Set or current locale
	 */
	public function setLocale(string $locale): string
	{
		if (!empty($locale)) {
			$this->locale = $locale;
		}
		return $this->locale;
	}

	/**
	 * get current set locale
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}

	/**
	 * get current set language
	 *
	 * @return string current set language string
	 * @deprecated Use getLocale()
	 */
	public function __getLang(): string
	{
		return $this->getLocale();
	}

	/**
	 * get current set mo file
	 *
	 * @return string current set mo language file
	 */
	public function getMoFile(): string
	{
		return $this->mofile;
	}

	/**
	 * get current set mo file
	 *
	 * @return string current set mo language file
	 * @deprecated  Use getMoFile()
	 */
	public function __getMoFile(): string
	{
		return $this->getMoFile();
	}

	/**
	 * get the current base path in which we search
	 *
	 * @return string
	 */
	public function getBaseLocalePath(): string
	{
		return $this->base_locale_path;
	}

	/**
	 * the path below the base path to where the mo file is located
	 *
	 * @return string
	 */
	public function getBaseContentPath(): string
	{
		return $this->base_content_path;
	}

	/**
	 * get the current load error status
	 * if true then the mo file failed to load
	 *
	 * @return bool
	 */
	public function getLoadError(): bool
	{
		return $this->load_failure;
	}

	/************
	 * TRANSLATION METHODS
	 */

	/**
	 * translates a string and returns translated text
	 *
	 * @param  string $text text to translate
	 * @return string       translated text
	 */
	public function __(string $text): string
	{
		// fallback passthrough
		if ($this->l10n === null) {
			return $text;
		}
		return $this->l10n->translate($text);
	}

	/**
	 * prints translated string out to the screen
	 * @param  string $text text to translate
	 * @return void         has no return
	 * @deprecated use echo __() instead
	 */
	public function __e(string $text): void
	{
		// fallback passthrough
		if ($this->l10n === null) {
			echo $text;
		}
		echo $this->l10n->translate($text);
	}

	/**
	 * Return the plural form.
	 *
	 * @param  string  $single string for single word
	 * @param  string  $plural string for plural word
	 * @param  int     $number number value
	 * @return string          translated plural string
	 */
	public function __n(string $single, string $plural, int $number): string
	{
		// in case nothing got set yet, this is fallback
		if ($this->l10n === null) {
			return $number > 1 ? $plural : $single;
		}
		return $this->l10n->ngettext($single, $plural, $number);
	}

	/**
	 * context translation via msgctxt
	 *
	 * @param  string $context context string
	 * @param  string $text    text to translate
	 * @return string
	 */
	public function __p(string $context, string $text): string
	{
		if ($this->l10n === null) {
			return $text;
		}
		return $this->l10n->pgettext($context, $text);
	}

	/**
	 * context translation via msgctxt
	 *
	 * @param  string $context context string
	 * @param  string $single  string for single word
	 * @param  string $plural  string for plural word
	 * @param  int    $number  number value
	 * @return string
	 */
	public function __pn(string $context, string $single, string $plural, int $number): string
	{
		if ($this->l10n === null) {
			return $number > 1 ? $plural : $single;
		}
		return $this->l10n->npgettext($context, $single, $plural, $number);
	}

	// alias functions to mimic gettext calls

	/**
	 * alias for gettext,
	 * calls __
	 *
	 * @param  string $text
	 * @return string
	 * @deprecated Use __()
	 */
	public function gettext(string $text): string
	{
		return $this->__($text);
	}

	/**
	 * alias for ngettext
	 * calls __n
	 *
	 * @param  string    $single
	 * @param  string    $plural
	 * @param  int       $number
	 * @return string
	 * @deprecated Use __n()
	 */
	public function ngettext(string $single, string $plural, int $number): string
	{
		return $this->__n($single, $plural, $number);
	}

	// TODO: dgettext(string $domain, string $message): string
	// TODO: dngettext(string $domain, string $singular, string $plural, int $count): string
	// TODO: dpgettext(string $domain, string $message, int $category): string
	// TODO: dpngettext(string $domain, string $singular, string $plural, int $count, int $category): string
}

// __END__
