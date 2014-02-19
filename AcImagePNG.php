<?php

/**
 * @package image
 *
 * @author Андрей Загорцев <freeron@ya.ru>
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-11
 */

require_once 'AcImage.php';
require_once 'geometry/exceptions.php';

/**
 * Класс, описывающий изображение в формате png
 */

class AcImagePNG extends AcImage
{
	/**
	 * Проверяет, поддерживается ли формат png
	 *
	 * @return boolean
	 */

	public static function isSupport()
	{
		$gdInfo = parent::getGDinfo();
		return (bool)$gdInfo['PNG Support'];
	}

	/**
	 * @param string путь к файлу с изображением
	 * @throws UnsupportedFormatException
	 */

	protected function __construct($filePath)
	{
		if (!self::isSupport())
			throw new UnsupportedFormatException('png');

		parent::__construct($filePath);
		$path = parent::getFilePath();
		parent::setResource(@imagecreatefrompng($path));
	}

	/**
	 * @param string путь, по которому будет сохранено изображение
	 * @return AcImage
	 */

	public function save($path)
	{
		return parent::saveAsPNG($path);
	}

	/**
	 * Возвращяет качество png-изображения
	 * @return int
	 * @throws FileAlreadyExistsException
	 * @throws FileNotSaveException
	 */

	// png qulity [0, 9] показывает степень сжатия
	// 0 - лучшее качество (нет сжатия)
	public static function getQuality()
	{
		return 9 - round(parent::getQuality() / 10);
	}
}
?>