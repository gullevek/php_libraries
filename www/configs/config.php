<?php // phpcs:ignore warning

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* pre config included -> includes master config
* HISTORY:
*********************************************************************/

declare(strict_types=1);

define('CONFIG_PATH', 'configs' . DIRECTORY_SEPARATOR);
// config path prefix search, start with 0, got down each level __DIR__ has, if nothing found -> bail
$CONFIG_PATH_PREFIX = '';
$end_autoload = false;
for ($dir_pos = 0, $dir_max = count(explode(DIRECTORY_SEPARATOR, __DIR__)); $dir_pos <= $dir_max; $dir_pos++) {
	$CONFIG_PATH_PREFIX .= '..' . DIRECTORY_SEPARATOR;
	if ($end_autoload === false) {
		/************* AUTO LOADER *******************/
		// read auto loader for lib only
		// It is recommended to setup basic composer and use just one auto loader
		// if (is_file($CONFIG_PATH_PREFIX . 'lib' . DIRECTORY_SEPARATOR . 'autoloader.php')) {
		// 	require $CONFIG_PATH_PREFIX . 'lib' . DIRECTORY_SEPARATOR . 'autoloader.php';
		//	$end_autoload = true;
		// }
		// composer auto loader, IF composer.json file includes classmap for lib/:
		// "autoload": {
		// 	"classmap": [
		// 		"lib/"
		// 	]
		// },
		// NOTE: MUST RUN composer dump-autoload if file/class names are changed or added
		// NOTE BASE: __DIR__ . DIRECTORY_SEPARATOR . '..' DIRECTORY_SEPARATOR;
		// load auto loader
		if (is_file($CONFIG_PATH_PREFIX . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
			require $CONFIG_PATH_PREFIX . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
			$end_autoload = true;
		}
		// load enviorment file if it exists
		\CoreLibs\Get\ReadEnvFile::readEnvFile($CONFIG_PATH_PREFIX . CONFIG_PATH);
	}
	/************* MASTER CONFIG *******************/
	if (is_file($CONFIG_PATH_PREFIX . CONFIG_PATH . 'config.master.php')) {
		// load master config file that loads all other config files
		require $CONFIG_PATH_PREFIX . CONFIG_PATH . 'config.master.php';
		break;
	}
}
// fail if no base DS is not set
if (!defined('DS')) {
	exit('Base config unloadable');
}
// find trigger name "admin/" or "frontend/" in the getcwd() folder
foreach (['admin', 'frontend'] as $folder) {
	if (strstr(getcwd() ?: '', DS . $folder)) {
		define('CONTENT_PATH', $folder . DS);
		break;
	}
}

// __END__
