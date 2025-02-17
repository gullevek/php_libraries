<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

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

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: CREATE EMAIL';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
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
	false,
	true,
	$log
);
print "SENDING A: " . $status . "<br>";
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
	false,
	true,
	$log
);
print "SENDING B: " . $status . "<br>";

$status = Email::sendEmail(
	'TEST',
	'BODY',
	'test@test.com',
	'Test Name',
	['a@a.com', 'b@b.com'],
	[],
	'UTF-8',
	false,
	true,
	$log
);
print "SENDING C: " . $status . "<br>";

// SUBJECT 日本語ｶﾀｶﾅﾊﾟ
$status = Email::sendEmail(
	'TEST 日本語ｶﾀｶﾅﾊﾟカタカナバ',
	'BODY 日本語ｶﾀｶﾅﾊﾟカタカナバ',
	'test@test.com',
	'Test Name 日本語ｶﾀｶﾅﾊﾟ',
	[
		['email' => 'a@a.com', 'name' => 'a 日本語ｶﾀｶﾅﾊﾟカタカナバ'],
		['email' => 'b@b.com', 'name' => 'b 日本語ﾌﾟﾌﾞｶﾞﾊﾞｹブプガバケ'],
	],
	[],
	'UTF-8',
	false,
	true,
	$log
);
print "SENDING D: " . $status . "<br>";

print "</body></html>";

// __END__
