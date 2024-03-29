<?php

/*
 * random key functions
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class RandomKey
{
	// key generation
	/** @var string */
	private static string $key_range = '';
	/** @var int */
	private static int $one_key_length;
	/** @var int */
	private static int $key_length = 4; // default key length
	/** @var int */
	private static int $max_key_length = 256; // max allowed length

	/**
	 * if launched as class, init random key data first
	 */
	public function __construct()
	{
		$this->initRandomKeyData();
	}
	/**
	 * sets the random key range with the default values
	 *
	 * @return void has no return
	 */
	private static function initRandomKeyData(): void
	{
		// random key generation base string
		self::$key_range = join('', array_merge(
			range('A', 'Z'),
			range('a', 'z'),
			range('0', '9')
		));
		self::$one_key_length = strlen(self::$key_range);
	}

	/**
	 * validates they key length for random string generation
	 *
	 * @param  int  $key_length key length
	 * @return bool             true for valid, false for invalid length
	 */
	private static function validateRandomKeyLenght(int $key_length): bool
	{
		if (
			$key_length > 0 &&
			$key_length <= self::$max_key_length
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
	 */
	public static function setRandomKeyLength(int $key_length): bool
	{
		// only if valid int key with valid length
		if (self::validateRandomKeyLenght($key_length) === true) {
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
	 * @param  int    $key_length key length override, -1 for use default
	 * @return string             random key
	 */
	public static function randomKeyGen(int $key_length = -1): string
	{
		// init random key strings if not set
		if (
			!isset(self::$one_key_length)
		) {
			self::initRandomKeyData();
		}
		$use_key_length = 0;
		// only if valid int key with valid length
		if (self::validateRandomKeyLenght($key_length) === true) {
			$use_key_length = $key_length;
		} else {
			$use_key_length = self::$key_length;
		}
		// create random string
		$random_string = '';
		for ($i = 1; $i <= $use_key_length; $i++) {
			$random_string .= self::$key_range[random_int(0, self::$one_key_length - 1)];
		}
		return $random_string;
	}
}
