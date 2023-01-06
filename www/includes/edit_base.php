<?php

/********************************************************************
* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
* CREATED: 2003/06/10
* SHORT DESCRIPTION:
*	central include for all edit_pages
*	 - edit_groups.php
*	 - edit_languages.php
*	 - edit_pages.php
*	 - edit_schemes.php
*	 - edit_users.php
*	 - edit_visible_group.php
* HISTORY:
* 2005/06/30 (cs) remove color settings, they are in CSS File now
* 2005/06/22 (cs) moved load of config array into form class, set lang
*                 and lang is must set var for form class; removed the
*                 page name setting, moved it into the form class,
*                 emove all HTML from main page
* 2004/09/30 (cs) changed layout to fit default layout & changed LIBS, etc
* 2003-06-10: creation of this page
*********************************************************************/

declare(strict_types=1);

ob_start();
require 'config.php';

// should be utf8
header("Content-type: text/html; charset=" . DEFAULT_ENCODING);
// start session
$session = new \CoreLibs\Create\Session(EDIT_SESSION_NAME);
// init logger
$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => LOG_FILE_ID . 'EditBase',
	'print_file_date' => true,
	'per_class' => true,
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
// db connection
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
// login page
$login = new CoreLibs\ACL\Login($db, $log, $session);
// space for setting special debug flags
// $login->log->setLogLevelAll('debug', true);
// lang, path, domain
// pre auto detect language after login
$locale = \CoreLibs\Language\GetLocale::setLocale();
// set lang and pass to smarty/backend
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
);
// flush and start
ob_end_flush();

// FIXME: only extract _POST data that is needed
// FIXME: update table_arrays reader to use other than $_GLOBALS
extract($_POST, EXTR_SKIP);

// init smarty and form class
$edit_base = new CoreLibs\Admin\EditBase(DB_CONFIG, $log, $l10n, $locale);
// creates edit pages and runs actions
$edit_base->editBaseRun();

// __END__
