<?php

/*
	Copyright (c) 2003, 2009 Danilo Segan <danilo@kvota.net>.
	Copyright (c) 2005 Nico Kaiser <nico@siriux.net>

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

/**
* Provides a simple gettext replacement that works independently from
* the system's gettext abilities.
* It can read MO files and use them for translating strings.
* The files are passed to gettext_reader as a Stream (see streams.php)
*
* This version has the ability to cache all strings and translations to
* speed up the string lookup.
* While the cache is enabled by default, it can be switched off with the
* second parameter in the constructor (e.g. whenusing very large MO files
* that you don't want to keep in memory)
*/
class GetTextReader
{
	// public:
	/** @var int */
	public int $error = 0; // public variable that holds error code (0 if no error)

	// private:
	/** @var int */
	private int $BYTEORDER = 0;        // 0: low endian, 1: big endian
	/** @var FileReader */
	private FileReader $STREAM;
	/** @var bool */
	private bool $short_circuit = false;
	/** @var bool */
	private bool $enable_cache = false;
	/** @var int */
	private int $originals = 0;      // offset of original table
	/** @var int */
	private int $translations = 0;    // offset of translation table
	/** @var string */
	private string $pluralheader = '';    // cache header field for plural forms
	/** @var int */
	private int $total = 0;          // total string count
	/** @var array<mixed>|null */
	private array|null $table_originals = null;  // table for original strings (offsets)
	/** @var array<mixed>|null */
	private array|null $table_translations = null;  // table for translated strings (offsets)
	/** @var array<mixed> */
	private array $cache_translations = [];  // original -> translation mapping

	/* Methods */

	/**
	* Reads a 32bit Integer from the Stream
	*
	* @access private
	* @return int Integer from the Stream
	*/
	private function readint(): int
	{
		if ($this->BYTEORDER == 0) {
			// low endian
			$input = unpack('V', $this->STREAM->read(4)) ?: [];
		} else {
			// big endian
			$input = unpack('N', $this->STREAM->read(4)) ?: [];
		}
		return array_shift($input);
	}

	/**
	 * read bytes
	 *
	 * @param  int    $bytes byte length to read
	 * @return string        return data, possible string
	 */
	public function read(int $bytes): string
	{
		return $this->STREAM->read($bytes);
	}

	/**
	* Reads an array of Integers from the Stream
	*
	* @param  int   $count How many elements should be read
	* @return array<mixed> Array of Integers
	*/
	public function readintarray(int $count): array
	{
		if ($this->BYTEORDER == 0) {
			// low endian
			return unpack('V' . $count, $this->STREAM->read(4 * $count)) ?: [];
		} else {
			// big endian
			return unpack('N' . $count, $this->STREAM->read(4 * $count)) ?: [];
		}
	}

	/**
	* Constructor
	*
	* @param FileReader|bool $Reader       the StreamReader object
	* @param bool            $enable_cache Enable or disable caching
	*                                      of strings (default on)
	*/
	public function __construct(FileReader|bool $Reader, bool $enable_cache = true)
	{
		// If there isn't a StreamReader, turn on short circuit mode.
		if ((!is_object($Reader) && !$Reader) || (is_object($Reader) && $Reader->error)) {
			$this->short_circuit = true;
			return;
		}
		// bail out for sure if this is not an objet here
		if (!is_object($Reader)) {
			$this->short_circuit = true;
			return;
		}

		// Caching can be turned off
		$this->enable_cache = $enable_cache;

		$MAGIC1 = "\x95\x04\x12\xde";
		$MAGIC2 = "\xde\x12\x04\x95";

		$this->STREAM = $Reader;
		$magic = $this->read(4);
		if ($magic == $MAGIC1) {
			$this->BYTEORDER = 1;
		} elseif ($magic == $MAGIC2) {
			$this->BYTEORDER = 0;
		} else {
			$this->error = 1; // not MO file
		}

		// FIXME: Do we care about revision? We should.
		$revision = $this->readint();

		$this->total = $this->readint();
		$this->originals = $this->readint();
		$this->translations = $this->readint();
	}

	/**
	 * Get current short circuit, equals to no translator running
	 *
	 * @return bool
	 */
	public function getShortCircuit(): bool
	{
		return $this->short_circuit;
	}

	/**
	 * get the current cache enabled status
	 *
	 * @return bool
	 */
	public function getEnableCache(): bool
	{
		return $this->enable_cache;
	}

	/**
	* Loads the translation tables from the MO file into the cache
	* If caching is enabled, also loads all strings into a cache
	* to speed up translation lookups
	*
	* @access private
	* @return void
	*/
	private function loadTables(): void
	{
		if (
			is_array($this->cache_translations) &&
			is_array($this->table_originals) &&
			is_array($this->table_translations)
		) {
			return;
		}

		/* get original and translations tables */
		if (!is_array($this->table_originals)) {
			$this->STREAM->seekto($this->originals);
			$this->table_originals = $this->readintarray($this->total * 2);
		}
		if (!is_array($this->table_translations)) {
			$this->STREAM->seekto($this->translations);
			$this->table_translations = $this->readintarray($this->total * 2);
		}

		if ($this->enable_cache) {
			$this->cache_translations = [];
			/* read all strings in the cache */
			for ($i = 0; $i < $this->total; $i++) {
				$this->STREAM->seekto($this->table_originals[$i * 2 + 2]);
				$original = $this->STREAM->read($this->table_originals[$i * 2 + 1]);
				$this->STREAM->seekto($this->table_translations[$i * 2 + 2]);
				$translation = $this->STREAM->read($this->table_translations[$i * 2 + 1]);
				$this->cache_translations[$original] = $translation;
			}
		}
	}

	/**
	* Returns a string from the "originals" table
	*
	* @access private
	* @param  int    $num Offset number of original string
	* @return string      Requested string if found, otherwise ''
	*/
	private function getOriginalString(int $num): string
	{
		$length = $this->table_originals[$num * 2 + 1] ?? 0;
		$offset = $this->table_originals[$num * 2 + 2] ?? 0;
		if (!$length) {
			return '';
		}
		$this->STREAM->seekto($offset);
		$data = $this->STREAM->read($length);
		return (string)$data;
	}

	/**
	* Returns a string from the "translations" table
	*
	* @access private
	* @param  int    $num Offset number of original string
	* @return string      Requested string if found, otherwise ''
	*/
	private function getTranslationString(int $num): string
	{
		$length = $this->table_translations[$num * 2 + 1] ?? 0;
		$offset = $this->table_translations[$num * 2 + 2] ?? 0;
		if (!$length) {
			return '';
		}
		$this->STREAM->seekto($offset);
		$data = $this->STREAM->read($length);
		return (string)$data;
	}

	/**
	* Binary search for string
	*
	* @access private
	* @param  string $string string to find
	* @param  int    $start  (internally used in recursive function)
	* @param  int    $end    (internally used in recursive function)
	* @return int            (offset in originals table)
	*/
	private function findString(string $string, int $start = -1, int $end = -1): int
	{
		if (($start == -1) or ($end == -1)) {
			// findString is called with only one parameter, set start end end
			$start = 0;
			$end = $this->total;
		}
		if (abs($start - $end) <= 1) {
			// We're done, now we either found the string, or it doesn't exist
			$txt = $this->getOriginalString($start);
			if ($string == $txt) {
				return $start;
			} else {
				return -1;
			}
		} elseif ($start > $end) {
			// start > end -> turn around and start over
			return $this->findString($string, $end, $start);
		} else {
			// Divide table in two parts
			$half = (int)(($start + $end) / 2);
			$cmp = strcmp($string, $this->getOriginalString($half));
			if ($cmp == 0) {
				// string is exactly in the middle => return it
				return $half;
			} elseif ($cmp < 0) {
				// The string is in the upper half
				return $this->findString($string, $start, $half);
			} else {
				// Translateshe string is in the lower half
				return $this->findString($string, $half, $end);
			}
		}
	}

	/**
	* Translates a string
	*
	* @access public
	* @param  string $string to be translated
	* @return string         translated string (or original, if not found)
	*/
	public function translate(string $string): string
	{
		if ($this->short_circuit) {
			return $string;
		}
		$this->loadTables();

		if ($this->enable_cache) {
			// Caching enabled, get translated string from cache
			if (
				is_array($this->cache_translations) &&
				array_key_exists($string, $this->cache_translations)
			) {
				return $this->cache_translations[$string];
			} else {
				return $string;
			}
		} else {
			// Caching not enabled, try to find string
			$num = $this->findString($string);
			if ($num == -1) {
				return $string;
			} else {
				return $this->getTranslationString($num);
			}
		}
	}

	/**
	* Sanitize plural form expression for use in PHP eval call.
	*
	* @access private
	* @param  string $expr an expression to match
	* @return string       sanitized plural form expression
	*/
	private function sanitizePluralExpression(string $expr): string
	{
		// Get rid of disallowed characters.
		$expr = preg_replace('@[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*/\%-]@', '', $expr);

		// Add parenthesis for tertiary '?' operator.
		$expr .= ';';
		$res = '';
		$p = 0;
		$expr_len = strlen($expr);
		for ($i = 0; $i < $expr_len; $i++) {
			$ch = $expr[$i];
			switch ($ch) {
				case '?':
					$res .= ' ? (';
					$p++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')', $p) . ';';
					$p = 0;
					break;
				default:
					$res .= $ch;
			}
		}
		return $res;
	}

	/**
	* Parse full PO header and extract only plural forms line.
	*
	* @access private
	* @param  string $header header search in plurals
	* @return string         verbatim plural form header field
	*/
	private function extractPluralFormsHeaderFromPoHeader(string $header): string
	{
		if (preg_match("/(^|\n)plural-forms: ([^\n]*)\n/i", $header, $regs)) {
			$expr = $regs[2];
		} else {
			$expr = "nplurals=2; plural=n == 1 ? 0 : 1;";
		}
		return $expr;
	}

	/**
	* Get possible plural forms from MO header
	*
	* @access private
	* @return string plural form header
	*/
	private function getPluralForms(): string
	{
		// lets assume message number 0 is header
		// this is true, right?
		$this->loadTables();

		// cache header field for plural forms
		if (empty($this->pluralheader) || !is_string($this->pluralheader)) {
			if ($this->enable_cache) {
				$header = $this->cache_translations[''];
			} else {
				$header = $this->getTranslationString(0);
			}
			$expr = $this->extractPluralFormsHeaderFromPoHeader($header);
			$this->pluralheader = $this->sanitizePluralExpression($expr);
		}
		return $this->pluralheader;
	}

	/**
	* Detects which plural form to take
	*
	* @access private
	* @param  int $n count
	* @return int    array index of the right plural form
	*/
	private function selectString(int $n): int
	{
		$string = $this->getPluralForms();
		$string = str_replace('nplurals', "\$total", $string);
		$string = str_replace("n", (string)$n, $string);
		$string = str_replace('plural', "\$plural", $string);

		$total = 0;
		$plural = 0;

		// FIXME use  Symfony\Component\ExpressionLanguage\ExpressionLanguage or similar
		eval("$string");
		/** @phpstan-ignore-next-line 0 >= 0 is always true*/
		if ($plural >= $total) {
			$plural = $total - 1;
		}
		return (int)$plural;
	}

	/**
	 * wrapper for translate() method
	 *
	 * @access public
	 * @param  string $string
	 * @return string
	 */
	public function gettext(string $string): string
	{
		return $this->translate($string);
	}

	/**
	* Plural version of gettext
	*
	* @access public
	* @param  string $single
	* @param  string $plural
	* @param  int    $number
	* @return string plural form
	*/
	public function ngettext(string $single, string $plural, int $number): string
	{
		if ($this->short_circuit) {
			if ($number != 1) {
				return $plural;
			} else {
				return $single;
			}
		}

		// find out the appropriate form
		$select = $this->selectString($number);

		// this should contains all strings separated by NULLs
		$key = $single . chr(0) . $plural;

		if ($this->enable_cache) {
			if (is_array($this->cache_translations) && !array_key_exists($key, $this->cache_translations)) {
				return ($number != 1) ? $plural : $single;
			} else {
				$result = $this->cache_translations[$key];
				$list = explode(chr(0), $result);
				return $list[$select];
			}
		} else {
			$num = $this->findString($key);
			if ($num == -1) {
				return ($number != 1) ? $plural : $single;
			} else {
				$result = $this->getTranslationString($num);
				$list = explode(chr(0), $result);
				return $list[$select];
			}
		}
	}

	/**
	 * p get text
	 *
	 * @param  string $context [description]
	 * @param  string $msgid   [description]
	 * @return string          [description]
	 */
	public function pgettext(string $context, string $msgid): string
	{
		$key = $context . chr(4) . $msgid;
		$ret = $this->translate($key);
		if (strpos($ret, "\004") !== false) {
			return $msgid;
		} else {
			return $ret;
		}
	}

	/**
	 * np get text
	 *
	 * @param  string $context  [description]
	 * @param  string $singular [description]
	 * @param  string $plural   [description]
	 * @param  int    $number   [description]
	 * @return string           [description]
	 */
	public function npgettext(
		string $context,
		string $singular,
		string $plural,
		int $number
	): string {
		$key = $context . chr(4) . $singular;
		$ret = $this->ngettext($key, $plural, $number);
		if (strpos($ret, "\004") !== false) {
			return $singular;
		} else {
			return $ret;
		}
	}
}

// __END__
