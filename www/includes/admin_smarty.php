<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2005/07/12
* SHORT DESCRIPTION:
* default smarty vars, and create output template for smarty
* HISTORY:
*********************************************************************/

declare(strict_types=1);

/******
NOTE THAT THIS INCLUDE IS OBSOLETE
USE THE BELOW FUNCTION CALL IN THE SCRIPT ITSELF
*******/
trigger_error(
	'admin_smarty.php is deprecated. Use SmartyExtended->setSmartyVarsAdmin(); or setSmartyVarsFrontend();',
	E_USER_DEPRECATED
);
$smarty->setSmartyVarsAdmin();

// __END__
