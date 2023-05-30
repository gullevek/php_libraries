<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/5/26
 * DESCRIPTION:
 * Logging class
 *
 * NOTE: This is Logging2 for testing simpler build
*/

declare(strict_types=1);

namespace CoreLibs\Logging;

use Psr\Log\InvalidArgumentException;
use CoreLibs\Logging\Logger\Level;
use CoreLibs\Logging\Logger\Flag;
use CoreLibs\Debug\Support;
use CoreLibs\Create\Uids;
use CoreLibs\Get\System;
use Stringable;

class Logging
{
	/** @var int minimum size for a max file size, so we don't set 1 byte, 10kb */
	public const MIN_LOG_MAX_FILESIZE = 10 * 1024;

	// NOTE: the second party array{} hs some errors
	/** @var array<string,array<string,string|bool|Level>>|array{string:array{type:string,type_info?:string,mandatory:true,alias?:string,default:string|bool|Level,deprecated:bool,use?:string}} */
	private const OPTIONS = [
		'log_folder' => [
			'type' => 'string', 'mandatory' => true,
			'default' => '', 'deprecated' => false
		],
		'log_file_id' => [
			'type' => 'string', 'mandatory' => true, 'alias' => 'file_id',
			'default' => '', 'deprecated' => false
		],
		'file_id' => [
			'type' => 'string', 'mandatory' => false,
			'default' => '', 'deprecated' => true, 'use' => 'log_file_id'
		],
		'log_level' => [
			'type' => 'instance',
			'type_info' => '\CoreLibs\Logging\Logger\Level',
			'mandatory' => false,
			'default' => Level::Debug,
			'deprecated' => false
		],
		// options
		'log_per_run' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'log_per_date' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'log_per_group' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'log_per_page' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'log_per_class' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'log_per_level' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => false
		],
		'print_file_date' => [
			'type' => 'bool', 'mandatory' => false,
			'default' => false, 'deprecated' => true, 'use' => 'log_per_date'
		],
	];

	// options
	/** @var array<mixed> */
	private $options = [];

	/** @var Level set level */
	private $log_level;

	// page and host name
	/** @var string */
	private $host_name;
	/** @var int */
	private $host_port;
	/** @var string unique ID set on class init and used in logging as prefix */
	private $running_uid = '';

	// log file name
	/** @var string */
	private $log_folder = '';
	/** @var string a alphanumeric name that has to be set as global definition */
	private $log_file_id = '';
	/** @var string log file name extension */
	private $log_file_name_ext = 'log';
	/** @var string log file name with folder, for actual writing */
	private $log_file_name = '';
	/** @var int set in bytes */
	private $log_max_filesize = 0;
	/** @var string used if no log id set or found */
	private $log_file_prefix = 'error_msg';
	/** @var string */
	private $log_print_file = '{LOGID}{LEVEL}{GROUP}{CLASS}{PAGENAME}{DATE_RUNID}';
	/** @var string  a unique ID set only once for call derived from this class */
	private $log_file_unique_id = '';
	/** @var string Y-m-d file in file name */
	private $log_file_date = '';

	/**
	 *  1: create a new log file per run (time stamp + unique ID)
	 *  2: add Y-m-d and do automatic daily rotation
	 *  4: split per group (first parameter in debug call, group id, former level)
	 *  8: split log per called file
	 * 16: split log per class
	 * 32: split log per set log level
	 */
	/** @var int bitwise set for log flags */
	private $log_flags = 0;
	/** @var array<string,Flag> valid log flag names */
	private $log_valid_flags = [
		'log_per_run' => Flag::per_run,
		'log_per_date' => Flag::per_date,
		'log_per_group' => Flag::per_group,
		'log_per_page' => Flag::per_page,
		'log_per_class' => Flag::per_class,
		// not before it was PER_GROUP type
		'log_per_level' => Flag::per_level,
		// below are old & deprecated
		'print_file_date' => Flag::per_date,
	];

	/**
	 * Init logger
	 *
	 * options array layout
	 * - log_folder:
	 * - log_file_id / file_id (will be deprecated):
	 * - log_level:
	 *
	 * - log_per_run:
	 * - log_per_date: (was print_file_date)
	 * - log_per_group
	 * - log_per_page:
	 * - log_per_class:
	 * - log_per_level:
	 *
	 * @param array<mixed> $options Array with settings options
	 */
	public function __construct(array $options = [])
	{
		// options chekc
		// must set values
		// * path
		// * file id
		// * log level
		$this->optionsCheck($options);

		// set log level
		$this->initLogLevel();
		// set log folder from options
		$this->initLogFolder();
		// set per run UID for logging
		$this->running_uid = Uids::uniqIdShort();
		// set host name
		$this->initHostName();
		// set file id
		$this->initLogFileId();
		// set max file size for logging, 0 = no limit
		$this->setLogMaxFileSize($this->options['log_max_file_size'] ?? 0);
		// set flags and values needed for those flags
		$this->initLogFlagsAndValues();
	}

	// *********************************************************************
	// PRIVATE METHODS
	// *********************************************************************

	/**
	 * Undocumented function
	 *
	 * @param  array<mixed> $options
	 * @return bool
	 */
	private function optionsCheck(array $options): bool
	{
		// make sure only valid ones are in the options list,
		// drop all others
		// check for missing (trigger warning?)
		foreach (self::OPTIONS as $name => $settings) {
			// first deprecation warnings
			if (isset($options[$name]) && $settings['deprecated']) {
				trigger_error(
					'options: "' . $name . '" is deprecated use: "'
						. ($settings['use'] ?? 'NO_REPLACEMENT') . '".',
					E_USER_DEPRECATED
				);
			}
			// if mandatory and not set -> warning
			if (
				$settings['mandatory'] && !isset($options[$name]) &&
				empty($settings['alias'])
			) {
				throw new \InvalidArgumentException(
					'Missing mandatory option: "' . $name . '"',
					E_USER_WARNING
				);
			} elseif (
				// if not mandatory and not set -> default
				!$settings['mandatory'] && !isset($options[$name])
			) {
				$this->options[$name] = $settings['default'];
			} else {
				// else set from options
				$this->options[$name] = $options[$name] ?? $settings['default'];
			}
			// check valid type (only type not content)
			switch ($settings['type']) {
				case 'bool':
					if (!is_bool($this->options[$name])) {
						throw new \InvalidArgumentException(
							'Option: "' . $name . '" is not of type bool',
							E_USER_ERROR
						);
					}
					break;
				case 'string':
					if (!is_string($this->options[$name])) {
						throw new \InvalidArgumentException(
							'Option: "' . $name . '" is not of type string',
							E_USER_ERROR
						);
					}
					break;
				case 'instance':
					if (
						empty($settings['type_info']) ||
						!$this->options[$name] instanceof $settings['type_info']
					) {
						throw new \InvalidArgumentException(
							'Option: "' . $name . '" is not of instance '
								. ($settings['type_info'] ?? 'NO INSTANCE DEFINED'),
							E_USER_ERROR
						);
					}
					break;
			}
		}
		return true;
	}

	/**
	 * init log level, just a wrapper to auto set from options
	 *
	 * @return void
	 */
	private function initLogLevel()
	{
		// if this is not a valid instance of Level Enum then set to Debug
		if (
			empty($this->options['log_level']) ||
			!$this->options['log_level'] instanceof Level
		) {
			$this->options['log_level'] = Level::Debug;
		}
		$this->setLoggingLevel($this->options['log_level']);
	}

	/**
	 * Set the log folder
	 * If folder is not writeable the script will throw an E_USER_ERROR
	 *
	 * @return bool True on proper set, False on not proper set folder
	 */
	private function initLogFolder(): bool
	{
		$status = true;
		// set log folder from options
		$log_folder = $this->options['log_folder'] ?? '';
		// legacy flow, check must set constants
		if (empty($log_folder) && defined('BASE') && defined('LOG')) {
			/** @deprecated Do not use this anymore, define path on class load */
			trigger_error(
				'options: log_folder must be set. Setting via BASE and LOG constants is deprecated',
				E_USER_DEPRECATED
			);
			// make sure this is writeable, else skip
			$log_folder = BASE . LOG;
			$status = false;
		}
		// fallback + notice
		if (empty($log_folder)) {
			/* trigger_error(
				'option log_folder is empty. fallback to: ' . getcwd(),
				E_USER_NOTICE
			); */
			$log_folder = getcwd() . DIRECTORY_SEPARATOR;
			$status = false;
		}
		// if folder is not writeable, abort
		if (!$this->setLogFolder($log_folder)) {
			throw new InvalidArgumentException(
				'Folder: "' . $log_folder . '" is not writeable for logging',
				E_USER_ERROR
			);
		}
		return $status;
	}

	/**
	 * Set the hostname and port
	 * If port is not defaul 80 it will be added to the host name
	 *
	 * @return void
	 */
	private function initHostName(): void
	{
		// set host name
		list($this->host_name , $this->host_port) = System::getHostName();
		// add port to host name if not port 80
		if ($this->host_port != 80) {
			$this->host_name .= ':' . $this->host_port;
		}
	}

	/**
	 * set log file prefix id
	 *
	 * @return bool
	 */
	private function initLogFileId(): bool
	{
		$status = true;

		// alert of log_file_id and file_id is, log_file_id is prefered
		if (
			!empty($this->options['log_file_id']) &&
			!empty($this->options['file_id'])
		) {
			trigger_error(
				'options: both log_file_id and log_id are set at the same time, will use log_file_id',
				E_USER_WARNING
			);
			$this->options['log_file_id'] = $this->options['file_id'];
			unset($this->options['file_id']);
		}
		if (
			empty($this->options['log_file_id']) &&
			!empty($this->options['file_id'])
		) {
			// will trigger deprecation in future
			$this->options['log_file_id'] = $this->options['file_id'];
			unset($this->options['file_id']);
		}

		// can be overridden with basicSetLogFileId later
		if (!empty($this->options['log_file_id'])) {
			$this->setLogFileId($this->options['log_file_id']);
		} elseif (!empty($GLOBALS['LOG_FILE_ID'])) {
			/** @deprecated Do not use this anymore, define file_id on class load */
			trigger_error(
				'options: log_file_id must be set. Setting via LOG_FILE_ID global variable is deprecated',
				E_USER_DEPRECATED
			);
			$status = false;
			// legacy flow, should be removed and only set via options
			$this->setLogFileId($GLOBALS['LOG_FILE_ID']);
		} else {
			// auto set (should be deprecated in future)
			$this->setLogFileId(
				str_replace(':', '-', $this->host_name) . '_'
					. str_replace('\\', '-', Support::getCallerClass())
			);
		}
		if (empty($this->getLogFileId())) {
			throw new InvalidArgumentException(
				'LogFileId: no log file id set',
				E_USER_ERROR
			);
		}
		return $status;
	}

	/**
	 * set flags from options and option flags connection internal settings
	 *
	 * @return void
	 */
	private function initLogFlagsAndValues(): void
	{
		// first set all flags
		foreach ($this->log_valid_flags as $log_flag => $log_flag_key) {
			if (empty($this->options[$log_flag])) {
				continue;
			}
			$this->setLogFlag($log_flag_key);
		}
		// init per run uid
		if ($this->getLogFlag(Flag::per_run)) {
			$this->setLogUniqueId();
		} elseif ($this->getLogFlag(Flag::per_date)) {
			// init file date
			$this->log_file_date = date('Y-m-d');
		}
	}

	/**
	 * check if set log level is equal or higher than
	 * requested one
	 *
	 * @param  Level $level
	 * @return bool         True to allow write, False for not
	 */
	private function checkLogLevel(Level $level): bool
	{
		return $this->log_level->includes($level);
	}

	/**
	 * Build the file name for writing
	 *
	 * @param  Level  $level    Request log level
	 * @param  string $group_id Debug level group name id
	 * @return string
	 */
	private function buildLogFileName(Level $level, string $group_id = ''): string
	{
		// init base file path
		$fn = $this->log_print_file . '.' . $this->log_file_name_ext;
		// log ID prefix settings, if not valid, replace with empty
		if (!empty($this->log_file_id)) {
			$rpl_string = $this->log_file_id;
		} else {
			$rpl_string = $this->log_file_prefix;
		}
		$fn = str_replace('{LOGID}', $rpl_string, $fn); // log id (like a log file prefix)

		$rpl_string = !$this->getLogFlag(Flag::per_level) ? '' :
			'_' . $level->getName();
		$fn = str_replace('{LEVEL}', $rpl_string, $fn); // create output filename

		// write per level
		$rpl_string = !$this->getLogFlag(Flag::per_group) ? '' :
			// normalize level, replace all non alphanumeric characters with -
			'_' . (
				// if return is only - then set error string
				preg_match(
					"/^-+$/",
					$level_string = preg_replace("/[^A-Za-z0-9-_]/", '-', $group_id) ?? ''
				) ?
					'INVALID-LEVEL-STRING' :
					$level_string
			);
		$fn = str_replace('{GROUP}', $rpl_string, $fn); // create output filename
		// set per class, but don't use get_class as we will only get self
		$rpl_string = !$this->getLogFlag(Flag::per_class) ? '' : '_'
			// set sub class settings
			. str_replace('\\', '-', Support::getCallerClass());
		$fn = str_replace('{CLASS}', $rpl_string, $fn); // create output filename

		// if request to write to one file
		$rpl_string = !$this->getLogFlag(Flag::per_page) ?
			'' :
			'_' . System::getPageName(System::NO_EXTENSION);
		$fn = str_replace('{PAGENAME}', $rpl_string, $fn); // create output filename

		// if run id, we auto add ymd, so we ignore the log file date
		if ($this->getLogFlag(Flag::per_run)) {
			$rpl_string = '_' . $this->getLogUniqueId(); // add 8 char unique string
		} elseif ($this->getLogFlag(Flag::per_date)) {
			$rpl_string = '_' . $this->getLogDate(); // add date to file
		} else {
			$rpl_string = '';
		}
		$fn = str_replace('{DATE_RUNID}', $rpl_string, $fn); // create output filename
		$this->log_file_name = $fn;

		return $fn;
	}

	/**
	 * writes error msg data to file for current level
	 *
	 * @param  Level  $level    Log Level we wnat to write to
	 * @param  string|Stringable $message  Log message to write
	 * @param  string $group_id A group
	 * @return bool             True if message written, False if not
	 */
	private function writeErrorMsg(
		Level $level,
		string|\Stringable $message,
		string $group_id = ''
	): bool {
		// only write if write is requested
		if (!$this->checkLogLevel($level)) {
			return false;
		}

		// build logging file name
		// fn is log folder + file name
		$fn = $this->log_folder . $this->buildLogFileName($level, $group_id);

		// write to file
		// first check if max file size is is set and file is bigger
		if (
			$this->log_max_filesize > 0 &&
			(filesize($fn) > $this->log_max_filesize)
		) {
			// for easy purpose, rename file only to attach timestamp, no sequence numbering
			rename($fn, $fn . '.' . date("YmdHis"));
		}
		$fp = fopen($fn, 'a');
		if ($fp === false) {
			echo "<!-- could not open file for writing //-->";
			return false;
		}
		fwrite($fp, $message . "\n");
		fclose($fp);
		return true;
	}

	/**
	 * Prepare the log message with all needed info blocks:
	 * [timestamp] [host name] [file path + file] [running uid] {class} <debug level/group id> - message
	 *
	 * @param  string  $level_str    Log level we will write to
	 * @param  string|Stringable $message  The message to write
	 * @param  mixed[] $context Any additional info we want to attach in any format
	 * @param  string $group_id A group id, only used in DEBUG level,
	 *                          if empty set to log level
	 * @return string
	 */
	private function prepareLog(
		string $level_str,
		string|\Stringable $message,
		array $context = [],
		string $group_id = '',
	): string {
		// get the last class entry and wrie that
		$class = Support::getCallerClass();
		// get timestamp
		$timestamp = Support::printTime();

		// if group id is empty replace it with current level
		$group_str = $level_str;
		if (!empty($group_id)) {
			$group_str .= ':' . $group_id;
		}
		// additional context
		$context_str = '';
		if ($context != []) {
			// TODO this here has to be changed to something better
			$context_str = ' ' . print_r($context, true);
		}
		// build log string
		return '[' . $timestamp . '] '
			. '[' . $this->host_name . '] '
			. '[' . System::getPageName(System::FULL_PATH) . '] '
			. '[' . $this->running_uid . '] '
			. '{' . $class . '} '
			. '<' . strtoupper($group_str) . '> '
			. $message
			. $context_str;
	}

	// *********************************************************************
	// PUBLIC STATIC METHJODS
	// *********************************************************************

	/**
	 * set the log level
	 *
	 * from Monolog\Logger
	 *
	 * @param  string|int|Level $level
	 * @return Level
	 */
	public static function processLogLevel(string|int|Level $level): Level
	{
		if ($level instanceof Level) {
			return $level;
		}

		if (\is_string($level)) {
			if (\is_numeric($level)) {
				$levelEnum = Level::tryFrom((int)$level);
				if ($levelEnum === null) {
					throw new InvalidArgumentException(
						'Level "' . $level . '" is not defined, use one of: '
						/** @phan-suppress-next-line PhanUselessBinaryAddRight */
							. implode(', ', Level::NAMES + Level::VALUES)
					);
				}

				return $levelEnum;
			}

			// Contains first char of all log levels and avoids using strtoupper() which may have
			// strange results depending on locale (for example, "i" will become "İ" in Turkish locale)
			$upper = strtr(substr($level, 0, 1), 'dinweca', 'DINWECA')
				. strtolower(substr($level, 1));
			if (defined(Level::class . '::' . $upper)) {
				return constant(Level::class . '::' . $upper);
			}

			throw new InvalidArgumentException(
				'Level "' . $level . '" is not defined, use one of: '
				/** @phan-suppress-next-line PhanUselessBinaryAddRight */
					. implode(', ', Level::NAMES + Level::VALUES)
			);
		}

		$levelEnum = Level::tryFrom($level);
		if ($levelEnum === null) {
			throw new InvalidArgumentException(
				'Level "' . var_export($level, true) . '" is not defined, use one of: '
				/** @phan-suppress-next-line PhanUselessBinaryAddRight */
					. implode(', ', Level::NAMES + Level::VALUES)
			);
		}

		return $levelEnum;
	}

	// *********************************************************************
	// PUBLIC METHODS
	// *********************************************************************

	// **** GET/SETTER

	// log level set and get

	/**
	 * set new log level
	 *
	 * @param  string|int|Level $level
	 * @return void
	 */
	public function setLoggingLevel(string|int|Level $level): void
	{
		$this->log_level = $this->processLogLevel($level);
	}

	/**
	 * return current set log level
	 *
	 * @return Level
	 */
	public function getLoggingLevel(): Level
	{
		return $this->log_level;
	}

	/**
	 * this is for older JS_DEBUG flags
	 *
	 * @return bool True, we are at debug level
	 */
	public function getJsDebug(): bool
	{
		return $this->log_level === Level::Debug ? true : false;
	}

	// log file id set (file name prefix)

	/**
	 * sets the internal log file prefix id
	 * string must be a alphanumeric string
	 *
	 * @param  string $string log file id string value
	 * @return bool
	 */
	public function setLogFileId(string $string): bool
	{
		if (!preg_match("/^[\w\.\-]+$/", $string)) {
			return false;
		}
		$this->log_file_id = $string;
		return true;
	}

	/**
	 * return current set log file id
	 *
	 * @return string
	 */
	public function getLogFileId(): string
	{
		return $this->log_file_id;
	}

	// log unique id set (for per run)

	/**
	 * Sets a unique id based on current date (y/m/d, h:i:s) and a unique id (8 chars)
	 * if override is set to true it will be newly set, else if already set nothing changes
	 *
	 * @param  bool $override True to force new set
	 * @return void
	 */
	public function setLogUniqueId(bool $override = false): void
	{
		if (empty($this->log_file_unique_id) || $override == true) {
			$this->log_file_unique_id =
				date('Y-m-d_His') . '_U_'
					. substr(hash('sha1', uniqid((string)mt_rand(), true)), 0, 8);
		}
	}

	/**
	 * Return current set log file unique id,
	 * empty string for not set
	 *
	 * @return string
	 */
	public function getLogUniqueId(): string
	{
		return $this->log_file_unique_id;
	}

	// general log date

	/**
	 * set the log file date to Y-m-d
	 * must be set if log_per_date is set
	 *
	 * @return void
	 */
	public function setLogDate(): void
	{
		$this->log_file_date = date('Y-m-d');
	}

	/**
	 * get the current set log file_date
	 *
	 * @return string
	 */
	public function getLogDate(): string
	{
		return $this->log_file_date;
	}

	// general flag set

	/**
	 * set one of the basic flags
	 *
	 * @param  Flag  $flag flag level to set
	 * @return void
	 */
	public function setLogFlag(Flag $flag): void
	{
		$this->log_flags |= $flag->value;
	}

	/**
	 * unset given from the log flags
	 *
	 * @param  Flag  $flag flag level to unset
	 * @return void
	 */
	public function unsetLogFlag(Flag $flag): void
	{
		$this->log_flags &= ~$flag->value;
	}

	/**
	 * check if a given flag is set
	 *
	 * @param  Flag  $flag
	 * @return bool
	 */
	public function getLogFlag(Flag $flag): bool
	{
		if ($this->log_flags & $flag->value) {
			return true;
		}
		return false;
	}

	/**
	 * Return all set log flags as int
	 *
	 * @return int
	 */
	public function getLogFlags(): int
	{
		return $this->log_flags;
	}

	// log folder/file

	/**
	 * set new log folder, check that folder is writeable
	 * If not setable keep older log folder setting
	 *
	 * @param  string $log_folder Folder to set
	 * @return bool               If not setable, return false
	 */
	public function setLogFolder(string $log_folder): bool
	{
		if (!is_writeable($log_folder)) {
			return false;
		}
		// check if log_folder has a trailing /
		if (substr($log_folder, -1, 1) != DIRECTORY_SEPARATOR) {
			$log_folder .= DIRECTORY_SEPARATOR;
		}
		$this->log_folder = $log_folder;
		return true;
	}

	/**
	 * get current set log folder
	 *
	 * @return string
	 */
	public function getLogFolder(): string
	{
		return $this->log_folder;
	}

	// note that set log filder is dynamic during log write

	/**
	 * get last set log file name
	 *
	 * @return string
	 */
	public function getLogFile(): string
	{
		return $this->log_file_name;
	}

	// max log file size

	/**
	 * set mag log file size
	 *
	 * @param  int  $file_size Set max file size in bytes, 0 for no limit
	 * @return bool False for invalid number
	 */
	public function setLogMaxFileSize(int $file_size): bool
	{
		if ($file_size < 0) {
			return false;
		}
		if ($file_size < self::MIN_LOG_MAX_FILESIZE) {
			return false;
		}
		$this->log_max_filesize = $file_size;
		return true;
	}

	/**
	 * Return current set log max file size in bytes
	 *
	 * @return int Max size in bytes, 0 for no limit
	 */
	public function getLogMaxFileSize(): int
	{
		return $this->log_max_filesize;
	}

	// *********************************************************************
	// OPTIONS CALLS
	// *********************************************************************

	/**
	 * get option
	 *
	 * @param  string               $option_key Which option key to search
	 * @return string|bool|int|null             Returns null on not found
	 */
	public function getOption(string $option_key): string|bool|int|null
	{
		return $this->options[$option_key] ?? null;
	}

	// *********************************************************************
	// MAIN CALLS
	// *********************************************************************

	/**
	 * DEBUG: 100
	 *
	 * write debug data to error_msg array
	 *
	 * @param  string $group_id id for error message, groups messages together
	 * @param  string|Stringable $message  the actual error message
	 * @param  string $prefix   Attach some block before $string.
	 *                          Will not be stripped even
	 *                          when strip is true
	 *                          if strip is false, recommended to add that to $string
	 * @param  mixed[] $context
	 * @return bool             True if logged, false if not logged
	 */
	public function debug(
		string $group_id,
		string|\Stringable $message,
		string $prefix = '',
		array $context = []
	): bool {
		return $this->writeErrorMsg(
			$this->log_level,
			$this->prepareLog(
				Level::Debug->getName(),
				$prefix . $message,
				$context,
				$group_id
			),
			$group_id
		);
	}

	/**
	 * INFO: 200
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function info(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Info,
			$this->prepareLog(
				Level::Info->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * NOTICE: 250
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function notice(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Notice,
			$this->prepareLog(
				Level::Notice->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * WARNING: 300
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function warning(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Warning,
			$this->prepareLog(
				Level::Warning->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * ERROR: 400
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function error(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Error,
			$this->prepareLog(
				Level::Error->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * CTRITICAL: 500
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function critical(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Critical,
			$this->prepareLog(
				Level::Critical->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * ALERT: 550
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function alert(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Alert,
			$this->prepareLog(
				Level::Alert->getName(),
				$message,
				$context,
			)
		);
	}

	/**
	 * EMERGENCY: 600
	 *
	 * @param  string|Stringable $message
	 * @param  mixed[] $context
	 * @return bool
	 */
	public function emergency(string|\Stringable $message, array $context = []): bool
	{
		return $this->writeErrorMsg(
			Level::Emergency,
			$this->prepareLog(
				Level::Emergency->getName(),
				$message,
				$context,
			)
		);
	}

	// *********************************************************************
	// DEPRECATED SUPPORT CALLS
	// *********************************************************************

	// legacy, but there are too many implemented

	/**
	 * A replacement for the \CoreLibs\Debug\Support::printAr
	 * But this does not wrap it in <pre></pre>
	 * Do not use this without using it in a string in debug function
	 *
	 * @param  array<mixed> $a Array to format
	 * @return string          print_r formated
	 */
	public function prAr(array $a): string
	{
		return Support::printArray($a, true);
	}

	/**
	 * Convert bool value to string value
	 *
	 * @param  bool   $bool  Bool value to be transformed
	 * @param  string $true  Override default string 'true'
	 * @param  string $false Override default string 'false'
	 * @return string        $true or $false string for true/false bool
	 */
	public function prBl(
		bool $bool,
		string $true = 'true',
		string $false = 'false'
	): string {
		return Support::printBool($bool, '', $true, $false, true);
	}

	/**
	 * Dump data as string without html
	 *
	 * @param  mixed  $data  Any data
	 * @param  bool   $strip If false, do not strip, default is true
	 * @return string        Output data for debug
	 */
	public function dV(mixed $data, bool $strip = true): string
	{
		return Support::dumpVar($data, $strip);
	}

	/**
	 * Export var data to string
	 *
	 * @param  mixed  $data
	 * @return string
	 */
	public function eV(mixed $data): string
	{
		return Support::exportVar($data, true);
	}

	// *********************************************************************
	// DEPRECATED METHODS
	// *********************************************************************

	/**
	 * Everything below here is deprecated and will be removed
	 */

	/**
	 * Temporary method to read all class variables for testing purpose
	 *
	 * @param  string $name what variable to return
	 * @return mixed        can be anything, bool, string, int, array
	 * @deprecated Use either log->getOption or log->get[Content] to fetch info
	 */
	public function getSetting(string $name): mixed
	{
		// for debug purpose only
		return $this->{$name};
	}

	/**
	 * sets the internal log file prefix id
	 * string must be a alphanumeric string
	 * if non valid string is given it returns the previous set one only
	 *
	 * @param  string $string log file id string value
	 * @return string        returns the set log file id string
	 * @deprecated Use Debug\Logging->setLogId() and Debug\Logging->getLogId()
	 */
	public function basicSetLogId(string $string): string
	{
		$this->setLogFileId($string);
		return $this->getLogFileId();
	}

	/**
	 * old name for setLogLevel
	 *
	 * @param  string $type  debug, echo, print
	 * @param  string $flag  on/off
	 *         array  $array of levels to turn on/off debug
	 * @return bool          Return false if type or flag is invalid
	 * @deprecated Use setLogLevel
	 */
	public function debugFor(string $type, string $flag): bool
	{
		trigger_error(
			'debugFor() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * set log level settings for All types
	 * if invalid type, skip
	 *
	 * @param  string $type Type to get: debug, echo, print
	 * @param  bool   $set  True or False
	 * @return bool         Return false if type invalid
	 * @deprecated Log levels (group id) are no longer supported
	 */
	public function setLogLevelAll(string $type, bool $set): bool
	{
		trigger_error(
			'setLogLevelAll() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * get the current log level setting for All level blocks
	 *
	 * @param  string $type Type to get: debug, echo, print
	 * @return bool         False on failure, or the boolean flag from the all var
	 * @deprecated Log levels (group id) are no longer supported
	 */
	public function getLogLevelAll(string $type): bool
	{
		trigger_error(
			'getLogLevelAll() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * passes list of level names, to turn on debug
	 * eg $foo->debugFor('print', 'on', ['LOG', 'DEBUG', 'INFO']);
	 *
	 * @param  string        $type     debug, echo, print
	 * @param  string        $flag     on/off
	 * @param  array<mixed>  $debug_on Array of levels to turn on/off debug
	 *                                 To turn off a level set 'Level' => false,
	 *                                 If not set, switches to on
	 * @return bool          Return false if type or flag invalid
	 *                       also false if debug array is empty
	 * @deprecated Log levels (group id) are no longer supported
	 */
	public function setLogLevel(string $type, string $flag, array $debug_on): bool
	{
		trigger_error(
			'setLogLevel() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * return the log level for the array type normal and not (disable)
	 *
	 * @param  string      $type  debug, echo, print
	 * @param  string      $flag  on/off
	 * @param  string|null $level if not null then check if this array entry is set
	 *                            else return false
	 * @return array<mixed>|bool  if $level is null, return array, else boolean true/false
	 * @deprecated Log levels (group id) are no longer supported
	 */
	public function getLogLevel(string $type, string $flag, ?string $level = null): array|bool
	{
		trigger_error(
			'getLogLevel() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * set flags for per log level type
	 * - level: set per sub group level
	 * - class: split by class
	 * - page: split per page called
	 * - run: for each run
	 *
	 * @param  string $type Type to get: level, class, page, run
	 * @param  bool   $set  True or False
	 * @return bool         Return false if type invalid
	 * @deprecated Set flags with setLogFlag
	 */
	public function setLogPer(string $type, bool $set): bool
	{
		trigger_error(
			'setLogPer() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	/**
	 * return current set log per flag in bool
	 *
	 * @param  string $type Type to get: level, class, page, run
	 * @return bool         True of false for turned on or off
	 * @deprecated Get flags with setLogFlag
	 */
	public function getLogPer(string $type): bool
	{
		trigger_error(
			'getLogPer() is deprecated',
			E_USER_DEPRECATED
		);
		return false;
	}

	// *********************************************************************
	// DEBUG METHODS
	// *********************************************************************

	/**
	 * only for debug
	 *
	 * @return void
	 */
	public function logger2Debug(): void
	{
		print "Options: " . Support::dumpVar($this->options) . "<br>";
		print "OPT set level: " . $this->getLoggingLevel()->getName() . "<br>";
		$this->setLoggingLevel(Level::Info);
		print "NEW set level: " . $this->getLoggingLevel()->getName() . "<br>";
		foreach (
			[
				Level::Debug, Level::Info, Level::Notice, Level::Warning,
				Level::Error, Level::Critical, Level::Alert, Level::Emergency
			] as $l
		) {
			if ($this->log_level->isHigherThan($l)) {
				print "L: " . $this->log_level->getName() . " > " .  $l->getName() . "<br>";
			}
			if ($this->log_level->includes($l)) {
				print "L: " . $this->log_level->getName() . " <= " .  $l->getName() . "<br>";
			}
			if ($this->log_level->isLowerThan($l)) {
				print "L: " . $this->log_level->getName() . " < " .  $l->getName() . "<br>";
			}
			echo "<br>";
		}
		// back to options level
		$this->initLogLevel();
		print "OPT set level: " . $this->getLoggingLevel()->getName() . "<br>";
	}
}

// __END__
