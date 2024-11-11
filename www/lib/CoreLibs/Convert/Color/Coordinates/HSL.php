<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HSL
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class HSL
{
	/** @var float hue */
	private float $H = 0.0;
	/** @var float saturation */
	private float $S = 0.0;
	/** @var float lightness (luminance) */
	private float $L = 0.0;
	/**
	 * Color Coordinate HSL
	 * Hue/Saturation/Lightness
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $H Hue
	 * @param  float $S Saturation
	 * @param  float $L Lightness
	 * @return self
	 */
	public static function __constructFromSet(float $H, float $S, float $L): self
	{
		return (new HSL())->setAsArray([$H, $S, $L]);
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Saturation, 2: Lightness
	 *
	 * @param  array{0:float,1:float,2:float} $hsl
	 * @return self
	 */
	public static function __constructFromArray(array $hsl): self
	{
		return (new HSL())->setAsArray($hsl);
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
				if ($value < 0 || $value > 359) {
					throw new \LengthException(
						'Argument value ' . $value . ' for hue is not in the range of 0 to 359',
						1
					);
				}
				break;
			case 'S':
				if ($value < 0 || $value > 100) {
					throw new \LengthException(
						'Argument value ' . $value . ' for saturation is not in the range of 0 to 100',
						2
					);
				}
				break;
			case 'L':
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
	 * @param  array{0:float,1:float,2:float} $hsl
	 * @return self
	 */
	public function setAsArray(array $hsl): self
	{
		$this->__set('H', $hsl[0]);
		$this->__set('S', $hsl[1]);
		$this->__set('L', $hsl[2]);
		return $this;
	}
}

// __END__
