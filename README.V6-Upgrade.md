# Upgrade to Version 6

 * remove old `lib/CoreLibs` and copy the new over
 * copy `config/config.php`
 * install composer if not installed `composer init` and `composer install`
 * update composer.json
 ```json
"autoload": {
    "classmap": [
        "lib/"
    ]
},
```
Run to update autoloader list
```sh
composer dump-autoload
```

 * copy `includes/edit_base.inc`
  * add session start in the top header block where the `header()` calls are
```php
// start session
CoreLibs\Create\Session::startSession();
```
 * update all header calls if needed to add new log type call
 ```php
// create logger
$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => LOG_FILE_ID,
	'print_file_date' => true,
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
```
 * add a db class
```php
// db config with logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
```
 * login class needs to have db and logger added
```php
// login & page access check
$login = new CoreLibs\ACL\Login($db, $log);
```
* update language class
```php
// pre auto detect language after login
$locale = \CoreLibs\Language\GetLocale::setLocale();
// set lang and pass to smarty/backend
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
);
```
* smarty needs language
```php
$smarty = new CoreLibs\Template\SmartyExtend($l10n, $locale);
```
* admin backend also needs logger
```php
$cms = new CoreLibs\Admin\Backend($db, $log, $l10n, $locale);
```
* update and `$cms` or similar calls so db is in `$cms->db->...` and log are in `$cms->log->...`
* update all `config.*.php` files where needed
* check config.master.php for `BASE_NAME` and `G_TITLE` and set them in the `.env` file so the `config.master.php` can be copied as os
* If not doable, see changed below in `config.master.php` must remove old auto loder and `FLASH` constant at least
**REMOVE:**
```php
/************* AUTO LOADER *******************/
// read auto loader
require BASE . LIB . 'autoloader.php';
```
**UPDATE:**
```php
// po langs [DEPRECAED: use LOCALE]
define('LANG', 'lang' . DIRECTORY_SEPARATOR);
// po locale file
define('LOCALE', 'locale' . DIRECTORY_SEPARATOR);
```
```php
// SSL host name
// define('SSL_HOST', $_ENV['SSL_HOST'] ?? '');
```
```php
// define full regex
define('PASSWORD_REGEX', "/^"
	. (defined('PASSWORD_LOWER') ? PASSWORD_LOWER : '')
	. (defined('PASSWORD_UPPER') ? PASSWORD_UPPER : '')
	. (defined('PASSWORD_NUMBER') ? PASSWORD_NUMBER : '')
	. (defined('PASSWORD_SPECIAL') ? PASSWORD_SPECIAL : '')
	. "[A-Za-z\d" . PASSWORD_SPECIAL_RANGE . "]{" . PASSWORD_MIN_LENGTH . "," . PASSWORD_MAX_LENGTH . "}$/");
```
```php
/************* LAYOUT WIDTHS *************/
define('PAGE_WIDTH', '100%');
define('CONTENT_WIDTH', '100%');
```
```php
/************* OVERALL CONTROL NAMES *************/
// BELOW has HAS to be changed
// base name for all session and log names
// only alphanumeric characters, strip all others
define('BASE_NAME', preg_replace('/[^A-Za-z0-9]/', '', $_ENV['BASE_NAME'] ?? ''));
```
```php
/************* LANGUAGE / ENCODING *******/
// default lang + encoding
define('DEFAULT_LOCALE', 'en_US.UTF-8');
// default web page encoding setting
define('DEFAULT_ENCODING', 'UTF-8');
```
```php
// BAIL ON MISSING DB CONFIG:
// we have either no db selction for this host but have db config entries
// or we have a db selection but no db config as array or empty
// or we have a selection but no matching db config entry
if (
	(!isset($SITE_CONFIG[HOST_NAME]['db_host']) && count($DB_CONFIG)) ||
	(isset($SITE_CONFIG[HOST_NAME]['db_host']) &&
		// missing DB CONFIG
		((is_array($DB_CONFIG) && !count($DB_CONFIG)) ||
		!is_array($DB_CONFIG) ||
		// has DB CONFIG but no match
		empty($DB_CONFIG[$SITE_CONFIG[HOST_NAME]['db_host']]))
	)
) {
	echo 'No matching DB config found for: "' . HOST_NAME . '". Contact Administrator';
	exit;
}
```
```php
// remove SITE_LANG
define('SITE_LOCALE', $SITE_CONFIG[HOST_NAME]['site_locale'] ?? DEFAULT_LOCALE);
define('SITE_ENCODING', $SITE_CONFIG[HOST_NAME]['site_encoding'] ?? DEFAULT_ENCODING);
```
```php
/************* GENERAL PAGE TITLE ********/
define('G_TITLE', $_ENV['G_TITLE'] ?? '');
```
* move all login passweords into the `.env` file in the `configs/` folder
in the `.env` file
```
DB_NAME.TEST=some_database
...
```
In the config then
```php
'db_name' => $_ENV['DB_NAME.TEST'] ?? '',
```
* config.host.php update
must add site_locale (site_lang + site_encoding)
remove site_lang
```php
	// lang + encoding
	'site_locale' => 'en_US.UTF-8',
	// site language
	'site_encoding' => 'UTF-8',
```
* copy `layout/admin/javascript/edit.jq.js`
* check other javacsript files if needed (`edit.jq.js`)

## IMPORTANT NOTE

If no upgrade to V5 was done all calls that refered to `CoreLibs\Basic` will now fail and no longer be warned as deprected
See the old file for all methods and where they have moved
