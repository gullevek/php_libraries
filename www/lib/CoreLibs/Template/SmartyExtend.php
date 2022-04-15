<?php // phpcs:ignore PSR1.Files.SideEffects

/**
 * @phan-file-suppress PhanRedefinedExtendedClass
 */

// because smarty is symlinked folder

declare(strict_types=1);

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2004/12/21
* SHORT DESCRIPTION:
*	extends smarty with the l10n class so I can use __(, etc calls
* HISTORY:
* 2005/06/22 (cs) include smarty class here, so there is no need to include it in the main file
*********************************************************************/

namespace CoreLibs\Template;

// leading slash if this is in lib\Smarty
class SmartyExtend extends \Smarty
{
	// internal translation engine
	/** @var \CoreLibs\Language\L10n */
	public $l10n;

	// lang & encoding
	/** @var string */
	public $lang_dir = '';
	/** @var string */
	public $lang;
	/** @var string */
	public $lang_short;
	/** @var string */
	public $domain;
	/** @var string */
	public $encoding;
	// page name
	/** @var string */
	public $page_name;

	// array for data parsing
	/** @var array<mixed> */
	public $HEADER = [];
	/** @var array<mixed> */
	public $DATA = [];
	/** @var array<mixed> */
	public $DEBUG_DATA = [];
	/** @var array<mixed> */
	private $CONTENT_DATA = [];
	// control vars
	/** @var bool */
	public $USE_PROTOTYPE = USE_PROTOTYPE;
	/** @var bool */
	public $USE_JQUERY = USE_JQUERY;
	/** @var bool */
	public $USE_SCRIPTACULOUS = USE_SCRIPTACULOUS;
	// sub content input vars
	/** @var bool */
	public $USE_TINY_MCE = false;
	/** @var bool */
	public $JS_DATEPICKR = false;
	/** @var bool */
	public $JS_FLATPICKR = false;
	/** @var bool */
	public $JS_FILE_UPLOADER = false;
	/** @var bool */
	public $DEBUG_TMPL = false;
	/** @var bool */
	public $USE_INCLUDE_TEMPLATE = false;
	// cache & compile
	/** @var string */
	public $CACHE_ID = '';
	/** @var string */
	public $COMPILE_ID = '';
	// template vars
	/** @var string */
	public $MASTER_TEMPLATE_NAME;
	/** @var string */
	public $PAGE_FILE_NAME;
	/** @var string */
	public $CONTENT_INCLUDE;
	/** @var string */
	public $FORM_NAME;
	/** @var string */
	public $FORM_ACTION;
	/** @var string */
	public $L_TITLE;
	/** @var string|int */
	public $PAGE_WIDTH;
	// smarty include/set var
	/** @var string */
	public $TEMPLATE_PATH;
	/** @var string */
	public $TEMPLATE_NAME;
	/** @var string */
	public $INC_TEMPLATE_NAME;
	/** @var string */
	public $JS_TEMPLATE_NAME;
	/** @var string */
	public $CSS_TEMPLATE_NAME;
	/** @var string|null */
	public $TEMPLATE_TRANSLATE;
	// core group
	/** @var string */
	public $JS_CORE_TEMPLATE_NAME;
	/** @var string */
	public $CSS_CORE_TEMPLATE_NAME;
	/** @var string */
	public $JS_CORE_INCLUDE;
	/** @var string */
	public $CSS_CORE_INCLUDE;
	// local names
	/** @var string */
	public $JS_SPECIAL_TEMPLATE_NAME = '';
	/** @var string */
	public $CSS_SPECIAL_TEMPLATE_NAME = '';
	/** @var string */
	public $JS_INCLUDE;
	/** @var string */
	public $CSS_INCLUDE;
	/** @var string */
	public $JS_SPECIAL_INCLUDE;
	/** @var string */
	public $CSS_SPECIAL_INCLUDE;
	/** @var string */
	public $ADMIN_JAVASCRIPT;
	/** @var string */
	public $ADMIN_STYLESHEET;
	/** @var string */
	public $FRONTEND_JAVASCRIPT;
	/** @var string */
	public $FRONTEND_STYLESHEET;
	// other smarty folder vars
	/** @var string */
	public $INCLUDES;
	/** @var string */
	public $JAVASCRIPT;
	/** @var string */
	public $CSS;
	/** @var string */
	public $FONT;
	/** @var string */
	public $PICTURES;
	/** @var string */
	public $CACHE_PICTURES;
	/** @var string */
	public $CACHE_PICTURES_ROOT;

	// constructor class, just sets the language stuff
	/**
	 * constructor class, just sets the language stuff
	 * calls L10 for pass on internaly in smarty
	 * also registers the getvar caller plugin
	 * @param \CoreLibs\Language\L10n $l10n l10n language class
	 * @param array<string,string>    $locale locale data read from setLocale
	 */
	public function __construct(\CoreLibs\Language\L10n $l10n, array $locale)
	{
		// call basic smarty
		// or Smarty::__construct();
		parent::__construct();
		// iinit lang
		$this->l10n = $l10n;
		// parse and read, legacy stuff
		$this->encoding = $locale['encoding'];
		$this->lang = $locale['lang'];
		// get first part from lang
		$this->lang_short = explode('_', $locale['lang'])[0];
		$this->domain = $this->l10n->getDomain();
		$this->lang_dir = $this->l10n->getBaseLocalePath();

		// opt load functions so we can use legacy init for smarty run perhaps
		$this->l10n->loadFunctions();
		__setlocale(LC_MESSAGES, $locale['locale']);
		__textdomain($this->domain);
		__bindtextdomain($this->domain, $this->lang_dir);
		__bind_textdomain_codeset($this->domain, $this->encoding);

		// register smarty variable
		$this->registerPlugin('modifier', 'getvar', [&$this, 'getTemplateVars']);

		$this->page_name = \CoreLibs\Get\System::getPageName();

		// set internal settings
		$this->CACHE_ID = defined('CACHE_ID') ? CACHE_ID : '';
		$this->COMPILE_ID = defined('COMPILE_ID') ? COMPILE_ID : '';
	}

	/**
	 * @return void
	 */
	private function setSmartCoreIncludeCssJs(): void
	{
		// core CS
		$this->CSS_CORE_INCLUDE = '';
		if (
			file_exists($this->CSS . $this->CSS_CORE_TEMPLATE_NAME) &&
			is_file($this->CSS . $this->CSS_CORE_TEMPLATE_NAME)
		) {
			$this->CSS_CORE_INCLUDE = $this->CSS . $this->CSS_CORE_TEMPLATE_NAME;
		}
		// core JS
		$this->JS_CORE_INCLUDE = '';
		if (
			file_exists($this->JAVASCRIPT . $this->JS_CORE_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT . $this->JS_CORE_TEMPLATE_NAME)
		) {
			$this->JS_CORE_INCLUDE = $this->JAVASCRIPT . $this->JS_CORE_TEMPLATE_NAME;
		}
		// additional per page Javascript include
		$this->JS_INCLUDE = '';
		if (
			file_exists($this->JAVASCRIPT . $this->JS_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT . $this->JS_TEMPLATE_NAME)
		) {
			$this->JS_INCLUDE = $this->JAVASCRIPT . $this->JS_TEMPLATE_NAME;
		}
		// per page css file
		$this->CSS_INCLUDE = '';
		if (
			file_exists($this->CSS . $this->CSS_TEMPLATE_NAME) &&
			is_file($this->CSS . $this->CSS_TEMPLATE_NAME)
		) {
			$this->CSS_INCLUDE = $this->CSS . $this->CSS_TEMPLATE_NAME;
		}
		// optional CSS file
		$this->CSS_SPECIAL_INCLUDE = '';
		if (
			file_exists($this->CSS . $this->CSS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->CSS . $this->CSS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->CSS_SPECIAL_INCLUDE = $this->CSS . $this->CSS_SPECIAL_TEMPLATE_NAME;
		}
		// optional JS file
		$this->JS_SPECIAL_INCLUDE = '';
		if (
			file_exists($this->JAVASCRIPT . $this->JS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT . $this->JS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->JS_SPECIAL_INCLUDE = $this->JAVASCRIPT . $this->JS_SPECIAL_TEMPLATE_NAME;
		}
	}


	/**
	 * sets all internal paths and names that need to be passed on to the smarty template
	 * @return void
	 */
	public function setSmartyPaths(): void
	{
		// master template
		if (empty($this->MASTER_TEMPLATE_NAME)) {
			$this->MASTER_TEMPLATE_NAME = MASTER_TEMPLATE_NAME;
		}

		// set include & template names
		if (empty($this->CONTENT_INCLUDE)) {
			$this->CONTENT_INCLUDE = str_replace('.php', '', $this->page_name) . '.tpl';
		}
		// strip tpl and replace it with php
		// php include file per page
		$this->INC_TEMPLATE_NAME = str_replace('.tpl', '.php', $this->CONTENT_INCLUDE);
		// javascript include per page
		$this->JS_TEMPLATE_NAME = str_replace('.tpl', '.js', $this->CONTENT_INCLUDE);
		// css per page
		$this->CSS_TEMPLATE_NAME = str_replace('.tpl', '.css', $this->CONTENT_INCLUDE);

		// set basic template path (tmp)
		$this->INCLUDES = BASE . INCLUDES; // no longer in templates, only global
		$this->TEMPLATE_PATH = BASE . INCLUDES . TEMPLATES . CONTENT_PATH;
		$this->setTemplateDir($this->TEMPLATE_PATH);
		$this->JAVASCRIPT = LAYOUT . JS;
		$this->CSS = LAYOUT . CSS;
		$this->FONT = LAYOUT . FONT;
		$this->PICTURES = LAYOUT . IMAGES;
		$this->CACHE_PICTURES = LAYOUT . CACHE;
		$this->CACHE_PICTURES_ROOT = ROOT . $this->CACHE_PICTURES;
		// check if we have an external file with the template name
		if (
			file_exists($this->INCLUDES . $this->INC_TEMPLATE_NAME) &&
			is_file($this->INCLUDES . $this->INC_TEMPLATE_NAME)
		) {
			include($this->INCLUDES . $this->INC_TEMPLATE_NAME);
		}
		// check for template include
		if (
			$this->USE_INCLUDE_TEMPLATE === true &&
			!$this->TEMPLATE_NAME
		) {
			$this->TEMPLATE_NAME = $this->CONTENT_INCLUDE;
			// add to cache & compile id
			$this->COMPILE_ID .= '_' . $this->TEMPLATE_NAME;
			$this->CACHE_ID .= '_' . $this->TEMPLATE_NAME;
		}
		// set all the additional CSS/JS parths
		$this->setSmartCoreIncludeCssJs();
		// check if template names exist
		if (!$this->MASTER_TEMPLATE_NAME) {
			exit('MASTER TEMPLATE is not set');
		} elseif (!file_exists($this->getTemplateDir()[0] . DIRECTORY_SEPARATOR . $this->MASTER_TEMPLATE_NAME)) {
			// abort if master template could not be found
			exit('MASTER TEMPLATE: ' . $this->MASTER_TEMPLATE_NAME . ' could not be found');
		}
		if (
			$this->TEMPLATE_NAME &&
			!file_exists($this->getTemplateDir()[0] . DIRECTORY_SEPARATOR . $this->TEMPLATE_NAME)
		) {
			exit('INCLUDE TEMPLATE: ' . $this->TEMPLATE_NAME . ' could not be found');
		}
		// javascript translate data as template for auto translate
		if (empty($this->TEMPLATE_TRANSLATE)) {
			$this->TEMPLATE_TRANSLATE = 'jsTranslate_' . $this->lang . '.tpl';
		} else {
			// we assume we have some fixed set
			// we must add _<$this->lang>
			// if .tpl, put before .tpl
			// if not .tpl, add _<$this->lang>.tpl
			if (strpos($this->TEMPLATE_TRANSLATE, '.tpl')) {
				$this->TEMPLATE_TRANSLATE = str_replace('.tpl', '_' . $this->lang . '.tpl', $this->TEMPLATE_TRANSLATE);
			} else {
				$this->TEMPLATE_TRANSLATE .= '_' . $this->lang . '.tpl';
			}
		}
		// if we can't find it, dump it
		if (!file_exists($this->getTemplateDir()[0] . DIRECTORY_SEPARATOR . $this->TEMPLATE_TRANSLATE)) {
			$this->TEMPLATE_TRANSLATE = null;
		}
	}

	/**
	 * wrapper call for setSmartyVars
	 * this is for frontend type and will not set any only admin needed variables
	 * @return void
	 */
	public function setSmartyVarsFrontend(): void
	{
		$this->setSmartyVars();
	}

	/**
	 * wrapper call for setSmartyVars
	 * this is only for admin interface and will set additional variables
	 */
	public function setSmartyVarsAdmin(): void
	{
		$this->setSmartyVars(true);
	}

	/**
	 * set smarty pass on variables, sub template names and finally calls the smarty parser
	 * @param  boolean $admin_call default false, will set admin only variables
	 * @return void
	 */
	private function setSmartyVars($admin_call = false): void
	{
		/** @var \CoreLibs\Admin\Backend This is an assumption */
		global $cms;
		if (is_object($cms)) {
			$this->mergeCmsSmartyVars($cms);
		}

		// trigger flags
		$this->HEADER['USE_PROTOTYPE'] = $this->USE_PROTOTYPE;
		// scriptacolous, can only be used with prototype
		if ($this->HEADER['USE_PROTOTYPE']) {
			$this->HEADER['USE_SCRIPTACULOUS'] = $this->USE_SCRIPTACULOUS;
		}
		// jquery and prototype should not be used together
		$this->HEADER['USE_JQUERY'] = $this->USE_JQUERY;

		// set all the additional CSS/JS parths
		$this->setSmartCoreIncludeCssJs();

		// the actual include files for javascript (per page)
		$this->HEADER['JS_CORE_INCLUDE'] = $this->JS_CORE_INCLUDE;
		$this->HEADER['CSS_CORE_INCLUDE'] = $this->CSS_CORE_INCLUDE;
		$this->HEADER['JS_INCLUDE'] = $this->JS_INCLUDE;
		$this->HEADER['CSS_INCLUDE'] = $this->CSS_INCLUDE;
		$this->HEADER['CSS_SPECIAL_INCLUDE'] = $this->CSS_SPECIAL_INCLUDE;
		$this->HEADER['JS_SPECIAL_INCLUDE'] = $this->JS_SPECIAL_INCLUDE;
		// paths to the files
		$this->DATA['includes'] = $this->INCLUDES;
		$this->DATA['js'] = $this->JAVASCRIPT;
		$this->DATA['css'] = $this->CSS;
		$this->DATA['pictures'] = $this->PICTURES;

		// default CMS settings
		// define all needed smarty stuff for the general HTML/page building
		$this->HEADER['CSS'] = CSS;
		$this->HEADER['FONT'] = FONT;
		$this->HEADER['JS'] = JS;
		$this->HEADER['ENCODING'] = $this->encoding;
		$this->HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;

		// form name
		$this->DATA['FORM_NAME'] = !$this->FORM_NAME ?
			str_replace('.php', '', $this->page_name) :
			$this->FORM_NAME;
		$this->DATA['FORM_ACTION'] = $this->FORM_ACTION;
		// special for admin
		if ($admin_call === true) {
			// set ACL extra show
			if (is_object($cms)) {
				$this->DATA['show_ea_extra'] = $cms->acl['show_ea_extra'] ?? false;
				$this->DATA['ADMIN'] = $cms->acl['admin'] ?? 0;
				// top menu
				$this->DATA['nav_menu'] = $cms->adbTopMenu();
				$this->DATA['nav_menu_count'] = is_array($this->DATA['nav_menu']) ? count($this->DATA['nav_menu']) : 0;
				// messages = ['msg' =>, 'class' => 'error/warning/...']
				$this->DATA['messages'] = $cms->messages;
			} else { /** @phpstan-ignore-line Because I assume object for phpstan */
				$this->DATA['show_ea_extra'] = false;
				$this->DATA['ADMIN'] = 0;
				$this->DATA['nav_menu'] = [];
				$this->DATA['nav_menu_count'] = 0;
				$this->DATA['messages'] = [];
			}
			// set style sheets
			$this->HEADER['STYLESHEET'] = $this->ADMIN_STYLESHEET ? $this->ADMIN_STYLESHEET : ADMIN_STYLESHEET;
			$this->HEADER['JAVASCRIPT'] = $this->ADMIN_JAVASCRIPT ? $this->ADMIN_JAVASCRIPT : ADMIN_JAVASCRIPT;
			// the page name
			$this->DATA['page_name'] = $this->page_name;
			$this->DATA['table_width'] = empty($this->PAGE_WIDTH) ?: PAGE_WIDTH;
			$this->DATA['form_name'] = $this->DATA['FORM_NAME'];
			// for tinymce special
			$this->DATA['TINYMCE_LANG'] = $this->lang_short;
			// include flags
			$this->DATA['USE_TINY_MCE'] = $this->USE_TINY_MCE;
			// debug data, if DEBUG flag is on, this data is print out
			$this->DEBUG_DATA['DEBUG'] = $this->DEBUG_TMPL;
		} else {
			$this->HEADER['STYLESHEET'] = $this->FRONTEND_STYLESHEET ? $this->FRONTEND_STYLESHEET : STYLESHEET;
			$this->HEADER['JAVASCRIPT'] = $this->FRONTEND_JAVASCRIPT ? $this->FRONTEND_JAVASCRIPT : JAVASCRIPT;
		}
		// html title
		// set local page title
		$this->HEADER['HTML_TITLE'] = !$this->L_TITLE ?
			ucfirst(str_replace('_', ' ', \CoreLibs\Get\System::getPageName(1)))
				. (defined('G_TITLE') ? ' - ' . $this->l10n->__(G_TITLE) : '') :
			$this->l10n->__($this->L_TITLE);

		// LANG
		$this->DATA['LANG'] = $this->lang;
		// include flags
		$this->DATA['JS_DATEPICKR'] = $this->JS_DATEPICKR;
		$this->DATA['JS_FLATPICKR'] = $this->JS_FLATPICKR;
		$this->DATA['JS_FILE_UPLOADER'] = $this->JS_FILE_UPLOADER;
		// user name
		$this->DATA['USER_NAME'] = !empty($_SESSION['USER_NAME']) ? $_SESSION['USER_NAME'] : '';
		// the template part to include into the body
		$this->DATA['TEMPLATE_NAME'] = $this->TEMPLATE_NAME;
		$this->DATA['CONTENT_INCLUDE'] = $this->CONTENT_INCLUDE;
		$this->DATA['TEMPLATE_TRANSLATE'] = $this->TEMPLATE_TRANSLATE ?? null;
		$this->DATA['PAGE_FILE_NAME'] = str_replace('.php', '', $this->page_name) . '.tpl';
		// render page
		$this->renderSmarty();
	}

	/**
	 * merge outside object HEADER/DATA/DEBUG_DATA vars into the smarty class
	 * @param  object $cms object that has header/data/debug_data
	 * @return void
	 */
	public function mergeCmsSmartyVars(object $cms): void
	{
		// array merge HEADER, DATA, DEBUG DATA
		foreach (['HEADER', 'DATA', 'DEBUG_DATA'] as $ext_smarty) {
			if (
				isset($cms->{$ext_smarty}) &&
				is_array($cms->{$ext_smarty})
			) {
				$this->{$ext_smarty} = array_merge($this->{$ext_smarty}, $cms->{$ext_smarty});
			}
		}
	}

	/**
	 * render smarty data (can be called sepparate)
	 * @return void
	 */
	public function renderSmarty(): void
	{
		// create main data array
		$this->CONTENT_DATA = array_merge($this->HEADER, $this->DATA, $this->DEBUG_DATA);
		// data is 1:1 mapping (all vars, values, etc)
		foreach ($this->CONTENT_DATA as $key => $value) {
			$this->assign($key, $value);
		}
		if (is_dir(BASE . TEMPLATES_C)) {
			$this->setCompileDir(BASE . TEMPLATES_C);
		}
		if (is_dir(BASE . CACHE)) {
			$this->setCacheDir(BASE . CACHE);
		}
		$this->display(
			$this->MASTER_TEMPLATE_NAME,
			$this->CACHE_ID . ($this->CACHE_ID ? '_' : '') . $this->lang,
			$this->COMPILE_ID . ($this->COMPILE_ID ? '_' : '') . $this->lang
		);
	}
}

// __END__
