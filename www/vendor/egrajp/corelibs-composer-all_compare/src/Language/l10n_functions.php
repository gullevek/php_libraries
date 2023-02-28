<?php

/*********************************************************************
* Original: https://github.com/phpmyadmin/motranslator
* Has the same function names, but uses a different base system
* setlocale -> setLocale
* bindtextdomain -> setTextDomain
* textdomain -> setDomain
*********************************************************************/

declare(strict_types=1);

use CoreLibs\Language\L10n as Loader;

/**
 * Sets a requested locale.
 *
 * @param int    $category Locale category, ignored
 * @param string $locale   Locale name
 *
 * @return string Set or current locale
 */
function _setlocale(int $category, string $locale): string
{
	return Loader::getInstance()->setLocale($locale);
}

/**
 * Sets the path for a domain.
 *
 * @param string $domain Domain name
 * @param string $path   Path where to find locales
 */
function _bindtextdomain(string $domain, string $path): void
{
	Loader::getInstance()->setTextDomain($domain, $path);
}

/**
 * Dummy compatibility function, MoTranslator assumes
 * everything is using same character set on input and
 * output.
 *
 * Generally it is wise to output in UTF-8 and have
 * mo files in UTF-8.
 *
 * @param string $domain  Domain where to set character set
 * @param string $codeset Character set to set
 */
function _bind_textdomain_codeset(string $domain, string $codeset): void
{
}

/**
 * Sets the default domain.
 *
 * @param string $domain Domain name
 */
function _textdomain(string $domain): void
{
	Loader::getInstance()->setDomain($domain);
}

/**
 * Translates a string.
 *
 * @param string $msgid String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function _gettext(string $msgid): string
{
	return Loader::getInstance()->getTranslator()->gettext(
		$msgid
	);
}

/**
 * Translates a string, alias for _gettext.
 *
 * @param string $msgid String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function __(string $msgid): string
{
	return Loader::getInstance()->getTranslator()->gettext(
		$msgid
	);
}

/**
 * Plural version of gettext.
 *
 * @param string $msgid       Single form
 * @param string $msgidPlural Plural form
 * @param int    $number      Number of objects
 *
 * @return string translated plural form
 */
function _ngettext(string $msgid, string $msgidPlural, int $number): string
{
	return Loader::getInstance()->getTranslator()->ngettext(
		$msgid,
		$msgidPlural,
		$number
	);
}

/**
 * Translate with context.
 *
 * @param string $msgctxt Context
 * @param string $msgid   String to be translated
 *
 * @return string translated plural form
 */
function _pgettext(string $msgctxt, string $msgid): string
{
	return Loader::getInstance()->getTranslator()->pgettext(
		$msgctxt,
		$msgid
	);
}

/**
 * Plural version of pgettext.
 *
 * @param string $msgctxt     Context
 * @param string $msgid       Single form
 * @param string $msgidPlural Plural form
 * @param int    $number      Number of objects
 *
 * @return string translated plural form
 */
function _npgettext(string $msgctxt, string $msgid, string $msgidPlural, int $number): string
{
	return Loader::getInstance()->getTranslator()->npgettext(
		$msgctxt,
		$msgid,
		$msgidPlural,
		$number
	);
}

/**
 * Translates a string.
 *
 * @param string $domain Domain to use
 * @param string $msgid  String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function _dgettext(string $domain, string $msgid): string
{
	return Loader::getInstance()->getTranslator('', '', $domain)->gettext(
		$msgid
	);
}

/**
 * Plural version of gettext.
 *
 * @param string $domain      Domain to use
 * @param string $msgid       Single form
 * @param string $msgidPlural Plural form
 * @param int    $number      Number of objects
 *
 * @return string translated plural form
 */
function _dngettext(string $domain, string $msgid, string $msgidPlural, int $number): string
{
	return Loader::getInstance()->getTranslator('', '', $domain)->ngettext(
		$msgid,
		$msgidPlural,
		$number
	);
}

/**
 * Translate with context.
 *
 * @param string $domain  Domain to use
 * @param string $msgctxt Context
 * @param string $msgid   String to be translated
 *
 * @return string translated plural form
 */
function _dpgettext(string $domain, string $msgctxt, string $msgid): string
{
	return Loader::getInstance()->getTranslator('', '', $domain)->pgettext(
		$msgctxt,
		$msgid
	);
}

/**
 * Plural version of pgettext.
 *
 * @param string $domain      Domain to use
 * @param string $msgctxt     Context
 * @param string $msgid       Single form
 * @param string $msgidPlural Plural form
 * @param int    $number      Number of objects
 *
 * @return string translated plural form
 */
function _dnpgettext(string $domain, string $msgctxt, string $msgid, string $msgidPlural, int $number): string
{
	return Loader::getInstance()->getTranslator('', '', $domain)->npgettext(
		$msgctxt,
		$msgid,
		$msgidPlural,
		$number
	);
}
