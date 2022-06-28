<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// will be overwritten in config.master.php depending on location
$DEBUG_ALL_OVERRIDE = true; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$ECHO_ALL = true;
$DB_DEBUG = true;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-create_email';
ob_end_flush();
// override echo all from config.master.php
$ECHO_ALL = true;

use CoreLibs\Create\Email;
use CoreLibs\Convert\Html;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL,
	'print_all' => $PRINT_ALL,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: CREATE EMAIL';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$from_name = '日本語';
$from_email = 'test@test.com';
print "SET: $from_name / $from_email: "
	. Html::htmlent(Email::encodeEmailName($from_email, $from_name)) . "<br>";

$status = Email::sendEmail(
	'TEST',
	'BODY',
	'test@test.com',
	'Test Name',
	[
		[
			'name' => 'To 1',
			'email' => 'to1@test.com'
		],
	],
	[],
	'UTF-8',
	true,
	$log
);
print "SENDING: " . $status . "<br>";
$status = Email::sendEmail(
	'TEST {REPLACE}',
	'BODY {OTHER}',
	'test@test.com',
	'Test Name',
	[
		[
			'name' => 'To 1-A',
			'email' => 'to1-a@test.com'
		],
		[
			'name' => 'To 2-A',
			'email' => 'to2-a@test.com',
			'replace' => [
				'OTHER' => '--FOR 2 A other--'
			]
		],
	],
	[
		'REPLACE' => '**replaced**',
		'OTHER' => '**other**'
	],
	'UTF-8',
	true,
	$log
);
print "SENDING: " . $status . "<br>";

$status = Email::sendEmail(
	'TEST',
	'BODY',
	'test@test.com',
	'Test Name',
	['a@a.com', 'b@b.com'],
	[],
	'UTF-8',
	true,
	$log
);
print "SENDING: " . $status . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
