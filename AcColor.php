<?php

/**
 * @package image
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-11
 */

require_once 'geometry/exceptions.php';

/**
 * Класс, описывающий цвет в RGB формате
 */

class AcColor
{
	/**
	 * Интенсивность красного канала.
	 * @var int
	 */

	private $red;

	/**
	 * Интенсивность зелёного канала.
	 * @var int
	 */

	private $green;

	/**
	 * Интенсивность синего канала.
	 * @var int
	 */

	private $blue;

	const WHITE = 16777215; // pow(2, 8 * 3) || 0xFFFFFF
	const BLACK = 0;

	public function __construct() // $r, $g, $b || $code
	{
		$a = func_get_args();
		if (count($a) == 3)
		{
			$this->setRed($a[0]);
			$this->setGreen($a[1]);
			$this->setBlue($a[2]);
		}
		else if (count($a) == 1)
		{
			if (!self::isValidCode($a[0]))
				throw new IllegalArgumentException();

			$hexCode = dechex($a[0]);
			$r = hexdec(substr($hexCode, 0, 2));
			$g = hexdec(substr($hexCode, 2, 2));
			$b = hexdec(substr($hexCode, 4, 2));
			$this->__construct($r, $g, $b);
		}
	}

	/**
	 * Устанавливает, валидна ли интенсивность цветового канала.
	 *
	 * @param $channel иненсивность канала
	 * @return boolean
	 */

	public static function isValidChanel($channel)
	{
		return is_integer($channel) && $channel >=0 && $channel < 256;
	}

	/**
	 * Устанавливает, валиден ли код цвета.
	 *
	 * @param int $code
	 * @return boolean
	 */

	public static function isValidCode($code)
	{
		return is_integer($code) && $code >= 0 && $code <= self::WHITE;
	}

	/**
	 * @ignore
	 */

	private function convert10To16($channel)
	{
		if ($channel < 16)
		{
			return "0".dechex($channel);
		}
		return dechex($channel);
	}

	/**
	 * Возвращает шестнадцатиричный код цвета в формате 0xRRGGBB
	 * @return string
	 */

	public function getHexCode()
	{
		$r = $this->convert10To16($this->red);
		$g = $this->convert10To16($this->green);
		$b = $this->convert10To16($this->blue);

		return "0x$r$g$b";
	}

	// getters and setters

	public function getCode()
	{
		return hexdec($this->getHexCode());
	}

	/**
	 * @param int
	 * @throws InvalidChannelException
	 */

	public function setRed($red)
	{
		if (self::isValidChanel($red))
			$this->red = $red;
		else
			throw new InvalidChannelException('red');
	}

	/**
	 * @param int
	 * @throws InvalidChannelException
	 */

	public function setGreen($green)
	{
		if (self::isValidChanel($green))
			$this->green = $green;
		else
			throw new InvalidChannelException('green');
	}

	/**
	 * @param int
	 * @throws InvalidChannelException
	 */

	public function setBlue($blue)
	{
		if (self::isValidChanel($blue))
			$this->blue = $blue;
		else
			throw new InvalidChannelException('blue');
	}

	public function getRed()
	{
		return $this->red;
	}

	public function getGreen()
	{
		return $this->green;
	}

	public function getBlue()
	{
		return $this->blue;
	}
}
?>