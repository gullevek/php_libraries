<?php

/*
 * Translates a mime id string into the actual application or file name
 * for example 'text/plain' will output 'Text file'
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class MimeAppName
{
	/** @var array<string,string> */
	private static array $mime_apps = [];

	/**
	 * constructor: init mime list
	 */
	public function __construct()
	{
		self::$mime_apps = [
			// zip
			'application/zip' => 'Zip File',
			// Powerpoint
			'application/vnd.ms-powerpoint' => 'Microsoft Powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'Microsoft Powerpoint',
			// PDF
			'pplication/pdf' => 'PDF',
			// JPEG
			'image/jpeg' => 'JPEG',
			// PNG
			'image/png' => 'PNG',
			// Indesign
			'application/x-indesign' => 'Adobe InDesign',
			// Photoshop
			'image/vnd.adobe.photoshop' => 'Adobe Photoshop',
			'application/photoshop' => 'Adobe Photoshop',
			// Illustrator
			'application/illustrator' => 'Adobe Illustrator',
			// Word
			'application/vnd.ms-word' => 'Microsoft Word',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Microsoft Word',
			// Excel
			'application/vnd.ms-excel' => 'Microsoft Excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Microsoft Excel',
			// plain text
			'text/plain' => 'Text file',
			// html
			'text/html' => 'HTML',
			// mp4 (max 45MB each)
			'video/mpeg' => 'MPEG Video'
		];
	}

	/**
	 * Sets or updates a mime type
	 *
	 * @param  string $mime MIME Name, no validiation
	 * @param  string $app  Applicaiton name
	 * @return void
	 */
	public static function mimeSetAppName(string $mime, string $app): void
	{
		// if empty, don't set
		if (empty($mime) || empty($app)) {
			return;
		}
		self::$mime_apps[$mime] = $app;
	}

	/**
	 * get the application name from mime type
	 * if not set returns "Other file"
	 *
	 * @param  string $mime MIME Name
	 * @return string       Application name matching
	 */
	public static function mimeGetAppName(string $mime): string
	{
		return self::$mime_apps[$mime] ?? 'Other file';
	}
}

// __END__
