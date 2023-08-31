<?php

/*
 * image thumbnail, rotate, etc
 */

declare(strict_types=1);

namespace CoreLibs\Output;

use Exception;

class Image
{
	/**
	 * converts picture to a thumbnail with max x and max y size
	 * TOOD: needs mandatory options for ImageMagic convert, paths, etc folders
	 *
	 * @param  string      $pic          source image file with or without path
	 * @param  int         $size_x       maximum size width
	 * @param  int         $size_y       maximum size height
	 * @param  string      $dummy        empty, or file_type to show an icon
	 *                                   instead of nothing if file is not found
	 * @param  string      $path         if source start is not ROOT path,
	 *                                   if empty ROOT is choosen
	 * @param  string      $cache_source cache path, if not given TMP is used
	 * @param  bool        $clear_cache  if set to true, will create thumb all the tame
	 * @return string                    thumbnail name
	 * @throws \RuntimeException no ImageMagick convert command found
	 */
	public static function createThumbnail(
		string $pic,
		int $size_x,
		int $size_y,
		string $dummy = '',
		string $path = '',
		string $cache_source = '',
		bool $clear_cache = false
	): string {
		// get image type flags
		$image_types = [
			0 => 'UNKOWN-IMAGE',
			1 => 'gif',
			2 => 'jpg',
			3 => 'png'
		];
		$return_data = '';
		$CONVERT = '';
		// if CONVERT is not defined, abort
		/** @phan-suppress-next-line PhanUndeclaredConstant */
		if (defined('CONVERT') && is_executable(CONVERT)) {
			/** @phan-suppress-next-line PhanUndeclaredConstant */
			$CONVERT = CONVERT;
		} else {
			throw new \RuntimeException('CONVERT set binary is not executable or CONVERT is not defined');
		}
		if (!empty($cache_source)) {
			$tmp_src = $cache_source;
		} else {
			$tmp_src = BASE . TMP;
		}
		// check if pic has a path, and override next sets
		if (strstr($pic, '/') === false) {
			if (empty($path)) {
				$path = BASE;
			}
			$filename = $path . MEDIA . PICTURES . $pic;
		} else {
			$filename = $pic;
			// and get the last part for pic (the filename)
			$tmp = explode('/', $pic);
			$pic = $tmp[(count($tmp) - 1)];
		}
		// does this picture exist and is it a picture
		if (!file_exists($filename) || !is_file($filename)) {
			if (!empty($dummy) && strstr($dummy, '/') === false) {
				// check if we have the "dummy" image flag set
				$filename = PICTURES . ICONS . strtoupper($dummy) . ".png";
				/** @phpstan-ignore-next-line */
				if (!empty($dummy) && file_exists($filename) && is_file($filename)) {
					$return_data = $filename;
				} else {
					throw new \Exception('Could not set dummy return file: ' . $dummy . ' in ' . $filename);
				}
			} else {
				$return_data = $dummy;
			}
			return $return_data;
		}
		// resize image
		[$width, $height, $type] = getimagesize($filename) ?: [0, 0, 0];
		$convert_prefix = '';
		$create_file = false;
		$delete_filename = '';
		// check if we can skip the PDF creation: if we have size, if do not have type, we assume type png
		if (!$type) {
			$check_thumb = $tmp_src . 'thumb_' . $pic . '_' . $size_x . 'x' . $size_y . '.' . $image_types[3];
			if (!is_file($check_thumb)) {
				$create_file = true;
			} else {
				$type = 3;
			}
		}
		// if type is not in the list, but returns as PDF, we need to convert to JPEG before
		if (!$type)	{
			$output = [];
			$return = null;
			// is this a PDF, if no, return from here with nothing
			$convert_prefix = 'png:';
			# TEMP convert to PNG, we then override the file name
			$convert_string = $CONVERT . ' ' . $filename . ' ' . $convert_prefix . $filename . '_TEMP';
			$status = exec($convert_string, $output, $return);
			$filename .= '_TEMP';
			// for delete, in case we need to glob
			$delete_filename = $filename;
			// find file, if we can't find base name, use -0 as the first one (ignore other pages in multiple ones)
			if (!is_file($filename)) {
				$filename .= '-0';
			}
			[$width, $height, $type] = getimagesize($filename) ?: [0, 0, 0];
		}
		// if no size given, set size to original
		if (!$size_x || $size_x < 1) {
			$size_x = $width;
		}
		if (!$size_y || $size_y < 1) {
			$size_y = $height;
		}
		$thumb = 'thumb_' . $pic . '_' . $size_x . 'x' . $size_y . '.' . $image_types[$type];
		$thumbnail = $tmp_src . $thumb;
		// check if we already have this picture converted
		if (!is_file($thumbnail) || $clear_cache == true) {
			// convert the picture
			if ($width > $size_x) {
				$convert_string = $CONVERT . ' -geometry ' . $size_x . 'x ' . $filename . ' ' . $thumbnail;
				$status = exec($convert_string, $output, $return);
				// get the size of the converted data, if converted
				if (is_file($thumbnail)) {
					[$width, $height, $type] = getimagesize($thumbnail) ?: [0, 0, 0];
				}
			}
			if ($height > $size_y) {
				$convert_string = $CONVERT . ' -geometry x' . $size_y . ' ' . $filename . ' ' . $thumbnail;
				$status = exec($convert_string, $output, $return);
			}
		}
		if (!is_file($thumbnail)) {
			copy($filename, $thumbnail);
		}
		$return_data = $thumb;
		// if we have a delete filename, delete here with glob
		if ($delete_filename) {
			array_map('unlink', glob($delete_filename . '*') ?: []);
		}
		return $return_data;
	}

	/**
	 * simple thumbnail creation for jpeg, png only
	 * TODO: add other types like gif, etc
	 * - bails with false on failed create
	 * - if either size_x or size_y are empty (0)
	 *   the resize is to max of one size
	 *   if both are set, those are the max sizes (aspect ration is always ekpt)
	 * - if path is not given will cache folder for current path set
	 *
	 * @param  string      $filename     source file name with full path
	 * @param  int         $thumb_width  thumbnail width
	 * @param  int         $thumb_height thumbnail height
	 * @param  string|null $cache_folder path for thumbnail cache
	 * @param  string|null $web_folder   frontend path for output
	 * @param  bool        $create_dummy if we encounter an invalid file
	 *                                   create a dummy image file and return it
	 * @param  bool        $use_cache    default to true, set to false to skip
	 *                                   creating new image if exists
	 * @param  bool        $high_quality default to true, uses sample version,
	 *                                   set to false to not use (default true)
	 *                                   to use quick but less nice version
	 * @param  int         $jpeg_quality default 80, set image quality for jpeg only
	 * @return string                    thumbnail with path
	 * @throws \UnexpectedValueException input values for filename or cache_folder are wrong
	 * @throws \RuntimeException         convert (gd) failed
	 */
	public static function createThumbnailSimple(
		string $filename,
		int $thumb_width = 0,
		int $thumb_height = 0,
		?string $cache_folder = null, // will be not null in future
		?string $web_folder = null,
		bool $create_dummy = true,
		bool $use_cache = true,
		bool $high_quality = true,
		int $jpeg_quality = 80
	): string {
		$thumbnail = false;
		$exception_message = 'Could not create thumbnail';
		// $this->debug('IMAGE PREPARE', "FILE: $filename (exists "
		//	.(string)file_exists($filename)."), WIDTH: $thumb_width, HEIGHT: $thumb_height");
		if (
			$cache_folder === null ||
			$web_folder === null
		) {
			/** @deprecated Do use cache folder and web folder parameters */
			trigger_error(
				'params $cache_folder and $web_folder must be set. Setting via constants is deprecated',
				E_USER_DEPRECATED
			);
			// NOTE: we need to depracte this
			$cache_folder = BASE . LAYOUT . CONTENT_PATH . CACHE . IMAGES;
			$web_folder = LAYOUT . CACHE . IMAGES;
			if (!is_dir($cache_folder)) {
				if (false === mkdir($cache_folder)) {
					$cache_folder = BASE . LAYOUT . CONTENT_PATH . CACHE;
					$web_folder = LAYOUT . CACHE;
				}
			}
		}
		// check that input image exists and is either jpeg or png
		// also fail if the basic CACHE folder does not exist at all
		if (!file_exists($filename)) {
			// return $thumbnail;
			throw new \UnexpectedValueException('Missing image file: ' . $filename);
		}
		if (!is_dir($cache_folder)) {
			// return $thumbnail;
			throw new \UnexpectedValueException('Cache folder is not a directory: ' . $cache_folder);
		}
		if (!is_writable($cache_folder)) {
			// return $thumbnail;
			throw new \UnexpectedValueException('Cache folder is not writeable: ' . $cache_folder);
		}
		// $this->debug('IMAGE PREPARE', "FILENAME OK, THUMB WIDTH/HEIGHT OK");
		[$inc_width, $inc_height, $img_type] = getimagesize($filename) ?: [0, 0, null];
		$thumbnail_write_path = null;
		$thumbnail_web_path = null;
		// path set first
		if (
			$img_type == IMAGETYPE_JPEG ||
			$img_type == IMAGETYPE_PNG ||
			$create_dummy === true
		) {
			// $this->debug('IMAGE PREPARE', "IMAGE TYPE OK: ".$inc_width.'x'.$inc_height);
			// set thumbnail paths
			$thumbnail_write_path = $cache_folder;
			$thumbnail_web_path = $web_folder;
		}
		// do resize or fall back on dummy run
		if (
			$img_type == IMAGETYPE_JPEG ||
			$img_type == IMAGETYPE_PNG
		) {
			// if missing width or height in thumb, use the set one
			if ($thumb_width == 0) {
				$thumb_width = $inc_width;
			}
			if ($thumb_height == 0) {
				$thumb_height = $inc_height;
			}
			// check resize parameters
			if ($inc_width > $thumb_width || $inc_height > $thumb_height) {
				$thumb_width_r = 0;
				$thumb_height_r = 0;
				// we need to keep the aspect ration on longest side
				if (
					($inc_height > $inc_width &&
					// and the height is bigger than thumb set
						$inc_height > $thumb_height) ||
					// or the height is smaller or equal width
					// but the width for the thumb is equal to the image height
					($inc_height <= $inc_width &&
						$inc_width == $thumb_width
					)
				) {
					// $this->debug('IMAGE PREPARE', 'HEIGHT > WIDTH');
					$ratio = $inc_height / $thumb_height;
					$thumb_width_r = (int)ceil($inc_width / $ratio);
					$thumb_height_r = $thumb_height;
				} else {
					// $this->debug('IMAGE PREPARE', 'WIDTH > HEIGHT');
					$ratio = $inc_width / $thumb_width;
					$thumb_width_r = $thumb_width;
					$thumb_height_r = (int)ceil($inc_height / $ratio);
				}
				// $this->debug('IMAGE PREPARE', "Ratio: $ratio, Target size $thumb_width_r x $thumb_height_r");
				// set output thumbnail name
				$thumbnail = 'thumb-' . pathinfo($filename)['filename'] . '-'
					. $thumb_width_r . 'x' . $thumb_height_r;
				if (
					$use_cache === false ||
					!file_exists($thumbnail_write_path . $thumbnail)
				) {
					// image, copy source image, offset in image, source x/y, new size, source image size
					$thumb = imagecreatetruecolor($thumb_width_r, $thumb_height_r);
					if ($thumb === false) {
						throw new \RuntimeException(
							'imagecreatetruecolor failed: ' . $thumbnail . ', ' . $filename,
							1
						);
					}
					if ($img_type == IMAGETYPE_PNG) {
						$imagecolorallocatealpha = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
						if ($imagecolorallocatealpha === false) {
							throw new \RuntimeException(
								'imagecolorallocatealpha failed: ' . $thumbnail . ', ' . $filename,
								2
							);
						}
						// preservere transaprency
						imagecolortransparent(
							$thumb,
							$imagecolorallocatealpha
						);
						imagealphablending($thumb, false);
						imagesavealpha($thumb, true);
					}
					$source = null;
					switch ($img_type) {
						case IMAGETYPE_JPEG:
							$source = imagecreatefromjpeg($filename);
							break;
						case IMAGETYPE_PNG:
							$source = imagecreatefrompng($filename);
							break;
					}
					// check that we have a source image resource
					if ($source !== null && $source !== false) {
						// resize no shift
						if ($high_quality === true) {
							imagecopyresized(
								$thumb,
								$source,
								0,
								0,
								0,
								0,
								$thumb_width_r,
								$thumb_height_r,
								$inc_width,
								$inc_height
							);
						} else {
							imagecopyresampled(
								$thumb,
								$source,
								0,
								0,
								0,
								0,
								$thumb_width_r,
								$thumb_height_r,
								$inc_width,
								$inc_height
							);
						}
						// write file
						switch ($img_type) {
							case IMAGETYPE_JPEG:
								imagejpeg($thumb, $thumbnail_write_path . $thumbnail, $jpeg_quality);
								break;
							case IMAGETYPE_PNG:
								imagepng($thumb, $thumbnail_write_path . $thumbnail);
								break;
						}
						// free up resources (in case we are called in a loop)
						imagedestroy($source);
						imagedestroy($thumb);
					} else {
						throw new \RuntimeException(
							'Invalid source image file. Only JPEG/PNG are allowed: ' . $filename,
							3
						);
					}
				}
			} else {
				// we just copy over the image as is, we never upscale
				$thumbnail = 'thumb-' . pathinfo($filename)['filename'] . '-' . $inc_width . 'x' . $inc_height;
				if (
					$use_cache === false ||
					!file_exists($thumbnail_write_path . $thumbnail)
				) {
					copy($filename, $thumbnail_write_path . $thumbnail);
				}
			}
			// add output path
			if ($thumbnail !== false) {
				$thumbnail = $thumbnail_web_path . $thumbnail;
			}
		} elseif ($create_dummy === true) {
			// create dummy image in the thumbnail size
			// if one side is missing, use the other side to create a square
			if (!$thumb_width) {
				$thumb_width = $thumb_height;
			}
			if (!$thumb_height) {
				$thumb_height = $thumb_width;
			}
			// do we have an image already?
			$thumbnail = 'thumb-' . pathinfo($filename)['filename'] . '-' . $thumb_width . 'x' . $thumb_height;
			if (
				$use_cache === false ||
				!file_exists($thumbnail_write_path . $thumbnail)
			) {
				// if both are unset, set to 250
				if ($thumb_height == 0) {
					$thumb_height = 250;
				}
				if ($thumb_width == 0) {
					$thumb_width = 250;
				}
				$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
				if ($thumb === false) {
					throw new \RuntimeException(
						'imagecreatetruecolor dummy failed: ' . $thumbnail . ', ' . $filename,
						3
					);
				}
				// add outside border px = 5% (rounded up)
				// eg 50px -> 2.5px
				$gray = imagecolorallocate($thumb, 200, 200, 200);
				$white = imagecolorallocate($thumb, 255, 255, 255);
				if ($gray === false || $white === false) {
					throw new \RuntimeException(
						'imagecolorallocate/imagecolorallocate dummy failed: ' . $thumbnail . ', ' . $filename,
						2
					);
				}
				// fill gray background
				imagefill($thumb, 0, 0, $gray);
				// now create rectangle
				if (imagesx($thumb) < imagesy($thumb)) {
					$width = (int)round(imagesx($thumb) / 100 * 5);
				} else {
					$width = (int)round(imagesy($thumb) / 100 * 5);
				}
				imagefilledrectangle(
					$thumb,
					0 + $width,
					0 + $width,
					imagesx($thumb) - $width,
					imagesy($thumb) - $width,
					$white
				);
				// add "No valid images source"
				// OR add circle
				// * find center
				// * width/height is 75% of size - border
				// smaller size is taken
				$base_width = imagesx($thumb) > imagesy($thumb) ? imagesy($thumb) : imagesx($thumb);
				// get 75% width
				$cross_width = (int)round((($base_width - ($width * 2)) / 100 * 75) / 2);
				$center_x = (int)round(imagesx($thumb) / 2);
				$center_y = (int)round(imagesy($thumb) / 2);
				imagefilledellipse($thumb, $center_x, $center_y, $cross_width, $cross_width, $gray);
				// find top left and bottom left for first line
				imagepng($thumb, $thumbnail_write_path . $thumbnail);
			}
			// add web path
			$thumbnail = $thumbnail_web_path . $thumbnail;
		}
		// if still false -> throw exception
		if ($thumbnail === false) {
			throw new \RuntimeException($exception_message);
		}
		// else return the thumbnail name + output path web
		return $thumbnail;
	}

	/**
	 * reads the rotation info of an file and rotates it to be correctly upright
	 * this is done because not all software honers the exit Orientation flag
	 * only works with jpg or png
	 *
	 * @param  string $filename path + filename to rotate. This file must be writeable
	 * @return void
	 */
	public static function correctImageOrientation(string $filename): void
	{
		// function exists & file is writeable, else do nothing
		if (!function_exists('exif_read_data') || !is_writeable($filename)) {
			return;
		}
		[$inc_width, $inc_height, $img_type] = getimagesize($filename) ?: [0, 0, null];
		// add @ to avoid "file not supported error"
		$exif = @exif_read_data($filename);
		$orientation = null;
		$img = null;
		if ($exif && isset($exif['Orientation'])) {
			$orientation = $exif['Orientation'];
		}
		 // only if we need to rotate, if 1 it is already upright
		if ($orientation === null || $orientation == 1) {
			return;
		}
		switch ($img_type) {
			case IMAGETYPE_JPEG:
				$img = imagecreatefromjpeg($filename);
				break;
			case IMAGETYPE_PNG:
				$img = imagecreatefrompng($filename);
				break;
		}
		// no image loaded (wrong type)
		if ($img === null || $img === false) {
			return;
		}
		$deg = 0;
		// 1 top, 6: left, 8: right, 3: bottom
		switch ($orientation) {
			case 3:
				$deg = 180;
				break;
			case 6:
				$deg = -90;
				break;
			case 8:
				$deg = 90;
				break;
		}
		// rotate if needed
		if ($deg) {
			$img = imagerotate($img, $deg, 0);
		}
		// rotate failed
		if ($img === false) {
			return;
		}
		// then rewrite the rotated image back to the disk as $filename
		switch ($img_type) {
			case IMAGETYPE_JPEG:
				imagejpeg($img, $filename);
				break;
			case IMAGETYPE_PNG:
				imagepng($img, $filename);
				break;
		}
		// clean up image if we have an image
		imagedestroy($img);
	}
}

// __END__
