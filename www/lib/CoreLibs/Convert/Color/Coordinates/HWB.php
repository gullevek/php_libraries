<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HWB
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

use CoreLibs\Convert\Color\Stringify;

class HWB
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['sRGB'];

	/** @var float Hue */
	private float $H = 0.0;
	/** @var float Whiteness */
	private float $W = 0.0;
	/** @var float Blackness */
	private float $B = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	/**
	 * Color Coordinate: HWB
	 * Hue/Whiteness/Blackness
	 */
	public function __construct()
	{
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Whiteness, 2: Blackness
	 *
	 * @param  array{0:float,1:float,2:float} $colors
	 * @param  string $colorspace [default=sRGB]
	 * @return self
	 */
	public static function __constructFromArray(array $colors, string $colorspace = 'sRGB'): self
	{
		return (new HWB())->setColorspace($colorspace)->setFromArray($colors);
	}

	/**
	 * set color
	 *
	 * @param  string $name
	 * @param  float  $value
	 * @return void
	 */
	public function __set(string $name, float $value): void
	{
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		switch ($name) {
			case 'H':
				if ($value == 360) {
					$value = 0;
				}
				if ($value < 0 || $value > 360) {
					throw new \LengthException(
						'Argument value ' . $value . ' for hue is not in the range of 0 to 360',
						1
					);
				}
				break;
			case 'W':
				if ($value < 0 || $value > 100) {
					throw new \LengthException(
						'Argument value ' . $value . ' for saturation is not in the range of 0 to 100',
						2
					);
				}
				break;
			case 'B':
				if ($value < 0 || $value > 100) {
					throw new \LengthException(
						'Argument value ' . $value . ' for luminance is not in the range of 0 to 100',
						3
					);
				}
				break;
		}
		$this->$name = $value;
	}

	/**
	 * get color
	 *
	 * @param string $name
	 * @return float
	 */
	public function __get(string $name): float
	{
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		return $this->$name;
	}

	/**
	 * set the colorspace
	 *
	 * @param  string $colorspace
	 * @return self
	 */
	private function setColorspace(string $colorspace): self
	{
		if (!in_array($colorspace, $this::COLORSPACES)) {
			throw new \InvalidArgumentException('Not allowed colorspace', 0);
		}
		$this->colorspace = $colorspace;
		return $this;
	}

	/**
	 * Returns the color as array
	 * where 0: Hue, 1: Whiteness, 2: Blackness
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->H, $this->W, $this->B];
	}

	/**
	 * set color as array
	 * where 0: Hue, 1: Whiteness, 2: Blackness
	 *
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	public function setFromArray(array $colors): self
	{
		$this->__set('H', $colors[0]);
		$this->__set('W', $colors[1]);
		$this->__set('B', $colors[2]);
		return $this;
	}

	/**
	 * convert to css string with optional opacityt
	 *
	 * @param  float|string|null $opacity
	 * @return string
	 */
	public function toCssString(null|float|string $opacity = null): string
	{
		$string = 'hwb('
			. $this->H
			. ' '
			. $this->W
			. ' '
			. $this->B
			. Stringify::setOpacity($opacity)
			. ')';
		return $string;
	}
}

// __END__
