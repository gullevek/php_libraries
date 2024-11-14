<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: HSB/HSV
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class HSB implements Interface\CoordinatesInterface
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['sRGB'];

	/** @var float hue */
	private float $H = 0.0;
	/** @var float saturation */
	private float $S = 0.0;
	/** @var float brightness / value */
	private float $B = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	/**
	 * HSB (HSV) color coordinates
	 * Hue/Saturation/Brightness or Value
	 *
	 * @param string|array{0:float,1:float,2:float} $colors
	 * @param string $colorspace [default=sRGB]
	 * @param array<string,string> $options [default=[]]
	 * @throws \InvalidArgumentException only array colors allowed
	 */
	public function __construct(string|array $colors, string $colorspace = 'sRGB', array $options = [])
	{
		if (!is_array($colors)) {
			throw new \InvalidArgumentException('Only array colors allowed', 0);
		}
		$this->setColorspace($colorspace)->parseOptions($options)->setFromArray($colors);
	}

	/**
	 * set from array
	 * where 0: Hue, 1: Saturation, 2: Brightness
	 *
	 * @param  string|array{0:float,1:float,2:float} $colors
	 * @param  string $colorspace [default=sRGB]
	 * @param  array<string,string> $options [default=[]]
	 * @return self
	 */
	public static function create(string|array $colors, string $colorspace = 'sRGB', array $options = []): self
	{
		return new HSB($colors, $colorspace, $options);
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
		$name = strtoupper($name);
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		switch ($name) {
			case 'H':
				if ((int)$value == 360) {
					$value = 0;
				}
				if ((int)$value < 0 || (int)$value > 360) {
					throw new \LengthException(
						'Argument value ' . $value . ' for hue is not in the range of 0 to 360',
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
			case 'B':
				if ((int)$value < 0 || (int)$value > 100) {
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
	public function __get(string $name): float|string|bool
	{
		$name = strtoupper($name);
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
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	private function setFromArray(array $colors): self
	{
		$this->set('H', $colors[0]);
		$this->set('S', $colors[1]);
		$this->set('B', $colors[2]);
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
