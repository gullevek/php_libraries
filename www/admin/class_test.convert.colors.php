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
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-convert-colors';
ob_end_flush();

use CoreLibs\Convert\Colors;
use CoreLibs\Convert\Color\Color;
use CoreLibs\Convert\Color\Coordinates;
use CoreLibs\Debug\Support as DgS;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$color_class = 'CoreLibs\Convert\Colors';

/**
 * print out a color block with info
 *
 * @param  string $color
 * @param  string $text
 * @param  string $text_add
 * @return string
 */
function display(string $color, string $text, string $text_add): string
{
	$css = 'margin:5px;padding:50px;'
		. 'width:10%;'
		. 'text-align:center;'
		. 'color:white;text-shadow: 0 0 5px black;font-weight:bold;';
	$template = <<<HTML
	<div style="background-color:{COLOR};{CSS}">
		{TEXT}
	</div>
	HTML;
	return str_replace(
		["{COLOR}", "{TEXT}", "{CSS}"],
		[
			$color,
			$text . (!empty($text_add) ? '<br>' . $text_add : ''),
			$css
		],
		$template
	);
}

$PAGE_NAME = 'TEST CLASS: CONVERT COLORS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// out of bounds test

// define a list of from to color sets for conversion test

$hwb = Color::hsbToHwb(new Coordinates\HSB([
	160,
	0,
	50,
]));
print "HWB: " . DgS::printAr($hwb) . "<br>";
$hsb = Color::hwbToHsb($hwb);
print "HSB: " . DgS::printAr($hsb) . "<br>";

$oklch = Color::rgbToOkLch(Coordinates\RGB::create([
	250,
	0,
	0
]));
print "OkLch: " . DgS::printAr($oklch) . "<br>";
$rgb = Color::okLchToRgb($oklch);
print "OkLch -> RGB: " . DgS::printAr($rgb) . "<br>";

$oklab = Color::rgbToOkLab(Coordinates\RGB::create([
	250,
	0,
	0
]));
print "OkLab: " . DgS::printAr($oklab) . "<br>";
print display($oklab->toCssString(), $oklab->toCssString(), 'Oklab');
$rgb = Color::okLabToRgb($oklab);
print "OkLab -> RGB: " . DgS::printAr($rgb) . "<br>";
print display($rgb->toCssString(), $rgb->toCssString(), 'OkLab to RGB');

$rgb = Coordinates\RGB::create([250, 100, 10])->toLinear();
print "RGBlinear: " . DgS::printAr($rgb) . "<br>";
$rgb = Coordinates\RGB::create([0, 0, 0])->toLinear();
print "RGBlinear: " . DgS::printAr($rgb) . "<br>";

$cie_lab = Color::okLabToLab($oklab);
print "CieLab: " . DgS::printAr($cie_lab) . "<br>";
print display($cie_lab->toCssString(), $cie_lab->toCssString(), 'OkLab to Cie Lab');

$rgb = Coordinates\RGB::create([0, 0, 60]);
$hsb = Color::rgbToHsb($rgb);
$rgb_b = Color::hsbToRgb($hsb);
print "RGB: " . DgS::printAr($rgb) . "<br>";
print "RGB->HSB: " . DgS::printAr($hsb) . "<br>";
print "HSB->RGB: " . DgS::printAr($rgb_b) . "<br>";

$hsl = Coordinates\HSL::create([0, 20, 0]);
$hsb = Coordinates\HSB::create([0, 20, 0]);
$hsl_from_hsb = Color::hsbToHsl($hsb);
print "HSL from HSB: " . DgS::printAr($hsl_from_hsb) . "<br>";

print "<hr>";

// A(out of bounds)
try {
	print "C::S/COLOR invalid rgb->hex (gray 125): -1, -1, -1: "
		. (new Coordinates\RGB([-1, -1, -1]))->returnAsHex() . "<br>";
} catch (\LengthException $e) {
	print "*Exception: " . $e->getMessage() . "<br><pre>" . print_r($e, true) . "</pre><br>";
}
print "<hr>";
print "<h2>LEGACY</h2>";
// B(valid)
$rgb = [50, 20, 30];
$hex = '#0a141e';
$hsb = [210, 67, 12];
$hsb_f = [210.5, 67.5, 12.5];
$hsb = [210, 50, 7.8];
print "S::COLOR rgb->hex: $rgb[0], $rgb[1], $rgb[2]: " . Colors::rgb2hex($rgb[0], $rgb[1], $rgb[2]) . "<br>";
print "S::COLOR hex->rgb: $hex: " . DgS::printAr(SetVarType::setArray(
	Colors::hex2rgb($hex)
)) . "<br>";
print "C::S/COLOR rgb->hex: $hex: " . DgS::printAr(SetVarType::setArray(
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
print "S::COLOR hsl->rgb: $hsb[0], $hsb[1], $hsb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsl2rgb($hsb[0], $hsb[1], $hsb[2])
	)) . "<br>";

$hsb = [0, 0, 5];
print "S::COLOR hsb->rgb: $hsb[0], $hsb[1], $hsb[2]: "
	. DgS::printAr(SetVarType::setArray(
		Colors::hsb2rgb($hsb[0], $hsb[1], $hsb[2])
	)) . "<br>";

print "<hr>";

// Random text
$h = rand(0, 359);
$s = rand(15, 70);
$b = 100;
$l = 50;
print "RANDOM IN: H: " . $h . ", S: " . $s . ", B/L: " . $b . "/" . $l . "<br>";
print "RANDOM hsb->rgb: <pre>"
	. DgS::printAr(SetVarType::setArray(Color::hsbToRgb(new Coordinates\HSB([$h, $s, $b])))) . "</pre><br>";
print "RANDOM hsl->rgb: <pre>"
	. DgS::printAr(SetVarType::setArray(Color::hslToRgb(new Coordinates\HSL([$h, $s, $l])))) . "</pre><br>";

print "<hr>";

$rgb = [0, 0, 0];
print "rgb 0,0,0: " . Dgs::printAr($rgb) . " => "
	. Dgs::printAr(Color::rgbToHsb(new Coordinates\RGB([$rgb[0], $rgb[1], $rgb[2]]))) . "<br>";

print "<hr>";

print "</body></html>";

// __END__
