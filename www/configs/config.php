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
for ($dir_pos = 0, $dir_max = count(explode(DIRECTORY_SEPARATOR, __DIR__)); $dir_pos <= $dir_max; $dir_pos++) {
	$CONFIG_PATH_PREFIX .= '..' . DIRECTORY_SEPARATOR;
	if (file_exists($CONFIG_PATH_PREFIX . CONFIG_PATH . 'config.master.php')) {
		// check if there is an read env file, load it
		if (file_exists($CONFIG_PATH_PREFIX . CONFIG_PATH . 'read_env_file.php')) {
			require $CONFIG_PATH_PREFIX . CONFIG_PATH . 'read_env_file.php';
			// load env variables first
			readEnvFile($CONFIG_PATH_PREFIX . CONFIG_PATH);
		}
		// then load master config file that loads all other config files
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
