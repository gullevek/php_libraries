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
*	__ngettext:	should return plural. never tested this.
*
*   PRIVATE METHODS
*
* HISTORY:
* 2005/10/17 (cs) made an on the fly switch method (reload of lang)
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs\Language;

use CoreLibs\Language\Core\FileReader;
use CoreLibs\Language\Core\GetTextReader;

class L10n extends \CoreLibs\Basic
{
	private $lang = '';
	private $mofile = '';
	private $input;
	private $l10n;

	/**
	 * class constructor call for language getstring
	 * @param string $lang language name (optional), fallback is en
	 * @param string $path path, if empty fallback on default internal path
	 */
	public function __construct(string $lang = '', string $path = '')
	{
		parent::__construct();
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
	 * translates a string and returns translated text
	 * @param  string $text text to translate
	 * @return string       translated text
	 */
	public function __($text): string
	{
		return $this->l10n->translate($text);
	}

	/**
	 * prints translated string out to the screen
	 * @param  string $text text to translate
	 * @return void         has no return
	 */
	public function __e($text): void
	{
		echo $this->l10n->translate($text);
	}

	// Return the plural form.
	/**
	 * Return the plural form.
	 * @param  string $single string for single word
	 * @param  string $plural string for plural word
	 * @param  string $number number value
	 * @return string translated plural string
	 */
	public function __ngettext($single, $plural, $number)
	{
		return $this->l10n->ngettext($single, $plural, $number);
	}

	/**
	 * get current set language
	 * @return string current set language string
	 */
	public function __getLang()
	{
		return $this->lang;
	}

	/**
	 * get current set mo file
	 * @return string current set mo language file
	 */
	public function __getMoFile()
	{
		return $this->mofile;
	}
}

// __END__
