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
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-token';
ob_end_flush();

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$basic = new CoreLibs\Basic($log);
$_token = new CoreLibs\Output\Form\Token();
$token_class = 'CoreLibs\Output\Form\Token';

print "<html><head><title>TEST CLASS: FORM TOKEN</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$token = 'test_form_token';
$token_id = $_token->setFormToken($token);
print "TOKEN: $token: (ID) " . $token_id . " => (S) " . $_SESSION[$token] . "<br>";
print "VALIDATE: $token: " . (string)$_token->validateFormToken($token_id, $token) . "<br>";

$token = 'test_form_token_static';
$token_id = $token_class::setFormToken($token);
print "S-TOKEN: $token: (ID) " . $token_id . " => (S) " . $_SESSION[$token] . "<br>";
print "S-VALIDATE: $token: " . (string)$token_class::validateFormToken($token_id, $token) . "<br>";

// DEPRECATED
/* $token = 'test_form_token_deprecated';
$token_id = $basic->setFormToken($token);
print "TOKEN: $token: (ID) ".$token_id." => (S) ".$_SESSION[$token]."<br>";
print "VALIDATE: $token: ".(string)$basic->validateFormToken($token_id, $token)."<br>"; */

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
