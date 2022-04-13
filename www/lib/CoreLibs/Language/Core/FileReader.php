<?php

/*
	Copyright (c) 2003, 2005, 2006, 2009 Danilo Segan <danilo@kvota.net>.

	This file is part of PHP-gettext.

	PHP-gettext is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	PHP-gettext is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with PHP-gettext; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

declare(strict_types=1);

namespace CoreLibs\Language\Core;

class FileReader
{
	/** @var int */
	public $fr_pos;
	/** @var resource|bool */
	public $fr_fd;
	/** @var int */
	public $fr_length;
	/** @var int */
	public $error = 0;

	/**
	 * file read constructor
	 * @param string $filename file name to load
	 */
	public function __construct(string $filename)
	{
		if (file_exists($filename)) {
			$this->fr_length = filesize($filename) ?: 0;
			$this->fr_pos = 0;
			$this->fr_fd = fopen($filename, 'rb');
			if (!is_resource($this->fr_fd)) {
				$this->error = 3; // Cannot read file, probably permissions
			}
		} else {
			$this->error = 2; // File doesn't exist
		}
	}

	/**
	 * read byte data length
	 * @param  int $bytes how many bytes to read
	 * @return string     read data as string
	 */
	public function read(int $bytes): string
	{
		if (!$bytes || !is_resource($this->fr_fd)) {
			return '';
		}
		fseek($this->fr_fd, $this->fr_pos);

		// PHP 5.1.1 does not read more than 8192 bytes in one fread()
		// the discussions at PHP Bugs suggest it's the intended behaviour
		$data = '';
		while ($bytes > 0) {
			$chunk = fread($this->fr_fd, $bytes);
			if ($chunk === false) {
				break;
			}
			$data .= $chunk;
			$bytes -= strlen($chunk);
		}
		$this->fr_pos = ftell($this->fr_fd) ?: 0;

		return $data;
	}

	/**
	 * seek to a position in the file
	 * @param  int $pos position where to go to
	 * @return int      file position after seek done
	 */
	public function seekto(int $pos): int
	{
		if (!is_resource($this->fr_fd)) {
			return 0;
		}
		fseek($this->fr_fd, $pos);
		$this->fr_pos = ftell($this->fr_fd) ?: 0;
		return $this->fr_pos;
	}

	/**
	 * get current position in file
	 * @return int current position in bytes
	 */
	public function currentpos(): int
	{
		return $this->fr_pos;
	}

	/**
	 * file length/size
	 * @return int file size in bytes
	 */
	public function length(): int
	{
		return $this->fr_length;
	}

	/**
	 * close open file handler
	 * @return void has no return
	 */
	public function close(): void
	{
		if (is_resource($this->fr_fd)) {
			fclose($this->fr_fd);
		}
	}
}

// __END__
