<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-convert-colors';
ob_end_flush();

use CoreLibs\Convert\Colors;
use CoreLibs\Debug\Support as DgS;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);
$color_class = 'CoreLibs\Convert\Colors';

$PAGE_NAME = 'TEST CLASS: CONVERT COLORS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// define a list of from to color sets for conversion test

// A(out of bounds)
print "C::S/COLOR invalid rgb->hex (gray 125): -1, -1, -1: " . CoreLibs\Convert\Colors::rgb2hex(-1, -1, -1) . "<br>";
print "\$C::S/COLOR invalid rgb->hex (gray 125): -1, -1, -1: " . $color_class::rgb2hex(-1, -1, -1) . "<br>";
// B(valid)
$rgb = [10, 20, 30];
$hex = '#0a141e';
$hsb = [210, 67, 12];
$hsb_f = [210.5, 67.5, 12.5];
$hsl = [210, 50, 7.8];
print "S::COLOR rgb->hex: $rgb[0], $rgb[1], $rgb[2]: " . Colors::rgb2hex($rgb[0], $rgb[1], $rgb[2]) . "<br>";
print "S::COLOR hex->rgb: $hex: " . DgS::printAr(SetVarType::setArray(
	Colors::hex2rgb($hex)
)) . "<br>";
print "C::S/COLOR rgb->hext: $hex: " . DgS::printAr(SetVarType::setArray(
	CoreLibs\Convert\Colors::hex2rgb($hex)
)) . "<br>";
// C(to hsb/hsl)
print "S::COLOR rgb->hsb: $rgb[0], $rgb[1], $rgb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::rgb2hsb($rgb[0], $rgb[1], $rgb[2])
	)) . "<br>";
print "S::COLOR rgb->hsl: $rgb[0], $rgb[1], $rgb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::rgb2hsl($rgb[0], $rgb[1], $rgb[2])
	)) . "<br>";
// D(from hsb/hsl) Note that param 2 + 3 is always 0-100 divided
print "S::COLOR hsb->rgb: $hsb[0], $hsb[1], $hsb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsb2rgb($hsb[0], $hsb[1], $hsb[2])
	)) . "<br>";
print "S::COLOR hsb_f->rgb: $hsb_f[0], $hsb_f[1], $hsb_f[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsb2rgb($hsb_f[0], $hsb_f[1], $hsb_f[2])
	)) . "<br>";
print "S::COLOR hsl->rgb: $hsl[0], $hsl[1], $hsl[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsl2rgb($hsl[0], $hsl[1], $hsl[2])
	)) . "<br>";

$hsb = [0, 0, 5];
print "S::COLOR hsb->rgb: $hsb[0], $hsb[1], $hsb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsb2rgb($hsb[0], $hsb[1], $hsb[2])
	)) . "<br>";

// Random text
$h = rand(0, 359);
$s = rand(15, 70);
$b = 100;
$l = 50;
print "RANDOM IN: H: " . $h . ", S: " . $s . ", B/L: " . $b . "/" . $l . "<br>";
print "RANDOM hsb->rgb: <pre>" . DgS::printAr(SetVarType::setArray(Colors::hsb2rgb($h, $s, $b))) . "</pre><br>";
print "RANDOM hsl->rgb: <pre>" . DgS::printAr(SetVarType::setArray(Colors::hsl2rgb($h, $s, $l))) . "</pre><br>";

// TODO: run compare check input must match output

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
