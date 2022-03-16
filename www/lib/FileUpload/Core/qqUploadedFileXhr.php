<?php

namespace FileUpload\Core;

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr implements qqUploadedFile // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
{
	/**
	 * Save the file to the specified path
	 *
	 * @param string $path
	 * @return boolean TRUE on success
	 */
	public function save(string $path): bool
	{
		$input = fopen("php://input", "r");
		$temp = tmpfile();
		// abort if not resources
		if (!is_resource($input) || !is_resource($temp)) {
			return false;
		}
		$realSize = stream_copy_to_stream($input, $temp);
		fclose($input);

		if ($realSize != $this->getSize()) {
			return false;
		}

		$target = fopen($path, "w");
		if (!is_resource($target)) {
			return false;
		}
		fseek($temp, 0, SEEK_SET);
		stream_copy_to_stream($temp, $target);
		fclose($target);

		return true;
	}

	/**
	 * get qqfile name from _GET array
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $_GET['qqfile'] ?? '';
	}

	/**
	 * Get file size from _SERVERa array, throws an error if not possible
	 *
	 * @return int
	 *
	 * @throws \Exception
	 */
	public function getSize(): int
	{
		if (isset($_SERVER['CONTENT_LENGTH'])) {
			return (int)$_SERVER['CONTENT_LENGTH'];
		} else {
			throw new \Exception('Getting content length is not supported.');
		}
	}
}

// __END__
