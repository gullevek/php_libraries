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
$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => BASE_NAME . 'EditBase',
	'log_level' => \CoreLibs\Logging\Logging::processLogLevel(DEBUG_LEVEL),
	'log_per_date' => true,
	'log_per_class' => true,
]);
// db connection
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
// login page
$login = new CoreLibs\ACL\Login(
	$db,
	$log,
	$session,
	[
		'auto_login' => true,
		'default_acl_level' => DEFAULT_ACL_LEVEL,
		'logout_target' => '',
		'site_locale' => SITE_LOCALE,
		'site_domain' => SITE_DOMAIN,
		'site_encoding' => SITE_ENCODING,
		'locale_path' => BASE . INCLUDES . LOCALE,
	]
);
// space for setting special debug flags
// $login->log->setLogLevelAll('debug', true);
// lang, path, domain
// pre auto detect language after login
$locale = $login->loginGetLocale();
// set lang and pass to smarty/backend
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
	$locale['encoding']
);
// flush and start
ob_end_flush();

// init smarty and form class
$edit_base = new CoreLibs\Admin\EditBase(
	DB_CONFIG,
	$log,
	$l10n,
	$login,
	[
		'cache_id' => CACHE_ID,
		'compile_id' => COMPILE_ID
	]
);
// creates edit pages and runs actions
$edit_base->editBaseRun(
	BASE . INCLUDES . TEMPLATES . CONTENT_PATH,
	BASE . TEMPLATES_C,
	BASE . CACHE,
	EDIT_BASE_STYLESHEET,
	DEFAULT_ENCODING,
	LAYOUT . CSS,
	LAYOUT . JS,
	ROOT,
	CONTENT_PATH
);

// __END__
