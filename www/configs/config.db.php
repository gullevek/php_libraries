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
		'db_name' => $_ENV['DB_NAME.TEST'] ?? '',
		'db_user' => $_ENV['DB_USER.TEST'] ?? '',
		'db_pass' => $_ENV['DB_PASS.TEST'] ?? '',
		'db_host' => $_ENV['DB_HOST.TEST'] ?? '',
		'db_port' => $_ENV['DB_PORT.PG'] ?? 5432,
		'db_schema' => 'public',
		'db_type' => 'pgsql',
		'db_encoding' => '',
		'db_ssl' => 'allow', // allow, disable, require, prefer
		// 'db_convert_type' => ['json'] // on, json, numeric, bytea
	],
	// same as above, but uses pg bouncer
	'test_pgbouncer' => [
		'db_name' => $_ENV['DB_NAME.TEST'] ?? '',
		'db_user' => $_ENV['DB_USER.TEST'] ?? '',
		'db_pass' => $_ENV['DB_PASS.TEST'] ?? '',
		'db_host' => $_ENV['DB_HOST.TEST'] ?? '',
		'db_port' => $_ENV['DB_PORT.PG_BOUNCER'] ?? 5432,
		'db_schema' => 'public',
		'db_type' => 'pgsql',
		'db_encoding' => '',
		'db_ssl' => 'allow', // allow, disable, require, prefer
	],
];

// __END__
