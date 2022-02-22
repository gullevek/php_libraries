<?php

/*
 * Debug support functions
 *
 * These are if there is any debug to print out at all at the end
　*	debug_output_all - general yes no
　* It's recommended to use the method "debug_for" to turn on of the array vars
　*	debug_output - turn on for one level (Array)
　*	debug_output_not - turn off for one level (array)
　*
　* Print out the debug at thend of the html
　*	echo_output_all
　*	echo_output
　*	echo_output_not
　*
　* Write debug to file
　*	print_output_all
　*	print_output
　*	print_output_not
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

use CoreLibs\Debug\Support;
use CoreLibs\Create\Hash;
use CoreLibs\Get\System;
use CoreLibs\Convert\Html;

class Logging
{
	// options
	/** @var array<mixed> */
	private $options = [];
	// page and host name
	/** @var string */
	private $page_name;
	/** @var string */
	private $host_name;
	/** @var int */
	private $host_port;
	// internal error reporting vars
	/** @var array<mixed> */
	private $error_msg = []; // the "connection" to the outside errors
	// debug output prefix
	/** @var string */
	private $error_msg_prefix = ''; // prefix to the error string (the class name)
	// debug flags
	/** @var array<mixed> */
	private $debug_output = []; // if this is true, show debug on desconstructor
	/** @var array<mixed> */
	private $debug_output_not = [];
	/** @var bool */
	private $debug_output_all = false;
	/** @var array<mixed> */
	private $echo_output = []; // errors: echo out, default is 1
	/** @var array<mixed> */
	private $echo_output_not = [];
	/** @var bool */
	private $echo_output_all = false;
	/** @var array<mixed> */
	private $print_output = []; // errors: print to file, default is 0
	/** @var array<mixed> */
	private $print_output_not = [];
	/** @var bool */
	private $print_output_all = false;
	// debug flags/settings
	/** @var string */
	private $running_uid = ''; // unique ID set on class init and used in logging as prefix
	// log file name
	/** @var string */
	private $log_folder = '';
	/** @var string */
	private $log_file_name_ext = 'log'; // use this for date rotate
	/** @var string */
	private $log_file_name = '';
	/** @var int */
	private $log_max_filesize = 0; // set in kilobytes
	/** @var string */
	private $log_print_file = 'error_msg##LOGID####LEVEL####CLASS####PAGENAME####DATE##';
	/** @var string */
	private $log_file_unique_id; // a unique ID set only once for call derived from this class
	/** @var string */
	private $log_file_date = ''; // Y-m-d file in file name
	/** @var bool */
	private $log_print_file_date = true; // if set add Y-m-d and do automatic daily rotation
	/** @var string */
	private $log_file_id = ''; // a alphanumeric name that has to be set as global definition
	/** @var bool */
	private $log_per_level = false; // set, it will split per level (first parameter in debug call)
	/** @var bool */
	private $log_per_class = false; // set, will split log per class
	/** @var bool */
	private $log_per_page = false; // set, will split log per called file
	/** @var bool */
	private $log_per_run = false; // create a new log file per run (time stamp + unique ID)
	// script running time
	/** @var float */
	private $script_starttime;

	/**
	 * Init logger
	 *
	 * global vars that can be used
	 * - BASE
	 * - LOG
	 * - LOG_FILE_ID
	 * options array layout
	 * - log_folder:
	 * - print_file_date:
	 * - file_id:
	 * - unique_id:
	 * - log_per_level:
	 * - log_per_class:
	 * - log_per_page:
	 * - log_per_run:
	 * - debug_all:
	 * - echo_all:
	 * - print_all:
	 * - debug (array):
	 * - echo (array):
	 * - print (array):
	 * - debug_not (array):
	 * - echo_not (array):
	 * - print_not (array):
	 *
	 * @param array<mixed> $options Array with settings options
	 */
	public function __construct(array $options = [])
	{
		// copy the options over
		$this->options = $options;
		// set log folder from options
		$this->log_folder = $this->options['log_folder'] ?? '';
		// legacy flow, check must set constants
		if (empty($this->log_folder) && defined('BASE') && defined('LOG')) {
			// make sure this is writeable, else skip
			$this->log_folder = BASE . LOG;
		}
		// fallback + notice
		if (empty($this->log_folder)) {
			/* trigger_error(
				'options or constant not set or folder not writable. fallback to: ' . getcwd(),
				E_USER_NOTICE
			); */
			$this->log_folder = getcwd() . DIRECTORY_SEPARATOR;
		}
		// if folder is not writeable, abort
		if (!is_writeable($this->log_folder)) {
			trigger_error(
				'Folder: ' . $this->log_folder . ' is not writeable for logging',
				E_USER_ERROR
			);
		}
		// check if log_folder has a trailing /
		if (substr($this->log_folder, -1, 1) != DIRECTORY_SEPARATOR) {
			$this->log_folder .= DIRECTORY_SEPARATOR;
		}
		// running time start for script
		$this->script_starttime = microtime(true);
		// set per run UID for logging
		$this->running_uid = Hash::__uniqId();
		// set the page name
		$this->page_name = System::getPageName();
		// set host name
		list($this->host_name , $this->host_port) = System::getHostName();
		// add port to host name if not port 80
		if ($this->host_port != 80) {
			$this->host_name .= ':' . $this->host_port;
		}

		// can be overridden with basicSetLogFileId later
		if (!empty($this->options['file_id'])) {
			$this->setLogId($this->options['file_id'] ?? '');
		} elseif (!empty($GLOBALS['LOG_FILE_ID'])) {
			// legacy flow, should be removed and only set via options
			$this->setLogId($GLOBALS['LOG_FILE_ID']);
		} elseif (defined('LOG_FILE_ID')) {
			// legacy flow, should be removed and only set via options
			$this->setLogId(LOG_FILE_ID);
		}

		// init the log levels
		$this->initLogLevels();
	}

	// *** PRIVATE ***

	/**
	 * init the basic log levels based on global set variables
	 *
	 * @return void
	 */
	private function initLogLevels(): void
	{
		// if given via parameters, only for all
		// globals overrule given settings, for one (array), eg $ECHO['db'] = 1;
		foreach (['debug', 'echo', 'print'] as $type) {
			// include or exclude (off) from output
			foreach (['on', 'off'] as $flag) {
				$in_type = $type;
				if ($flag == 'off') {
					$in_type .= '_not';
				}
				$up_type = strtoupper($in_type);
				if (
					isset($this->options[$in_type]) &&
					is_array($this->options[$in_type])
				) {
					$this->setLogLevel($type, $flag, $this->options[$in_type]);
				} elseif (
					isset($GLOBALS[$up_type]) &&
					is_array($GLOBALS[$up_type])
				) {
					$this->setLogLevel($type, $flag, $GLOBALS[$up_type]);
				}
			}
		}

		// all overrule
		$this->setLogLevelAll(
			'debug',
			$this->options['debug_all'] ??
				$GLOBALS['DEBUG_ALL'] ??
				false
		);
		$this->setLogLevelAll(
			'echo',
			$this->options['echo_all'] ??
				$GLOBALS['ECHO_ALL'] ??
				false
		);
		$this->setLogLevelAll(
			'print',
			$this->options['print_all'] ??
				$GLOBALS['PRINT_ALL'] ??
				false
		);

		// GLOBAL rules for log writing
		$this->setGetLogPrintFileDate(
			$this->options['print_file_date'] ??
				$GLOBALS['LOG_PRINT_FILE_DATE'] ??
				true
		);
		$this->setLogPer(
			'level',
			$this->options['per_level'] ??
				$GLOBALS['LOG_PER_LEVEL'] ??
				false
		);
		$this->setLogPer(
			'class',
			$this->options['per_class'] ??
				$GLOBALS['LOG_PER_CLASS'] ??
				false
		);
		$this->setLogPer(
			'page',
			$this->options['per_page'] ??
				$GLOBALS['LOG_PER_PAGE'] ??
				false
		);
		$this->setLogPer(
			'run',
			$this->options['per_run'] ??
				$GLOBALS['LOG_PER_RUN'] ??
				false
		);
		// set log per date
		if ($this->setGetLogPrintFileDate()) {
			$this->log_file_date = date('Y-m-d');
		}
		// set per run ID
		if ($this->log_per_run) {
			/* if (isset($GLOBALS['LOG_FILE_UNIQUE_ID'])) {
				$this->log_file_unique_id = $GLOBALS['LOG_FILE_UNIQUE_ID'];
			} */
			if (!$this->log_file_unique_id) {
				// $GLOBALS['LOG_FILE_UNIQUE_ID'] =
				$this->log_file_unique_id =
					date('Y-m-d_His') . '_U_'
						. substr(hash('sha1', uniqid((string)mt_rand(), true)), 0, 8);
			}
		}
	}

	/**
	 * checks if we have a need to work on certain debug output
	 * Needs debug/echo/print ad target for which of the debug flag groups we check
	 * also needs level string to check in the per level output flag check.
	 * In case we have invalid target it will return false
	 * @param  string $target target group to check debug/echo/print
	 * @param  string $level  level to check in detailed level flag
	 * @return bool           true on access allowed or false on no access
	 */
	private function doDebugTrigger(string $target, string $level): bool
	{
		$access = false;
		// check if we do debug, echo or print
		if (
			(
				$this->getLogLevel($target, 'on', $level) ||
				$this->getLogLevelAll($target)
			) &&
			!$this->getLogLevel($target, 'off', $level)
		) {
			$access = true;
		}
		return $access;
	}

	/**
	 * writes error msg data to file for current level
	 * @param  string $level        the level to write
	 * @param  string $error_string error string to write
	 * @return bool                 True if message written, FAlse if not
	 */
	private function writeErrorMsg(string $level, string $error_string): bool
	{
		// only write if write is requested
		if (
			!($this->doDebugTrigger('debug', $level) &&
			$this->doDebugTrigger('print', $level))
		) {
			return false;
		}

		// init base file path
		$fn = $this->log_folder . $this->log_print_file . '.' . $this->log_file_name_ext;
		// log ID prefix settings, if not valid, replace with empty
		if (!empty($this->log_file_id)) {
			$rpl_string = '_' . $this->log_file_id;
		} else {
			$rpl_string = '';
		}
		$fn = str_replace('##LOGID##', $rpl_string, $fn); // log id (like a log file prefix)

		if ($this->log_per_run) {
			$rpl_string = '_' . $this->log_file_unique_id; // add 8 char unique string
		} elseif ($this->setGetLogPrintFileDate()) {
			$rpl_string = '_' . $this->log_file_date; // add date to file
		} else {
			$rpl_string = '';
		}
		$fn = str_replace('##DATE##', $rpl_string, $fn); // create output filename

		$rpl_string = !$this->log_per_level ? '' : '_' . $level; // if request to write to one file
		$fn = str_replace('##LEVEL##', $rpl_string, $fn); // create output filename
		// set per class, but don't use get_class as we will only get self
		$rpl_string = !$this->log_per_class ? '' : '_'
			// set sub class settings
			. str_replace('\\', '-', Support::getCallerClass());
		$fn = str_replace('##CLASS##', $rpl_string, $fn); // create output filename

		// if request to write to one file
		$rpl_string = !$this->log_per_page ?
			'' :
			'_' . System::getPageName(System::NO_EXTENSION);
		$fn = str_replace('##PAGENAME##', $rpl_string, $fn); // create output filename

		// write to file
		// first check if max file size is is set and file is bigger
		if ($this->log_max_filesize > 0 && ((filesize($fn) / 1024) > $this->log_max_filesize)) {
			// for easy purpose, rename file only to attach timestamp, nur sequence numbering
			rename($fn, $fn . '.' . date("YmdHis"));
		}
		$this->log_file_name = $fn;
		$fp = fopen($this->log_file_name, 'a');
		if ($fp !== false) {
			fwrite($fp, $error_string);
			fclose($fp);
			return true;
		} else {
			echo "<!-- could not open file: " . $this->log_file_name . " //-->";
			return false;
		}
	}

	// *** PUBLIC ***

	/**
	 * Temporary method to read all class variables for testing purpose
	 * @param string $name
	 * @return mixed       can be anything, bool, string, int, array
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
	 * @param  string $string log file id string value
	 * @return string        returns the set log file id string
	 * @deprecated Use $log->setLogId()
	 */
	public function basicSetLogId(string $string): string
	{
		return $this->setLogId($string);
	}

	/**
	 * sets the internal log file prefix id
	 * string must be a alphanumeric string
	 * if non valid string is given it returns the previous set one only
	 * @param  string $string log file id string value
	 * @return string        returns the set log file id string
	 */
	public function setLogId(string $string): string
	{
		if (preg_match("/^[\w\-]+$/", $string)) {
			$this->log_file_id = $string;
		}
		return $this->log_file_id;
	}

	/**
	 * return current set log file id
	 * @return string
	 */
	public function getLogId(): string
	{
		return $this->log_file_id;
	}

	/**
	 * old name for setLogLevel
	 * @param  string $type  debug, echo, print
	 * @param  string $flag  on/off
	 *         array  $array of levels to turn on/off debug
	 * @return bool          Return false if type or flag is invalid
	 * @deprecated Use setLogLevel
	 */
	public function debugFor(string $type, string $flag): bool
	{
		/** @phan-suppress-next-line PhanTypeMismatchArgumentReal, PhanParamTooFew @phpstan-ignore-next-line */
		return $this->setLogLevel(...[func_get_args()]);
	}

	/**
	 * set log level settings for All types
	 * if invalid type, skip
	 * @param  string $type Type to get: debug, echo, print
	 * @param  bool   $set  True or False
	 * @return bool         Return false if type invalid
	 */
	public function setLogLevelAll(string $type, bool $set): bool
	{
		// skip set if not valid
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return false;
		}
		$this->{$type . '_output_all'} = $set;
		return true;
	}

	/**
	 * get the current log level setting for All level blocks
	 * @param  string $type Type to get: debug, echo, print
	 * @return bool         False on failure, or the boolean flag from the all var
	 */
	public function getLogLevelAll(string $type): bool
	{
		// type check for debug/echo/print
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return false;
		}
		return $this->{$type . '_output_all'};
	}

	/**
	 * passes list of level names, to turn on debug
	 * eg $foo->debugFor('print', 'on', ['LOG', 'DEBUG', 'INFO']);
	 * @param  string        $type     debug, echo, print
	 * @param  string        $flag     on/off
	 * @param  array<mixed>  $debug_on Array of levels to turn on/off debug
	 *                                 To turn off a level set 'Level' => false,
	 *                                 If not set, switches to on
	 * @return bool          Return false if type or flag invalid
	 *                       also false if debug array is empty
	 */
	public function setLogLevel(string $type, string $flag, array $debug_on): bool
	{
		// abort if not valid type
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return false;
		}
		// invalid flag type
		if (!in_array($flag, ['on', 'off'])) {
			return false;
		}
		if (count($debug_on) >= 1) {
			foreach ($debug_on as $level => $set) {
				$switch = $type . '_output' . ($flag == 'off' ? '_not' : '');
				if (!is_bool($set)) {
					$level = $set;
					$set = true;
				}
				$this->{$switch}[$level] = $set;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * return the log level for the array type normal and not (disable)
	 * @param  string      $type  debug, echo, print
	 * @param  string      $flag  on/off
	 * @param  string|null $level if not null then check if this array entry is set
	 *                            else return false
	 * @return bool|array<mixed>  if $level is null, return array, else boolean true/false
	 */
	public function getLogLevel(string $type, string $flag, ?string $level = null)
	{
		// abort if not valid type
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return false;
		}
		// invalid flag type
		if (!in_array($flag, ['on', 'off'])) {
			return false;
		}
		$switch = $type . '_output' . ($flag == 'off' ? '_not' : '');
		// log level direct check must be not null or not empty string
		if (!empty($level)) {
			return $this->{$switch}[$level] ?? false;
		}
		// array
		return $this->{$switch};
	}

	/**
	 * set flags for per log level type
	 * - level: set per sub group level
	 * - class: split by class
	 * - page: split per page called
	 * - run: for each run
	 * @param  string $type Type to get: level, class, page, run
	 * @param  bool   $set  True or False
	 * @return bool         Return false if type invalid
	 */
	public function setLogPer(string $type, bool $set): bool
	{
		if (!in_array($type, ['level', 'class', 'page', 'run'])) {
			return false;
		}
		$this->{'log_per_' . $type} = $set;
		return true;
	}

	/**
	 * return current set log per flag in bool
	 * @param  string $type Type to get: level, class, page, run
	 * @return bool         True of false for turned on or off
	 */
	public function getLogPer(string $type): bool
	{
		if (!in_array($type, ['level', 'class', 'page', 'run'])) {
			return false;
		}
		return $this->{'log_per_' . $type};
	}

	/**
	 * Set or get the log file date extension flag
	 * if null or empty parameter gets current flag
	 * @param boolean|null $set Set the date suffix for log files
	 *                          If set to null return current set
	 * @return boolean          Current set flag
	 */
	public function setGetLogPrintFileDate(?bool $set = null): bool
	{
		if ($set !== null) {
			$this->log_print_file_date = $set;
		}
		return $this->log_print_file_date;
	}

	/**
	 * Return current set log file name
	 * @return string Filename set set after the last time debug was called
	 */
	public function getLogFileName(): string
	{
		return $this->log_file_name;
	}

	/**
	 * A replacement for the \CoreLibs\Debug\Support::printAr
	 * But this does not wrap it in <pre></pre>
	 * It uses some special code sets so we can convert that to pre flags
	 * for echo output {##HTMLPRE##} ... {##/HTMLPRE##}
	 * Do not use this without using it in a string in debug function
	 * @param  array<mixed> $a Array to format
	 * @return string          print_r formated
	 */
	public function prAr(array $a): string
	{
		return '##HTMLPRE##' . print_r($a, true) . '##/HTMLPRE##';
	}

	/**
	 * write debug data to error_msg array
	 * @param  string $level  id for error message, groups messages together
	 * @param  string $string the actual error message
	 * @param  bool   $strip  default on false, if set to true,
	 *                        all html tags will be stripped and <br> changed to \n
	 *                        this is only used for debug output
	 * @param  string $prefix Attach some block before $string. Will not be stripped even
	 *                        when strip is true
	 *                        if strip is false, recommended to add that to $string
	 * @return bool           True if logged, false if not logged
	 */
	public function debug(
		string $level,
		string $string,
		bool $strip = false,
		string $prefix = ''
	): bool {
		$status = false;
		// must be debug on and either echo or print on
		if (
			!$this->doDebugTrigger('debug', $level) ||
			(
				// if debug is on, either print or echo must be set to on
				!$this->doDebugTrigger('print', $level) &&
				!$this->doDebugTrigger('echo', $level)
			)
		) {
			return $status;
		}
		// get the last class entry and wrie that
		$class = Support::getCallerClass();
		// get timestamp
		$timestamp = Support::printTime();
		// same string put for print (no html data inside)
		// write to file if set
		$status = $this->writeErrorMsg(
			$level,
			'[' . $timestamp . '] '
			. '[' . $this->host_name . '] '
			. '[' . System::getPageName(System::FULL_PATH) . '] '
			. '[' . $this->running_uid . '] '
			. '{' . $class . '} '
			. '<' . $level . '> - '
			// strip the htmlpre special tags if exist
			. str_replace(
				['##HTMLPRE##', '##/HTMLPRE##'],
				'',
				// if stripping all html, etc is requested, only for write error msg
				($strip ?
					// find any <br> and replace them with \n
					// strip rest of html elements (base only)
					preg_replace(
						"/(<\/?)(\w+)([^>]*>)/",
						'',
						str_replace('<br>', "\n", $prefix . $string)
					) :
					$prefix . $string
				) ?: ''
			)
			. "\n"
		);
		// write to error level msg array if there is an echo request
		if ($this->doDebugTrigger('echo', $level)) {
			// init if not set
			if (!isset($this->error_msg[$level])) {
				$this->error_msg[$level] = [];
			}
			// HTML string
			$this->error_msg[$level][] = '<div>'
				. '[<span style="font-weight: bold; color: #5e8600;">' . $timestamp . '</span>] '
				. '[<span style="font-weight: bold; color: #c56c00;">' . $level . '</span>] '
				. '[<span style="color: #b000ab;">' . $this->host_name . '</span>] '
				. '[<span style="color: #08b369;">' . $this->page_name . '</span>] '
				. '[<span style="color: #0062A2;">' . $this->running_uid . '</span>] '
				. '{<span style="font-style: italic; color: #928100;">' . $class . '</span>} - '
				// as is prefix, allow HTML
				. $prefix
				// we replace special HTMLPRE with <pre> entries
				. str_replace(
					['##HTMLPRE##', '##/HTMLPRE##'],
					['<pre>', '</pre>'],
					Html::htmlent($string)
				)
				. "</div><!--#BR#-->";
			$status = true;
		}
		return $status;
	}

	/**
	 * for ECHO ON only
	 * returns error data as string so it can be echoed out
	 * @param  string $header_prefix prefix string for header
	 * @return string                error msg for all levels
	 */
	public function printErrorMsg(string $header_prefix = ''): string
	{
		$string_output = '';
		// if not debug && echo on, do not return anything
		if (
			!$this->getLogLevelAll('debug') ||
			!$this->getLogLevelAll('echo')
		) {
			return $string_output;
		}
		if ($this->error_msg_prefix) {
			$header_prefix = $this->error_msg_prefix;
		}
		$script_end = microtime(true) - $this->script_starttime;
		foreach ($this->error_msg as $level => $temp_debug_output) {
			if ($this->doDebugTrigger('debug', $level)) {
				if ($this->doDebugTrigger('echo', $level)) {
					$string_output .= '<div style="font-size: 12px;">'
						. '[<span style="font-style: italic; color: #c56c00;">' . $level . '</span>] '
						. ($header_prefix ? "<b>**** " . Html::htmlent($header_prefix) . " ****</br>\n" : '')
						. '</div>'
						. join('', $temp_debug_output);
				} // echo it out
			} // do printout
		} // for each level
		// create the output wrapper around
		// so we have a nice formated output per class
		if ($string_output) {
			$string_prefix = '<div style="text-align: left; padding: 5px; font-size: 10px; '
				. 'font-family: sans-serif; border-top: 1px solid black; '
				. 'border-bottom: 1px solid black; margin: 10px 0 10px 0; '
				. 'background-color: white; color: black;">'
				. '<div style="font-size: 12px;">{<span style="font-style: italic; color: #928100;">'
				. Support::getCallerClass() . '</span>}</div>';
			$string_output = $string_prefix . $string_output
				. '<div><span style="font-style: italic; color: #108db3;">Script Run Time:</span> '
				. $script_end . '</div>'
				. '</div>';
		}
		// }
		return $string_output;
	}

	/**
	 * for ECHO ON only
	 * unsests the error message array
	 * can be used if writing is primary to file
	 * if no level given resets all
	 * @param  string $level optional level
	 * @return void          has no return
	 */
	public function resetErrorMsg(string $level = ''): void
	{
		if (!$level) {
			$this->error_msg = [];
		} elseif (isset($this->error_msg[$level])) {
			unset($this->error_msg[$level]);
		}
	}

	/**
	 * for ECHO ON only
	 * Get current error message array
	 * @return array<mixed> error messages collected
	 */
	public function getErrorMsg(): array
	{
		return $this->error_msg;
	}

	/**
	 * for ECHO ON only
	 * merges the given error array with the one from this class
	 * only merges visible ones
	 * @param  array<mixed> $error_msg error array
	 * @return void                    has no return
	 */
	public function mergeErrors(array $error_msg = []): void
	{
		array_push($this->error_msg, ...$error_msg);
	}
}

// __END__
