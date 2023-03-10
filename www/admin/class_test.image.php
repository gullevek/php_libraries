<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
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
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);
$_image = new CoreLibs\Output\Image();
$image_class = 'CoreLibs\Output\Image';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: IMAGE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// thumb sizes
$thumb_width = 250;
$thumb_height = 300;
// class
$image = BASE . LAYOUT . CONTENT_PATH . IMAGES . 'no_picture_square.jpg';
// folders
$cache_folder = BASE . LAYOUT . CONTENT_PATH . CACHE . IMAGES;
$web_folder = LAYOUT . CACHE . IMAGES;
// rotate image first
$_image->correctImageOrientation($image);
// thumbnail tests
echo "<div>CLASS->CREATETHUMBNAILSIMPLE: "
	. basename($image) . ": WIDTH: $thumb_width<br><img src="
	. $_image->createThumbnailSimple($image, $thumb_width, 0, $cache_folder, $web_folder) . "></div>";
// static
$image = BASE . LAYOUT . CONTENT_PATH . IMAGES . 'no_picture.jpg';
// rotate image first
$image_class::correctImageOrientation($image);
// thumbnail tests
echo "<div>S::CREATETHUMBNAILSIMPLE: "
	. basename($image) . ": WIDTH: $thumb_width<br><img src="
	. $image_class::createThumbnailSimple($image, $thumb_width, 0, $cache_folder, $web_folder) . "></div>";

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
	list ($height, $width, $img_type) = \CoreLibs\Convert\SetVarType::setArray(getimagesize($image));
	echo "<div><b>IMAGE INFO</b>: " . $height . "x" . $width . ", TYPE: "
		. $log->prAr($img_type) . " [" . $finfo->file($image) . "]</div>";
	// rotate image first
	Image::correctImageOrientation($image);
	// thumbnail tests
	echo "<div>" . basename($image) . ": WIDTH: $thumb_width<br><img src="
		. Image::createThumbnailSimple($image, $thumb_width, 0, $cache_folder, $web_folder) . "></div>";
	echo "<div>" . basename($image) . ": HEIGHT: $thumb_height<br><img src="
		. Image::createThumbnailSimple($image, 0, $thumb_height, $cache_folder, $web_folder) . "></div>";
	echo "<div>" . basename($image) . ": WIDTH/HEIGHT: $thumb_width x $thumb_height<br><img src="
		. Image::createThumbnailSimple($image, $thumb_width, $thumb_height, $cache_folder, $web_folder) . "></div>";
	// test with dummy
	echo "<div>" . basename($image) . ": WIDTH/HEIGHT: $thumb_width x $thumb_height (+DUMMY)<br><img src="
		. Image::createThumbnailSimple(
			$image,
			$thumb_width,
			$thumb_height,
			$cache_folder,
			$web_folder,
			true,
			false
		) . "></div>";
	echo "<hr>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
