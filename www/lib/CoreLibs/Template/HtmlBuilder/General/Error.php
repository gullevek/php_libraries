<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/6/27
 * DESCRIPTION:
 * Error logging for the HtmlBuilder systs
*/

declare(strict_types=1);

namespace CoreLibs\Template\HtmlBuilder\General;

class Error
{
	/** @var array{level:string,id:string,message:string,context:array<mixed>} */
	private static array $messages = [];

	/**
	 * internal writer for messages
	 *
	 * @param  string $level
	 * @param  string $id
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	private static function writeContent(
		string $level,
		string $id,
		string $message,
		array $context
	): void {
		self::$messages[] = [
			'level' => $level,
			'id' => $id,
			'message' => $message,
			'context' => $context,
		];
	}

	/**
	 * warning collector for all internal string errors
	 * builds an warning with warning id, message text and array with optional content
	 *
	 * @param  string $id
	 * @param  string $message
	 * @param  array<mixed> $context
	 * @return void
	 */
	public static function setWarning(string $id, string $message, array $context = []): void
	{
		self::writeContent('Warning', $id, $message, $context);
	}

	/**
	 * error collector for all internal string errors
	 * builds an error with error id, message text and array with optional content
	 *
	 * @param  string $id
	 * @param  string $message
	 * @param  array<mixed> $context
	 * @return void
	 */
	public static function setError(string $id, string $message, array $context = []): void
	{
		self::writeContent('Error', $id, $message, $context);
	}

	/**
	 * Return all set errors
	 *
	 * @return array<mixed>
	 */
	public static function getMessages(): array
	{
		return self::$messages;
	}

	/**
	 * Reset all errors
	 *
	 * @return void
	 */
	public static function resetMessages(): void
	{
		self::$messages = [];
	}

	/**
	 * internal level in message array exists check
	 *
	 * @param  string $level
	 * @return bool
	 */
	private static function hasLevel(string $level): bool
	{
		return array_filter(
			self::$messages,
			function ($var) use ($level) {
				return ($var['level'] ?? '') == $level ? true : false;
			}
		) === [] ? false : true;
	}

	/**
	 * Check if any error is set
	 *
	 * @return bool
	 */
	public static function hasError(): bool
	{
		return self::hasLevel('Error');
	}

	/**
	 * Check if any warning is set
	 *
	 * @return bool
	 */
	public static function hasWarning(): bool
	{
		return self::hasLevel('Warning');
	}
}

// __END__
