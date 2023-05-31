<?php

/*
 * dump memory usage
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

use CoreLibs\Convert\Byte;

class MemoryUsage
{
	/** @var int */
	private static int $start_memory = 0;
	/** @var int */
	private static int $set_memory = 0;
	/** @var int */
	private static int $previous_memory = 0;
	/** @var bool */
	private static bool $debug_memory = false;

	/**
	 * set memory flag, or return set memory flag
	 *
	 * @param  bool|null $set_debug
	 * @return bool
	 */
	public static function debugMemoryFlag(?bool $set_debug = null): bool
	{
		if ($set_debug === null) {
			return self::$debug_memory;
		}
		self::$debug_memory = $set_debug;
		return self::$debug_memory;
	}

	/**
	 * Reset all memory variables to 0
	 *
	 * @return void
	 */
	public static function resetMemory(): void
	{
		self::$start_memory = 0;
		self::$set_memory = 0;
		self::$previous_memory = 0;
	}

	/**
	 * set the start memory velue, or reset to a new start value
	 *
	 * @return void
	 */
	public static function setStartMemory(): void
	{
		self::$start_memory = memory_get_usage();
	}

	/**
	 * set the and independent memory set for a sub tracking outside main
	 *
	 * @return void
	 */
	public static function setMemory(): void
	{
		self::$set_memory = memory_get_usage();
	}

	/**
	 * calculate and set memory usage values
	 * this will return an array with all the data that can be used in
	 * printMemoryUsage for human readable output
	 *
	 * @param  string $prefix           A prefix tag
	 * @return array<string,int|string> return array
	 */
	public static function memoryUsage(string $prefix): array
	{
		// skip if DEBUG is off
		if (self::$debug_memory === false) {
			return [];
		}
		if (empty(self::$start_memory)) {
			self::$start_memory = memory_get_usage();
		}
		$memory_usage = memory_get_usage();
		$data = [
			'prefix' => $prefix,
			'peak' => memory_get_peak_usage(),
			'usage' => $memory_usage,
			'start' => $memory_usage - self::$start_memory,
			'last' => $memory_usage - self::$previous_memory,
			'set' => $memory_usage - self::$set_memory
		];
		self::$previous_memory = $memory_usage;
		return $data;
	}

	/**
	 * returns a human readable output from the memoryUsage function
	 * can be used for logging purpose
	 *
	 * @param  array<string,int|string> $data Data array from memoryUsage
	 * @param  bool                     $raw  Flag to shaw unconverted memory numbers
	 * @return string                         Return debug string with memory usage
	 */
	public static function printMemoryUsage(array $data, bool $raw = false): string
	{
		return
			'[' . $data['prefix'] . '] Peak/Curr/Change: '
			. Byte::humanReadableByteFormat($data['peak'])
			. '/'
			. Byte::humanReadableByteFormat($data['usage'])
			// . ($raw === true ? ' [' . $data['usage'] . ']' : '')
			. '/Since Start: '
			. Byte::humanReadableByteFormat($data['start'])
			. ($raw === true ? ' [' . $data['start'] . ']' : '')
			. ' | Since Last: '
			. Byte::humanReadableByteFormat($data['last'])
			. ($raw === true ? ' [' . $data['last'] . ']' : '')
			. ' | Since Set: '
			. Byte::humanReadableByteFormat($data['set'])
			. ($raw === true ? ' [' . $data['set'] . ']' : '');
	}
}

// __END__
