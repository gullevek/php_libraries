<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* configuration file for core host settings
* - DB access name (array group from config.db)
* - location (test/stage/live)
* - debug flag (true/false)
* - DB path (eg PUBLIC_SCHEMA)
* - stie lang
* HISTORY:
*********************************************************************/

// other master config to attach
// $__LOCAL_CONFIG = array(
// 	'db_host' => '',
// 	'location' => '',
//	'debug_flag' => true,
//	'site_lang' => 'en_utf8',
//	'login_enabled' => true
// );

// each host has a different db_host
$SITE_CONFIG = array(
	// development host
	'soba.tokyo.tequila.jp' => array(
		// db config selection
		'db_host' => 'test',
		// other db connections
		// 'db_host_target' => '',
		// 'db_host_other' => '',
		// location flagging (test/dev/live) for debug output
		'location' => 'test',
		// show DEBUG override
		'debug_flag' => true,
		// site language
		'site_lang' => 'en_utf8',
		// enable/disable login override
		'login_enabled' => true
	),
	// 'other.host.com' => $__LOCAL_CONFIG
);

// __END__