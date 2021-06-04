<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-colors';
ob_end_flush();

$basic = new CoreLibs\Basic();
$color = new CoreLibs\Convert\Colors();
$color_class = 'CoreLibs\Convert\Colors';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: COLORS</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// A(out of bounds)
print "C::S/COLOR invalid rgb->hex (gray 125): -1, -1, -1: ".CoreLibs\Convert\Colors::rgb2hex(-1, -1, -1)."<br>";
print "\$C::S/COLOR invalid rgb->hex (gary 125): -1, -1, -1: ".$color_class::rgb2hex(-1, -1, -1)."<br>";
// B(valid)
$rgb = [10, 20, 30];
$hex = '#0a141e';
$hsb = [210, 67, 12];
$hsl = [210, 50, 7.8];
print "C/COLOR rgb->hex: $rgb[0], $rgb[1], $rgb[2]: ".$color->rgb2hex($rgb[0], $rgb[1], $rgb[2])."<br>";
print "C/COLOR hex->rgb: $hex: ".$basic->printAr($color->hex2rgb($hex))."<br>";
print "C::S/COLOR rgb->hext: $hex: ".$basic->printAr(CoreLibs\Convert\Colors::hex2rgb($hex))."<br>";
// C(to hsb/hsl)
print "C/COLOR rgb->hsb: $rgb[0], $rgb[1], $rgb[2]: ".$basic->printAr($color->rgb2hsb($rgb[0], $rgb[1], $rgb[2]))."<br>";
print "C/COLOR rgb->hsl: $rgb[0], $rgb[1], $rgb[2]: ".$basic->printAr($color->rgb2hsl($rgb[0], $rgb[1], $rgb[2]))."<br>";
// D(from hsb/hsl) Note that param 2 + 3 is always 0-100 divided
print "C/COLOR hsb->rgb: $hsb[0], $hsb[1], $hsb[2]: ".$basic->printAr($color->hsb2rgb($hsb[0], $hsb[1], $hsb[2]))."<br>";
print "C/COLOR hsl->rgb: $hsl[0], $hsl[1], $hsl[2]: ".$basic->printAr($color->hsl2rgb($hsl[0], $hsl[1], $hsl[2]))."<br>";

// TODO: run compare check input must match output

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
