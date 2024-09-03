<?php // phpcs:ignore PSR1.Files.SideEffects

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* configuration file for core path settings
* CSV target paths, and other download access URLS or paths needed
* HISTORY:
*********************************************************************/

declare(strict_types=1);

// find trigger name "admin/" or "frontend/" in the getcwd() folder
$folder = '';
foreach (['admin', 'frontend'] as $_folder) {
	if (strstr(getcwd() ?: '', DIRECTORY_SEPARATOR . $_folder)) {
		$folder = $_folder;
		break;
	}
}
// if content path is empty, fallback is default
if (empty($folder)) {
	$folder = 'default';
}
define('CONTENT_PATH', $folder . DIRECTORY_SEPARATOR);

// File and Folder paths
// ID is TARGET (first array element)
/*$PATHS = [
	'test' => [
		'csv_path' => '',
		'perl_bin' => '',
		'other_url' => '',
	],
];*/

// __END__
