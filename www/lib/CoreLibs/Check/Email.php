<?php declare(strict_types=1);

namespace CoreLibs\Check;

class Email
{
	// this is for error check parts in where the email regex failed
	private static $email_regex_check = [
		0 => "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]{1,})*\.([a-zA-Z]{2,}){1}$", // MASTER
		1 => "@(.*)@(.*)", // double @
		2 => "^[A-Za-z0-9!#$%&'*+-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+-\/=?^_`{|}~\.]{0,63}@", // wrong part before @
		3 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.([a-zA-Z]{2,}){1}$", // wrong part after @
		4 => "@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*\.", // wrong domain name part
		5 => "\.([a-zA-Z]{2,6}){1}$", // wrong top level part
		6 => "@(.*)\.{2,}", // double .. in domain name part
		7 => "@.*\.$" // ends with a dot, top level, domain missing
	];
	// the array with the mobile types that are valid
	private static $mobile_email_type = [
		'.*@docomo\.ne\.jp$' => 'keitai_docomo',
		'.*@([a-z0-9]{2}\.)?ezweb\.ne\.jp$' => 'keitai_kddi_ezweb', # correct are a[2-4], b2, c[1-9], e[2-9], h[2-4], t[1-9]
		'.*@(ez[a-j]{1}\.)?ido\.ne\.jp$' => 'keitai_kddi_ido', # ez[a-j] or nothing
		'.*@([a-z]{2}\.)?sky\.tu-ka\.ne\.jp$' => 'keitai_kddi_tu-ka', # (sky group)
		'.*@([a-z]{2}\.)?sky\.tk[kc]{1}\.ne\.jp$' => 'keitai_kddi_sky', # (sky group) [tkk,tkc only]
		'.*@([a-z]{2}\.)?sky\.dtg\.ne\.jp$' => 'keitai_kddi_dtg', # dtg (sky group)
		'.*@[tkdhcrnsq]{1}\.vodafone\.ne\.jp$' => 'keitai_softbank_vodafone', # old vodafone [t,k,d,h,c,r,n,s,q]
		'.*@jp-[dhtkrsnqc]{1}\.ne\.jp$' => 'keitai_softbank_j-phone', # very old j-phone (pre vodafone) [d,h,t,k,r,s,n,q,c]
		'.*@([dhtcrknsq]{1}\.)?softbank\.ne\.jp$' => 'keitai_softbank', # add i for iphone also as keitai, others similar to the vodafone group
		'.*@i{1}\.softbank(\.ne)?\.jp$' => 'smartphone_softbank_iphone', # add iPhone also as keitai and not as pc
		'.*@disney\.ne\.jp$' => 'keitai_softbank_disney', # (kids)
		'.*@willcom\.ne\.jp$' => 'keitai_willcom',
		'.*@willcom\.com$' => 'keitai_willcom', # new for pdx.ne.jp address
		'.*@wcm\.ne\.jp$' => 'keitai_willcom', # old willcom wcm.ne.jp
		'.*@pdx\.ne\.jp$' => 'keitai_willcom_pdx', # old pdx address for willcom
		'.*@bandai\.jp$' => 'keitai_willcom_bandai', # willcom paipo! (kids)
		'.*@pipopa\.ne\.jp$' => 'keitai_willcom_pipopa', # willcom paipo! (kids)
		'.*@([a-z0-9]{2,4}\.)?pdx\.ne\.jp$' => 'keitai_willcom_pdx', # actually only di,dj,dk,wm -> all others are "wrong", but none also allowed?
		'.*@ymobile([1]{1})?\.ne\.jp$' => 'keitai_willcom_ymobile', # ymobile, ymobile1 techincally not willcom, but I group them there (softbank sub)
		'.*@y-mobile\.ne\.jp$' => 'keitai_willcom_ymobile', # y-mobile techincally not willcom, but I group them there (softbank sub)
		'.*@emnet\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
		'.*@emobile\.ne\.jp$' => 'keitai_willcom_emnet', # e-mobile, group will willcom
		'.*@emobile-s\.ne\.jp$' => 'keitai_willcom_emnet' # e-mobile, group will willcom
	];
	// short list for mobile email types
	private static $mobile_email_type_short = [
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
	 * Undocumented function
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
	 * get the full check array
	 * this will be deprected at some point
	 *
	 * @return array
	 */
	public static function getEmailRegexCheck(): array
	{
		return self::$email_regex_check;
	}

	/**
	 * guesses the email type (mostly for mobile) from the domain
	 * if second is set to true, it will return short naming scheme (only provider)
	 * @param  string $email email string
	 * @param  bool   $short default false, if true, returns only short type (pc instead of pc_html)
	 * @return string|bool   email type, eg "pc", "docomo", etc, false for invalid short type
	 */
	public static function getEmailType(string $email, bool $short = false)
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
	 * @param  string $email_type email string
	 * @return string|bool              short string or false for invalid
	 */
	public static function getShortEmailType(string $email_type)
	{
		// check if the short email type exists
		if (isset(self::$mobile_email_type_short[$email_type])) {
			return self::$mobile_email_type_short[$email_type];
		} else {
			// return false on not found
			return false;
		}
	}
}

// __END__
