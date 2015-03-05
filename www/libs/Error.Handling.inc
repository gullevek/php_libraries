<?
    /*********************************************************************
    * AUTHOR: Clemens Schwaighofer
    * CREATED: 2011/2/8
	* DESCRIPTION: pre function to collect all non critical errors into a log file if possible
	* include this file at the very beginning of the script to get the notices, strict, etc messages.
	* error etc will still be written to the log/display
	*********************************************************************/

	// define the base working directory outside because in the function it might return undefined
	DEFINE('CURRENT_WORKING_DIR', getcwd().'/');

	// METHOD: MyErrorHandler
	// PARAMS: $type: the error code from PHP
	//         $message: the error message from php
	//         $file: in which file the error happend. this is the source file (eg include)
	//         $line: in which line the error happened
	//         $context: array with all the variable
	// RETURN: true, so cought errors do not get processed by the PHP error engine
	// DESC:   will catch any error except E_ERROR and try to write them to the log file in log/php_error-<DAY>.llog
	//         if this fails, it will print the data to the window via echo
	function MyErrorHandler ($type, $message, $file, $line, $context)
	{
		if (!(error_reporting() & $type) && !SHOW_ALL_ERRORS) {
			// This error code is not included in error_reporting
			return;
		}
		// ERROR LEVEL
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
			4096 => 'E_RECOVERABLE_ERROR',
			8192 => 'E_DEPRICATED',
			16384 => 'E_USER_DEPRICATED',
			30719  => 'E_ALL'
		);

		// get the current page name (strip path)
		$page_temp = explode("/", $_SERVER["PHP_SELF"]);
		// the output string:
		// [] current timestamp
		// {} the current page name in which the error occured (running script)
		// [] the file where the error actually happened
		// <> the line number in this file
		// [|] error name and error number
		// : the php error message
		$output = '['.date("Y-m-d H:i:s").'] {'.array_pop($page_temp).'} ['.$file.'] <'.$line.'> ['.$error_level[$type].'|'.$type.']: '.$message;
		# try to open file
		$ROOT = CURRENT_WORKING_DIR;
		$LOG = 'log/';
		// if the log folder is not found, try to create it
		if (!is_dir($ROOT.$LOG) && !is_file($ROOT.LOG))
			$ok = mkdir($ROOT.$LOG);
		$error = 0;
		// again, if the folder now exists, else set error flag
		if (is_dir($ROOT.$LOG))
		{
			$fn = $ROOT.$LOG.'php_errors-'.date('Y-m-d').'.log';
			// when opening, surpress the warning so we can catch the no file pointer below without throwing a warning for this
			$fp = @fopen($fn, 'a');
			// write if we have a file pointer, else set error flag
			if ($fp)
			{
				fwrite($fp, $output."\n");
				fclose($fp);
			}
			else
				$error = 1;
		}
		else
			$error = 1;

		// if the above writing failed
		if ($error)
		{
			// if the display errors is on
			// pretty print output for HTML
			if (ini_get("display_errors"))
			{
				echo "<div style='border: 1px dotted red; background-color: #ffffe5; color: #000000; padding: 5px; margin-bottom: 2px;'>";
				echo "<div style='color: orange; font-weight: bold;'>".$error_level[$type].":</div>";
				echo "<b>$message</b> on line <b>$line</b> in <b>$file</b>";
				echo "</div>";
			}
			// if write to log is on
			// simplified, remove datetime for log file
			if (ini_get('log_errors')) 
				error_log('{'.$page_temp.'} ['.$file.'] <'.$line.'> ['.$error_level[$type].'|'.$type.']: '.$message);
		}
		// return true, to avoid that php calls its own error stuff
		// if E_ERROR, the php one gets called anyway
		return true;
	}

	// init the error handler
	set_error_handler("MyErrorHandler");
?>
