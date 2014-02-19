<?php

/**
 * @package geometry
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-09
 */

require_once 'exceptions.php';

/**
 * Класс, описывающий размер как пару высота-ширина.
 */

class Size {

	/**
	 * ширина
	 *
	 * @var int
	 */
	private $width;

	/**
	 * высота
	 *
	 * @var int
	 */

	private $height;

	/**
	 * @param int $width
	 * @param int $height
	 * @throws IllegalArgumentException
	 */

	public function __construct($width, $height) {
		$this->setWidth($width);
		$this->setHeight($height);
	}

	/**
	 * Устанавливает больше ли, (по высоте <b>или</b> по ширине)
	 * размер заданного размера.
	 *
	 * @param Size $s
	 * @return boolean
	 */

	public function greatThen(Size $s) {
		return $this->getWidth() > $s->getWidth() || $this->getHeight() > $s->getHeight();
	}

	/**
	 * Устанавливает меньше ли, (по высоте <b>и</b> по ширине)
	 * размер заданного размера.
	 *
	 * @param Size $s
	 * @return bool
	 */

	public function lessThen(Size $s) {
		return !$this->greatThen($s) && !$this->equals($s);
	}

	/**
	 * Устанавливает, меньше или равен ли размер (по высоте <b>и</b> по ширине)
	 * заданного размера.
	 *
	 * @param Size
	 * @return bool
	 */

	public function isInner(Size $s) {
		return $this->getWidth() <= $s->getWidth() && $this->getHeight() <= $s->getHeight();
	}

	/**
	 * Устанавливает равен ли заданный размер текущему.
	 *
	 * @param Size
	 * @return bool
	 */

	public function equals(Size $s) {
		return $this->getWidth() == $s->getWidth() && $this->getHeight() == $s->getHeight();
	}

	/**
	 * Менеят местами высоту и ширину размера, "переварачивая" его.
	 * @return Size
	 */

	public function flip() {
		$t = $this->getWidth();
		$this->setWidth($this->getHeight());
		$this->setHeight($t);
		return $this;
	}

	/**
	 * Пропорционально уменьшает размер по заданной ширине.
	 * Возвращает новый размер, не изменяя старый.
	 *
	 * @param int $width
	 * @return Size
	 * @throws IllegalArgumentExceptions
	 */

	public function getByWidth($width) {
		if (!is_integer($width)) {
			throw new IllegalArgumentExceptions();
		}

		if ($width >= $this->getWidth()) {
			return $this;
		}

		$height = (int) round($this->getHeight() * $width / $this->getWidth());
		return new Size($width, $height);
	}

	/**
	 * Пропорционально уменьшает размер по заданной высоте.
	 * Возвращает новый размер, не изменяя старый.
	 *
	 * @param int $height
	 * @return Size
	 * @throws IllegalArgumentException */

	public function getByHeight($height) {
		if (!is_integer($height)) {
			throw new IllegalArgumentException();
		}

		if ($height >= $this->getHeight()) {
			return $this;
		}

		$width = (int) round($this->getWidth() * $height / $this->getHeight());
		return new Size($width, $height);
	}

	/**
	 * Вписывает размер в рамки, пропорционально уменьшая его.
	 * Возвращает новый размер, не изменяя старый.
	 *
	 * @return Size
	 * @throws IllegalArgumentException
	 */

	public function getByFrame() { // Size $frame || $width, $height
		$args = func_get_args();
		if (count($args) == 2) {
			$this->getByFrame(new Size($args[0], $args[1]));
		} else if (count($args) == 1 && $args[0] instanceof Size) {
			$frame = $args[0];
		} else {
			throw new IllegalArgumentException();
		}

		if ($frame->getWidth() <= 0 || $frame->getHeight() <= 0)
			throw new IllegalArgumentException();

		if ($this->isInner($frame))
			return $this;

		$height = $frame->getHeight();
		$width = $frame->getWidth();

		if ($this->getWidth() / $width > $this->getHeight() / $height)
			return $this->getByWidth($width);

		return $this->getByHeight($height);
	}

	/**
	 * Складывает высоту и ширину размера с координатами точки или
	 * высотой и шириной другого размера и возвращает получившийся размер.
	 *
	 * @param Size $s
	 * @param Size|Point $obj
	 * @return Size
	 * @throws IllegalArgumentException
	 */

	public static function add(Size $s, $obj) {
		if($obj instanceof Size) {
			return new Size($s->getWidth() + $obj->getWidth(), $s->getHeight() + $obj->getHeight());
		} else if ($obj instanceof Point) {
			return new Size($s->getWidth() + $obj->getX(),	$s->getHeigth() + $obj->getY());
		}
		throw new IllegalArgumentException();
	}

	/**
	 *
	 * Вычитает из высоты и ширины размера координаты точки или
	 * высоту и ширину другого размера и возвращает получившийся размер.
	 *
	 * @param Size $s
	 * @param Size|Point $obj
	 * @return Size
	 * @throws IllegalArgumentException
	 */

	public static function subtract(Size $s, $obj) {
		if($obj instanceof Size) {
			return new Size($s->getWidth() - $obj->getWidth(), $s->getHeight() - $obj->getHeight());
		} else if ($obj instanceof Point) {
			return new Size($s->getWidth() - $obj->getX(),	$s->getHeigth() - $obj->getY());
		}
		throw new IllegalArgumentException();
	}

	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param int
	 * @throws IllegalArgumentException
	 */

	public function setWidth($width) {
		if (is_integer($width)) {
			$this->width = $width;
		} else {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * @param int
	 * @throws IllegalArgumentException
	 */

	public function setHeight($height) {
		if (is_integer($height)) {
			$this->height = $height;
		} else {
			throw new IllegalArgumentException();
		}
	}

	public function __toString() {
		return "{width: {$this->width}, height: {$this->height}}";
	}
}
?>