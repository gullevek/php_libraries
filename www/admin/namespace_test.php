<?php declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

// namespace test
ob_start();

// admin class tests
require 'config.php';
DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);

echo "DIR: ".DIR."<br>ROOT: ".ROOT."<br>BASE: ".BASE."<br>";

$lang = 'ja_utf8';
$base = new CoreLibs\Admin\Backend($DB_CONFIG[MAIN_DB], $lang);
ob_end_flush();

print "Start time: ".$base->runningTime()."<br>";
print "ByteStringFormat: ".$base->ByteStringFormat(1234567.12)."<br>";
print "byteStringFormat: ".$base->byteStringFormat(1234567.12)."<br>";
print "get_page_name [DEPRECATED]: ".$base->get_page_name()."<br>";
print "getPageName: ".$base->getPageName()."<br>";

print "DB Info: ".$base->dbInfo(1)."<br>";


print "End Time: ".$base->runningTime()."<br>";
print "Run Time: ".$base->runningTime()."<br>";
$base->resetRunningtime();

print "Lang: ".$base->l->__getLang().", MO File: ".$base->l->__getMoFile()."<br>";
print "Translate test: Year -> ".$base->l->__('Year')."<br>";

// end error print
print $base->printErrorMsg();

# __END__
