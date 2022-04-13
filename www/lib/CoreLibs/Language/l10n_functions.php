<?php

declare(strict_types=1);

use CoreLibs\Language\L10n;

/**
 * Sets a requested locale.
 *
 * @param int    $category Locale category, ignored
 * @param string $locale   Locale name
 *
 * @return string Set or current locale
 */
function __setlocale(int $category, string $locale): string
{
	return L10n::getInstance()->setLocale($locale);
}

/**
 * Sets the path for a domain.
 *
 * @param string $domain Domain name
 * @param string $path   Path where to find locales
 */
function __bindtextdomain(string $domain, string $path): void
{
	L10n::getInstance()->setTextDomain($domain, $path);
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
function __bind_textdomain_codeset(string $domain, string $codeset): void
{
}

/**
 * Sets the default domain.
 *
 * @param string $domain Domain name
 */
function __textdomain(string $domain): void
{
	L10n::getInstance()->setDomain($domain);
}

/**
 * Translates a string.
 *
 * @param string $msgid String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function __gettext(string $msgid): string
{
	return L10n::getInstance()->getTranslator()->gettext(
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
	return L10n::getInstance()->getTranslator()->gettext(
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
function __ngettext(string $msgid, string $msgidPlural, int $number): string
{
	return L10n::getInstance()->getTranslator()->ngettext(
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
function __pgettext(string $msgctxt, string $msgid): string
{
	return L10n::getInstance()->getTranslator()->pgettext(
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
function __npgettext(string $msgctxt, string $msgid, string $msgidPlural, int $number): string
{
	return L10n::getInstance()->getTranslator()->npgettext(
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
function __dgettext(string $domain, string $msgid): string
{
	return L10n::getInstance()->getTranslator('', '', $domain)->gettext(
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
function __dngettext(string $domain, string $msgid, string $msgidPlural, int $number): string
{
	return L10n::getInstance()->getTranslator('', '', $domain)->ngettext(
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
function __dpgettext(string $domain, string $msgctxt, string $msgid): string
{
	return L10n::getInstance()->getTranslator('', '', $domain)->pgettext(
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
function __dnpgettext(string $domain, string $msgctxt, string $msgid, string $msgidPlural, int $number): string
{
	return L10n::getInstance()->getTranslator('', '', $domain)->npgettext(
		$msgctxt,
		$msgid,
		$msgidPlural,
		$number
	);
}
