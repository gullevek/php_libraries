<?php

/*
 * AUTHOR: Clemens Schwaighofer
 * DESCRIPTION:
 * start a php sesseion
 * name can be given via startSession parameter
 * if not set tries to read $SET_SESSION_NAME from global
 * if this is not set tries to read SET_SESSION_NAME constant
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Session
{
	/** @var string list for errors*/
	private static $error_str = '';

	/**
	 * init a session
	 */
	public function __construct()
	{
	}

	/**
	 * Return set error string, empty if none set
	 *
	 * @return string Last error string
	 */
	public static function getErrorStr(): string
	{
		return self::$error_str;
	}

	/**
	 * check if session name is valid
	 *
	 * As from PHP 8.1/8.0/7.4 error
	 * INVALID CHARS: =,; \t\r\n\013\014
	 * NOTE: using . will fail even thought valid
	 * we allow only alphanumeric with - (dash) and 1 to 128 characters
	 *
	 * @param  string $session_name any string, not null
	 * @return bool                 True for valid, False for invalid
	 */
	public static function checkValidSessionName(string $session_name): bool
	{
		// check
		if (
			// must only have those
			!preg_match('/^[-a-zA-Z0-9]{1,128}$/', $session_name) ||
			// cannot be only numbers
			preg_match('/^[0-9]+$/', $session_name)
		) {
			return false;
		}
		return true;
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
			self::$error_str = '[SESSION] No sessions in php cli';
			return false;
		}
		// if session are OFF
		if (self::getSessionStatus() === PHP_SESSION_DISABLED) {
			self::$error_str = '[SESSION] Sessions are disabled';
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
				// invalid session name, abort
				if (!self::checkValidSessionName($session_name)) {
					self::$error_str = '[SESSION] Invalid session name: ' . $session_name;
					return false;
				}
				session_name($session_name);
			}
			// start session
			session_start();
		}
		// if we still have no active session
		if (!self::checkActiveSession()) {
			self::$error_str = '[SESSION] Failed to activate session';
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
	 * @return bool True if there is an active session, else false
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
	 * unlock the session file, so concurrent AJAX requests can be done
	 * NOTE: after this has been called, no changes in _SESSION will be stored
	 * NOTE: a new session with a different name can be started after this one is called
	 * if problem, run ob_flush() and flush() too
	 *
	 * @return bool True und sucess, false on failure
	 */
	public static function writeClose(): bool
	{
		return session_write_close();
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
