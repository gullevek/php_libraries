<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Output\Image
 * @coversDefaultClass \CoreLibs\Output\Image
 * @testdox \CoreLibs\Output\Image method tests
 */
final class CoreLibsOutputImageTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @covers ::createThumbnail
	 * @testdox createThumbnail checks
	 *
	 * @return void
	 */
	public function testCreateThumbnail(): void
	{
		// CONVERT does not exist
		$this->expectException(\RuntimeException::class);
		\CoreLibs\Output\Image::createThumbnail('do_not_exist.png', 200, 200);
		// set convert
		$paths = [
			'/bin',
			'/usr/bin',
			'/usr/local/bin',
		];
		// find convert
		foreach ($paths as $path) {
			if (
				file_exists($path . DIRECTORY_SEPARATOR . 'convert') &&
				is_file($path . DIRECTORY_SEPARATOR . 'convert')
			) {
				// image magick convert location
				define('CONVERT', $path . DIRECTORY_SEPARATOR . 'convert');
				break;
			}
		}
		unset($paths);
		// cannot set dummy file
		$this->expectException(\Exception::class);
		\CoreLibs\Output\Image::createThumbnail('do_not_exist.png', 200, 200);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::createThumbnailSimple
	 * @testdox createThumbnailSimple checks
	 *
	 * @return void
	 */
	public function testCreateThumbnailSimple(): void
	{
		// file does not exist
		$this->expectException(\UnexpectedValueException::class);
		\CoreLibs\Output\Image::createThumbnailSimple(
			'do_not_exist.png',
			200,
			200,
			cache_folder: '/tmp/',
			web_folder: '/tmp/'
		);
		// cache folder is not dir
		$this->expectException(\UnexpectedValueException::class);
		\CoreLibs\Output\Image::createThumbnailSimple(
			'do_not_exist.png',
			200,
			200,
			cache_folder: '/foo/bar/',
			web_folder: '/tmp/'
		);
		// target cache folder is not writeable

		// RuntimeException: imagecreatetruecolor failed
		// RuntimeException: imagecolorallocatealpha failed
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::correctImageOrientation
	 * @testdox correctImageOrientation checks
	 *
	 * @return void
	 */
	public function testCorrectImageOrientation(): void
	{
		// test file does not exist
		$this->expectException(\UnexpectedValueException::class);
		\CoreLibs\Output\Image::correctImageOrientation('do_not_exist.png');
		// test folder not writeable
		// test exit_read_data not present (how)?
		// test image rotate
	}
}

// __END__
