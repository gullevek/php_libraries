<?php

// phpcs:disable Generic.Files.LineLength

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/03/24
* VERSION: 5.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTION:
*   2021/6/7, transfer all methods in this class to their own classes
*   so we can use them independent without always using the full class
*   2018/3/23, the whole class system is transformed to namespaces
*   also all internal class calls are converted to camel case
*
*   basic class start class for ALL clases, holds basic vars, infos, methods, etc
*
* HISTORY:
* 2021/06/16 (cs) CLASS MOSTLY DEPRECATED: moved all methids in their own classes
* 2010/12/24 (cs) add crypt classes with auto detect what crypt we can use, add php version check class
* 2008/08/07 (cs) fixed strange DEBUG_ALL on off behavour. data was written even thought
*                 DBEUG_ALL was off. now no debug logging is done at all if DEBUG_ALL is off
* 2007/11/13 (cs) add Comparedate function
* 2007/11/05 (cs) added GenAssocArray and CheckDate functions
* 2007/10/10 (cs) magic links function can use http:///path as a local prefix.
*                 blank target is removed & http:// also
* 2006/03/09 (cs) added Byte/TimeStringFormat functions
* 2006/02/21 (cs) fix various problems with the mime magic function: || not always working,
*                 fix prefix replacement, etc
* 2006/02/09 (cs) added _mb_mime_encode function, replacement for php internal one
* 2005/07/12 (cs) added some small stylesheet defs to debug output
* 2005/06/24 (cs) made the check selected/checked function way easier
* 2005/06/24 (cs) added a function to wrap around print_r for html formatted array print
* 2005/06/21 (cs) made the error_msg file writing immediatly after something is written with debug method
* 2005/06/20 (cs) added a quick to file write function, removed the mobile detect code
* 2005/06/20 (cs) test debug method, add surpress of <br> in debug output
* 2005/06/17 (cs) error_msg is an array, to put in various levels of error reporting
* 2005/04/06 (cs) added filename for error page when print to file
* 2005/05/31 (cs) added file printout of errors
* 2005/03/01 (cs) set a global regex for checking the email
* 2005/01/27 (cs) updated checked, haystack can be valur or array
* 2004/11/16 (cs) removed mobile detection here
* 2004/11/15 (cs) error_msg is no longer echoed, but returned
* 2004/11/15 (cs) added new functions: checked, magic_links, get_page_name
* 2004/08/06 (cs) bug with $_GLOBALS, should be $GLOBALS
* 2004/07/15 (cs) added print_error_msg method, updated to new schema
* 2003-06-09: added "detect_mobile" class for japanese mobile phone
* detection
* 2003-03-24: start of stub/basic class
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs;

/** Basic core class declaration */
class Basic
{
	// page and host name
	/** @var string */
	public string $page_name;
	/** @var string */
	public string $host_name;
	/** @var int */
	public int $host_port;
	// logging interface, Debug\Logging class
	/** @var \CoreLibs\Logging\Logging */
	public \CoreLibs\Logging\Logging $log;
	/** @var \CoreLibs\Create\Session */
	public \CoreLibs\Create\Session $session;

	// email valid checks
	/** @var array<mixed> */
	public array $email_regex_check = [];
	/** @var string */
	public string $email_regex; // regex var for email check

	// data path for files
	/** @var array<mixed> */
	public array $data_path = [];

	// ajax flag
	/** @var bool */
	protected bool $ajax_page_flag = false;

	/**
	 * main Basic constructor to init and check base settings
	 * @param \CoreLibs\Logging\Logging|null $log Logging class
	 * @param string|null $session_name Set session name
	 * @deprecated DO NOT USE Class\Basic anymore. Use dedicated logger and sub classes
	 */
	public function __construct(
		?\CoreLibs\Logging\Logging $log = null,
		?string $session_name = null
	) {
		trigger_error('Class \CoreLibs\Basic is deprected', E_USER_DEPRECATED);
		// TODO make check dynamic for entries we MUST have depending on load type
		// before we start any work, we should check that all MUST constants are defined
		$abort = false;
		foreach (
			[
				'DS', 'DIR', 'BASE', 'ROOT', 'LIB', 'INCLUDES', 'LAYOUT', 'PICTURES', 'DATA',
				'VIDEOS', 'DOCUMENTS', 'PDFS', 'BINARIES', 'ICONS', 'UPLOADS', 'CSV', 'JS',
				'CSS', 'TABLE_ARRAYS', 'SMARTY', 'LANG', 'CACHE', 'TMP', 'LOG', 'TEMPLATES',
				'TEMPLATES_C', 'DEFAULT_LANG', 'DEFAULT_ENCODING', 'DEFAULT_HASH',
				'DB_CONFIG_NAME', 'DB_CONFIG', 'TARGET'
			] as $constant
		) {
			if (!defined($constant)) {
				echo "Constant $constant misssing<br>";
				$abort = true;
			}
		}
		if ($abort === true) {
			die('Core Constant missing. Check config file.');
		}

		// logging interface moved here (->debug is now ->log->debug)
		$this->log = $log ?? new \CoreLibs\Logging\Logging([
			'log_folder' => BASE . LOG,
			'log_file_id' => 'ClassBasic-DEPRECATED',
		]);

		// set ajax page flag based on the AJAX_PAGE varaibles
		// convert to true/false so if AJAX_PAGE is 0 or false it is
		// always boolean false
		$this->ajax_page_flag = isset($GLOBALS['AJAX_PAGE']) && $GLOBALS['AJAX_PAGE'] ? true : false;

		// set the paths matching to the valid file types
		$this->data_path = [
			'P' => PICTURES,
			'F' => DATA,
			'V' => VIDEOS,
			'D' => DOCUMENTS,
			'A' => PDFS,
			'B' => BINARIES
		];

		// set the page name
		$this->page_name = \CoreLibs\Get\System::getPageName();
		// set host name
		list($this->host_name , $this->host_port) = \CoreLibs\Get\System::getHostName();

		// set the regex for checking emails
		/** @deprecated */
		$this->email_regex = \CoreLibs\Check\Email::getEmailRegex();
		// this is for error check parts in where the email regex failed
		/** @deprecated */
		$this->email_regex_check = \CoreLibs\Check\Email::getEmailRegexCheck();

		// initial the session if there is no session running already
		$this->session = new \CoreLibs\Create\Session($session_name ?? '');
	}

	/**
	 * deconstructor
	 * basic deconstructor (should be called from all deconstructors in higher classes)
	 * writes out $error_msg to global var
	 */
	public function __destruct()
	{
	}

	// *************************************************************
	// GENERAL METHODS
	// *************************************************************

	/**
	 * sets the internal log file prefix id
	 * string must be a alphanumeric string
	 * if non valid string is given it returns the previous set one only
	 * @param  string $string log file id string value
	 * @return string        returns the set log file id string
	 * @deprecated Use $basic->log->basicSetLogId() instead
	 */
	public function basicSetLogId(string $string): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use log->setLogId() or use \CoreLibs\Logging\Logging() class', E_USER_DEPRECATED);
		$this->log->setLogFileId($string);
		return $this->log->getLogFileId();
	}

	// ****** DEBUG/ERROR FUNCTIONS ******

	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Debug\RunningTime

	/**
	 * for messure run time between two calls for this method
	 * uses the hrtime() for running time
	 * first call sets start time and returns 0,
	 * second call sets end time and returns the run time
	 * the out_time parameter can be:
	 * n/ns (nano), y/ys (micro), m/ms (milli), s
	 * default is milliseconds
	 * @param  string $out_time set return time adjustment calculation
	 * @return float            running time without out_time suffix
	 * @deprecated Use \CoreLibs\Debug\RunningTime::hrRunningTime() instead
	 */
	public function hrRunningTime(string $out_time = 'ms'): float
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Debug\RunningTime::hrRunningTime()', E_USER_DEPRECATED);
		return \CoreLibs\Debug\RunningTime::hrRunningTime($out_time);
	}

	/**
	 * prints start or end time in text format. On first call sets start time
	 * on second call it sends the end time and then also prints the running time
	 * Sets the internal runningtime_string variable with Start/End/Run time string
	 * NOTE: for pure running time check it is recommended to use hrRunningTime method
	 * @param  bool|boolean $simple     if true prints HTML strings, default text only
	 * @return float                    running time as float number
	 * @deprecated Use \CoreLibs\Debug\RunningTime::runningTime() instead
	 */
	public function runningTime(bool $simple = false): float
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Debug\RunningTime::runningTime()', E_USER_DEPRECATED);
		return \CoreLibs\Debug\RunningTime::runningTime($simple);
	}

	// ****** DEBUG SUPPORT FUNCTIONS ******
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Debug\Support

	/**
	 * wrapper around microtime function to print out y-m-d h:i:s.ms
	 * @param  int $set_microtime -1 to set micro time, 0 for none, positive for rounding
	 * @return string             formated datetime string with microtime
	 * @deprecated Use \CoreLibs\Debug\Support::printTime() instead
	 */
	public static function printTime(int $set_microtime = -1): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Debug\Support::printTime()', E_USER_DEPRECATED);
		return \CoreLibs\Debug\Support::printTime($set_microtime);
	}

	// ****** DEBUG SUPPORT FUNCTIONS ******
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Debug\FileWriter

	/**
	 * writes a string to a file immediatly, for fast debug output
	 * @param  string  $string string to write to the file
	 * @param  boolean $enter  default true, if set adds a linebreak \n at the end
	 * @return bool            True of False for success of writing
	 * @deprecated Use \CoreLibs\Debug\FileWriter::fdebug() instead
	 */
	public function fdebug(string $string, bool $enter = true): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Debug\FileWriter::fdebug()', E_USER_DEPRECATED);
		return \CoreLibs\Debug\FileWriter::fdebug($string, $enter);
	}

	// ****** DEBUG LOGGING FUNCTIONS ******
	// Moved to \CoreLibs\Logging\Logging

	/**
	 * passes list of level names, to turn on debug
	 * eg $foo->debugFor('print', 'on', ['LOG', 'DEBUG', 'INFO']);
	 * @param  string $type  error, echo, print
	 * @param  string $flag  on/off
	 *         array<mixed> $array of levels to turn on/off debug
	 * @return void          has no return
	 * @deprecated Use $basic->log->debugFor() instead
	 */
	public function debugFor(string $type, string $flag): void
	{
		trigger_error('Method ' . __METHOD__ . ' functionaility is fully deprecated', E_USER_DEPRECATED);
	}

	/**
	 * write debug data to error_msg array
	 * @param  string       $level  id for error message, groups messages together
	 * @param  string       $string the actual error message
	 * @param  bool|boolean $strip  default on false, if set to true,
	 *                              all html tags will be stripped and <br> changed to \n
	 *                              this is only used for debug output
	 * @return void                 has no return
	 * @deprecated Use Logger\Logger->debug() instead
	 */
	public function debug(string $level, string $string, bool $strip = false): void
	{
		trigger_error('Method ' . __METHOD__ . ' has moved to Logger\Logger->debug()', E_USER_DEPRECATED);
		$this->log->debug($level, $string);
	}

	/**
	 * merges the given error array with the one from this class
	 * only merges visible ones
	 * @param  array<mixed>  $error_msg error array
	 * @return void
	 * @deprecated Use $basic->log->mergeErrors() instead
	 */
	public function mergeErrors(array $error_msg = []): void
	{
		trigger_error('Method ' . __METHOD__ . ' is fully deprecated', E_USER_DEPRECATED);
	}

	/**
	 * prints out the error string
	 * @param  string $string prefix string for header
	 * @return string         error msg for all levels
	 * @deprecated Use $basic->log->printErrorMsg() instead
	 */
	public function printErrorMsg(string $string = ''): string
	{
		trigger_error('Method ' . __METHOD__ . ' is fully deprecated', E_USER_DEPRECATED);
		return '';
	}

	/**
	 * unsests the error message array
	 * can be used if writing is primary to file
	 * if no level given resets all
	 * @param  string $level optional level
	 * @return void          has no return
	 * @deprecated Use $basic->log->resetErrorMsg() instead
	 */
	public function resetErrorMsg(string $level = ''): void
	{
		trigger_error('Method ' . __METHOD__ . ' is fully deprecated', E_USER_DEPRECATED);
	}

	// ****** DEBUG SUPPORT FUNCTIONS ******
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Debug\Support

	/**
	 * prints a html formatted (pre) array
	 * @param  array<mixed> $array any array
	 * @return string              formatted array for output with <pre> tag added
	 * @deprecated Use \CoreLibs\Debug\Support::prAr() instead
	 */
	public function printAr(array $array): string
	{
		return \CoreLibs\Debug\Support::prAr($array);
	}

	/**
	 * if there is a need to find out which parent method called a child method,
	 * eg for debugging, this function does this
	 * call this method in the child method and you get the parent function that called
	 * @param  int    $level debug level, default 2
	 * @return ?string       null or the function that called the function
	 *                       where this method is called
	 * @deprecated Use \CoreLibs\Debug\Support::getCallerMethod() instead
	 */
	public static function getCallerMethod(int $level = 2): ?string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Debug\Support::getCallerMethod()', E_USER_DEPRECATED);
		return \CoreLibs\Debug\Support::getCallerMethod($level);
	}

	// *** SYSTEM HANDLING
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Get\System

	/**
	 * helper function for PHP file upload error messgaes to messge string
	 * @param  int    $error_code integer _FILE upload error code
	 * @return string                     message string, translated
	 * @deprecated Use \CoreLibs\Get\System::fileUploadErrorMessage() instead
	 */
	public function fileUploadErrorMessage(int $error_code): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Get\System::fileUploadErrorMessage()', E_USER_DEPRECATED);
		return \CoreLibs\Get\System::fileUploadErrorMessage($error_code);
	}

	// ****** DEBUG/ERROR FUNCTIONS ******

	// ****** RANDOM KEY GEN ******
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Create\RandomKey

	/**
	 * sets the key length and checks that they key given is valid
	 * if failed it will not change the default key length and return false
	 * @param  int    $key_length key length
	 * @return bool               true/false for set status
	 * @deprecated Use \CoreLibs\Create\RandomKey::setRandomKeyLength() instead
	 */
	public function initRandomKeyLength(int $key_length): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\RandomKey::setRandomKeyLength()', E_USER_DEPRECATED);
		// no op, we do no longer pre set the random key length
		return true;
	}

	/**
	 * creates a random key based on the key_range with key_length
	 * if override key length is set, it will check on valid key and use this
	 * this will not set the class key length variable
	 * @param  int    $key_length key length override, -1 for use default
	 * @return string             random key
	 * @deprecated Use \CoreLibs\Create\RandomKey::randomKeyGen() instead
	 */
	public function randomKeyGen(int $key_length = -1): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\RandomKey::randomKeyGen()', E_USER_DEPRECATED);
		return \CoreLibs\Create\RandomKey::randomKeyGen($key_length);
	}

	// ****** MAGIC LINK/CHECKED/SELECTED ******
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Output\Form\Elements

	/**
	 * returns 'checked' or 'selected' if okay
	 * $needle is a var, $haystack an array or a string
	 * **** THE RETURN: VALUE WILL CHANGE TO A DEFAULT NULL IF NOT FOUND ****
	 * @param  array<mixed>|string $haystack (search in) haystack can be an array or a string
	 * @param  string       $needle   needle (search for)
	 * @param  int          $type     type: 0: returns selected, 1, returns checked
	 * @return ?string                returns checked or selected, else returns null
	 * @deprecated Use \CoreLibs\Convert\Html::checked() instead
	 */
	public static function checked($haystack, $needle, int $type = 0): ?string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Html::checked()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Html::checked($haystack, $needle, $type);
	}

	/**
	 * tries to find mailto:user@bubu.at and changes it into -> <a href="mailto:user@bubu.at">E-Mail senden</a>
	 * or tries to take any url (http, ftp, etc) and transform it into a valid URL
	 * the string is in the format: some url|name#css|, same for email
	 * @param  string $string data to transform to a valud HTML url
	 * @param  string $target target string, default _blank
	 * @return string         correctly formed html url link
	 * @deprecated Use \CoreLibs\Output\Form\Elements::magicLinks() instead
	 */
	public function magicLinks(string $string, string $target = "_blank"): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Form\Elements::magicLinks()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Form\Elements::magicLinks($string, $target);
	}

	// *** SYSTEM HANDLING
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Get\System

	/**
	 * get the host name without the port as given by the SELF var
	 * @return array<mixed> host name/port name
	 * @deprecated Use \CoreLibs\Get\System::getHostName() instead
	 */
	public function getHostName(): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Get\System::getHostName()', E_USER_DEPRECATED);
		return \CoreLibs\Get\System::getHostName();
	}

	/**
	 * get the page name of the curronte page
	 * @param  int    $strip_ext 1: strip page file name extension
	 *                           0: keep filename as is
	 *                           2: keep filename as is, but add dirname too
	 * @return string            filename
	 * @deprecated Use \CoreLibs\Get\System::getPageName() instead
	 */
	public static function getPageName(int $strip_ext = 0): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Get\System::getPageName()', E_USER_DEPRECATED);
		return \CoreLibs\Get\System::getPageName($strip_ext);
	}

	// *** FILE HANDLING
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Check\File

	/**
	 * quick return the extension of the given file name
	 * @param  string $filename file name
	 * @return string           extension of the file name
	 * @deprecated Use \CoreLibs\Check\File::getFilenameEnding() instead
	 */
	public static function getFilenameEnding(string $filename): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Check\File::getFilenameEnding()', E_USER_DEPRECATED);
		return \CoreLibs\Check\File::getFilenameEnding($filename);
	}

	/**
	 * get lines in a file
	 * @param  string $file file for line count read
	 * @return int          number of lines or -1 for non readable file
	 * @deprecated Use \CoreLibs\Check\File::getLinesFromFile() instead
	 */
	public static function getLinesFromFile(string $file): int
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Check\File::getLinesFromFile()', E_USER_DEPRECATED);
		return \CoreLibs\Check\File::getLinesFromFile($file);
	}

	// *** ARRAY HANDLING
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Combined\ArrayHandler

	/**
	 * searches key = value in an array / array
	 * only returns the first one found
	 * @param  string|int  $needle     needle (search for)
	 * @param  array<mixed>       $haystack   haystack (search in)
	 * @param  string|null $key_lookin the key to look out for, default empty
	 * @return array<mixed>                   array with the elements where the needle can be
	 *                                 found in the haystack array
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::arraySearchRecursive() instead
	 */
	public static function arraySearchRecursive($needle, array $haystack, ?string $key_lookin = null): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arraySearchRecursive()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arraySearchRecursive($needle, $haystack, $key_lookin);
	}

	/**
	 * recursive array search function, which returns all found not only the first one
	 * @param  string|int   $needle   needle (search for)
	 * @param  array<mixed> $haystack haystack (search in)
	 * @param  string|int   $key      the key to look for in
	 * @param  array<mixed>|null $path recursive call for previous path
	 * @return ?array<mixed>           all array elements paths where the element was found
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::arraySearchRecursiveAll() instead
	 */
	public static function arraySearchRecursiveAll($needle, array $haystack, $key, ?array $path = null): ?array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arraySearchRecursiveAll()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arraySearchRecursiveAll($needle, $haystack, $key, true, $path);
	}

	/**
	 * array search simple. looks for key, value Combined, if found, returns true
	 * @param  array<mixed>      $array array (search in)
	 * @param  string|int $key   key (key to search in)
	 * @param  string|int $value value (what to find)
	 * @return bool              true on found, false on not found
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::arraySearchSimple() instead
	 */
	public static function arraySearchSimple(array $array, $key, $value): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arraySearchSimple()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arraySearchSimple($array, $key, $value);
	}

	/**
	 * correctly recursive merges as an array as array_merge_recursive just glues things together
	 *         array first array to merge
	 *         array second array to merge
	 *         ...   etc
	 *         bool  key flag: true: handle keys as string or int
	 *               default false: all keys are string
	 * @return array<mixed>|bool merged array
	 * @deprecated MUSER BE CONVERTED TO \CoreLibs\Combined\ArrayHandler::arrayMergeRecursive() instead
	 */
	public static function arrayMergeRecursive()
	{
		trigger_error('MUST CHANGE: Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arrayMergeRecursive()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arrayMergeRecursive(...func_get_args());
	}

	/**
	 * correct array_diff that does an actualy difference between two arrays.
	 * array_diff only checks elements from A that are not in B, but not the
	 * other way around.
	 * Note that like array_diff this only checks first level values not keys
	 * @param  array<mixed>  $a array to compare a
	 * @param  array<mixed>  $b array to compare b
	 * @return array<mixed>     array with missing elements from a & b
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::arrayDiff() instead
	 */
	public static function arrayDiff(array $a, array $b): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arrayDiff()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arrayDiff($a, $b);
	}

	/**
	 * search for the needle array elements in haystack and return the ones found as an array,
	 * is there nothing found, it returns FALSE (boolean)
	 * @param  array<mixed> $needle   elements to search for
	 * @param  array<mixed> $haystack array where the $needle elements should be searched int
	 * @return array<mixed>|bool      either the found elements or false for nothing found or error
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::inArrayAny() instead
	 */
	public static function inArrayAny(array $needle, array $haystack)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::inArrayAny()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::inArrayAny($needle, $haystack);
	}

	/**
	 * creates out of a normal db_return array an assoc array
	 * @param  array<mixed>           $db_array return array from the database
	 * @param  string|int|bool $key      key set, false for not set
	 * @param  string|int|bool $value    value set, false for not set
	 * @param  bool            $set_only flag to return all (default), or set only
	 * @return array<mixed>                     associative array
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::genAssocArray() instead
	 */
	public static function genAssocArray(array $db_array, $key, $value, bool $set_only = false): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::flattenArray()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::genAssocArray($db_array, $key, $value, $set_only);
	}

	/**
	 * [NOTE]: This is an old function and is deprecated
	 * wrapper for join, but checks if input is an array and if not returns null
	 * @param  array<mixed>  $array        array to convert
	 * @param  string $connect_char connection character
	 * @return string               joined string
	 * @deprecated use join() instead
	 */
	public static function arrayToString(array $array, string $connect_char): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use join()', E_USER_DEPRECATED);
		return join($connect_char, $array);
	}

	/**
	 * converts multi dimensional array to a flat array
	 * does NOT preserve keys
	 * @param  array<mixed>  $array ulti dimensionial array
	 * @return array<mixed>         flattened array
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::flattenArray() instead
	 */
	public static function flattenArray(array $array): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::flattenArray()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::flattenArray($array);
	}

	/**
	 * will loop through an array recursivly and write the array keys back
	 * @param  array<mixed>  $array  multidemnsional array to flatten
	 * @return array<mixed>          flattened keys array
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::flattenArrayKey() instead
	 */
	public static function flattenArrayKey(array $array/*, array $return = []*/): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::flattenArrayKey()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::flattenArrayKey($array);
	}

	/**
	 * searches for key -> value in an array tree and writes the value one level up
	 * this will remove this leaf will all other values
	 * @param  array<mixed>      $array  array (nested)
	 * @param  string|int $search key to find that has no sub leaf and will be pushed up
	 * @return array<mixed>              modified, flattened array
	 * @deprecated Use \CoreLibs\Combined\ArrayHandler::arrayFlatForKey() instead
	 */
	public static function arrayFlatForKey(array $array, $search): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use  \CoreLibs\Combined\ArrayHandler::arrayFlatForKey()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\ArrayHandler::arrayFlatForKey($array, $search);
	}

	// *** ARRAY HANDLING END
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Convert\MimeEncode

	/**
	 * wrapper function for mb mime convert, for correct conversion with long strings
	 * @param  string $string   string to encode
	 * @param  string $encoding target encoding
	 * @return string           encoded string
	 * @deprecated Use \CoreLibs\Convert\MimeEncode::__mbMimeEncode() instead
	 */
	public static function __mbMimeEncode(string $string, string $encoding): string
	{

		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\MimeEncode::__mbMimeEncode()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\MimeEncode::__mbMimeEncode($string, $encoding);
	}

	// *** HUMAND BYTE READABLE CONVERT
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Convert\Byte

	/**
	 * WRAPPER call to new humanReadableByteFormat
	 * converts bytes into formated string with KB, MB, etc
	 * @param  string|int|float $bytes  bytes as string int or pure int
	 * @param  bool             $space  default true, to add space between number and suffix
	 * @param  bool             $adjust default false, always print two decimals (sprintf)
	 * @param  bool             $si     default false, if set to true, use 1000 for calculation
	 * @return string                   converted byte number (float) with suffix
	 * @deprecated Use \CoreLibs\Convert\Byte::humanReadableByteFormat() instead
	 */
	public static function byteStringFormat($bytes, bool $space = true, bool $adjust = false, bool $si = false): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Byte::humanReadableByteFormat()', E_USER_DEPRECATED);
		$flags = 0;
		// match over the true/false flags to the new int style flag
		// if space need to set 1
		if ($space === false) {
			$flags |= \CoreLibs\Convert\Byte::BYTE_FORMAT_NOSPACE;
		}
		// if adjust need to set 2
		if ($adjust === true) {
			$flags |= \CoreLibs\Convert\Byte::BYTE_FORMAT_ADJUST;
		}
		// if si need to set 3
		if ($si === true) {
			$flags |= \CoreLibs\Convert\Byte::BYTE_FORMAT_SI;
		}
		// call
		return \CoreLibs\Convert\Byte::humanReadableByteFormat($bytes, $flags);
	}


	/**
	 * This function replaces the old byteStringFormat
	 *
	 * Converts any number string to human readable byte format
	 * Maxium is Exobytes and above that the Exobytes suffix is used for all
	 * If more are needed only the correct short name for the suffix has to be
	 * added to the labels array
	 * On no number string it returns string as is
	 * Source Idea: SOURCE: https://programming.guide/worlds-most-copied-so-snippet.html
	 *
	 * The class itself hast the following defined
	 * BYTE_FORMAT_NOSPACE [1] turn off spaces between number and extension
	 * BYTE_FORMAT_ADJUST  [2] use sprintf to always print two decimals
	 * BYTE_FORMAT_SI      [3] use si standard 1000 instead of bytes 1024
	 * To use the constant from outside use $class::CONSTANT
	 * @param  string|int|float $bytes bytes as string int or pure int
	 * @param  int              $flags bitwise flag with use space turned on
	 * @return string                  converted byte number (float) with suffix
	 * @deprecated Use \CoreLibs\Convert\Byte::humanReadableByteFormat() instead
	 */
	public static function humanReadableByteFormat($bytes, int $flags = 0): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Byte::humanReadableByteFormat()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Byte::humanReadableByteFormat($bytes, $flags);
	}

	/**
	 * calculates the bytes based on a string with nnG, nnGB, nnM, etc
	 * @param  string|int|float $number       any string or number to convert
	 * @return string|int|float               converted value or original value
	 * @deprecated Use \CoreLibs\Convert\Byte::stringByteFormat() instead
	 */
	public static function stringByteFormat($number)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Byte::stringByteFormat()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Byte::stringByteFormat($number);
	}

	// *** DATETIME FUNCTONS
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Combined\DateTime

	/**
	 * a simple wrapper for the date format
	 * @param  int|float $timestamp  unix timestamp
	 * @param  bool      $show_micro show the micro time (default false)
	 * @return string                formated date+time in Y-M-D h:m:s ms
	 * @deprecated Use \CoreLibs\Combined\DateTime::dateStringFormat() instead
	 */
	public static function dateStringFormat($timestamp, bool $show_micro = false): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::dateStringFormat()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::dateStringFormat($timestamp, $show_micro);
	}

	/**
	 * formats a timestamp into interval, not into a date
	 * @param  string|int|float $timestamp  interval in seconds and optional float micro seconds
	 * @param  bool             $show_micro show micro seconds, default true
	 * @return string                       interval formatted string or string as is
	 * @deprecated Use \CoreLibs\Combined\DateTime::timeStringFormat() instead
	 */
	public static function timeStringFormat($timestamp, bool $show_micro = true): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::timeStringFormat()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::timeStringFormat($timestamp, $show_micro);
	}

	/**
	 * does a reverse of the TimeStringFormat and converts the string from
	 * xd xh xm xs xms to a timestamp.microtime format
	 * @param  string|int|float $timestring formatted interval
	 * @return string|int|float             converted float interval, or string as is
	 * @deprecated Use \CoreLibs\Combined\DateTime::stringToTime() instead
	 */
	public static function stringToTime($timestring)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::stringToTime()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::stringToTime($timestring);
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 * @param  string $date a date string in the format YYYY-MM-DD
	 * @return bool         true if valid date, false if date not valid
	 * @deprecated Use \CoreLibs\Combined\DateTime::checkDate() instead
	 */
	public static function checkDate($date): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::checkDate()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::checkDate($date);
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 * @param  string $datetime date (YYYY-MM-DD) + time (HH:MM:SS), SS can be dropped
	 * @return bool             true if valid date, false if date not valid
	 * @deprecated Use \CoreLibs\Combined\DateTime::checkDateTime() instead
	 */
	public static function checkDateTime($datetime): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::checkDateTime()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::checkDateTime($datetime);
	}

	/**
	 * plits & checks date, wrap around for check_date function
	 * returns int in:
	 *     -1 if the first date is smaller the last
	 *     0 if both are equal
	 *     1 if the first date is bigger than the last
	 *     false (bool): error
	 * @param  string $start_date start date string in YYYY-MM-DD
	 * @param  string $end_date   end date string in YYYY-MM-DD
	 * @return int|bool           false on error, or int -1/0/1 as difference
	 * @deprecated Use \CoreLibs\Combined\DateTime::compareDate() instead
	 */
	public static function compareDate($start_date, $end_date)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::compareDate()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::compareDate($start_date, $end_date);
	}

	/**
	 * compares the two dates + times. if seconds missing in one set, add :00, converts / to -
	 * returns int/bool in:
	 *     -1 if the first date is smaller the last
	 *     0 if both are equal
	 *     1 if the first date is bigger than the last
	 *     false if no valid date/times chould be found
	 * @param  string $start_datetime start date/time in YYYY-MM-DD HH:mm:ss
	 * @param  string $end_datetime   end date/time in YYYY-MM-DD HH:mm:ss
	 * @return int|bool               false for error or -1/0/1 as difference
	 * @deprecated Use \CoreLibs\Combined\DateTime::compareDateTime() instead
	 */
	public static function compareDateTime($start_datetime, $end_datetime)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::compareDateTime()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::compareDateTime($start_datetime, $end_datetime);
	}

	/**
	 * calculates the days between two dates
	 * return: overall days, week days, weekend days as array 0...2 or named
	 * as overall, weekday and weekend
	 * @param  string $start_date   valid start date (y/m/d)
	 * @param  string $end_date     valid end date (y/m/d)
	 * @param  bool   $return_named return array type, false (default), true for named
	 * @return array<mixed>                0/overall, 1/weekday, 2/weekend
	 * @deprecated Use \CoreLibs\Combined\DateTime::calcDaysInterval() instead
	 */
	public static function calcDaysInterval($start_date, $end_date, bool $return_named = false): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Combined\DateTime::calcDaysInterval()', E_USER_DEPRECATED);
		return \CoreLibs\Combined\DateTime::calcDaysInterval($start_date, $end_date, $return_named);
	}

	// *** DATETIME END

	// *** IMAGE FUNCTIONS
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Output\Image

	/**
	 * converts picture to a thumbnail with max x and max y size
	 * @param  string      $pic          source image file with or without path
	 * @param  int         $size_x       maximum size width
	 * @param  int         $size_y       maximum size height
	 * @param  string      $dummy        empty, or file_type to show an icon instead of nothing if file is not found
	 * @param  string      $path         if source start is not ROOT path, if empty ROOT is choosen
	 * @param  string      $cache_source cache path, if not given TMP is used
	 * @param  bool        $clear_cache  if set to true, will create thumb all the tame
	 * @return string|bool               thumbnail name, or false for error
	 *@deprecated use \CoreLibs\Output\Image::createThumbnail() instead
	 */
	public static function createThumbnail(
		string $pic,
		int $size_x,
		int $size_y,
		string $dummy = '',
		string $path = '',
		string $cache_source = '',
		bool $clear_cache = false
	) {
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Image::createThumbnail()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Image::createThumbnail($pic, $size_x, $size_y, $dummy, $path, $cache_source, $clear_cache);
	}

	/**
	 * simple thumbnail creation for jpeg, png only
	 * TODO: add other types like gif, etc
	 * - bails with false on failed create
	 * - if either size_x or size_y are empty (0)
	 *   the resize is to max of one size
	 *   if both are set, those are the max sizes (aspect ration is always ekpt)
	 * - if path is not given will cache folder for current path set
	 * @param  string      $filename       source file name with full path
	 * @param  int         $thumb_width    thumbnail width
	 * @param  int         $thumb_height   thumbnail height
	 * @param  string|null $thumbnail_path altnerative path for thumbnails
	 * @param  bool        $create_dummy   if we encounter an invalid file
	 *                                     create a dummy image file and return it
	 * @param  bool        $use_cache      default to true, set to false to skip
	 *                                     creating new image if exists
	 * @param  bool        $high_quality   default to true, uses sample version, set to false
	 *                                     to use quick but less nice version
	 * @param  int         $jpeg_quality   default 80, set image quality for jpeg only
	 * @return string|bool                 thumbnail with path
	 * @deprecated use \CoreLibs\Output\Image::createThumbnailSimple() instead
	 */
	public function createThumbnailSimple(
		string $filename,
		int $thumb_width = 0,
		int $thumb_height = 0,
		?string $thumbnail_path = null,
		bool $create_dummy = true,
		bool $use_cache = true,
		bool $high_quality = true,
		int $jpeg_quality = 80
	) {
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Image::createThumbnailSimple()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Image::createThumbnailSimple($filename, $thumb_width, $thumb_height, $thumbnail_path, null, $create_dummy, $use_cache, $high_quality, $jpeg_quality);
	}

	/**
	 * reads the rotation info of an file and rotates it to be correctly upright
	 * this is done because not all software honers the exit Orientation flag
	 * only works with jpg or png
	 * @param  string $filename path + filename to rotate. This file must be writeable
	 * @return void
	 * @deprecated use \CoreLibs\Output\Image::correctImageOrientation() instead
	 */
	public function correctImageOrientation($filename): void
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Image::correctImageOrientation()', E_USER_DEPRECATED);
		\CoreLibs\Output\Image::correctImageOrientation($filename);
	}

	// *** IMAGE END

	// *** ENCODING FUNCTIONS
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Check\Encoding and \CoreLibs\Convert\Encoding

	/**
	 * test if a string can be safely convert between encodings. mostly utf8 to shift jis
	 * the default compare has a possibility of failure, especially with windows
	 * it is recommended to the following in the script which uses this method:
	 * mb_substitute_character(0x2234);
	 * $class->mbErrorChar = '∴';
	 * if check to Shift JIS
	 * if check to ISO-2022-JP
	 * if check to ISO-2022-JP-MS
	 * set three dots (∴) as wrong character for correct convert error detect
	 * (this char is used, because it is one of the least used ones)
	 * @param  string     $string        string to test
	 * @param  string     $from_encoding encoding of string to test
	 * @param  string     $to_encoding   target encoding
	 * @return bool|array<mixed>         false if no error or array with failed characters
	 * @deprecated use \CoreLibs\Check\Encoding::checkConvertEncoding() instead
	 */
	public function checkConvertEncoding(string $string, string $from_encoding, string $to_encoding)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Encoding::checkConvertEncoding()', E_USER_DEPRECATED);
		return \CoreLibs\Check\Encoding::checkConvertEncoding($string, $from_encoding, $to_encoding);
	}

	/**
	 * detects the source encoding of the string and if doesn't match
	 * to the given target encoding it convert is
	 * if source encoding is set and auto check is true (default) a second
	 * check is done so that the source string encoding actually matches
	 * will be skipped if source encoding detection is ascii
	 * @param  string $string          string to convert
	 * @param  string $to_encoding     target encoding
	 * @param  string $source_encoding optional source encoding, will try to auto detect
	 * @param  bool   $auto_check      default true, if source encoding is set
	 *                                 check that the source is actually matching
	 *                                 to what we sav the source is
	 * @return string|false            encoding converted string
	 * @deprecated use \CoreLibs\Convert\Encoding::convertEncoding() instead
	 */
	public static function convertEncoding(string $string, string $to_encoding, string $source_encoding = '', bool $auto_check = true): string|false
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Encoding::convertEncoding()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Encoding::convertEncoding($string, $to_encoding, $source_encoding, $auto_check);
	}

	// *** ENCODING FUNCTIONS END

	// *** HASH FUNCTIONS
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Create\Hash

	/**
	 * checks php version and if >=5.2.7 it will flip the string
	 * @param  string $string string to crc
	 * @return string         crc32b hash (old type)
	 * @deprecated use \CoreLibs\Create\Hash::__crc32b() instead
	 */
	public function __crc32b(string $string): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\Hash::__crc32b()', E_USER_DEPRECATED);
		return \CoreLibs\Create\Hash::__crc32b($string);
	}

	/**
	 * replacement for __crc32b call
	 * @param  string $string  string to hash
	 * @param  bool   $use_sha use sha instead of crc32b (default false)
	 * @return string          hash of the string
	 * @deprecated use \CoreLibs\Create\Hash::__sha1Short() instead
	 */
	public function __sha1Short(string $string, bool $use_sha = false): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\Hash::sha1Short() or ::__crc32b()', E_USER_DEPRECATED);
		if ($use_sha) {
			return \CoreLibs\Create\Hash::sha1Short($string);
		} else {
			return \CoreLibs\Create\Hash::__crc32b($string);
		}
	}

	/**
	 * replacemend for __crc32b call (alternate)
	 * defaults to adler 32
	 * allowed adler32, fnv132, fnv1a32, joaat
	 * all that create 8 char long hashes
	 * @param  string $string    string to hash
	 * @param  string $hash_type hash type (default adler32)
	 * @return string            hash of the string
	 * @deprecated use \CoreLibs\Create\Hash::__hash() instead
	 */
	public function __hash(string $string, string $hash_type = 'adler32'): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\Hash::hash()', E_USER_DEPRECATED);
		return \CoreLibs\Create\Hash::hash($string, $hash_type);
	}

	// *** HASH FUNCTIONS END

	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Check\PhpVersion

	/**
	 * checks if running PHP version matches given PHP version (min or max)
	 * @param  string $min_version minimum version as string (x, x.y, x.y.x)
	 * @param  string $max_version optional maximum version as string (x, x.y, x.y.x)
	 * @return bool                true if ok, false if not matching version
	 * @deprecated use \CoreLibs\Check\PhpVersion::checkPHPVersion() instead
	 */
	public static function checkPHPVersion(string $min_version, string $max_version = ''): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\PhpVersion::checkPHPVersion()', E_USER_DEPRECATED);
		return \CoreLibs\Check\PhpVersion::checkPHPVersion($min_version, $max_version);
	}

	// *** UIDS ELEMENTS
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Create\Uids

	/**
	 * creates psuedo random uuid v4
	 * Code take from class here:
	 * https://www.php.net/manual/en/function.uniqid.php#94959
	 * @return string pseudo random uuid v4
	 * @deprecated use \CoreLibs\Create\Uids::uuidv4() instead
	 */
	public static function uuidv4(): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\Uids::uuidv4()', E_USER_DEPRECATED);
		return \CoreLibs\Create\Uids::uuidv4();
	}

	/**
	 * TODO: make this a proper uniq ID creation
	 *       add uuidv4 subcall to the uuid function too
	 * creates a uniq id
	 * @param  string $type uniq id type, currently md5 or sha256 allowed
	 *                      if not set will use DEFAULT_HASH if set
	 * @return string       uniq id
	 * @deprecated use \CoreLibs\Create\Uids::uniqId() instead
	 */
	public function uniqId(string $type = ''): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Create\Uids::uniqId()', E_USER_DEPRECATED);
		return \CoreLibs\Create\Uids::uniqId($type);
	}

	// *** UIDS END

	/**
	 * creates the password hash
	 * @param  string $password password
	 * @return string           hashed password
	 * @deprecated use \CoreLibs\Check\Password::passwordSet() instead
	 */
	public function passwordSet(string $password): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Password::passwordSet()', E_USER_DEPRECATED);
		return \CoreLibs\Security\Password::passwordSet($password);
	}

	/**
	 * checks if the entered password matches the hash
	 * @param  string $password password
	 * @param  string $hash     password hash
	 * @return bool             true or false
	 * @deprecated use \CoreLibs\Check\Password::passwordVerify() instead
	 */
	public function passwordVerify(string $password, string $hash): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Password::passwordVerify()', E_USER_DEPRECATED);
		return \CoreLibs\Security\Password::passwordVerify($password, $hash);
	}

	/**
	 * checks if the password needs to be rehashed
	 * @param  string $hash password hash
	 * @return bool         true or false
	 * @deprecated use \CoreLibs\Check\Password::passwordRehashCheck() instead
	 */
	public function passwordRehashCheck(string $hash): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Password::passwordRehashCheck()', E_USER_DEPRECATED);
		return \CoreLibs\Security\Password::passwordRehashCheck($hash);
	}

	// *** BETTER PASSWORD OPTIONS END ***

	// *** EMAIL FUNCTIONS ***
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Check\Email

	/**
	 * guesses the email type (mostly for mobile) from the domain
	 * if second is set to true, it will return short naming scheme (only provider)
	 * @param  string $email email string
	 * @param  bool   $short default false, if true, returns only short type (pc instead of pc_html)
	 * @return string|bool   email type, eg "pc", "docomo", etc, false for invalid short type
	 * @deprecated use \CoreLibs\Check\Email::getEmailType() instead
	 */
	public function getEmailType(string $email, bool $short = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Email::getEmailType()', E_USER_DEPRECATED);
		return \CoreLibs\Check\Email::getEmailType($email, $short);
	}

	/**
	 * gets the short email type from a long email type
	 * @param  string $email_type email string
	 * @return string|bool              short string or false for invalid
	 * @deprecated use \CoreLibs\Check\Email::getShortEmailType() instead
	 */
	public function getShortEmailType(string $email_type)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Email::getShortEmailType()', E_USER_DEPRECATED);
		return \CoreLibs\Check\Email::getShortEmailType($email_type);
	}

	// *** EMAIL FUNCTIONS END ***

	// *** HTML FUNCTIONS ***
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Output\Form\Elements

	/**
	 * print the date/time drop downs, used in any queue/send/insert at date/time place
	 * @param  int    $year          year YYYY
	 * @param  int    $month         month m
	 * @param  int    $day           day d
	 * @param  int    $hour          hour H
	 * @param  int    $min           min i
	 * @param  string $suffix        additional info printed after the date time variable in the drop down
	 *                               also used for ID in the on change JS call
	 * @param  int    $min_steps     default is 1 (minute), can set to anything, is used as sum up from 0
	 * @param  bool   $name_pos_back default false, if set to true, the name will be printend
	 *                               after the drop down and not before the drop down
	 * @return string                HTML formated strings for drop down lists of date and time
	 * @deprecated use \CoreLibs\Output\Form\Elements::printDateTime() instead
	 */
	public static function printDateTime($year, $month, $day, $hour, $min, string $suffix = '', int $min_steps = 1, bool $name_pos_back = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Form\Elements::printDateTime()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Form\Elements::printDateTime($year, $month, $day, $hour, $min, $suffix, $min_steps, $name_pos_back);
	}

	// Moved to \CoreLibs\Convert\Html

	/**
	 * full wrapper for html entities
	 * @param  mixed $string string to html encode
	 * @return mixed         if string, encoded, else as is (eg null)
	 * @deprecated use \CoreLibs\Convert\Html::htmlent() instead
	 */
	public static function htmlent($string)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Html::htmlent()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Html::htmlent($string);
	}

	/**
	 * strips out all line breaks or replaced with given string
	 * @param  string $string  string
	 * @param  string $replace replace character, default ' '
	 * @return string          cleaned string without any line breaks
	 * @deprecated use \CoreLibs\Convert\Html::removeLB() instead
	 */
	public static function removeLB(string $string, string $replace = ' '): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Html::removeLB()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Html::removeLB($string, $replace);
	}

	// *** HTML FUNCTIONS END ***

	// *** MATH FUNCTIONS ***
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Convert\Math

	/**
	 * some float numbers will be rounded up even if they have no decimal entries
	 * this function fixes this by pre-rounding before calling ceil
	 * @param  float       $number    number to round
	 * @param  int|integer $precision intermediat round up decimals (default 10)
	 * @return float                  correct ceil number
	 * @deprecated use \CoreLibs\Convert\Math::fceil() instead
	 */
	public static function fceil(float $number, int $precision = 10): float
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Math::fceil()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Math::fceil($number, $precision);
	}

	/**
	 * round inside an a number, not the decimal part only
	 * eg 48767 with -2 -> 48700
	 * @param  float $number    number to round
	 * @param  int   $precision negative number for position in number (default -2)
	 * @return float            rounded number
	 * @deprecated use \CoreLibs\Convert\Math::floorp() instead
	 */
	public static function floorp(float $number, int $precision = -2): float
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Math::floorp()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Math::floorp($number, $precision);
	}

	/**
	 * inits input to 0, if value is not numeric
	 * @param  string|int|float $number string or number to check
	 * @return float                    if not number, then returns 0, else original input
	 * @deprecated use \CoreLibs\Convert\Math::initNumeric() instead
	 */
	public static function initNumeric($number): float
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\Math::initNumeric()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Math::initNumeric($number);
	}

	// *** MATH FUNCTIONS END ***

	// *** FORM TOKEN PARTS ***
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Output\Form\Token

	/**
	 * sets a form token in a session and returns form token
	 * @param  string $name optional form name, default form_token
	 * @return string       token name for given form id string
	 * @deprecated use \CoreLibs\Output\Form\Token::setFormToken() instead
	 */
	public static function setFormToken(string $name = 'form_token'): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Form\Token::setFormToken()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Form\Token::setFormToken($name);
	}

	/**
	 * checks if the form token matches the session set form token
	 * @param  string $token token string to check
	 * @param  string $name  optional form name to check to, default form_token
	 * @return bool          false if not set, or true/false if matching or not mtaching
	 * @deprecated use \CoreLibs\Output\Form\Token::validateFormToken() instead
	 */
	public static function validateFormToken(string $token, string $name = 'form_token'): bool
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Output\Form\Token::validateFormToken()', E_USER_DEPRECATED);
		return \CoreLibs\Output\Form\Token::validateFormToken($token, $name);
	}

	// *** FORM TOKEN END ***

	// *** MIME PARTS ***
	// [!!! DEPRECATED !!!]
	// Moved to \CoreLibs\Convert\MimeAppName

	/**
	 * Sets or updates a mime type
	 * @param  string $mime MIME Name, no validiation
	 * @param  string $app  Applicaiton name
	 * @return void
	 * @deprecated use \CoreLibs\Convert\MimeAppName()->mimeSetAppName() instead
	 */
	public function mimeSetAppName(string $mime, string $app): void
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\MimeAppName()->mimeSetAppName()', E_USER_DEPRECATED);
		\CoreLibs\Convert\MimeAppName::mimeSetAppName($mime, $app);
	}

	/**
	 * get the application name from mime type
	 * if not set returns "Other file"
	 * @param  string $mime MIME Name
	 * @return string       Application name matching
	 * @deprecated use \CoreLibs\Convert\MimeAppName()->mimeGetAppName() instead
	 */
	public function mimeGetAppName(string $mime): string
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Convert\MimeAppName()->mimeGetAppName()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\MimeAppName::mimeGetAppName($mime);
	}

	// *** MIME PARTS END ***

	// *** JSON ***
	// [!!! DEPRECATED !!!]
	// \CoreLibs\Check\Json

	/**
	 * converts a json string to an array
	 * or inits an empty array on null string
	 * or failed convert to array
	 * In ANY case it will ALWAYS return array.
	 * Does not throw errors
	 * @param  string|null $json     a json string, or null data
	 * @param  bool        $override if set to true, then on json error
	 *                               set original value as array
	 * @return array<mixed>                 returns an array from the json values
	 * @deprecated use \CoreLibs\Check\Json::jsonConvertToArray() instead
	 */
	public function jsonConvertToArray(?string $json, bool $override = false): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Json::jsonConvertToArray()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Json::jsonConvertToArray($json, $override);
	}

	/**
	 * [jsonGetLastError description]
	 * @param  bool|boolean $return_string [default=false] if set to true
	 *                                     it will return the message string and not
	 *                                     the error number
	 * @return int|string                  Either error number (0 for no error)
	 *                                     or error string ('' for no error)
	 * @deprecated use \CoreLibs\Check\Json::jsonGetLastError() instead
	 */
	public function jsonGetLastError(bool $return_string = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated, use \CoreLibs\Check\Json::jsonGetLastError()', E_USER_DEPRECATED);
		return \CoreLibs\Convert\Json::jsonGetLastError($return_string);
	}

	// *** JSON END ***
}

// phpcs:enable

// __END__
