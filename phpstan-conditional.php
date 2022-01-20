<?php

// conditional formats for PHP versions

declare(strict_types=1);

$config = [];

if (PHP_VERSION_ID >= 8_00_00) {
	// Change of signature in PHP 8.1
	/* $config['parameters']['ignoreErrors'][] = [
		'message' => '~Parameter #1 \$(result|connection) of function pg_\w+ '
			. 'expects resource(\|null)?, object\|resource given\.~',
		'path' => 'www/lib/CoreLibs/DB/SQL/PgSQL.php',
		// 'count' => 1,
	]; */
}

return $config;

// __END_
