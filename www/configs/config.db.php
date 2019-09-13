<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* configuration file for database settings
* HISTORY:
*********************************************************************/

// please be VERY carefull only to change the right side
$DB_CONFIG = array (
	'test' => array (
		'db_name' => 'gullevek',
		'db_user' => 'gullevek',
		'db_pass' => 'gullevek',
		'db_host' => 'db.tokyo.tequila.jp',
		'db_port' => '5432',
		'db_schema' => 'public',
		'db_type' => 'pgsql',
		'db_encoding' => '',
		'db_ssl' => 'disable' // allow, disable, require, prefer
	)
);

// __END__
