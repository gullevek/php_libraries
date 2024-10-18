<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/5/29
 * DESCRIPTION:
 * Logging options flags for output file name building
 *
 * per_run: and timestamp + uid will be added
 * per_date: ymd will be added (per_run > per_date, cannot be used at the same time)
 * per_group: for debug level, group per group id (old level)
 * per_page: per file name logging
 * per_class: log per class
 * per_level: per logging level file split
*/

declare(strict_types=1);

namespace CoreLibs\Logging\Logger;

enum Flag: int
{
	/** all off flag */
	case all_off = 0;

	/** write per run */
	case per_run = 1;

	/** write per date */
	case per_date = 2;

	/** was PER_LEVEL, write per group id (debug) */
	case per_group = 4;

	/** write per page (filename) */
	case per_page = 8;

	/** write per class */
	case per_class = 16;

	/** write per log level name */
	case per_level = 32;

	/**
	 * get internal name from string value
	 *
	 * @param non-empty-string $name
	 * @return self
	 */
	public static function fromName(string $name): self
	{
		return match ($name) {
			'Run', 'run', 'per_run', 'PER_RUN' => self::per_run,
			'Date', 'date', 'per_date', 'PER_DATE' => self::per_date,
			'Group', 'group', 'per_group', 'PER_GROUP' => self::per_group,
			'Page', 'page', 'per_page', 'PER_PAGE' => self::per_page,
			'Class', 'class', 'per_class', 'PER_CLASS' => self::per_class,
			'Level', 'level', 'per_level', 'PER_LEVEL' => self::per_level,
			default => self::all_off,
		};
	}

	/**
	 * Get internal name from int value
	 *
	 * @param  int  $value
	 * @return self
	 */
	public static function fromValue(int $value): self
	{
		return self::from($value);
	}

	/**
	 * convert current set level to name (upper case)
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return strtoupper($this->name);
	}

	/** @var int[] */
	public const VALUES = [
		0,
		1,
		2,
		4,
		8,
		16,
		32,
	];

	/** @var string[] */
	public const NAMES = [
		'ALL_OFF',
		'PER_RUN',
		'PER_DATE',
		'PER_GROUP',
		'PER_PAGE',
		'PER_CLASS',
		'PER_LEVEL',
	];
}

// __END__
