<?php

/*
 * valid checks for css/html based colors
 * # hex
 * # hex + alpha
 * rgb
 * rgba
 * hsl
 * hsla
 */

declare(strict_types=1);

namespace CoreLibs\Check;

class Colors
{
	/** @var int 1 for HEX rgb */
	public const HEX_RGB = 1;
	/** @var int 2 for HEX rgb with alpha */
	public const HEX_RGBA = 2;
	/** @var int 4 for rgb() */
	public const RGB = 4;
	/** @var int 8 for rgba() */
	public const RGBA = 8;
	/** @var int 16 for hsl() */
	public const HSL = 16;
	/** @var int 32 for hsla() */
	public const HSLA = 32;
	/** @var int 63 for all bits set (sum of above) */
	public const ALL = 63;

	/**
	 * check rgb/hsl content values in detail
	 * will abort and return false on first error found
	 *
	 * @param  string    $color    html/css tring to check
	 * @param  int|false $rgb_flag flag to check for rgb
	 * @param  int|false $hsl_flag flag to check for hsl type
	 * @return bool                True if no error, False if error
	 * @throws \UnexpectedValueException 1: cannot extract color from string
	 */
	private static function rgbHslContentCheck(
		string $color,
		int|false $rgb_flag,
		int|false $hsl_flag
	): bool {
		// extract string between () and split into elements
		preg_match("/\((.*)\)/", $color, $matches);
		if (
			!is_array($color_list = preg_split("/,\s*/", $matches[1] ?? ''))
		) {
			throw new \UnexpectedValueException("Could not extract color list from rgg/hsl", 1);
		}
		// based on rgb/hsl settings check that entries are valid
		// rgb: either 0-255 OR 0-100%
		// hsl: first: 0-360
		foreach ($color_list as $pos => $color_check) {
			if (empty($color_check)) {
				return false;
			}
			$percent_check = false;
			if (strrpos($color_check, '%', -1) !== false) {
				$percent_check = true;
				$color_check = str_replace('%', '', $color_check);
			}
			// first three normal percent or valid number
			if ($rgb_flag !== false) {
				if ($percent_check === true) {
					// for ALL pos
					if ($color_check  < 0  || $color_check > 100) {
						return false;
					}
				} elseif (
					$pos < 3 &&
					($color_check < 0 || $color_check > 255)
				) {
						return false;
				} elseif (
					// RGBA set pos 3 if not percent
					$pos == 3 &&
					($color_check < 0 || $color_check > 1)
				) {
					return false;
				}
			} elseif ($hsl_flag !== false) {
				// pos 0: 0-360
				// pos 1,2: %
				// pos 3: % or 0-1 (float)
				if (
					$pos == 0 &&
					($color_check < 0 || $color_check > 360)
				) {
					return false;
				} elseif (
					// if pos 1/2 are not percent
					($pos == 1 || $pos == 2) &&
					($percent_check != true ||
					($color_check  < 0  || $color_check > 100))
				) {
					return false;
				} elseif (
					// 3 is either percent or 0~1
					$pos == 3 &&
					(
						($percent_check == false &&
						($color_check < 0 || $color_check > 1)) ||
						($percent_check === true  &&
						($color_check  < 0  || $color_check > 100))
					)
				) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * check if html/css color string is valid
	 *
	 * TODO: update check for correct validate values
	 * - space instead of ","
	 * - / opcatiy checks
	 * - loose numeric values
	 * - lab/lch,oklab/oklch validation too
	 *
	 * @param string $color A color string of any format
	 * @param int $flags    defaults to ALL, else use | to combined from
	 *                      HEX_RGB, HEX_RGBA, RGB, RGBA, HSL, HSLA
	 * @return bool         True if valid, False if not
	 * @throws \UnexpectedValueException 1: no valid flag set
	 * @throws \InvalidArgumentException 2: no regex block set
	 */
	public static function validateColor(string $color, int $flags = self::ALL): bool
	{
		// blocks for each check
		$regex_blocks = [];
		// set what to check
		if ($flags & self::HEX_RGB) {
			$regex_blocks[] = '#[\dA-Fa-f]{6}';
		}
		if ($flags & self::HEX_RGBA) {
			$regex_blocks[] = '#[\dA-Fa-f]{8}';
		}
		if ($flags & self::RGB) {
			$regex_blocks[] = 'rgb\(\d{1,3}%?,\s*\d{1,3}%?,\s*\d{1,3}%?\)';
		}
		if ($flags & self::RGBA) {
			$regex_blocks[] = 'rgba\(\d{1,3}%?,\s*\d{1,3}%?,\s*\d{1,3}%?(,\s*(0\.\d{1,2}|1(\.0)?|\d{1,3}%))?\)';
		}
		if ($flags & self::HSL) {
			$regex_blocks[] = 'hsl\(\d{1,3},\s*\d{1,3}(\.\d{1})?%,\s*\d{1,3}(\.\d{1})?%\)';
		}
		if ($flags & self::HSLA) {
			$regex_blocks[] = 'hsla\(\d{1,3},\s*\d{1,3}(\.\d{1})?%,\s*\d{1,3}'
				. '(\.\d{1})?%(,\s*(0\.\d{1,2}|1(\.0)?|\d{1,3}%))?\)';
		}
		// wrong flag set
		if ($flags > self::ALL) {
			throw new \UnexpectedValueException("Invalid flags parameter: $flags", 1);
		}
		if (!count($regex_blocks)) {
			throw new \InvalidArgumentException("No regex blocks set: $flags", 2);
		}

		// build regex
		$regex = '^('
			. join('|', $regex_blocks)
			// close regex
			. ')$';
		// print "C: $color, F: $flags, R: $regex\n";

		if (preg_match("/$regex/", $color)) {
			// if valid regex, we now need to check if the content is actually valid
			// only for rgb/hsl type
			/** @var int<0, max>|false */
			$rgb_flag = strpos($color, 'rgb');
			/** @var int<0, max>|false */
			$hsl_flag = strpos($color, 'hsl');
			// if both not match, return true
			if (
				$rgb_flag === false &&
				$hsl_flag === false
			) {
				return true;
			}
			// run detaul rgb/hsl content check
			return self::rgbHslContentCheck($color, $rgb_flag, $hsl_flag);
		} else {
			return false;
		}
	}
}

// __END__
