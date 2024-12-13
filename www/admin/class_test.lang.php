<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-lang';
$SET_SESSION_NAME = EDIT_SESSION_NAME;
$session = new CoreLibs\Create\Session($SET_SESSION_NAME);
ob_end_flush();

$PAGE_NAME = 'TEST CLASS: LANG';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

use CoreLibs\Language\L10n;
use CoreLibs\Language;
use CoreLibs\Debug\Support;

echo "<br><b>LIST LOCALES</b><br>";

$locale = 'en_US.UTF-8';
$locales = Language\L10n::listLocales($locale);
print "[" . $locale . "] LOCALES: " . Support::printAr($locales) . "<br>";
$locale = 'en.UTF-8';
$locales = Language\L10n::listLocales($locale);
print "[" . $locale . "] LOCALES: " . Support::printAr($locales) . "<br>";

echo "<br><b>PARSE LOCAL</b><br>";
$locale = 'en_US.UTF-8';
$locale_info = Language\L10n::parseLocale($locale);
print "[" . $locale . "] INFO: " . Support::printAr($locale_info) . "<br>";
$locale = 'en.UTF-8';
$locale_info = Language\L10n::parseLocale($locale);
print "[" . $locale . "] INFO: " . Support::printAr($locale_info) . "<br>";

/* echo "<br><b>AUTO DETECT</b><br>";
// DEPRECATED
// $get_locale = Language\GetLocale::setLocale();
// print "[AUTO, DEPRECATED]: " . Support::printAr($get_locale) . "<br>";
$get_locale = Language\GetLocale::setLocaleFromSession(
	SITE_LOCALE,
	str_replace('/', '', CONTENT_PATH),
	'',
	BASE . INCLUDES . LOCALE
);
print "[NAMED CONSTANTS OUTSIDE]: " . Support::printAr($get_locale) . "<br>";
$get_locale = Language\GetLocale::setLocaleFromSession(
	'en',
	'foo',
	'ISO-8895',
	BASE . INCLUDES . LOCALE
);
print "[OVERRIDE]: " . Support::printAr($get_locale) . "<br>";
// must set session vars for setLangFromSession
// DEFAULT_LOCALE
// DEFAULT_DOMAIN
// DEFAULT_CHARSET (should be set from DEFAULT_LOCALE)
// LOCALE_PATH
$session->setMany([
	'DEFAULT_LOCALE' => 'ja_JP.UTF-8',
	'DEFAULT_CHARSET' => 'UTF-8',
	'DEFAULT_DOMAIN' => 'admin',
	'LOCALE_PATH' => BASE . INCLUDES . LOCALE,
]);
$get_locale = Language\GetLocale::setLocaleFromSession(
	SITE_LOCALE,
	SITE_DOMAIN,
	SITE_ENCODING,
	BASE . INCLUDES . LOCALE
);
print "[SESSION SET]: " . Support::printAr($get_locale) . "<br>";
// must set session vars for setLangFromSession
// DEFAULT_LOCALE
// DEFAULT_DOMAIN
// DEFAULT_CHARSET (should be set from DEFAULT_LOCALE)
// LOCALE_PATH
$session->setMany([
	'DEFAULT_LOCALE' => '00000#####',
	'DEFAULT_CHARSET' => '',
	'DEFAULT_DOMAIN' => 'admin',
	'LOCALE_PATH' => BASE . INCLUDES . LOCALE,
]);
$get_locale = Language\GetLocale::setLocaleFromSession(
	SITE_LOCALE,
	SITE_DOMAIN,
	SITE_ENCODING,
	BASE . INCLUDES . LOCALE
);
print "[SESSION SET INVALID]: " . Support::printAr($get_locale) . "<br>";
 */

// try to load non existing
echo "<br><b>NEW TYPE</b><br>";
// translate string
$string = 'INPUT TEST';
// new path test
$lang = 'ja';
$domain = 'admin';
$encoding = 'UTF-8';
$path = BASE . INCLUDES . LOCALE;
// load direct
echo "* <b>NEW CLASS SET</b><br>";
$l = new L10n($lang, $domain, $path, $encoding);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "CONTENT PATH: " . $l->getBaseContentPath() . "<br>";
echo "DOMAIN PATH: " . $l->getTextDomain($domain) . "<br>";
echo "BASE PATH: " . $l->getBaseLocalePath() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";
$single_string = 'single';
$multi_string = 'multi';
for ($n = 0; $n <= 3; $n++) {
	echo "MULTI TEST $n: " . $single_string . "/" . $multi_string . " => "
		. $l->__n($single_string, $multi_string, $n) . "<br>";
}
$context = "month name";
$context_string = "May";
echo "CONTEXT TRANSLATION: " . $context_string . " => " . $l->__p($context, $context_string) . "<br>";
$single_string = 'single';
$multi_string = 'multi';
for ($n = 0; $n <= 3; $n++) {
	echo "CONTEXT MULTI TEST $n: " . $single_string . "/" . $multi_string . " => "
		. $l->__np($context, $single_string, $multi_string, $n) . "<br>";
}
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
// change domain
$domain = 'frontend';
echo "* <b>CHANGE DOMAIN $domain</b><br>";
$l->getTranslator('', $domain, $path);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "CONTENT PATH: " . $l->getBaseContentPath() . "<br>";
echo "DOMAIN PATH: " . $l->getTextDomain($domain) . "<br>";
echo "BASE PATH: " . $l->getBaseLocalePath() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
// change language short type
$lang = 'en';
$domain = 'admin';
echo "* <b>CHANGE LANG $lang AND DOMAIN $domain</b><br>";
$l->getTranslator($lang, $domain, $path);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "CONTENT PATH: " . $l->getBaseContentPath() . "<br>";
echo "DOMAIN PATH: " . $l->getTextDomain($domain) . "<br>";
echo "BASE PATH: " . $l->getBaseLocalePath() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
$encoding = 'SJIS';
echo "* <b>SET DIFFERENT CHARSET $encoding</b><br>";
$l->getTranslator($lang, $domain, $path, $encoding);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "CONTENT PATH: " . $l->getBaseContentPath() . "<br>";
echo "DOMAIN PATH: " . $l->getTextDomain($domain) . "<br>";
echo "BASE PATH: " . $l->getBaseLocalePath() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
// chang to wrong language
$lang = 'tr';
echo "* <b>CHANGE NOT FOUND LANG $lang</b><br>";
$l->getTranslator($lang, $domain, $path);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "CONTENT PATH: " . $l->getBaseContentPath() . "<br>";
echo "DOMAIN PATH: " . $l->getTextDomain($domain) . "<br>";
echo "BASE PATH: " . $l->getBaseLocalePath() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
// set different encoding
$lang = 'ja';
$domain = 'admin';
$encoding = 'SJIS';
echo "* <b>CLASS NEW LAUNCH: $lang / $encoding</b><br>";
$path = BASE . INCLUDES . LOCALE;
// load direct
$l = new L10n($lang, $domain, $path, $encoding);
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";
// lang with full set
$lang = 'ja_JP.UTF-8';
$domain = 'admin';
$encoding = 'SJIS';
echo "* <b>CLASS NEW LAUNCH: $lang / $encoding</b><br>";
$path = BASE . INCLUDES . LOCALE;
// load direct
$l = new L10n($lang, $domain, $path, $encoding);
echo "LOCALE: " . Support::printAr($l->getLocaleAsArray()) . "<br>";

$lang = 'en';
$domain = 'admin';
echo "<br><b>STATIC TYPE TEST</b><br>";
// static tests from l10n_load
L10n::getInstance()->setLocale($lang);
echo "SET LOCALE: " . L10n::getInstance()->getLocale() . "<br>";
L10n::getInstance()->setDomain($domain);
echo "SET DOMAIN: " . L10n::getInstance()->getDomain() . "<br>";
L10n::getInstance()->setTextDomain($domain, $path);
echo "SET TEXT DOMAIN: " . L10n::getInstance()->getTextDomain($domain) . "<br>";
// L10n::getInstance()->setOverrideEncoding('SJIS');
// null call __bind_textdomain_codeset
echo "INPUT TEST: " . $string . " => " . L10n::getInstance()->getTranslator()->gettext($string) . "<br>";
echo "LOCALE: " . Support::printAr(L10n::getInstance()->getLocaleAsArray()) . "<br>";

echo "<br><b>FUNCTIONS</b><br>";
// real statisc test
L10n::loadFunctions();
$locale = 'ja';
_setlocale(LC_MESSAGES, $locale);
_textdomain($domain);
_bindtextdomain($domain, $path);
_bind_textdomain_codeset($domain, $encoding);
echo "INPUT TEST $locale: " . $string . " => " . __($string) . "<br>";
$single_string = 'single';
$multi_string = 'multi';
for ($n = 0; $n <= 3; $n++) {
	echo "MULTI TEST $n: " . $single_string . "/" . $multi_string . " => "
		. _ngettext($single_string, $multi_string, $n) . "<br>";
}

$locale = 'en_US.UTF-8';
_setlocale(LC_MESSAGES, $locale);
_textdomain($domain);
_bindtextdomain($domain, $path);
_bind_textdomain_codeset($domain, $encoding);
echo "INPUT TEST $locale: " . $string . " => " . __($string) . "<br>";
$single_string = 'single';
$multi_string = 'multi';
for ($n = 0; $n <= 3; $n++) {
	echo "MULTI TEST $n: " . $single_string . "/" . $multi_string . " => "
		. _ngettext($single_string, $multi_string, $n) . "<br>";
}

print "</body></html>";

// __END__
