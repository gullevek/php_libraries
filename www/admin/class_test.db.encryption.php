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
// for testing encryption compare
use OpenPGP\OpenPGP;
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
$key_new = CreateKey::generateRandomKey();
print "Secret Key NEW: " . $key_new . "<br>";
// for reproducable test results
$key = 'e475c19b9a3c8363feb06b51f5b73f1dc9b6f20757d4ab89509bf5cc70ed30ec';
print "Secret Key: " . $key . "<br>";

// test text
$text_string = "I a some deep secret";
$text_string = "I a some deep secret ABC";
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
print "INSERTED: " . print_r($cuuid, true) . "<br>";
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
		pg_crypt_bytea, pg_crypt_text,
		encode(pg_crypt_bytea, 'hex') AS pg_crypt_bytea_hex,
		pgp_sym_decrypt(pg_crypt_bytea, $2) AS from_pg_crypt_bytea,
		pgp_sym_decrypt(decode(pg_crypt_text, 'hex'), $2) AS from_pg_crypt_text
	FROM
		test_encryption
	WHERE
		cuuid = $1
	SQL,
	[
		$cuuid, $key
	]
);

print "RES: <pre>" . Support::prAr($res) . "</pre><br>";

if ($res === false) {
	echo "Failed to run query<br>";
} else {
	if (hash_equals($string_hashed, $res['pg_digest_text'])) {
		print "libsodium and pgcrypto hash match<br>";
	}
	if (hash_equals($string_hmac, $res['pg_hmac_text'])) {
		print "libsodium and pgcrypto hash hmac match<br>";
	}
	// do compare for PHP and pgcrypto settings
	$encryptedMessage_template = <<<TEXT
	-----BEGIN PGP MESSAGE-----

	{BASE64}
	-----END PGP MESSAGE-----
	TEXT;
	$base64_string = base64_encode(hex2bin($res['pg_crypt_text']) ?: '');
	$encryptedMessage = str_replace(
		'{BASE64}',
		$base64_string,
		$encryptedMessage_template
	);
	try {
		$literalMessage = OpenPGP::decryptMessage($encryptedMessage, passwords: [$key]);
		$decrypted = $literalMessage->getLiteralData()->getData();
		print "Pg decrypted PHP: " . $decrypted . "<br>";
		if ($decrypted == $text_string) {
			print "Decryption worked<br>";
		}
	} catch (\Exception $e) {
		print "Error decrypting message: " . $e->getMessage() . "<br>";
	}
}

print "</body></html>";

// __END__
