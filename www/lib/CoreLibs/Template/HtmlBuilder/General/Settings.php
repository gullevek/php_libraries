<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/7/22
 * DESCRIPTION:
 * General settings for html elements
*/

declare(strict_types=1);

namespace CoreLibs\Template\HtmlBuilder\General;

class Settings
{
	/** @var array<string> list of html elements that can have the name tag */
	public const NAME_ELEMENTS = [
		'button',
		'fieldset',
		'form',
		'iframe',
		'input',
		'map',
		'meta',
		'object',
		'output',
		'param',
		'select',
		'textarea',
	];

	/** @var array<string> options key entries to be skipped in build */
	public const SKIP_OPTIONS = [
		'id',
		'name',
		'class',
	];

	/** @var array<string> html elements that don't need to be closed */
	public const NO_CLOSE = [
		'input',
		'br',
		'img',
		'hr',
		'area',
		'col',
		'keygen',
		'wbr',
		'track',
		'source',
		'param',
		'command',
		// only in header
		'base',
		'meta',
		'link',
		'embed',
	];

	/** @var array<string> invalid tags, not allowed in body */
	public const NOT_IN_BODY_ALLOWED = [
		'base',
		'meta',
		'link',
		'embed', // not sure
	];
}

// __END__
