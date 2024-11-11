<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: XYZ (Cie)
 * Note, this is only for the D65 whitepoint
 * https://en.wikipedia.org/wiki/CIE_1931_color_space#Construction_of_the_CIE_XYZ_color_space_from_the_Wright%E2%80%93Guild_data
 * https://en.wikipedia.org/wiki/Standard_illuminant#D65_values
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class XYZD65
{
	private float $X = 0.0;
	private float $Y = 0.0;
	private float $Z = 0.0;

	/**
	 * Color Coordinate Lch
	 * for oklch
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $X
	 * @param  float $Y
	 * @param  float $Z
	 * @return self
	 */
	public static function __constructFromSet(float $X, float $Y, float $Z): self
	{
		return (new XYZD65())->setAsArray([$X, $Y, $Z]);
	}

	/**
	 * set from array
	 * where 0: X, 1: Y, 2: Z
	 *
	 * @param  array{0:float,1:float,2:float} $xyzD65
	 * @return self
	 */
	public static function __constructFromArray(array $xyzD65): self
	{
		return (new XYZD65())->setAsArray($xyzD65);
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
		// if ($value < 0 || $value > 255) {
		// 	throw new \LengthException('Argument value ' . $value . ' for color ' . $name
		// 		. ' is not in the range of 0 to 255', 1);
		// }
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
	 * where 0: X, 1: Y, 2: Z
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->X, $this->Y, $this->Z];
	}

	/**
	 * set color as array
	 * where 0: X, 1: Y, 2: Z
	 *
	 * @param  array{0:float,1:float,2:float} $xyzD65
	 * @return self
	 */
	public function setAsArray(array $xyzD65): self
	{
		$this->__set('X', $xyzD65[0]);
		$this->__set('Y', $xyzD65[1]);
		$this->__set('Z', $xyzD65[2]);
		return $this;
	}
}

// __END__
