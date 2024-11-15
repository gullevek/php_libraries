<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate: RGB
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color\Coordinates;

use CoreLibs\Convert\Color\Utils;

class RGB implements Interface\CoordinatesInterface
{
	/** @var array<string> allowed colorspaces */
	private const COLORSPACES = ['sRGB'];

	/** @var float red 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $R = 0.0;
	/** @var float green 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $G = 0.0;
	/** @var float blue 0 to 255 or 0.0f to 1.0f for linear RGB */
	private float $B = 0.0;

	/** @var string color space: either ok or cie */
	private string $colorspace = '';

	/** @var bool set if this is linear */
	private bool $linear = false;

	/**
	 * Color Coordinate RGB
	*  @param array{0:float,1:float,2:float}|string $colors RGB color array or hex string
	 * @param string $colorspace [default=sRGB]
	 * @param array<string,bool> $options [default=[]] only "linear" allowed at the moment
	 */
	public function __construct(string|array $colors, string $colorspace = 'sRGB', array $options = [])
	{
		$this->setColorspace($colorspace)->parseOptions($options);
		if (is_array($colors)) {
			$this->setFromArray($colors);
		} else {
			$this->setFromHex($colors);
		}
	}

	/**
	 * set from array or string
	 * where 0: Red, 1: Green, 2: Blue
	 * OR #ffffff or ffffff
	 *
	 * @param  array{0:float,1:float,2:float}|string $colors RGB color array or hex string
	 * @param  string $colorspace [default=sRGB]
	 * @param  array<string,bool> $options [default=[]] only "linear" allowed at the moment
	 * @return self
	 */
	public static function create(string|array $colors, string $colorspace = 'sRGB', array $options = []): self
	{
		return new RGB($colors, $colorspace, $options);
	}

	/**
	 * parse options
	 *
	 * @param  array<string,bool> $options
	 * @return self
	 */
	private function parseOptions(array $options): self
	{
		$this->flagLinear($options['linear'] ?? false);
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
		// do not allow setting linear from outside
		if ($name == 'linear') {
			return;
		}
		if (!property_exists($this, $name)) {
			throw new \ErrorException('Creation of dynamic property is not allowed', 0);
		}
		// if not linear
		if (!$this->linear && ((int)$value < 0 || (int)$value > 255)) {
			throw new \LengthException('Argument value ' . $value . ' for color ' . $name
			. ' is not in the range of 0 to 255', 1);
		} elseif (
			// $this->linear && ($value < 0.0 || $value > 1.0)
			$this->linear && Utils::compare(0.0, $value, 1.0, 0.000001)
		) {
			throw new \LengthException('Argument value ' . $value . ' for color ' . $name
				. ' is not in the range of 0 to 1 for linear rgb', 2);
		}
		$this->$name = $value;
	}

	/**
	 * get color
	 *
	 * @param string $name
	 * @return float|bool
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
	 * @param  array{0:float,1:float,2:float} $colors
	 * @return self
	 */
	private function setFromArray(array $colors): self
	{
		$this->set('R', $colors[0]);
		$this->set('G', $colors[1]);
		$this->set('B', $colors[2]);
		return $this;
	}

	/**
	 * Return current set RGB as hex string. with or without # prefix
	 *
	 * @param  bool   $hex_prefix
	 * @return string
	 */
	public function returnAsHex(bool $hex_prefix = true): string
	{
		// prefix
		$hex_color = '';
		if ($hex_prefix === true) {
			$hex_color = '#';
		}
		// convert if in linear
		if ($this->linear) {
			$this->fromLinear();
		}
		foreach ($this->returnAsArray() as $color) {
			$hex_color .= str_pad(dechex((int)$color), 2, '0', STR_PAD_LEFT);
		}
		return $hex_color;
	}

	/**
	 * set colors RGB from hex string
	 *
	 * @param  string $hex_string
	 * @return self
	 */
	private function setFromHex(string $hex_string): self
	{
		$hex_string = preg_replace("/[^0-9A-Fa-f]/", '', $hex_string); // Gets a proper hex string
		if (empty($hex_string) || !is_string($hex_string)) {
			throw new \InvalidArgumentException('hex_string argument cannot be empty', 3);
		}
		$rgbArray = [];
		if (strlen($hex_string) == 6) {
			// If a proper hex code, convert using bitwise operation.
			// No overhead... faster
			$colorVal = hexdec($hex_string);
			$rgbArray = [
				0xFF & ($colorVal >> 0x10),
				0xFF & ($colorVal >> 0x8),
				0xFF & $colorVal
			];
		} elseif (strlen($hex_string) == 3) {
			// If shorthand notation, need some string manipulations
			$rgbArray = [
				hexdec(str_repeat(substr($hex_string, 0, 1), 2)),
				hexdec(str_repeat(substr($hex_string, 1, 1), 2)),
				hexdec(str_repeat(substr($hex_string, 2, 1), 2))
			];
		} else {
			// Invalid hex color code
			throw new \UnexpectedValueException('Invalid hex_string: ' . $hex_string, 4);
		}
		return $this->setFromArray($rgbArray);
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
		// if linear, as is
		if ($this->linear) {
			return $this;
		}
		$this->flagLinear(true)->setFromArray(array_map(
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
		// if not linear, as is
		if (!$this->linear) {
			return $this;
		}
		$this->flagLinear(false)->setFromArray(array_map(
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
		return $this;
	}

	/**
	 * convert to css string with optional opacity
	 * Note: if this is a linear RGB, the data will converted during this operation and the converted back
	 *
	 * @param  float|string|null $opacity
	 * @return string
	 */
	public function toCssString(null|float|string $opacity = null): string
	{
		// if we are in linear mode, convert to normal mode temporary
		$was_linear = false;
		if ($this->linear) {
			$this->fromLinear();
			$was_linear = true;
		}
		$string = 'rgb('
			. (int)round($this->R, 0)
			. ' '
			. (int)round($this->G, 0)
			. ' '
			. (int)round($this->B, 0)
			. Utils::setOpacity($opacity)
			. ')';
		if ($was_linear) {
			$this->toLinear();
		}
		return $string;
	}
}

// __END__
