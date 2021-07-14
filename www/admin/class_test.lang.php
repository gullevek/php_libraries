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

$l = new CoreLibs\Language\L10n($lang);
ob_end_flush();

print "<html><head><title>TEST CLASS: LANG</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$string = 'INPUT TEST';

echo "LANGUAGE SET: " . $l->__getLang() . "<br>";
echo "LANGUAGE FILE: " . $l->__getMoFile() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";

// switch to other language
$lang = 'ja_utf8';
$l->l10nReloadMOfile($lang);

echo "LANGUAGE SET: " . $l->__getLang() . "<br>";
echo "LANGUAGE FILE: " . $l->__getMoFile() . "<br>";
echo "INPUT TEST: " . $string . " => " . $l->__($string) . "<br>";
// TODO: run compare check input must match output

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
