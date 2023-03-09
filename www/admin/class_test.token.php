<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-token';
ob_end_flush();

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);
$_token = new CoreLibs\Output\Form\Token();
$token_class = 'CoreLibs\Output\Form\Token';

$PAGE_NAME = 'TEST CLASS: FORM TOKEN';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

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
