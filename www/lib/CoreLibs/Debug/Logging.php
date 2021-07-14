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

class Logging
{
	// page and host name
	private $page_name;
	private $host_name;
	private $host_port;
	// internal error reporting vars
	private $error_msg = []; // the "connection" to the outside errors
	// debug output prefix
	private $error_msg_prefix = ''; // prefix to the error string (the class name)
	// debug flags
	private $debug_output = []; // if this is true, show debug on desconstructor
	private $debug_output_not = [];
	private $debug_output_all = false;
	private $echo_output = []; // errors: echo out, default is 1
	private $echo_output_not = [];
	private $echo_output_all = false;
	private $print_output = []; // errors: print to file, default is 0
	private $print_output_not = [];
	private $print_output_all = false;
	// debug flags/settings
	private $running_uid = ''; // unique ID set on class init and used in logging as prefix
	// log file name
	private $log_folder = '';
	private $log_file_name_ext = 'log'; // use this for date rotate
	private $log_max_filesize = 0; // set in kilobytes
	private $log_print_file = 'error_msg##LOGID####LEVEL####CLASS####PAGENAME####DATE##';
	private $log_file_unique_id; // a unique ID set only once for call derived from this class
	private $log_print_file_date = true; // if set add Y-m-d and do automatic daily rotation
	private $log_file_id = ''; // a alphanumeric name that has to be set as global definition
	private $log_per_level = false; // set, it will split per level (first parameter in debug call)
	private $log_per_class = false; // set, will split log per class
	private $log_per_page = false; // set, will split log per called file
	private $log_per_run = false; // create a new log file per run (time stamp + unique ID)
	// script running time
	private $script_starttime;

	public function __construct()
	{
		// check must set constants
		if (defined('BASE') && defined('LOG')) {
			$this->log_folder = BASE . LOG;
		} else {
			// fallback + warning
			trigger_error('constant BASE or LOG are not defined, fallback to getcwd()', E_USER_WARNING);
			$this->log_folder = getcwd() . DS;
		}
		// running time start for script
		$this->script_starttime = microtime(true);
		// set per run UID for logging
		$this->running_uid = \CoreLibs\Create\Hash::__uniqId();
		// set the page name
		$this->page_name = \CoreLibs\Get\System::getPageName();
		// set host name
		list($this->host_name , $this->host_port) = \CoreLibs\Get\System::getHostName();
		// add port to host name if not port 80
		if ($this->host_port != 80) {
			$this->host_name .= ':' . $this->host_port;
		}
		// can be overridden with basicSetLogFileId
		if (isset($GLOBALS['LOG_FILE_ID'])) {
			$this->basicSetLogId($GLOBALS['LOG_FILE_ID']);
		} elseif (defined('LOG_FILE_ID')) {
			$this->basicSetLogId(LOG_FILE_ID);
		}

		// init the log levels
		$this->setLogLevels();
	}

	// *** PRIVATE ***

	/**
	 * init the basic log levels based on global set variables
	 *
	 * @return void
	 */
	private function setLogLevels(): void
	{
		// if given via parameters, only for all
		// globals overrule given settings, for one (array), eg $ECHO['db'] = 1;
		if (isset($GLOBALS['DEBUG']) && is_array($GLOBALS['DEBUG'])) {
			$this->debug_output = $GLOBALS['DEBUG'];
		}
		if (isset($GLOBALS['ECHO']) && is_array($GLOBALS['ECHO'])) {
			$this->echo_output = $GLOBALS['ECHO'];
		}
		if (isset($GLOBALS['PRINT']) && is_array($GLOBALS['PRINT'])) {
			$this->print_output = $GLOBALS['PRINT'];
		}

		// exclude these ones from output
		if (isset($GLOBALS['DEBUG_NOT']) && is_array($GLOBALS['DEBUG_NOT'])) {
			$this->debug_output_not = $GLOBALS['DEBUG_NOT'];
		}
		if (isset($GLOBALS['ECHO_NOT']) && is_array($GLOBALS['ECHO_NOT'])) {
			$this->echo_output_not = $GLOBALS['ECHO_NOT'];
		}
		if (isset($GLOBALS['PRINT_NOT']) && is_array($GLOBALS['PRINT_NOT'])) {
			$this->print_output_not = $GLOBALS['PRINT_NOT'];
		}

		// all overrule
		$this->debug_output_all = $GLOBALS['DEBUG_ALL'] ?? false;
		$this->echo_output_all = $GLOBALS['ECHO_ALL'] ?? false;
		$this->print_output_all = $GLOBALS['PRINT_ALL'] ?? false;

		// GLOBAL rules for log writing
		$this->log_print_file_date = $GLOBALS['LOG_PRINT_FILE_DATE'] ?? true;
		$this->log_per_level = $GLOBALS['LOG_PER_LEVEL'] ?? false;
		$this->log_per_class = $GLOBALS['LOG_PER_CLASS'] ?? false;
		$this->log_per_page = $GLOBALS['LOG_PER_PAGE'] ?? false;
		$this->log_per_run = $GLOBALS['LOG_PER_RUN'] ?? false;
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
		switch ($target) {
			case 'debug':
				if (
					(
						(isset($this->debug_output[$level]) && $this->debug_output[$level]) ||
						$this->debug_output_all
					) &&
					(!isset($this->debug_output_not[$level]) ||
						(isset($this->debug_output_not[$level]) && !$this->debug_output_not[$level])
					)
				) {
					$access = true;
				}
				break;
			case 'echo':
				if (
					(
						(isset($this->echo_output[$level]) && $this->echo_output[$level]) ||
						$this->echo_output_all
					) &&
					(!isset($this->echo_output_not[$level]) ||
						(isset($this->echo_output_not[$level]) && !$this->echo_output_not[$level])
					)
				) {
					$access = true;
				}
				break;
			case 'print':
				if (
					(
						(isset($this->print_output[$level]) && $this->print_output[$level]) ||
						$this->print_output_all
					) &&
					(!isset($this->print_output_not[$level]) ||
						(isset($this->print_output_not[$level]) && !$this->print_output_not[$level])
					)
				) {
					$access = true;
				}
				break;
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
		// replace all html tags
		// $error_string = preg_replace("/(<\/?)(\w+)([^>]*>)/", "##\\2##", $error_string);
		// $error_string = preg_replace("/(<\/?)(\w+)([^>]*>)/", "", $error_string);
		// replace special line break tag
		// $error_string = str_replace('<!--#BR#-->', "\n", $error_string);

		// init output variable
		$output = $error_string; // output formated error string to output file
		// init base file path
		$fn = $this->log_folder . $this->log_print_file . '.' . $this->log_file_name_ext;
		// log ID prefix settings, if not valid, replace with empty
		if (preg_match("/^[A-Za-z0-9]+$/", $this->log_file_id)) {
			$rpl_string = '_' . $this->log_file_id;
		} else {
			$rpl_string = '';
		}
		$fn = str_replace('##LOGID##', $rpl_string, $fn); // log id (like a log file prefix)

		if ($this->log_per_run) {
			if (isset($GLOBALS['LOG_FILE_UNIQUE_ID'])) {
				$this->log_file_unique_id = $GLOBALS['LOG_FILE_UNIQUE_ID'];
			}
			if (!$this->log_file_unique_id) {
				$GLOBALS['LOG_FILE_UNIQUE_ID'] = $this->log_file_unique_id =
					date('Y-m-d_His') . '_U_'
						. substr(hash('sha1', uniqid((string)mt_rand(), true)), 0, 8);
			}
			$rpl_string = '_' . $this->log_file_unique_id; // add 8 char unique string
		} else {
			$rpl_string = !$this->log_print_file_date ? '' : '_' . date('Y-m-d'); // add date to file
		}
		$fn = str_replace('##DATE##', $rpl_string, $fn); // create output filename

		$rpl_string = !$this->log_per_level ? '' : '_' . $level; // if request to write to one file
		$fn = str_replace('##LEVEL##', $rpl_string, $fn); // create output filename
		// set per class, but don't use get_class as we will only get self
		$rpl_string = !$this->log_per_class ? '' : '_'
			// set sub class settings
			. str_replace('\\', '-', \CoreLibs\Debug\Support::getCallerClass());
		$fn = str_replace('##CLASS##', $rpl_string, $fn); // create output filename

		// if request to write to one file
		$rpl_string = !$this->log_per_page ? '' : '_' . \CoreLibs\Get\System::getPageName(1);
		$fn = str_replace('##PAGENAME##', $rpl_string, $fn); // create output filename

		// write to file
		// first check if max file size is is set and file is bigger
		if ($this->log_max_filesize > 0 && ((filesize($fn) / 1024) > $this->log_max_filesize)) {
			// for easy purpose, rename file only to attach timestamp, nur sequence numbering
			rename($fn, $fn . '.' . date("YmdHis"));
		}
		$fp = fopen($fn, 'a');
		if ($fp !== false) {
			fwrite($fp, $output);
			fclose($fp);
		} else {
			echo "<!-- could not open file: $fn //-->";
		}
		return true;
	}

	/**
	 * Superseeded by \CoreLibs\Debug\Support::getCallerClass() calls
	 *
	 * @return string Class name where called
	 * @deprecated Use \CoreLibs\Debug\Support::getCallerClass() insteader
	 */
	private function getCallerClass(): string
	{
		// get the last class entry and wrie that
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) ?? [['class' => get_class($this)]];
		return end($backtrace)['class'];
	}

	// *** PUBLIC ***

	/**
	 * sets the internal log file prefix id
	 * string must be a alphanumeric string
	 * if non valid string is given it returns the previous set one only
	 * @param  string $string log file id string value
	 * @return string        returns the set log file id string
	 */
	public function basicSetLogId(string $string): string
	{
		if (preg_match("/^\w+$/", $string)) {
			$this->log_file_id = $string;
		}
		return $this->log_file_id;
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
	 * set log level settings for All types
	 * if invalid type, skip
	 * @param  string $type Type to get: debug, echo, print
	 * @param  bool   $set  True or False
	 * @return void         No return
	 */
	public function setLogLevelAll(string $type, bool $set): void
	{
		// skip set if not valid
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return;
		}
		$this->{$type . '_output_all'} = $set;
	}

	/**
	 * old name for setLogLevel
	 * @param  string $type  debug, echo, print
	 * @param  string $flag  on/off
	 *         array  $array of levels to turn on/off debug
	 * @return void          has no return
	 */
	public function debugFor(string $type, string $flag): void
	{
		/** @phan-suppress-next-line PhanTypeMismatchArgumentReal */
		$this->setLogLevel(...[func_get_args()]);
	}

	/**
	 * passes list of level names, to turn on debug
	 * eg $foo->debugFor('print', 'on', ['LOG', 'DEBUG', 'INFO']);
	 * TODO: currently we can only turn ON
	 * @param  string $type  debug, echo, print
	 * @param  string $flag  on/off
	 *         array  $array of levels to turn on/off debug
	 * @return void          has no return
	 */
	public function setLogLevel(string $type, string $flag): void
	{
		// abort if not valid type
		if (!in_array($type, ['debug', 'echo', 'print'])) {
			return;
		}
		// invalid flag type
		if (!in_array($flag, ['on', 'off'])) {
			return;
		}
		$debug_on = func_get_args();
		array_shift($debug_on); // kick out type
		array_shift($debug_on); // kick out flag (on/off)
		if (count($debug_on) >= 1) {
			foreach ($debug_on as $level) {
				$switch = $type . '_output' . ($flag == 'off' ? '_not' : '');
				$this->{$switch}[$level] = true;
			}
		}
	}

	/**
	 * return the log level for the array type normal and not (disable)
	 * @param  string      $type  debug, echo, print
	 * @param  string      $flag  on/off
	 * @param  string|null $level if not null then check if this array entry is set
	 *                            else return false
	 * @return bool|array         if $level is null, return array, else boolean true/false
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
		// bool
		if ($level !== null) {
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
	 * @return void
	 */
	public function setLogPer(string $type, bool $set): void
	{
		if (!in_array($type, ['level', 'class', 'page', 'run'])) {
			return;
		}
		$this->{'log_per_' . $type} = $set;
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
	 * A replacement for the \CoreLibs\Debug\Support::printAr
	 * But this does not wrap it in <pre></pre>
	 * It uses some special code sets so we can convert that to pre flags
	 * for echo output {##HTMLPRE##} ... {##/HTMLPRE##}
	 * Do not use this without using it in a string in debug function
	 * @param  array $a Array to format
	 * @return string   print_r formated
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
	public function debug(string $level, string $string, bool $strip = false, string $prefix = ''): bool
	{
		if (!$this->doDebugTrigger('debug', $level)) {
			return false;
		}
		// get the last class entry and wrie that
		$class = \CoreLibs\Debug\Support::getCallerClass();
		// get timestamp
		$timestamp = \CoreLibs\Debug\Support::printTime();
		// same string put for print (no html data inside)
		// write to file if set
		$this->writeErrorMsg(
			$level,
			'[' . $timestamp . '] '
			. '[' . $this->host_name . '] '
			. '[' . \CoreLibs\Get\System::getPageName(2) . '] '
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
					preg_replace("/(<\/?)(\w+)([^>]*>)/", '', str_replace('<br>', "\n", $prefix . $string)) :
					$prefix . $string
				)
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
					\CoreLibs\Convert\Html::htmlent($string)
				)
				. "</div><!--#BR#-->";
		}
		return true;
	}

	/**
	 * merges the given error array with the one from this class
	 * only merges visible ones
	 * @param  array  $error_msg error array
	 * @return void              has no return
	 */
	public function mergeErrors(array $error_msg = []): void
	{
		if (!is_array($error_msg)) {
			$error_msg = [];
		}
		array_push($this->error_msg, ...$error_msg);
	}

	/**
	 * prints out the error string
	 * @param  string $string prefix string for header
	 * @return string         error msg for all levels
	 */
	public function printErrorMsg(string $string = ''): string
	{
		$string_output = '';
		if ($this->debug_output_all) {
			if ($this->error_msg_prefix) {
				$string = $this->error_msg_prefix;
			}
			$script_end = microtime(true) - $this->script_starttime;
			foreach ($this->error_msg as $level => $temp_debug_output) {
				if ($this->doDebugTrigger('debug', $level)) {
					if ($this->doDebugTrigger('echo', $level)) {
						$string_output .= '<div style="font-size: 12px;">'
							. '[<span style="font-style: italic; color: #c56c00;">' . $level . '</span>] '
							. ($string ? "<b>**** " . \CoreLibs\Convert\Html::htmlent($string) . " ****</br>\n" : "")
							. '</div>'
							. join('', $temp_debug_output);
					} // echo it out
				} // do printout
			} // for each level
			// create the output wrapper around, so we have a nice formated output per class
			if ($string_output) {
				$string_prefix = '<div style="text-align: left; padding: 5px; font-size: 10px; '
					. 'font-family: sans-serif; border-top: 1px solid black; '
					. 'border-bottom: 1px solid black; margin: 10px 0 10px 0; '
					. 'background-color: white; color: black;">'
					. '<div style="font-size: 12px;">{<span style="font-style: italic; color: #928100;">'
					. \CoreLibs\Debug\Support::getCallerClass() . '</span>}</div>';
				$string_output = $string_prefix . $string_output
					. '<div><span style="font-style: italic; color: #108db3;">Script Run Time:</span> '
					. $script_end . '</div>'
					. '</div>';
			}
		}
		return $string_output;
	}

	/**
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
	 * Get current error message array
	 *
	 * @return array error messages collected
	 */
	public function getErrorMsg(): array
	{
		return $this->error_msg;
	}
}

// __END__
