<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HWB
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class HWB
{
	/** @var float Hue */
	private float $H = 0.0;
	/** @var float Whiteness */
	private float $W = 0.0;
	/** @var float Blackness */
	private float $B = 0.0;
	/**
	 * Color Coordinate: HWB
	 * Hue/Whiteness/Blackness
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $H Hue
	 * @param  float $W Whiteness
	 * @param  float $B Blackness
	 * @return self
	 */
	public static function __constructFromSet(float $H, float $W, float $B): self
	{
		return (new HWB())->setAsArray([$H, $W, $B]);
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Whiteness, 2: Blackness
	 *
	 * @param  array{0:float,1:float,2:float} $hwb
	 * @return self
	 */
	public static function __constructFromArray(array $hwb): self
	{
		return (new HWB())->setAsArray($hwb);
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
	 * @param  array{0:float,1:float,2:float} $hwb
	 * @return self
	 */
	public function setAsArray(array $hwb): self
	{
		$this->__set('H', $hwb[0]);
		$this->__set('W', $hwb[1]);
		$this->__set('B', $hwb[2]);
		return $this;
	}
}

// __END__
