<?php

/*
 * sets a form token in the _SESSION variable
 * session must be started for this to work
 */

declare(strict_types=1);

namespace CoreLibs\Output\Form;

class Token
{
	/**
	 * sets a form token in a session and returns form token
	 *
	 * @param  string $name optional form name, default form_token
	 * @return string       token name for given form id string
	 */
	public static function setFormToken(string $name = 'form_token'): string
	{
		// current hard set to sha256
		$token = uniqid(hash('sha256', (string)rand()));
		$_SESSION[$name] = $token;
		return $token;
	}

	/**
	 * checks if the form token matches the session set form token
	 *
	 * @param  string $token token string to check
	 * @param  string $name  optional form name to check to, default form_token
	 * @return bool          false if not set, or true/false if matching or not mtaching
	 */
	public static function validateFormToken(string $token, string $name = 'form_token'): bool
	{
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name] === $token;
		} else {
			return false;
		}
	}
}

// __END_
