<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: Lch
 * for oklch or cie
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class LCH
{
	/** @var float Lightness/Luminance
	 * CIE: 0 to 100
	 * OKlch: 0.0 to 1.0
	 * BOTH: 0% to 100%
	 */
	private float $L = 0.0;
	/** @var float Chroma
	 * CIE: 0 to 150, cannot be more than 230
	 * OkLch: 0 to 0.4, does not exceed 0.5
	 * BOTH: 0% to 100% (0 to 150, 0 to 0.4)
	 */
	private float $C = 0.0;
	/** @var float Hue
	 * 0 to 360 deg
	 */
	private float $H = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

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
	 * @param  float $L
	 * @param  float $c
	 * @param  float $h
	 * @return self
	 */
	public static function __constructFromSet(float $L, float $c, float $h): self
	{
		return (new LCH())->setAsArray([$L, $c, $h]);
	}

	/**
	 * set from array
	 * where 0: Lightness, 1: Chroma, 2: Hue
	 *
	 * @param  array{0:float,1:float,2:float} $lch
	 * @return self
	 */
	public static function __constructFromArray(array $lch): self
	{
		return (new LCH())->setAsArray($lch);
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
			// case 'L':
			// 	if ($this->colorspace == 'cie' && ($value < 0 || $value > 100)) {
			// 		throw new \LengthException(
			// 			'Argument value ' . $value . ' for lightness is not in the range of '
			// 				. '0 to 100',
			// 			3
			// 		);
			// 	} elseif ($this->colorspace == 'ok' && ($value < 0 || $value > 1)) {
			// 		throw new \LengthException(
			// 			'Argument value ' . $value . ' for lightness is not in the range of '
			// 				. '0 to 1',
			// 			3
			// 		);
			// 	}
			// 	break;
			// case 'c':
			// 	if ($this->colorspace == 'cie' && ($value < 0 || $value > 230)) {
			// 		throw new \LengthException(
			// 			'Argument value ' . $value . ' for chroma is not in the range of '
			// 				. '0 to 230 with normal upper limit of 150',
			// 			3
			// 		);
			// 	} elseif ($this->colorspace == 'ok' && ($value < 0 || $value > 0.5)) {
			// 		throw new \LengthException(
			// 			'Argument value ' . $value . ' for chroma is not in the range of '
			// 				. '0 to 0.5 with normal upper limit of 0.5',
			// 			3
			// 		);
			// 	}
			// 	break;
			case 'h':
				if ($value == 360) {
					$value = 0;
				}
				if ($value < 0 || $value > 360) {
					throw new \LengthException(
						'Argument value ' . $value . ' for lightness is not in the range of 0 to 360',
						1
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
	 * where 0: Lightness, 1: Chroma, 2: Hue
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->L, $this->C, $this->H];
	}

	/**
	 * set color as array
	 * where 0: Lightness, 1: Chroma, 2: Hue
	 *
	 * @param  array{0:float,1:float,2:float} $lch
	 * @return self
	 */
	public function setAsArray(array $lch): self
	{
		$this->__set('L', $lch[0]);
		$this->__set('C', $lch[1]);
		$this->__set('H', $lch[2]);
		return $this;
	}
}

// __END__
