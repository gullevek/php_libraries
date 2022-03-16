<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

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
$LOG_FILE_ID = 'classTest-image';
ob_end_flush();

use CoreLibs\Output\Image;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$_image = new CoreLibs\Output\Image();
$image_class = 'CoreLibs\Output\Image';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: IMAGE</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// thumb sizes
$thumb_width = 250;
$thumb_height = 300;
// class
$image = BASE . LAYOUT . CONTENT_PATH . IMAGES . 'no_picture_square.jpg';
// rotate image first
$_image->correctImageOrientation($image);
// thumbnail tests
echo "<div>CLASS->CREATETHUMBNAILSIMPLE: "
	. basename($image) . ": WIDTH: $thumb_width<br><img src="
	. $_image->createThumbnailSimple($image, $thumb_width) . "></div>";
// static
$image = BASE . LAYOUT . CONTENT_PATH . IMAGES . 'no_picture.jpg';
// rotate image first
$image_class::correctImageOrientation($image);
// thumbnail tests
echo "<div>S::CREATETHUMBNAILSIMPLE: "
	. basename($image) . ": WIDTH: $thumb_width<br><img src="
	. $image_class::createThumbnailSimple($image, $thumb_width) . "></div>";

echo "U-STATIC VARIOUS:<br>";
// image thumbnail
$images = array(
	// height bigger
	// 'no_picture.jpg',
	// 'no_picture.png',
	// width bigger
	// 'no_picture_width_bigger.jpg',
	// 'no_picture_width_bigger.png',
	// square
	// 'no_picture_square.jpg',
	// 'no_picture_square.png',
	// other sample images
	// '5c501af48da6c.jpg',
	// Apple HEIC files
	// 'img_2145.heic',
	// Photoshop
	'photoshop_test.psd',
);
// return mime type ala mimetype
$finfo = new finfo(FILEINFO_MIME_TYPE);
foreach ($images as $image) {
	$image = BASE . LAYOUT . CONTENT_PATH . IMAGES . $image;
	list ($height, $width, $img_type) = getimagesize($image);
	echo "<div><b>IMAGE INFO</b>: " . $height . "x" . $width . ", TYPE: "
		. $img_type . " [" . $finfo->file($image) . "]</div>";
	// rotate image first
	Image::correctImageOrientation($image);
	// thumbnail tests
	echo "<div>" . basename($image) . ": WIDTH: $thumb_width<br><img src="
		. Image::createThumbnailSimple($image, $thumb_width) . "></div>";
	echo "<div>" . basename($image) . ": HEIGHT: $thumb_height<br><img src="
		. Image::createThumbnailSimple($image, 0, $thumb_height) . "></div>";
	echo "<div>" . basename($image) . ": WIDTH/HEIGHT: $thumb_width x $thumb_height<br><img src="
		. Image::createThumbnailSimple($image, $thumb_width, $thumb_height) . "></div>";
	// test with dummy
	echo "<div>" . basename($image) . ": WIDTH/HEIGHT: $thumb_width x $thumb_height (+DUMMY)<br><img src="
		. Image::createThumbnailSimple($image, $thumb_width, $thumb_height, null, true, false) . "></div>";
	echo "<hr>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
