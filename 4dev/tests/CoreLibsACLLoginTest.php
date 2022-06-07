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
	/** @var \CoreLibs\Create\Session&MockObject */
	private static $session;

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
		// 0: mock settings/override flag settings
		// 1: post array IN
		//    login_login, login_username, login_password, login_logout
		//    change_password, pw_username, pw_old_password, pw_new_password,
		//    pw_new_password_confirm
		// 2: override session set
		// 3: expected error code, 0 for all ok, 3000 for login page view
		//    note that 1000 (no db), 2000 (no session) must be tested too
		// 4: expected return array, eg login_error code, or other info data to match
		return [
			'load, no login' => [
				// error code, only for exceptions
				[
					'page_name' => 'edit_users.php',
				],
				[],
				[],
				3000,
				[
					'login_error' => 0,
					'error_string' => 'Success: <b>No error</b>',
					'error_string_text' => 'Success: No error',
				],
			],
			'load, session euid set only, php error' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			'login: all missing' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			'login: missing username' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			'login: missing password' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			'login: user not found' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			'login: invalid password' => [
				[
					'page_name' => 'edit_users.php',
				],
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
			// login: ok (but not enabled)
			'login: ok, but not enabled' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_enabled' => true
				],
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
			'login: ok, but locked' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_locked' => true
				],
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
			'login: ok, get locked, strict' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
					'base_access' => 'list',
					'page_access' => 'list',
					'test_get_locked' => true,
					'max_login_error_count' => 2,
					'test_locked_strict' => true,
				],
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
			//
			// other:
			// login check edit access id of ID not null and not in array
		];
	}

	/**
	 * main test for acl login
	 *
	 * @dataProvider loginProvider
	 * @testdox ACL\Login Class tests [$_dataName]
	 *
	 * @param  array<string,mixed>  $mock_settings
	 * @param  array<string,string> $post
	 * @param  array<string,mixed>  $session
	 * @param  int                  $error
	 * @param  array<string,mixed>  $expected
	 * @return void
	 */
	public function testACLLogin(
		array $mock_settings,
		array $post,
		array $session,
		int $error,
		array $expected
	): void {
		$_SESSION = [];
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

		// run test
		try {
			$login_mock->loginMainCall();
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
			// TODO: detail match of ACL array (loginGetAcl)

			// .. end with: loginLogoutUser
			// _POST['login_logout'] = 'lgogout
			// $login_mock->loginMainCall();
			// - loginCheckPermissions
			// - loginGetPermissionOkay
		} catch (\Exception $e) {
			// print "[E]: " . $e->getCode() . ", ERROR: " . $login_mock->loginGetLastErrorCode() . "/"
			// 	. ($expected['login_error'] ?? 0) . "\n";
			// if this is 3000, then we do further error checks
			if ($e->getCode() == 3000) {
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
					'<html>',
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
			}
			// print "EXCEPTION: " . print_r($e, true) . "\n";
			$this->assertEquals(
				$e->getCode(),
				$error,
				'Expected error code: ' . $e->getCode()
					. ', ' . $e->getMessage()
					. ', ' . $e->getLine()
			);
		}

		// enable user again if flag set
		if (!empty($mock_settings['test_enabled'])) {
			self::$db->dbExec(
				"UPDATE edit_user SET enabled = 1 "
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
			'invalud search' => [
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
	public function testACLLoginGetPasswordLenght(string $input): void
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
