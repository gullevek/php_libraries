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

// Simple class to wrap file streams, string streams, etc.
// seek is essential, and it should be byte stream
class StreamReader
{
	/**
	 * constructor, empty
	 */
	public function __construct()
	{
		// empty
	}

	/**
	 * should return a string [FIXME: perhaps return array of bytes?]
	 *
	 * @param  int $bytes bytes to read
	 * @return bool       dummy false
	 */
	public function read(int $bytes): bool
	{
		return false;
	}

	/**
	 * should return new position
	 *
	 * @param  int $position seek to position
	 * @return bool          dummy false
	 */
	public function seekto(int $position): bool
	{
		return false;
	}

	/**
	 * returns current position
	 *
	 * @return bool dummy false
	 */
	public function currentpos(): bool
	{
		return false;
	}

	/**
	 * returns length of entire stream (limit for seekto()s)
	 *
	 * @return bool dummy false
	 */
	public function length(): bool
	{
		return false;
	}
}

// __END__
