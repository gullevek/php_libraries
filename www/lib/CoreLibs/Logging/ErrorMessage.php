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
	/** @var array<int,array{id:string,level:string,str:string,target:string,highlight:string[]}> */
	private array $error_str = [];
	/** @var \CoreLibs\Logging\Logging $log */
	public \CoreLibs\Logging\Logging $log;

	/** @var bool $log_error global flag to log error level message */
	private bool $log_error = false;

	/**
	 * init ErrorMessage
	 *
	 * @param  \CoreLibs\Logging\Logging $log
	 * @param  bool                      $log_error [=false]
	 */
	public function __construct(
		\CoreLibs\Logging\Logging $log,
		bool $log_error = false
	) {
		$this->log = $log;
		$this->log_error = $log_error;
	}

	/**
	 * pushes new error message into the error_str array
	 * error_id: internal Error ID (should be unique)
	 * level: error level, can only be ok, info, warn, error, abort, crash
	 *        ok and info are positive response: success
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
	 *
	 * @param  string        $error_id  Any internal error ID for this error
	 * @param  string        $level     Error level in ok/info/warn/error
	 * @param  string        $str       Error message (out)
	 * @param  string        $target    alternate attachment point for this error message
	 * @param  array<string> $highlight Any additional error data as error OR
	 *                                  highlight points for field highlights
	 * @param  string|null   $message   If abort/crash, non localized $str
	 * @param  array<mixed>  $context   Additionl info for abort/crash messages
	 * @param  bool|null     $log_error [=null] log level 'error' to error, if null use global,
	 *                                  else set for this call only
	 */
	public function setErrorMsg(
		string $error_id,
		string $level,
		string $str,
		string $target = '',
		array $highlight = [],
		?string $message = null,
		array $context = [],
		?bool $log_error = null,
	): void {
		if ($log_error === null) {
			$log_error = $this->log_error;
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
			'highlight' => $highlight,
		];
		// write to log for abort/crash
		switch ($level) {
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
	 * @param  string        $level     error level (ok/warn/info/error)
	 * @param  string        $str       error string
	 * @param  string|null   $error_id  optional error id for precise error lookup
	 * @param  string        $target    Alternate id name for output target on frontend
	 * @param  array<string> $highlight Any additional error data as error OR
	 *                                  highlight points for field highlights
	 * @param  string|null   $message   If abort/crash, non localized $str
	 * @param  array<mixed>  $context   Additionl info for abort/crash messages
	 * @param  bool|null     $log_error [=null] log level 'error' to error, if null use global,
	 *                                  else set for this call only
	 */
	public function setMessage(
		string $level,
		string $str,
		?string $error_id = null,
		string $target = '',
		array $highlight = [],
		?string $message = null,
		array $context = [],
		?bool $log_error = null,
	): void {
		$this->setErrorMsg($error_id ?? '', $level, $str, $target, $highlight, $message, $context, $log_error);
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
	 * @return array{id:string,level:string,str:string,target:string,highlight:string[]} Error block
	 */
	public function getLastErrorMsg(): array
	{
		return $this->error_str[array_key_last($this->error_str)] ?? [
			'level' => '',
			'str' => '',
			'id' => '',
			'target' => '',
			'highlight' => [],
		];
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
}

// __END__
