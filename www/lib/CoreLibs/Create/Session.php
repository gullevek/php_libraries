<?php

/*
 * start a php sesseion
 * name can be given via startSession parameter
 * if not set tries to read $SET_SESSION_NAME from global
 * if this is not set tries to read SET_SESSION_NAME constant
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Session
{
	/**
	 * init a session
	 */
	public function __construct()
	{
	}

	/**
	 * Undocumented function
	 *
	 * @param string|null $session_name
	 * @return string|bool
	 */
	public static function startSession(?string $session_name = null)
	{
		// initial the session if there is no session running already
		if (!session_id()) {
			$session_name = $session_name ?? $GLOBALS['SET_SESSION_NAME'] ?? '';
			// check if we have an external session name given, else skip this step
			if (
				empty($session_name) &&
				defined('SET_SESSION_NAME') &&
				!empty(SET_SESSION_NAME)
			) {
				// set the session name for possible later check
				$session_name = SET_SESSION_NAME;
			}
			// if set, set special session name
			if (!empty($session_name)) {
				session_name($session_name);
			}
			// start session
			session_start();
		}
		return self::getSessionId();
	}

	/**
	 * Undocumented function
	 *
	 * @return string|bool
	 */
	public static function getSessionId()
	{
		return session_id();
	}

	/**
	 * Undocumented function
	 *
	 * @return string|bool
	 */
	public static function getSessionName()
	{
		return session_name();
	}
}

// __END__
