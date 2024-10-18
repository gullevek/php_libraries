<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/9/7
 * DESCRIPTION:
 * General error collection class for output to frontend or to log
*/

declare(strict_types=1);

namespace CoreLibs\Logging;

use CoreLibs\Logging\Logger\MessageLevel;

class ErrorMessage
{
	/** @var array<int,array{id:string,level:string,str:string,target:string,target_style:string,highlight:string[]}> */
	private array $error_str = [];
	/** @var array<string,array{info:string,level:string}> */
	private array $jump_targets = [];
	/** @var \CoreLibs\Logging\Logging $log */
	public \CoreLibs\Logging\Logging $log;

	/** @var bool $log_error global flag to log error level message */
	private bool $log_error = false;
	/** @var bool $log_warning global flat to log warning level messages */
	private bool $log_warning = false;

	/**
	 * init ErrorMessage
	 *
	 * @param  \CoreLibs\Logging\Logging $log
	 * @param  null|bool                 $log_error [=null], defaults to false if log is not level debug
	 * @param  null|bool                 $log_warning [=null], defaults to false if log is not level debug
	 */
	public function __construct(
		\CoreLibs\Logging\Logging $log,
		?bool $log_error = null,
		?bool $log_warning = null
	) {
		$this->log = $log;
		// if log default logging is debug then log_error is default set to true
		if ($this->log->loggingLevelIsDebug() && $log_error === null) {
			$log_error = true;
		} else {
			$log_error = $log_error ?? false;
		}
		$this->log_error = $log_error;
		// if log default logging is debug then log_warning is default set to true
		if ($this->log->loggingLevelIsDebug() && $log_warning === null) {
			$log_warning = true;
		} else {
			$log_warning = $log_warning ?? false;
		}
		$this->log_warning = $log_warning;
	}

	/**
	 * pushes new error message into the error_str array
	 * error_id: internal Error ID (should be unique)
	 * level: error level, can only be ok, info, warn, error, abort, crash
	 *        ok and info are positive response: success
	 *        notice: a debug message for information only
	 *        warn: success, but there might be some things that are not 100% ok
	 *        error: input error or error in executing request
	 *        abort: an internal error happened as mandatory information that normally is
	 *               there is missing, or the ACL level that should normally match does not
	 *               will be logged to "critical"
	 *        crash: system failure or critical system problems (db connection failure)
	 *               will be logged as "alert"
	 *        not set: unkown, will be logged as "emergency"
	 * target/highlight: id target name for frontend where to attach this message
	 *                   highlight is a list of other target points to highlight
	 *                   for highlight targets css names are $level without a prefix and should be
	 *                   nested in the target element "input .error { ... }"
	 * jump_target: a target id for to jump and message, is stored in separate jump array
	 *              where the target is unique, first one set is used for info message
	 * target_style: if not set uses 'error-' $level as css style. applies to targets or main only
	 *
	 * @param  string        $error_id     Any internal error ID for this error
	 * @param  string        $level        Error level in ok/info/warn/error
	 * @param  string        $str          Error message (out)
	 * @param  string        $target       alternate attachment point for this error message
	 * @param  string        $target_style Alternate color style for the error message
	 * @param  array<string> $highlight    Any additional error data as error OR
	 *                                     highlight points for field highlights
	 * @param  array{}|array{target:string,info?:string} $jump_target with "target" for where to jump and
	 *                                     "info" for string to show in jump list
	 *                                     target must be set, if info not set, default message used
	 * @param  string|null   $message      If abort/crash, non localized $str
	 * @param  array<mixed>  $context      Additionl info for abort/crash messages
	 * @param  bool|null     $log_error    [=null] log level 'error' to error, if null use global,
	 *                                     else set for this call only
	 * @param  bool|null     $log_warning  [=null] log level 'warning' to warning, if null use global,
	 *                                     else set for this call only
	 */
	public function setErrorMsg(
		string $error_id,
		string $level,
		string $str,
		string $target = '',
		string $target_style = '',
		array $highlight = [],
		array $jump_target = [],
		?string $message = null,
		array $context = [],
		?bool $log_error = null,
		?bool $log_warning = null,
	): void {
		if ($log_error === null) {
			$log_error = $this->log_error;
		}
		if ($log_warning === null) {
			$log_warning = $this->log_warning;
		}
		$original_level = $level;
		$level = MessageLevel::fromName($level)->name;
		// if not string set, write message string if set, else level/error id
		if (empty($str)) {
			$str = $message ?? 'L:' . $level . '|E:' . $error_id;
		}
		$this->error_str[] = [
			'id' => $error_id,
			'level' => $level,
			'str' => $str,
			'target' => $target,
			'target_style' => $target_style,
			'highlight' => $highlight,
		];
		// set a jump target
		$this->setJumpTarget($jump_target['target'] ?? null, $jump_target['info'] ?? null, $level);
		// write to log for abort/crash
		switch ($level) {
			case 'notice':
				$this->log->notice($message ?? $str, array_merge([
					'id' => $error_id,
					'level' => $original_level,
				], $context));
				break;
			case 'warn':
				if ($log_warning) {
					$this->log->warning($message ?? $str, array_merge([
						'id' => $error_id,
						'level' => $original_level,
					], $context));
				}
				break;
			case 'error':
				if ($log_error) {
					$this->log->error($message ?? $str, array_merge([
						'id' => $error_id,
						'level' => $original_level,
					], $context));
				}
				break;
			case 'abort':
				$this->log->critical($message ?? $str, array_merge([
					'id' => $error_id,
					'level' => $original_level,
				], $context));
				break;
			case 'crash':
				$this->log->alert($message ?? $str, array_merge([
					'id' => $error_id,
					'level' => $original_level,
				], $context));
				break;
			case 'unknown':
				$this->log->emergency($message ?? $str, array_merge([
					'id' => $error_id,
					'level' => $original_level,
				], $context));
				break;
		}
	}

	/**
	 * pushes new error message into the error_str array
	 * Note, the parameter order is different and does not need an error id
	 * This is for backend alerts
	 *
	 * @param  string        $level        error level (ok/warn/info/error)
	 * @param  string        $str          error string
	 * @param  string|null   $error_id     optional error id for precise error lookup
	 * @param  string        $target       Alternate id name for output target on frontend
	 * @param  string        $target_style Alternate color style for the error message
	 * @param  array<string> $highlight    Any additional error data as error OR
	 *                                     highlight points for field highlights
	 * @param  array{}|array{target:string,info?:string} $jump_target with "target" for where to jump and
	 *                                     "info" for string to show in jump list
	 *                                     target must be set, if info not set, default message used
	 * @param  string|null   $message      If abort/crash, non localized $str
	 * @param  array<mixed>  $context      Additionl info for abort/crash messages
	 * @param  bool|null     $log_error    [=null] log level 'error' to error, if null use global,
	 *                                     else set for this call only
	 * @param  bool|null     $log_warning  [=null] log level 'warning' to warning, if null use global,
	 *                                     else set for this call only
	 */
	public function setMessage(
		string $level,
		string $str,
		?string $error_id = null,
		string $target = '',
		string $target_style = '',
		array $highlight = [],
		array $jump_target = [],
		?string $message = null,
		array $context = [],
		?bool $log_error = null,
		?bool $log_warning = null,
	): void {
		$this->setErrorMsg(
			$error_id ?? '',
			$level,
			$str,
			$target,
			$target_style,
			$highlight,
			$jump_target,
			$message,
			$context,
			$log_error,
			$log_warning
		);
	}

	/**
	 * Set a jump target. This can be used to jump directly a frontend html block
	 * with the target id set
	 *
	 * @param  string|null $target
	 * @param  string|null $info
	 * @param  string      $level [='error']
	 * @return void
	 */
	public function setJumpTarget(
		?string $target,
		?string $info,
		string $level = 'error',
	): void {
		if (
			empty($target) ||
			array_key_exists($target, $this->jump_targets)
			// !empty($this->jump_targets[$target])
			// also check if this is an alphanumeric string? css id compatible?
		) {
			return;
		}
		if (empty($info)) {
			$info = 'Jump to: ' . $target;
		}
		$level = MessageLevel::fromName($level)->name;
		$this->jump_targets[$target] = [
			'info' => $info,
			'level' => $level,
		];
	}

	// *********************************************************************
	// GETTERS
	// *********************************************************************

	/**
	 * Returns the current set error content from setErrorMsg method
	 *
	 * @return array<int,array{id:string,level:string,str:string,target:string,highlight:string[]}> Error messages array
	 */
	public function getErrorMsg(): array
	{
		return $this->error_str;
	}

	/**
	 * Current set error ids
	 *
	 * @return array<string>
	 */
	public function getErrorIds(): array
	{
		return array_column($this->error_str, 'id');
	}

	/**
	 * Gets the LAST entry in the array list.
	 * If nothing found returns empty array set
	 *
	 * @return array{id:string,level:string,str:string,target:string,target:string,highlight:string[]} Error block
	 */
	public function getLastErrorMsg(): array
	{
		return $this->error_str[array_key_last($this->error_str)] ?? [
			'level' => '',
			'str' => '',
			'id' => '',
			'target' => '',
			'target_string' => '',
			'highlight' => [],
		];
	}

	/**
	 * Return the jump target list
	 *
	 * @return array{}|array<int,array{target:string,info:string,level:string}> List of jump targets with info text,
	 *                                                                          or empty array if not set
	 */
	public function getJumpTarget(): array
	{
		$_jump_target = [];
		foreach ($this->jump_targets as $target => $jump) {
			$_jump_target[] = array_merge(
				$jump,
				[
					'target' => $target,
				]
			);
		}
		return $_jump_target;
	}

	// *********************************************************************
	// FLAG SETTERS
	// *********************************************************************

	/**
	 * Set the log error flag
	 *
	 * @param  bool $flag True to log level error too, False for do not (Default)
	 * @return void
	 */
	public function setFlagLogError(bool $flag): void
	{
		$this->log_error = $flag;
	}

	/**
	 * Get the current log error flag
	 *
	 * @return bool
	 */
	public function getFlagLogError(): bool
	{
		return $this->log_error;
	}

	/**
	 * Set the log warning flag
	 *
	 * @param  bool $flag True to log level warning too, False for do not (Default)
	 * @return void
	 */
	public function setFlagLogWarning(bool $flag): void
	{
		$this->log_warning = $flag;
	}

	/**
	 * Get the current log error flag
	 *
	 * @return bool
	 */
	public function getFlagLogWarning(): bool
	{
		return $this->log_warning;
	}
}

// __END__
