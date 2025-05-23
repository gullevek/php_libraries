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

class SmartyExtend extends \Smarty\Smarty
{
	// internal translation engine
	/** @var \CoreLibs\Language\L10n language class */
	public \CoreLibs\Language\L10n $l10n;
	/** @var \CoreLibs\Logging\Logging $log logging class */
	public \CoreLibs\Logging\Logging $log;

	// lang & encoding
	/** @var string */
	public string $lang_dir = '';
	/** @var string */
	public string $lang;
	/** @var string */
	public string $lang_short;
	/** @var string */
	public string $domain;
	/** @var string */
	public string $encoding;
	// page name
	/** @var string */
	public string $page_name;

	// array for data parsing
	/** @var array<mixed> */
	public array $HEADER = [];
	/** @var array<mixed> */
	public array $DATA = [];
	/** @var array<mixed> */
	public array $DEBUG_DATA = [];
	/** @var array<mixed> */
	private array $CONTENT_DATA = [];
	// control vars
	/** @var bool */
	public bool $USE_PROTOTYPE = USE_PROTOTYPE;
	/** @var bool */
	public bool $USE_JQUERY = USE_JQUERY;
	/** @var bool */
	public bool $USE_SCRIPTACULOUS = USE_SCRIPTACULOUS;
	// sub content input vars
	/** @var bool */
	public bool $USE_TINY_MCE = false;
	/** @var bool */
	public bool $JS_DATEPICKR = false;
	/** @var bool */
	public bool $JS_FLATPICKR = false;
	/** @var bool */
	public bool $JS_FILE_UPLOADER = false;
	/** @var bool */
	public bool $DEBUG_TMPL = false;
	/** @var bool */
	public bool $USE_INCLUDE_TEMPLATE = false;
	// cache & compile
	/** @var string */
	public string $CACHE_ID = '';
	/** @var string */
	public string $COMPILE_ID = '';
	// template vars
	/** @var string */
	public string $MASTER_TEMPLATE_NAME = '';
	/** @var string */
	public string $PAGE_FILE_NAME = '';
	/** @var string */
	public string $CONTENT_INCLUDE = '';
	/** @var string */
	public string $FORM_NAME = '';
	/** @var string */
	public string $FORM_ACTION = '';
	/** @var string */
	public string $L_TITLE = '';
	/** @var string|int */
	public string|int $PAGE_WIDTH;
	// smarty include/set var
	/** @var string */
	public string $TEMPLATE_PATH = '';
	/** @var string */
	public string $TEMPLATE_NAME = '';
	/** @var string */
	public string $INC_TEMPLATE_NAME = '';
	/** @var string */
	public string $JS_TEMPLATE_NAME = '';
	/** @var string */
	public string $CSS_TEMPLATE_NAME = '';
	/** @var string|null */
	public string|null $TEMPLATE_TRANSLATE;
	/** @var string|null */
	public string|null $JS_TRANSLATE;
	// core group
	/** @var string */
	public string $JS_CORE_TEMPLATE_NAME = '';
	/** @var string */
	public string $CSS_CORE_TEMPLATE_NAME = '';
	/** @var string */
	public string $JS_CORE_INCLUDE = '';
	/** @var string */
	public string $CSS_CORE_INCLUDE = '';
	// local names
	/** @var string */
	public string $JS_SPECIAL_TEMPLATE_NAME = '';
	/** @var string */
	public string $CSS_SPECIAL_TEMPLATE_NAME = '';
	/** @var string */
	public string $JS_INCLUDE = '';
	/** @var string */
	public string $CSS_INCLUDE = '';
	/** @var string */
	public string $JS_SPECIAL_INCLUDE = '';
	/** @var string */
	public string $CSS_SPECIAL_INCLUDE = '';
	/** @var string */
	public string $ADMIN_JAVASCRIPT = '';
	/** @var string */
	public string $ADMIN_STYLESHEET = '';
	/** @var string */
	public string $FRONTEND_JAVASCRIPT = '';
	/** @var string */
	public string $FRONTEND_STYLESHEET = '';
	// other smarty folder vars
	/** @var string */
	public string $INCLUDES = '';
	/** @var string */
	public string $JAVASCRIPT = '';
	/** @var string */
	public string $CSS = '';
	/** @var string */
	public string $FONT = '';
	/** @var string */
	public string $PICTURES = '';
	/** @var string */
	public string $CACHE_PICTURES = '';
	/** @var string */
	public string $CACHE_PICTURES_ROOT = '';

	// constructor class, just sets the language stuff
	/**
	 * constructor class, just sets the language stuff
	 * calls L10 for pass on internaly in smarty
	 * also registers the getvar caller plugin
	 *
	 * @param \CoreLibs\Language\L10n   $l10n l10n language class
	 * @param \CoreLibs\Logging\Logging $log Logger class
	 * @param string|null               $cache_id [default=null]
	 * @param string|null               $compile_id [default=null]
	 * @param array<string,mixed>       $options [default=[]]
	 */
	public function __construct(
		\CoreLibs\Language\L10n $l10n,
		\CoreLibs\Logging\Logging $log,
		?string $cache_id = null,
		?string $compile_id = null,
		array $options = []
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
		// set variables from global constants (deprecated)
		if ($cache_id === null && defined('CACHE_ID')) {
			trigger_error(
				'SmartyExtended: No cache_id set and CACHE_ID constant set, this is deprecated',
				E_USER_DEPRECATED
			);
			$cache_id = CACHE_ID;
		}
		if ($compile_id === null && defined('COMPILE_ID')) {
			trigger_error(
				'SmartyExtended: No compile_id set and COMPILE_ID constant set, this is deprecated',
				E_USER_DEPRECATED
			);
			$compile_id = COMPILE_ID;
		}
		if (empty($cache_id)) {
			throw new \BadMethodCallException('cache_id parameter is not set');
		}
		if (empty($compile_id)) {
			throw new \BadMethodCallException('compile_id parameter is not set');
		}

		// call basic smarty
		parent::__construct();

		$this->log = $log;

		// init lang
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
		$this->registerPlugin(self::PLUGIN_MODIFIER, 'getvar', [&$this, 'getTemplateVars']);

		$this->page_name = \CoreLibs\Get\System::getPageName();

		// set internal settings
		$this->CACHE_ID = $cache_id;
		$this->COMPILE_ID = $compile_id;
		// set options
		$this->setOptions($options);
	}

	/**
	 * set options
	 *
	 * @param  array<string,mixed> $options
	 * @return void
	 */
	private function setOptions(array $options): void
	{
		// set escape html if option is set
		if (!empty($options['escape_html'])) {
			$this->setEscapeHtml(true);
		}
		// load plugins
		// plugin array:
		// 'file': string, path to plugin content to load
		// 'type': a valid smarty type see Smarty PLUGIN_ constants for correct names
		// 'tag': the smarty tag
		// 'callback': the function to call in 'file'
		if (!empty($options['plugins'])) {
			foreach ($options['plugins'] as $plugin) {
				// file is readable
				if (
					empty($plugin['file']) ||
					!is_file($plugin['file']) ||
					!is_readable($plugin['file'])
				) {
					$this->log->warning('SmartyExtended plugin load failed, file not accessable', [
						'plugin' => $plugin,
					]);
					continue;
				}
				// tag is alphanumeric
				if (!preg_match("/^\w+$/", $plugin['tag'] ?? '')) {
					$this->log->warning('SmartyExtended plugin load failed, invalid tag', [
						'plugin' => $plugin,
					]);
					continue;
				}
				// callback is alphanumeric
				if (!preg_match("/^\w+$/", $plugin['callback'] ?? '')) {
					$this->log->warning('SmartyExtended plugin load failed, invalid callback', [
						'plugin' => $plugin,
					]);
					continue;
				}
				try {
					/** @phan-suppress-next-line PhanNoopNew */
					new \ReflectionClassConstant($this, $plugin['type']);
				} catch (\ReflectionException $e) {
					$this->log->error('SmartyExtended plugin load failed, type is not valid', [
						'message' => $e->getMessage(),
						'plugin' => $plugin,
					]);
					continue;
				}
				try {
					require $plugin['file'];
					$this->registerPlugin($plugin['type'], $plugin['tag'], $plugin['callback']);
				} catch (\Smarty\Exception $e) {
					$this->log->error('SmartyExtended plugin load failed with exception', [
						'message' => $e->getMessage(),
						'plugin' => $plugin,
					]);
					continue;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function setSmartCoreIncludeCssJs(): void
	{
		// core CS
		$this->CSS_CORE_INCLUDE = '';
		if (
			!empty($this->CSS_CORE_TEMPLATE_NAME) &&
			file_exists($this->CSS . $this->CSS_CORE_TEMPLATE_NAME) &&
			is_file($this->CSS . $this->CSS_CORE_TEMPLATE_NAME)
		) {
			$this->CSS_CORE_INCLUDE = $this->CSS . $this->CSS_CORE_TEMPLATE_NAME;
		}
		// core JS
		$this->JS_CORE_INCLUDE = '';
		if (
			!empty($this->JS_CORE_TEMPLATE_NAME) &&
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
			empty($this->TEMPLATE_NAME)
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
			!empty($this->TEMPLATE_NAME) &&
			!file_exists($this->getTemplateDir()[0] . DIRECTORY_SEPARATOR . $this->TEMPLATE_NAME)
		) {
			exit('INCLUDE TEMPLATE: ' . $this->TEMPLATE_NAME . ' could not be found');
		}
		// javascript translate data as template for auto translate
		if (empty($this->TEMPLATE_TRANSLATE)) {
			$this->TEMPLATE_TRANSLATE = 'jsTranslate-'
				. $this->l10n->getLocaleSet() . '.' . $this->encoding
				. '.tpl';
		} else {
			// we assume we have some fixed set
			// we must add _<locale>.<encoding>
			// if .tpl, put before .tpl
			// if not .tpl, add _<locale>.<encoding>.tpl
			if (strpos($this->TEMPLATE_TRANSLATE, '.tpl')) {
				$this->TEMPLATE_TRANSLATE = str_replace(
					'.tpl',
					'-' . $this->l10n->getLocaleSet() . '.' . $this->encoding . '.tpl',
					$this->TEMPLATE_TRANSLATE
				);
			} else {
				$this->TEMPLATE_TRANSLATE .= '-'
					. $this->l10n->getLocaleSet() . '.' . $this->encoding
					. '.tpl';
			}
		}
		// if we can't find it, dump it
		if (
			!file_exists(
				$this->getTemplateDir()[0] . DIRECTORY_SEPARATOR
					. $this->TEMPLATE_TRANSLATE
			)
		) {
			$this->TEMPLATE_TRANSLATE = null;
		}
		if (empty($this->JS_TRANSLATE)) {
			$this->JS_TRANSLATE = 'translate-'
				. $this->l10n->getLocaleSet() . '.' . $this->encoding . '.js';
		} else {
			// we assume we have some fixed set
			// we must add _<locale>.<encoding>
			// if .js, put before .js
			// if not .js, add _<locale>.<encoding>.js
			if (strpos($this->JS_TRANSLATE, '.js')) {
				$this->JS_TRANSLATE = str_replace(
					'.js',
					'-' . $this->l10n->getLocaleSet() . '.' . $this->encoding . '.js',
					$this->JS_TRANSLATE
				);
			} else {
				$this->JS_TRANSLATE .= '-'
					. $this->l10n->getLocaleSet() . '.' . $this->encoding
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
	 * @param  array<string,string> $options list with the following value:
	 *                              compile_dir      :BASE . TEMPLATES_C
	 *                              cache_dir        :BASE . CACHE
	 *                              js               :JS
	 *                              css              :CSS
	 *                              font             :FONT
	 *                              default_encoding :DEFAULT_ENCODING
	 *                              g_title          :G_TITLE
	 *                              stylesheet       :STYLESHEET
	 *                              javascript       :JAVASCRIPT
	 * @param  array<string,mixed>  $smarty_data     array of three keys
	 *                                               that hold smarty set strings
	 *                                               HEADER, DATA, DEBUG_DATA
	 * @return void
	 */
	public function setSmartyVarsFrontend(
		array $options,
		array $smarty_data
	): void {
		$this->setSmartyVars(
			false,
			$smarty_data,
			null,
			$options['compile_dir'] ?? null,
			$options['cache_dir'] ?? null,
			$options['js'] ?? null,
			$options['css'] ?? null,
			$options['font'] ?? null,
			$options['default_encoding'] ?? null,
			$options['g_title'] ?? null,
			null,
			null,
			null,
			null,
			null,
			$options['stylesheet'] ?? null,
			$options['javascript'] ?? null
		);
	}

	/**
	 * wrapper call for setSmartyVars
	 * this is only for admin interface and will set additional variables
	 * @param  array<string,string> $options list with the following value:
	 *                              compile_dir      :BASE . TEMPLATES_C
	 *                              cache_dir        :BASE . CACHE
	 *                              js               :JS
	 *                              css              :CSS
	 *                              font             :FONT
	 *                              default_encoding :DEFAULT_ENCODING
	 *                              g_title          :G_TITLE
	 *                              admin_stylesheet :ADMIN_STYLESHEET
	 *                              admin_javascript :ADMIN_JAVASCRIPT
	 *                              page_width       :PAGE_WIDTH
	 *                              content_path     :CONTENT_PATH
	 *                              user_name        :_SESSION['USER_NAME']
	 * @param  \CoreLibs\Admin\Backend|null $cms Optinal Admin Backend for
	 *                                           smarty variables merge
	 * @return void
	 */
	public function setSmartyVarsAdmin(
		array $options,
		?\CoreLibs\Admin\Backend $cms = null
	): void {
		// if we have cms data, check for array blocks and build
		$smarty_data = [];
		if ($cms !== null) {
			$smarty_data = [
				'HEADER' => $cms->HEADER,
				'DATA' => $cms->DATA,
				'DEBUG_DATA' => $cms->DEBUG_DATA
			];
		}
		$this->setSmartyVars(
			true,
			$smarty_data,
			$cms,
			$options['compile_dir'] ?? null,
			$options['cache_dir'] ?? null,
			$options['js'] ?? null,
			$options['css'] ?? null,
			$options['font'] ?? null,
			$options['g_title'] ?? null,
			$options['default_encoding'] ?? null,
			$options['admin_stylesheet'] ?? null,
			$options['admin_javascript'] ?? null,
			$options['page_width'] ?? null,
			$options['content_path'] ?? null,
			$options['user_name'] ?? null,
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
	 * @param  array<string,mixed> $smarty_data  smarty data to merge
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
	 * @param  string|null $set_content_path     CONTENT_PATH  (only if $cms set and admin)
	 * @param  string|null $set_user_name        _SESSION['USER_NAME']
	 * @param  string|null $set_stylesheet       STYLESHEET
	 * @param  string|null $set_javascript       JAVASCRIPT
	 * @return void
	 */
	private function setSmartyVars(
		bool $admin_call,
		array $smarty_data = [],
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
		?string $set_content_path = null,
		?string $set_user_name = null,
		?string $set_stylesheet = null,
		?string $set_javascript = null,
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
					$set_page_width === null ||
					$set_user_name === null
				)
			) ||
			(
				$admin_call === false && (
					$set_stylesheet === null ||
					$set_javascript === null
				)
			) ||
			(
				$admin_call === true && $cms !== null && $set_content_path === null
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
		$set_content_path = $set_content_path ?? CONTENT_PATH;
		$set_stylesheet = $set_stylesheet ?? STYLESHEET;
		$set_javascript = $set_javascript ?? JAVASCRIPT;
		$set_user_name = $set_user_name ?? $_SESSION['USER_NAME'] ?? '';
		// merge additional smarty data
		$this->mergeCmsSmartyVars($smarty_data);

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
		$this->DATA['FORM_NAME'] = empty($this->FORM_NAME) ?
			str_replace('.php', '', $this->page_name) :
			$this->FORM_NAME;
		$this->DATA['FORM_ACTION'] = empty($this->FORM_ACTION) ?
			'' :
			$this->FORM_ACTION;
		// special for admin
		if ($admin_call === true) {
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
			// set ACL extra show
			if ($cms instanceof \CoreLibs\Admin\Backend) {
				$this->DATA['show_ea_extra'] = $cms->acl['show_ea_extra'] ?? false;
				$this->DATA['ADMIN'] = $cms->acl['admin'] ?? 0;
				// top menu
				$this->DATA['nav_menu'] = $cms->adbTopMenu(
					$set_content_path
				);
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
		$this->HEADER['HTML_TITLE'] = empty($this->L_TITLE) ?
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
		$this->DATA['USER_NAME'] = $set_user_name;
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
	 * @param  array<string,mixed> $smarty_data array that has header/data/debug_data
	 * @return void
	 */
	public function mergeCmsSmartyVars(array $smarty_data): void
	{
		// array merge HEADER, DATA, DEBUG DATA
		foreach (['HEADER', 'DATA', 'DEBUG_DATA'] as $ext_smarty) {
			if (
				isset($smarty_data[$ext_smarty]) &&
				is_array($smarty_data[$ext_smarty])
			) {
				$this->{$ext_smarty} = array_merge($this->{$ext_smarty}, $smarty_data[$ext_smarty]);
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
