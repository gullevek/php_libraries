<?php

namespace FileUpload;

use FileUpload\Core;

// TODO: find all usages from qqFileUploader and name to Qq
class qqFileUploader // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
{
	/** @var array<mixed> */
	private $allowedExtensions = [];
	/** @var int */
	private $sizeLimit = 10485760;
	/** @var null|Core\qqUploadedFileXhr|Core\qqUploadedFileForm */
	private $file;
	/** @var string */
	public $uploadFileName;
	/** @var string */
	public $uploadFileExt;

	/**
	 * Undocumented function
	 *
	 * @param array<string> $allowedExtensions
	 * @param integer $sizeLimit
	 */
	public function __construct(array $allowedExtensions = [], int $sizeLimit = 10485760)
	{
		$allowedExtensions = array_map('strtolower', $allowedExtensions);

		$this->allowedExtensions = $allowedExtensions;
		$this->sizeLimit = $sizeLimit;

		$this->checkServerSettings();

		if (isset($_GET['qqfile'])) {
			$this->file = new Core\qqUploadedFileXhr();
		} elseif (isset($_FILES['qqfile'])) {
			$this->file = new Core\qqUploadedFileForm();
		} else {
			$this->file = null;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	private function checkServerSettings(): void
	{
		$postSize = $this->toBytes(ini_get('post_max_size') ?: '');
		$uploadSize = $this->toBytes(ini_get('upload_max_filesize') ?: '');

		if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
			$size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
			die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $str
	 * @return integer
	 */
	private function toBytes(string $str): int
	{
		$val = (int)trim($str);
		$last = strtolower($str[strlen($str) - 1]);
		switch ($last) {
			case 'g':
				$val *= 1024;
				// no break
			case 'm':
				$val *= 1024;
				// no break
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	/**
	 * Undocumented function
	 *
	 * @param  string  $uploadDirectory
	 * @param  boolean $replaceOldFile
	 * @return array<string,string|bool> Returns ['success'=>true] or
	 *                                   ['error'=>'error message']
	 */
	public function handleUpload(string $uploadDirectory, bool $replaceOldFile = false): array
	{
		if (!is_writable($uploadDirectory)) {
			return ['error' => "Server error. Upload directory isn't writable."];
		}

		if (!is_object($this->file)) {
			return ['error' => 'No files were uploaded.'];
		}

		$size = 0;
		$size = $this->file->getSize();

		if ($size == 0) {
			return ['error' => 'File is empty'];
		}

		if ($size > $this->sizeLimit) {
			return ['error' => 'File is too large'];
		}

		$pathinfo = pathinfo($this->file->getName());
		$filename = $pathinfo['filename'];
		//$filename = md5(uniqid());
		$ext = $pathinfo['extension'] ?? '';

		if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
			$these = implode(', ', $this->allowedExtensions);
			return ['error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
		}

		if (!$replaceOldFile) {
			/// don't overwrite previous files that were uploaded
			while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
				$filename .= rand(10, 99);
			}
		}

		$this->uploadFileName = $uploadDirectory . $filename . '.' . $ext;
		$this->uploadFileExt = $ext;

		if ($this->file->save($uploadDirectory . $filename . '.' . $ext)) {
			return ['success' => true];
		} else {
			return [
				'error' => 'Could not save uploaded file.' .
				'The upload was cancelled, or server error encountered'
			];
		}
	}
}

// __END__
