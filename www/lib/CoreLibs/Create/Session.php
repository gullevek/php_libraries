<?php

/*
 * AUTHOR: Clemens Schwaighofer
 * DESCRIPTION:
 * start a php sesseion
 * name can be given via startSession parameter
 * if not set tries to read $SET_SESSION_NAME from global
 * else will use default set in php.ini
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Session
{
	/**
	 * init a session, if array is empty or array does not have session_name set
	 * then no auto init is run
	 *
	 * @param string $session_name if set and not empty, will start session
	 */
	public function __construct(string $session_name = '')
	{
		if (!empty($session_name)) {
			$this->startSession($session_name);
		}
	}

	/**
	 * Start session
	 * startSession should be called for complete check
	 * If this is called without any name set before the php.ini name is
	 * used.
	 *
	 * @return void
	 */
	protected function startSessionCall(): void
	{
		session_start();
	}

	/**
	 * check if we are in CLI, we set this, so we can mock this
	 * Not this is just a wrapper for the static System::checkCLI call
	 *
	 * @return bool True if we are in a CLI enviroment, or false for everything else
	 */
	public function checkCliStatus(): bool
	{
		return \CoreLibs\Get\System::checkCLI();
	}

	/**
	 * Set session name call. If not valid session name, will return false
	 *
	 * @param  string $session_name A valid string for session name
	 * @return bool                 True if session name is valid,
	 *                              False if not
	 */
	public function setSessionName(string $session_name): bool
	{
		if (!$this->checkValidSessionName($session_name)) {
			return false;
		}
		session_name($session_name);
		return true;
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
	 * start session with given session name if set
	 * aborts on command line or if sessions are not enabled
	 * also aborts if session cannot be started
	 * On sucess returns the session id
	 *
	 * @param string|null $session_name
	 * @return string|bool
	 */
	public function startSession(?string $session_name = null): string|bool
	{
		// we can't start sessions on command line
		if ($this->checkCliStatus()) {
			throw new \RuntimeException('[SESSION] No sessions in php cli', 1);
		}
		// if session are OFF
		if ($this->getSessionStatus() === PHP_SESSION_DISABLED) {
			throw new \RuntimeException('[SESSION] Sessions are disabled', 2);
		}
		// session_status
		// initial the session if there is no session running already
		if (!$this->checkActiveSession()) {
			// if session name is emtpy, check if there is a global set
			// this is a deprecated fallback
			$session_name = $session_name ?? $GLOBALS['SET_SESSION_NAME'] ?? '';
			// DEPRECTED: constant SET_SESSION_NAME is no longer used
			// if set, set special session name
			if (!empty($session_name)) {
				// invalid session name, abort
				if (!$this->checkValidSessionName($session_name)) {
					throw new \UnexpectedValueException('[SESSION] Invalid session name: ' . $session_name, 3);
				}
				$this->setSessionName($session_name);
			}
			// start session
			$this->startSessionCall();
		}
		// if we still have no active session
		if (!$this->checkActiveSession()) {
			throw new \RuntimeException('[SESSION] Failed to activate session', 4);
		}
		if (false === ($session_id = $this->getSessionId())) {
			throw new \UnexpectedValueException('[SESSION] getSessionId did not return a session id', 5);
		}
		return $session_id;
	}

	/**
	 * get current set session id or false if none started
	 *
	 * @return string|bool
	 */
	public function getSessionId(): string|bool
	{
		return session_id();
	}

	/**
	 * get set session name or false if none started
	 *
	 * @return string|bool
	 */
	public function getSessionName(): string|bool
	{
		return session_name();
	}

	/**
	 * Checks if there is an active session.
	 * Does not check if we can have a session
	 *
	 * @return bool True if there is an active session, else false
	 */
	public function checkActiveSession(): bool
	{
		if ($this->getSessionStatus() === PHP_SESSION_ACTIVE) {
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
	public function writeClose(): bool
	{
		return session_write_close();
	}

	/**
	 * Proper destroy a session
	 * - unset the _SESSION array
	 * - unset cookie if cookie on and we have not strict mode
	 * - destroy session
	 *
	 * @return bool
	 */
	public function sessionDestroy(): bool
	{
		$_SESSION = [];
		if (
			ini_get('session.use_cookies') &&
			!ini_get('session.use_strict_mode')
		) {
			$session_name = $this->getSessionName();
			if ($session_name === false) {
				$session_name = '';
			}
			$params = session_get_cookie_params();
			setcookie(
				(string)$session_name,
				'',
				time() - 42000,
				$params['path'],
				$params['domain'],
				$params['secure'],
				$params['httponly']
			);
		}
		return session_destroy();
	}

	/**
	 * get session status
	 * PHP_SESSION_DISABLED if sessions are disabled.
	 * PHP_SESSION_NONE if sessions are enabled, but none exists.
	 * PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
	 *
	 * https://www.php.net/manual/en/function.session-status.php
	 *
	 * @return int See possible return int values above
	 */
	public function getSessionStatus(): int
	{
		return session_status();
	}

	// _SESSION set/unset methods

	/**
	 * unset all _SESSION entries
	 *
	 * @return void
	 */
	public function unsetAllS(): void
	{
		foreach (array_keys($_SESSION ?? []) as $name) {
			unset($_SESSION[$name]);
		}
	}

	/**
	 * set _SESSION entry 'name' with any value
	 *
	 * @param  string|int $name  array name in _SESSION
	 * @param  mixed      $value value to set (can be anything)
	 * @return void
	 */
	public function setS(string|int $name, mixed $value): void
	{
		$_SESSION[$name] = $value;
	}

	/**
	 * get _SESSION 'name' entry or empty string if not set
	 *
	 * @param  string|int $name value key to get from _SESSION
	 * @return mixed            value stored in _SESSION
	 */
	public function getS(string|int $name): mixed
	{
		return $_SESSION[$name] ?? '';
	}

	/**
	 * Check if a name is set in the _SESSION array
	 *
	 * @param  string|int $name Name to check for
	 * @return bool             True for set, False fornot set
	 */
	public function issetS(string|int $name): bool
	{
		return isset($_SESSION[$name]);
	}

	/**
	 * unset one _SESSION entry 'name' if exists
	 *
	 * @param  string|int $name _SESSION key name to remove
	 * @return void
	 */
	public function unsetS(string|int $name): void
	{
		if (isset($_SESSION[$name])) {
			unset($_SESSION[$name]);
		}
	}

	// set/get below
	// ->var = value;

	/**
	 * Undocumented function
	 *
	 * @param  string|int $name
	 * @param  mixed      $value
	 * @return void
	 */
	public function __set(string|int $name, mixed $value): void
	{
		$_SESSION[$name] = $value;
	}

	/**
	 * Undocumented function
	 *
	 * @param  string|int $name
	 * @return mixed            If name is not found, it will return null
	 */
	public function __get(string|int $name): mixed
	{
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}
		return null;
	}

	/**
	 * Undocumented function
	 *
	 * @param  string|int $name
	 * @return bool
	 */
	public function __isset(string|int $name): bool
	{
		return isset($_SESSION[$name]);
	}

	/**
	 * Undocumented function
	 *
	 * @param  string|int $name
	 * @return void
	 */
	public function __unset(string|int $name): void
	{
		if (isset($_SESSION[$name])) {
			unset($_SESSION[$name]);
		}
	}
}

// __END__
