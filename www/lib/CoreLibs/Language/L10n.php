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
	/** @var string */
	private $lang = '';
	/** @var string */
	private $mofile = '';
	/** @var FileReader|bool */
	private $input;
	/** @var GetTextReader */
	private $l10n;

	/**
	 * class constructor call for language getstring
	 * @param string $lang language name (optional), fallback is en
	 * @param string $path path, if empty fallback on default internal path
	 */
	public function __construct(string $lang = '', string $path = '')
	{
		if (!$lang) {
			$this->lang = 'en';
		} else {
			$this->lang = $lang;
		}

		// override path check
		if (!is_dir($path)) {
			$path = BASE . INCLUDES . LANG . CONTENT_PATH;
		}

		$this->mofile = $path . $this->lang . ".mo";

		// check if get a readable mofile
		if (is_readable($this->mofile)) {
			$this->input = new FileReader($this->mofile);
		} else {
			$this->input = false;
		}
		$this->l10n = new GetTextReader($this->input);
	}

	/**
	 * reloads the mofile, if the location of the lang file changes
	 * @param  string $lang language to reload data
	 * @param  string $path optional path, if not set fallback on internal
	 * @return bool         successfull reload true/false
	 */
	public function l10nReloadMOfile(string $lang, string $path = ''): bool
	{
		$success = false;
		$old_mofile = $this->mofile;
		$old_lang = $this->lang;

		$this->lang = $lang;

		// override path check
		if (!is_dir($path)) {
			$path = BASE . INCLUDES . LANG . CONTENT_PATH;
		}

		$this->mofile = $path . $this->lang . ".mo";

		// check if get a readable mofile
		if (is_readable($this->mofile)) {
			$this->input = new FileReader($this->mofile);
			$this->l10n = new GetTextReader($this->input);
			// we successfully loaded
			$success = true;
		} else {
			// else fall back to the old ones
			$this->mofile = $old_mofile;
			$this->lang = $old_lang;
		}
		return $success;
	}

		/**
	 * get current set language
	 * @return string current set language string
	 */
	public function __getLang(): string
	{
		return $this->lang;
	}

	/**
	 * get current set mo file
	 * @return string current set mo language file
	 */
	public function __getMoFile(): string
	{
		return $this->mofile;
	}

	/**
	 * translates a string and returns translated text
	 * @param  string $text text to translate
	 * @return string       translated text
	 */
	public function __(string $text): string
	{
		return $this->l10n->translate($text);
	}

	/**
	 * prints translated string out to the screen
	 * @param  string $text text to translate
	 * @return void         has no return
	 */
	public function __e(string $text): void
	{
		echo $this->l10n->translate($text);
	}

	// Return the plural form.
	/**
	 * Return the plural form.
	 * @param  string    $single string for single word
	 * @param  string    $plural string for plural word
	 * @param  int|float $number number value
	 * @return string            translated plural string
	 */
	public function __n(string $single, string $plural, $number): string
	{
		return $this->l10n->ngettext($single, $plural, $number);
	}

	// alias functions to mimic gettext calls

	/**
	 * Undocumented function
	 *
	 * @param  string $text
	 * @return string
	 */
	public function gettext(string $text): string
	{
		return $this->__($text);
	}

	/**
	 * Undocumented function
	 *
	 * @param  string    $single
	 * @param  string    $plural
	 * @param  int|float $number
	 * @return string
	 */
	public function ngettext(string $single, string $plural, $number): string
	{
		return $this->__n($single, $plural, $number);
	}

	// TODO: dgettext(string $domain, string $message): string
	// TODO: dngettext(string $domain, string $singular, string $plural, int $count): string
	// TODO: dcgettext(string $domain, string $message, int $category): string
	// TODO: dcngettext(string $domain, string $singular, string $plural, int $count, int $category): string
}

// __END__
