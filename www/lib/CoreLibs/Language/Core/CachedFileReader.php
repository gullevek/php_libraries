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

// Preloads entire file in memory first, then creates a StringReader
// over it (it assumes knowledge of StringReader internals)
class CachedFileReader extends \CoreLibs\Language\Core\StringReader
{
	public $error = 0;
	public $_str = '';

	public function __construct($filename)
	{
		parent::__construct();
		if (file_exists($filename)) {
			$length = filesize($filename);
			$fd = fopen($filename, 'rb');

			if (!$fd) {
				$this->error = 3; // Cannot read file, probably permissions
			}
			$this->_str = fread($fd, $length);
			fclose($fd);
		} else {
			$this->error = 2; // File doesn't exist
		}
	}
}

// __END__
