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

use CoreLibs\Convert\Color\Utils;

class Lab implements Interface\CoordinatesInterface
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['OkLab', 'CIELab'];

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
	 *
	 * @param string|array{0:float,1:float,2:float} $rgb
	 * @param string $colorspace [default='']
	 * @param array<string,string> $options [default=[]]
	 * @throws \InvalidArgumentException only array colors allowed
	 */
	public function __construct(string|array $colors, string $colorspace = '', array $options = [])
	{
		if (!is_array($colors)) {
			throw new \InvalidArgumentException('Only array colors allowed', 0);
		}
		$this->setColorspace($colorspace)->parseOptions($options)->setFromArray($colors);
	}

	/**
	 * set from array
	 * where 0: Lightness, 1: a, 2: b
	 *
	 * @param array{0:float,1:float,2:float} $rgb
	 * @param string $colorspace [default='']
	 * @param array<string,string> $options [default=[]]
	 * @return self
	 */
	public static function create(string|array $colors, string $colorspace = '', array $options = []): self
	{
		return new Lab($colors, $colorspace, $options);
	}

	/**
	 * parse options
	 *
	 * @param  array<string,string> $options
	 * @return self
	 */
	private function parseOptions(array $options): self
	{
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
		switch ($name) {
			case 'L':
				// if ($this->colorspace == 'CIELab' && ($value < 0 || $value > 100)) {
				if ($this->colorspace == 'CIELab' && Utils::compare(0.0, $value, 100.0, Utils::ESPILON_BIG)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for lightness is not in the range of 0 to 100 for CIE Lab',
						1
					);
				// } elseif ($this->colorspace == 'OkLab' && ($value < 0 || $value > 1)) {
				} elseif ($this->colorspace == 'OkLab' && Utils::compare(0.0, $value, 1.0, Utils::EPSILON_SMALL)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for lightness is not in the range of 0.0 to 1.0 for OkLab',
						1
					);
				}
				break;
			case 'a':
				// if ($this->colorspace == 'CIELab' && ($value < -125 || $value > 125)) {
				if ($this->colorspace == 'CIELab' && Utils::compare(-125.0, $value, 125.0, Utils::EPSILON_SMALL)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for a is not in the range of -125 to 125 for CIE Lab',
						2
					);
				// } elseif ($this->colorspace == 'OkLab' && ($value < -0.55 || $value > 0.55)) {
				} elseif ($this->colorspace == 'OkLab' && Utils::compare(-0.55, $value, 0.55, Utils::EPSILON_SMALL)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for a is not in the range of -0.5 to 0.5 for OkLab',
						2
					);
				}
				break;
			case 'b':
				// if ($this->colorspace == 'CIELab' && ($value < -125 || $value > 125)) {
				if ($this->colorspace == 'CIELab' && Utils::compare(-125.0, $value, 125.0, Utils::EPSILON_SMALL)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for b is not in the range of -125 to 125 for CIE Lab',
						3
					);
				// } elseif ($this->colorspace == 'OkLab' && ($value < -0.55 || $value > 0.55)) {
				} elseif ($this->colorspace == 'OkLab' && Utils::compare(-0.55, $value, 0.55, Utils::EPSILON_SMALL)) {
					throw new \LengthException(
						'Argument value ' . $value . ' for b is not in the range of -0.5 to 0.5 for OkLab',
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
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	private function setFromArray(array $colors): self
	{
		$this->set('L', $colors[0]);
		$this->set('a', $colors[1]);
		$this->set('b', $colors[2]);
		return $this;
	}

	/**
	 * Convert into css string with optional opacity
	 *
	 * @param  null|float|string|null $opacity
	 * @return string
	 */
	public function toCssString(null|float|string $opacity = null): string
	{
		$string = '';
		switch ($this->colorspace) {
			case 'CIELab':
				$string = 'lab';
				break;
			case 'OkLab':
				$string = 'oklab';
				break;
		}
		$string .= '('
			. $this->L
			. ' '
			. $this->a
			. ' '
			. $this->b
			. Utils::setOpacity($opacity)
			. ');';

		return $string;
	}
}

// __END__
