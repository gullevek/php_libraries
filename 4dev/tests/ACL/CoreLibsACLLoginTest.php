<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \CoreLibs\ACL\Login
 * @testdox \CoreLibs\ACL\Login method tests
 */
final class CoreLibsACLLoginTest extends TestCase
{
	private static $db;
	private static $log;

	/**
	 * start DB conneciton, setup DB, etc
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		if (!extension_loaded('pgsql')) {
			self::markTestSkipped(
				'The PgSQL extension is not available for ACL\Login test.'
			);
		}
		// database/CoreLibsACLLogin_database_create_data.sql
		$load_sql_file = __DIR__
			. DIRECTORY_SEPARATOR
			. 'database/CoreLibsACLLogin_database_create_data.sql';
		if (!is_file($load_sql_file)) {
			self::markTestIncomplete(
				'Missing ACL\Login database load SQL file'
			);
		}
		// ASSUME that DB is on Port 5432 and use DEFAULT in path postgresql
		$db_user = "corelibs_acl_login_test";
		$db_password = "corelibs_acl_login_test";
		$db_name = "corelibs_acl_login_test";
		$db_host = "localhost";
		// run the drop restore script before connecting to the database
		// check exit, if not null then abort
		$command = __DIR__ . DIRECTORY_SEPARATOR
			. "CoreLibsACLLogin_database_prepare.sh "
			. "$load_sql_file "
			. "$db_user "
			. "$db_name "
			. "$db_host ";
		exec($command, $ouput, $result);
		if ($result != 0 || !empty($output[0])) {
			self::markTestIncomplete(
				'Drop/Create ACL\Login database failed with: ' . $result
			);
		}

		// logger is always needed
		// define basic connection set valid and one invalid
		self::$log = new \CoreLibs\Debug\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'file_id' => 'CoreLibs-ACL-Login-Test',
			'debug_all' => true,
			'echo_all' => false,
			'print_all' => true,
		]);
		// test database we need to connect do, if not possible this test is skipped
		self::$db = new \CoreLibs\DB\IO(
			[
				'db_name' => $db_name,
				'db_user' => $db_user,
				'db_pass' => $db_password,
				'db_host' => $db_host,
				'db_port' => 5432,
				'db_schema' => 'public',
				'db_type' => 'pgsql',
				'db_encoding' => '',
				'db_ssl' => 'allow', // allow, disable, require, prefer
				'db_debug' => true,
			],
			self::$log
		);
		// ALWAYS drop DB and RECREATE DB
		// dropdb -U corelibs_acl_login_test -h localhost corelibs_acl_login_test
		// createdb -U corelibs_acl_login_test -O corelibs_acl_login_test -E utf8 corelibs_acl_login_test;
		if (!self::$db->dbGetConnectionStatus()) {
			self::markTestSkipped(
				'Cannot connect to valid Test DB for ACL\Login test.'
			);
		}
		// check that edit_user table exist, I assume if this one does,
		// the rest does too
		if (!self::$db->dbShowTableMetaData('edit_user') !== false) {
			self::markTestIncomplete(
				'Cannot find edit_user table in ACL\Login database for testing'
			);
		}
		// always disable max query calls
		self::$db->dbSetMaxQueryCall(-1);
		// insert additional content for testing (locked user, etc)
		$queries = [
			"INSERT INTO edit_access_data "
				. "(edit_access_id, name, value, enabled) VALUES "
				. "((SELECT edit_access_id FROM edit_access WHERE uid = 'AdminAccess'), "
				. "'test', 'value', 1)"
		];
		foreach ($queries as $query) {
			self::$db->dbExec($query);
		}

		// define mandatory constant
		// must set
		// TARGET
		define('TARGET', 'test');
		// LOGIN DB SCHEMA
		// define('LOGIN_DB_SCHEMA', '');

		// SHOULD SET
		// PASSWORD_MIN_LENGTH (d9)
		// PASSWORD_MAX_LENGTH (d255)
		// DEFAULT_ACL_LEVEL (d80)

		// OPT:
		// LOGOUT_TARGET
		// PASSWORD_CHANGE
		// PASSWORD_FORGOT

		// LANG:
		// SITE_LOCALE
		// DEFAULT_LOCALE
		// CONTENT_PATH (domain)

		$_SESSION = [];
		global $_SESSION;
	}

	/**
	 * close db
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		if (self::$db->dbGetConnectionStatus()) {
			self::$db->dbClose();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function loginProvider(): array
	{
		// 0[mock]   : mock settings/override flag settings
		// 1[get]    : get array IN
		// 2[post]   : post array IN
		//             login_login, login_username, login_password, login_logout
		//             change_password, pw_username, pw_old_password, pw_new_password,
		//             pw_new_password_confirm
		// 3[session]: override session set
		// 4[error]  : expected error code, 0 for all ok, 3000 for login page view
		//             note that 1000 (no db), 2000 (no session) must be tested too
		// 5[return] : expected return array, eg login_error code,
		//             or other info data to match
		$tests = [
			'load, no login' => [
				// error code, only for exceptions
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[],
				[],
				3000,
				[
					'login_error' => 0,
					'error_string' => 'Success: <b>No error</b>',
					'error_string_text' => 'Success: No error',
				],
			],
			'load, no login, ajax flag' => [
				// error code, only for exceptions
				[
					'page_name' => 'edit_users.php',
					// for ajax
					'ajax_page' => true,
					'ajax_test_type' => 'parameter',
				],
				[],
				[],
				[],
				3000,
				[
					'login_error' => 0,
					'error_string' => 'Success: <b>No error</b>',
					'error_string_text' => 'Success: No error',
					// for ajax
					'action' => 'login',
					'ajax_get_count' => 0,
					'ajax_post_count' => 5,
					'ajax_post_action' => 'login',
				],
			],
			'load, no login, ajax globals' => [
				// error code, only for exceptions
				[
					'page_name' => 'edit_users.php',
					// for ajax
					'ajax_page' => true,
					'ajax_test_type' => 'globals',
				],
				[],
				[],
				[],
				3000,
				[
					'login_error' => 0,
					'error_string' => 'Success: <b>No error</b>',
					'error_string_text' => 'Success: No error',
					// for ajax
					'action' => 'login',
					'ajax_get_count' => 0,
					'ajax_post_count' => 5,
					'ajax_post_action' => 'login',
				],
			],
			'load, session euid set only, php error' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[],
				[
					'EUID' => 1,
				],
				2,
				[],
			],
			'load, session euid set, all set' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
				],
				[],
				[],
				[
					'EUID' => 1,
					'USER_NAME' => '',
					'GROUP_NAME' => '',
					'ADMIN' => 1,
					'GROUP_ACL_LEVEL' => -1,
					'PAGES_ACL_LEVEL' => [],
					'USER_ACL_LEVEL' => -1,
					'UNIT_UID' => [
						'AdminAccess' => 1,
					],
					'UNIT' => [
						1 => [
							'acl_level' => 80,
							'name' => 'Admin Access',
							'uid' => 'AdminAccess',
							'level' => -1,
							'default' => 0,
							'data' => [
								'test' => 'value',
							],
						],
					],
					// 'UNIT_DEFAULT' => '',
					// 'DEFAULT_ACL_LIST' => [],
				],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				],
			],
			// login: all missing
			'login: failed: all missing' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => '',
					'login_password' => '',
				],
				[],
				3000,
				[
					'login_error' => 102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Please enter username and password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Please enter username and password'
				]
			],
			// login: missing username
			'login: failed: missing username' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => '',
					'login_password' => 'abc',
				],
				[],
				3000,
				[
					'login_error' => 102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Please enter username and password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Please enter username and password'
				]
			],
			// login: missing password
			'login: failed: missing password' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'abc',
					'login_password' => '',
				],
				[],
				3000,
				[
					'login_error' => 102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Please enter username and password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Please enter username and password'
				]
			],
			// login: user not found
			'login: failed: user not found' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'abc',
					'login_password' => 'abc',
				],
				[],
				3000,
				[
					'login_error' => 1010,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Wrong Username or Password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Wrong Username or Password'
				]
			],
			// login: invalid password
			//           9999: not valid password encoding
			//           1013: normal password failed
			//           1012: plain password check failed
			'login: failed: invalid password' => [
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'abc',
				],
				[],
				3000,
				[
					// default password is plain text
					'login_error' => 1012,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Wrong Username or Password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Wrong Username or Password'
				]
			],
			// login: ok (but deleted)
			'login: ok -> failed: but deleted' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_deleted' => true
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 106,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User is deleted</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User is deleted'
				]
			],
			// login: ok (but not enabled)
			'login: ok -> failed: but not enabled' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_enabled' => true
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 104,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User not enabled</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User not enabled'
				]
			],
			// login: ok (but locked)
			'login: ok -> failed: but locked' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked' => true
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 105,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User is locked</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User is locked'
				]
			],
			// login: make user get locked strict
			'login: ok -> failed: get locked, strict' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_get_locked' => true,
					'max_login_error_count' => 2,
					'test_locked_strict' => true,
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				0,
				[
					'lock_run_login_error' => 1012,
					'login_error' => 105,
				]
			],
			// login ok, but in locked period (until)
			'login: ok -> failed: but locked period (until:on)' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked_period_until' => 'on'
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 107,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User in locked via date period</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User in locked via date period'
				]
			],
			// login ok, but in locked period (until)
			'login: ok, but locked period (until:off)' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked_period_until' => 'off'
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// login ok, but in locked period (after)
			'login: ok -> failed: but locked period (after:on)' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked_period_after' => 'on'
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 107,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User in locked via date period</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User in locked via date period'
				]
			],
			// login ok, but in locked period (until, after)
			'login: ok -> failed:, but locked period (until:on, after:on)' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked_period_until' => 'on',
					'test_locked_period_after' => 'on'
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 107,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User in locked via date period</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User in locked via date period'
				]
			],
			// login ok, but login user id locked
			'login: ok -> failed:, but loginUserId locked' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_locked' => true
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				3000,
				[
					'login_error' => 108,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - User is locked via Login User ID</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - User is locked via Login User ID'
				]
			],
			// login: ok
			'login: ok' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
				],
				[],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// login check via _GET loginUserId
			'login: ok, _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// login check via _POST loginUserId
			'login: ok, _POST loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// login: wrong GET loginUserId
			'login: ok, illegal chars in _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG'
				],
				[
					'loginUserId' => '123$%_/45678¥\-^9~~0$AB&CDEFG',
				],
				[],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			'login: not matching _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG'
				],
				[
					'loginUserId' => 'ABC'
				],
				[],
				[],
				3000,
				[
					'login_error' => 1010,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Wrong Username or Password</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Wrong Username or Password'
				]
			],
			// login ok with both _GET loginUserId and _POST login username/password
			'login: ok, _GET loginUserId AND login post user data' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// login with invalid loginUserId but valid username/password
			'login: ok, bad _GET loginUserId AND good login post user data' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => 'ABCS',
				],
				[
					'login_login' => 'Login',
					'login_username' => 'admin',
					'login_password' => 'admin',
				],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// loginUserId check with revalidate on/off
			'login: ok -> failed:, but revalidate trigger, _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_revalidate_after' => 'on',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				3000,
				[
					'login_error' => 1101,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Login User ID must be validated</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Login User ID must be validated'
				]
			],
			// loginUserId check with revalidate on/off
			'login: ok, revalidate set (outside), _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_revalidate_after' => 'off',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// loginUserId check with active time from only
			'login: ok -> failed:, _GET loginUserId, but outside valid (from:on) ' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_valid_from' => 'on',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				3000,
				[
					'login_error' => 1102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Login User ID is outside valid date range</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Login User ID is outside valid date range'
				]
			],
			// loginUserId check with inactive time from only
			'login: ok, _GET loginUserId, but outside valid (from:off) ' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_valid_from' => 'off',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			],
			// loginUserId check with active time until only
			'login: ok -> failed:, _GET loginUserId, but outside valid (until:on) ' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_valid_until' => 'on',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				3000,
				[
					'login_error' => 1102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Login User ID is outside valid date range</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Login User ID is outside valid date range'
				]
			],
			// loginUserId check with active time from/until
			'login: ok -> failed:, _GET loginUserId, but outside valid (from:on,until:on) ' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_valid_from' => 'on',
					'test_login_user_id_valid_until' => 'on',
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
				],
				[
					'loginUserId' => '1234567890ABCDEFG',
				],
				[],
				[],
				3000,
				[
					'login_error' => 1102,
					'error_string' => '<span style="color: red;">Fatal Error:</span> '
						. '<b>Login Failed - Login User ID is outside valid date range</b>',
					'error_string_text' => 'Fatal Error: '
						. 'Login Failed - Login User ID is outside valid date range'
				]
			],
			// TODO: Test that if we have n day check with login, that after login we can use parameter login again
			'login: ok -> failed -> ok:, _GET loginUserId, but must revalidate, normal login, _GET loginUserId' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'edit_access_uid' => 'AdminAccess',
					'edit_access_data' => 'test',
					'base_access' => 'list',
					'page_access' => 'list',
					'test_login_user_id_revalidate_reset' => true,
					'test_login_user_id' => true,
					'test_username' => 'admin',
					'loginUserId' => '1234567890ABCDEFG',
					// this error is thrown on first login round
					'login_error' => 1101,
					// get post as set sub arrays
					'get' => [
						'loginUserId' => '1234567890ABCDEFG',
					],
					'post' => [
						'login_login' => 'Login',
						'login_username' => 'admin',
						'login_password' => 'admin',
					],
				],
				// all empty get, post, session
				[],
				[],
				[],
				0,
				[
					'login_error' => 0,
					'admin_flag' => true,
					'check_access' => true,
					'check_access_id' => 1,
					'check_access_data' => 'value',
					'base_access' => true,
					'page_access' => true,
				]
			]
			//
			// other:
			// login check edit access id of ID not null and not in array
			// login OK, but during action user gets disabled/deleted/etc
		];

		return $tests;
	}

	/**
	 * main test for acl login
	 *
	 * @dataProvider loginProvider
	 * @testdox ACL\Login Class tests [$_dataName]
	 *
	 * @param  array<string,mixed>  $mock_settings
	 * @param  array<string,string> $get
	 * @param  array<string,string> $post
	 * @param  array<string,mixed>  $session
	 * @param  int                  $error
	 * @param  array<string,mixed>  $expected
	 * @return void
	 */
	public function testACLLoginFlow(
		array $mock_settings,
		array $get,
		array $post,
		array $session,
		int $error,
		array $expected
	): void {
		// reset session
		$_SESSION = [];
		// reset post & get
		$_GET = [];
		$_POST = [];
		// reset global ajax page call
		unset($GLOBALS['AJAX_PAGE']);
		// init session (as MOCK)
		/** @var \CoreLibs\Create\Session&MockObject */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			['startSession', 'checkActiveSession', 'sessionDestroy']
		);
		$session_mock->method('startSession')->willReturn('ACLLOGINTEST12');
		$session_mock->method('checkActiveSession')->willReturn(true);
		$session_mock->method('sessionDestroy')->will(
			$this->returnCallback(function () {
				global $_SESSION;
				$_SESSION = [];
				return true;
			})
		);

		// set _GET data
		foreach ($get as $get_var => $get_value) {
			$_GET[$get_var] = $get_value;
		}

		// set _POST data
		foreach ($post as $post_var => $post_value) {
			$_POST[$post_var] = $post_value;
		}

		// set _SESSION data
		foreach ($session as $session_var => $session_value) {
			$_SESSION[$session_var] = $session_value;
		}

		/** @var \CoreLibs\ACL\Login&MockObject */
		$login_mock = $this->getMockBuilder(\CoreLibs\ACL\Login::class)
			->setConstructorArgs([self::$db, self::$log, $session_mock, false])
			->onlyMethods(['loginTerminate', 'loginReadPageName', 'loginPrintLogin'])
			->getMock();
		$login_mock->expects($this->any())
			->method('loginTerminate')
			->will(
				$this->returnCallback(function ($code) {
					throw new \Exception('', $code);
				})
			);
		$login_mock->expects($this->any())
			->method('loginReadPageName')
			// set from mock settings, or empty if not set at all
			->willReturn($mock_settings['page_name'] ?? '');
		// do not echo out any string here
		$login_mock->expects($this->any())
			->method('loginPrintLogin')
			->willReturnCallback(function () {
			});

		// if mock_settings: enabled OFF
		// run DB update and set off
		if (!empty($mock_settings['test_enabled'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET enabled = 0 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (!empty($mock_settings['test_deleted'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET deleted = 1 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id_locked'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET login_user_id_locked = 1 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (
			!empty($mock_settings['test_locked_period_until']) ||
			!empty($mock_settings['test_locked_period_after'])
		) {
			$q_sub = '';
			if (!empty($mock_settings['test_locked_period_until'])) {
				if ($mock_settings['test_locked_period_until'] == 'on') {
					$q_sub .= "lock_until = NOW() + '1 day'::interval ";
				} elseif ($mock_settings['test_locked_period_until'] == 'off') {
					$q_sub .= "lock_until = NOW() - '1 day'::interval ";
				}
			}
			if (!empty($mock_settings['test_locked_period_after'])) {
				if (!empty($q_sub)) {
					$q_sub .= ", ";
				}
				if ($mock_settings['test_locked_period_after'] == 'on') {
					$q_sub .= "lock_after = NOW() - '1 day'::interval ";
				} elseif ($mock_settings['test_locked_period_after'] == 'off') {
					$q_sub .= "lock_after = NOW() + '1 day'::interval ";
				}
			}
			self::$db->dbExec(
				"UPDATE edit_user SET "
					. $q_sub
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		// test locked already
		if (!empty($mock_settings['test_locked'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET locked = 1 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		// test get locked
		if (!empty($mock_settings['test_get_locked'])) {
			// enable strict if needed
			if (!empty($mock_settings['test_locked_strict'])) {
				self::$db->dbExec(
					"UPDATE edit_user SET strict = 1 WHERE LOWER(username) = "
						. self::$db->dbEscapeLiteral($post['login_username'])
				);
			}
			// reset any previous login error counts
			self::$db->dbExec(
				"UPDATE edit_user "
					. "SET login_error_count = 0, login_error_date_last = NULL, "
					. "login_error_date_first = NULL "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
			// check the max login error count and try login until one time before
			// on each run, check that lock count is matching
			// next run (run test) should then fail with user locked IF strict,
			// else fail as normal login
			$login_mock->loginSetMaxLoginErrorCount($mock_settings['max_login_error_count']);
			// temporary wrong password
			$_POST['login_password'] = 'wrong';
			for ($run = 1, $max_run = $login_mock->loginGetMaxLoginErrorCount(); $run <= $max_run; $run++) {
				try {
					$login_mock->loginMainCall();
				} catch (\Exception $e) {
					// print 'Expected error code: ' . $e->getCode()
					// . ', M:' . $e->getMessage()
					// . ', L:' . $e->getLine()
					// . ', E: ' . $login_mock->loginGetLastErrorCode()
					// . "\n";
					$this->assertEquals(
						$expected['lock_run_login_error'],
						$login_mock->loginGetLastErrorCode(),
						'Assert login error code, exit on lock run'
					);
				}
				// check user error count
				$res = self::$db->dbReturnRow(
					"SELECT login_error_count FROM edit_user "
						. "WHERE LOWER(username) = "
						. self::$db->dbEscapeLiteral($post['login_username'])
				);
				$this->assertEquals(
					$res['login_error_count'],
					$run,
					'Assert equal login error count'
				);
			}
			// set correct password next locked login
			$_POST['login_password'] = $post['login_password'];
		}
		if (!empty($mock_settings['test_login_user_id'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET "
				. "login_user_id = "
				. self::$db->dbEscapeLiteral($mock_settings['loginUserId'])
				. " "
				. "WHERE LOWER(username) = "
				. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id_revalidate_after'])) {
			$q_sub = '';
			if ($mock_settings['test_login_user_id_revalidate_after'] == 'on') {
				$q_sub = "login_user_id_last_revalidate = NOW() - '1 day'::interval, "
					. "login_user_id_revalidate_after = '1 day'::interval ";
			} else {
				$q_sub = "login_user_id_last_revalidate = NOW(), "
					. "login_user_id_revalidate_after = '6 day'::interval ";
			}
			self::$db->dbExec(
				"UPDATE edit_user SET "
				. $q_sub
				. "WHERE LOWER(username) = "
				. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id_revalidate_reset'])) {
			// init dates data for revalidate frame,
			// set to last revalidate 3 days ago and set revalidate frame to
			// three days
			self::$db->dbExec(
				"UPDATE edit_user SET "
				. "login_user_id_last_revalidate = NOW() - '3 day'::interval, "
				. "login_user_id_revalidate_after = '3 day'::interval "
				. "WHERE LOWER(username) = "
				. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
			$_GET = $mock_settings['get'];
			// login with loginUserId -> fail
			try {
				$login_mock->loginMainCall();
			} catch (\Exception $e) {
				$this->assertEquals(
					$mock_settings['login_error'],
					$login_mock->loginGetLastErrorCode(),
					'loginUserId reset 1: Assert first loginUserId run failes'
				);
			}
			$_GET = [];
			// login with username and password -> reset -> ok
			// set _POST data
			$_POST = $mock_settings['post'];
			try {
				$login_mock->loginMainCall();
				$this->assertEquals(
					0,
					$login_mock->loginGetLastErrorCode(),
					'loginUserId reset 2: Assert username/password login is successful'
				);
			} catch (\Exception $e) {
				// if we end up here we have an issue
				$this->assertTrue(
					false,
					'loginUserId reset 2: FAILED successful login'
				);
			}
			$_POST = [];
			// logut and run normal login with loginUserId
			$_GET = $mock_settings['get'];
		}
		if (
			!empty($mock_settings['test_login_user_id_valid_from']) ||
			!empty($mock_settings['test_login_user_id_valid_until'])
		) {
			$q_sub = '';
			if (!empty($mock_settings['test_login_user_id_valid_from'])) {
				if ($mock_settings['test_login_user_id_valid_from'] == 'on') {
					$q_sub .= "login_user_id_valid_from = NOW() + '1 day'::interval ";
				} elseif ($mock_settings['test_login_user_id_valid_from'] == 'off') {
					$q_sub .= "login_user_id_valid_from = NOW() - '1 day'::interval ";
				}
			}
			if (!empty($mock_settings['test_login_user_id_valid_until'])) {
				if (!empty($q_sub)) {
					$q_sub .= ", ";
				}
				if ($mock_settings['test_login_user_id_valid_until'] == 'on') {
					$q_sub .= "login_user_id_valid_until = NOW() - '1 day'::interval ";
				} elseif ($mock_settings['test_login_user_id_valid_until'] == 'off') {
					$q_sub .= "login_user_id_valid_until = NOW() + '1 day'::interval ";
				}
			}
			self::$db->dbExec(
				"UPDATE edit_user SET "
					. $q_sub
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}

		// run test
		try {
			// if ajax call
			// check if parameter, or globals (old type)
			// else normal call
			if (
				!empty($mock_settings['ajax_test_type']) &&
				$mock_settings['ajax_test_type'] == 'parameter'
			) {
				$login_mock->loginMainCall($mock_settings['ajax_page']);
			} elseif (
				!empty($mock_settings['ajax_test_type']) &&
				$mock_settings['ajax_test_type'] == 'globals'
			) {
				$GLOBALS['AJAX_PAGE'] = $mock_settings['ajax_page'];
				$login_mock->loginMainCall();
			} else {
				$login_mock->loginMainCall();
			}
			// on ok, do post login check based on expected return
			// - loginGetLastErrorCode
			$this->assertEquals(
				$expected['login_error'],
				$login_mock->loginGetLastErrorCode(),
				'Assert login error code'
			);
			// - loginGetPageName
			$this->assertEquals(
				$mock_settings['page_name'],
				$login_mock->loginGetPageName(),
				'Assert page name'
			);
			// - loginCheckPermissions [duplicated from loginrun]
			$this->assertTrue(
				$login_mock->loginCheckPermissions(),
				'Assert true for login permission ok'
			);
			// - loginCheckAccess [use Base, Page below]
			$this->assertEquals(
				$expected['base_access'],
				$login_mock->loginCheckAccess('base', $mock_settings['base_access']),
				'Assert base access, via main method'
			);
			$this->assertEquals(
				$expected['base_access'],
				$login_mock->loginCheckAccess('page', $mock_settings['base_access']),
				'Assert page access, via main method'
			);
			// - loginCheckAccessBase
			$this->assertEquals(
				$expected['base_access'],
				$login_mock->loginCheckAccessBase($mock_settings['base_access']),
				'Assert base access'
			);
			// - loginCheckAccessPage
			$this->assertEquals(
				$expected['page_access'],
				$login_mock->loginCheckAccessPage($mock_settings['page_access']),
				'Assert page access'
			);
			// - loginCheckEditAccess
			$this->assertEquals(
				$expected['check_access'],
				$login_mock->loginCheckEditAccess($mock_settings['edit_access_id']),
				'Assert check access'
			);
			// - loginCheckEditAccessId
			$this->assertEquals(
				$expected['check_access_id'],
				$login_mock->loginCheckEditAccessId((int)$mock_settings['edit_access_id']),
				'Assert check access id valid'
			);
			// - loginGetEditAccessIdFromUid
			$this->assertEquals(
				$expected['check_access_id'],
				$login_mock->loginGetEditAccessIdFromUid($mock_settings['edit_access_uid']),
				'Assert check access uid to id valid'
			);
			// - loginGetEditAccessData
			$this->assertEquals(
				$expected['check_access_data'],
				$login_mock->loginGetEditAccessData(
					$mock_settings['edit_access_id'],
					$mock_settings['edit_access_data']
				),
				'Assert check access id data value valid'
			);
			// - loginIsAdmin
			$this->assertEquals(
				$expected['admin_flag'],
				$login_mock->loginIsAdmin(),
				'Assert admin flag set'
			);
			// - loginGetAcl
			$this->assertIsArray(
				$login_mock->loginGetAcl(),
				'Assert get acl is array'
			);
			// if loginUserId in _GET or _POST check that it is set
			if (!empty($get['loginUserId']) || !empty($post['loginUserId'])) {
				$this->assertNotEmpty(
					$login_mock->loginGetLoginUserId(),
					'Assert loginUserId is set'
				);
			}
			// TODO: detail match of ACL array (loginGetAcl)

			// .. end with: loginLogoutUser
			// _POST['login_logout'] = 'lgogout
			// $login_mock->loginMainCall();
			// - loginCheckPermissions
			// - loginGetPermissionOkay
		} catch (\Exception $e) {
			// print "[E]: " . $e->getCode() . ", ERROR: " . $login_mock->loginGetLastErrorCode() . "/"
			// 	. ($expected['login_error'] ?? 0) . "\n";
			// print "AJAX: " . $login_mock->loginGetAjaxFlag() . "\n";
			// print "AJAX GLOBAL: " . ($GLOBALS['AJAX_PAGE'] ?? '{f}') . "\n";
			// print "Login error expext: " . ($expected['login_error'] ?? '{0}') . "\n";
			// if this is 3000, then we do further error checks
			if (
				$e->getCode() == 3000 ||
				!empty($_POST['login_exit']) && $_POST['login_exit'] == 3000
			) {
				$this->assertEquals(
					$expected['login_error'],
					$login_mock->loginGetLastErrorCode(),
					'Assert login error code, exit'
				);
				// - loginGetErrorMsg
				$this->assertEquals(
					$expected['error_string'],
					$login_mock->loginGetErrorMsg($login_mock->loginGetLastErrorCode()),
					'Assert error string, html'
				);
				$this->assertEquals(
					$expected['error_string_text'],
					$login_mock->loginGetErrorMsg($login_mock->loginGetLastErrorCode(), true),
					'Assert error string, text'
				);
				// - loginGetLoginHTML
				$this->assertStringContainsString(
					'<html lang="',
					$login_mock->loginGetLoginHTML(),
					'Assert login html string exits'
				);
				// check that login script has above error message
				if (!empty($login_mock->loginGetLastErrorCode())) {
					$this->assertStringContainsString(
						$login_mock->loginGetErrorMsg($login_mock->loginGetLastErrorCode()),
						$login_mock->loginGetLoginHTML(),
						'Assert login error string exits'
					);
				} else {
					$this->assertStringNotContainsString(
						$login_mock->loginGetErrorMsg($login_mock->loginGetLastErrorCode()),
						$login_mock->loginGetLoginHTML(),
						'Assert login error string does not exit'
					);
				}
				// for ajax type, test post return values
				if ($login_mock->loginGetAjaxFlag()) {
					$this->assertCount(
						$expected['ajax_get_count'],
						$_GET,
						'Assert ajax error _GET is valid count'
					);
					// post has only action, login_exit, login_error,
					// login_error_text and login_html
					// 5 entries
					$this->assertCount(
						$expected['ajax_post_count'],
						$_POST,
						'Assert ajax error _POST is valid count'
					);
					// test post entries
					$this->assertEquals(
						$expected['ajax_post_action'],
						$_POST['action'],
						'Assert ajax _POST action'
					);
					$this->assertEquals(
						$login_mock->loginGetLastErrorCode(),
						$_POST['login_error'],
						'Assert ajax _POST error'
					);
					$this->assertEquals(
						$login_mock->loginGetErrorMsg($login_mock->loginGetLastErrorCode(), true),
						$_POST['login_error_text'],
						'Assert ajax _POST error text'
					);
					// html login basic check only, content is the same as when
					// read from loginGetLoginHTML()
					$this->assertStringContainsString(
						'<html lang="',
						$_POST['login_html'],
						'Assert ajax _POST html string exits'
					);
				}
			}
			// print "EXCEPTION: " . print_r($e, true) . "\n";
			$this->assertEquals(
				!$login_mock->loginGetAjaxFlag() ?
					$e->getCode() :
					$_POST['login_exit'] ?? 0,
				$error,
				'Expected error code: ' . $e->getCode()
					. ', ' . $e->getMessage()
					. ', ' . $e->getLine()
			);
		}

		// if _POST login set check this is matching
		if (!empty($post['login_login'])) {
			$this->assertTrue(
				$login_mock->loginActionRun(),
				'Assert that post login_login was pressed'
			);
		}

		// always check, even on error or not set
		if (!$login_mock->loginGetLoginUserIdUnclean()) {
			$this->assertEquals(
				$_GET['loginUserId'] ?? $_POST['loginUserId'] ?? '',
				$login_mock->loginGetLoginUserId(),
				'Assert loginUserId matches'
			);
		} else {
			$this->assertTrue(
				$login_mock->loginGetLoginUserIdUnclean(),
				'Assert loginUserId is unclear'
			);
			$this->assertNotEquals(
				$_GET['loginUserId'] ?? $_POST['loginUserId'] ?? '',
				$login_mock->loginGetLoginUserId(),
				'Assert loginUserId does not matche _GET/_POST'
			);
		}
		// check get/post login user id
		$this->assertEquals(
			(!empty($_GET['loginUserId']) ?
				'GET' :
				(!empty($_POST['loginUserId']) ?
					'POST' : '')
			),
			$login_mock->loginGetLoginUserIdSource(),
			'Assert loginUserId source matches'
		);

		// enable user again if flag set
		if (!empty($mock_settings['test_enabled'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET enabled = 1 "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (!empty($mock_settings['test_deleted'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET deleted = 0 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id_locked'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET login_user_id_locked = 0 WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (
			!empty($mock_settings['test_locked_period_until']) ||
			!empty($mock_settings['test_locked_period_after'])
		) {
			self::$db->dbExec(
				"UPDATE edit_user SET "
					. "lock_until = NULL, "
					. "lock_after = NULL "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		// reset lock flag
		if (!empty($mock_settings['test_locked'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET locked = 0 "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		// rest the get locked flow
		if (!empty($mock_settings['test_get_locked'])) {
			self::$db->dbExec(
				"UPDATE edit_user "
					. "SET login_error_count = 0, login_error_date_last = NULL, "
					. "login_error_date_first = NULL, locked = 0, strict = 0 "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($post['login_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET "
				. "login_user_id = NULL, "
				// below to rows are automatcially reset
				. "login_user_id_set_date = NULL, "
				. "login_user_id_last_revalidate = NULL "
				. "WHERE LOWER(username) = "
				. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}
		if (!empty($mock_settings['test_login_user_id_revalidate_after'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET "
				. "login_user_id_last_revalidate = NULL, "
				. "login_user_id_revalidate_after = NULL "
				. "WHERE LOWER(username) = "
				. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}
		if (
			!empty($mock_settings['test_login_user_id_valid_from']) ||
			!empty($mock_settings['test_login_user_id_valid_until'])
		) {
			self::$db->dbExec(
				"UPDATE edit_user SET "
					. "login_user_id_valid_from = NULL, "
					. "login_user_id_valid_until = NULL "
					. "WHERE LOWER(username) = "
					. self::$db->dbEscapeLiteral($mock_settings['test_username'])
			);
		}
	}

	// - loginGetAclList (null, invalid,)

	public function aclListProvider(): array
	{
		// 0: level (int|null)
		// 2: type (string), null for skip (if 0 = null)
		// 1: acl return from level (array)
		// 2: level number to return (must match 0)
		return [
			'null, get full list' => [
				null,
				null,
				[
					0 => [
						'type' => 'none',
						'name' => 'No Access',
					],
					10 => [
						'type' => 'list',
						'name' => 'List',
					],
					20 => [
						'type' => 'read',
						'name' => 'Read',
					],
					30 => [
						'type' => 'mod_trans',
						'name' => 'Translator',
					],
					40 => [
						'type' => 'mod',
						'name' => 'Modify',
					],
					60 => [
						'type' => 'write',
						'name' => 'Create/Write',
					],
					80 => [
						'type' => 'del',
						'name' => 'Delete',
					],
					90 => [
						'type' => 'siteadmin',
						'name' => 'Site Admin',
					],
					100 => [
						'type' => 'admin',
						'name' => 'Admin',
					],
				],
				null
			],
			'valid, search' => [
				20,
				'read',
				[
					'type' => 'read',
					'name' => 'Read'
				],
				20
			],
			'invalid search' => [
				12,
				'foo',
				[],
				false,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider aclListProvider()
	 * @testdox ACL\Login list if $level and $type exepcted level is $expected_level [$_dataName]
	 *
	 * @param  int|null      $level
	 * @param  string|null   $type
	 * @param  array         $expected_list
	 * @param  int|null|bool $expected_level
	 * @return void
	 */
	public function testAclLoginList(
		?int $level,
		?string $type,
		array $expected_list,
		$expected_level
	): void {
		$_SESSION = [];
		// init session (as MOCK)
		/** @var \CoreLibs\Create\Session&MockObject */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			['startSession', 'checkActiveSession', 'sessionDestroy']
		);
		$session_mock->method('startSession')->willReturn('ACLLOGINTEST34');
		$session_mock->method('checkActiveSession')->willReturn(true);
		$session_mock->method('sessionDestroy')->will(
			$this->returnCallback(function () {
				global $_SESSION;
				$_SESSION = [];
				return true;
			})
		);
		/** @var \CoreLibs\ACL\Login&MockObject */
		$login_mock = $this->getMockBuilder(\CoreLibs\ACL\Login::class)
			->setConstructorArgs([self::$db, self::$log, $session_mock, false])
			->onlyMethods(['loginTerminate'])
			->getMock();
		$login_mock->expects($this->any())
			->method('loginTerminate')
			->will(
				$this->returnCallback(function ($code) {
					throw new \Exception('', $code);
				})
			);

		$list = $login_mock->loginGetAclList($level);
		$this->assertIsArray(
			$list,
			'assert get acl list is array'
		);
		$this->assertEquals(
			$expected_list,
			$list
		);
		if ($type !== null) {
			$this->assertEquals(
				$expected_level,
				$login_mock->loginGetAclListFromType($type),
				'assert type is level'
			);
			// only back assert if found
			if (isset($list['type'])) {
				$this->assertEquals(
					$list['type'],
					$type,
					'assert level read type is type'
				);
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function minPasswordCheckProvider(): array
	{
		// 0: set length
		// 1: expected return from set
		// 2: expected set length
		return [
			'set new length' => [
				12,
				true,
				12,
			],
			'set new length, too short' => [
				5,
				false,
				9
			],
			'set new length, too long' => [
				500,
				false,
				9
			]
		];
	}

	/**
	 * check setting minimum password length
	 *
	 * @covers ::loginSetPasswordMinLength
	 * @covers ::loginGetPasswordLenght
	 * @dataProvider minPasswordCheckProvider()
	 * @testdox ACL\Login password min length set $input is $expected_return and matches $expected [$_dataName]
	 *
	 * @param  int  $input
	 * @param  bool $expected_return
	 * @param  int  $expected
	 * @return void
	 */
	public function testACLLoginPasswordMinLenght(int $input, bool $expected_return, int $expected): void
	{
		$_SESSION = [];
		// init session (as MOCK)
		/** @var \CoreLibs\Create\Session&MockObject */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			['startSession', 'checkActiveSession', 'sessionDestroy']
		);
		$session_mock->method('startSession')->willReturn('ACLLOGINTEST34');
		$session_mock->method('checkActiveSession')->willReturn(true);
		$session_mock->method('sessionDestroy')->will(
			$this->returnCallback(function () {
				global $_SESSION;
				$_SESSION = [];
				return true;
			})
		);
		/** @var \CoreLibs\ACL\Login&MockObject */
		$login_mock = $this->getMockBuilder(\CoreLibs\ACL\Login::class)
			->setConstructorArgs([self::$db, self::$log, $session_mock, false])
			->onlyMethods(['loginTerminate'])
			->getMock();
		$login_mock->expects($this->any())
			->method('loginTerminate')
			->will(
				$this->returnCallback(function ($code) {
					throw new \Exception('', $code);
				})
			);

		// set new min password length
		$this->assertEquals(
			$expected_return,
			$login_mock->loginSetPasswordMinLength($input),
			'assert bool set password min length'
		);
		// check value
		$this->assertEquals(
			$expected,
			$login_mock->loginGetPasswordLenght('min'),
			'assert get password min length'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getPasswordLengthProvider(): array
	{
		return [
			'min' => ['min'],
			'lower' => ['lower'],
			'max' => ['max'],
			'upper' => ['upper'],
			'minimum_length' => ['minimum_length'],
			'min_length' => ['min_length'],
			'length' => ['length'],
		];
	}

	/**
	 * check all possible readable password length params
	 *
	 * @covers ::loginGetPasswordLenght
	 * @dataProvider getPasswordLengthProvider()
	 * @testdox ACL\Login get password length $input [$_dataName]
	 *
	 * @param  string $input
	 * @return void
	 */
	public function testACLLoginGetPasswordLength(string $input): void
	{
		$_SESSION = [];
		// init session (as MOCK)
		/** @var \CoreLibs\Create\Session&MockObject */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			['startSession', 'checkActiveSession', 'sessionDestroy']
		);
		$session_mock->method('startSession')->willReturn('ACLLOGINTEST34');
		$session_mock->method('checkActiveSession')->willReturn(true);
		$session_mock->method('sessionDestroy')->will(
			$this->returnCallback(function () {
				global $_SESSION;
				$_SESSION = [];
				return true;
			})
		);
		/** @var \CoreLibs\ACL\Login&MockObject */
		$login_mock = $this->getMockBuilder(\CoreLibs\ACL\Login::class)
			->setConstructorArgs([self::$db, self::$log, $session_mock, false])
			->onlyMethods(['loginTerminate'])
			->getMock();
		$login_mock->expects($this->any())
			->method('loginTerminate')
			->will(
				$this->returnCallback(function ($code) {
					throw new \Exception('', $code);
				})
			);

		$this->assertMatchesRegularExpression(
			"/^\d+$/",
			(string)$login_mock->loginGetPasswordLenght($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function loginMaxErrorProvider(): array
	{
		return [
			'set valid max failed login' => [
				10,
				true,
				10
			],
			'set valid unlimted' => [
				-1,
				true,
				-1
			],
			'set invalid 0' => [
				0,
				false,
				-1
			],
			'set invalid negative' => [
				-5,
				false,
				-1
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::loginSetMaxLoginErrorCount
	 * @covers ::loginGetMaxLoginErrorCount
	 * @dataProvider loginMaxErrorProvider()
	 * @testdox ACL\Login failed login set/get $input is $expected_return and matches $expected [$_dataName]
	 *
	 * @param  int  $input
	 * @param  bool $expected_return
	 * @param  int  $expected
	 * @return void
	 */
	public function testACLLoginErrorCount(int $input, bool $expected_return, int $expected): void
	{
		$_SESSION = [];
		// init session (as MOCK)
		/** @var \CoreLibs\Create\Session&MockObject */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			['startSession', 'checkActiveSession', 'sessionDestroy']
		);
		$session_mock->method('startSession')->willReturn('ACLLOGINTEST34');
		$session_mock->method('checkActiveSession')->willReturn(true);
		$session_mock->method('sessionDestroy')->will(
			$this->returnCallback(function () {
				global $_SESSION;
				$_SESSION = [];
				return true;
			})
		);
		/** @var \CoreLibs\ACL\Login&MockObject */
		$login_mock = $this->getMockBuilder(\CoreLibs\ACL\Login::class)
			->setConstructorArgs([self::$db, self::$log, $session_mock, false])
			->onlyMethods(['loginTerminate'])
			->getMock();
		$login_mock->expects($this->any())
			->method('loginTerminate')
			->will(
				$this->returnCallback(function ($code) {
					throw new \Exception('', $code);
				})
			);

		// set new min password length
		$this->assertEquals(
			$expected_return,
			$login_mock->loginSetMaxLoginErrorCount($input),
			'assert bool set max login errors'
		);
		// check value
		$this->assertEquals(
			$expected,
			$login_mock->loginGetMaxLoginErrorCount(),
			'assert get max login errors'
		);
	}
}

// __END__
