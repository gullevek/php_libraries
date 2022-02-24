<?php

/*
 * AUTHOR: Clemens Schwaighofer
 * DESCRIPTION:
 * start a php sesseion
 * name can be given via startSession parameter
 * if not set tries to read $SET_SESSION_NAME from global
 * if this is not set tries to read SET_SESSION_NAME constant
 *
 * TODO: add _SESSION write unset
 * TODO: add session close down with all _SESSION vars unset
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
		// we can't start sessions on command line
		if (php_sapi_name() === 'cli') {
			return false;
		}
		// if session are OFF
		if (self::getSessionStatus() === PHP_SESSION_DISABLED) {
			return false;
		}
		// session_status
		// initial the session if there is no session running already
		if (!self::checkActiveSession()) {
			// if session name is emtpy, check if there is a global set
			// this is a deprecated fallback
			$session_name = $session_name ?? $GLOBALS['SET_SESSION_NAME'] ?? '';
			// check if we have an external session name given, else skip this step
			// this is a deprecated fallback
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
		// if we still have no active session
		if (!self::checkActiveSession()) {
			return false;
		}
		return self::getSessionId();
	}

	/**
	 * get current set session id or false if none started
	 *
	 * @return string|bool
	 */
	public static function getSessionId()
	{
		return session_id();
	}

	/**
	 * get set session name or false if none started
	 *
	 * @return string|bool
	 */
	public static function getSessionName()
	{
		return session_name();
	}

	/**
	 * Checks if there is an active session.
	 * Does not check if we can have a session
	 *
	 * @return boolean True if there is an active session, else false
	 */
	public static function checkActiveSession(): bool
	{
		if (self::getSessionStatus() === PHP_SESSION_ACTIVE) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get session status
	 * PHP_SESSION_DISABLED if sessions are disabled.
	 * PHP_SESSION_NONE if sessions are enabled, but none exists.
	 * PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
	 *
	 * https://www.php.net/manual/en/function.session-status.php
	 *
	 * @return int
	 */
	public static function getSessionStatus(): int
	{
		return session_status();
	}
}

// __END__
