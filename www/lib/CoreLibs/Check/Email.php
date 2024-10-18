<?php

declare(strict_types=1);

namespace CoreLibs\Check;

class Email
{
	// this is for error check parts in where the email regex failed
	/** @var array<int,string> */
	private static array $email_regex_check = [
		0 => "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@"
			. "[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]{1,})*\.([a-zA-Z]{2,}){1}$", // MASTER
		1 => "@(.*)@(.*)", // double @
		2 => "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@", // wrong part before @
		3 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.([a-zA-Z]{2,}){1}$", // wrong part after @
		4 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.", // wrong domain name part
		5 => "\.([a-zA-Z]{2,6}){1}$", // wrong top level part
		6 => "@(.*)\.{2,}", // double .. in domain name part
		7 => "@.*\.$" // ends with a dot, top level, domain missing
	];
	// for above position, description string below
	/** @var array<int,string> */
	private static array $email_regex_check_message = [
		0 => 'Invalid email address',
		1 => 'Double @ mark in email address',
		2 => 'Invalid email part before @ sign',
		3 => 'Invalid domain part after @ sign',
		4 => 'Invalid domain name part',
		5 => 'Wrong domain top level part',
		6 => 'Double consecutive dots in domain name (..)',
		7 => 'Domain ends with a dot or is missing top level part'
	];
	// the array with the mobile types that are valid
	/** @var array<string,string> */
	private static array $mobile_email_type = [
		'.*@docomo\.ne\.jp$' => 'keitai_docomo',
		// correct are a[2-4], b2, c[1-9], e[2-9], h[2-4], t[1-9]
		'.*@([a-z0-9]{2}\.)?ezweb\.ne\.jp$' => 'keitai_kddi_ezweb',
		// ez[a-j] or nothing
		'.*@(ez[a-j]{1}\.)?ido\.ne\.jp$' => 'keitai_kddi_ido',
		// (sky group)
		'.*@([a-z]{2}\.)?sky\.tu-ka\.ne\.jp$' => 'keitai_kddi_tu-ka',
		// (sky group) [tkk,tkc only]
		'.*@([a-z]{2}\.)?sky\.tk[kc]{1}\.ne\.jp$' => 'keitai_kddi_sky',
		// dtg (sky group)
		'.*@([a-z]{2}\.)?sky\.dtg\.ne\.jp$' => 'keitai_kddi_dtg',
		// old vodafone [t,k,d,h,c,r,n,s,q]
		'.*@[tkdhcrnsq]{1}\.vodafone\.ne\.jp$' => 'keitai_softbank_vodafone',
		// very old j-phone (pre vodafone) [d,h,t,k,r,s,n,q,c]
		'.*@jp-[dhtkrsnqc]{1}\.ne\.jp$' => 'keitai_softbank_j-phone',
		// add i for iphone also as keitai, others similar to the vodafone group
		'.*@([dhtcrknsq]{1}\.)?softbank\.ne\.jp$' => 'keitai_softbank',
		// add iPhone also as keitai and not as pc
		'.*@i{1}\.softbank(\.ne)?\.jp$' => 'smartphone_softbank_iphone',
		'.*@disney\.ne\.jp$' => 'keitai_softbank_disney', // (kids)
		'.*@willcom\.ne\.jp$' => 'keitai_willcom',
		'.*@willcom\.com$' => 'keitai_willcom', // new for pdx.ne.jp address
		'.*@wcm\.ne\.jp$' => 'keitai_willcom', // old willcom wcm.ne.jp
		'.*@pdx\.ne\.jp$' => 'keitai_willcom_pdx', // old pdx address for willcom
		'.*@bandai\.jp$' => 'keitai_willcom_bandai', // willcom paipo! (kids)
		'.*@pipopa\.ne\.jp$' => 'keitai_willcom_pipopa', // willcom paipo! (kids)
		// actually only di,dj,dk,wm -> all others are "wrong", but none also allowed?
		'.*@([a-z0-9]{2,4}\.)?pdx\.ne\.jp$' => 'keitai_willcom_pdx',
		// ymobile, ymobile1 techincally not willcom, but I group them there (softbank sub)
		'.*@ymobile([1]{1})?\.ne\.jp$' => 'keitai_willcom_ymobile',
		// y-mobile techincally not willcom, but I group them there (softbank sub)
		'.*@y-mobile\.ne\.jp$' => 'keitai_willcom_ymobile',
		'.*@emnet\.ne\.jp$' => 'keitai_willcom_emnet', // e-mobile, group will willcom
		'.*@emobile\.ne\.jp$' => 'keitai_willcom_emnet', // e-mobile, group will willcom
		'.*@emobile-s\.ne\.jp$' => 'keitai_willcom_emnet' # e-mobile, group will willcom
	];
	// short list for mobile email types
	/** @var array<string,string> */
	private static array $mobile_email_type_short = [
		'keitai_docomo' => 'docomo',
		'keitai_kddi_ezweb' => 'kddi',
		'keitai_kddi' => 'kddi',
		'keitai_kddi_tu-ka' => 'kddi',
		'keitai_kddi_sky' => 'kddi',
		'keitai_softbank' => 'softbank',
		'smartphone_softbank_iphone' => 'iphone',
		'keitai_softbank_disney' => 'softbank',
		'keitai_softbank_vodafone' => 'softbank',
		'keitai_softbank_j-phone' => 'softbank',
		'keitai_willcom' => 'willcom',
		'keitai_willcom_pdx' => 'willcom',
		'keitai_willcom_bandai' => 'willcom',
		'keitai_willcom_pipopa' => 'willcom',
		'keitai_willcom_ymobile' => 'willcom',
		'keitai_willcom_emnet' => 'willcom',
		'pc_html' => 'pc',
		// old sets -> to be removed later
		'docomo' => 'docomo',
		'kddi_ezweb' => 'kddi',
		'kddi' => 'kddi',
		'kddi_tu-ka' => 'kddi',
		'kddi_sky' => 'kddi',
		'softbank' => 'softbank',
		'keitai_softbank_iphone' => 'iphone',
		'softbank_iphone' => 'iphone',
		'softbank_disney' => 'softbank',
		'softbank_vodafone' => 'softbank',
		'softbank_j-phone' => 'softbank',
		'willcom' => 'willcom',
		'willcom_pdx' => 'willcom',
		'willcom_bandai' => 'willcom',
		'willcom_pipopa' => 'willcom',
		'willcom_ymobile' => 'willcom',
		'willcom_emnet' => 'willcom',
		'pc' => 'pc'
	];

	/**
	 * get one position from the regex check list
	 *
	 * @param int     $type Which position in the regex list to get
	 *                      if not set or not valid get default pos 0
	 * @return string
	 */
	public static function getEmailRegex(int $type = 0): string
	{
		// make sure type is in valid range
		if ($type < 0 || $type >= count(self::$email_regex_check)) {
			$type = 0;
		}
		return self::$email_regex_check[$type];
	}

	/**
	 * get the full check array, except position 0, but preserve keys
	 * Currently used to add per error level type from
	 * getEmailRegex to error reporting
	 * Might be deprecated at some point
	 *
	 * @return array<mixed>
	 */
	public static function getEmailRegexCheck(): array
	{
		// return all but the first
		return array_slice(
			self::$email_regex_check,
			1,
			count(self::$email_regex_check) - 1,
			true
		);
	}

	/**
	 * Returns error message for email ergex error, or empty string if not set
	 *
	 * @param  int $error
	 * @return array<string,string|int> Error message and regex
	 */
	public static function getEmailRegexErrorMessage(int $error): array
	{
		// return error message and regex
		return [
			'error' => $error,
			'message' => self::$email_regex_check_message[$error] ?? '',
			'regex' => self::$email_regex_check[$error] ?? '',
		];
	}

	/**
	 * guesses the email type (mostly for mobile) from the domain
	 * if second is set to true, it will return short naming scheme (only provider)
	 *
	 * @param  string      $email email string
	 * @param  bool        $short default false, if true,
	 *                            returns only short type (pc instead of pc_html)
	 * @return string|false       email type, eg "pc", "docomo", etc,
	 *                            false for invalid short type
	 */
	public static function getEmailType(string $email, bool $short = false): string|false
	{
		// trip if there is no email address
		if (!$email) {
			return 'invalid';
		}
		// loop until we match a mobile type, return this first found type
		foreach (self::$mobile_email_type as $email_regex => $email_type) {
			if (preg_match("/$email_regex/", $email)) {
				if ($short) {
					return self::getShortEmailType($email_type);
				} else {
					return $email_type;
				}
			}
		}
		// if no previous return we assume this is a pc address
		if ($short) {
			return 'pc';
		} else {
			return 'pc_html';
		}
	}

	/**
	 * gets the short email type from a long email type
	 *
	 * @param  string $email_type email string
	 * @return string|false             short string or false for invalid
	 */
	public static function getShortEmailType(string $email_type): string|false
	{
		// check if the short email type exists
		if (isset(self::$mobile_email_type_short[$email_type])) {
			return self::$mobile_email_type_short[$email_type];
		} else {
			// return false on not found
			return false;
		}
	}

	/**
	 * simple email check with the basic email eregex
	 *
	 * @param  string $email Email address, will be checkd as lower
	 * @return bool          True if email is ok, or false if regex failed
	 */
	public static function checkEmail(string $email): bool
	{
		$email_regex = self::getEmailRegex();
		if (!preg_match("/$email_regex/", strtolower($email))) {
			return false;
		}
		return true;
	}

	/**
	 * check email with all regex checks possible and return errors as array
	 *
	 * @param  string $email           Email address, will be checkd as lower
	 * @param  bool   $error_code_only If this is set to true it will only return
	 *                                 the error pos, instead of detailed array
	 * @return array<mixed> Errors as array with message and regex
	 */
	public static function checkEmailFull(string $email, bool $error_code_only = false): array
	{
		$errors = [];
		foreach (self::$email_regex_check as $pos => $email_regex) {
			$match = preg_match("/$email_regex/", strtolower($email));
			// if the first does not fail, quit as ok
			if ($pos == 0 && $match) {
				break;
			}
			// else do error storage
			// not that for 1, 6, 7 the regex is matching
			if (
				(!$match && in_array($pos, [0, 2, 3, 4, 5])) ||
				($match && in_array($pos, [1, 6, 7]))
			) {
				if ($error_code_only === true) {
					$errors[] = $pos;
				} else {
					$errors[] = self::getEmailRegexErrorMessage($pos);
				}
			}
		}
		return $errors;
	}
}

// __END__
