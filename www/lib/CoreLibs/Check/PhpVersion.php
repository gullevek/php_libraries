<?php declare(strict_types=1);

namespace CoreLibs\Check;

class PhpVersion
{
	/**
	 * checks if running PHP version matches given PHP version (min or max)
	 * @param  string $min_version minimum version as string (x, x.y, x.y.x)
	 * @param  string $max_version optional maximum version as string (x, x.y, x.y.x)
	 * @return bool                true if ok, false if not matching version
	 */
	public static function checkPHPVersion(string $min_version, string $max_version = ''): bool
	{
		// exit with false if the min/max strings are wrong
		if (!preg_match("/^\d{1}(\.\d{1})?(\.\d{1,2})?$/", $min_version)) {
			return false;
		}
		// max is only chcked if it is set
		if ($max_version && !preg_match("/^\d{1}(\.\d{1})?(\.\d{1,2})?$/", $max_version)) {
			return false;
		}
		// split up the version strings to calc the compare number
		$version = explode('.', $min_version);
		$min_version = (int)$version[0] * 10000 + (int)($version[1] ?? 0) * 100 + (int)($version[2] ?? 0);
		if ($max_version) {
			$version = explode('.', $max_version);
			$max_version = (int)$version[0] * 10000 + (int)($version[1] ?? 0) * 100 + (int)($version[2] ?? 0);
			// drop out if min is bigger max, equal size is okay, that would be only THIS
			if ($min_version > $max_version) {
				return false;
			}
		}
		// set the php version id
		if (!defined('PHP_VERSION_ID')) {
			$version = explode('.', phpversion());
			// creates something like 50107
			define('PHP_VERSION_ID', (int)$version[0] * 10000 + (int)$version[1] * 100 + (int)$version[2]);
		}
		// check if matching for version
		if ($min_version && !$max_version) {
			if (PHP_VERSION_ID >= $min_version) {
				return true;
			}
		} elseif ($min_version && $max_version) {
			if (PHP_VERSION_ID >= $min_version && PHP_VERSION_ID <= $max_version) {
				return true;
			}
		}
		// if no previous return, fail
		return false;
	}
}

// __END__
