<?php declare(strict_types=1);
/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/03/24
* VERSION: 2.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTION:
*   2018/3/23, the whole class system is transformed to namespaces
*   also all internal class calls are converted to camel case
*
*   basic class start class for ALL clases, holds basic vars, infos, methods, etc
*
* PUBLIC VARIABLES
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
*
* PRIVATE VARIABLES
*	error_msg -> array that holds all the error messages, should not be written from outside, use debug method
*	error_id
*	error_string
*
* PUBLIC METHODS
*	debug -> calls with "level", "string" and flag to turn off (0) the newline at the end
*	debugFor -> sets debug on/off for a type (error, echo, print) for a certain level
*	printErrorMsg -> prints out the error message, optional parameter is a header prefix
*	fdebug -> prints line directly to debug_file.log in tmp
*
* 	printTime -> prints time + microtime, optional flag to turn off (0) microtime printout
*	info -> info about that class
*	runningTime -> prints out the time of start/end (automatically called on created and error printout
*	checked -> returnes checked or selected for var & array
*	magicLinks -> parses text and makes <a href> out of links
*	getPageName -> get the filename of the current page
*	arraySearchRecursive -> search for a value/key combination in an array of arrays
*	byteStringFormat -> format bytes into KB, MB, GB, ...
*	timeStringFormat -> format a timestamp (seconds) into days, months, ... also with ms
*	stringToTime -> reverste a TimeStringFormat to a timestamp
*	genAssocArray -> generactes a new associativ array from an existing array
*	checkDate -> checks if a date is valid
*   compareDate -> compares two dates. -1 if the first is smaller, 0 if they are equal, 1 if the first is bigger
*   compareDateTime -> compares two dates with time. -1 if the first is smaller, 0 if they are equal, 1 if the first is bigger
*   __crc32b -> behaves like the hash("crc32b") in php < 5.2.8. this function will flip the hash like it was (wrong)
*              before if a new php version is found
*   crypt* -> encrypt and decrypt login string data, used by Login class
*   setFormToken/validateFormToken -> form protection with token
*
* PRIVATE METHODS
*	fdebug_fp -> opens and closes file, called from fdebug method
*	write_error_msg -> writes error msg to file if requested
*
* HISTORY:
* 2010/12/24 (cs) add crypt classes with auto detect what crypt we can use, add php version check class
* 2008/08/07 (cs) fixed strange DEBUG_ALL on off behavour. data was written even thought DBEUG_ALL was off. now no debug logging is done at all if DEBUG_ALL is off
* 2007/11/13 (cs) add Comparedate function
* 2007/11/05 (cs) added GenAssocArray and CheckDate functions
* 2007/10/10 (cs) magic links function can use http:///path as a local prefix. blank target is removed & http:// also
* 2006/03/09 (cs) added Byte/TimeStringFormat functions
* 2006/02/21 (cs) fix various problems with the mime magic function: || not always working, fix prefix replacement, etc
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

namespace CoreLibs;

/** Basic core class declaration */
class Basic
{
	// define check vars for the flags we can have
	const CLASS_STRICT_MODE = 1;
	const CLASS_OFF_COMPATIBLE_MODE = 2;
	// control vars
	/** @var bool compatible mode sets variable even if it is not defined */
	private $set_compatible = true;
	/** @var bool strict mode throws an error if the variable is not defined */
	private $set_strict_mode = false;
	// page and host name
	public $page_name;
	public $host_name;
	public $host_port;
	// internal error reporting vars
	protected $error_id; // error ID for errors in classes
	protected $error_msg = array(); // the "connection" to the outside errors
	// debug output prefix
	public $error_msg_prefix = ''; // prefix to the error string (the class name)
	// debug flags
	public $debug_output; // if this is true, show debug on desconstructor
	public $debug_output_not;
	public $debug_output_all;
	public $echo_output; // errors: echo out, default is 1
	public $echo_output_not;
	public $echo_output_all;
	public $print_output; // errors: print to file, default is 0
	public $print_output_not;
	public $print_output_all;
	// debug flags/settings
	public $debug_fp; // filepointer for writing to file
	public $debug_filename = 'debug_file.log'; // where to write output
	public $hash_algo = 'crc32b'; // the hash algo used for the internal debug uid
	public $running_uid = ''; // unique ID set on class init and used in logging as prefix
	// log file name
	private $log_file_name_ext = 'log'; // use this for date rotate
	public $log_max_filesize = 0; // set in kilobytes
	private $log_print_file = 'error_msg##LOGID####LEVEL####CLASS####PAGENAME####DATE##';
	private $log_file_unique_id; // a unique ID set only once for call derived from this class
	public $log_print_file_date = 1; // if set add Y-m-d and do automatic daily rotation
	private $log_file_id = ''; // a alphanumeric name that has to be set as global definition
	public $log_per_level = false; // set, it will split per level (first parameter in debug call)
	public $log_per_class = false; // set, will split log per class
	public $log_per_page = false; // set, will split log per called file
	public $log_per_run = false; // create a new log file per run (time stamp + unique ID)
	// run time messurements
	private $starttime; // start time if time debug is used
	private $endtime; // end time if time debug is used
	public $runningtime_string; // the running time as a string with info text
	private $hr_starttime; // start time
	private $hr_endtime; // end time
	private $hr_runtime = 0; // run time
	// script running time
	private $script_starttime;

	// email valid checks
	public $email_regex_check = array();
	public $mobile_email_type = array();
	public $mobile_email_type_short = array();
	public $email_regex; // regex var for email check
	public $keitai_email_regex; // regex var for email check

	// data path for files
	public $data_path = array();

	// error char for the char conver
	public $mbErrorChar;

	// [!!! DEPRECATED !!!] crypt saslt prefix
	public $cryptSaltPrefix = '';
	public $cryptSaltSuffix = '';
	public $cryptIterationCost = 7; // this is for staying backwards compatible with the old ones
	public $cryptSaltSize = 22; // default 22 chars for blowfish, 2 for STD DES, 8 for MD5,
	// new better password management
	protected $password_options = array();
	// session name
	private $session_name = '';
	private $session_id = '';
	// key generation
	private $key_range = array();
	private $one_key_length;
	private $key_length;
	private $max_key_length = 256; // max allowed length

	// form token (used for form validation)
	private $form_token = '';
	// ajax flag
	protected $ajax_page_flag = false;

	// METHOD: __construct
	// PARAMS: set_control_flag [current sets set/get var errors]
	// RETURN: none
	// DESC  : class constructor
	/**
	 * main Basic constructor to init and check base settings
	 * @param int $set_control_flag 0/1/2/3 to set internal class parameter check
	 */
	public function __construct(int $set_control_flag = 0)
	{
		// init flags
		$this->__setControlFlag($set_control_flag);

		// set per run UID for logging
		$this->running_uid = hash($this->hash_algo, uniqid((string)rand(), true));
		// running time start for script
		$this->script_starttime = microtime(true);

		// before we start any work, we should check that all MUST constants are defined
		$abort = false;
		foreach (array(
			'DS', 'DIR', 'BASE', 'ROOT', 'LIB', 'INCLUDES', 'LAYOUT', 'PICTURES', 'FLASH', 'VIDEOS', 'DOCUMENTS', 'PDFS', 'BINARIES', 'ICONS',
			'UPLOADS', 'CSV', 'JS', 'CSS', 'TABLE_ARRAYS', 'SMARTY', 'LANG', 'CACHE', 'TMP', 'LOG', 'TEMPLATES', 'TEMPLATES_C',
			'DEFAULT_LANG', 'DEFAULT_ENCODING', 'DEFAULT_HASH',
			'DEFAULT_ACL_LEVEL', 'LOGOUT_TARGET', 'PASSWORD_CHANGE', 'AJAX_REQUEST_TYPE', 'USE_PROTOTYPE', 'USE_SCRIPTACULOUS', 'USE_JQUERY',
			'PAGE_WIDTH', 'MASTER_TEMPLATE_NAME', 'PUBLIC_SCHEMA', 'TEST_SCHEMA', 'DEV_SCHEMA', 'LIVE_SCHEMA', 'DB_CONFIG_NAME', 'DB_CONFIG', 'TARGET', 'DEBUG', 'SHOW_ALL_ERRORS'
		) as $constant) {
			if (!defined($constant)) {
				echo "Constant $constant misssing<br>";
				$abort = true;
			}
		}
		if ($abort === true) {
			die('Core Constant missing. Check config file.');
		}

		// set ajax page flag based on the AJAX_PAGE varaibles
		// convert to true/false so if AJAX_PAGE is 0 or false it is
		// always boolean false
		$this->ajax_page_flag = isset($GLOBALS['AJAX_PAGE']) && $GLOBALS['AJAX_PAGE'] ? true : false;

		// set the page name
		$this->page_name = $this->getPageName();
		$this->host_name = $this->getHostName();
		// init the log file id
		// * GLOBALS
		// * CONSTANT
		// can be overridden with basicSetLogFileId
		if (isset($GLOBALS['LOG_FILE_ID'])) {
			$this->basicSetLogId($GLOBALS['LOG_FILE_ID']);
		} elseif (defined('LOG_FILE_ID')) {
			$this->basicSetLogId(LOG_FILE_ID);
		}

		// set the paths matching to the valid file types
		$this->data_path = array(
			'P' => PICTURES,
			'F' => FLASH,
			'V' => VIDEOS,
			'D' => DOCUMENTS,
			'A' => PDFS,
			'B' => BINARIES
		);

		// if given via parameters, only for all
		$this->debug_output_all = false;
		$this->echo_output_all = false;
		$this->print_output_all = false;
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
		if (isset($GLOBALS['DEBUG_ALL'])) {
			$this->debug_output_all = $GLOBALS['DEBUG_ALL'];
		}
		if (isset($GLOBALS['ECHO_ALL'])) {
			$this->echo_output_all = $GLOBALS['ECHO_ALL'];
		}
		if (isset($GLOBALS['PRINT_ALL'])) {
			$this->print_output_all = $GLOBALS['PRINT_ALL'];
		}

		// GLOBAL rules for log writing
		if (isset($GLOBALS['LOG_PRINT_FILE_DATE'])) {
			$this->log_print_file_date = $GLOBALS['LOG_PRINT_FILE_DATE'];
		}
		if (isset($GLOBALS['LOG_PER_LEVEL'])) {
			$this->log_per_level = $GLOBALS['LOG_PER_LEVEL'];
		}
		if (isset($GLOBALS['LOG_PER_CLASS'])) {
			$this->log_per_class = $GLOBALS['LOG_PER_CLASS'];
		}
		if (isset($GLOBALS['LOG_PER_PAGE'])) {
			$this->log_per_page = $GLOBALS['LOG_PER_PAGE'];
		}
		if (isset($GLOBALS['LOG_PER_RUN'])) {
			$this->log_per_run = $GLOBALS['LOG_PER_RUN'];
		}

		// set the regex for checking emails
		$this->email_regex = "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]{1,})*\.([a-zA-Z]{2,}){1}$";
		// this is for error check parts in where the email regex failed
		$this->email_regex_check = array(
			1 => "@(.*)@(.*)", // double @
			2 => "^[A-Za-z0-9!#$%&'*+-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+-\/=?^_`{|}~\.]{0,63}@", // wrong part before @
			3 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.([a-zA-Z]{2,}){1}$", // wrong part after @
			4 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.", // wrong domain name part
			5 => "\.([a-zA-Z]{2,6}){1}$", // wrong top level part
			6 => "@(.*)\.{2,}", // double .. in domain name part
			7 => "@.*\.$" // ends with a dot, top level, domain missing
		);
		// the array with the mobile types that are valid
		$this->mobile_email_type = array(
			'.*@docomo\.ne\.jp$' => 'keitai_docomo',
			'.*@([a-z0-9]{2}\.)?ezweb\.ne\.jp$' => 'keitai_kddi_ezweb', # correct are a[2-4], b2, c[1-9], e[2-9], h[2-4], t[1-9]
			'.*@(ez[a-j]{1}\.)?ido\.ne\.jp$' => 'keitai_kddi_ido', # ez[a-j] or nothing
			'.*@([a-z]{2}\.)?sky\.tu-ka\.ne\.jp$' => 'keitai_kddi_tu-ka', # (sky group)
			'.*@([a-z]{2}\.)?sky\.tk[kc]{1}\.ne\.jp$' => 'keitai_kddi_sky', # (sky group) [tkk,tkc only]
			'.*@([a-z]{2}\.)?sky\.dtg\.ne\.jp$' => 'keitai_kddi_dtg', # dtg (sky group)
			'.*@[tkdhcrnsq]{1}\.vodafone\.ne\.jp$' => 'keitai_softbank_vodafone', # old vodafone [t,k,d,h,c,r,n,s,q]
			'.*@jp-[dhtkrsnqc]{1}\.ne\.jp$' => 'keitai_softbank_j-phone', # very old j-phone (pre vodafone) [d,h,t,k,r,s,n,q,c]
			'.*@([dhtcrknsq]{1}\.)?softbank\.ne\.jp$' => 'keitai_softbank', # add i for iphone also as keitai, others similar to the vodafone group
			'.*@i{1}\.softbank(\.ne)?\.jp$' => 'smartphone_softbank_iphone', # add iPhone also as keitai and not as pc
			'.*@disney\.ne\.jp$' => 'keitai_softbank_disney', # (kids)
			'.*@willcom\.ne\.jp$' => 'keitai_willcom',
			'.*@willcom\.com$' => 'keitai_willcom', # new for pdx.ne.jp address
			'.*@wcm\.ne\.jp$' => 'keitai_willcom', # old willcom wcm.ne.jp
			'.*@pdx\.ne\.jp$' => 'keitai_willcom_pdx', # old pdx address for willcom
			'.*@bandai\.jp$' => 'keitai_willcom_bandai', # willcom paipo! (kids)
			'.*@pipopa\.ne\.jp$' => 'keitai_willcom_pipopa', # willcom paipo! (kids)
			'.*@([a-z0-9]{2,4}\.)?pdx\.ne\.jp$' => 'keitai_willcom_pdx', # actually only di,dj,dk,wm -> all others are "wrong", but none also allowed?
			'.*@ymobile([1]{1})?\.ne\.jp$' => 'keitai_willcom_ymobile', # ymobile, ymobile1 techincally not willcom, but I group them there (softbank sub)
			'.*@y-mobile\.ne\.jp$' => 'keitai_willcom_ymobile', # y-mobile techincally not willcom, but I group them there (softbank sub)
			'.*@emnet\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
			'.*@emobile\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
			'.*@emobile-s\.ne\.jp$' => 'keitai_willcom_emnet' # e-mobile, group will willcom
		);
		// short list for mobile email types
		$this->mobile_email_type_short = array(
			'keitai_docomo' => 'docomo',
			'keitai_kddi_ezweb' => 'kddi',
			'keitai_kddi' => 'kddi',
			'keitai_kddi_tu-ka' => 'kddi',
			'keitai_kddi_sky' => 'kddi',
			'keitai_softbank' => 'softbank',
			'smartphone_softbank_iphone' => 'iphone',
			'keitai_softbank_disney' => 'softbank',
			'keitai_softbank_vodafone' => 'softbank',
			'keitai_softbank_j-phone' => 'softbank',
			'keitai_willcom' => 'willcom',
			'keitai_willcom_pdx' => 'willcom',
			'keitai_willcom_bandai' => 'willcom',
			'keitai_willcom_pipopa' => 'willcom',
			'keitai_willcom_ymobile' => 'willcom',
			'keitai_willcom_emnet' => 'willcom',
			'pc_html' => 'pc',
			// old sets -> to be removed later
			'docomo' => 'docomo',
			'kddi_ezweb' => 'kddi',
			'kddi' => 'kddi',
			'kddi_tu-ka' => 'kddi',
			'kddi_sky' => 'kddi',
			'softbank' => 'softbank',
			'keitai_softbank_iphone' => 'iphone',
			'softbank_iphone' => 'iphone',
			'softbank_disney' => 'softbank',
			'softbank_vodafone' => 'softbank',
			'softbank_j-phone' => 'softbank',
			'willcom' => 'willcom',
			'willcom_pdx' => 'willcom',
			'willcom_bandai' => 'willcom',
			'willcom_pipopa' => 'willcom',
			'willcom_ymobile' => 'willcom',
			'willcom_emnet' => 'willcom',
			'pc' => 'pc'
		);

		// initial the session if there is no session running already
		if (!session_id()) {
			// check if we have an external session name given, else skip this step
			if (defined('SET_SESSION_NAME')) {
				// set the session name for possible later check
				$this->session_name = SET_SESSION_NAME;
			}
			// override with global if set
			if (isset($GLOBALS['SET_SESSION_NAME'])) {
				$this->session_name = $GLOBALS['SET_SESSION_NAME'];
			}
			// if set, set special session name
			if ($this->session_name) {
				session_name($this->session_name);
			}
			// start session
			session_start();
			// set internal session id, we can use that later for protection check
			$this->session_id = session_id();
		}

		// new better password init
		$this->passwordInit();

		// key generation init
		$this->initRandomKeyData();
	}

	// METHOD: __destruct
	// PARAMS: none
	// RETURN: if debug is on, return error data
	// DESC  : basic deconstructor (should be called from all deconstructors in higher classes)
	//        writes out $error_msg to global var
	public function __destruct()
	{
		// this has to be changed, not returned here, this is the last class to close
		// return $this->error_msg;
		// close open file handles
		// $this->fdebugFP('c');
	}

	// *************************************************************
	// INTERAL VARIABLE ERROR HANDLER
	// *************************************************************

	/**
	 * sets internal control flags for class variable check
	 * 0 -> turn of all, works like default php class
	 * CLASS_STRICT_MODE: 1 -> if set throws error on unset class variable
	 * CLASS_OFF_COMPATIBLE_MODE: 2 -> if set turns of auto set for unset variables
	 * 3 -> sets error on unset and does not set variable (strict)
	 * @param  int    $set_control_flag control flag as 0/1/2/3
	 * @return void
	 */
	private function __setControlFlag(int $set_control_flag): void
	{
		// is there either a constant or global set to override the control flag
		if (defined('CLASS_VARIABLE_ERROR_MODE')) {
			$set_control_flag = CLASS_VARIABLE_ERROR_MODE;
		}
		if (isset($GLOBALS['CLASS_VARIABLE_ERROR_MODE'])) {
			$set_control_flag = $GLOBALS['CLASS_VARIABLE_ERROR_MODE'];
		}
		// bit wise check of int and set
		if ($set_control_flag & self::CLASS_OFF_COMPATIBLE_MODE) {
			$this->set_compatible = false;
		} else {
			$this->set_compatible = true;
		}
		if ($set_control_flag & self::CLASS_STRICT_MODE) {
			$this->set_strict_mode = true;
		} else {
			$this->set_strict_mode = false;
		}
	}

	/**
	 * if strict mode is set, throws an error if the class variable is not set
	 * if compatible mode is set, also auto sets variable even if not declared
	 * default is strict mode false and compatible mode on
	 * @param  mixed $name  class variable name
	 * @return void
	 */
	public function __set($name, $value): void
	{
		if ($this->set_strict_mode === true && !property_exists($this, $name)) {
			trigger_error('Undefined property via __set(): '.$name, E_USER_NOTICE);
		}
		// use this for fallback as to work like before to set unset
		if ($this->set_compatible === true) {
			$this->{$name} = $value;
		}
	}

	/**
	 * if strict mode is set, throws an error if the class variable is not set
	 * default is strict mode false
	 * @param  mixed $name class variable name
	 * @return mixed       return set variable content
	 */
	public function &__get($name)
	{
		if ($this->set_strict_mode === true && !property_exists($this, $name)) {
			trigger_error('Undefined property via __get(): '.$name, E_USER_NOTICE);
		}
		// on set return
		if (property_exists($this, $name)) {
			return $this->$name;
		} elseif ($this->set_compatible === true && !property_exists($this, $name)) {
			// if it is not set, and we are in compatible mode we need to init.
			// This is so that $class->array['key'] = 'bar'; works
			$this->{$name} = null;
			return $this->$name;
		}
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
	 */
	public function basicSetLogId(string $string): string
	{
		if (preg_match("/^\w+$/", $string)) {
			$this->log_file_id = $string;
		}
		return $this->log_file_id;
	}

	// ****** DEBUG/ERROR FUNCTIONS ******

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
	 */
	public function hrRunningTime(string $out_time = 'ms'): float
	{
		// if start time not set, set start time
		if (!$this->hr_starttime) {
			$this->hr_starttime = hrtime(true);
			$this->hr_runtime = 0;
		} else {
			$this->hr_endtime = hrtime(true);
			$this->hr_runtime = $this->hr_endtime - $this->hr_starttime;
			// reset start and end time past run
			$this->hr_starttime = 0;
			$this->hr_endtime = 0;
		}
		// init divisor, just in case
		$divisor = 1;
		// check through valid out time, if nothing matches default to ms
		switch ($out_time) {
			case 'n':
			case 'ns':
				$divisor = 1;
				break;
			case 'y':
			case 'ys':
				$divisor = 1000;
				break;
			case 'm':
			case 'ms':
				$divisor = 1000000;
				break;
			case 's':
				$divisor = 1000000000;
				break;
			// default is ms
			default:
				$divisor = 1000000;
				break;
		}
		// return the run time in converted format
		$this->hr_runtime /= $divisor;
		return $this->hr_runtime;
	}

	/**
	 * prints start or end time in text format. On first call sets start time
	 * on second call it sends the end time and then also prints the running time
	 * Sets the internal runningtime_string variable with Start/End/Run time string
	 * NOTE: for pure running time check it is recommended to use hrRunningTime method
	 * @param  bool|boolean $simple     if true prints HTML strings, default text only
	 * @return float                    running time as float number
	 */
	public function runningTime(bool $simple = false): float
	{
		list($micro, $timestamp) = explode(' ', microtime());
		$running_time = 0;
		// set start & end time
		if (!$this->starttime) {
			// always reset running time string on first call
			$this->runningtime_string = '';
			$this->starttime = ((float)$micro + (float)$timestamp);
			$this->runningtime_string .= $simple ? 'Start: ' : "<b>Started at</b>: ";
		} else {
			$this->endtime = ((float)$micro + (float)$timestamp);
			$this->runningtime_string .= $simple ? 'End: ' : "<b>Stopped at</b>: ";
		}
		$this->runningtime_string .= date('Y-m-d H:i:s', (int)$timestamp);
		$this->runningtime_string .= ' '.$micro.($simple ? ', ' : '<br>');
		// if both are set
		if ($this->starttime && $this->endtime) {
			$running_time = $this->endtime - $this->starttime;
			$this->runningtime_string .= ($simple ? 'Run: ' : "<b>Script running time</b>: ").$running_time." s";
			// reset start & end time after run
			$this->starttime = 0;
			$this->endtime = 0;
		}
		return $running_time;
	}

	/**
	 * wrapper around microtime function to print out y-m-d h:i:s.ms
	 * @param  int $set_microtime -1 to set micro time, 0 for none, positive for rounding
	 * @return string             formated datetime string with microtime
	 */
	public static function printTime(int $set_microtime = -1): string
	{
		list($microtime, $timestamp) = explode(' ', microtime());
		$string = date("Y-m-d H:i:s", (int)$timestamp);
		// if microtime flag is -1 no round, if 0, no microtime, if >= 1, round that size
		if ($set_microtime == -1) {
			$string .= substr($microtime, 1);
		} elseif ($set_microtime >= 1) {
			// in round case we run this through number format to always get the same amount of digits
			$string .= substr(number_format(round((float)$microtime, $set_microtime), $set_microtime), 1);
		}
		return $string;
	}

	/**
	 * writes a string to a file immediatly, for fast debug output
	 * @param  string  $string string to write to the file
	 * @param  boolean $enter  default true, if set adds a linebreak \n at the end
	 * @return void            has no return
	 */
	public function fdebug(string $string, bool $enter = true): void
	{
		if ($this->debug_filename) {
			$this->fdebugFP();
			if ($enter === true) {
				$string .= "\n";
			}
			$string = "[".$this->printTime()."] [".$this->getPageName(2)."] - ".$string;
			fwrite($this->debug_fp, $string);
			$this->fdebugFP();
		}
	}

	/**
	 * if no debug_fp found, opens a new one; if fp exists close it
	 * @param  string $flag default '', 'o' -> open, 'c' -> close
	 * @return void         has no return
	 */
	private function fdebugFP(string $flag = ''): void
	{
		if (!$this->debug_fp || $flag == 'o') {
			$fn = BASE.LOG.$this->debug_filename;
			$this->debug_fp = @fopen($fn, 'a');
		} elseif ($this->debug_fp || $flag == 'c') {
			fclose($this->debug_fp);
		}
	}

	/**
	 * passes list of level names, to turn on debug
	 * eg $foo->debugFor('print', 'on', array('LOG', 'DEBUG', 'INFO'));
	 * @param  string $type  error, echo, print
	 * @param  string $flag  on/off
	 *         array  $array of levels to turn on/off debug
	 * @return void          has no return
	 */
	public function debugFor(string $type, string $flag): void
	{
		$debug_on = func_get_args();
		array_shift($debug_on); // kick out type
		array_shift($debug_on); // kick out flag (on/off)
		if (count($debug_on) >= 1) {
			foreach ($debug_on as $level) {
				$switch = $type.'_output';
				if ($flag == 'off') {
					$switch .= '_not';
				}
				$this->{$switch}[$level] = 1;
			}
		}
	}

	/**
	 * write debug data to error_msg array
	 * @param  string       $level  id for error message, groups messages together
	 * @param  string       $string the actual error message
	 * @param  bool|boolean $strip  default on false, if set to true,
	 *                              all html tags will be stripped and <br> changed to \n
	 *                              this is only used for debug output
	 * @return void                 has no return
	 */
	public function debug(string $level, string $string, bool $strip = false): void
	{
		if (($this->debug_output[$level] || $this->debug_output_all) && !$this->debug_output_not[$level]) {
			if (!isset($this->error_msg[$level])) {
				$this->error_msg[$level] = '';
			}
			$error_string = '<div>';
			$error_string .= '[<span style="font-weight: bold; color: #5e8600;">'.$this->printTime().'</span>] ';
			$error_string .= '[<span style="font-weight: bold; color: #c56c00;">'.$level.'</span>] ';
			$error_string .= '[<span style="color: #b000ab;">'.$this->host_name.'</span>] ';
			$error_string .= '[<span style="color: #08b369;">'.$this->page_name.'</span>] ';
			$error_string .= '[<span style="color: #0062A2;">'.$this->running_uid.'</span>] ';
			$error_string .= '{<span style="font-style: italic; color: #928100;">'.get_class($this).'</span>} - '.$string;
			$error_string .= "</div><!--#BR#-->";
			if ($strip) {
				// find any <br> and replace them with \n
				$string = str_replace('<br>', "\n", $string);
				// strip rest of html elements
				$string = preg_replace("/(<\/?)(\w+)([^>]*>)/", '', $string);
			}
			// same string put for print (no html crap inside)
			$error_string_print = '['.$this->printTime().'] ['.$this->host_name.'] ['.$this->getPageName(2).'] ['.$this->running_uid.'] {'.get_class($this).'} <'.$level.'> - '.$string;
			$error_string_print .= "\n";
			// write to file if set
			$this->writeErrorMsg($level, $error_string_print);
			// write to error level
			if (($this->echo_output[$level] || $this->echo_output_all) && !$this->echo_output_not[$level]) {
				$this->error_msg[$level] .= $error_string;
			}
		}
	}

	/**
	 * if there is a need to find out which parent method called a child method,
	 * eg for debugging, this function does this
	 * call this method in the child method and you get the parent function that called
	 * @param  int    $level debug level, default 2
	 * @return ?string       null or the function that called the function where this method is called
	 */
	public function getCallerMethod(int $level = 2): ?string
	{
		$traces = debug_backtrace();
		// extended info (later)
		/*$file = $trace[$level]['file'];
		$line = $trace[$level]['line'];
		$object = $trace[$level]['object'];
		if (is_object($object)) {
			$object = get_class($object);
		}
		return "Where called: line $line of $object \n(in $file)";*/
		// sets the start point here, and in level two (the sub call) we find this
		if (isset($traces[$level])) {
			return $traces[$level]['function'];
		}
		return null;
	}

	/**
	 * merges the given error array with the one from this class
	 * only merges visible ones
	 * @param  array  $error_msg error array
	 * @return void              has no return
	 */
	public function mergeErrors(array $error_msg = array()): void
	{
		if (!is_array($error_msg)) {
			$error_msg = array();
		}
		foreach ($error_msg as $level => $msg) {
			$this->error_msg[$level] .= $msg;
		}
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
				if (($this->debug_output[$level] || $this->debug_output_all) && !$this->debug_output_not[$level]) {
					if (($this->echo_output[$level] || $this->echo_output_all) && !$this->echo_output_not[$level]) {
						$string_output .= '<div style="font-size: 12px;">[<span style="font-style: italic; color: #c56c00;">'.$level.'</span>] '.(($string) ? "<b>**** ".$this->htmlent($string)." ****</b>\n" : "").'</div>';
						$string_output .= $temp_debug_output;
					} // echo it out
				} // do printout
			} // for each level
			// create the output wrapper around, so we have a nice formated output per class
			if ($string_output) {
				$string_prefix = '<div style="text-align: left; padding: 5px; font-size: 10px; font-family: sans-serif; border-top: 1px solid black; border-bottom: 1px solid black; margin: 10px 0 10px 0; background-color: white; color: black;">';
				$string_prefix .= '<div style="font-size: 12px;">{<span style="font-style: italic; color: #928100;">'.get_class($this).'</span>}</div>';
				$string_output = $string_prefix.$string_output;
				$string_output .= '<div><span style="font-style: italic; color: #108db3;">Script Run Time:</span> '.$script_end.'</div>';
				$string_output .= '</div>';
			}
		}
		return $string_output;
	}

	/**
	 * writes error msg data to file for current level
	 * @param  string $level        the level to write
	 * @param  string $error_string error string to write
	 * @return void                 has no return
	 */
	private function writeErrorMsg(string $level, string $error_string): void
	{
		if (($this->debug_output[$level] || $this->debug_output_all) && !$this->debug_output_not[$level]) {
			// only write if write is requested
			if (($this->print_output[$level] || $this->print_output_all) && !$this->print_output_not[$level]) {
				// replace all html tags
				// $error_string = preg_replace("/(<\/?)(\w+)([^>]*>)/", "##\\2##", $error_string);
				// $error_string = preg_replace("/(<\/?)(\w+)([^>]*>)/", "", $error_string);
				// replace special line break tag
				// $error_string = str_replace('<!--#BR#-->', "\n", $error_string);

				// init output variable
				$output = $error_string; // output formated error string to output file
				// init base file path
				$fn = BASE.LOG.$this->log_print_file.'.'.$this->log_file_name_ext;
				// log ID prefix settings, if not valid, replace with empty
				if (preg_match("/^[A-Za-z0-9]+$/", $this->log_file_id)) {
					$rpl_string = '_'.$this->log_file_id;
				} else {
					$rpl_string = '';
				}
				$fn = str_replace('##LOGID##', $rpl_string, $fn); // log id (like a log file prefix)

				if ($this->log_per_run) {
					if (isset($GLOBALS['LOG_FILE_UNIQUE_ID'])) {
						$this->log_file_unique_id = $GLOBALS['LOG_FILE_UNIQUE_ID'];
					}
					if (!$this->log_file_unique_id) {
						$GLOBALS['LOG_FILE_UNIQUE_ID'] = $this->log_file_unique_id = date('Y-m-d_His').'_U_'.substr(hash('sha1', uniqid((string)mt_rand(), true)), 0, 8);
					}
					$rpl_string = '_'.$this->log_file_unique_id; // add 8 char unique string
				} else {
					$rpl_string = !$this->log_print_file_date ? '' : '_'.date('Y-m-d'); // add date to file
				}
				$fn = str_replace('##DATE##', $rpl_string, $fn); // create output filename

				$rpl_string = !$this->log_per_level ? '' : '_'.$level; // if request to write to one file
				$fn = str_replace('##LEVEL##', $rpl_string, $fn); // create output filename

				$rpl_string = !$this->log_per_class ? '' : '_'.str_replace('\\', '-', get_class($this)); // set sub class settings
				$fn = str_replace('##CLASS##', $rpl_string, $fn); // create output filename

				$rpl_string = !$this->log_per_page ? '' : '_'.$this->getPageName(1); // if request to write to one file
				$fn = str_replace('##PAGENAME##', $rpl_string, $fn); // create output filename

				// write to file
				// first check if max file size is is set and file is bigger
				if ($this->log_max_filesize > 0 && ((filesize($fn) / 1024) > $this->log_max_filesize)) {
					// for easy purpose, rename file only to attach timestamp, nur sequence numbering
					rename($fn, $fn.'.'.date("YmdHis"));
				}
				$fp = fopen($fn, 'a');
				if ($fp !== false) {
					fwrite($fp, $output);
					fclose($fp);
				} else {
					echo "<!-- could not open file: $fn //-->";
				}
			} // do write to file
		}
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
			$this->error_msg = array();
		} elseif (isset($this->error_msg[$level])) {
			unset($this->error_msg[$level]);
		}
	}

	/**
	 * prints a html formatted (pre) array
	 * @param  array  $array any array
	 * @return string        formatted array for output with <pre> tag added
	 */
	public static function printAr(array $array): string
	{
		return "<pre>".print_r($array, true)."</pre>";
	}

	/**
	 * helper function for PHP file upload error messgaes to messge string
	 * @param  int    $error_code integer _FILE upload error code
	 * @return string                     message string, translated
	 */
	public function fileUploadErrorMessage(int $error_code): string
	{
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;
			default:
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}

	// ****** DEBUG/ERROR FUNCTIONS ******

	// ****** RANDOM KEY GEN ******

	// METHOD: initRandomKeyData
	// PARAMS: none
	// RETURN: none
	// DESC  : sets the random key range with the default values
	/**
	 * sets the random key range with the default values
	 * @return void has no return
	 */
	private function initRandomKeyData()
	{
		// random key generation
		$this->key_range = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
		$this->one_key_length = count($this->key_range);
		// pow($this->one_key_length, 4);
		// default set to 4, should be more than enought (62*62*62*62)
		$this->key_length = 4;
	}

	/**
	 * validates they key length for random string generation
	 * @param  int    $key_length key length
	 * @return bool               true for valid, false for invalid length
	 */
	private function validateRandomKeyLenght(int $key_length): bool
	{
		if (is_numeric($key_length) &&
			$key_length > 0 &&
			$key_length <= $this->max_key_length
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * sets the key length and checks that they key given is valid
	 * if failed it will not change the default key length and return false
	 * @param  int    $key_length key length
	 * @return bool               true/false for set status
	 */
	public function initRandomKeyLength(int $key_length): bool
	{
		// only if valid int key with valid length
		if ($this->validateRandomKeyLenght($key_length) === true) {
			$this->key_length = $key_length;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * creates a random key based on the key_range with key_length
	 * if override key length is set, it will check on valid key and use this
	 * this will not set the class key length variable
	 * @param  int    $key_length key length override, -1 for use default
	 * @return string             random key
	 */
	public function randomKeyGen(int $key_length = -1): string
	{
		$use_key_length = 0;
		// only if valid int key with valid length
		if ($this->validateRandomKeyLenght($key_length) === true) {
			$use_key_length = $key_length;
		} else {
			$use_key_length = $this->key_length;
		}

		return join(
			'',
			array_map(
				function ($value) {
					return $this->key_range[rand(0, $this->one_key_length - 1)];
				},
				range(1, $use_key_length)
			)
		);
	}

	// ****** RANDOM KEY GEN ******

	/**
	 * returns 'checked' or 'selected' if okay
	 * $needle is a var, $haystack an array or a string
	 * **** THE RETURN: VALUE WILL CHANGE TO A DEFAULT NULL IF NOT FOUND ****
	 * @param  array|string $haystack (search in) haystack can be an array or a string
	 * @param  string       $needle   needle (search for)
	 * @param  int          $type     type: 0: returns selected, 1, returns checked
	 * @return ?string                returns checked or selected, else returns null
	 */
	public static function checked($haystack, $needle, int $type = 0): ?string
	{
		if (is_array($haystack)) {
			if (in_array((string)$needle, $haystack)) {
				return (($type) ? "checked" : "selected");
			}
		} else {
			if ($haystack == $needle) {
				return (($type) ? "checked" : "selected");
			}
		}
		return null;
	}

	/**
	 * tries to find mailto:user@bubu.at and changes it into -> <a href="mailto:user@bubu.at">E-Mail senden</a>
	 * or tries to take any url (http, ftp, etc) and transform it into a valid URL
	 * the string is in the format: some url|name#css|, same for email
	 * @param  string $string data to transform to a valud HTML url
	 * @param  string $target target string, default _blank
	 * @return string         correctly formed html url link
	 */
	public function magicLinks(string $string, string $target = "_blank"): string
	{
		$output = $string;
		$protList = array("http", "https", "ftp", "news", "nntp");

		// find urls w/o  protocol
		$output = preg_replace("/([^\/])www\.([\w\.-]+)\.([a-zA-Z]{2,4})/", "\\1http://www.\\2.\\3", $output);
		$output = preg_replace("/([^\/])ftp\.([\w\.-]+)\.([a-zA-Z]{2,4})/", "\\1ftp://ftp.\\2.\\3", $output);

		// remove doubles, generate protocol-regex
		// DIRTY HACK
		$protRegex = "";
		foreach ($protList as $protocol) {
			if ($protRegex)	{
				$protRegex .= "|";
			}
			$protRegex .= "$protocol:\/\/";
		}

		// find urls w/ protocol
		// cs: escaped -, added / for http urls
		// added | |, this time mandatory, todo: if no | |use \\1\\2
		// backslash at the end of a url also allowed now
		// do not touch <.*=".."> things!
		// _1: URL or email
		// _2: atag (>)
		// _3: (_1) part of url or email [main url or email pre @ part]
		// _4: (_2) parameters of url or email post @ part
		// _5: (_3) parameters of url or tld part of email
		// _7: link name/email link name
		// _9: style sheet class
		$self = $this;
		// $this->debug('URL', 'Before: '.$output);
		$output = preg_replace_callback(
			"/(href=\")?(\>)?\b($protRegex)([\w\.\-?&=+%#~,;\/]+)\b([\.\-?&=+%#~,;\/]*)(\|([^\||^#]+)(#([^\|]+))?\|)?/",
			function ($matches) use ($self) {
				return @$self->createUrl($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[7], $matches[9]);
			},
			$output
		);
		// find email-addresses, but not mailto prefix ones
		$output = preg_replace_callback(
			"/(mailto:)?(\>)?\b([\w\.-]+)@([\w\.\-]+)\.([a-zA-Z]{2,4})\b(\|([^\||^#]+)(#([^\|]+))?\|)?/",
			function ($matches) use ($self) {
				return @$self->createEmail($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[7], $matches[9]);
			},
			$output
		);

		$this->debug('URL', 'After: '.$output);
		// we have one slashes after the Protocol -> internal link no domain, strip out the proto
		// $output = preg_replace("/($protRegex)\/(.*)/e", "\\2", $ouput);
		// $this->debug('URL', "$output");

		// post processing
		$output = str_replace("{TARGET}", $target, $output);
		$output = str_replace("##LT##", "<", $output);
		$output = str_replace("##GT##", ">", $output);
		$output = str_replace("##QUOT##", "\"", $output);

		return $output;
	}

	/**
	 * internal function, called by the magic url create functions.
	 * checks if title $_4 exists, if not, set url as title
	 * @param  string $href  url link
	 * @param  string $atag  anchor tag (define both type or url)
	 * @param  string $_1    part of the URL, if atag is set, _1 is not used
	 * @param  string $_2    part of the URL
	 * @param  string $_3    part of the URL
	 * @param  string $name  name for the url, if not given _2 + _3 is used
	 * @param  string $class style sheet
	 * @return string        correct string for url href process
	 */
	private function createUrl($href, $atag, $_1, $_2, $_3, $name, $class): string
	{
		// $this->debug('URL', "1: $_1 - 2: $_2 - $_3 - atag: $atag - name: $name - class: $class");
		// if $_1 ends with //, then we strip $_1 complete & target is also blanked (its an internal link)
		if (preg_match("/\/\/$/", $_1) && preg_match("/^\//", $_2)) {
			$_1 = '';
			$target = '';
		} else {
			$target = '{TARGET}';
		}
		// if it is a link already just return the original link do not touch anything
		if (!$href && !$atag) {
			return "##LT##a href=##QUOT##".$_1.$_2.$_3."##QUOT##".(($class) ? ' class=##QUOT##'.$class.'##QUOT##' : '').(($target) ? " target=##QUOT##".$target."##QUOT##" : '')."##GT##".(($name) ? $name : $_2.$_3)."##LT##/a##GT##";
		} elseif ($href && !$atag) {
			return "href=##QUOT##$_1$_2$_3##QUOT##";
		} elseif ($atag) {
			return $atag.$_2.$_3;
		}
	}

	/**
	 * internal function for createing email, returns data to magic_url method
	 * @param  string $mailto email address
	 * @param  string $atag   atag (define type of url)
	 * @param  string $_1     parts of the email _1 before @, 3_ tld
	 * @param  string $_2     _2 domain part after @
	 * @param  string $_3     _3 tld
	 * @param  string $title  name for the link, if not given use email
	 * @param  string $class  style sheet
	 * @return string         created html email a href string
	 */
	private function createEmail($mailto, $atag, $_1, $_2, $_3, $title, $class)
	{
		$email = $_1."@".$_2.".".$_3;
		if (!$mailto && !$atag) {
			return "##LT##a href=##QUOT##mailto:".$email."##QUOT##".(($class) ? ' class=##QUOT##'.$class.'##QUOT##' : '')."##GT##".(($title) ? $title : $email)."##LT##/a##GT##";
		} elseif ($mailto && !$atag) {
			return "mailto:".$email;
		} elseif ($atag) {
			return $atag.$email;
		}
	}

	/**
	 * get the host name without the port as given by the SELF var
	 * @return string host name
	 */
	public function getHostName(): string
	{
		$port = '';
		if ($_SERVER['HTTP_HOST'] && preg_match("/:/", $_SERVER['HTTP_HOST'])) {
			list($host_name, $port) = explode(":", $_SERVER['HTTP_HOST']);
		} elseif ($_SERVER['HTTP_HOST']) {
			$host_name = $_SERVER['HTTP_HOST'];
		} else {
			$host_name = 'NA';
		}
		$this->host_port = $port ? $port : 80;
		$this->host_name = $host_name;
		// also return for old type call
		return $host_name;
	}

	/**
	 * get the page name of the curronte page
	 * @param  int    $strip_ext 1: strip page file name extension
	 *                           0: keep filename as is
	 *                           2: keep filename as is, but add dirname too
	 * @return string            filename
	 */
	public static function getPageName(int $strip_ext = 0): string
	{
		// get the file info
		$page_temp = pathinfo($_SERVER["PHP_SELF"]);
		if ($strip_ext == 1) {
			return $page_temp['filename'];
		} elseif ($strip_ext == 2) {
			return $_SERVER['PHP_SELF'];
		} else {
			return $page_temp['basename'];
		}
	}

	/**
	 * quick return the extension of the given file name
	 * @param  string $filename file name
	 * @return string           extension of the file name
	 */
	public static function getFilenameEnding(string $filename): string
	{
		$page_temp = pathinfo($filename);
		return $page_temp['extension'];
	}

	/**
	 * searches key = value in an array / array
	 * only returns the first one found
	 * @param  string|int  $needle     needle (search for)
	 * @param  array       $haystack   haystack (search in)
	 * @param  string|null $key_lookin the key to look out for, default empty
	 * @return array                   array with the elements where the needle can be
	 *                                 found in the haystack array
	 */
	public static function arraySearchRecursive($needle, array $haystack, ?string $key_lookin = null): array
	{
		$path = array();
		if (!is_array($haystack)) {
			$haystack = array();
		}
		if ($key_lookin != null &&
			!empty($key_lookin) &&
			array_key_exists($key_lookin, $haystack) &&
			$needle === $haystack[$key_lookin]
		) {
			$path[] = $key_lookin;
		} else {
			foreach ($haystack as $key => $val) {
				if (is_scalar($val) && $val === $needle && empty($key_lookin)) {
					break;
				} elseif (is_scalar($val) && !empty($key_lookin) && $key === $key_lookin && $val == $needle) {
					$path[] = $key;
					break;
				} elseif (is_array($val) && $path = Basic::arraySearchRecursive($needle, $val, $key_lookin)) {
					array_unshift($path, $key);
					break;
				}
			}
		}
		return $path;
	}

	/**
	 * recursive array search function, which returns all found not only the first one
	 * @param  string|int $needle   needle (search for)
	 * @param  array      $haystack haystack (search in)
	 * @param  string|int $key      the key to look for in
	 * @param  array      $path     recursive call for previous path
	 * @return ?array               all array elements paths where the element was found
	 */
	public static function arraySearchRecursiveAll($needle, array $haystack, $key, $path = null): ?array
	{
		if (!isset($path['level'])) {
			$path['level'] = 0;
		}
		if (!isset($path['work'])) {
			$path['work'] = array();
		}
		if (!is_array($haystack)) {
			$haystack = array();
		}

		// go through the array,
		foreach ($haystack as $_key => $_value) {
			if (is_scalar($_value) && $_value == $needle && !$key) {
				// only value matches
				$path['work'][$path['level']] = $_key;
				$path['found'][] = $path['work'];
			} elseif (is_scalar($_value) && $_value == $needle && $_key == $key) {
				// key and value matches
				$path['work'][$path['level']] = $_key;
				$path['found'][] = $path['work'];
			} elseif (is_array($_value)) {
				// add position to working
				$path['work'][$path['level']] = $_key;
				// we will up a level
				$path['level'] += 1;
				// call recursive
				$path = Basic::arraySearchRecursiveAll($needle, $_value, $key, $path);
			}
		}
		// cut all that is >= level
		array_splice($path['work'], $path['level']);
		// step back a level
		$path['level'] -= 1;
		return $path;
	}

	/**
	 * array search simple. looks for key, value combination, if found, returns true
	 * @param  array      $array array(search in)
	 * @param  string|int $key   key (key to search in)
	 * @param  string|int $value value (what to find)
	 * @return bool              true on found, false on not found
	 */
	public static function arraySearchSimple(array $array, $key, $value): bool
	{
		if (!is_array($array)) {
			$array = array();
		}
		foreach ($array as $_key => $_value) {
			// if value is an array, we search
			if (is_array($_value)) {
				// call recursive, and return result if it is true, else continue
				if (($result = Basic::arraySearchSimple($_value, $key, $value)) !== false) {
					return $result;
				}
			} elseif ($_key == $key && $_value == $value) {
				return true;
			}
		}
		// no true returned, not found
		return false;
	}

	/**
	 * correctly recursive merges as an array as array_merge_recursive just glues things together
	 *         array first array to merge
	 *         array second array to merge
	 *         ...   etc
	 *         bool  key flag: true: handle keys as string or int
	 *               default false: all keys are string
	 * @return array|bool merged array
	 */
	public static function arrayMergeRecursive()
	{
		// croak on not enough arguemnts (we need at least two)
		if (func_num_args() < 2) {
			trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
			return false;
		}
		// default key is not string
		$key_is_string = false;
		$arrays = func_get_args();
		// if last is not array, then assume it is trigger for key is always string
		if (!is_array(end($arrays))) {
			if (array_pop($arrays)) {
				$key_is_string = true;
			}
		}
		// check that arrays count is at least two, else we don't have enough to do anything
		if (count($arrays) < 2) {
			trigger_error(__FUNCTION__.' needs two or more array arguments', E_USER_WARNING);
			return false;
		}
		$merged = array();
		while ($arrays) {
			$array = array_shift($arrays);
			if (!is_array($array)) {
				trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
				return false;
			}
			if (!$array) {
				continue;
			}
			foreach ($array as $key => $value) {
				// if string or if key is assumed to be string do key match else add new entry
				if (is_string($key) || $key_is_string === false) {
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
						// $merged[$key] = call_user_func(__METHOD__, $merged[$key], $value, $key_is_string);
						$merged[$key] = Basic::arrayMergeRecursive($merged[$key], $value, $key_is_string);
					} else {
						$merged[$key] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}
		return $merged;
	}

	/**
	 * search for the needle array elements in haystack and return the ones found as an array,
	 * is there nothing found, it returns FALSE (boolean)
	 * @param  array $needle   elements to search for
	 * @param  array $haystack array where the $needle elements should be searched int
	 * @return array|bool      either the found elements or false for nothing found or error
	 */
	public static function inArrayAny(array $needle, array $haystack)
	{
		if (!is_array($needle)) {
			return false;
		}
		if (!is_array($haystack)) {
			return false;
		}
		$found = array();
		foreach ($needle as $element) {
			if (in_array($element, $haystack)) {
				$found[] = $element;
			}
		}
		if (count($found) == 0) {
			return false;
		} else {
			return $found;
		}
	}

	/**
	 * creates out of a normal db_return array an assoc array
	 * @param  array           $db_array return array from the database
	 * @param  string|int|bool $key      key set, false for not set
	 * @param  string|int|bool $value    value set, false for not set
	 * @param  bool            $set_only flag to return all (default), or set only
	 * @return array                     associative array
	 */
	public static function genAssocArray(array $db_array, $key, $value, bool $set_only = false): array
	{
		$ret_array = array();
		// do this to only run count once
		for ($i = 0, $iMax = count($db_array); $i < $iMax; $i ++) {
			// if no key then we make an order reference
			if ($key !== false &&
				$value !== false &&
				(($set_only && $db_array[$i][$value]) || (!$set_only))
			) {
				$ret_array[$db_array[$i][$key]] = $db_array[$i][$value];
			} elseif ($key === false && $value !== false) {
				$ret_array[] = $db_array[$i][$value];
			} elseif ($key !== false && $value === false) {
				$ret_array[$db_array[$i][$key]] = $i;
			}
		}
		return $ret_array;
	}

	/**
	 * [NOTE]: This is an old function and is deprecated
	 * wrapper for join, but checks if input is an array and if not returns null
	 * @param  array  $array        array to convert
	 * @param  string $connect_char connection character
	 * @return string               joined string
	 * @deprecated use join() instead
	 */
	public static function arrayToString(array $array, string $connect_char): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use join()', E_USER_DEPRECATED);
		if (!is_array($array)) {
			$array = array();
		}
		return join($connect_char, $array);
	}

	/**
	 * converts multi dimensional array to a flat array
	 * does NOT preserve keys
	 * @param  array  $array ulti dimensionial array
	 * @return array         flattened array
	 */
	public static function flattenArray(array $array): array
	{
		$return = array();
		array_walk_recursive(
			$array,
			function ($value) use (&$return) {
				$return[] = $value;
			}
		);
		return $return;
	}

	/**
	 * will loop through an array recursivly and write the array keys back
	 * @param  array  $array  multidemnsional array to flatten
	 * @return array          flattened keys array
	 */
	public static function flattenArrayKey(array $array/*, array $return = array()*/): array
	{
		$return = array();
		array_walk_recursive(
			$array,
			function ($value, $key) use (&$return) {
				$return [] = $key;
			}
		);
		return $return;
	}

	/**
	 * searches for key -> value in an array tree and writes the value one level up
	 * this will remove this leaf will all other values
	 * @param  array      $array  array(nested)
	 * @param  string|int $search key to find that has no sub leaf and will be pushed up
	 * @return array              modified, flattened array
	 */
	public static function arrayFlatForKey(array $array, $search): array
	{
		if (!is_array($array)) {
			$array = array();
		}
		foreach ($array as $key => $value) {
			// if it is not an array do just nothing
			if (is_array($value)) {
				// probe it has search key
				if (isset($value[$search])) {
					// set as current
					$array[$key] = $value[$search];
				} else {
					// call up next node down
					// $array[$key] = call_user_func(__METHOD__, $value, $search);
					$array[$key] = Basic::arrayFlatForKey($value, $search);
				}
			}
		}
		return $array;
	}

	/**
	 * wrapper function for mb mime convert, for correct conversion with long strings
	 * @param  string $string   string to encode
	 * @param  string $encoding target encoding
	 * @return string           encoded string
	 */
	public static function __mbMimeEncode(string $string, string $encoding): string
	{
		// set internal encoding, so the mimeheader encode works correctly
		mb_internal_encoding($encoding);
		// if a subject, make a work around for the broken mb_mimencode
		$pos = 0;
		$split = 36; // after 36 single bytes characters, if then comes MB, it is broken
					 // has to 2 x 36 < 74 so the mb_encode_mimeheader 74 hardcoded split does not get triggered
		$_string = '';
		while ($pos < mb_strlen($string, $encoding)) {
			$output = mb_strimwidth($string, $pos, $split, "", $encoding);
			$pos += mb_strlen($output, $encoding);
			// if the strinlen is 0 here, get out of the loop
			if (!mb_strlen($output, $encoding)) {
				$pos += mb_strlen($string, $encoding);
			}
			$_string_encoded = mb_encode_mimeheader($output, $encoding);
			// only make linebreaks if we have mime encoded code inside
			// the space only belongs in the second line
			if ($_string && preg_match("/^=\?/", $_string_encoded)) {
				$_string .= "\n ";
			}
			$_string .= $_string_encoded;
		}
		// strip out any spaces BEFORE a line break
		$string = str_replace(" \n", "\n", $_string);
		return $string;
	}

	/**
	 * converts bytes into formated string with KB, MB, etc
	 * @param  string|int|float $number bytes as string int or pure int
	 * @param  bool             $space  true (default) to add space between number and suffix
	 * @return string                   converted byte number (float) with suffix
	 */
	public static function byteStringFormat($number, bool $space = true): string
	{
		if (is_numeric($number) && $number > 0) {
			// labels in order of size
			$labels = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
			// calc file size, round down too two digits, add label based max change
			return round((float)$number / pow(1024, ($i = floor(log((float)$number, 1024)))), 2).($space ? ' ' : '').(isset($labels[(int)$i]) ? $labels[(int)$i] : '>EB');
		}
		return (string)$number;
	}

	/**
	 * calculates the bytes based on a string with nnG, nnGB, nnM, etc
	 * if the number has a non standard thousand seperator ","" inside, the second
	 * flag needs to be set true (eg german style notaded numbers)
	 * @param  string|int|float $number       any string or number to convert
	 * @param  bool             $dot_thousand default is ".", set true for ","
	 * @return string|int|float               converted value or original value
	 */
	public static function stringByteFormat($number, bool $dot_thousand = false)
	{
		// detects up to exo bytes
		preg_match("/([\d.,]*)\s?(eb|pb|tb|gb|mb|kb|e|p|t|g|m|k|b)$/", strtolower($number), $matches);
		if (isset($matches[1]) && isset($matches[2])) {
			// $last = strtolower($number[strlen($number) - 1]);
			if ($dot_thousand === false) {
				$number = str_replace(',', '', $matches[1]);
			} else {
				$number = str_replace('.', '', $matches[1]);
			}
			$number = (float)trim($number);
			// match string in type to calculate
			switch ($matches[2]) {
				// exo bytes
				case 'e':
				case 'eb':
					$number *= 1024;
				// peta bytes
				case 'p':
				case 'pb':
					$number *= 1024;
				// tera bytes
				case 't':
				case 'tb':
					$number *= 1024;
				// giga bytes
				case 'g':
				case 'gb':
					$number *= 1024;
				// mega bytes
				case 'm':
				case 'mb':
					$number *= 1024;
				// kilo bytes
				case 'k':
				case 'kb':
					$number *= 1024;
					break;
			}
			$number = (int)round($number, 0);
		}
		// if not matching return as is
		return $number;
	}

	/**
	 * a simple wrapper for the date format
	 * @param  int|float $timestamp  unix timestamp
	 * @param  bool      $show_micro show the micro time (default false)
	 * @return string                formated date+time in Y-M-D h:m:s ms
	 */
	public static function dateStringFormat($timestamp, bool $show_micro = false): string
	{
		// split up the timestamp, assume . in timestamp
		// array pad $ms if no microtime
		list ($timestamp, $ms) = array_pad(explode('.', (string)round($timestamp, 4)), 2, null);
		$string = date("Y-m-d H:i:s", (int)$timestamp);
		if ($show_micro && $ms) {
			$string .= ' '.$ms.'ms';
		}
		return $string;
	}

	/**
	 * formats a timestamp into interval, not into a date
	 * @param  string|int|float $timestamp  interval in seconds and optional float micro seconds
	 * @param  bool             $show_micro show micro seconds, default true
	 * @return string                       interval formatted string or string as is
	 */
	public static function timeStringFormat($timestamp, bool $show_micro = true): string
	{
		// check if the timestamp has any h/m/s/ms inside, if yes skip
		if (!preg_match("/(h|m|s|ms)/", (string)$timestamp)) {
			$ms = 0;
			list ($timestamp, $ms) = explode('.', (string)round($timestamp, 4));
			$timegroups = array(86400, 3600, 60, 1);
			$labels = array('d', 'h', 'm', 's');
			$time_string = '';
			for ($i = 0, $iMax = count($timegroups); $i < $iMax; $i ++) {
				$output = floor((float)$timestamp / $timegroups[$i]);
				$timestamp = (float)$timestamp % $timegroups[$i];
				// output has days|hours|min|sec
				if ($output || $time_string) {
					$time_string .= $output.$labels[$i].(($i + 1) != count($timegroups) ? ' ' : '');
				}
			}
			// if we have ms and it has leading zeros, remove them
			$ms = preg_replace("/^0+/", '', $ms);
			// add ms if there
			if ($show_micro) {
				$time_string .= ' '.(!$ms ? 0 : $ms).'ms';
			} elseif (!$time_string) {
				$time_string .= (!$ms ? 0 : $ms).'ms';
			}
		} else {
			$time_string = $timestamp;
		}
		return $time_string;
	}

	/**
	 * does a reverse of the TimeStringFormat and converts the string from
	 * xd xh xm xs xms to a timestamp.microtime format
	 * @param  string|int|float $timestring formatted interval
	 * @return string|int|float             converted float interval, or string as is
	 */
	public static function stringToTime($timestring)
	{
		$timestamp = 0;
		if (preg_match("/(d|h|m|s|ms)/", $timestring)) {
			// pos for preg match read + multiply factor
			$timegroups = array(2 => 86400, 4 => 3600, 6 => 60, 8 => 1);
			$matches = array();
			// preg match: 0: full strsing
			// 2, 4, 6, 8 are the to need values
			preg_match("/^((\d+)d ?)?((\d+)h ?)?((\d+)m ?)?((\d+)s ?)?((\d+)ms)?$/", $timestring, $matches);
			// multiply the returned matches and sum them up. the last one (ms) is added with .
			foreach ($timegroups as $i => $time_multiply) {
				if (is_numeric($matches[$i])) {
					$timestamp += (float)$matches[$i] * $time_multiply;
				}
			}
			if (is_numeric($matches[10])) {
				$timestamp .= '.'.$matches[10];
			}
			return $timestamp;
		} else {
			return $timestring;
		}
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 * @param  string $date a date string in the format YYYY-MM-DD
	 * @return bool         true if valid date, false if date not valid
	 */
	public static function checkDate($date): bool
	{
		if (!$date) {
			return false;
		}
		list ($year, $month, $day) = preg_split("/[\/-]/", $date);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate((int)$month, (int)$day, (int)$year)) {
			return false;
		}
		return true;
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 * @param  string $datetime date (YYYY-MM-DD) + time (HH:MM:SS), SS can be dropped
	 * @return bool             true if valid date, false if date not valid
	 */
	public static function checkDateTime($datetime): bool
	{
		if (!$datetime) {
			return false;
		}
		list ($year, $month, $day, $hour, $min, $sec) = preg_split("/[\/\- :]/", $datetime);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate((int)$month, (int)$day, (int)$year)) {
			return false;
		}
		if (!is_numeric($hour) || !is_numeric($min)) {
			return false;
		}
		if (($hour < 0 || $hour > 24) ||
			($min < 0 || $min > 60) ||
			(is_numeric($sec) && ($sec < 0 || $sec > 60))
		) {
			return false;
		}
		return true;
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
	 */
	public static function compareDate($start_date, $end_date)
	{
		// pre check for empty or wrong
		if ($start_date == '--' || $end_date == '--' || !$start_date || !$end_date) {
			return false;
		}

		// splits the data up with / or -
		list ($start_year, $start_month, $start_day) = preg_split('/[\/-]/', $start_date);
		list ($end_year, $end_month, $end_day) = preg_split('/[\/-]/', $end_date);
		// check that month & day are two digits and then combine
		foreach (array('start', 'end') as $prefix) {
			foreach (array('month', 'day') as $date_part) {
				$_date = $prefix.'_'.$date_part;
				if ($$_date < 10 && !preg_match("/^0/", $$_date)) {
					$$_date = '0'.$$_date;
				}
			}
			$_date = $prefix.'_date';
			$$_date = '';
			foreach (array('year', 'month', 'day') as $date_part) {
				$_sub_date = $prefix.'_'.$date_part;
				$$_date .= $$_sub_date;
			}
		}
		// now do the compare
		if ($start_date < $end_date) {
			return -1;
		} elseif ($start_date == $end_date) {
			return 0;
		} elseif ($start_date > $end_date) {
			return 1;
		}
	}

	/**
	 * compares the two dates + times. if seconds missing in one set, add :00, converts / to -
	 * returns int/bool in:
	 *     -1 if the first date is smaller the last
	 *     0 if both are equal
	 *     1 if the end date is bigger than the last
	 *     false if no valid date/times chould be found
	 * @param  string $start_datetime start date/time in YYYY-MM-DD HH:mm:ss
	 * @param  string $end_datetime   end date/time in YYYY-MM-DD HH:mm:ss
	 * @return int|bool               false for error or -1/0/1 as difference
	 */
	public static function compareDateTime($start_datetime, $end_datetime)
	{
		// pre check for empty or wrong
		if ($start_datetime == '--' || $end_datetime == '--' || !$start_datetime || !$end_datetime) {
			return false;
		}
		$start_timestamp = strtotime($start_datetime);
		$end_timestamp = strtotime($end_datetime);
		if ($start_timestamp < $end_timestamp) {
			return -1;
		} elseif ($start_timestamp == $end_timestamp) {
			return 0;
		} elseif ($start_timestamp > $end_timestamp) {
			return 1;
		}
	}

	/**
	 * calculates the days between two dates
	 * return: overall days, week days, weekend days as array 0...2 or named
	 * as overall, weekday and weekend
	 * @param  string $start_date   valid start date (y/m/d)
	 * @param  string $end_date     valid end date (y/m/d)
	 * @param  bool   $return_named return array type, false (default), true for named
	 * @return array                0/overall, 1/weekday, 2/weekend
	 */
	public static function calcDaysInterval($start_date, $end_date, bool $return_named = false): array
	{
		// pos 0 all, pos 1 weekday, pos 2 weekend
		$days = array();
		$start = new \DateTime($start_date);
		$end = new \DateTime($end_date);
		// so we include the last day too, we need to add +1 second in the time
		$end->setTime(0, 0, 1);

		$days[0] = $end->diff($start)->days;
		$days[1] = 0;
		$days[2] = 0;

		$period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

		foreach ($period as $dt) {
			$curr = $dt->format('D');
			if ($curr == 'Sat' || $curr == 'Sun') {
				$days[2] ++;
			} else {
				$days[1] ++;
			}
		}
		if ($return_named === true) {
			return array(
				'overall' => $days[0],
				'weekday' => $days[1],
				'weekend' => $days[2]
			);
		} else {
			return $days;
		}
	}

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
		// get image type flags
		$image_types = array(
			1 => 'gif',
			2 => 'jpg',
			3 => 'png'
		);
		$return_data = false;
		$CONVERT = '';
		// if CONVERT is not defined, abort
		/** @phan-suppress-next-line PhanUndeclaredConstant */
		if (defined('CONVERT') && is_executable(CONVERT)) {
			/** @phan-suppress-next-line PhanUndeclaredConstant */
			$CONVERT = CONVERT;
		} else {
			return $return_data;
		}
		if (!empty($cache_source)) {
			$tmp_src = $cache_source;
		} else {
			$tmp_src = BASE.TMP;
		}
		// check if pic has a path, and override next sets
		if (strstr($pic, '/') === false) {
			if (empty($path)) {
				$path = BASE;
			}
			$filename = $path.MEDIA.PICTURES.$pic;
		} else {
			$filename = $pic;
			// and get the last part for pic (the filename)
			$tmp = explode('/', $pic);
			$pic = $tmp[(count($tmp) - 1)];
		}
		// does this picture exist and is it a picture
		if (file_exists($filename) && is_file($filename)) {
			list($width, $height, $type) = getimagesize($filename);
			$convert_prefix = '';
			$create_file = false;
			$delete_filename = '';
			// check if we can skip the PDF creation: if we have size, if do not have type, we assume type png
			if (!$type && is_numeric($size_x) && is_numeric($size_y)) {
				$check_thumb = $tmp_src.'thumb_'.$pic.'_'.$size_x.'x'.$size_y.'.'.$image_types[3];
				if (!is_file($check_thumb)) {
					$create_file = true;
				} else {
					$type = 3;
				}
			}
			// if type is not in the list, but returns as PDF, we need to convert to JPEG before
			if (!$type)	{
				// is this a PDF, if no, return from here with nothing
				$convert_prefix = 'png:';
				# TEMP convert to PNG, we then override the file name
				$convert_string = $CONVERT.' '.$filename.' '.$convert_prefix.$filename.'_TEMP';
				$status = exec($convert_string, $output, $return);
				$filename .= '_TEMP';
				// for delete, in case we need to glob
				$delete_filename = $filename;
				// find file, if we can't find base name, use -0 as the first one (ignore other pages in multiple ones)
				if (!is_file($filename)) {
					$filename .= '-0';
				}
				list($width, $height, $type) = getimagesize($filename);
			}
			// if no size given, set size to original
			if (!$size_x || $size_x < 1 || !is_numeric($size_x)) {
				$size_x = $width;
			}
			if (!$size_y || $size_y < 1 || !is_numeric($size_y)) {
				$size_y = $height;
			}
			$thumb = 'thumb_'.$pic.'_'.$size_x.'x'.$size_y.'.'.$image_types[$type];
			$thumbnail = $tmp_src.$thumb;
			// check if we already have this picture converted
			if (!is_file($thumbnail) || $clear_cache == true) {
				// convert the picture
				if ($width > $size_x) {
					$convert_string = $CONVERT.' -geometry '.$size_x.'x '.$filename.' '.$thumbnail;
					$status = exec($convert_string, $output, $return);
					// get the size of the converted data, if converted
					if (is_file($thumbnail)) {
						list ($width, $height, $type) = getimagesize($thumbnail);
					}
				}
				if ($height > $size_y) {
					$convert_string = $CONVERT.' -geometry x'.$size_y.' '.$filename.' '.$thumbnail;
					$status = exec($convert_string, $output, $return);
				}
			}
			if (!is_file($thumbnail)) {
				copy($filename, $thumbnail);
			}
			$return_data = $thumb;
			// if we have a delete filename, delete here with glob
			if ($delete_filename) {
				array_map('unlink', glob($delete_filename.'*'));
			}
		} else {
			if ($dummy && strstr($dummy, '/') === false) {
				// check if we have the "dummy" image flag set
				$filename = PICTURES.ICONS.strtoupper($dummy).".png";
				if ($dummy && file_exists($filename) && is_file($filename)) {
					$return_data = $filename;
				} else {
					$return_data = false;
				}
			} else {
				$filename = $dummy;
			}
		}
		return $return_data;
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
		$thumbnail = false;
		// $this->debug('IMAGE PREPARE', "FILE: $filename (exists ".(string)file_exists($filename)."), WIDTH: $thumb_width, HEIGHT: $thumb_height");
		// check that input image exists and is either jpeg or png
		// also fail if the basic CACHE folder does not exist at all
		if (file_exists($filename) &&
			is_dir(BASE.LAYOUT.CONTENT_PATH.CACHE) &&
			is_writable(BASE.LAYOUT.CONTENT_PATH.CACHE)
		) {
			// $this->debug('IMAGE PREPARE', "FILENAME OK, THUMB WIDTH/HEIGHT OK");
			list($inc_width, $inc_height, $img_type) = getimagesize($filename);
			$thumbnail_write_path = null;
			$thumbnail_web_path = null;
			// path set first
			if ($img_type == IMG_JPG ||
				$img_type == IMG_PNG ||
				$create_dummy === true
			) {
				// $this->debug('IMAGE PREPARE', "IMAGE TYPE OK: ".$inc_width.'x'.$inc_height);
				// set thumbnail paths
				$thumbnail_write_path = BASE.LAYOUT.CONTENT_PATH.CACHE.IMAGES;
				$thumbnail_web_path = LAYOUT.CACHE.IMAGES;
				// if images folder in cache does not exist create it, if failed, fall back to base cache folder
				if (!is_dir($thumbnail_write_path)) {
					if (false === mkdir($thumbnail_write_path)) {
						$thumbnail_write_path = BASE.LAYOUT.CONTENT_PATH.CACHE;
						$thumbnail_web_path = LAYOUT.CACHE;
					}
				}
			}
			// do resize or fall back on dummy run
			if ($img_type == IMG_JPG ||
				$img_type == IMG_PNG
			) {
				// if missing width or height in thumb, use the set one
				if ($thumb_width == 0) {
					$thumb_width = $inc_width;
				}
				if ($thumb_height == 0) {
					$thumb_height = $inc_height;
				}
				// check resize parameters
				if ($inc_width > $thumb_width || $inc_height > $thumb_height) {
					$thumb_width_r = 0;
					$thumb_height_r = 0;
					// we need to keep the aspect ration on longest side
					if (($inc_height > $inc_width &&
						// and the height is bigger than thumb set
							$inc_height > $thumb_height) ||
						// or the height is smaller or equal width
						// but the width for the thumb is equal to the image height
						($inc_height <= $inc_width &&
							$inc_width == $thumb_width
						)
					) {
						// $this->debug('IMAGE PREPARE', 'HEIGHT > WIDTH');
						$ratio = $inc_height / $thumb_height;
						$thumb_width_r = (int)ceil($inc_width / $ratio);
						$thumb_height_r = $thumb_height;
					} else {
						// $this->debug('IMAGE PREPARE', 'WIDTH > HEIGHT');
						$ratio = $inc_width / $thumb_width;
						$thumb_width_r = $thumb_width;
						$thumb_height_r = (int)ceil($inc_height / $ratio);
					}
					// $this->debug('IMAGE PREPARE', "Ratio: $ratio, Target size $thumb_width_r x $thumb_height_r");
					// set output thumbnail name
					$thumbnail = 'thumb-'.pathinfo($filename)['filename'].'-'.$thumb_width_r.'x'.$thumb_height_r;
					if ($use_cache === false ||
						!file_exists($thumbnail_write_path.$thumbnail)
					) {
						// image, copy source image, offset in image, source x/y, new size, source image size
						$thumb = imagecreatetruecolor($thumb_width_r, $thumb_height_r);
						if ($img_type == IMG_PNG) {
							// preservere transaprency
							imagecolortransparent(
								$thumb,
								imagecolorallocatealpha($thumb, 0, 0, 0, 127)
							);
							imagealphablending($thumb, false);
							imagesavealpha($thumb, true);
						}
						$source = null;
						switch ($img_type) {
							case IMG_JPG:
								$source = imagecreatefromjpeg($filename);
								break;
							case IMG_PNG:
								$source = imagecreatefrompng($filename);
								break;
						}
						// check that we have a source image resource
						if ($source !== null) {
							// resize no shift
							if ($high_quality === true) {
								imagecopyresized($thumb, $source, 0, 0, 0, 0, $thumb_width_r, $thumb_height_r, $inc_width, $inc_height);
							} else {
								imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumb_width_r, $thumb_height_r, $inc_width, $inc_height);
							}
							// write file
							switch ($img_type) {
								case IMG_JPG:
									imagejpeg($thumb, $thumbnail_write_path.$thumbnail, $jpeg_quality);
									break;
								case IMG_PNG:
									imagepng($thumb, $thumbnail_write_path.$thumbnail);
									break;
							}
							// free up resources (in case we are called in a loop)
							imagedestroy($source);
							imagedestroy($thumb);
						} else {
							$thumbnail = false;
						}
					}
				} else {
					// we just copy over the image as is, we never upscale
					$thumbnail = 'thumb-'.pathinfo($filename)['filename'].'-'.$inc_width.'x'.$inc_height;
					if ($use_cache === false ||
						!file_exists($thumbnail_write_path.$thumbnail)
					) {
						copy($filename, $thumbnail_write_path.$thumbnail);
					}
				}
				// add output path
				if ($thumbnail !== false) {
					$thumbnail = $thumbnail_web_path.$thumbnail;
				}
			} elseif ($create_dummy === true) {
				// create dummy image in the thumbnail size
				// if one side is missing, use the other side to create a square
				if (!$thumb_width) {
					$thumb_width = $thumb_height;
				}
				if (!$thumb_height) {
					$thumb_height = $thumb_width;
				}
				// do we have an image already?
				$thumbnail = 'thumb-'.pathinfo($filename)['filename'].'-'.$thumb_width.'x'.$thumb_height;
				if ($use_cache === false ||
					!file_exists($thumbnail_write_path.$thumbnail)
				) {
					// if both are unset, set to 250
					if ($thumb_height == 0) {
						$thumb_height = 250;
					}
					if ($thumb_width == 0) {
						$thumb_width = 250;
					}
					$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
					// add outside border px = 5% (rounded up)
					// eg 50px -> 2.5px
					$gray = imagecolorallocate($thumb, 200, 200, 200);
					$white = imagecolorallocate($thumb, 255, 255, 255);
					// fill gray background
					imagefill($thumb, 0, 0, $gray);
					// now create rectangle
					if (imagesx($thumb) < imagesy($thumb)) {
						$width = (int)round(imagesx($thumb) / 100 * 5);
					} else {
						$width = (int)round(imagesy($thumb) / 100 * 5);
					}
					imagefilledrectangle($thumb, 0 + $width, 0 + $width, imagesx($thumb) - $width, imagesy($thumb) - $width, $white);
					// add "No valid images source"
					// OR add circle
					// * find center
					// * width/height is 75% of size - border
					// smaller size is taken
					$base_width = imagesx($thumb) > imagesy($thumb) ? imagesy($thumb) : imagesx($thumb);
					// get 75% width
					$cross_width = (int)round((($base_width - ($width * 2)) / 100 * 75) / 2);
					$center_x = (int)round(imagesx($thumb) / 2);
					$center_y = (int)round(imagesy($thumb) / 2);
					imagefilledellipse($thumb, $center_x, $center_y, $cross_width, $cross_width, $gray);
					// find top left and bottom left for first line
					imagepng($thumb, $thumbnail_write_path.$thumbnail);
				}
				// add web path
				$thumbnail = $thumbnail_web_path.$thumbnail;
			}
		}
		// either return false or the thumbnail name + output path web
		return $thumbnail;
	}

	/**
	 * reads the rotation info of an file and rotates it to be correctly upright
	 * this is done because not all software honers the exit Orientation flag
	 * only works with jpg or png
	 * @param  string $filename path + filename to rotate. This file must be writeable
	 * @return void
	 */
	public function correctImageOrientation($filename): void
	{
		if (function_exists('exif_read_data') && is_writeable($filename)) {
			list($inc_width, $inc_height, $img_type) = getimagesize($filename);
			// add @ to avoid "file not supported error"
			$exif = @exif_read_data($filename);
			$orientation = null;
			$img = null;
			if ($exif && isset($exif['Orientation'])) {
				$orientation = $exif['Orientation'];
			}
			if ($orientation != 1) {
				$this->debug('IMAGE FILE ROTATE', 'Need to rotate image ['.$filename.'] from: '.$orientation);
				switch ($img_type) {
					case IMG_JPG:
						$img = imagecreatefromjpeg($filename);
						break;
					case IMG_PNG:
						$img = imagecreatefrompng($filename);
						break;
				}
				$deg = 0;
				// 1 top, 6: left, 8: right, 3: bottom
				switch ($orientation) {
					case 3:
						$deg = 180;
						break;
					case 6:
						$deg = -90;
						break;
					case 8:
						$deg = 90;
						break;
				}
				if ($img !== null) {
					if ($deg) {
						$img = imagerotate($img, $deg, 0);
					}
					// then rewrite the rotated image back to the disk as $filename
					switch ($img_type) {
						case IMG_JPG:
							imagejpeg($img, $filename);
							break;
						case IMG_PNG:
							imagepng($img, $filename);
							break;
					}
					// clean up image if we have an image
					imagedestroy($img);
				}
			} // only if we need to rotate
		} // function exists & file is writeable, else do nothing
	}

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
	 * @return bool|array            false if no error or array with failed characters
	 */
	public function checkConvertEncoding(string $string, string $from_encoding, string $to_encoding)
	{
		// convert to target encoding and convert back
		$temp = mb_convert_encoding($string, $to_encoding, $from_encoding);
		$compare = mb_convert_encoding($temp, $from_encoding, $to_encoding);
		// if string does not match anymore we have a convert problem
		if ($string != $compare) {
			$failed = array();
			// go through each character and find the ones that do not match
			for ($i = 0, $iMax = mb_strlen($string, $from_encoding); $i < $iMax; $i ++) {
				$char = mb_substr($string, $i, 1, $from_encoding);
				$r_char = mb_substr($compare, $i, 1, $from_encoding);
				// the ord 194 is a hack to fix the IE7/IE8 bug with line break and illegal character
				// $this->debug('CHECK CONVERTT', '['.$this->mbErrorChar.'] O: '.$char.', C: '.$r_char);
				if ((($char != $r_char && !$this->mbErrorChar) || ($char != $r_char && $r_char == $this->mbErrorChar && $this->mbErrorChar)) && ord($char) != 194) {
					$this->debug('CHARS', "'".$char."'".' == '.$r_char.' ('.ord($char).')');
					$failed[] = $char;
				}
			}
			return $failed;
		} else {
			return false;
		}
	}

	/**
	 * detects the source encoding of the string and if doesn't match to the given target encoding it convert is
	 * @param  string $string          string to convert
	 * @param  string $to_encoding     target encoding
	 * @param  string $source_encoding optional source encoding, will try to auto detect
	 * @return string                  encoding converted string
	 */
	public static function convertEncoding(string $string, string $to_encoding, string $source_encoding = ''): string
	{
		// set if not given
		if (!$source_encoding) {
			$source_encoding = mb_detect_encoding($string);
		}
		if ($source_encoding != $to_encoding) {
			if ($source_encoding) {
				$string = mb_convert_encoding($string, $to_encoding, $source_encoding);
			} else {
				$string = mb_convert_encoding($string, $to_encoding);
			}
		}
		return $string;
	}

	/**
	 * checks php version and if >=5.2.7 it will flip the string
	 * @param  string $string string to crc
	 * @return string         crc32b hash (old type)
	 */
	public function __crc32b(string $string): string
	{
		// do normal hash crc32b
		$string = hash('crc32b', $string);
		// if bigger than 5.2.7, we need to "unfix" the fix
		if (self::checkPHPVersion('5.2.7')) {
			// flip it back to old (two char groups)
			$string = preg_replace("/^([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})$/", "$4$3$2$1", $string);
		}
		return $string;
	}

	/**
	 * replacement for __crc32b call
	 * @param  string $string  string to hash
	 * @param  bool   $use_sha use sha instead of crc32b (default false)
	 * @return string          hash of the string
	 */
	public function __sha1Short(string $string, bool $use_sha = false): string
	{
		if ($use_sha) {
			// return only the first 9 characters
			return substr(hash('sha1', $string), 0, 9);
		} else {
			return $this->__crc32b($string);
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
	 */
	public function __hash(string $string, string $hash_type = 'adler32'): string
	{
		if (!in_array($hash_type, array('adler32', 'fnv132', 'fnv1a32', 'joaat'))) {
			$hash_type = 'adler32';
		}
		return hash($hash_type, $string);
	}

	/**
	 * checks if running PHP version matches given PHP version (min or max)
	 * @param  string $min_version minimum version as string (x, x.y, x.y.x)
	 * @param  string $max_version optional maximum version as string (x, x.y, x.y.x)
	 * @return bool                true if ok, false if not matching version
	 */
	public static function checkPHPVersion(string $min_version, string $max_version = ''): bool
	{
		// exit with false if the min/max strings are wrong
		if (!preg_match("/^\d{1}(\.\d{1})?(\.\d{1,2})?$/", $min_version)) {
			return false;
		}
		// max is only chcked if it is set
		if ($max_version && !preg_match("/^\d{1}(\.\d{1})?(\.\d{1,2})?$/", $max_version)) {
			return false;
		}
		// split up the version strings to calc the compare number
		$version = explode('.', $min_version);
		$min_version = (int)$version[0] * 10000 + (int)$version[1] * 100 + (int)$version[2];
		if ($max_version) {
			$version = explode('.', $max_version);
			$max_version = (int)$version[0] * 10000 + (int)$version[1] * 100 + (int)$version[2];
			// drop out if min is bigger max, equal size is okay, that would be only THIS
			if ($min_version > $max_version) {
				return false;
			}
		}
		// set the php version id
		if (!defined('PHP_VERSION_ID')) {
			$version = explode('.', phpversion());
			// creates something like 50107
			define('PHP_VERSION_ID', (int)$version[0] * 10000 + (int)$version[1] * 100 + (int)$version[2]);
		}
		// check if matching for version
		if ($min_version && !$max_version) {
			if (PHP_VERSION_ID >= $min_version) {
				return true;
			}
		} elseif ($min_version && $max_version) {
			if (PHP_VERSION_ID >= $min_version && PHP_VERSION_ID <= $max_version) {
				return true;
			}
		}
		// if no previous return, fail
		return false;
	}

	/**
	 * creates psuedo random uuid v4
	 * Code take from class here:
	 * https://www.php.net/manual/en/function.uniqid.php#94959
	 * @return string pseudo random uuid v4
	 */
	public static function uuidv4(): string
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			// 48 bits for "node"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

	// [!!! DEPRECATED !!!]
	// ALL crypt* methids are DEPRECATED and SHALL NOT BE USED
	// use the new password* instead

	// [!!! DEPRECATED !!!] -> passwordInit
	/**
	 * inits crypt settings for the crypt functions
	 * this function NEEDS (!) to be called BEFORE any of the crypt functions is called
	 * there is no auto init for this at the moment
	 * @return void has not return
	 * @deprecated use passwordInit instead
	 */
	private function cryptInit()
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use passwordInit', E_USER_DEPRECATED);
		// SET CRYPT SALT PREFIX:
		// the prefix string is defined by what the server can do
		// first we check if we can do blowfish, if not we try md5 and then des
		// WARNING: des is very bad, only first 6 chars get used for the password
		// MD5 is a bit better but is already broken
		// problem with PHP < 5.3 is that you mostly don't have access to blowfish
		if (CRYPT_BLOWFISH == 1 || self::checkPHPVersion('5.3.0')) {
			// blowfish salt prefix
			// for < 5.3.7 use the old one for anything newer use the new version
			if (self::checkPHPVersion('5.3.7')) {
				$this->cryptSaltPrefix = '$2y$';
			} else {
				$this->cryptSaltPrefix = '$2a$';
			}
			// add the iteration cost prefix (currently fixed 07)
			$this->cryptSaltPrefix .= chr(ord('0') + $this->cryptIterationCost / 10);
			$this->cryptSaltPrefix .= chr(ord('0') + $this->cryptIterationCost % 10);
			$this->cryptSaltPrefix .= '$';
			$this->cryptSaltSuffix = '$';
		} else {
			// any version lower 5.3 we do check
			if (CRYPT_MD5 == 1) {
				$this->cryptSaltPrefix = '$1$';
				$this->cryptSaltSize = 6;
				$this->cryptSaltSuffix = '$';
			} elseif (CRYPT_STD_DES == 1) {
				// so I know this is standard DES, I prefix this with $ and have only one random char
				$this->cryptSaltPrefix = '$';
				$this->cryptSaltSize = 1;
				$this->cryptSaltSuffix = '$';
			} else {
				// emergency fallback
				$this->cryptSaltPrefix = '$0';
				$this->cryptSaltSuffix = '$';
			}
		}
	}

	// [!!! DEPRECATED !!!] -> passwordInit
	/**
	 * creates a random string from alphanumeric characters: A-Z a-z 0-9 ./
	 * @param  integer $nSize random string length, default is 22 (for blowfish crypt)
	 * @return string         random string
	 * @deprecated use passwordInit instead
	 */
	private function cryptSaltString(int $nSize = 22): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use passwordInit', E_USER_DEPRECATED);
		// A-Z is 65,90
		// a-z is 97,122
		// 0-9 is 48,57
		// ./ is 46,47 (so first lower limit is 46)
		$min = array(46, 65, 97);
		$max = array(57, 90, 122);
		$chars = array();
		for ($i = 0, $iMax = count($min); $i < $iMax; $i ++) {
			for ($j = $min[$i]; $j <= $max[$i]; $j ++) {
				$chars[] = chr($j);
			}
		}
		// max should be 63 for this case
		$max_rand = count($chars) - 1;
		$salt_string = '';
		// create the salt part
		for ($i = 1; $i <= $nSize; $i ++) {
			$salt_string .= $chars[mt_rand(0, $max_rand)];
		}
		return $salt_string;
	}

	// [!!! DEPRECATED !!!] -> passwordSet
	/**
	 * encrypts the string with blowfish and returns the full string + salt part that needs to be stored somewhere (eg DB)
	 * @param  string $string string to be crypted (one way)
	 * @return string         encrypted string
	 * @deprecated use passwordSet instead
	 */
	public function cryptString(string $string): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use passwordSet', E_USER_DEPRECATED);
		// the crypt prefix is set in the init of the class
		// uses the random string method to create the salt
		// suppress deprecated error, as this is an internal call
		/** @phan-suppress-next-line PhanDeprecatedFunction */
		return crypt($string, $this->cryptSaltPrefix.$this->cryptSaltString($this->cryptSaltSize).$this->cryptSaltSuffix);
	}

	// [!!! DEPRECATED !!!] -> passwordVerify
	/**
	 * compares the string with the crypted one, is counter method to cryptString
	 * @param  string $string plain string (eg password)
	 * @param  string $crypt  full crypted string (from cryptString
	 * @return bool           true on matching or false for not matching
	 * @deprecated use passwordVerify instead
	 */
	public function verifyCryptString(string $string, string $crypt): bool
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use passwordVerify', E_USER_DEPRECATED);
		// the full crypted string needs to be passed on to the salt, so the init (for blowfish) and salt are passed on
		if (crypt($string, $crypt) == $crypt) {
			return true;
		} else {
			return false;
		}
	}

	// *** BETTER PASSWORD OPTIONS, must be used ***
	/**
	 * inits the password options set
	 * currently this is et empty, and the default options are used
	 * @return void has no reutrn
	 */
	private function passwordInit(): void
	{
		// set default password cost: use default set automatically
		$this->password_options = array(
			// 'cost' => PASSWORD_BCRYPT_DEFAULT_COST
		);
	}

	// METHOD: passwordSet
	// PARAMS: password
	// RETURN: hashed password
	// DESC  : creates the password hash
	/**
	 * creates the password hash
	 * @param  string $password password
	 * @return string           hashed password
	 */
	public function passwordSet(string $password): string
	{
		// always use the PHP default for the password
		// password options ca be set in the password init, but should be kept as default
		return password_hash($password, PASSWORD_DEFAULT, $this->password_options);
	}

	/**
	 * checks if the entered password matches the hash
	 * @param  string $password password
	 * @param  string $hash     password hash
	 * @return bool             true or false
	 */
	public function passwordVerify(string $password, string $hash): bool
	{
		if (password_verify($password, $hash)) {
			return true;
		} else {
			return false;
		}
		// in case something strange, return false on default
		return false;
	}

	/**
	 * checks if the password needs to be rehashed
	 * @param  string $hash password hash
	 * @return bool         true or false
	 */
	public function passwordRehashCheck(string $hash): bool
	{
		if (password_needs_rehash($hash, PASSWORD_DEFAULT, $this->password_options)) {
			return true;
		} else {
			return false;
		}
		// in case of strange, force re-hash
		return true;
	}

	// *** COLORS ***

	/**
	 * converts a hex RGB color to the int numbers
	 * @param  string            $hexStr         RGB hexstring
	 * @param  bool              $returnAsString flag to return as string
	 * @param  string            $seperator      string seperator: default: ","
	 * @return string|array|bool                 false on error or array with RGB or a string with the seperator
	 */
	public static function hex2rgb(string $hexStr, bool $returnAsString = false, string $seperator = ',')
	{
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) {
			// If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['R'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['G'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['B'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) {
			// If shorthand notation, need some string manipulations
			$rgbArray['R'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['G'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['B'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			// Invalid hex color code
			return false;
		}
		// returns the rgb string or the associative array
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
	}

	/**
	 * converts the rgb values from int data to the valid rgb html hex string
	 * optional can turn of leading #
	 * @param  int    $red        red 0-255
	 * @param  int    $green      green 0-255
	 * @param  int    $blue       blue 0-255
	 * @param  bool   $hex_prefix default true, prefix with "#"
	 * @return string             rgb in hex values with leading # if set
	 */
	public static function rgb2hex(int $red, int $green, int $blue, bool $hex_prefix = true): string
	{
		$hex_color = '';
		if ($hex_prefix === true) {
			$hex_color = '#';
		}
		foreach (array('red', 'green', 'blue') as $color) {
			// if not valid, set to gray
			if ($$color < 0 || $$color > 255) {
				$$color = 125;
			}
			// pad left with 0
			$hex_color .= str_pad(dechex($$color), 2, '0', STR_PAD_LEFT);
		}
		return $hex_color;
	}

	/**
	 * converts and int RGB to the HTML color string in hex format
	 * @param  int    $red   red 0-255
	 * @param  int    $green green 0-255
	 * @param  int    $blue  blue 0-255
	 * @return string        hex rgb string
	 * @deprecated use rgb2hex instead
	 */
	public static function rgb2html(int $red, int $green, int $blue): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use rgb2hex', E_USER_DEPRECATED);
		// check that each color is between 0 and 255
		foreach (array('red', 'green', 'blue') as $color) {
			if ($$color < 0 || $$color > 255) {
				$$color = 125;
			}
			// convert to HEX value
			$$color = dechex($$color);
			// prefix with 0 if only one char
			$$color = ((strlen($$color) < 2) ? '0' : '').$$color;
		}
		// prefix hex parts with 0 if they are just one char long and return the html color string
		return '#'.$red.$green.$blue;
	}

	/**
	 * converts RGB to HSB/V values
	 * returns:
	 * array with hue (0-360), sat (0-100%), brightness/value (0-100%)
	 * @param  int    $r red 0-255
	 * @param  int    $g green 0-255
	 * @param  int    $b blue 0-255
	 * @return array  Hue, Sat, Brightness/Value
	 */
	public static function rgb2hsb(int $r, int $g, int $b): array
	{
		// check that rgb is from 0 to 255
		foreach (array('r', 'g', 'b') as $c) {
			if ($$c < 0 || $$c > 255) {
				$$c = 0;
			}
			$$c = $$c / 255;
		}

		$MAX = max($r, $g, $b);
		$MIN = min($r, $g, $b);
		$HUE = 0;

		if ($MAX == $MIN) {
			return array(0, 0, round($MAX * 100));
		}
		if ($r == $MAX) {
			$HUE = ($g - $b) / ($MAX - $MIN);
		} elseif ($g == $MAX) {
			$HUE = 2 + (($b - $r) / ($MAX - $MIN));
		} elseif ($b == $MAX) {
			$HUE = 4 + (($r - $g) / ($MAX - $MIN));
		}
		$HUE *= 60;
		if ($HUE < 0) {
			$HUE += 360;
		}

		return array(round($HUE), round((($MAX - $MIN) / $MAX) * 100), round($MAX * 100));
	}

	/**
	 * converts HSB/V to RGB values RGB is full INT
	 * @param  int    $H hue 0-360
	 * @param  float  $S saturation 0-1 (float)
	 * @param  float  $V brightness/value 0-1 (float)
	 * @return array  0 red/1 green/2 blue array
	 */
	public static function hsb2rgb(int $H, float $S, float $V): array
	{
		// check that H is 0 to 359, 360 = 0
		// and S and V are 0 to 1
		if ($H < 0 || $H > 359 || $H == 360) {
			$H = 0;
		}
		if ($S < 0 || $S > 1) {
			$S = 0;
		}
		if ($V < 0 || $V > 1) {
			$V = 0;
		}

		if ($S == 0) {
			return array($V * 255, $V * 255, $V * 255);
		}

		$Hi = floor($H / 60);
		$f = ($H / 60) - $Hi;
		$p = $V * (1 - $S);
		$q = $V * (1 - ($S * $f));
		$t = $V * (1 - ($S * (1 - $f)));

		switch ($Hi) {
			case 0:
				$red = $V;
				$green = $t;
				$blue = $p;
				break;
			case 1:
				$red = $q;
				$green = $V;
				$blue = $p;
				break;
			case 2:
				$red = $p;
				$green = $V;
				$blue = $t;
				break;
			case 3:
				$red = $p;
				$green = $q;
				$blue = $V;
				break;
			case 4:
				$red = $t;
				$green = $p;
				$blue = $V;
				break;
			case 5:
				$red = $V;
				$green = $p;
				$blue = $q;
				break;
			default:
				$red = 0;
				$green = 0;
				$blue = 0;
		}

		return array(round($red * 255), round($green * 255), round($blue * 255));
	}

	/**
	 * converts a RGB (0-255) to HSL
	 * return:
	 * array with hue (0-360), saturation (0-100%) and luminance (0-100%)
	 * @param  int    $r red 0-255
	 * @param  int    $g green 0-255
	 * @param  int    $b blue 0-255
	 * @return array  hue/sat/luminance
	 */
	public static function rgb2hsl(int $r, int $g, int $b): array
	{
		// check that rgb is from 0 to 255
		foreach (array('r', 'g', 'b') as $c) {
			if ($$c < 0 || $$c > 255) {
				$$c = 0;
			}
			$$c = $$c / 255;
		}

		$MIN = min($r, $g, $b);
		$MAX = max($r, $g, $b);
		$HUE = 0;
		// luminance
		$L = round((($MAX + $MIN) / 2) * 100);

		if ($MIN == $MAX) {
			// H, S, L
			return array(0, 0, $L);
		} else {
			// HUE to 0~360
			if ($r == $MAX) {
				$HUE = ($g - $b) / ($MAX - $MIN);
			} elseif ($g == $MAX) {
				$HUE = 2 + (($b - $r) / ($MAX - $MIN));
			} elseif ($b == $MAX) {
				$HUE = 4 + (($r - $g) / ($MAX - $MIN));
			}
			$HUE *= 60;
			if ($HUE < 0) {
				$HUE += 360;
			}

			// H, S, L
			// S= L <= 0.5 ? C/2L : C/2 - 2L
			return array(round($HUE), round((($MAX - $MIN) / (($L <= 0.5) ? ($MAX + $MIN) : (2 - $MAX - $MIN))) * 100), $L);
		}
	}

	/**
	 * converts an HSL to RGB
	 * @param  int    $h hue: 0-360 (degrees)
	 * @param  float  $s saturation: 0-1
	 * @param  float  $l luminance: 0-1
	 * @return array  red/blue/green 0-255 each
	 */
	public static function hsl2rgb(int $h, float $s, float $l): array
	{
		$h = (1 / 360) * $h; // calc to internal convert value for hue
		// if saturation is 0
		if ($s == 0) {
			return array($l * 255, $l * 255, $l * 255);
		} else {
			$m2 = ($l < 0.5) ? $l * ($s + 1) : ($l + $s) - ($l * $s);
			$m1 = $l * 2 - $m2;
			$hue = function ($base) use ($m1, $m2) {
				// base = hue, hue > 360 (1) - 360 (1), else < 0 + 360 (1)
				$base = ($base < 0) ? $base + 1 : (($base > 1) ? $base - 1 : $base);
				// 6: 60, 2: 180, 3: 240
				// 2/3 = 240
				// 1/3 = 120 (all from 360)
				if ($base * 6 < 1) {
					return $m1 + ($m2 - $m1) * $base * 6;
				}
				if ($base * 2 < 1) {
					return $m2;
				}
				if ($base * 3 < 2) {
					return $m1 + ($m2 - $m1) * ((2 / 3) - $base) * 6;
				}
				return $m1;
			};

			return array(round(255 * $hue($h + (1 / 3))), round(255 * $hue($h)), round(255 * $hue($h - (1 / 3))));
		}
	}

	/**
	 * guesses the email type (mostly for mobile) from the domain
	 * if second is set to true, it will return short naming scheme (only provider)
	 * @param  string $email email string
	 * @param  bool   $short default false, if true, returns only short type (pc instead of pc_html)
	 * @return string|bool   email type, eg "pc", "docomo", etc, false for invalid short type
	 */
	public function getEmailType(string $email, bool $short = false)
	{
		// trip if there is no email address
		if (!$email) {
			return 'invalid';
		}
		// loop until we match a mobile type, return this first found type
		foreach ($this->mobile_email_type as $email_regex => $email_type) {
			if (preg_match("/$email_regex/", $email)) {
				if ($short) {
					return $this->getShortEmailType($email_type);
				} else {
					return $email_type;
				}
			}
		}
		// if no previous return we assume this is a pc address
		if ($short) {
			return 'pc';
		} else {
			return 'pc_html';
		}
	}

	/**
	 * gets the short email type from a long email type
	 * @param  string $email_type email string
	 * @return string|bool              short string or false for invalid
	 */
	public function getShortEmailType(string $email_type)
	{
		// check if the short email type exists
		if (isset($this->mobile_email_type_short[$email_type])) {
			return $this->mobile_email_type_short[$email_type];
		} else {
			// return false on not found
			return false;
		}
	}

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
	 */
	public static function printDateTime($year, $month, $day, $hour, $min, string $suffix = '', int $min_steps = 1, bool $name_pos_back = false)
	{
		// if suffix given, add _ before
		if ($suffix) {
			$suffix = '_'.$suffix;
		}
		if ($min_steps < 1 || $min_steps > 59) {
			$min_steps = 1;
		}

		$on_change_call = 'dt_list(\''.$suffix.'\');';

		// always be 1h ahead (for safety)
		$timestamp = time() + 3600; // in seconds

		// the max year is this year + 1;
		$max_year = (int)date("Y", $timestamp) + 1;

		// preset year, month, ...
		$year = (!$year) ? date("Y", $timestamp) : $year;
		$month = (!$month) ? date("m", $timestamp) : $month;
		$day = (!$day) ? date("d", $timestamp) : $day;
		$hour = (!$hour) ? date("H", $timestamp) : $hour;
		$min = (!$min) ? date("i", $timestamp) : $min; // add to five min?
		// max days in selected month
		$days_in_month = date("t", strtotime($year."-".$month."-".$day." ".$hour.":".$min.":0"));
		$string = '';
		// from now to ?
		if ($name_pos_back === false) {
			$string = 'Year ';
		}
		$string .= '<select id="year'.$suffix.'" name="year'.$suffix.'" onChange="'.$on_change_call.'">';
		for ($i = date("Y"); $i <= $max_year; $i ++) {
			$string .= '<option value="'.$i.'" '.(($year == $i) ? 'selected' : '').'>'.$i.'</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Year ';
		}
		if ($name_pos_back === false) {
			$string .= 'Month ';
		}
		$string .= '<select id="month'.$suffix.'" name="month'.$suffix.'" onChange="'.$on_change_call.'">';
		for ($i = 1; $i <= 12; $i ++) {
			$string .= '<option value="'.(($i < 10) ? '0'.$i : $i).'" '.(($month == $i) ? 'selected' : '').'>'.$i.'</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Month ';
		}
		if ($name_pos_back === false) {
			$string .= 'Day ';
		}
		$string .= '<select id="day'.$suffix.'" name="day'.$suffix.'" onChange="'.$on_change_call.'">';
		for ($i = 1; $i <= $days_in_month; $i ++) {
			// set weekday text based on current month ($month) and year ($year)
			$string .= '<option value="'.(($i < 10) ? '0'.$i : $i).'" '.(($day == $i) ? 'selected' : '').'>'.$i.' ('.date('D', mktime(0, 0, 0, $month, $i, $year)).')</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Day ';
		}
		if ($name_pos_back === false) {
			$string .= 'Hour ';
		}
		$string .= '<select id="hour'.$suffix.'" name="hour'.$suffix.'" onChange="'.$on_change_call.'">';
		for ($i = 0; $i <= 23; $i += $min_steps) {
			$string .= '<option value="'.(($i < 10) ? '0'.$i : $i).'" '.(($hour == $i) ? 'selected' : '').'>'.$i.'</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Hour ';
		}
		if ($name_pos_back === false) {
			$string .= 'Minute ';
		}
		$string .= '<select id="min'.$suffix.'" name="min'.$suffix.'" onChange="'.$on_change_call.'">';
		for ($i = 0; $i <= 59; $i ++) {
			$string .= '<option value="'.(( $i < 10) ? '0'.$i : $i).'" '.(($min == $i) ? 'selected' : '').'>'.$i.'</option>';
		}
		$string .= '</select>';
		if ($name_pos_back === true) {
			$string .= ' Minute ';
		}
		// return the datetime select string
		return $string;
	}

	/**
	 * full wrapper for html entities
	 * @param  string $string string to html encode
	 * @return mixed  if string, encoded, else as is
	 */
	public function htmlent(string $string)
	{
		if (is_string($string)) {
			return htmlentities($string, ENT_COMPAT|ENT_HTML401, 'UTF-8', false);
		} else {
			return $string;
		}
	}

	/**
	 * strips out all line breaks or replaced with given string
	 * @param  string $string  string
	 * @param  string $replace replace character, default ' '
	 * @return string          cleaned string without any line breaks
	 */
	public function removeLB(string $string, string $replace = ' '): string
	{
		return str_replace(array("\r", "\n"), $replace, $string);
	}

	/**
	 * some float numbers will be rounded up even if they have no decimal entries
	 * this function fixes this by pre-rounding before calling ceil
	 * @param  float       $number    number to round
	 * @param  int|integer $precision intermediat round up decimals (default 10)
	 * @return float                  correct ceil number
	 */
	public function fceil(float $number, int $precision = 10): float
	{
		return ceil(round($number, $precision));
	}

	/**
	 * round inside an a number, not the decimal part only
	 * eg 48767 with -2 -> 48700
	 * @param  float $number    number to round
	 * @param  int   $precision negative number for position in number (default -2)
	 * @return float            rounded number
	 */
	public function floorp(float $number, int $precision = -2): float
	{
		$mult = pow(10, $precision); // Can be cached in lookup table
		return floor($number * $mult) / $mult;
	}

	/**
	 * inits input to 0, if value is not numeric
	 * @param  string|int|float $number string or number to check
	 * @return float                    if not number, then returns 0, else original input
	 */
	public function initNumeric($number): float
	{
		if (!is_numeric($number)) {
			return 0;
		} else {
			return $number;
		}
	}

	/**
	 * sets a form token in a session and returns form token
	 * @param  string $name optional form name, default form_token
	 * @return string       token name for given form id string
	 */
	public function setFormToken(string $name = 'form_token'): string
	{
		// current hard set to sha256
		$token = uniqid(hash('sha256', (string)rand()));
		$_SESSION[$name] = $token;
		return $token;
	}

	/**
	 * checks if the form token matches the session set form token
	 * @param  string $token token string to check
	 * @param  string $name  optional form name to check to, default form_token
	 * @return bool          false if not set, or true/false if matching or not mtaching
	 */
	public function validateFormToken(string $token, string $name = 'form_token'): bool
	{
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name] === $token;
		} else {
			return false;
		}
	}
}

// __END__
