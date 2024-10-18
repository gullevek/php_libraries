<?php

/*
 * NOTE: this is deprecated and all moved \CoreLibs\Security\Password
 *
 * core password set, check and rehash check wrapper functions
 */

declare(strict_types=1);

namespace CoreLibs\Check;

use CoreLibs\Security\Password as PasswordNew;

class Password
{
	/**
	 * creates the password hash
	 *
	 * @param  string $password password
	 * @return string           hashed password
	 * @deprecated v9.0 Moved to \CoreLibs\Security\Password::passwordSet
	 */
	public static function passwordSet(string $password): string
	{
		trigger_error(
			'Method ' . __METHOD__ . ' is deprecated, use '
				. '\CoreLibs\Security\Password::passwordSet',
			E_USER_DEPRECATED
		);
		return PasswordNew::passwordSet($password);
	}

	/**
	 * checks if the entered password matches the hash
	 *
	 * @param  string $password password
	 * @param  string $hash     password hash
	 * @return bool             true or false
	 * @deprecated v9.0 Moved to \CoreLibs\Security\Password::passwordVerify
	 */
	public static function passwordVerify(string $password, string $hash): bool
	{
		trigger_error(
			'Method ' . __METHOD__ . ' is deprecated, use '
				. '\CoreLibs\Security\Password::passwordVerify',
			E_USER_DEPRECATED
		);
		return PasswordNew::passwordVerify($password, $hash);
	}

	/**
	 * checks if the password needs to be rehashed
	 *
	 * @param  string $hash password hash
	 * @return bool         true or false
	 * @deprecated v9.0 Moved to \CoreLibs\Security\Password::passwordRehashCheck
	 */
	public static function passwordRehashCheck(string $hash): bool
	{
		trigger_error(
			'Method ' . __METHOD__ . ' is deprecated, use '
				. '\CoreLibs\Security\Password::passwordRehashCheck',
			E_USER_DEPRECATED
		);
		return PasswordNew::passwordRehashCheck($hash);
	}
}

// __END__
