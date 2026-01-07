<?php

/*
 * random key functions
 */

declare(strict_types=1);

namespace CoreLibs\Create;

use CoreLibs\Convert\Strings;

class RandomKey
{
	/** @var int set the default key length it nothing else is set */
	public const int KEY_LENGTH_DEFAULT = 4;
	/** @var int the maximum key length allowed */
	public const int KEY_LENGTH_MAX = 256;
	/** @var string the default characters in the key range */
	public const string KEY_CHARACTER_RANGE_DEFAULT =
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
		. 'abcdefghijklmnopqrstuvwxyz'
		. '0123456789';
	// key generation
	/** @var string all the characters that are int he current radnom key range */
	private static string $key_character_range = '';
	/** @var int character count in they key character range */
	private static int $key_character_range_length = 0;
	/** @var int default key lenghth */
	/** @deprecated Will be removed, as setting has moved to randomKeyGen */
	private static int $key_length = 4;

	/**
	 * if launched as class, init random key data first
	 *
	 * @param  array<string>  ...$key_range
	 */
	public function __construct(array ...$key_range)
	{
		$this->setRandomKeyData(...$key_range);
	}

	/**
	 * internal key range validation
	 *
	 * @param  array<string>  ...$key_range
	 * @return string
	 */
	private static function validateRandomKeyData(array ...$key_range): string
	{
		$key_character_range = Strings::buildCharStringFromLists(...$key_range);
		if (strlen(self::$key_character_range) <= 1) {
			return '';
		}
		return $key_character_range;
	}

	/**
	 * sets the random key range with the default values
	 *
	 * @param  array<string> $key_range a list of key ranges as array
	 * @return void has no return
	 * @throws \LengthException If the string length is only 1 abort
	 */
	public static function setRandomKeyData(array ...$key_range): void
	{
		// if key range is not set
		if (!count($key_range)) {
			self::$key_character_range = self::KEY_CHARACTER_RANGE_DEFAULT;
		} else {
			self::$key_character_range = self::validateRandomKeyData(...$key_range);
			// random key generation base string
		}
		self::$key_character_range_length = strlen(self::$key_character_range);
		if (self::$key_character_range_length <= 1) {
			throw new \LengthException(
				"The given key character range '" . self::$key_character_range . "' "
				. "is too small, must be at lest two characters: "
				. self::$key_character_range_length
			);
		}
	}

	/**
	 * get the characters for the current key characters
	 *
	 * @return string
	 */
	public static function getRandomKeyData(): string
	{
		return self::$key_character_range;
	}

	/**
	 * get the length of all random characters
	 *
	 * @return int
	 */
	public static function getRandomKeyDataLength(): int
	{
		return self::$key_character_range_length;
	}

	/**
	 * validates they key length for random string generation
	 *
	 * @param  int  $key_length key length
	 * @return bool             true for valid, false for invalid length
	 */
	private static function validateRandomKeyLength(int $key_length): bool
	{
		if (
			$key_length > 0 &&
			$key_length <= self::KEY_LENGTH_MAX
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * sets the key length and checks that they key given is valid
	 * if failed it will not change the default key length and return false
	 *
	 * @param  int  $key_length key length
	 * @return bool             true/false for set status
	 * @deprecated This function does no longer set the key length, the randomKeyGen parameter has to be used
	 */
	public static function setRandomKeyLength(int $key_length): bool
	{
		// only if valid int key with valid length
		if (self::validateRandomKeyLength($key_length) === true) {
			self::$key_length = $key_length;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get the current set random key length
	 *
	 * @return int Current set key length
	 * @deprecated Key length is set during randomKeyGen call, this nethid is deprecated
	 */
	public static function getRandomKeyLength(): int
	{
		return self::$key_length;
	}

	/**
	 * creates a random key based on the key_range with key_length
	 * if override key length is set, it will check on valid key and use this
	 * this will not set the class key length variable
	 *
	 * @param  int           $key_length [default=-1] key length override,
	 *                                   if not set use default [LEGACY]
	 * @param  array<string> $key_range  a list of key ranges as array,
	 *                                   if not set use previous set data
	 * @return string                    random key
	 */
	public static function randomKeyGen(
		int $key_length = self::KEY_LENGTH_DEFAULT,
		array ...$key_range
	): string {
		$key_character_range = '';
		if (count($key_range)) {
			$key_character_range = self::validateRandomKeyData(...$key_range);
			$key_character_range_length = strlen($key_character_range);
		} else {
			if (!self::$key_character_range_length) {
				self::setRandomKeyData();
			}
			$key_character_range = self::getRandomKeyData();
			$key_character_range_length = self::getRandomKeyDataLength();
		}
		// if not valid key length, fallback to default
		if (!self::validateRandomKeyLength($key_length)) {
			$key_length = self::KEY_LENGTH_DEFAULT;
		}
		// create random string
		$random_string = '';
		for ($i = 1; $i <= $key_length; $i++) {
			$random_string .= $key_character_range[
				random_int(0, $key_character_range_length - 1)
			];
		}
		return $random_string;
	}
}
