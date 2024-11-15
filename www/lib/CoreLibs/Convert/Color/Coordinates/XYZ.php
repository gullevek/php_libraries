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

// use CoreLibs\Convert\Color\Utils;

class XYZ implements Interface\CoordinatesInterface
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

	/** @var string illuminat white point: only D50 and D65 are allowed */
	private string $whitepoint = '';

	/**
	 * Color Coordinate Lch
	 * for oklch conversion
	 *
	 * @param string|array{0:float,1:float,2:float} $colors
	 * @param string $colorspace [default=CIEXYZ]
	 * @param array<string,string> $options [default=[]] Only "whitepoint" option allowed
	 * @throws \InvalidArgumentException only array colors allowed
	 */
	public function __construct(
		string|array $colors,
		string $colorspace = 'CIEXYZ',
		array $options = [],
	) {
		if (!is_array($colors)) {
			throw new \InvalidArgumentException('Only array colors allowed', 0);
		}
		$this->setColorspace($colorspace)
			->parseOptions($options)
			->setFromArray($colors);
	}

	/**
	 * set from array
	 * where 0: X, 1: Y, 2: Z
	 *
	 * @param array{0:float,1:float,2:float} $colors
	 * @param string $colorspace [default=CIEXYZ]
	 * @param array<string,string> $options [default=[]] Only "whitepoint" option allowed
	 * @return self
	 */
	public static function create(
		string|array $colors,
		string $colorspace = 'CIEXYZ',
		array $options = [],
	): self {
		return new XYZ($colors, $colorspace, $options);
	}

	/**
	 * parse options
	 *
	 * @param  array<string,string> $options
	 * @return self
	 */
	private function parseOptions(array $options): self
	{
		$this->setWhitepoint($options['whitepoint'] ?? '');
		return $this;
	}

	/**
	 * set color
	 *
	 * @param  string $name
	 * @param  float  $value
	 * @return void
	 */
	private function set(string $name, float $value): void
	{
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		// TODO: setup XYZ value limits
		// X: 0 to 95.047, Y: 0 to 100, Z: 0 to 108.88
		// if (Utils::compare(0.0, $value, 100.0, Utils::EPSILON_SMALL))) {
		// 	throw new \LengthException('Argument value ' . $value . ' for color ' . $name
		// 		. ' is not in the range of 0 to 100.0', 1);
		// }
		$this->$name = $value;
	}

	/**
	 * get color
	 *
	 * @param string $name
	 * @return float
	 */
	public function __get(string $name): float|string|bool
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
	private function setFromArray(array $colors): self
	{
		$this->set('X', $colors[0]);
		$this->set('Y', $colors[1]);
		$this->set('Z', $colors[2]);
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
		throw new \ErrorException('XYZ is not available as CSS color string', 0);
	}
}

// __END__
