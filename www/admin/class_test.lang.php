<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// init language
$lang = 'en_utf8';
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-lang';
ob_end_flush();

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: LANG</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

use CoreLibs\Language\L10n;

$string = 'INPUT TEST';

$l = new CoreLibs\Language\L10n($lang);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";

// switch to other language
$lang = 'ja_utf8';
$l->l10nReloadMOfile($lang);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
// switch to non existing language
$lang = 'tr_utf8';
$l->l10nReloadMOfile($lang);
echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";

echo "<br><b>LIST LOCALES</b><br>";

$locale = 'en_US.UTF-8';
$locales = CoreLibs\Language\L10n::listLocales($locale);
print "[" . $locale . "] LOCALES: " . CoreLibs\Debug\Support::printAr($locales) . "<br>";
$locale = 'en.UTF-8';
$locales = CoreLibs\Language\L10n::listLocales($locale);
print "[" . $locale . "] LOCALES: " . CoreLibs\Debug\Support::printAr($locales) . "<br>";

// try to load non existing
echo "<br><b>NEW TYPE</b><br>";
// new path test
$lang = 'ja';
$domain = 'admin';
$encoding = 'UTF-8';
$path = BASE . INCLUDES . LOCALE;
$l = new CoreLibs\Language\L10n($lang, $path, $domain, false);

echo "LANGUAGE WANT/SET: " . $lang  . '/' . $l->getLocale() . "<br>";
echo "DOMAIN WANT/SET: " . $domain  . '/' . $l->getDomain() . "<br>";
echo "LANGUAGE FILE: " . $l->getMoFile() . "<br>";
echo "LOAD ERROR: " . $l->getLoadError() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
echo "TROUGH LOAD: " . $l->getTranslatorClass()->gettext($string) . "<br>";


echo "<br><b>STATIC TYPE</b><br>";
// static tests from l10n_load
L10n::getInstance()->setLocale($lang);
echo "SET LOCALE: " . L10n::getInstance()->getLocale() . "<br>";
L10n::getInstance()->setDomain($domain);
echo "SET DOMAIN: " . L10n::getInstance()->getDomain() . "<br>";
L10n::getInstance()->setTextDomain($domain, $path);
echo "SET DOMAIN: " . L10n::getInstance()->getTextDomain($domain) . "<br>";
// null call __bind_textdomain_codeset
echo "INPUT TEST: " . $string . " => " . L10n::getInstance()->getTranslator()->gettext($string) . "<br>";

echo "<br><b>FUNCTIONS</b><br>";
// real statisc test
L10n::loadFunctions();
__setlocale(LC_MESSAGES, $lang);
__textdomain($domain);
__bindtextdomain($domain, $path);
__bind_textdomain_codeset($domain, $encoding);
echo "INPUT TEST: " . $string . " => " . __($string) . "<br>";

print "</body></html>";

// __END__
