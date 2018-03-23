<?php

// namespace test
ob_start();

// admin class tests
require 'config.inc' ;
DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);

echo "CONFIG: ".CONFIG."<br>ROOT: ".ROOT."<br>BASE: ".BASE."<br>";

$lang = 'en_utf8';
$base = new CoreLibs\Basic($DB_CONFIG[MAIN_DB]);

print "ByteStringFormat: ".$base->ByteStringFormat(1234567.12)."<br>";
print "byteStringFormat: ".$base->byteStringFormat(1234567.12)."<br>";

ob_end_flush();

# __END__
