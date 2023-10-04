<?php

$set = 0;
foreach (['/../..', '/..', '/../../www', '/../www'] as $src) {
	if (is_file(dirname(__DIR__) . $src . '/vendor/autoload.php')) {
		require dirname(__DIR__) . $src . '/vendor/autoload.php';
		$set = 1;
		break;
	}
}
if (!$set) {
	die("Cannot find /vendor/autoload.php in reference to: " . dirname(__DIR__));
}

// __END__
