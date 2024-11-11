<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: RGB
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

class RGB
{
	/** @var float red 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $R = 0.0;
	/** @var float green 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $G = 0.0;
	/** @var float blue 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $B = 0.0;

	/** @var bool set if this is linear */
	private bool $linear = false;

	/**
	 * Color Coordinate RGB
	 */
	public function __construct()
	{
	}

	/**
	 * set with each value as parameters
	 *
	 * @param  float $R Red
	 * @param  float $G Green
	 * @param  float $B Blue
	 * @param  bool $linear [default=false]
	 * @return self
	 */
	public static function __constructFromSet(float $R, float $G, float $B, bool $linear = false): self
	{
		return (new RGB())->flagLinear($linear)->setAsArray([$R, $G, $B]);
	}

	/**
	 * set from array
	 * where 0: Red, 1: Green, 2: Blue
	 *
	 * @param  array{0:float,1:float,2:float} $rgb
	 * @param  bool $linear [default=false]
	 * @return self
	 */
	public static function __constructFromArray(array $rgb, bool $linear = false): self
	{
		return (new RGB())->flagLinear($linear)->setAsArray($rgb);
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
		// do not allow setting linear from outside
		if ($name == 'linear') {
			return;
		}
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		// if not linear
		if (!$this->linear && ($value < 0 || $value > 255)) {
			throw new \LengthException('Argument value ' . $value . ' for color ' . $name
				. ' is not in the range of 0 to 255', 1);
		} elseif ($this->linear && ($value < -10E10 || $value > 1)) {
			// not allow very very small negative numbers
			throw new \LengthException('Argument value ' . $value . ' for color ' . $name
				. ' is not in the range of 0 to 1 for linear rgb', 1);
		}
		$this->$name = $value;
	}

	/**
	 * get color
	 *
	 * @param string $name
	 * @return float|bool
	 */
	public function __get(string $name): float|bool
	{
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		return $this->$name;
	}

	/**
	 * Returns the color as array
	 * where 0: Red, 1: Green, 2: Blue
	 *
	 * @return array{0:float,1:float,2:float}
	 */
	public function returnAsArray(): array
	{
		return [$this->R, $this->G, $this->B];
	}

	/**
	 * set color as array
	 * where 0: Red, 1: Green, 2: Blue
	 *
	 * @param  array{0:float,1:float,2:float} $rgb
	 * @return self
	 */
	public function setAsArray(array $rgb): self
	{
		$this->__set('R', $rgb[0]);
		$this->__set('G', $rgb[1]);
		$this->__set('B', $rgb[2]);
		return $this;
	}

	/**
	 * set as linear
	 * can be used as chain call on create if input is linear RGB
	 * RGB::__construct**(...)->flagLinear();
	 * as it returns self
	 *
	 * @return self
	 */
	private function flagLinear(bool $linear): self
	{
		$this->linear = $linear;
		return $this;
	}

	/**
	 * Both function source:
	 * https://bottosson.github.io/posts/colorwrong/#what-can-we-do%3F
	 * but reverse f: fromLinear and f_inv for toLinear
	 * Code copied from here:
	 * https://stackoverflow.com/a/12894053
	 *
	 * converts RGB to linear
	 * We come from 0-255 so we need to divide by 255
	 *
	 * @return self
	 */
	public function toLinear(): self
	{
		$this->flagLinear(true)->setAsArray(array_map(
			callback: function (int|float $v) {
				$v = (float)($v / 255);
				$abs = abs($v);
				$sign = ($v < 0) ? -1 : 1;
				return (float)(
					$abs <= 0.04045 ?
						$v / 12.92 :
						$sign * pow(($abs + 0.055) / 1.055, 2.4)
				);
			},
			array: $this->returnAsArray(),
		));
		return $this;
	}

	/**
	 * convert back to normal sRGB from linear RGB
	 * we go to 0-255 rgb so we multiply by 255
	 *
	 * @return self
	 */
	public function fromLinear(): self
	{
		$this->flagLinear(false)->setAsArray(array_map(
			callback: function (int|float $v) {
				$abs  = abs($v);
				$sign = ($v < 0) ? -1 : 1;
				// during reverse in some situations the values can become negative in very small ways
				// like -...E16 and ...E17
				return ($ret = (float)(255 * (
					$abs <= 0.0031308 ?
						$v * 12.92 :
						$sign * (1.055 * pow($abs, 1.0 / 2.4) - 0.055)
				))) < 0 ? 0 : $ret;
			},
			array: $this->returnAsArray(),
		));
		// $this->linear = false;
		return $this;
	}

	/**
	 * convert to css string with optional opacity
	 * Note: if this is a linea RGB, this data will not be correct
	 *
	 * @param  float|string|null $opacity
	 * @return string
	 */
	public function toCssString(null|float|string $opacity = null): string
	{
		// set opacity, either a string or float
		if (is_string($opacity)) {
			$opacity = ' / ' . $opacity;
		} elseif ($opacity !== null) {
			$opacity = ' / ' . $opacity;
		} else {
			$opacity = '';
		}
		return 'rgb('
			. (int)round($this->R, 0)
			. ' '
			. (int)round($this->G, 0)
			. ' '
			. (int)round($this->B, 0)
			. $opacity
			. ')';
	}
}

// __END__
