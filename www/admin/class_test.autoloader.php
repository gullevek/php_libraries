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
$LOG_FILE_ID = 'classTest-autoloader';
ob_end_flush();

# Test if composer autoloader works here

use CoreLibs\Convert\Byte;

$PAGE_NAME = 'TEST CLASS: AUTOLOADER';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$bytes = 10242424;
$_bytes = Byte::humanReadableByteFormat($bytes);
print "BYTES: " . $_bytes  . "<br>";

print "</body></html>";

// __END__
