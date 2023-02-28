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

class StringReader
{
	/** @var int */
	public $sr_pos;
	/** @var string */
	public $sr_str;

	/**
	 * constructor for string reader
	 *
	 * @param string $str basic string
	 */
	public function __construct(string $str = '')
	{
		$this->sr_str = $str;
		$this->sr_pos = 0;
	}

	/**
	 * read bytes in string
	 *
	 * @param  int $bytes bytes to read in string
	 * @return string     data read in length of bytes as string
	 */
	public function read(int $bytes): string
	{
		$data = substr($this->sr_str, $this->sr_pos, $bytes);
		$this->sr_pos += $bytes;
		if (strlen($this->sr_str) < $this->sr_pos) {
			$this->sr_pos = strlen($this->sr_str);
		}

		return $data;
	}

	/**
	 * go to position in string
	 *
	 * @param  int $pos position in string
	 * @return int      new position in string after seek
	 */
	public function seekto(int $pos): int
	{
		$this->sr_pos = $pos;
		if (strlen($this->sr_str) < $this->sr_pos) {
			$this->sr_pos = strlen($this->sr_str);
		}
		return $this->sr_pos;
	}

	/**
	 * get current position in string
	 *
	 * @return int position in string
	 */
	public function currentpos(): int
	{
		return $this->sr_pos;
	}

	/**
	 * get length of string
	 *
	 * @return int return length of assigned string
	 */
	public function length(): int
	{
		return strlen($this->sr_str);
	}
}

// __END__
