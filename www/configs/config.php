<?php // phpcs:ignore PSR1.Files.SideEffects

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* pre config included -> includes master config
* HISTORY:
*********************************************************************/

declare(strict_types=1);

define('CONFIG_PATH', 'configs' . DIRECTORY_SEPARATOR);
// config path prefix search, start with 0, got down each level __DIR__ has,
// if nothing found -> bail
$CONFIG_PATH_PREFIX = '';
// base path for loads
$__DIR__PATH = __DIR__ . DIRECTORY_SEPARATOR;
// don't load autoloader twice
$end_autoload = false;
for (
	$dir_pos = 0, $dir_max = count(explode(DIRECTORY_SEPARATOR, __DIR__));
	$dir_pos <= $dir_max;
	$dir_pos++
) {
	$CONFIG_PATH_PREFIX .= '..' . DIRECTORY_SEPARATOR;
	if ($end_autoload === false) {
		/************* AUTO LOADER *******************/
		// composer auto loader, in composer.json file add classmap for lib/:
		// "autoload": {
		// 	"classmap": [
		// 		"lib/"
		// 	]
		// },
		// NOTE: MUST RUN composer dump-autoload if file/class names are
		// changed or new ones are added
		if (
			is_file(
				$__DIR__PATH . $CONFIG_PATH_PREFIX
					. 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
			)
		) {
			require $__DIR__PATH . $CONFIG_PATH_PREFIX
				. 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
			$end_autoload = true;
		}
	}
	/************* MASTER CONFIG *******************/
	if (
		is_file($__DIR__PATH . $CONFIG_PATH_PREFIX . CONFIG_PATH . 'config.master.php')
	) {
		// load enviorment file if it exists
		\gullevek\dotEnv\DotEnv::readEnvFile(
			$__DIR__PATH . $CONFIG_PATH_PREFIX . CONFIG_PATH
		);
		// load master config file that loads all other config files
		require $__DIR__PATH . $CONFIG_PATH_PREFIX . CONFIG_PATH . 'config.master.php';
		break;
	}
}
// fail if no base DIR is not set
if (!defined('DIR')) {
	exit('Base config could not be loaded');
}

// __END__
