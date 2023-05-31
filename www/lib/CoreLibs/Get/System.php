<?php

/*
 * system related functions to get self name, host name, error strings
 */

declare(strict_types=1);

namespace CoreLibs\Get;

class System
{
	public const WITH_EXTENSION = 0;
	public const NO_EXTENSION = 1;
	public const FULL_PATH = 2;
	private const DEFAULT_PORT = '80';

	/**
	 * helper function for PHP file upload error messgaes to messge string
	 *
	 * @param  int    $error_code integer _FILE upload error code
	 * @return string                     message string, translated
	 */
	public static function fileUploadErrorMessage(int $error_code): string
	{
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;
			default:
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}

	/**
	 * get the host name without the port as given by the SELF var
	 * if no host name found will set to NOHOST:0
	 *
	 * @return array{string,int} host name/port number
	 */
	public static function getHostName(): array
	{
		$host = $_SERVER['HTTP_HOST'] ?? 'NOHOST:0';
		[$host_name, $port] = array_pad(explode(':', $host), 2, self::DEFAULT_PORT);
		return [$host_name, (int)$port];
	}

	/**
	 * get the page name of the curronte page
	 *
	 * @param  int    $strip_ext WITH_EXTENSION: keep filename as is (default)
	 *                           NO_EXTENSION: strip page file name extension
	 *                           FULL_PATH: keep filename as is, but add dirname too
	 * @return string            filename
	 */
	public static function getPageName(int $strip_ext = self::WITH_EXTENSION): string
	{
		// get the file info
		$page_temp = pathinfo($_SERVER['PHP_SELF']);
		if ($strip_ext == self::NO_EXTENSION) {
			// no extension
			return $page_temp['filename'];
		} elseif ($strip_ext == self::FULL_PATH) {
			// full path
			return $_SERVER['PHP_SELF'];
		} else {
			// with extension
			return $page_temp['basename'];
		}
	}

	/**
	 * similar to getPageName, but it retuns the raw array
	 *
	 * @return array<string> pathinfo array from PHP SELF
	 */
	public static function getPageNameArray(): array
	{
		return pathinfo($_SERVER['PHP_SELF']);
	}

	/**
	 * Check if the php sapi interface has cli inside
	 *
	 * @return bool True for CLI type PHP, else false
	 */
	public static function checkCLI(): bool
	{
		return substr(
			// if return is false, use empty string
			(($sapi_name = php_sapi_name()) === false ?
				'' :
				$sapi_name
			),
			0,
			3
		) === 'cli' ? true : false;
	}
}

// __END__
