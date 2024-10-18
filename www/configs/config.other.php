<?php // phpcs:ignore PSR1.Files.SideEffects

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2019/10/28
* SHORT DESCRIPTION:
* other global constant variables
* HISTORY:
*********************************************************************/

declare(strict_types=1);

// style sheet for the edit base interface
define('EDIT_BASE_STYLESHEET', 'edit.css');

// define('SOME_ID', <SOME VALUE>);

/************* CONVERT *******************/
// this only needed if the external thumbnail create is used
$paths = [
	'/bin',
	'/usr/bin',
	'/usr/local/bin',
];
// find convert
foreach ($paths as $path) {
	if (
		file_exists($path . DIRECTORY_SEPARATOR . 'convert') &&
		is_file($path . DIRECTORY_SEPARATOR . 'convert')
	) {
		// image magick convert location
		define('CONVERT', $path . DIRECTORY_SEPARATOR . 'convert');
		break;
	}
}
unset($paths);

// __END__
