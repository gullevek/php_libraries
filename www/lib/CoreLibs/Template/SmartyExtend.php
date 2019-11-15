<?php declare(strict_types=1);
// because smarty is symlinked folder
/**
 * @phan-file-suppress PhanRedefinedExtendedClass
 */

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
	// internal translation engine
	public $l10n;

	// lang & encoding
	public $lang_dir = '';
	public $lang;
	public $lang_short;
	public $encoding;
	// page name
	public $page_name;

	// array for data parsing
	public $HEADER = array();
	public $DATA = array();
	public $DEBUG_DATA = array();
	private $CONTENT_DATA = array();
	// control vars
	public $USE_PROTOTYPE = USE_PROTOTYPE;
	public $USE_JQUERY = USE_JQUERY;
	public $USE_SCRIPTACULOUS = USE_SCRIPTACULOUS;
	// sub content input vars
	public $USE_TINY_MCE = false;
	public $JS_DATEPICKR = false;
	public $JS_FLATPICKR = false;
	public $DEBUG_TMPL = false;
	public $USE_INCLUDE_TEMPLATE = false;
	// cache & compile
	public $CACHE_ID = '';
	public $COMPILE_ID = '';
	// template vars
	public $MASTER_TEMPLATE_NAME;
	public $PAGE_FILE_NAME;
	public $CONTENT_INCLUDE;
	public $FORM_NAME;
	public $FORM_ACTION;
	public $L_TITLE;
	public $PAGE_WIDTH;
	// smarty include/set var
	public $TEMPLATE_PATH;
	public $TEMPLATE_NAME;
	public $INC_TEMPLATE_NAME;
	public $JS_TEMPLATE_NAME;
	public $CSS_TEMPLATE_NAME;
	public $TEMPLATE_TRANSLATE;
	// local names
	public $JS_SPECIAL_TEMPLATE_NAME = '';
	public $CSS_SPECIAL_TEMPLATE_NAME = '';
	public $JS_INCLUDE;
	public $CSS_INCLUDE;
	public $JS_SPECIAL_INCLUDE;
	public $CSS_SPECIAL_INCLUDE;
	public $ADMIN_JAVASCRIPT;
	public $ADMIN_STYLESHEET;
	public $FRONTEND_JAVASCRIPT;
	public $FRONTEND_STYLESHEET;
	// other smarty folder vars
	public $INCLUDES;
	public $JAVASCRIPT;
	public $CSS;
	public $PICTURES;
	public $CACHE_PICTURES;
	public $CACHE_PICTURES_ROOT;

	// constructor class, just sets the language stuff
	/**
	 * constructor class, just sets the language stuff
	 * calls L10 for pass on internaly in smarty
	 * also registers the getvar caller plugin
	 */
	public function __construct()
	{
		// call basic smarty
		parent::__construct();
		// set lang vars
		$this->setLangEncoding();
		// iinit lang
		$this->l10n = new \CoreLibs\Language\L10n($this->lang);
		// variable variable register
		// $this->register_modifier('getvar', array(&$this, 'get_template_vars'));
		$this->registerPlugin('modifier', 'getvar', array(&$this, 'get_template_vars'));

		$this->page_name = pathinfo($_SERVER["PHP_SELF"])['basename'];

		// set internal settings
		$this->CACHE_ID = defined('CACHE_ID') ? CACHE_ID : '';
		$this->COMPILE_ID = defined('COMPILE_ID') ? COMPILE_ID : '';
	}

	/**
	 * ORIGINAL in \CoreLibs\Admin\Backend
	 * set the language encoding and language settings
	 * the default charset from _SESSION login or from
	 * config DEFAULT ENCODING
	 * the lang full name for mo loading from _SESSION login
	 * or SITE LANG or DEFAULT LANG from config
	 * creates short lang (only first two chars) from the lang
	 * @return void
	 */
	private function setLangEncoding(): void
	{
		// just emergency fallback for language
		// set encoding
		if (isset($_SESSION['DEFAULT_CHARSET'])) {
			$this->encoding = $_SESSION['DEFAULT_CHARSET'];
		} else {
			$this->encoding = DEFAULT_ENCODING;
		}
		// gobal override
		if (isset($GLOBALS['OVERRIDE_LANG'])) {
			$this->lang = $GLOBALS['OVERRIDE_LANG'];
		} elseif (isset($_SESSION['DEFAULT_LANG'])) {
			// session (login)
			$this->lang = $_SESSION['DEFAULT_LANG'];
		} else {
			// mostly default SITE LANG or DEFAULT LANG
			$this->lang = defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG;
		}
		// create the char lang encoding
		$this->lang_short = substr($this->lang, 0, 2);
		// set the language folder
		$this->lang_dir = BASE.INCLUDES.LANG.CONTENT_PATH;
	}


	/**
	 * sets all internal paths and names that need to be passed on to the smarty template
	 * @return void
	 */
	public function setSmartyPaths(): void
	{
		// master template
		if (!isset($this->MASTER_TEMPLATE_NAME)) {
			$this->MASTER_TEMPLATE_NAME = MASTER_TEMPLATE_NAME;
		}

		// set include & template names
		if (!isset($this->CONTENT_INCLUDE)) {
			$this->CONTENT_INCLUDE = str_replace('.php', '', $this->page_name).'.tpl';
		}
		// strip tpl and replace it with php
		// php include file per page
		$this->INC_TEMPLATE_NAME = str_replace('.tpl', '.php', $this->CONTENT_INCLUDE);
		// javascript include per page
		$this->JS_TEMPLATE_NAME = str_replace('.tpl', '.js', $this->CONTENT_INCLUDE);
		// css per page
		$this->CSS_TEMPLATE_NAME = str_replace('.tpl', '.css', $this->CONTENT_INCLUDE);

		// set basic template path (tmp)
		$this->INCLUDES = BASE.INCLUDES; // no longer in templates, only global
		$this->TEMPLATE_PATH = BASE.INCLUDES.TEMPLATES.CONTENT_PATH;
		$this->setTemplateDir($this->TEMPLATE_PATH);
		$this->JAVASCRIPT = LAYOUT.JS;
		$this->CSS = LAYOUT.CSS;
		$this->PICTURES = LAYOUT.IMAGES;
		$this->CACHE_PICTURES = LAYOUT.CACHE;
		$this->CACHE_PICTURES_ROOT = ROOT.$this->CACHE_PICTURES;
		// check if we have an external file with the template name
		if (file_exists($this->INCLUDES.$this->INC_TEMPLATE_NAME) &&
			is_file($this->INCLUDES.$this->INC_TEMPLATE_NAME)
		) {
			include($this->INCLUDES.$this->INC_TEMPLATE_NAME);
		}
		// check for template include
		if ($this->USE_INCLUDE_TEMPLATE === true &&
			!$this->TEMPLATE_NAME
		) {
			$this->TEMPLATE_NAME = $this->CONTENT_INCLUDE;
			// add to cache & compile id
			$this->COMPILE_ID .= '_'.$this->TEMPLATE_NAME;
			$this->CACHE_ID .= '_'.$this->TEMPLATE_NAME;
		}
		// additional per page Javascript include
		$this->JS_INCLUDE = '';
		if (file_exists($this->JAVASCRIPT.$this->JS_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT.$this->JS_TEMPLATE_NAME)
		) {
			$this->JS_INCLUDE = $this->JAVASCRIPT.$this->JS_TEMPLATE_NAME;
		}
		// per page css file
		$this->CSS_INCLUDE = '';
		if (file_exists($this->CSS.$this->CSS_TEMPLATE_NAME) &&
			is_file($this->CSS.$this->CSS_TEMPLATE_NAME)
		) {
			$this->CSS_INCLUDE = $this->CSS.$this->CSS_TEMPLATE_NAME;
		}
		// optional CSS file
		$this->CSS_SPECIAL_INCLUDE = '';
		if (file_exists($this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->CSS_SPECIAL_INCLUDE = $this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME;
		}
		// optional JS file
		$this->JS_SPECIAL_INCLUDE = '';
		if (file_exists($this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->JS_SPECIAL_INCLUDE = $this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME;
		}
		// check if template names exist
		if (!$this->MASTER_TEMPLATE_NAME) {
			exit('MASTER TEMPLATE is not set');
		} elseif (!file_exists($this->getTemplateDir()[0].DS.$this->MASTER_TEMPLATE_NAME)) {
			// abort if master template could not be found
			exit('MASTER TEMPLATE: '.$this->MASTER_TEMPLATE_NAME.' could not be found');
		}
		if ($this->TEMPLATE_NAME &&
			!file_exists($this->getTemplateDir()[0].DS.$this->TEMPLATE_NAME)
		) {
			exit('INCLUDE TEMPLATE: '.$this->TEMPLATE_NAME.' could not be found');
		}
		// javascript translate data as template for auto translate
		if (empty($this->TEMPLATE_TRANSLATE)) {
			$this->TEMPLATE_TRANSLATE = 'jsTranslate_'.$this->lang.'.tpl';
		} else {
			// we assume we have some fixed set
			// we must add _<$this->lang>
			// if .tpl, put before .tpl
			// if not .tpl, add _<$this->lang>.tpl
			if (strpos($this->TEMPLATE_TRANSLATE, '.tpl')) {
				$this->TEMPLATE_TRANSLATE = str_replace('.tpl', '_'.$this->lang.'.tpl', $this->TEMPLATE_TRANSLATE);
			} else {
				$this->TEMPLATE_TRANSLATE .= '_'.$this->lang.'.tpl';
			}
		}
		// if we can't find it, dump it
		if (!file_exists($this->getTemplateDir()[0].DS.$this->TEMPLATE_TRANSLATE)) {
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
		global $cms;
		$this->mergeCmsSmartyVars($cms);

		// trigger flags
		$this->HEADER['USE_PROTOTYPE'] = $this->USE_PROTOTYPE;
		// scriptacolous, can only be used with prototype
		if ($this->HEADER['USE_PROTOTYPE']) {
			$this->HEADER['USE_SCRIPTACULOUS'] = $this->USE_SCRIPTACULOUS;
		}
		// jquery and prototype should not be used together
		$this->HEADER['USE_JQUERY'] = $this->USE_JQUERY;

		// additional per page Javascript include
		$this->JS_INCLUDE = '';
		if (file_exists($this->JAVASCRIPT.$this->JS_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT.$this->JS_TEMPLATE_NAME)
		) {
			$this->JS_INCLUDE = $this->JAVASCRIPT.$this->JS_TEMPLATE_NAME;
		}
		// per page css file
		$this->CSS_INCLUDE = '';
		if (file_exists($this->CSS.$this->CSS_TEMPLATE_NAME) &&
			is_file($this->CSS.$this->CSS_TEMPLATE_NAME)
		) {
			$this->CSS_INCLUDE = $this->CSS.$this->CSS_TEMPLATE_NAME;
		}
		// optional CSS file
		$this->CSS_SPECIAL_INCLUDE = '';
		if (file_exists($this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->CSS_SPECIAL_INCLUDE = $this->CSS.$this->CSS_SPECIAL_TEMPLATE_NAME;
		}
		// optional JS file
		$this->JS_SPECIAL_INCLUDE = '';
		if (file_exists($this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME) &&
			is_file($this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME)
		) {
			$this->JS_SPECIAL_INCLUDE = $this->JAVASCRIPT.$this->JS_SPECIAL_TEMPLATE_NAME;
		}

		// the actual include files for javascript (per page)
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
		$this->HEADER['JS'] = JS;
		$this->HEADER['ENCODING'] = $this->encoding;
		$this->HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;

		// special for admin
		if ($admin_call === true) {
			// set ACL extra show
			$this->DATA['show_ea_extra'] = isset($cms->acl['show_ea_extra']) ? $cms->acl['show_ea_extra'] : false;
			$this->DATA['ADMIN'] = !empty($cms->acl['admin']) ? $cms->acl['admin'] : 0;
			// set style sheets
			$this->HEADER['STYLESHEET'] = $this->ADMIN_STYLESHEET ? $this->ADMIN_STYLESHEET : ADMIN_STYLESHEET;
			$this->HEADER['JAVASCRIPT'] = $this->ADMIN_JAVASCRIPT ? $this->ADMIN_JAVASCRIPT : ADMIN_JAVASCRIPT;
			// top menu
			$this->DATA['nav_menu'] = $cms->adbTopMenu();
			$this->DATA['nav_menu_count'] = is_array($this->DATA['nav_menu']) ? count($this->DATA['nav_menu']) : 0;
			// messages = array('msg' =>, 'class' => 'error/warning/...')
			$this->DATA['messages'] = isset($cms->messages) ? $cms->messages : $cms->messages;
			// the page name
			$this->DATA['page_name'] = $this->page_name;
			$this->DATA['table_width'] = isset($this->PAGE_WIDTH) ? $this->PAGE_WIDTH : PAGE_WIDTH;
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
			ucfirst(str_replace('_', ' ', $cms->getPageName(1))).(defined(G_TITLE) ? ' - '.$this->l10n->__(G_TITLE) : '') :
			$this->l10n->__($this->L_TITLE);

		// LANG
		$this->DATA['LANG'] = $this->lang;
		// form name
		$this->DATA['FORM_NAME'] = !$this->FORM_NAME ?
			str_replace('.php', '', $this->page_name) :
			$this->FORM_NAME;
		$this->DATA['FORM_ACTION'] = $this->FORM_ACTION;
		// include flags
		$this->DATA['JS_DATEPICKR'] = $this->JS_DATEPICKR;
		$this->DATA['JS_FLATPICKR'] = $this->JS_FLATPICKR;
		// user name
		$this->DATA['USER_NAME'] = !empty($_SESSION['USER_NAME']) ? $_SESSION['USER_NAME'] : '';
		// the template part to include into the body
		$this->DATA['TEMPLATE_NAME'] = $this->TEMPLATE_NAME;
		$this->DATA['CONTENT_INCLUDE'] = $this->CONTENT_INCLUDE;
		$this->DATA['TEMPLATE_TRANSLATE'] = isset($this->TEMPLATE_TRANSLATE) ? $this->TEMPLATE_TRANSLATE : null;
		$this->DATA['PAGE_FILE_NAME'] = str_replace('.php', '', $this->page_name).'.tpl';
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
		foreach (array('HEADER', 'DATA', 'DEBUG_DATA') as $ext_smarty) {
			if (is_array($cms->{$ext_smarty})) {
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
		if (is_dir(BASE.TEMPLATES_C)) {
			$this->setCompileDir(BASE.TEMPLATES_C);
		}
		if (is_dir(BASE.CACHE)) {
			$this->setCacheDir(BASE.CACHE);
		}
		$this->display(
			$this->MASTER_TEMPLATE_NAME,
			$this->CACHE_ID.($this->CACHE_ID ? '_' : '').$this->lang,
			$this->COMPILE_ID.($this->COMPILE_ID ? '_' : '').$this->lang
		);
	}
}

// __END__
