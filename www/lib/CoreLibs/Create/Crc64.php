<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/10/15
 * DESCRIPTION:
 * AWS CRC64 Implementation
 * This can be replaced by:
 * https://github.com/awesomized/crc-fast-php-ext
 * This this needs: https://github.com/awesomized/crc-fast-rust
 * installed, and there is no easy binary package available.
 *
 * NOTE: for anything larger than 10MB this is absolutely NOT recommended
*/

declare(strict_types=1);

namespace CoreLibs\Create;

class Crc64
{
	/**
	 * Internal CRC64 calculation for a given data string.
	 *
	 * @param  string $data
	 * @param  int    $crc
	 * @return int
	 */
	private function crc64Calc(string $data, int $crc = 0): int
	{
		static $table = null;
		if ($table === null) {
			// reflected poly 0x9A6C9329AC4BC9B5 (built from halves, too big for a PHP int literal)
			$poly = (0x9A6C9329 << 32) | 0xAC4BC9B5;
			$table = [];
			for ($i = 0; $i < 256; $i++) {
				$c = $i;
				for ($k = 0; $k < 8; $k++) {
					// logical right shift: PHP >> is arithmetic, so mask off the sign bit
					$c = ($c & 1)
						? ((($c >> 1) & PHP_INT_MAX) ^ $poly)
						: (($c >> 1) & PHP_INT_MAX);
				}
				$table[$i] = $c;
			}
		}
		$crc = ~$crc; // xor 0xFFFFFFFFFFFFFFFF
		foreach (unpack('C*', $data) ?: [] as $b) {
			$crc = $table[($crc ^ $b) & 0xFF] ^ (($crc >> 8) & 0x00FFFFFFFFFFFFFF);
		}
		return ~$crc;
	}

	/**
	 * Returns int value of the CRC64 checksum for the given file.
	 *
	 * @param  string $path
	 * @param  int<1,16777216> $chunkSize
	 * @return int
	 */
	private function crc64CalcFile(string $path, int $chunkSize = 8 * 1024 * 1024): int
	{
		$fh = fopen($path, 'rb');
		if ($fh === false) {
			throw new \RuntimeException("Cannot open $path");
		}
		$crc = 0;
		try {
			while (!feof($fh)) {
				$chunk = fread($fh, $chunkSize);
				if ($chunk === false) {
					throw new \RuntimeException("Read error on $path");
				}
				$crc = $this->crc64Calc($chunk, $crc);
			}
		} finally {
			fclose($fh);
		}
		return $crc;
	}

	/**
	 * Returns the Base64-encoded CRC64 checksum for the given file.
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function crc64(string $path): string
	{
		return base64_encode(pack('J', (new self())->crc64CalcFile($path))); // J = uint64 big-endian
	}

	/**
	 * Returns the 16-character lowercase hexadecimal CRC64 checksum for the given file.
	 * Same as base64.b64decode(...).hex()
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function crc64hex(string $path): string
	{
		return sprintf('%016x', (new self())->crc64CalcFile($path));
	}
}

// __END__
