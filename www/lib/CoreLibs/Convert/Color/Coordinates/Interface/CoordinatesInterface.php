<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: Ymd
 * DESCRIPTION:
 * DescriptionHere
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates\Interface;

interface CoordinatesInterface
{
	/**
	 * create class via "Class::create()" call
	 * was used for multiple create interfaces
	 * no longer needed, use "new Class()" instead
	 *
	 * @param  string|array{0:float,1:float,2:float} $colors
	 * @param  string $colorspace [default='']
	 * @param  array<string,string|bool|int> $options [default=[]]
	 * @return self
	 */
	public static function create(string|array $colors, string $colorspace = '', array $options = []): self;

	/**
	 * get color
	 *
	 * @param string $name
	 * @return float
	 */
	public function get(string $name): float|string|bool;

	/**
	 * Returns the color as array
	 * where 0: Lightness, 1: a, 2: b
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array;

	/**
	 * Convert into css string with optional opacity
	 *
	 * @param  null|float|string|null $opacity
	 * @return string
	 */
	public function toCssString(null|float|string $opacity = null): string;
}

// __END__
