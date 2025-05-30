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
$LOG_FILE_ID = 'classTest-uids';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$_uids = new CoreLibs\Create\Uids();
use CoreLibs\Create\Uids;
$uids_class = 'CoreLibs\Create\Uids';

$PAGE_NAME = 'TEST CLASS: UIDS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// class
print "UUIDV4: " . $_uids->uuidv4() . "<br>";
print "UNIQID (d): " . $_uids->uniqId() . "<br>";
print "UNIQID (md5): " . $_uids->uniqId('md5') . "<br>";
print "UNIQID (sha256): " . $_uids->uniqId('sha256') . "<br>";
// static
print "S::UUIDV4: " . $uids_class::uuidv4() . "<br>";
print "S::UNIQID (d): " . $uids_class::uniqId() . "<br>";
print "S::UNIQID (md5): " . $uids_class::uniqId('md5') . "<br>";
print "S::UNIQID (sha256): " . $uids_class::uniqId('sha256') . "<br>";
// with direct length
print "S:UNIQID (0->4): " . Uids::uniqId(0) . "<br>";
print "S:UNIQID (9->8): " . Uids::uniqId(9) . "<br>";
print "S:UNIQID (9,true): " . Uids::uniqId(9, true) . "<br>";
print "S:UNIQID (512): " . Uids::uniqId(512) . "<br>";
// uniq ids
print "UNIQU ID SHORT : " . Uids::uniqIdShort() . "<br>";
print "UNIQU ID LONG : " . Uids::uniqIdLong() . "<br>";
// validate
$uuidv4 = Uids::uuidv4();
if (!Uids::validateUuuidv4($uuidv4)) {
	print "Invalid UUIDv4: " . $uuidv4 . "<br>";
}
if (!Uids::validateUuuidv4("foobar")) {
	print "Invalid UUIDv4: hard coded<Br>";
}

// DEPRECATED
/* print "D/UUIDV4: ".$basic->uuidv4()."<br>";
print "/DUNIQID (d): ".$basic->uniqId()."<br>"; */

print "</body></html>";

// __END__
