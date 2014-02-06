<?
	/********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2004/12/21
	* SHORT DESCRIPTION:
	*	extends smarty with the l10n class so I can use __(, etc calls
	* HISTORY:
	* 2005/06/22 (cs) include smarty class here, so there is no need to include it in the main file
	*********************************************************************/

	// read in the Smarty class for definition
	// use smarty BC for backwards compability
	require_once(SMARTY.'SmartyBC.class.php');

	class SmartyML extends SmartyBC
	{
		public $l10n;

		// constructor class, just sets the language stuff
		public function __construct($lang)
		{
			SmartyBC::__construct();
			require_once(LIBS."Class.l10n.inc");
			$this->l10n = new l10n($lang);
			// variable variable register
			$this->register_modifier('getvar', array(&$this, 'get_template_vars'));
		}

	}
?>
