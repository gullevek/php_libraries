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
		// 0: mock settings
		// 1: post array IN
		//    login_login, login_username, login_password, login_logout
		//    change_password, pw_username, pw_old_password, pw_new_password,
		//    pw_new_password_confirm
		// 2: override session set
		// 3: expected error code, 0 for all ok, 3 for login page view
		//    note that 1 (no db), 2 (no session) must be tested too
		// 4: expected return on ok (error: 0)
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
					'login_error' => 0
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
					'UNIT' => [
						1 => [
							'acl_level' => 80,
							'name' => 'Admin Access',
							'uid' => '',
							'level' => -1,
							'default' => 0,
							'data' => []
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
					'login_error' => 102
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
					'login_error' => 102
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
					'login_error' => 102
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
					'login_error' => 1010
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
					'login_error' => 1012
				]
			],
			// login: ok (but not enabled)
			// login: ok (but locked)
			// login: ok
			'login: ok' => [
				[
					'page_name' => 'edit_users.php',
					'edit_access_id' => 1,
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
	 * @param  array<string,string> $mock_settings
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
		// echo "ACL LOGIN TEST\n";
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
			// - loginCheckAccess [use Base, Page below]
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
			// - loginGetEditAccessData [test extra]
			// - loginIsAdmin
			$this->assertEquals(
				$expected['admin_flag'],
				$login_mock->loginIsAdmin(),
				'Assert admin flag set'
			);
			// .. end with: loginLogoutUser
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
		// print "PAGENAME: " . $login_mock->loginGetPageName() . "\n";
		// $echo_string = $this->getActualOutput();

		// $this->setOutputCallback(
		// 	function ($echo) {
		// 		// echo "A";
		// 		echo "--" . $echo . "--\n";
		// 	}
		// );

		// $echo_string = $this->getActualOutput();
		// echo "~~~~~~~~~~~~~~~~\n";
		// print "ECHO: " . $echo_string . "\n";

		// compare result to expected
	}

	// other tests
	// - loginSetPasswordMinLength
	//

	/**
	 * Undocumented function
	 *
	 * @testdox ACL\Login Class empty void
	 *
	 * @return void
	 */
	// public function testOther(): void
	// {
	// 	echo "HERE EMPTY 1\n";
	// }
}

// __END__
