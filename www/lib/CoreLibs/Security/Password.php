<?php

/*
 * core password set, check and rehash check wrapper functions
 */

declare(strict_types=1);

namespace CoreLibs\Security;

class Password
{
	/**
	 * creates the password hash
	 *
	 * @param  string $password password
	 * @return string           hashed password
	 */
	public static function passwordSet(
		#[\SensitiveParameter]
		string $password
	): string {
		// always use the PHP default for the password
		// password options ca be set in the password init,
		// but should be kept as default
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * checks if the entered password matches the hash
	 *
	 * @param  string $password password
	 * @param  string $hash     password hash
	 * @return bool             true or false
	 */
	public static function passwordVerify(
		#[\SensitiveParameter]
		string $password,
		string $hash
	): bool {
		if (password_verify($password, $hash)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if the password needs to be rehashed
	 *
	 * @param  string $hash password hash
	 * @return bool         true or false
	 */
	public static function passwordRehashCheck(string $hash): bool
	{
		if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
			return true;
		} else {
			return false;
		}
	}
}

// __END__
