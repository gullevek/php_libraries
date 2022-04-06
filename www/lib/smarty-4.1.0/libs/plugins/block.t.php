<?php

/**
 * smarty-gettext.php - Gettext support for smarty
 *
 * ------------------------------------------------------------------------- *
 * This library is free software; you can redistribute it and/or             *
 * modify it under the terms of the GNU Lesser General Public                *
 * License as published by the Free Software Foundation; either              *
 * version 2.1 of the License, or (at your option) any later version.        *
 *                                                                           *
 * This library is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         *
 * Lesser General Public License for more details.                           *
 *                                                                           *
 * You should have received a copy of the GNU Lesser General Public          *
 * License along with this library; if not, write to the Free Software       *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA *
 * ------------------------------------------------------------------------- *
 *
 * To register as a smarty block function named 't', use:
 *   $smarty->register_block('t', 'smarty_translate');
 *
 * @package	smarty-gettext
 * @version	$Id: block.t.php 4738 2022-05-06 01:28:48Z clemens $
 * @link	http://smarty-gettext.sf.net/
 * @author	Sagi Bashari <sagi@boom.org.il>
 * @copyright 2004 Sagi Bashari
 */

/**
 * Replaces arguments in a string with their values.
 * Arguments are represented by % followed by their number.
 *
 * @param string $str Source string
 * @param mixed mixed Arguments, can be passed in an array or through single variables.
 * @return string Modified string
 */
function smarty_gettext_strarg($str/*, $varargs... */)
{
	$tr = array();
	$p = 0;

	$nargs = func_num_args();
	for ($i = 1; $i < $nargs; $i++) {
		$arg = func_get_arg($i);

		if (is_array($arg)) {
			foreach ($arg as $aarg) {
				$tr['%' . ++$p] = $aarg;
			}
		} else {
			$tr['%' . ++$p] = $arg;
		}
	}

	return strtr($str, $tr);
}

/**
 * Smarty block function, provides gettext support for smarty.
 *
 * The block content is the text that should be translated.
 *
 * Any parameter that is sent to the function will be represented as %n in the translation text,
 * where n is 1 for the first parameter. The following parameters are reserved:
 *   - escape - sets escape mode:
 *       - 'html' for HTML escaping, this is the default.
 *       - 'js' for javascript escaping.
 *       - 'no'/'off'/0 - turns off escaping
 *   - plural - The plural version of the text (2nd parameter of ngettext())
 *   - count - The item count for plural mode (3rd parameter of ngettext())
 */

// cs modified: __ calls instead of direct gettext calls

function smarty_block_t($params, $text, $template, &$repeat)
{
	if (!isset($text)) {
		return $text;
	}

	// set escape mode, default html escape
	if (isset($params['escape'])) {
		$escape = $params['escape'];
		unset($params['escape']);
	} else {
		$escape = 'html';
	}

	// set plural parameters 'plural' and 'count'.
	if (isset($params['plural'])) {
		$plural = $params['plural'];
		unset($params['plural']);

		// set count
		if (isset($params['count'])) {
			$count = $params['count'];
			unset($params['count']);
		}
	}

	// use plural if required parameters are set
	if (isset($count) && isset($plural)) {
		$text = $template->l10n->__ngettext($text, $plural, $count);
	} else { // use normal
		$text = $template->l10n->__($text);
	}

	// run strarg if there are parameters
	if (count($params)) {
		$text = smarty_gettext_strarg($text, $params);
	}

	switch ($escape) {
		case 'html':
			$text = nl2br(htmlspecialchars($text));
			break;
		case 'javascript':
		case 'js':
			// javascript escape
			$text = strtr(
				$text,
				array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/')
			);
			break;
		case 'url':
			// url escape
			$text = urlencode($text);
			break;
	}

	return $text;
}

// __END__
