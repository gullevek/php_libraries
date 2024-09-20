<?php // phpcs:disable Generic.Files.LineLength

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023-09-08
 * DESCRIPTION:
 * Error message return levels
*/

declare(strict_types=1);

namespace CoreLibs\Logging\Logger;

enum MessageLevel: int
{
	case ok = 100;
	case success = 110; // special for file uploads
	case info = 200;
	case notice = 250;
	case warn = 300;
	case error = 400;
	case abort = 500;
	case crash = 550;
	case unknown = 600;

	/**
	 * @param string $name any string name, if not matching use unkown
	 * @return static
	 */
	public static function fromName(string $name): self
	{
		return match (strtolower($name)) {
			'ok' => self::ok,
			'success' => self::success,
			'info' => self::info,
			'notice' => self::notice,
			'warn', 'warning' => self::warn,
			'error' => self::error,
			'abort' => self::abort,
			'crash' => self::crash,
			default => self::unknown,
		};
	}

	/**
	 * @param int $value
	 * @return static
	 */
	public static function fromValue(int $value): self
	{
		return self::tryFrom($value) ?? self::unknown;
	}

	/**
	 * Returns true if the passed $level is higher or equal to $this
	 *
	 * @param  MessageLevel $level
	 * @return bool
	 */
	public function includes(MessageLevel $level): bool
	{
		return $this->value <= $level->value;
	}

	/**
	 * If level is higher than set one
	 *
	 * @param  MessageLevel $level
	 * @return bool
	 */
	public function isHigherThan(MessageLevel $level): bool
	{
		return $this->value > $level->value;
	}

	/**
	 * if level is lower than set one
	 *
	 * @param  MessageLevel $level
	 * @return bool
	 */
	public function isLowerThan(MessageLevel $level): bool
	{
		return $this->value < $level->value;
	}
}

// __END__
