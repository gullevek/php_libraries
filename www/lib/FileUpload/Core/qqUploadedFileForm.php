<?php

namespace FileUpload\Core;

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm implements qqUploadedFile // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
{
	/**
	 * Save the file to the specified path
	 *
	 * @param string $path
	 * @return boolean TRUE on success
	 */
	public function save(string $path): bool
	{
		if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
			return false;
		}
		return true;
	}

	/**
	 * get qqfile name from _FILES array
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return (string)$_FILES['qqfile']['name'];
	}

	/**
	 * get files size from _FILES array
	 *
	 * @return int
	 */
	public function getSize(): int
	{
		return (int)$_FILES['qqfile']['size'];
	}
}

// __END__
