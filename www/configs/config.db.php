<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* configuration file for database settings
* HISTORY:
*********************************************************************/

declare(strict_types=1);

// please be VERY carefull only to change the right side
$DB_CONFIG = [
	'test' => [
		'db_name' => 'clemens',
		'db_user' => 'clemens',
		'db_pass' => 'clemens',
		'db_host' => 'db.tokyo.tequila.jp',
		'db_port' => 5432,
		'db_schema' => 'public',
		'db_type' => 'pgsql',
		'db_encoding' => '',
		'db_ssl' => 'disable' // allow, disable, require, prefer
	],
];

// __END__
