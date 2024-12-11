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
	/** @var string current session name */
	private string $session_name = '';
	/** @var string current session id */
	private string $session_id = '';
	/** @var bool flag auto write close */
	private bool $auto_write_close = false;
	/** @var string regenerate option, default never */
	private string $regenerate = 'never';
	/** @var int regenerate interval either 1 to 100 for random or 0 to 3600 for interval */
	private int $regenerate_interval = 0;

	/** @var array<string> allowed session id regenerate (rotate) options */
	private const ALLOWED_REGENERATE_OPTIONS = ['none', 'random', 'interval'];
	/** @var int default random interval */
	public const DEFAULT_REGENERATE_RANDOM = 100;
	/** @var int default rotate internval in minutes */
	public const DEFAULT_REGENERATE_INTERVAL = 5 * 60;
	/** @var int maximum time for regenerate interval is one hour */
	public const MAX_REGENERATE_INTERAL = 60 * 60;

	/**
	 * init a session, if array is empty or array does not have session_name set
	 * then no auto init is run
	 *
	 * @param string $session_name if set and not empty, will start session
	 * @param  array<string,bool> $options
	 */
	public function __construct(
		string $session_name,
		array $options = []
	) {
		$this->setOptions($options);
		$this->initSession($session_name);
	}

	// MARK: private methods

	/**
	 * set session class options
	 *
	 * @param  array<string,bool> $options
	 * @return void
	 */
	private function setOptions(array $options): void
	{
		if (
			!isset($options['auto_write_close']) ||
			!is_bool($options['auto_write_close'])
		) {
			$options['auto_write_close'] = false;
		}
		$this->auto_write_close = $options['auto_write_close'];
		if (
			!isset($options['session_strict']) ||
			!is_bool($options['session_strict'])
		) {
			$options['session_strict'] = true;
		}
		// set strict options, on not started sessiononly
		if (
			$options['session_strict'] &&
			$this->getSessionStatus() === PHP_SESSION_NONE
		) {
			// use cookies to store session IDs
			ini_set('session.use_cookies', 1);
			// use cookies only (do not send session IDs in URLs)
			ini_set('session.use_only_cookies', 1);
			// do not send session IDs in URLs
			ini_set('session.use_trans_sid', 0);
		}
		// session regenerate id options
		if (
			empty($options['regenerate']) ||
			!in_array($options['regenerate'], self::ALLOWED_REGENERATE_OPTIONS)
		) {
			$options['regenerate'] = 'never';
		}
		$this->regenerate = (string)$options['regenerate'];
		// for regenerate: 'random' (default 100)
		// regenerate_interval must be between (1 = always) and 100 (1 in 100)
		// for regenerate: 'interval' (default 5min)
		// regenerate_interval must be 0 = always, to 3600 (every hour)
		if (
			$options['regenerate'] == 'random' &&
			(
				!isset($options['regenerate_interval']) ||
				!is_numeric($options['regenerate_interval']) ||
				$options['regenerate_interval'] < 0 ||
				$options['regenerate_interval'] > 100
			)
		) {
			$options['regenerate_interval'] = self::DEFAULT_REGENERATE_RANDOM;
		}
		if (
			$options['regenerate'] == 'interval' &&
			(
				!isset($options['regenerate_interval']) ||
				!is_numeric($options['regenerate_interval']) ||
				$options['regenerate_interval'] < 1 ||
				$options['regenerate_interval'] > self::MAX_REGENERATE_INTERAL
			)
		) {
			$options['regenerate_interval'] = self::DEFAULT_REGENERATE_INTERVAL;
		}
		$this->regenerate_interval = (int)($options['regenerate_interval'] ?? 0);
	}

	/**
	 * Start session
	 * startSession should be called for complete check
	 * If this is called without any name set before the php.ini name is
	 * used.
	 *
	 * @return void
	 */
	private function startSessionCall(): void
	{
		session_start();
	}

	/**
	 * get current set session id or false if none started
	 *
	 * @return string|false
	 */
	public function getSessionIdCall(): string|false
	{
		return session_id();
	}

	/**
	 * automatically closes a session if the auto write close flag is set
	 *
	 * @return bool
	 */
	private function closeSessionCall(): bool
	{
		if ($this->auto_write_close) {
			return $this->writeClose();
		}
		return false;
	}

	// MARK: regenerate session

	/**
	 * auto rotate session id
	 *
	 * @return void
	 * @throws \RuntimeException failure to regenerate session id
	 * @throws \UnexpectedValueException failed to get new session id
	 * @throws \RuntimeException failed to set new sesson id
	 * @throws \UnexpectedValueException new session id generated does not match the new set one
	 */
	private function sessionRegenerateSessionId()
	{
		// never
		if ($this->regenerate == 'never') {
			return;
		}
		// regenerate
		if (
			!(
				// is not session obsolete
				empty($_SESSION['SESSION_REGENERATE_OBSOLETE']) &&
				(
					(
						// random
						$this->regenerate == 'random' &&
						mt_rand(1, $this->regenerate_interval) == 1
					) || (
						// interval type
						$this->regenerate == 'interval' &&
						($_SESSION['SESSION_REGENERATE_TIMESTAMP'] ?? 0) + $this->regenerate_interval < time()
					)
				)
			)
		) {
			return;
		}
		// Set current session to expire in 1 minute
		$_SESSION['SESSION_REGENERATE_OBSOLETE'] = true;
		$_SESSION['SESSION_REGENERATE_EXPIRES'] = time() + 60;
		$_SESSION['SESSION_REGENERATE_TIMESTAMP'] = time();
		// Create new session without destroying the old one
		if (session_regenerate_id(false) === false) {
			throw new \RuntimeException('[SESSION] Session id regeneration failed', 1);
		}
		// Grab current session ID and close both sessions to allow other scripts to use them
		if (false === ($new_session_id = $this->getSessionIdCall())) {
			throw new \UnexpectedValueException('[SESSION] getSessionIdCall did not return a session id', 2);
		}
		$this->writeClose();
		// Set session ID to the new one, and start it back up again
		if (($get_new_session_id = session_id($new_session_id)) === false) {
			throw new \RuntimeException('[SESSION] set session_id failed', 3);
		}
		if ($get_new_session_id != $new_session_id) {
			throw new \UnexpectedValueException('[SESSION] new session id does not match the new set one', 4);
		}
		$this->session_id = $new_session_id;
		$this->startSessionCall();
		// Don't want this one to expire
		unset($_SESSION['SESSION_REGENERATE_OBSOLETE']);
		unset($_SESSION['SESSION_REGENERATE_EXPIRES']);
	}

	// MARK: session validation

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
	 * validate _SESSION key, must be valid variable
	 *
	 * @param  int|float|string $key
	 * @return true
	 */
	private function checkValidSessionEntryKey(int|float|string $key): true
	{
		if (!is_string($key) || is_numeric($key)) {
			throw new \UnexpectedValueException(
				'[SESSION] Given key for _SESSION is not a valid value for a varaible: ' . $key,
				1
			);
		}
		return true;
	}

	// MARK: init session (on class start)

	/**
	 * stinitart session with given session name if set
	 * aborts on command line or if sessions are not enabled
	 * also aborts if session cannot be started
	 * On sucess returns the session id
	 *
	 * @param string $session_name
	 * @return void
	 */
	private function initSession(string $session_name): void
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
			// invalid session name, abort
			if (!$this->checkValidSessionName($session_name)) {
				throw new \UnexpectedValueException('[SESSION] Invalid session name: ' . $this->session_name, 3);
			}
			// set session name
			$this->session_name = $session_name;
			session_name($this->session_name);
			// start session
			$this->startSessionCall();
			// if we faild to start the session
			if (!$this->checkActiveSession()) {
				throw new \RuntimeException('[SESSION] Failed to activate session', 5);
			}
			if (
				!empty($_SESSION['SESSION_REGENERATE_OBSOLETE']) &&
				!empty($_SESSION['SESSION_REGENERATE_EXPIRES']) && $_SESSION['SESSION_REGENERATE_EXPIRES'] < time()
			) {
				$this->sessionDestroy();
				throw new \RuntimeException('[SESSION] Expired session found', 6);
			}
		} elseif ($session_name != $this->getSessionName()) {
			throw new \UnexpectedValueException(
				'[SESSION] Another session exists with a different name: ' . $this->getSessionName(),
				4
			);
		}
		// check session id
		if (false === ($session_id = $this->getSessionIdCall())) {
			throw new \UnexpectedValueException('[SESSION] getSessionIdCall did not return a session id', 7);
		}
		// set session id
		$this->session_id = $session_id;
		// run session id re-create from time to time
		$this->sessionRegenerateSessionId();
		// if flagged auto close, write close session
		if ($this->auto_write_close) {
			$this->writeClose();
		}
	}

	// MARK: public set/get status

	/**
	 * start session, will only run after initSession
	 *
	 * @return bool True if started, False if alrady running
	 */
	public function restartSession(): bool
	{
		if (!$this->checkActiveSession()) {
			if (empty($this->session_name)) {
				throw new \RuntimeException('[SESSION] Cannot restart session without a session name', 1);
			}
			$this->startSessionCall();
			return true;
		}
		return false;
	}

	/**
	 * current set session id
	 *
	 * @return string
	 */
	public function getSessionId(): string
	{
		return $this->session_id;
	}

	/**
	 * set the auto write close flag
	 *
	 * @param  bool $flag
	 * @return void
	 */
	public function setAutoWriteClose(bool $flag): void
	{
		$this->auto_write_close = $flag;
	}

	/**
	 * return the auto write close flag
	 *
	 * @return bool
	 */
	public function checkAutoWriteClose(): bool
	{
		return $this->auto_write_close;
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

	// MARK: write close session

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

	// MARK: session close and clean up

	/**
	 * Proper destroy a session
	 * - unset the _SESSION array
	 * - unset cookie if cookie on and we have not strict mode
	 * - unset session_name and session_id internal vars
	 * - destroy session
	 *
	 * @return bool True on successful session destroy
	 */
	public function sessionDestroy(): bool
	{
		// abort to false if not unsetable
		if (!session_unset()) {
			return false;
		}
		$this->clear();
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
		// unset internal vars
		$this->session_name = '';
		$this->session_id = '';
		return session_destroy();
	}

	// MARK: _SESSION set/unset methods

	/**
	 * unset all _SESSION entries
	 *
	 * @return void
	 */
	public function clear(): void
	{
		$this->restartSession();
		if (!session_unset()) {
			throw new \RuntimeException('[SESSION] Cannot unset session vars', 1);
		}
		if (!empty($_SESSION)) {
			$_SESSION = [];
		}
		$this->closeSessionCall();
	}

	/**
	 * set _SESSION entry 'name' with any value
	 *
	 * @param  string $name  array name in _SESSION
	 * @param  mixed  $value value to set (can be anything)
	 * @return void
	 */
	public function set(string $name, mixed $value): void
	{
		$this->checkValidSessionEntryKey($name);
		$this->restartSession();
		$_SESSION[$name] = $value;
		$this->closeSessionCall();
	}

	/**
	 * set many session entries in one set
	 *
	 * @param  array<string,mixed> $set key is the key in the _SESSION, value is any data to set
	 * @return void
	 */
	public function setMany(array $set): void
	{
		$this->restartSession();
		// skip any that are not valid
		foreach ($set as $key => $value) {
			$this->checkValidSessionEntryKey($key);
			$_SESSION[$key] = $value;
		}
		$this->closeSessionCall();
	}

	/**
	 * get _SESSION 'name' entry or empty string if not set
	 *
	 * @param  string $name value key to get from _SESSION
	 * @return mixed        value stored in _SESSION, if not found set to null
	 */
	public function get(string $name): mixed
	{
		return $_SESSION[$name] ?? null;
	}

	/**
	 * get multiple session entries
	 *
	 * @param  array<string> $set
	 * @return array<string,mixed>
	 */
	public function getMany(array $set): array
	{
		return array_intersect_key($_SESSION, array_flip($set));
	}

	/**
	 * Check if a name is set in the _SESSION array
	 *
	 * @param  string $name Name to check for
	 * @return bool                   True for set, False fornot set
	 */
	public function isset(string $name): bool
	{
		return isset($_SESSION[$name]);
	}

	/**
	 * unset one _SESSION entry 'name' if exists
	 *
	 * @param  string $name _SESSION key name to remove
	 * @return void
	 */
	public function unset(string $name): void
	{
		if (!isset($_SESSION[$name])) {
			return;
		}
		$this->restartSession();
		unset($_SESSION[$name]);
		$this->closeSessionCall();
	}

	/**
	 * reset many session entry
	 *
	 * @param  array<string> $set list of session keys to reset
	 * @return void
	 */
	public function unsetMany(array $set): void
	{
		$this->restartSession();
		foreach ($set as $key) {
			if (!isset($_SESSION[$key])) {
				continue;
			}
			unset($_SESSION[$key]);
		}
		$this->closeSessionCall();
	}
}

// __END__
