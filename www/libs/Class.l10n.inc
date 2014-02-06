<?
	/*********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2004/11/18
	* VERSION: 0.1.1
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
	* SHORT DESCRIPTION:
	* 	init class for gettext. Original was just a function & var setting include for wordpress.
	*	I changed that to a class to be more portable with my style of coding
	*
	* PUBLIC VARIABLES
	*	
	* PRIVATE VARIABLES
	*	
	* PUBLIC METHODS
	*	__:	returns string (translated or original if not found)
	*	_e:	echos out string (translated or original if not found)
	*	__ngettext:	should return plural. never tested this.
	*
	*   PRIVATE METHODS
	*
	* HISTORY:
	* 2005/10/17 (cs) made an on the fly switch method (reload of lang)
	*********************************************************************/

	require_once(LIBS."Class.Basic.inc");

	class l10n extends basic
	{
		private $lang = '';
		private $mofile = '';
		private $input;
		private $l10n;

		public function __construct($lang = '', $path = '')
		{
			require_once(LIBS.'streams.php');
			require_once(LIBS.'gettext.php');

			if (!$lang)
				$this->lang = 'en';
			else
				$this->lang = $lang;

			if (!$path)
				$path = DEFAULT_TEMPLATE;
			if (!is_dir(LAYOUT.$path.LANG))
				$path = '';

			$this->mofile = LAYOUT.$path.LANG.$this->lang.".mo";

			// check if get a readable mofile
			if (is_readable($this->mofile))
				$this->input = new FileReader($this->mofile);
			else
				$this->input = false;

			$this->l10n = new gettext_reader($this->input);
		}

		// reloads the mofile, if the location of the lang file changes
		public function l10nReloadMOfile($lang, $path = DEFAULT_TEMPLATE)
		{
			$old_mofile = $this->mofile;
			$old_lang = $this->lang;

			$this->lang = $lang;

			if (!is_dir(LAYOUT.$path.LANG))
				$path = '';

			$this->mofile = LAYOUT.$path.LANG.$this->lang.".mo";

			// check if get a readable mofile
			if (is_readable($this->mofile))
			{
				$this->input = new FileReader($this->mofile);
				$this->l10n = new gettext_reader($this->input);
			}
			else
			{
				// else fall back to the old ones
				$this->mofile = $old_mofile;
				$this->lang = $old_lang;
			}
		}

		public function __($text)
		{
			return $this->l10n->translate($text);
		}

		public function _e($text)
		{
			echo $this->l10n->translate($text);
		}

		// Return the plural form.
		public function __ngettext($single, $plural, $number)
		{
			return $this->l10n->ngettext($single, $plural, $number);
		}
	}

//require(LIBS.'locale.php');
?>
