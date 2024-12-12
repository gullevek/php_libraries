<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/12/12
 * DESCRIPTION:
 * ACL Login user status bitmap list
*/

declare(strict_types=1);

namespace CoreLibs\ACL;

final class LoginUserStatus
{
	// lock status bitmap (smallint, 256)
	/** @var int enabled flag */
	public const ENABLED = 1;
	/** @var int deleted flag */
	public const DELETED = 2;
	/** @var int locked flag */
	public const LOCKED = 4;
	/** @var int banned/suspened flag [not implemented] */
	public const BANNED = 8;
	/** @var int password reset in progress [not implemented] */
	public const RESET = 16;
	/** @var int confirm/paending, eg waiting for confirm of email [not implemented] */
	public const CONFIRM = 32;
	/** @var int strict, on error lock */
	public const STRICT = 64;
	/** @var int proected, cannot delete */
	public const PROTECTED = 128;
	/** @var int master admin flag */
	public const ADMIN = 256;

	/**
	 * Returns an array mapping the numerical role values to their descriptive names
	 *
	 * @return array<int,string>
	 */
	public static function getMap()
	{
		return array_flip((new \ReflectionClass(static::class))->getConstants());
	}

	/**
	 * Returns the descriptive role names
	 *
	 * @return string[]
	 */
	public static function getNames()
	{

		return array_keys((new \ReflectionClass(static::class))->getConstants());
	}

	/**
	 * Returns the numerical role values
	 *
	 * @return int[]
	 */
	public static function getValues()
	{
		return array_values((new \ReflectionClass(static::class))->getConstants());
	}
}

// __END__
