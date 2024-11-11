<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HSB/HSV
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class HSB
{
	/** @var float hue */
	private float $H = 0.0;
	/** @var float saturation */
	private float $S = 0.0;
	/** @var float brightness / value */
	private float $B = 0.0;

	/**
	 * HSB (HSV) color coordinates
	 * Hue/Saturation/Brightness or Value
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $H Hue
	 * @param  float $S Saturation
	 * @param  float $B Brightness
	 * @return self
	 */
	public static function __constructFromSet(float $H, float $S, float $B): self
	{
		return (new HSB())->setAsArray([$H, $S, $B]);
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Saturation, 2: Brightness
	 *
	 * @param  array{0:float,1:float,2:float} $hsb
	 * @return self
	 */
	public static function __constructFromArray(array $hsb): self
	{
		return (new HSB())->setAsArray($hsb);
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
		$name = strtoupper($name);
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
			case 'B':
				if ($value < 0 || $value > 100) {
					throw new \LengthException(
						'Argument value ' . $value . ' for brightness is not in the range of 0 to 100',
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
		$name = strtoupper($name);
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		return $this->$name;
	}

	/**
	 * Returns the color as array
	 * where 0: Hue, 1: Saturation, 2: Brightness
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->H, $this->S, $this->B];
	}

	/**
	 * set color as array
	 * where 0: Hue, 1: Saturation, 2: Brightness
	 *
	 * @param  array{0:float,1:float,2:float} $hsb
	 * @return self
	 */
	public function setAsArray(array $hsb): self
	{
		$this->__set('H', $hsb[0]);
		$this->__set('S', $hsb[1]);
		$this->__set('B', $hsb[2]);
		return $this;
	}

	/**
	 * no hsb in css
	 *
	 * @param  float|string|null $opacity
	 * @return string
	 * @throws \ErrorException
	 */
	public function toCssString(null|float|string $opacity = null): string
	{
		throw new \ErrorException('HSB is not available as CSS color string', 0);
	}
}

// __END__
