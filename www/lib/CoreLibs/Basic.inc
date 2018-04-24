<?php
/*********************************************************************
* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
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
*	class_info
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

class Basic
{
	public $class_info; // class info var

	public $page_name;
	public $host_name;
	public $host_port;

	private $error_id; // error ID for errors in classes
	private $error_string; // error strings in classes (for error_id)
	private $error_msg = array (); // the "connection" to the outside errors

	public $debug_output; // if this is true, show debug on desconstructor
	public $debug_output_not;
	public $debug_output_all;
	public $echo_output; // errors: echo out, default is 1
	public $echo_output_not;
	public $echo_output_all;
	public $print_output; // errors: print to file, default is 0
	public $print_output_not;
	public $print_output_all;

	// log file name
	private $log_file_name_ext = 'log'; // use this for date rotate
	public $log_max_filesize = 0; // set in kilobytes
	private $log_print_file = 'error_msg##LOGID####LEVEL####CLASS####PAGENAME####DATE##';
	private $log_file_unique_id; // a unique ID set only once for call derived from this class
	public $log_print_file_date = 1; // if set add Y-m-d and do automatic daily rotation
	private $log_file_id = LOG_FILE_ID ? LOG_FILE_ID : ''; // a alphanumeric name that has to be set as global definition
	public $log_per_level = 0; // set, it will split per level (first parameter in debug call)
	public $log_per_class = 0; // set, will split log per class
	public $log_per_page = 0; // set, will split log per called file
	public $log_per_run = 0; // create a new log file per run (time stamp + unique ID)

	private $starttime; // start time if time debug is used
	private $endtime; // end time if time debug is used

	public $email_regex; // regex var for email check
	public $keitai_email_regex; // regex var for email check

	public $error_msg_prefix = ''; // prefix to the error string (the class name)

	public $debug_fp = ''; // filepointer for writing to file
	public $debug_filename = 'debug_file.log'; // where to write output
	public $hash_algo = 'crc32b'; // the hash algo used for the internal debug uid
	public $running_uid = ''; // unique ID set on class init and used in logging as prefix

	// data path for files
	public $data_path = array ();

	// error char for the char conver
	public $mbErrorChar;

	// crypt saslt prefix
	public $cryptSaltPrefix = '';
	public $cryptSaltSuffix = '';
	public $cryptIterationCost = 7; // this is for staying backwards compatible with the old ones
	public $cryptSaltSize = 22; // default 22 chars for blowfish, 2 for STD DES, 8 for MD5,

	// session name
	private $session_name = '';
	private $session_id = '';

	// form token (used for form validation)
	private $form_token = '';

	// METHOD: __construct
	// PARAMS: debug_all (0)/1, echo_all (1)/0, print_all (0)/1
	// RETURN: none
	// DESC constructor
	public function __construct($debug_all = 0, $echo_all = 1, $print_all = 0)
	{
		// set per run UID for logging
		$this->running_uid = hash($this->hash_algo, uniqid(rand(), true));

		// internal info var
		$this->class_info["basic"] = array (
			"class_name" => "Basic",
			"class_version" => "2.0.0",
			"class_created" => "2003-03-24",
			"class_author" => 'Clemens Schwaighofer'
		);

		// set the page name
		$this->page_name = $this->getPageName();
		$this->host_name = $this->getHostName();

		// set the paths matching to the valid file types
		$this->data_path = array (
			'P' => PICTURES,
			'F' => FLASH,
			'V' => VIDEOS,
			'D' => DOCUMENTS,
			'A' => PDFS,
			'B' => BINARIES
		);

		// if given via parameters, only for all
		$this->debug_output_all = $debug_all;
		$this->echo_output_all = $echo_all;
		$this->print_output_all = $print_all;
		// globals overrule given settings, for one (array), eg $ECHO['db'] = 1;
		if (isset($GLOBALS['DEBUG'])) {
			$this->debug_output = $GLOBALS['DEBUG'];
		}
		if (isset($GLOBALS['ECHO'])) {
			$this->echo_output = $GLOBALS['ECHO'];
		}
		if (isset($GLOBALS['PRINT'])) {
			$this->print_output = $GLOBALS['PRINT'];
		}

		// exclude these ones from output
		if (isset($GLOBALS['DEBUG_NOT'])) {
			$this->debug_output_not = $GLOBALS['DEBUG_NOT'];
		}
		if (isset($GLOBALS['ECHO_NOT'])) {
			$this->echo_output_not = $GLOBALS['ECHO_NOT'];
		}
		if (isset($GLOBALS['PRINT_NOT'])) {
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
		$this->email_regex = "^[A-Za-z0-9!#$%&'*+-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+-\/=?^_`{|}~\.]{0,63}@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.([a-zA-Z]{2,}){1}$";
		// this is for error check parts in where the email regex failed
		$this->email_regex_check = array (
			1 => "@(.*)@(.*)", // double @
			2 => "^[A-Za-z0-9!#$%&'*+-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+-\/=?^_`{|}~\.]{0,63}@", // wrong part before @
			3 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.([a-zA-Z]{2,}){1}$", // wrong part after @
			4 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.", // wrong domain name part
			5 => "\.([a-zA-Z]{2,6}){1}$", // wrong top level part
			6 => "@(.*)\.{2,}", // double .. in domain name part
			7 => "@.*\.$" // ends with a dot, top level, domain missing
		);
		// the array with the mobile types that are valid
		$this->mobile_email_type = array (
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
			'.*@ymobile([1]{1})?\.ne\.jp$' => 'keitai_willcom_ymobile', # ymobile, ymobile1 techincally not willcom, but I group them there
			'.*@y-mobile\.ne\.jp$' => 'keitai_willcom_ymobile', # y-mobile techincally not willcom, but I group them there
			'.*@emnet\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
			'.*@emobile\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
			'.*@emobile-s\.ne\.jp$' => 'keitai_willcom_emnet' # e-mobile, group will willcom
		);
		// short list for mobile email types
		$this->mobile_email_type_short = array (
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
			if (SET_SESSION_NAME) {
				// set the session name for possible later check
				$this->session_name = SET_SESSION_NAME;
			}
			if ($this->session_name) {
				session_name($this->session_name);
			}
			// start session
			session_start();
			// set internal session id, we can use that later for protection check
			$this->session_id = session_id();
		}

		// init crypt settings
		$this->cryptInit();

		// start logging running time
		$this->runningTime();
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
	// GENERAL METHODS
	// *************************************************************

	// METHOD: db_io_info
	// PARAMS: show, default 1, if set to 0 won't write to error_msg var
	// RETURN: string with info
	// DESC  : default class info (prints out class info)
	public function info($class_name = "basic", $stdio = 0)
	{
		unset($string);
		list($major, $minor, $patchlvl) = explode(".", $this->class_info[$class_name]["class_version"]);
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Name: <b>".$this->class_info[$class_name]["class_name"]."</b><br>";
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Author: <b>".$this->class_info[$class_name]["class_author"]."</b><br>";
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Version: <b>".$this->class_info[$class_name]["class_version"]."</b><br>";
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Revision: <b>".$this->class_info[$class_name]["class_revision"]."</b><br>";
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Created: <b>".$this->class_info[$class_name]["class_created"]."</b><br>";
		$string .= "<b>-Class-info-[".$class_name."]-></b> Class Last Change: <b>".$this->class_info[$class_name]["class_last_changed"]."</b>";
		if ($stdio) {
			echo $string.'<br>';
		} else {
			$this->debug('info', '<br>'.$string);
		}
		return $string;
	}

	// METHOD: runningTime
	// WAS   : running_time
	// PARAMS: simple flag true/false, if given print non HTML info
	// RETURN: string with running time for debugging
	// DESC  : prints start or end time in text format. On first call sets start time
	//         on second call it sends the end time and then also prints the running time
	public function runningTime($simple = false)
	{
		list($micro, $timestamp) = explode(" ", microtime());
		$string = '';
		$running_time = '';
		if (!$this->starttime) {
			$this->starttime = ((float)$micro + (float)$timestamp);
			$string .= $simple ? 'Start: ' : "<b>Started at</b>: ";
		} else {
			$this->endtime = ((float)$micro + (float)$timestamp);
			$string .= $simple ? 'End: ' : "<b>Stopped at</b>: ";
		}
		$string .= date("Y-m-d H:i:s", $timestamp);
		$string .= " ".$micro;
		if ($this->starttime && $this->endtime) {
			$running_time = $this->endtime - $this->starttime;
			$string .= ($simple ? 'Run: ' : "<br><b>Script running time</b>: ").$running_time." s";
		}
		// $this->debug('info', $string);
		return $running_time;
	}

	// METHOD: resetRunningtime
	// PARAMS: none
	// RETURN: none
	// DESC  : resets start & end time for runningTime call
	public function resetRunningTime()
	{
		$this->starttime = '';
		$this->endtime = '';
	}

	// METHOD: printTime
	// WAS   : print_time
	// PARAMS: $set_microtime, 0 shows none, default (-1) shows all, positive number is for rounding
	// RETURN: formated datetime string
	// DESC  : wrapper around microtime function to print out y-m-d h:i:s.ms
	public static function printTime($set_microtime = -1)
	{
		list($microtime, $timestamp) = explode(" ", microtime());
		$string = date("Y-m-d H:i:s", $timestamp);
		// if microtime flag is -1 no round, if 0, no microtime, if >= 1, round that size
		if ($set_microtime == -1) {
			$string .= substr($microtime, 1);
		} elseif ($set_microtime >= 1) {
			$string .= substr(round($microtime, $set_microtime), 1);
		}
		return $string;
	}


	// METHOD: fdebug
	// PARAMS: $string: data to write to file
	//        $enter: default on true, if set to false, no linebreak (\n) will be put at the end
	// RETURN:  none
	// DESC  : writes a string to a file immediatly, for fast debug output
	public function fdebug($string, $enter = 1)
	{
		if ($this->debug_filename) {
			$this->fdebugFP();
			if ($enter) {
				$string .= "\n";
			}
			$string = "[".$this->printTime()."] [".$this->getPageName(2)."] - ".$string;
			fwrite($this->debug_fp, $string);
			$this->fdebugFP();
		}
	}

	// METHOD: fdebugFP
	// WAS   : fdebug_fp
	// PARAMS: $flag: default '', 'o' -> open, 'c' -> close
	// RETURN: none
	// DESC  : if no debug_fp found, opens a new one; if fp exists close it
	private function fdebugFP($flag = '')
	{
		if (!$this->debug_fp || $flag == 'o') {
			$fn = BASE.LOG.$this->debug_filename;
			$this->debug_fp = @fopen($fn, 'a');
		} elseif ($this->debug_fp || $flag == 'c') {
			fclose($this->debug_fp);
		}
	}

	// METHOD: debugFor
	// WAS   : debug_for
	// PARAMS: $type: error, echo, print
	//         $flag: on/off
	//         $array of levels to turn on/off debug
	// RETURN: none
	// DESC  : passes list of level names, to turn on debug
	//         eg $foo->debug_for('print', 'on', array('LOG', 'DEBUG', 'INFO'));
	public function debugFor($type, $flag)
	{
		$debug_on = func_get_args();
		array_shift($debug_on); // kick out type
		array_shift($debug_on); // kick out flag (on/off)
		if (count($debug_on) >= 1) {
			foreach ($debug_on as $level) {
				$switch = $type."_output";
				if ($flag == 'off') {
					$switch .= '_not';
				}
				$this->{$switch}[$level] = 1;
			}
		}
	}

	// METHOD: debug
	// PARAMS: $level: id for error message, groups messages together
	//         $string: the actual error message
	//         $strip: default on false, if set to true, all html tags will be stripped and <br> changed to \n
	//                 this is only used for debug output
	// RETURN: none
	// DESC  : write debug data to error_msg array
	public function debug($level, $string, $strip = false)
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
				$string = preg_replace("/(<\/?)(\w+)([^>]*>)/", "", $string);
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

	// METHOD: getCallerMethod
	// WAS   : get_caller_method
	// PARAMS: none (optional later: which level to deb ug)
	// RETURN: null or the function that called the function where this methid is called
	// DESC  : if there is a need to find out which parent method called a child method, eg for debugging, this function does this
	//         call this method in the child method and you get the parent function that called it
	public function getCallerMethod($level = 2)
	{
		$traces = debug_backtrace();
		// extended info (later)
		/*
		 * $file = $trace[$level]['file'];
		 * $line = $trace[$level]['line'];
		 * $object = $trace[$level]['object'];
		 * if (is_object($object)) { $object = get_class($object); }
		 *
		 * return "Where called: line $line of $object \n(in $file)";
		 */
		// sets the start point here, and in level two (the sub call) we find this
		if (isset($traces[$level])) {
			return $traces[$level]['function'];
		}
		return null;
	}

	// METHOD: mergeErrors
	// WAS   : merge_errors
	// PARAMS: error array
	// RETURN: none
	// DESC  : merges the given error array with the one from this class
	//         only merges visible ones
	public function mergeErrors($error_msg = array ())
	{
		if (!is_array($error_msg)) {
			$error_msg = array ();
		}
		foreach ($error_msg as $level => $msg) {
			$this->error_msg[$level] .= $msg;
		}
	}

	// METHOD: printErrorMsg
	// WAS   : print_error_msg
	// PARAMS: $string: prefix string for header
	// RETURN: error msg for all levels
	// DESC  : prints out the error string
	public function printErrorMsg($string = '')
	{
		$string_output = '';
		if ($this->debug_output_all) {
			if ($this->error_msg_prefix) {
				$string = $this->error_msg_prefix;
			}
			$script_end = $this->runningTime();
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

	// METHOD: writeErrorMsg
	// WAS   : write_error_msg
	// PARAMS: $level: the level to write
	// RETURN: none
	// DESC  : writes error msg data to file for current level
	private function writeErrorMsg($level, $error_string)
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
					if ($GLOBALS['LOG_FILE_UNIQUE_ID']) {
						$this->log_file_unique_id = $GLOBALS['LOG_FILE_UNIQUE_ID'];
					}
					if (!$this->log_file_unique_id) {
						$GLOBALS['LOG_FILE_UNIQUE_ID'] = $this->log_file_unique_id = date('Y-m-d_His').'_U_'.substr(hash('sha1', uniqid(mt_rand(), true)), 0, 8);
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

	// METHOD: resetErrorMsg
	// WAS   : reset_error_msg
	// PARAMS: level -> optional level
	// RETURN: none
	// DESC  : unsests the error message array
	//         can be used if writing is primary to file
	//         if no level given resets all
	public function resetErrorMsg($level = '')
	{
		if (!$level) {
			unset($this->error_msg);
		} else {
			unset($this->error_msg[$level]);
		}
	}

	// METHOD: errorHandler
	// WAS   : ErrorHandler
	// PARAMS: none
	// RETURN: none
	// DESC  : catch function to handle all errors that are not handled by php itself
	//         eg all errors that would be surpressed are written to a log file if this function is enabled
	//         to use it call with set_error_handler(array("Basic", "ErrorHandler"));
	// NOTE  : this will only catch any additional erros created AFTER the set_error_handler was set,
	//         so mostly no strict/notices from the classes are visible
	//         also, this currently returns true, which will invoke the standard PHP error reporter too
	public static function errorHandler($type, $message, $file, $line)
	{
		// error levels for PHP
		// values based on 5.3
		$error_level = array (
			1 => 'E_ERROR',
			2 => 'E_WARNING',
			4 => 'E_PARSE',
			8 => 'E_NOTICE',
			16 => 'E_CORE_ERROR',
			32 => 'E_CORE_WARNING',
			64 => 'E_COMPILE_ERROR',
			128 => 'E_COMPILE_WARNING',
			256 => 'E_USER_ERROR',
			512 => 'E_USER_WARNING',
			1024 => 'E_USER_NOTICE',
			2048 => 'E_STRICT',
			4096 => 'E_RECOVERABLE_ERROR', // since 5.2
			8192 => 'E_DEPRICATED', // since 5.3
			16384 => 'E_USER_DEPRICATED', // since 5.3
			30719 => 'E_ALL' // 6143 in 5.2, 2047 in previous versions
		);

		$fn = BASE.LOG.'php_errors-'.date('Y-m-d').'.log';
		$output = '['.Basic::print_time().'] {'.Basic::get_page_name().'} ['.$file.'] <'.$line.'> ['.$error_level[$type].'|'.$type.']: '.$message."\n";
		$fp = fopen($fn, 'a');
		fwrite($fp, $output);
		fclose($fp);
		// if set to false the PHP error reporter will be called after this
		return false;
	}

	// METHOD: printAr
	// WAS   : print_ar
	// PARAMS: $array
	// RETURN: string html formatted
	// DESC  : prints a html formatted (pre) array
	public static function printAr($array)
	{
		return "<pre>".print_r($array, 1)."</pre>";
	}

	// METHOD: checked
	// PARAMS: haystack (search in), needle (search for), type: 0: returns selected, 1, returns checked
	//         haystack can be an array or a string
	// RETURN: returns checked or selected, else returns nothing (empty return)
	// DESC  : returns 'checked' or 'selected' if okay
	//         $needle is a var, $haystack an array
	// **** THE RETURN: VALUE WILL CHANGE TO A DEFAULT "FALSE" ****
	public static function checked($haystack, $needle, $type = 0)
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
	}

	// METHOD: magicLinks
	// WAS   : magic_links
	// PARAMS: string: data to transform to a valud HTML url, target: default _blank
	// RETURN: correctly formed html url link
	// DESC  : tries to find mailto:user@bubu.at and changes it into -> <a href="mailto:user@bubu.at">E-Mail senden</a>
	//         or tries to take any url (http, ftp, etc) and transform it into a valid URL
	//         the string is in the format: some url|name#css|, same for email
	public function magicLinks($string, $target = "_blank")
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

	// METHOD: createUrl [INTERNAL]
	// WAS   : create_url
	// PARAMS: url link, anchor tag (define both type or url),
	//         _1, _2, _3 = part of thel URL, if atag is set, _1 is not used
	//         target: link target, name: name for the url, if not given _2 + _3 is used
	//         class: style sheet
	// RETURN: correct string for url href process
	// DESC  : internal function, called by the magic url create functions.
	//         checks if title $_4 exists, if not, set url as title
	private function createUrl($href, $atag, $_1, $_2, $_3, $name, $class)
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

	// METHOD: createEmail [INTERNAL]
	// WAS   : create_email
	// PARAMS: email address, atag (define type of url)
	//         _1, _2, _3: parts of the email _1 before @, _2 after @, 3_ tld
	//         title: name for the link, if not given use email
	//         class: style sheet
	// DESC  : internal function for createing email, returns data to magic_url method
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

	// METHOD: getHostName
	// WAS   : get_host_name
	// PARAMS: none
	// RETURN: host name
	// DESC  : get the host name without the port as given by the SELF var
	public function getHostName()
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
		return $host_name;
	}

	// METHOD: getPageName
	// WAS   : get_page_name
	// PARAMS: 1: strip page file name extension
	//         0: keep filename as is
	//         2: keep filename as is, but add dirname too
	// RETURN: filename
	// DESC  : get the page name of the curronte page:
	public static function getPageName($strip_ext = 0)
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

	// METHOD: getFilenameEnding
	// WAS   : get_filename_ending
	// PARAMS: filename
	// RETURN: extension of the file name
	// DESC  : quick return the extension of the given file name
	public static function getFilenameEnding($filename)
	{
		$page_temp = pathinfo($filename);
		return $page_temp['extension'];
	}

	// METHOD: arraySearchRecursive
	// WAS   : array_search_recursive
	// PARAMS: needle (search for)
	//         haystack (search in)
	//         key_lookin: the key to look out for, default empty
	// RETURN: array with the elements where the needle can be found in the haystack array
	// DESC  : searches key = value in an array / array
	//         only returns the first one found
	public static function arraySearchRecursive($needle, $haystack, $key_lookin = "")
	{
		$path = null;
		if (!is_array($haystack)) {
			$haystack = array();
		}
		if (!is_array($key_lookin) && !empty($key_lookin) && array_key_exists($key_lookin, $haystack) && $needle === $haystack[$key_lookin]) {
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

	// METHOD: arraySearchRecursiveAll
	// WAS   : array_search_recursive_all
	// PARAMS: needle (search for)
	//         haystack (search in)
	//         key: the key to look for in
	//         path: recursive call for previous path
	// RETURN: all array elements paths where the element was found
	// DESC  : recursive array search function, which returns all found not only the first one
	public static function arraySearchRecursiveAll($needle, $haystack, $key, $path = null)
	{
		if (!isset($path['level'])) {
			$path['level'] = 0;
		}
		if (!isset($path['work'])) {
			$path['work'] = array();
		}
		if (!isset($haystack)) {
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

	// METHOD: arraySearchSimple
	// WAS   : array_search_simple
	// PARAMS: array (search in), key (key to search in), value (what to find
	// RETURN: true on found, false on not found
	// DESC  : array search simple. looks for key, value combination, if found, returns true
	public static function arraySearchSimple($array, $key, $value)
	{
		if (!is_array($array)) {
			$array = array ();
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

	// METHOD: inArrayAny
	// WAS   : in_array_any
	// PARAMS: needle: array
	//         haystack: array
	// RETURN: found elements: array
	// DESC  : search for the needle array elements in haystack and return the ones found as an array,
	//         is there nothing found, it returns FALSE (boolean)
	public static function inArrayAny($needle, $haystack)
	{
		if (!is_array($needle)) {
			return false;
		}
		if (!is_array($haystack)) {
			return false;
		}
		$found = array ();
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

	// METHOD: genAssocArray
	// WAS   : GenAssocArray
	// PARAMS: db array, key, value part, flag if set all or only set
	// RETURN: returns and associative array
	// DESC  : creates out of a normal db_return array an assoc array
	public static function genAssocArray($db_array, $key, $value, $set_only = 0)
	{
		// do this to only run count once
		for ($i = 0, $iMax = count($db_array); $i < $iMax; $i ++) {
			// if no key then we make an order reference
			if ($key && $value && (($set_only && $db_array[$i][$value]) || (!$set_only))) {
				$ret_array[$db_array[$i][$key]] = $db_array[$i][$value];
			} elseif (!$key && $value) {
				$ret_array[] = $db_array[$i][$value];
			} elseif ($key && !$value) {
				$ret_array[$db_array[$i][$key]] = $i;
			}
		}
		return $ret_array;
	}

	// METHOD: __mbMimeEncode
	// WAS   : _mb_mime_encode
	// PARAMS: string to encode, encoding to encode in
	// RETURN: encoded string
	// DESC  : wrapper function for mb mime convert, for correct conversion with long strings
	public static function __mbMimeEncode($string, $encoding)
	{
		// set internal encoding, so the mimeheader encode works correctly
		mb_internal_encoding($encoding);
		// if a subject, make a work around for the broken mb_mimencode
		$pos = 0;
		$split = 36; // after 36 single bytes characters, if then comes MB, it is broken
					 // has to 2 x 36 < 74 so the mb_encode_mimeheader 74 hardcoded split does not get triggered
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

	// METHOD: byteStringFormat
	// WAS   : ByteStringFormat
	// PARAMS: int bytes, boolean for space, default is set
	// RETURN: string
	// DESC  : converts bytes into formated string with KB, MB, etc
	public static function byteStringFormat($number, $space = true)
	{
		if (is_numeric($number) && $number > 0) {
			// labels in order of size
			$labels = array('B', 'KB', 'MB', 'GB', 'TB');
			// calc file size, round down too two digits, add label based max change
			return round($number / pow(1024, ($i = floor(log($number, 1024)))), 2).($space ? ' ' : '').$labels[$i];
		}
		return $number;
	}

	// METHOD: stringByteFormat
	// WAS   : StringByteFormat
	// PARAMS: string
	// RETURN: int
	// DESC  : calculates the bytes based on a string with nnG, nnM, etc
	public static function stringByteFormat($number)
	{
		$number = trim($number);
		$last = strtolower($number[strlen($number) - 1]);
		switch ($last) {
			case 't':
				$number *= 1024;
				// no break, calc down next level
			case 'g':
				$number *= 1024;
				// no break: if we have giga we do first multiplication before the others
			case 'm':
				$number *= 1024;
				// no break: if we have mega, do first before we do final kilo
			case 'k':
				$number *= 1024;
		}
		return $number;
	}

	// METHOD: dateStringFormat
	// WAS   : DateStringFormat
	// PARAMS: unix timestamp, true/false to show microtime
	// RETURN: string formated date+time in Y-M-D h:m:s
	// DESC  : a simple wrapper for the date format
	public static function dateStringFormat($timestamp, $show_micro = true)
	{
		list ($timestamp, $ms) = explode('.', round($timestamp, 4));
		$string = date("Y-m-d H:i:s", $timestamp);
		if ($show_micro) {
			$string .= ' '.$ms.'ms';
		}
		return $string;
	}

	// METHOD: timeStringFormat
	// WAS   : TimeStringFormat
	// PARAMS: seconds
	// RETURN: formated time string
	// DESC  : formats a timestamp into time from. not a date
	public static function timeStringFormat($timestamp, $show_micro = true)
	{
		// check if the timestamp has any h/m/s/ms inside, if yes skip
		if (!preg_match("/(h|m|s|ms)/", $timestamp)) {
			$ms = 0;
			list ($timestamp, $ms) = explode('.', round($timestamp, 4));
			$timegroups = array (86400, 3600, 60, 1);
			$labels = array ('d', 'h', 'm', 's');
			$time_string = '';
			for ($i = 0, $iMax = count($timegroups); $i < $iMax; $i ++) {
				$output = floor($timestamp / $timegroups[$i]);
				$timestamp = $timestamp % $timegroups[$i];
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

	// METHOD: stringToTime
	// WAS   : StringToTime
	// PARAMS: TimeStringFormat string
	// RETURN: timestamp with microseconds
	// DESC  : does a reverse of the TimeStringFormat and converts the string from
	//         xd xh xm xs xms to a timestamp.microtime format
	public static function stringToTime($timestring)
	{
		$timestamp = '';
		if (preg_match("/(d|h|m|s|ms)/", $timestring)) {
			$timegroups = array (1 => 86400, 3 => 3600, 5 => 60, 7 => 1);
			preg_match("/^((\d+)d ?)?((\d+)h ?)?((\d+)m ?)?((\d+)s ?)?((\d+)ms)?$/", $timestring, $matches);
			// multiply the returned matches and sum them up. the last one (ms) is added with .
			for ($i = 1; $i <= 7; $i += 2) {
				if ($matches[$i]) {
					$timestamp += ($matches[($i + 1)] * $timegroups[$i]);
				}
			}
			if ($matches[10]) {
				$timestamp .= '.'.$matches[10];
			}
			return $timestamp;
		} else {
			return $timestring;
		}
	}

	// METHOD: checkDate
	// WAS   : CheckDate
	// PARAMS: date (YYYY-MM-DD)
	// RETURN: true if valid date, false if date not valid
	// DESC  : splits & checks date, wrap around for check_date function
	public static function checkDate($date)
	{
		list ($year, $month, $day) = preg_split("/[\/-]/", $date);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate($month, $day, $year)) {
			return false;
		}
		return true;
	}

	// METHOD: checkDateTime
	// WAS   : CheckDateTime
	// PARAMS: date (YYYY-MM-DD) + time (HH:MM:SS), SS can be dropped
	// RETURN: true if valid date, false if date not valid
	// DESC  : splits & checks date, wrap around for check_date function
	public static function checkDateTime($datetime)
	{
		list ($year, $month, $day, $hour, $min, $sec) = preg_split("/[\/\- :]/", $datetime);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate($month, $day, $year)) {
			return false;
		}
		if (!$hour || !$min) {
			return false;
		}
		if (($hour < 0 && $hour > 24) || ($min < 0 && $hour > 60) || ($sec && $sec < 0 && $sec > 60)) {
			return false;
		}
		return true;
	}

	// METHOD: compareDate
	// WAS   : CompareDate
	// PARAMS: start_date, end_date (both: YYYY-MM-DD)
	// RETURN: -1 if the first date is smaller the last, 0 if both are equal, 1 if the end date is bigger than the last
	// DESC  : splits & checks date, wrap around for check_date function
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
		}
		if ($start_date == $end_date) {
			return 0;
		}
		if ($start_date > $end_date) {
			return 1;
		}
	}

	// METHOD: compareDateTime
	// WAS   : CompareDateTime
	// PARAMS: start_datetime, end_datetime (both YYYY-MM-DD HH:mm:ss)
	// RETURN: -1 if the first date is smaller the last, 0 if both are equal, 1 if the end date is bigger than the last
	// DESC  : compares the two dates + times. if seconds missing in one set, add :00, converts / to -
	public static function compareDateTime($start_datetime, $end_datetime)
	{
		// pre check for empty or wrong
		if ($start_date == '--' || $end_date == '--' || !$start_date || !$end_date) {
			return false;
		}
		$start_timestamp = strtotime($start_datetime);
		$end_timestamp = strtotime($end_datetime);
		if ($start_timestamp < $end_timestamp) {
			return -1;
		}
		if ($start_timestamp == $end_timestamp) {
			return 0;
		}
		if ($start_timestamp > $end_timestamp) {
			return 1;
		}
	}

	// METHOD: arrayToString
	// WAS   : ArrayToString
	// PARAMS: array, connect char
	// RETRUN: string
	// DESC  : wrapper for join, but checks if input is an array and if not returns null
	public static function arrayToString($array, $connect_char)
	{
		if (is_array($array)) {
			return join($connect_char, $array);
		} else {
			return false;
		}
	}

	// METHOD: createThumbnail
	// WAS   : CreateThumbnail
	// PARAMS: pic -> picture where from we create a thumbnail
	//         x -> max x size of thumbnail
	//         y -> max y size of thumbnail
	//         dummy -> empty, or file_type to show an icon instead of nothing if file is not found
	//         path -> if source start is not ROOT path, if empty ROOT is choosen
	//         cache -> cache path, if not given TMP is used
	//         clear cache -> if set to true, will create thumb all the tame
	// RETURN: thumbnail name
	// DESC  : converts picture to a thumbnail with max x and max y size
	public static function createThumbnail($pic, $size_x, $size_y, $dummy = "", $path = "", $cache_source = "", $clear_cache = false)
	{
		// get image type flags
		$image_types = array (
			1 => 'gif',
			2 => 'jpg',
			3 => 'png'
		);

		if ($cache_source) {
			$tmp_src = $cache_source;
		} else {
			$tmp_src = BASE.TMP;
		}
		// check if pic has a path, and override next sets
		if (strstr($pic, '/') === false) {
			if (!$path) {
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
				$convert_string = CONVERT.' '.$filename.' '.$convert_prefix.$filename.'_TEMP';
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
					$convert_string = CONVERT.' -geometry '.$size_x.'x '.$filename.' '.$thumbnail;
					$status = exec($convert_string, $output, $return);
					// get the size of the converted data, if converted
					if (is_file($thumbnail)) {
						list ($width, $height, $type) = getimagesize($thumbnail);
					}
				}
				if ($height > $size_y) {
					$convert_string = CONVERT.' -geometry x'.$size_y.' '.$filename.' '.$thumbnail;
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

	// METHOD: checkConvertEncoding
	// PARAMS: string: string to test
	//         from_encoding: source encoding of this string
	//         to_encoding: target encoding of this string
	// RETURN: false if no problem
	//         on error, return array with characters that failed in the convert
	// DESC  : test if a string can be safely convert between encodings. mostly utf8 to shift jis
	//         the default compare has a possibility of failure, especially with windows
	//         it is recommended to the following in the script which uses this method:
	//         mb_substitute_character(0x2234);
	//         $class->mbErrorChar = '∴';
	//         if check to Shift JIS
	//         if check to ISO-2022-JP
	//         if check to ISO-2022-JP-MS
	//         set three dots (∴) as wrong character for correct convert error detect
	//         (this char is used, because it is one of the least used ones)
	public function checkConvertEncoding($string, $from_encoding, $to_encoding)
	{
		// convert to target encoding and convert back
		$temp = mb_convert_encoding($string, $to_encoding, $from_encoding);
		$compare = mb_convert_encoding($temp, $from_encoding, $to_encoding);
		// if string does not match anymore we have a convert problem
		if ($string != $compare) {
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

	// METHOD: convertEncoding
	// PARAMS: string to convert
	//         target encoding (to which to convert to)
	//         optional source encoding
	// RETURN: converted string
	// DESC  : detects the source encoding of the string and if doesn't match to the given target encoding it convert is
	public static function convertEncoding($string, $to_encoding, $source_encoding = '')
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

	// METHOD: __crc32b
	// WAS   : _crc32b
	// PARAMS: string
	// RETURN: old (wrong) crc32b hash
	// DESC  : checks php version and if >=5.2.7 it will flip the string
	public function __crc32b($string)
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

	// METHOD: __sha1Short
	// WAS   : _sha1_short
	// PARAMS: string, flag to use sha
	// RETURN: sha1 short (9 chars), but current calls __crc32b
	// DESC  : replacement for __crc32b call
	public function __sha1Short($string, $use_sha = false)
	{
		if ($use_sha) {
			return substr(hash('sha1', $string), 0, 9);
		} else {
			return $this->__crc32b($string);
		}
	}

	// METHOD: __hash
	// WAS   : _hash
	// PARAMS: string, type of hash to use
	// RETURN: hashed string
	// DESC  : replacemend for __crc32b call (alternate)
	//         defaults to adler 32, fnv132, fnv1a32, joaat
	//         all that create 8 char long hashes
	public function __hash($string, $hash_type = 'adler32')
	{
		if (!in_array($hash_type, array('adler32', 'fnv132', 'fnv1a32', 'joaat'))) {
			$hash_type = 'adler32';
		}
		return hash($hash_type, $string);
	}

	// METHOD: checkPHPVersion
	// PARAMS: $min_version: minimum version. in format x, x.y or x.y.z
	//         $max_version: default empty, else in same format as min version
	// RETURN: true if ok, false if not matching version
	// DESC  : checks if running PHP version matches given PHP version (min or max)
	public static function checkPHPVersion($min_version, $max_version = '')
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
		$min_version = $version[0] * 10000 + $version[1] * 100 + $version[2];
		if ($max_version) {
			$version = explode('.', $max_version);
			$max_version = $version[0] * 10000 + $version[1] * 100 + $version[2];
			// drop out if min is bigger max, equal size is okay, that would be only THIS
			if ($min_version > $max_version) {
				return false;
			}
		}
		// set the php version id
		if (!defined('PHP_VERSION_ID')) {
			$version = explode('.', phpversion());
			// creates something like 50107
			define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
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

	// METHOD: cryptInit
	// PARAMS: none
	// RETURN: none
	// DESC  : inits crypt settings for the crypt functions
	//         this function NEEDS (!) to be called BEFORE any of the crypt functions is called
	//         there is no auto init for this at the moment
	private function cryptInit()
	{
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

	// METHOD: cryptSaltString
	// PARAMS: random string length, default is 22 (for blowfish crypt)
	// RETURN: random string
	// DESC  : creates a random string from alphanumeric characters: A-Z a-z 0-9 ./
	private function cryptSaltString($nSize = 22)
	{
		// A-Z is 65,90
		// a-z is 97,122
		// 0-9 is 48,57
		// ./ is 46,47 (so first lower limit is 46)
		$min = array (46, 65, 97);
		$max = array (57, 90, 122);
		$chars = array ();
		for ($i = 0, $iMax = count($min); $i < $iMax; $i ++) {
			for ($j = $min[$i]; $j <= $max[$i]; $j ++) {
				$chars[] = chr($j);
			}
		}
		// max should be 63 for this case
		$max_rand = count($chars) - 1;
		// create the salt part
		for ($i = 1; $i <= $nSize; $i ++) {
			$salt_string .= $chars[mt_rand(0, $max_rand)];
		}
		return $salt_string;
	}

	// METHOD: cryptString
	// PARAMS: string to be crypted (one way)
	// RETURN: encrypted string
	// DESC  : encrypts the string with blowfish and returns the full string + salt part that needs to be stored somewhere (eg DB)
	public function cryptString($string)
	{
		// the crypt prefix is set in the init of the class
		// uses the random string method to create the salt
		return crypt($string, $this->cryptSaltPrefix.$this->cryptSaltString($this->cryptSaltSize).$this->cryptSaltSuffix);
	}

	// METHOD: verifyCryptString
	// PARAMS: plain string (eg password)
	//         full crypted string (from cryptString
	// RETURN: true on matching or false for not matching
	// DESC  : compares the string with the crypted one, is counter method to cryptString
	public function verifyCryptString($string, $crypt)
	{
		// the full crypted string needs to be passed on to the salt, so the init (for blowfish) and salt are passed on
		if (crypt($string, $crypt) == $crypt) {
			return true;
		} else {
			return false;
		}
	}

	// *** COLORS ***

	// METHOD: hex2rgb
	// PARAMS: hexstring, flag to return as string (true/false), string seperator: default: ,
	// RETURN: array with RGB or a string with the seperator
	// DESC  : converts a hex RGB color to the int numbers
	public static function hex2rgb($hexStr, $returnAsString = false, $seperator = ',')
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
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}

	// METHOD: rgb2hex
	// PARAMS: red, green, blue (0-255)
	// RETURN: string with hex rgb color plus # in front
	// DESC  : converts the rgb values from int data to the valid rgb html hex string
	public static function rgb2hex($red, $green, $blue)
	{
		$hex_color = '#';
		foreach (array ('red', 'green', 'blue') as $color) {
			// pad left with 0
			$hex_color .= str_pad(dechex($$color), 2, '0', STR_PAD_LEFT);
		}
		return $hex_color;
	}

	// METHOD: rgb2hsb
	// PARAMS: red, green, blue (0-255)
	// RETURN: array with hue (0-360), sat (0-100%), brightness/value (0-100%)
	// DESC  : converts RGB to HSB/V values
	public static function rgb2hsb($r, $g, $b)
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

	// METHOD: hsb2rgb
	// PARAMS: hue (0-360), saturation (0-1), brightness/value (0-1)
	// RETURN: array with red, blue, green
	// DESC  : converts HSB/V to RGB values RGB is full INT
	public static function hsb2rgb($H, $S, $V)
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
				$gre = $t;
				$blu = $p;
				break;
			case 1:
				$red = $q;
				$gre = $V;
				$blu = $p;
				break;
			case 2:
				$red = $p;
				$gre = $V;
				$blu = $t;
				break;
			case 3:
				$red = $p;
				$gre = $q;
				$blu = $V;
				break;
			case 4:
				$red = $t;
				$gre = $p;
				$blu = $V;
				break;
			case 5:
				$red = $V;
				$gre = $p;
				$blu = $q;
				break;
			default:
				$red = 0;
				$gre = 0;
				$blue = 0;
		}

		return array(round($red * 255), round($gre * 255), round($blu * 255));
	}

	// METHOD: rgb2hsl
	// PARAMS: red, blue, green (all 0-255)
	// RETURN: array with hue (0-360), saturation (0-100%) and luminance (0-100%)
	// DESC  : converts a RGB (0-255) to HSL
	public static function rgb2hsl($r, $g, $b)
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

	// METHOD: hsl2rgb
	// PARAMS: hue: 0-360 (degrees)
	//		   saturation: 0-1
	//         luminance: 0-1
	// RETURN: array with RGB as full int
	// DESC  : converts an HSL to RGB
	public static function hsl2rgb($h, $s, $l)
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

	// METHOD: rgb2html
	// PARAMS: red, green, blue
	// RETRUN valid # prefix hex html color string
	// DESC  : converts and int RGB to the HTML color string in hex format
	public static function rgb2html($red, $green, $blue)
	{
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

	// METHOD: getEmailType
	// PARAMS: email, short == false
	// RETURN: string for email type, eg "pc", "docomo", etc
	// DESC  : guesses the email type (mostly for mobile) from the domain
	//         if second is set to true, it will return short naming scheme (only provider)
	public function getEmailType($email, $short = false)
	{
		// trip if there is no email address
		if (!$email) {
			return "invalid";
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
			return "pc";
		} else {
			return "pc_html";
		}
	}

	// METHOD: getShortEmailType
	// PARAMS: long email type (not email)
	// RETURN: short email type
	// DESC  : gets the short email type from a long email type
	public function getShortEmailType($email_type)
	{
		return $this->mobile_email_type_short[$email_type];
	}

	// METHOD: printDateTime
	// PARAMS: year, month, day, hour, min: the date and time values
	//         suffix: additional info printed after the date time variable in the drop down,
	//         also used for ID in the on change JS call
	//         min_steps: default is 1 (minute), can set to anything, is used as sum up from 0
	//         name_pos_back: default false, if set to true, the name will be printend
	//                        after the drop down and not before the drop down
	// RETURN: HTML formated strings for drop down lists of date and time
	// DESC  : print the date/time drop downs, used in any queue/send/insert at date/time place
	public static function printDateTime($year, $month, $day, $hour, $min, $suffix = '', $min_steps = 1, $name_pos_back = false)
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
		$max_year = date("Y", $timestamp) + 1;

		// preset year, month, ...
		$year = (!$year) ? date("Y", $timestamp) : $year;
		$month = (!$month) ? date("m", $timestamp) : $month;
		$day = (!$day) ? date("d", $timestamp) : $day;
		$hour = (!$hour) ? date("H", $timestamp) : $hour;
		$min = (!$min) ? date("i", $timestamp) : $min; // add to five min?
		// max days in selected month
		$days_in_month = date("t", strtotime($year."-".$month."-".$day." ".$hour.":".$min.":0"));

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

	// METHOD: htmlent
	// PARAMS: string to encode
	// RETURN: encoded string
	// DESC  : full wrapper for html entities
	public function htmlent($string)
	{
		return htmlentities($string, ENT_COMPAT|ENT_HTML401, 'UTF-8', false);
	}

	// METHOD: setFormToken
	// PARAMS: session name, if not set then default is form_token
	// RETURN: form token
	// DESC  : sets a form token in a session and returns form token
	public function setFormToken($name = 'form_token')
	{
		// current hard set to sha256
		$token = uniqid(hash('sha256', rand()));
		$_SESSION[$name] = $token;
		return $token;
	}

	// METHOD: validateFormToken
	// PARAMS: form token, session name (default form_token)
	// RETURN: true or false
	// DESC  : checks if the form token matches the session set form token
	public function validateFormToken($token, $name = 'form_token')
	{
		return $_SESSION[$name] === $token;
	}

	// *************************************************************
	// *** DEPRICATED CALL ***
	// *************************************************************
	// should be removed later
	public function checkConvert($string, $from_encoding, $to_encoding)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->checkConvertEncoding($string, $from_encoding, $to_encoding);
	}

	// *************************************************************
	// *** COMPATIBILITY METHODS ***
	// those methods are deprecated function call names
	// they exist for backwards compatibility only
	// *************************************************************

	public function running_time($simple = false)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->runningTime($simple);
	}

	public static function print_time($set_microtime = -1)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::printTime($set_microtime);
	}

	private function fdebug_fp($flag = '')
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->fdebugFP($flag);
	}

	public function debug_for($type, $flag)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->debugFor($type, $flag);
	}

	public function get_caller_method($level = 2)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->getCallerMethod($level);
	}

	public function merge_errors($error_msg = array ())
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->mergeErrors($error_msg);
	}

	public function print_error_msg($string = '')
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->printErrorMsg($string);
	}

	private function write_error_msg($level, $error_string)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->writeErrorMsg($level, $error_string);
	}

	public function reset_error_msg($level = '')
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->resetErrorMsg($level);
	}

	public static function print_ar($array)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::printAr($array);
	}

	public function magic_links($string, $target = "_blank")
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->magicLinks($string, $target);
	}

	private function create_url($href, $atag, $_1, $_2, $_3, $name, $class)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->createUrl($href, $atag, $_1, $_2, $_3, $name, $class);
	}

	private function create_email($mailto, $atag, $_1, $_2, $_3, $title, $class)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->createEmail($mailto, $atag, $_1, $_2, $_3, $title, $class);
	}

	public function get_host_name()
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->getHostName();
	}

	public static function get_page_name($strip_ext = 0)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::getPageName($strip_ext);
	}

	public static function get_filename_ending($filename)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::getFilenameEnding($filename);
	}

	public static function array_search_recursive($needle, $haystack, $key_lookin = "")
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::arraySearchRecursive($needle, $haystack, $key_lookin);
	}

	public static function array_search_recursive_all($needle, $haystack, $key, $path = null)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::arraySearchRecursiveAll($needle, $haystack, $key, $path);
	}

	public static function array_search_simple($array, $key, $value)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::arraySearchSimple($array, $key, $value);
	}

	public static function _mb_mime_encode($string, $encoding)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::__mbMimeEncode($string, $encoding);
	}

	public function _crc32b($string)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->__crc32b($string);
	}

	public function _sha1_short($string, $use_sha = false)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->__sha1Short($string, $use_sha);
	}

	public function _hash($string, $hash_type = 'adler32')
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return $this->__hash($string, $hash_type);
	}

	public static function in_array_any($needle, $haystack)
	{
		error_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);
		return Basic::inArrayAny($needle, $haystack);
	}
}

# __END__
