<?php declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

// namespace test
ob_start();

// admin class tests
require 'config.php';
$SET_SESSION_NAME = EDIT_SESSION_NAME;

echo "DIR: ".DIR."<br>ROOT: ".ROOT."<br>BASE: ".BASE."<br>";

$base = new CoreLibs\Admin\Backend(DB_CONFIG);
ob_end_flush();
if ($base->getConnectionStatus()) {
	die("Cannot connect to database");
}

print "Start time: ".\CoreLibs\Debug\RunningTime::runningTime()."<br>";
print "HumanReadableByteFormat: ".\CoreLibs\Convert\Byte::HumanReadableByteFormat(1234567.12)."<br>";
print "humanReadableByteFormat: ".\CoreLibs\Convert\Byte::humanReadableByteFormat(1234567.12)."<br>";
print "getPageName: ". \CoreLibs\Get\System::getPageName()."<br>";

print "DB Info: ".$base->dbInfo(true)."<br>";


print "End Time: ".\CoreLibs\Debug\RunningTime::runningTime()."<br>";
print "Start Time: ".\CoreLibs\Debug\RunningTime::runningTime()."<br>";

print "Lang: ".$base->l->__getLang().", MO File: ".$base->l->__getMoFile()."<br>";
print "Translate test: Year -> ".$base->l->__('Year')."<br>";

print "End Time: ".\CoreLibs\Debug\RunningTime::runningTime()."<br>";
// end error print
print $base->log->printErrorMsg();

# __END__
