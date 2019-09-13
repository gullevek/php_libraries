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

// each host has a different db_host
// development host
$DB_HOST['soba.tokyo.tequila.jp'] = 'test';
// target host (live)
//	$DB_TARGET_HOST['soba'] = '<DB ID>';
// url redirect database
//	$DB_URL_REDIRECT_HOST['soba'] = '<DB ID>';
// location flagging
// test/dev/live
$LOCATION['soba.tokyo.tequila.jp'] = 'test';
// show DEBUG override
// true/false
$DEBUG_FLAG['soba.tokyo.tequila.jp'] = true;
// set postgresql paths (schemas)
$DB_PATH['soba.tokyo.tequila.jp'] = PUBLIC_SCHEMA;
// site language
$SITE_LANG['soba.tokyo.tequila.jp'] = 'en_utf8';

// __END__
