<?php declare(strict_types=1);

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

namespace CoreLibs\Language\Core;

class FileReader
{
	public $fr_pos;
	public $fr_fd;
	public $fr_length;

	public function __construct($filename)
	{
		if (file_exists($filename)) {
			$this->fr_length = filesize($filename);
			$this->fr_pos = 0;
			$this->fr_fd = fopen($filename, 'rb');
			if (!$this->fr_fd) {
				$this->error = 3; // Cannot read file, probably permissions
				return false;
			}
		} else {
			$this->error = 2; // File doesn't exist
			return false;
		}
	}

	public function read($bytes)
	{
		if ($bytes) {
			fseek($this->fr_fd, $this->fr_pos);

			// PHP 5.1.1 does not read more than 8192 bytes in one fread()
			// the discussions at PHP Bugs suggest it's the intended behaviour
			$data = '';
			while ($bytes > 0) {
				$chunk = fread($this->fr_fd, $bytes);
				$data .= $chunk;
				$bytes -= strlen($chunk);
			}
			$this->fr_pos = ftell($this->fr_fd);

			return $data;
		} else {
			return '';
		}
	}

	public function seekto($pos)
	{
		fseek($this->fr_fd, $pos);
		$this->fr_pos = ftell($this->fr_fd);
		return $this->fr_pos;
	}

	public function currentpos()
	{
		return $this->fr_pos;
	}

	public function length()
	{
		return $this->fr_length;
	}

	public function close()
	{
		fclose($this->fr_fd);
	}
}

// __END__
