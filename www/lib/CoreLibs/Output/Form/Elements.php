<?php

/*
 * elements for html output direct
 */

declare(strict_types=1);

namespace CoreLibs\Output\Form;

class Elements
{
	/**
	 * print the date/time drop downs, used in any queue/send/insert at date/time place
	 *
	 * @param  int    $year          year YYYY
	 * @param  int    $month         month m
	 * @param  int    $day           day d
	 * @param  int    $hour          hour H
	 * @param  int    $min           min i
	 * @param  string $suffix        additional info printed after the date time
	 *                               variable in the drop down
	 *                               also used for ID in the on change JS call
	 * @param  int    $min_steps     default is 1 (minute), can set to anything,
	 *                               is used as sum up from 0
	 * @param  bool   $name_pos_back default false, if set to true,
	 *                               the name will be printend after the drop down
	 *                               and not before the drop down
	 * @return string                HTML formated strings for drop down lists of date and time
	 */
	public static function printDateTime(
		$year,
		$month,
		$day,
		$hour,
		$min,
		string $suffix = '',
		int $min_steps = 1,
		bool $name_pos_back = false
	) {
		// if suffix given, add _ before
		if ($suffix) {
			$suffix = '_' . $suffix;
		}
		if ($min_steps < 1 || $min_steps > 59) {
			$min_steps = 1;
		}

		$on_change_call = 'dt_list(\'' . $suffix . '\');';

		// always be 1h ahead (for safety)
		$timestamp = time() + 3600; // in seconds

		// the max year is this year + 1;
		$max_year = (int)date("Y", $timestamp) + 1;

		// preset year, month, ...
		$year = !$year ? date('Y', $timestamp) : $year;
		$month = !$month ? date('m', $timestamp) : $month;
		$day = !$day ? date('d', $timestamp) : $day;
		$hour = !$hour ? date('H', $timestamp) : $hour;
		$min = !$min ? date('i', $timestamp) : $min; // add to five min?
		// max days in selected month
		$days_in_month = date(
			't',
			strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $min . ':0') ?: null
		);
		$string = '';
		// from now to ?
		if ($name_pos_back === false) {
			$string = 'Year ';
		}
		$string .= '<select id="year' . $suffix . '" name="year' . $suffix . '" onChange="' . $on_change_call . '">';
		for ($i = date("Y"); $i <= $max_year; $i++) {
			$string .= '<option value="' . $i . '" ' . ($year == $i ? 'selected' : '') . '>' . $i . '</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Year ';
		}
		if ($name_pos_back === false) {
			$string .= 'Month ';
		}
		$string .= '<select id="month' . $suffix . '" name="month' . $suffix . '" onChange="' . $on_change_call . '">';
		for ($i = 1; $i <= 12; $i++) {
			$string .= '<option value="' . ($i < 10 ? '0' . $i : $i) . '" '
				. ($month == $i ? 'selected' : '') . '>' . $i . '</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Month ';
		}
		if ($name_pos_back === false) {
			$string .= 'Day ';
		}
		$string .= '<select id="day' . $suffix . '" name="day' . $suffix . '" onChange="' . $on_change_call . '">';
		for ($i = 1; $i <= $days_in_month; $i++) {
			// set weekday text based on current month ($month) and year ($year)
			$string .= '<option value="' . ($i < 10 ? '0' . $i : $i) . '" '
				. ($day == $i ? 'selected' : '') . '>' . $i
				. ' (' . date('D', mktime(0, 0, 0, (int)$month, $i, (int)$year) ?: null) . ')</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Day ';
		}
		if ($name_pos_back === false) {
			$string .= 'Hour ';
		}
		$string .= '<select id="hour' . $suffix . '" name="hour' . $suffix . '" onChange="' . $on_change_call . '">';
		for ($i = 0; $i <= 23; $i += $min_steps) {
			$string .= '<option value="' . ($i < 10 ? '0' . $i : $i)
				. '" ' . ($hour == $i ? 'selected' : '') . '>' . $i . '</option>';
		}
		$string .= '</select> ';
		if ($name_pos_back === true) {
			$string .= 'Hour ';
		}
		if ($name_pos_back === false) {
			$string .= 'Minute ';
		}
		$string .= '<select id="min' . $suffix . '" name="min'
			. $suffix . '" onChange="' . $on_change_call . '">';
		for ($i = 0; $i <= 59; $i++) {
			$string .= '<option value="' . ($i < 10 ? '0' . $i : $i)
				. '" ' . ($min == $i ? 'selected' : '') . '>' . $i . '</option>';
		}
		$string .= '</select>';
		if ($name_pos_back === true) {
			$string .= ' Minute ';
		}
		// return the datetime select string
		return $string;
	}

	/**
	 * tries to find mailto:user@bubu.at and changes it into ->
	 * <a href="mailto:user@bubu.at">E-Mail senden</a>
	 * or tries to take any url (http, ftp, etc) and transform it into a valid URL
	 * the string is in the format: some url|name#css|, same for email
	 *
	 * @param  string $string data to transform to a valid HTML url
	 * @param  string $target target string, default _blank
	 * @return string         correctly formed html url link
	 */
	public static function magicLinks(string $string, string $target = "_blank"): string
	{
		$output = $string;
		$protList = ["http", "https", "ftp", "news", "nntp"];

		// find urls w/o  protocol
		$output = preg_replace("/([^\/])www\.([\w\.-]+)\.([a-zA-Z]{2,4})/", "\\1http://www.\\2.\\3", $output) ?: '';
		$output = preg_replace("/([^\/])ftp\.([\w\.-]+)\.([a-zA-Z]{2,4})/", "\\1ftp://ftp.\\2.\\3", $output) ?: '';

		// remove doubles, generate protocol-regex
		// DIRTY HACK
		$protRegex = "";
		foreach ($protList as $protocol) {
			if ($protRegex)	{
				$protRegex .= "|";
			}
			$protRegex .= "$protocol:\/\/";
		}

		// find urls w/ protocol
		// cs: escaped -, added / for http urls
		// added | |, this time mandatory, todo: if no | |use \\1\\2
		// backslash at the end of a url also allowed now
		// do not touch <.*=".."> things!
		// _1: URL or email
		// _2: atag (>)
		// _3: (_1) part of url or email [main url or email pre @ part]
		// _4: (_2) parameters of url or email post @ part
		// _5: (_3) parameters of url or tld part of email
		// _7: link name/email link name
		// _9: style sheet class
		$output = preg_replace_callback(
			"/(href=\")?(\>)?\b($protRegex)([\w\.\-?&=+%#~,;\/]+)\b([\.\-?&=+%#~,;\/]*)(\|([^\||^#]+)(#([^\|]+))?\|)?/",
			function ($matches) {
				return self::createUrl(
					$matches[1] ?? '',
					$matches[2] ?? '',
					$matches[3] ?? '',
					$matches[4] ?? '',
					$matches[5] ?? '',
					$matches[7] ?? '',
					$matches[9] ?? ''
				);
			},
			$output
		) ?: '';
		// find email-addresses, but not mailto prefix ones
		$output = preg_replace_callback(
			"/(mailto:)?(\>)?\b([\w\.-]+)@([\w\.\-]+)\.([a-zA-Z]{2,4})\b(\|([^\||^#]+)(#([^\|]+))?\|)?/",
			function ($matches) {
				return self::createEmail(
					$matches[1] ?? '',
					$matches[2] ?? '',
					$matches[3] ?? '',
					$matches[4] ?? '',
					$matches[5] ?? '',
					$matches[7] ?? '',
					$matches[9] ?? ''
				);
			},
			$output
		) ?: '';

		// we have one slashes after the Protocol ->
		// internal link no domain, strip out the proto
		// $output = preg_replace("/($protRegex)\/(.*)/e", "\\2", $ouput);

		// post processing
		$output = str_replace("{TARGET}", $target, $output);
		$output = str_replace("##LT##", "<", $output);
		$output = str_replace("##GT##", ">", $output);
		$output = str_replace("##QUOT##", "\"", $output);

		return $output;
	}

	/**
	 * internal function, called by the magic url create functions.
	 * checks if title $_4 exists, if not, set url as title
	 *
	 * @param  string $href  url link
	 * @param  string $atag  anchor tag (define both type or url)
	 * @param  string $_1    part of the URL, if atag is set, _1 is not used
	 * @param  string $_2    part of the URL
	 * @param  string $_3    part of the URL
	 * @param  string $name  name for the url, if not given _2 + _3 is used
	 * @param  string $class style sheet
	 * @return string        correct string for url href process
	 */
	private static function createUrl($href, $atag, $_1, $_2, $_3, $name, $class): string
	{
		// $this->debug('URL', "1: $_1 - 2: $_2 - $_3 - atag: $atag - name: $name - class: $class");
		// if $_1 ends with //, then we strip $_1 complete & target is also blanked (its an internal link)
		if (preg_match("/\/\/$/", $_1) && preg_match("/^\//", $_2)) {
			$_1 = '';
			$target = '';
		} else {
			$target = '{TARGET}';
		}
		// if it is a link already just return the original link do not touch anything
		if (!$href && !$atag) {
			return "##LT##a href=##QUOT##" . $_1 . $_2 . $_3 . "##QUOT##"
				. ($class ? ' class=##QUOT##' . $class . '##QUOT##' : '')
				. ($target ? " target=##QUOT##" . $target . "##QUOT##" : '')
				. "##GT##" . ($name ? $name : $_2 . $_3) . "##LT##/a##GT##";
		} elseif ($href && !$atag) {
			return "href=##QUOT##$_1$_2$_3##QUOT##";
		} elseif ($atag) {
			return $atag . $_2 . $_3;
		} else {
			return $href;
		}
	}

	/**
	 * internal function for createing email, returns data to magic_url method
	 *
	 * @param  string $mailto email address
	 * @param  string $atag   atag (define type of url)
	 * @param  string $_1     parts of the email _1 before @, 3_ tld
	 * @param  string $_2     _2 domain part after @
	 * @param  string $_3     _3 tld
	 * @param  string $title  name for the link, if not given use email
	 * @param  string $class  style sheet
	 * @return string         created html email a href string
	 */
	private static function createEmail($mailto, $atag, $_1, $_2, $_3, $title, $class)
	{
		$email = $_1 . "@" . $_2 . "." . $_3;
		if (!$mailto && !$atag) {
			return "##LT##a href=##QUOT##mailto:" . $email . "##QUOT##"
				. ($class ? ' class=##QUOT##' . $class . '##QUOT##' : '')
				. "##GT##" . ($title ? $title : $email) . "##LT##/a##GT##";
		} elseif ($mailto && !$atag) {
			return "mailto:" . $email;
		} elseif ($atag) {
			return $atag . $email;
		} else {
			// else just return email as is
			return $email;
		}
	}
}

// __END__
