<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: Lab
 * for oklab or cie
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class Lab
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['Oklab', 'cie'];

	/** @var float lightness/luminance
	 * CIE: 0f to 100f
	 * OKlab: 0.0 to 1.0
	 * BOTH: 0% to 100%
	 */
	private float $L = 0.0;
	/** @var float a axis distance
	 * CIE: -125 to 125, cannot be more than +/- 160
	 * OKlab: -0.4 to 0.4, cannot exceed +/- 0.5
	 * BOTH: -100% to 100% (+/-125 or 0.4)
	 */
	private float $a = 0.0;
	/** @var float b axis distance
	 * CIE: -125 to 125, cannot be more than +/- 160
	 * OKlab: -0.4 to 0.4, cannot exceed +/- 0.5
	 * BOTH: -100% to 100% (+/-125 or 0.4)
	 */
	private float $b = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	/**
	 * Color Coordinate: Lab
	 * for oklab or cie
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $L
	 * @param  float $a
	 * @param  float $b
	 * @param  string $colorspace
	 * @return self
	 */
	public static function __constructFromSet(float $L, float $a, float $b, string $colorspace): self
	{
		return (new Lab())->setColorspace($colorspace)->setAsArray([$L, $a, $b]);
	}

	/**
	 * set from array
	 * where 0: Lightness, 1: a, 2: b
	 *
	 * @param  array{0:float,1:float,2:float} $rgb
	 * @param  string $colorspace
	 * @return self
	 */
	public static function __constructFromArray(array $lab, string $colorspace): self
	{
		return (new Lab())->setColorspace($colorspace)->setAsArray($lab);
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
		// switch ($name) {
		// 	case 'L':
		// 		if ($value == 360) {
		// 			$value = 0;
		// 		}
		// 		if ($value < 0 || $value > 360) {
		// 			throw new \LengthException(
		// 				'Argument value ' . $value . ' for lightness is not in the range of 0 to 360',
		// 				1
		// 			);
		// 		}
		// 		break;
		// 	case 'a':
		// 		if ($value < 0 || $value > 100) {
		// 			throw new \LengthException(
		// 				'Argument value ' . $value . ' for a is not in the range of 0 to 100',
		// 				2
		// 			);
		// 		}
		// 		break;
		// 	case 'b':
		// 		if ($value < 0 || $value > 100) {
		// 			throw new \LengthException(
		// 				'Argument value ' . $value . ' for b is not in the range of 0 to 100',
		// 				3
		// 			);
		// 		}
		// 		break;
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
	 * Returns the color as array
	 * where 0: Lightness, 1: a, 2: b
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->L, $this->a, $this->b];
	}

	/**
	 * set color as array
	 * where 0: Lightness, 1: a, 2: b
	 *
	 * @param  array{0:float,1:float,2:float} $lab
	 * @return self
	 */
	public function setAsArray(array $lab): self
	{
		$this->__set('L', $lab[0]);
		$this->__set('a', $lab[1]);
		$this->__set('b', $lab[2]);
		return $this;
	}
}

// __END__
