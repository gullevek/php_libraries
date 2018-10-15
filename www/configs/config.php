<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2018/10/11
* SHORT DESCRIPTION:
* pre config included -> includes master config
* HISTORY:
*********************************************************************/

define('CONFIG_PATH', 'configs'.DIRECTORY_SEPARATOR);
// config path prefix search, start with 0, got down each level __DIR__ has, if nothing found -> bail
$CONFIG_PATH_PREFIX = '';
for ($dir_pos = 0, $dir_max = count(explode('/', __DIR__)); $dir_pos <= $dir_max; $dir_pos ++) {
	$CONFIG_PATH_PREFIX .= '..'.DIRECTORY_SEPARATOR;
	if (file_exists($CONFIG_PATH_PREFIX.CONFIG_PATH.'config.inc')) {
		require $CONFIG_PATH_PREFIX.CONFIG_PATH.'config.inc';
		break;
	}
}
// fail if no base DS is not set
if (!defined('DS')) {
	exit('Base config unloadable');
}

// __END__
