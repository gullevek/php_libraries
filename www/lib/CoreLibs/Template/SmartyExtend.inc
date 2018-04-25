<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2004/12/21
* SHORT DESCRIPTION:
*	extends smarty with the l10n class so I can use __(, etc calls
* HISTORY:
* 2005/06/22 (cs) include smarty class here, so there is no need to include it in the main file
*********************************************************************/

namespace CoreLibs\Template;

// I need to manually load Smarty BC here (it is not namespaced)
require_once(BASE.LIB.SMARTY.'SmartyBC.class.php');
// So it doesn't start looking around in the wrong naemspace as smarty doesn't have one
use SmartyBC;

class SmartyExtend extends SmartyBC
{
	public $l10n;

	// constructor class, just sets the language stuff
	public function __construct($lang)
	{
		SmartyBC::__construct();
		$this->l10n = new \CoreLibs\Language\L10n($lang);
		// variable variable register
		$this->register_modifier('getvar', array(&$this, 'get_template_vars'));
	}
}

# __END__
