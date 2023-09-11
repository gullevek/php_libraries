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
$LOG_FILE_ID = 'classTest-error_msg';
ob_end_flush();

use CoreLibs\Logging\Logger\MessageLevel as ml;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: ERROR MSG';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$em = new \CoreLibs\Logging\ErrorMessage($log);

print "FN: " . ml::fromName('Affe')->name . "<br>";
print "NU: " . ml::fromValue(100)->name . "<br>";
print "NU: " . ml::fromValue(1000)->name . "<br>";

$em->setErrorMsg('123', 'error', 'msg this is bad, not logged');
$em->setErrorMsg('123', 'error', 'msg this is bad, logged', log_error:true);
$em->setErrorMsg('1000', 'info', 'This is good');
$em->setErrorMsg('9999', 'abort', 'BAD: This is critical (abort)');
$em->setErrorMsg('10-1000', 'wrong', 'Wrong level: This is emergency');
print "ErrorsLast: <pre>" . $log->prAr($em->getLastErrorMsg()) . "</pre>";
print "ErrorsIds: <pre>" . $log->prAr($em->getErrorIds()) . "</pre>";
print "Errors: <pre>" . $log->prAr($em->getErrorMsg()) . "</pre>";

print "</body></html>";

// __END__
