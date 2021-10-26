<?php

namespace FileUpload\Core;

interface qqUploadedFile // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
{
	/**
	 * Save the file to the specified path
	 *
	 * @param string $path
	 * @return boolean TRUE on success
	 */
	public function save(string $path): bool;

	/**
	 * get qqfile name from _GET array
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Get file size from _SERVERa array, throws an error if not possible
	 *
	 * @return int
	 *
	 * @throws \Exception
	 */
	public function getSize(): int;
}

// __END__
