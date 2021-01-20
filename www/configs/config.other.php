<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2019/10/28
* SHORT DESCRIPTION:
* other global constant variables
* HISTORY:
*********************************************************************/

// DEFINE('SOME_ID', <SOME VALUE>);

/************* CONVERT *******************/
// this only needed if the external thumbnail create is used
$paths = [
	'/bin',
	'/usr/bin',
	'/usr/local/bin'
];
// find convert
foreach ($paths as $path) {
	if (file_exists($path.DS.'convert') && is_file($path.DS.'convert')) {
		// image magick convert location
		define('CONVERT', $path.DS.'convert');
	}
}
unset($paths);

// __END__
