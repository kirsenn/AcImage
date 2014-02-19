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
 * Класс, описываюший точку на плоскости.
 */
class Point {

	/**
	 * Координата x точки
	 *
	 * @var int
	 */
	private $x;

	/**
	 * Координата y точки
	 *
	 * @var int
	 */
	private $y;

	/**
	 * @param int
	 * @param int
	 * @throws IllegalArgumentException
	 */
	public function __construct($x, $y) {
		$this->setX($x);
		$this->setY($y);
	}

	/**
	 * Смещает точку на указанную точку, складывая их координаты.
	 * Метод возвращает смещённую точку.
	 *
	 * @param Point
	 * @return Point
	 */
	public function offset(Point $p) {
		$this->setX($this->getX() + $p->getX());
		$this->setY($this->getY() + $p->getY());
		return $this;
	}

	/**
	 * Складывает координаты точки с координатами точки или высотой и шириной
	 * размера и возвращяет новую точку.
	 *
	 * @param Point
	 * @param Point|Size
	 * @return Point
	 * @throws IllegalArgumentException
	 */
	public static function add(Point $p, $obj) {
		if ($obj instanceof Point) {
			return new Point($p->getX() + $obj->getX(), $p->getY() + $obj->getY());
		} else if ($obj instanceof Size) {
			return new Point($p->getX() + $obj->getWidth(), $p->getY() + $obj->getHeight());
		}
		throw new IllegalArgumentException();
	}

	/**
	 * Вычитает из координат точки координаты другой точки или
	 * высоту и ширину размера.
	 *
	 * @param Point
	 * @param Point|Size
	 * @return Point
	 * @throws IllegalArgumentException
	 */
	public static function subtract(Point $p, $obj) {
		if ($obj instanceof Point) {
			return self::add($p, new Point(-$obj->getX(), -$obj->getY()));
		} else if ($obj instanceof Size) {
			return self::add($p, new Point(-$obj->getWidth(), -$obj->getHeight()));
		}
		throw new IllegalArgumentException();
	}

	/**
	 * Сравнивает две точки
	 *
	 * @param Point
	 * @return boolean
	 */
	public function equals(Point $p) {
		return $this->getX() == $p->getX() && $this->getY() == $p->getY();
	}

	public function getX() {
		return $this->x;
	}

	public function getY() {
		return $this->y;
	}

	/**
	 * @param int
	 * @throws IllegalArgumentException
	 */

	public function setX($x) {
		if (is_integer($x)) {
			$this->x = $x;
		} else {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * @param int
	 * @throws IllegalArgumentException
	 */

	public function setY($y) {
		if (is_integer($y)) {
			$this->y = $y;
		} else {
			throw new IllegalArgumentException();
		}
	}

	public function __toString() {
		return "{x: {$this->x}, y: {$this->y}}";
	}

}

?>