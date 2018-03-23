<?php

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

// namespace test
ob_start();

// admin class tests
require 'config.inc' ;
DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);

echo "CONFIG: ".CONFIG."<br>ROOT: ".ROOT."<br>BASE: ".BASE."<br>";

$lang = 'en_utf8';
$base = new CoreLibs\Admin\Backend($DB_CONFIG[MAIN_DB], $lang);

print "ByteStringFormat: ".$base->ByteStringFormat(1234567.12)."<br>";
print "byteStringFormat: ".$base->byteStringFormat(1234567.12)."<br>";
print "get_page_name: ".$base->get_page_name()."<br>";
print "getPageName: ".$base->getPageName()."<br>";

print "DB Info: ".$base->dbInfo(1);

ob_end_flush();

print $base->printErrorMsg();

# __END__
