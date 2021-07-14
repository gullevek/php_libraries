<?php

/*
 * system related functions to get self name, host name, error strings
 */

declare(strict_types=1);

namespace CoreLibs\Get;

class System
{
		/**
	 * helper function for PHP file upload error messgaes to messge string
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
	 * @return array host name/port name
	 */
	public static function getHostName(): array
	{
		$port = '';
		if ($_SERVER['HTTP_HOST'] && preg_match("/:/", $_SERVER['HTTP_HOST'])) {
			list($host_name, $port) = explode(":", $_SERVER['HTTP_HOST']);
		} elseif ($_SERVER['HTTP_HOST']) {
			$host_name = $_SERVER['HTTP_HOST'];
		} else {
			$host_name = 'NA';
		}
		return [$host_name, ($port ? $port : 80)];
	}

	/**
	 * get the page name of the curronte page
	 * @param  int    $strip_ext 1: strip page file name extension
	 *                           0: keep filename as is
	 *                           2: keep filename as is, but add dirname too
	 * @return string            filename
	 */
	public static function getPageName(int $strip_ext = 0): string
	{
		// get the file info
		$page_temp = pathinfo($_SERVER['PHP_SELF']);
		if ($strip_ext == 1) {
			return $page_temp['filename'];
		} elseif ($strip_ext == 2) {
			return $_SERVER['PHP_SELF'];
		} else {
			return $page_temp['basename'];
		}
	}

	/**
	 * similar to getPageName, but it retuns the raw array
	 *
	 * @return array pathinfo array from PHP SELF
	 */
	public static function getPageNameArray(): array
	{
		return pathinfo($_SERVER['PHP_SELF']);
	}
}

// __END__
