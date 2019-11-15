<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2007/09/03
* SHORT DESCRIPTION:
* set paths & language variables
* HISTORY:
*********************************************************************/

/******
NOTE THAT THIS INCLUDE IS OBSOLETE
USE THE BELOW FUNCTION CALL IN THE SCRIPT ITSELF
*******/
trigger_error('admin_set_paths.php is deprecated. Use SmartyExtended->setSmartyPaths();', E_USER_DEPRECATED);
if ($smarty) {
	$smarty->setSmartyPaths();
}

// __END__
