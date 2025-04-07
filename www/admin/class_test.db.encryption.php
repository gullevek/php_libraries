<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// turn on all error reporting
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-db-query-encryption';
ob_end_flush();

// use CoreLibs\Debug\Support;
use CoreLibs\Security\SymmetricEncryption;
use CoreLibs\Security\CreateKey;
use CoreLibs\Create\Hash;
use CoreLibs\Debug\Support;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB QUERY ENCRYPTION';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// encryption key
$key = CreateKey::generateRandomKey();
print "Secret Key: " . $key . "<br>";

// test text
$text_string = "I a some deep secret";
//
$crypt = new SymmetricEncryption($key);
$encrypted = $crypt->encrypt($text_string);
$string_hashed = Hash::hashStd($text_string);
$string_hmac = Hash::hashHmac($text_string, $key);
$decrypted = $crypt->decrypt($encrypted);

print "String: " . $text_string . "<br>";
print "Encrypted: " . $encrypted . "<br>";
print "Hashed: " . $string_hashed . "<br>";
print "Hmac: " . $string_hmac . "<br>";

$db->dbExecParams(
	<<<SQL
	INSERT INTO test_encryption (
		-- for compare
		plain_text,
		-- via php encryption
		hash_text, hmac_text, crypt_text,
		-- -- in DB encryption
		pg_digest_bytea, pg_digest_text,
		pg_hmac_bytea, pg_hmac_text,
		pg_crypt_bytea, pg_crypt_text
	) VALUES (
		$1,
		$2, $3, $4,
		digest($1::VARCHAR, $5),
		encode(digest($1, $5), 'hex'),
		hmac($1, $6, $5),
		encode(hmac($1, $6, $5), 'hex'),
		pgp_sym_encrypt($1, $7),
		encode(pgp_sym_encrypt($1, $7), 'hex')
	) RETURNING cuuid
	SQL,
	[
		// 1: original string
		$text_string,
		// 2: hashed, 3: hmac, 4: encrypted
		$string_hashed, $string_hmac, $encrypted,
		// 5: hash type, 6: hmac secret, 7: pgp secret
		'sha256', $key, $key
	]
);
$cuuid = $db->dbGetReturningExt('cuuid');
print "INSERTED: $cuuid<br>";
print "LAST ERROR: " . $db->dbGetLastError(true) . "<br>";

// read back
$res = $db->dbReturnRowParams(
	<<<SQL
	SELECT
		-- for compare
		plain_text,
		-- via php encryption
		hash_text, hmac_text, crypt_text,
		-- in DB encryption
		pg_digest_bytea, pg_digest_text,
		pg_hmac_bytea, pg_hmac_text,
		pg_crypt_bytea, pg_crypt_text
	FROM
		test_encryption
	WHERE
		cuuid = $1
	SQL,
	[
		$cuuid
	]
);

print "RES: <pre>" . Support::prAr($res) . "</pre><br>";

// do compare

print "</body></html>";

// __END__
