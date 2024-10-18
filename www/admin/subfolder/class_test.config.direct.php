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
require '../../configs/config.php';
// define log file id
$LOG_FILE_ID = 'classTest-config-direct';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: CONFIG DIRECT SUB';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="../class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "DIR: " . DIR . "<br>";
print "BASE: " . BASE . "<br>";
print "ROOT: " . ROOT . "<br>";
print "BASE NAME: " . BASE_NAME . "<br>";
echo "Config path prefix: " . ($CONFIG_PATH_PREFIX ?? '') . "<br>";
print "DB Name: " . DB_CONFIG_NAME . "<br>";
print "DB Config: " . \CoreLibs\Debug\Support::printAr(DB_CONFIG) . "<br>";

print "</body></html>";

// __END__
