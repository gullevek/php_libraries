<?php

declare(strict_types=1);

namespace CoreLibs\Get;

/**
 * @deprecated use \CoreLibs\Get\DotEnv instead
 */
class ReadEnvFile
{
	/** @var string constant comment char, set to # */
	private const COMMENT_CHAR = '#';

	/**
	 * parses .env file
	 *
	 * Rules for .env file
	 * variable is any alphanumeric string followed by = on the same line
	 * content starts with the first non space part
	 * strings can be contained in "
	 * strings MUST be contained in " if they are multiline
	 * if string starts with " it will match until another " is found
	 * anything AFTER " is ignored
	 * if there are two variables with the same name only the first is used
	 * variables are case sensitive
	 *
	 * @param  string $path     Folder to file, default is __DIR__
	 * @param  string $env_file What file to load, default is .env
	 * @return int              -1 other error
	 *                          0 for success full load
	 *                          1 for file loadable, but no data inside
	 *                          2 for file not readable or open failed
	 *                          3 for file not found
	 * @deprecated Use \CoreLibs\Get\DotEnv::readEnvFile() instead
	 */
	public static function readEnvFile(
		string $path = __DIR__,
		string $env_file = '.env'
	): int {
		return \CoreLibs\Get\DotEnv::readEnvFile($path, $env_file);
	}
}

// __END__
