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
	public $locale_set;
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
	/** @var string|null */
	public $JS_TRANSLATE;
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
	 *
	 * @param \CoreLibs\Language\L10n $l10n l10n language class
	 * @param string|null             $cache_id
	 * @param string|null             $compile_id
	 */
	public function __construct(
		\CoreLibs\Language\L10n $l10n,
		?string $cache_id = null,
		?string $compile_id = null
	) {
		// trigger deprecation
		if (
			$cache_id === null ||
			$compile_id === null
		) {
			/** @deprecated SmartyExtend::__construct call without parameters */
			trigger_error(
				'Calling SmartyExtend::__construct without paramters is deprecated',
				E_USER_DEPRECATED
			);
		}
		// set variables (to be deprecated)
		$cache_id = $cache_id ??
			(defined('CACHE_ID') ? CACHE_ID : '');
		$compile_id = $compile_id ??
			(defined('COMPILE_ID') ? COMPILE_ID : '');
		// call basic smarty
		// or Smarty::__construct();
		parent::__construct();
		// iinit lang
		$this->l10n = $l10n;
		// parse and read, legacy stuff
		$locale = $this->l10n->getLocaleAsArray();
		$this->encoding = $locale['encoding'];
		$this->lang = $locale['lang'];
		$this->lang_short = $locale['lang_short'];
		$this->domain = $locale['domain'];
		$this->lang_dir = $locale['path'];

		// opt load functions so we can use legacy init for smarty run perhaps
		\CoreLibs\Language\L10n::loadFunctions();
		_setlocale(LC_MESSAGES, $locale['locale']);
		_textdomain($this->domain);
		_bindtextdomain($this->domain, $this->lang_dir);
		_bind_textdomain_codeset($this->domain, $this->encoding);

		// register smarty variable
		$this->registerPlugin('modifier', 'getvar', [&$this, 'getTemplateVars']);

		$this->page_name = \CoreLibs\Get\System::getPageName();

		// set internal settings
		$this->CACHE_ID = $cache_id;
		$this->COMPILE_ID = $compile_id;
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
	 *
	 * @return void
	 */

	/**
	 * sets all internal paths and names that need to be passed on
	 * to the smarty template
	 *
	 * @param  string|null $set_includes             INCLUDES
	 * @param  string|null $set_template_path        TEMPLATE_PATH
	 * @param  string|null $set_javascript           JAVASCRIPT
	 * @param  string|null $set_css                  CSS
	 * @param  string|null $set_font                 FONT
	 * @param  string|null $set_pictures             PICTURES
	 * @param  string|null $set_cache_pictures       CACHE_PICTURES
	 * @param  string|null $set_cache_pictures_root  CACHE_PICTURES_ROOT
	 * @param  string|null $set_master_template_name MASTAER_TEMPLATE_NAME
	 * @return void
	 */
	public function setSmartyPaths(
		?string $set_includes = null,
		?string $set_template_path = null,
		?string $set_javascript = null,
		?string $set_css = null,
		?string $set_font = null,
		?string $set_pictures = null,
		?string $set_cache_pictures = null,
		?string $set_cache_pictures_root = null,
		?string $set_master_template_name = null,
	): void {
		// trigger deprecation
		if (
			$set_includes === null ||
			$set_template_path === null ||
			$set_javascript === null ||
			$set_css === null ||
			$set_font === null ||
			$set_pictures === null ||
			$set_cache_pictures === null ||
			$set_cache_pictures_root === null
		) {
			/** @deprecated setSmartyPaths call without parameters */
			trigger_error(
				'Calling setSmartyPaths without paramters is deprecated',
				E_USER_DEPRECATED
			);
		}
		// set variables (to be deprecated)
		$set_master_template_name = $set_master_template_name ??
			(defined('MASTER_TEMPLATE_NAME') ? MASTER_TEMPLATE_NAME : '');
		$set_includes = $set_includes ??
			BASE . INCLUDES;
		$set_template_path = $set_template_path ??
			BASE . INCLUDES . TEMPLATES . CONTENT_PATH;
		$set_javascript = $set_javascript ?? LAYOUT . JS;
		$set_css = $set_css ?? LAYOUT . CSS;
		$set_font = $set_font ?? LAYOUT . FONT;
		$set_pictures = $set_pictures ?? LAYOUT . IMAGES;
		$set_cache_pictures = $set_cache_pictures ?? LAYOUT . CACHE;
		$set_cache_pictures_root = $set_cache_pictures_root ??
			ROOT . $set_cache_pictures;

		// master template
		if (
			empty($this->MASTER_TEMPLATE_NAME)
		) {
			$this->MASTER_TEMPLATE_NAME = $set_master_template_name;
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
		$this->INCLUDES = $set_includes; // no longer in templates, only global
		$this->TEMPLATE_PATH = $set_template_path;
		$this->setTemplateDir($this->TEMPLATE_PATH);
		$this->JAVASCRIPT = $set_javascript;
		$this->CSS = $set_css;
		$this->FONT = $set_font;
		$this->PICTURES = $set_pictures;
		$this->CACHE_PICTURES = $set_cache_pictures;
		$this->CACHE_PICTURES_ROOT = $set_cache_pictures_root;
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
		if (empty($this->MASTER_TEMPLATE_NAME)) {
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
			$this->TEMPLATE_TRANSLATE = 'jsTranslate-'
				. $this->locale_set . '.' . $this->encoding
				. '.tpl';
		} else {
			// we assume we have some fixed set
			// we must add _<locale>.<encoding>
			// if .tpl, put before .tpl
			// if not .tpl, add _<locale>.<encoding>.tpl
			if (strpos($this->TEMPLATE_TRANSLATE, '.tpl')) {
				$this->TEMPLATE_TRANSLATE = str_replace(
					'.tpl',
					'-' . $this->locale_set . '.' . $this->encoding . '.tpl',
					$this->TEMPLATE_TRANSLATE
				);
			} else {
				$this->TEMPLATE_TRANSLATE .= '-'
					. $this->locale_set . '.' . $this->encoding
					. '.tpl';
			}
		}
		// if we can't find it, dump it
		if (!file_exists($this->getTemplateDir()[0] . DIRECTORY_SEPARATOR . $this->TEMPLATE_TRANSLATE)) {
			$this->TEMPLATE_TRANSLATE = null;
		}
		if (empty($this->JS_TRANSLATE)) {
			$this->JS_TRANSLATE = 'translate-'
				. $this->locale_set . '.' . $this->encoding . '.js';
		} else {
			// we assume we have some fixed set
			// we must add _<locale>.<encoding>
			// if .js, put before .js
			// if not .js, add _<locale>.<encoding>.js
			if (strpos($this->JS_TRANSLATE, '.js')) {
				$this->JS_TRANSLATE = str_replace(
					'.js',
					'-' . $this->locale_set . '.' . $this->encoding . '.js',
					$this->JS_TRANSLATE
				);
			} else {
				$this->JS_TRANSLATE .= '-'
					. $this->locale_set . '.' . $this->encoding
					. '.js';
			}
		}
		if (!file_exists($this->JAVASCRIPT . $this->JS_TRANSLATE)) {
			$this->JS_TRANSLATE = null;
		} else {
			$this->JS_TRANSLATE = $this->JAVASCRIPT . $this->JS_TRANSLATE;
		}
	}

	/**
	 * wrapper call for setSmartyVars
	 * this is for frontend type and will not set any only admin needed variables
	 *
	 * @param  string|null $compile_dir          BASE . TEMPLATES_C
	 * @param  string|null $cache_dir            BASE . CACHE
	 * @param  string|null $set_js               JS
	 * @param  string|null $set_css              CSS
	 * @param  string|null $set_font             FONT
	 * @param  string|null $set_default_encoding DEFAULT_ENCODING
	 * @param  string|null $set_g_title          G_TITLE
	 * @param  string|null $set_stylesheet       STYLESHEET
	 * @param  string|null $set_javascript       JAVASCRIPT
	 * @param  \CoreLibs\Admin\Backend|null $cms Optinal Admin Backend for
	 *                                           smarty variables merge
	 * @return void
	 */
	public function setSmartyVarsFrontend(
		?string $compile_dir = null,
		?string $cache_dir = null,
		?string $set_js = null,
		?string $set_css = null,
		?string $set_font = null,
		?string $set_default_encoding = null,
		?string $set_g_title = null,
		?string $set_stylesheet = null,
		?string $set_javascript = null,
		?\CoreLibs\Admin\Backend $cms = null
	): void {
		$this->setSmartyVars(
			false,
			$cms,
			$compile_dir,
			$cache_dir,
			$set_js,
			$set_css,
			$set_font,
			$set_default_encoding,
			$set_g_title,
			null,
			null,
			null,
			$set_stylesheet,
			$set_javascript
		);
	}

	/**
	 * wrapper call for setSmartyVars
	 * this is only for admin interface and will set additional variables
	 * @param  string|null $compile_dir          BASE . TEMPLATES_C
	 * @param  string|null $cache_dir            BASE . CACHE
	 * @param  string|null $set_js               JS
	 * @param  string|null $set_css              CSS
	 * @param  string|null $set_font             FONT
	 * @param  string|null $set_default_encoding DEFAULT_ENCODING
	 * @param  string|null $set_g_title          G_TITLE
	 * @param  string|null $set_admin_stylesheet ADMIN_STYLESHEET
	 * @param  string|null $set_admin_javascript ADMIN_JAVASCRIPT
	 * @param  string|null $set_page_width       PAGE_WIDTH
	 * @param  \CoreLibs\Admin\Backend|null $cms Optinal Admin Backend for
	 *                                           smarty variables merge
	 * @return void
	 */
	public function setSmartyVarsAdmin(
		?string $compile_dir = null,
		?string $cache_dir = null,
		?string $set_js = null,
		?string $set_css = null,
		?string $set_font = null,
		?string $set_default_encoding = null,
		?string $set_g_title = null,
		?string $set_admin_stylesheet = null,
		?string $set_admin_javascript = null,
		?string $set_page_width = null,
		?\CoreLibs\Admin\Backend $cms = null
	): void {
		$this->setSmartyVars(
			true,
			$cms,
			$compile_dir,
			$cache_dir,
			$set_js,
			$set_css,
			$set_font,
			$set_g_title,
			$set_default_encoding,
			$set_admin_stylesheet,
			$set_admin_javascript,
			$set_page_width,
			null,
			null
		);
	}

	/**
	 * set smarty pass on variables, sub template names and
	 * finally calls the smarty parser
	 *
	 * @param  bool        $admin_call           default false
	 *                                           will set admin only variables
	 * @param  \CoreLibs\Admin\Backend|null $cms Optinal Admin Backend for
	 *                                           smarty variables merge
	 * @param  string|null $compile_dir          BASE . TEMPLATES_C
	 * @param  string|null $cache_dir            BASE . CACHE
	 * @param  string|null $set_js               JS
	 * @param  string|null $set_css              CSS
	 * @param  string|null $set_font             FONT
	 * @param  string|null $set_default_encoding DEFAULT_ENCODING
	 * @param  string|null $set_g_title          G_TITLE
	 * @param  string|null $set_admin_stylesheet ADMIN_STYLESHEET
	 * @param  string|null $set_admin_javascript ADMIN_JAVASCRIPT
	 * @param  string|null $set_page_width       PAGE_WIDTH
	 * @param  string|null $set_stylesheet       STYLESHEET
	 * @param  string|null $set_javascript       JAVASCRIPT
	 * @return void
	 */
	private function setSmartyVars(
		bool $admin_call,
		?\CoreLibs\Admin\Backend $cms = null,
		?string $compile_dir = null,
		?string $cache_dir = null,
		?string $set_js = null,
		?string $set_css = null,
		?string $set_font = null,
		?string $set_default_encoding = null,
		?string $set_g_title = null,
		?string $set_admin_stylesheet = null,
		?string $set_admin_javascript = null,
		?string $set_page_width = null,
		?string $set_stylesheet = null,
		?string $set_javascript = null
	): void {
		// trigger deprecation
		if (
			$compile_dir === null ||
			$cache_dir === null ||
			$set_css === null ||
			$set_font === null ||
			$set_js === null ||
			$set_default_encoding === null ||
			$set_g_title === null ||
			(
				$admin_call === true && (
					$set_admin_stylesheet === null ||
					$set_admin_javascript === null ||
					$set_page_width === null
				)
			) ||
			(
				$admin_call === false && (
					$set_stylesheet === null ||
					$set_javascript === null
				)
			)
		) {
			/** @deprecated setSmartyVars call without parameters */
			trigger_error(
				'Calling setSmartyVars without paramters is deprecated',
				E_USER_DEPRECATED
			);
		}
		// set variables (will be deprecated)
		$compile_dir = $compile_dir ?? BASE . TEMPLATES_C;
		$cache_dir = $cache_dir ?? BASE . CACHE;
		$set_css = $set_css ?? CSS;
		$set_font = $set_font ?? FONT;
		$set_js = $set_js ?? JS;
		$set_default_encoding = $set_default_encoding ?? DEFAULT_ENCODING;
		$set_g_title = $set_g_title ?? G_TITLE;
		$set_admin_stylesheet = $set_admin_stylesheet ?? ADMIN_STYLESHEET;
		$set_admin_javascript = $set_admin_javascript ?? ADMIN_JAVASCRIPT;
		$set_page_width = $set_page_width ?? PAGE_WIDTH;
		$set_stylesheet = $set_stylesheet ?? STYLESHEET;
		$set_javascript = $set_javascript ?? JAVASCRIPT;
		// depreacte call globals cms on null 4mcs
		if (
			$cms === null &&
			isset($GLOBALS['cms'])
		) {
			/** @deprecated setSmartyVars globals cms is deprecated */
			trigger_error(
				'Calling setSmartyVars without cms parameter when needed is deprecated',
				E_USER_DEPRECATED
			);
		}
		// this is ugly
		$cms = $cms ?? $GLOBALS['cms'] ?? null;
		if ($cms instanceof \CoreLibs\Admin\Backend) {
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
		$this->HEADER['CSS'] = $set_css;
		$this->HEADER['FONT'] = $set_font;
		$this->HEADER['JS'] = $set_js;
		$this->HEADER['ENCODING'] = $this->encoding;
		$this->HEADER['DEFAULT_ENCODING'] = $set_default_encoding;

		// form name
		$this->DATA['FORM_NAME'] = !$this->FORM_NAME ?
			str_replace('.php', '', $this->page_name) :
			$this->FORM_NAME;
		$this->DATA['FORM_ACTION'] = $this->FORM_ACTION;
		// special for admin
		if ($admin_call === true) {
			// set ACL extra show
			if ($cms instanceof \CoreLibs\Admin\Backend) {
				$this->DATA['show_ea_extra'] = $cms->acl['show_ea_extra'] ?? false;
				$this->DATA['ADMIN'] = $cms->acl['admin'] ?? 0;
				// top menu
				$this->DATA['nav_menu'] = $cms->adbTopMenu();
				$this->DATA['nav_menu_count'] = count($this->DATA['nav_menu']);
				// messages = ['msg' =>, 'class' => 'error/warning/...']
				$this->DATA['messages'] = $cms->messages;
			} else {
				$this->DATA['show_ea_extra'] = false;
				$this->DATA['ADMIN'] = 0;
				$this->DATA['nav_menu'] = [];
				$this->DATA['nav_menu_count'] = 0;
				$this->DATA['messages'] = [];
			}
			// set style sheets
			$this->HEADER['STYLESHEET'] = !empty($this->ADMIN_STYLESHEET) ?
				$this->ADMIN_STYLESHEET : $set_admin_stylesheet;
			$this->HEADER['JAVASCRIPT'] = !empty($this->ADMIN_JAVASCRIPT) ?
				$this->ADMIN_JAVASCRIPT : $set_admin_javascript;
			// the page name
			$this->DATA['page_name'] = $this->page_name;
			$this->DATA['table_width'] = !empty($this->PAGE_WIDTH) ?: $set_page_width;
			$this->DATA['form_name'] = $this->DATA['FORM_NAME'];
			// for tinymce special
			$this->DATA['TINYMCE_LANG'] = $this->lang_short;
			// include flags
			$this->DATA['USE_TINY_MCE'] = $this->USE_TINY_MCE;
			// debug data, if DEBUG flag is on, this data is print out
			$this->DEBUG_DATA['DEBUG'] = $this->DEBUG_TMPL;
		} else {
			$this->HEADER['STYLESHEET'] = !empty($this->FRONTEND_STYLESHEET) ?
				$this->FRONTEND_STYLESHEET : $set_stylesheet;
			$this->HEADER['JAVASCRIPT'] = !empty($this->FRONTEND_JAVASCRIPT) ?
				$this->FRONTEND_JAVASCRIPT : $set_javascript;
		}
		// html title
		// set local page title
		$this->HEADER['HTML_TITLE'] = !$this->L_TITLE ?
			ucfirst(str_replace('_', ' ', \CoreLibs\Get\System::getPageName(1)))
				. (!empty($set_g_title) ? '-' . $this->l10n->__($set_g_title) : '') :
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
		$this->DATA['JS_TRANSLATE'] = $this->JS_TRANSLATE ?? null;
		$this->DATA['PAGE_FILE_NAME'] = str_replace('.php', '', $this->page_name) . '.tpl';
		// render page
		$this->renderSmarty($compile_dir, $cache_dir);
	}

	/**
	 * merge outside object HEADER/DATA/DEBUG_DATA vars into the smarty class
	 *
	 * @param  \CoreLibs\Admin\Backend $cms object that has header/data/debug_data
	 * @return void
	 */
	public function mergeCmsSmartyVars(\CoreLibs\Admin\Backend $cms): void
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
	 *
	 * @param  string|null $compile_dir BASE . TEMPLATES_C
	 * @param  string|null $cache_dir   BASE . CACHE
	 * @return void
	 */
	public function renderSmarty(
		?string $compile_dir = null,
		?string $cache_dir = null
	): void {
		// trigger deprecation
		if (
			$compile_dir === null ||
			$cache_dir === null
		) {
			/** @deprecated renderSmarty call without parameters */
			trigger_error(
				'Calling renderSmarty without paramters is deprecated',
				E_USER_DEPRECATED
			);
		}
		// set vars (to be deprecated)
		$compile_dir = $compile_dir ?? BASE . TEMPLATES_C;
		$cache_dir = $cache_dir ?? BASE . CACHE;
		// create main data array
		$this->CONTENT_DATA = array_merge($this->HEADER, $this->DATA, $this->DEBUG_DATA);
		// data is 1:1 mapping (all vars, values, etc)
		foreach ($this->CONTENT_DATA as $key => $value) {
			$this->assign($key, $value);
		}
		if (is_dir($compile_dir)) {
			$this->setCompileDir($compile_dir);
		}
		if (is_dir($cache_dir)) {
			$this->setCacheDir($cache_dir);
		}
		$this->display(
			$this->MASTER_TEMPLATE_NAME,
			$this->CACHE_ID . ($this->CACHE_ID ? '_' : '') . $this->lang,
			$this->COMPILE_ID . ($this->COMPILE_ID ? '_' : '') . $this->lang
		);
	}
}

// __END__
