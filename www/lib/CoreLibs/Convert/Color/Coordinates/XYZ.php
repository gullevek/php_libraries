<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: XYZ (Cie) (colorspace CIEXYZ)
 * Note, this is only for the D50 & D65 whitepoint conversion
 * https://en.wikipedia.org/wiki/CIE_1931_color_space#Construction_of_the_CIE_XYZ_color_space_from_the_Wright%E2%80%93Guild_data
 * https://en.wikipedia.org/wiki/Standard_illuminant#Illuminant_series_D
 * https://en.wikipedia.org/wiki/Standard_illuminant#D65_values
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class XYZ
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['CIEXYZ'];
	/** @var array<string> allowed whitepoints
	 * D50: ICC profile PCS (horizon light) <-> CieLab
	 * D65: RGB color space (noon) <-> linear RGB
	 */
	private const ILLUMINANT = ['D50', 'D65'];

	/** @var float X coordinate */
	private float $X = 0.0;
	/** @var float Y coordinate (Luminance) */
	private float $Y = 0.0;
	/** @var float Z coordinate (blue) */
	private float $Z = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	private string $whitepoint = '';

	/**
	 * Color Coordinate Lch
	 * for oklch
	 */
	public function __construct()
	{
	}

	/**
	 * set from array
	 * where 0: X, 1: Y, 2: Z
	 *
	 * @param  array{0:float,1:float,2:float} $colors
	 * @param  string $colorspace [default=CIEXYZ]
	 * @param  string $whitepoint [default=''] only D65 or D50 allowed
	 * @return self
	 */
	public static function __constructFromArray(
		array $colors,
		string $colorspace = 'CIEXYZ',
		string $whitepoint = ''
	): self {
		return (new XYZ())
			->setColorspace($colorspace)
			->setWhitepoint($whitepoint)
			->setFromArray($colors);
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
	 * set the whitepoint flag
	 *
	 * @param  string $whitepoint
	 * @return self
	 */
	private function setWhitepoint(string $whitepoint): self
	{
		if (empty($whitepoint)) {
			$this->whitepoint = '';
			return $this;
		}
		if (!in_array($whitepoint, $this::ILLUMINANT)) {
			throw new \InvalidArgumentException('Not allowed whitepoint', 0);
		}
		$this->whitepoint = $whitepoint;
		return $this;
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
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	public function setFromArray(array $colors): self
	{
		$this->__set('X', $colors[0]);
		$this->__set('Y', $colors[1]);
		$this->__set('Z', $colors[2]);
		return $this;
	}
}

// __END__
