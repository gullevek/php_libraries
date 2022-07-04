<?php

/*
 * Create email class
 */

declare(strict_types=1);

namespace CoreLibs\Create;

/**
 * sending simple text emails
 */
class Email
{
	/** @var array<string> allowed list for encodings that can do KV folding */
	private static $encoding_kv_allowed = [
		'UTF-8',
		'EUC-JP',
		'SJIS',
		'SJIS-win',
		'ISO-2022-JP',
		'ISO-2022-JP-MS',
		'JIS',
		'JIS-ms',
	];
	/** @var string, normaly this does not need to be changed */
	private static $mb_convert_kana_mode = 'KV';

	/**
	 * create mime encoded email part for to/from emails.
	 * If encoding is not UTF-8 it will convert the email name to target encoding
	 * FROM UTF-8
	 * Source data is ALWAYS seen as utf-8
	 *
	 * @param  string $email      E-Mail address
	 * @param  string $email_name Name for the email address, in UTF-8, if not set, empty
	 * @param  string $encoding   Encoding, if not set UTF-8
	 * @param  bool   $kv_fodling If set to true and a valid encoding, do KV folding
	 * @return string             Correctly encoded and build email string
	 */
	public static function encodeEmailName(
		string $email,
		string $email_name = '',
		string $encoding = 'UTF-8',
		bool $kv_folding = false
	): string {
		if (!empty($email_name)) {
			// if encoding is not UTF-8 then we convert
			if ($encoding != 'UTF-8') {
				$email_name = mb_convert_encoding($email_name, $encoding, 'UTF-8');
			}
			$email_name =
				mb_encode_mimeheader(
					in_array($encoding, self::$encoding_kv_allowed) && $kv_folding ?
						mb_convert_kana(
							$email_name,
							self::$mb_convert_kana_mode,
							$encoding
						) :
						$email_name,
					$encoding
				);
			return '"' . $email_name . '" '
				. '<' . (string)$email . '>';
		} else {
			return $email;
		}
	}

	/**
	 * Subject/Body replace sub function
	 *
	 * @param  string               $subject    Subject string, in UTF-8
	 * @param  string               $body       Body string, in UTF-8
	 * @param  array<string,string> $replace    Replace the array as key -> value, in UTF-8
	 * @param  string               $encoding   Encoding for subject encode mime header
	 * @param  bool                 $kv_fodling If set to true and a valid encoding,
	 *                                          do KV folding
	 * @return array<string>                    Pos 0: Subject, Pos 1: Body
	 */
	private static function replaceContent(
		string $subject,
		string $body,
		array $replace,
		string $encoding,
		bool $kv_folding
	): array {
		foreach (['subject', 'body'] as $element) {
			$$element = str_replace(
				array_map(
					function ($key) {
						return '{' . $key . '}';
					},
					array_keys($replace)
				),
				array_values($replace),
				$$element
			);
		}
		// if encoding is NOT UTF-8 convert to target
		if ($encoding != 'UTF-8') {
			$subject = mb_convert_encoding($subject, $encoding, 'UTF-8');
			$body = mb_convert_encoding($body, $encoding, 'UTF-8');
		}
		// we need to encodde the subject
		$subject = mb_encode_mimeheader(
			in_array($encoding, self::$encoding_kv_allowed) && $kv_folding ?
				// for any non UTF-8 encoding convert kana
				mb_convert_kana(
					$subject,
					self::$mb_convert_kana_mode,
					$encoding
				) :
				$subject,
			$encoding
		);
		return [$subject, $body];
	}

	/**
	 * Send plain text email with possible to replace subject/body data
	 * either global or per to email set.
	 * replace to tags are in {} in the subject or body
	 *
	 * @param  string               $subject         Mail subject, mandatory, in UTF-8
	 * @param  string               $body            Mail body, mandatory, in UTF-8
	 * @param  string               $from_email      From email, mandatory
	 * @param  string               $from_name       From email name, in UTF-8
	 *                                               if empty '' then not set
	 * @param  array<mixed>         $send_to_emails  to email or array for email/replace
	 *                                               If array: name/email/replace[key,value]
	 *                                               name and replace must be in UTF-8
	 *                                               At least one must be set
	 * @param  array<string,string> $replace_content Subject/Body replace as
	 *                                               search -> replace, in UTF-8
	 * @param  string               $encoding        E-Mail encoding, default UTF-8
	 * @param  bool                 $kv_folding      If set to true and a valid encoding,
	 *                                               do KV folding
	 * @param  bool                 $test            test flag, default off
	 * @param  \CoreLibs\Debug\Logging|null $log     Logging class,
	 *                                               only used if test flag is true
	 * @return int                  2 test only, no sent
	 *                              1 for ok,
	 *                              0 for send not ok
	 *                              -1 for nothing set (emails, subject, body)
	 *                              -2 for empty to list
	 *                              -3 encoding target not valid or not installed
	 */
	public static function sendEmail(
		string $subject,
		string $body,
		string $from_email,
		string $from_name,
		array $send_to_emails,
		array $replace_content = [],
		string $encoding = 'UTF-8',
		bool $kv_folding = false,
		bool $test = false,
		?\CoreLibs\Debug\Logging $log = null
	): int {
		/** @var array<string> */
		$to_emails = [];
		/** @var array<string,array<string,string>> */
		$to_replace = [];
		/** @var string */
		$out_subject = $subject;
		/** @var string */
		$out_body = $body;
		// check basic set
		if (empty($subject) || empty($body) || empty($from_email)) {
			return -1;
		}
		if (
			$encoding != 'UTF-8' &&
			!in_array($encoding, mb_list_encodings())
		) {
			return -3;
		}
		// if not one valid to, abort
		foreach ($send_to_emails as $to_email) {
			// to_email can be string, then only to email
			// else expect 'email' & 'name'
			if (
				is_array($to_email) &&
				isset($to_email['email'])
			) {
				$_to_email = self::encodeEmailName(
					$to_email['email'],
					$to_email['name'] ?? '',
					$encoding,
					$kv_folding
				);
				$to_emails[] = $_to_email;
				// if we have to replacement, this override replace content
				if (isset($to_email['replace']) && count($to_email['replace'])) {
					// merge with original replace content,
					// to data will override original data
					$to_replace[$_to_email] = array_merge(
						$replace_content,
						$to_email['replace']
					);
				}
			} elseif (is_string($to_email)) {
				$to_emails[] = $to_email;
			}
		}
		if (!count($to_emails)) {
			return -2;
		}

		// the  email headers needed
		$headers = [
			'From' => self::encodeEmailName($from_email, $from_name, $encoding),
			'Content-type' => "text/plain; charset=" . $encoding,
			'MIME-Version' => "1.0",
		];

		// if we have a replace string, we need to do replace run
		// only if there is no dedicated to replace
		// also run replace if there is nothing to replace at all
		// this will mime encode the subject
		if (!count($to_replace)) {
			list($out_subject, $out_body) = self::replaceContent(
				$subject,
				$body,
				$replace_content,
				$encoding,
				$kv_folding
			);
		}

		$mail_delivery_status = 1;
		// send the email
		foreach ($to_emails as $to_email) {
			// default mail status is success
			$mail_status = true;
			// if there is a to replace, if not use the original replace content
			if (count($to_replace)) {
				$_replace = [];
				if (!empty($to_replace[$to_email])) {
					$_replace = $to_replace[$to_email];
				} elseif (count($replace_content)) {
					$_replace = $replace_content;
				}
				if (count($_replace)) {
					list($out_subject, $out_body) = self::replaceContent(
						$subject,
						$body,
						$_replace,
						$encoding,
						$kv_folding
					);
				}
			}
			// if we are in test mode, do not send an email and set status to 2
			if ($test === false) {
				$mail_status = mail($to_email, $out_subject, $out_body, $headers);
			} else {
				$mail_delivery_status = 2;
			}
			// log if an log instance exists
			if ($log instanceof \CoreLibs\Debug\Logging) {
				// build debug strings: convert to UTF-8 if not utf-8
				$log->debug('SEND EMAIL', 'HEADERS: ' . $log->prAr($headers) . ', '
					. 'ENCODING: ' . $encoding . ',  '
					. 'KV FOLDING: ' . $log->prBl($kv_folding) . ',  '
					. 'TO: ' . $to_email . ', '
					. 'SUBJECT: ' . $out_subject . ', '
					. 'BODY: ' . ($encoding == 'UTF-8' ?
						$out_body :
						mb_convert_encoding($out_body, 'UTF-8', $encoding)));
				$log->debug('SEND EMAIL JSON', json_encode([
					'encoding' => $encoding,
					'kv_folding' => $kv_folding,
					'header' => $headers,
					'to' => $to_email,
					'subject' => $out_subject,
					'body' => ($encoding == 'UTF-8' ?
						$out_body :
						mb_convert_encoding($out_body, 'UTF-8', $encoding))
				]) ?: '{}');
			}
			if (!$mail_status) {
				$mail_delivery_status = 0;
			}
		}
		return $mail_delivery_status;
	}
}

// __END__
