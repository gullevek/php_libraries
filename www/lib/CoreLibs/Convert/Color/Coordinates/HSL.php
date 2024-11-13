<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HSL
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

use CoreLibs\Convert\Color\Stringify;

class HSL
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['sRGB'];

	/** @var float hue */
	private float $H = 0.0;
	/** @var float saturation */
	private float $S = 0.0;
	/** @var float lightness (luminance) */
	private float $L = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	/**
	 * Color Coordinate HSL
	 * Hue/Saturation/Lightness
	 */
	public function __construct()
	{
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Saturation, 2: Lightness
	 *
	 * @param  array{0:float,1:float,2:float} $colors
	 * @param  string $colorspace [default=sRGB]
	 * @return self
	 */
	public static function __constructFromArray(array $colors, string $colorspace = 'sRGB'): self
	{
		return (new HSL())->setColorspace($colorspace)->setFromArray($colors);
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
				if ((int)$value == 360) {
					$value = 0;
				}
				if ((int)$value < 0 || (int)$value > 359) {
					throw new \LengthException(
						'Argument value ' . $value . ' for hue is not in the range of 0 to 359',
						1
					);
				}
				break;
			case 'S':
				if ((int)$value < 0 || (int)$value > 100) {
					throw new \LengthException(
						'Argument value ' . $value . ' for saturation is not in the range of 0 to 100',
						2
					);
				}
				break;
			case 'L':
				if ((int)$value < 0 || (int)$value > 100) {
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
	 * where 0: Hue, 1: Saturation, 2: Lightness
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->H, $this->S, $this->L];
	}

	/**
	 * set color as array
	 * where 0: Hue, 1: Saturation, 2: Lightness
	 *
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	public function setFromArray(array $colors): self
	{
		$this->__set('H', $colors[0]);
		$this->__set('S', $colors[1]);
		$this->__set('L', $colors[2]);
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
		$string = 'hsl('
			. $this->H
			. ' '
			. $this->S
			. ' '
			. $this->L
			. Stringify::setOpacity($opacity)
			. ')';
		return $string;
	}
}

// __END__
