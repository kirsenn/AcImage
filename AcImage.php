<?php
/**
 * @package image
 *
 * @author Андрей Загорцев <freeron@ya.ru>
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 2.0.1
 * @since 2013-03-12
 */

require_once 'geometry/Rectangle.php';
require_once 'geometry/exceptions.php';
require_once 'AcColor.php';
require_once 'AcImageJPG.php';
require_once 'AcImageGIF.php';
require_once 'AcImagePNG.php';

/**
 * Класс, описывающий изображение, и содержащий методы для работы с ним.
 */

class AcImage
{
	const PNG = 'image/png';
	const JPEG = 'image/jpeg';
	const GIF = 'image/gif';

	const PROPORTION = 'pr';
	const PIXELS = 'px';
	const PERCENT = '%';

	const TOP_LEFT = 0;
	const TOP_RIGHT = 1;
	const BOTTOM_RIGHT = 2;
	const BOTTOM_LEFT = 3;

	private static $correctCorners = array (0, 1, 2, 3);
	private static $cornerLogo = 2; // BOTTOM_RIGHT

	/**
	 * Путь к файлу с изображением
	 *
	 * @var string
	 */

	private $filePath;

	/**
	 * Размер (высота и ширина) изображения
	 *
	 * @var Size
	 */

	private $size;

	/**
	 * Массив с информацией об изображении,
	 * который возвращаыет функция getimagesize
	 *
	 * @var array
	 */

	private $imageInfo;

	/**
	 * Ресурс изображения
	 */

	private $resource;

	/**
	 * Цвет фона по умолчанию
	 * @var int|AcColor
	 */

	private static $backgroundColor = AcColor::WHITE;

	/**
	 * Качество по умолчанию
	 *
	 * @var int
	 */

	private static $quality = 85;

	/**
	 * Вкл/откл прозрачность
	 * @var boolean
	 */

	private static $transparency = true;

	/**
	 * Информация о поддерживаемых типах изображений
	 *
	 * @var array
	 */

	private static $gdInfo;

	/**
	 * Разрешить перезаписывание существующих файлов
	 * @var boolean
	 */

	private static $rewrite = false;

	/**
	 * Какую часть максимальную часть лого может занимать от стороны исходного изображения
	 * @var float
	 */

	private static $maxProportionLogo = 0.1;
	private static $paddingProportionLogo = 0.02;

	/**
	 * @param string путь к файлу с изображением
	 */

	protected function __construct($filePath)
	{
		if (!self::isFileExists($filePath))
			throw new FileNotFoundException();

		$this->filePath = $filePath;

		$imageInfo = $this->getImageInfo();
		if (!is_array($imageInfo))
			throw new InvalidFileException($filePath);

		$this->setSize(new Size($imageInfo[0], $imageInfo[1]));
	}

	/**
	 * Cоздаёт экземпляры классов AcImageJPG, AcImageGIF, AcImagePNG
	 * в зависимости от типа изображения.
	 *
	 * @param string путь к файлу с изображением
	 * @return AcImageJPG|AcImagePNG|AcImageGIF
	 */

	public static function createImage($filePath)
	{
		$image = new AcImage($filePath);

		if (!self::isSupportedGD())
			throw new GDnotInstalledException();

		$imageInfo = $image->getImageInfo();
		if (!is_array($imageInfo))
			throw new InvalidFileException($filePath);

		$mimeType = $imageInfo['mime'];

		switch ($mimeType)
		{
			case self::JPEG :
				return new AcImageJPG($filePath);
			case self::PNG :
				return new AcImagePNG($filePath);
			case self::GIF :
				return new AcImageGIF($filePath);
			default:
				throw new InvalidFileException($filePath);
		}
	}

	/**
	 * Сохраняет изображение в формате jpg
	 *
	 * @param string путь, по которому сохранится изображение
	 * @return AcImage
	 *
	 * @throws UnsupportedFormatException
	 * @throws FileAlreadyExistsException
	 * @throws FileNotSaveException
	 */

	public function saveAsJPG($path)
	{
		if(!AcImageJPG::isSupport())
			throw new UnsupportedFormatException();

		if (!self::getRewrite() && self::isFileExists($path))
			throw new FileAlreadyExistsException($path);

		$this->putBackground(self::$backgroundColor);
		if(!imagejpeg(self::getResource(), $path, self::getQuality()))
			throw new FileNotSaveException($path);

		return $this;
	}

	/**
	 * Сохраняет изображение в формате png
	 *
	 * @param string путь, по которому сохранится изображение
	 * @return AcImage
	 *
	 * @throws UnsupportedFormatException
	 * @throws FileAlreadyExistsException
	 * @throws FileNotSaveException
	 */

	public function saveAsPNG($path)
	{
		if(!AcImagePNG::isSupport())
			throw new UnsupportedFormatException('png');

		if (!self::getRewrite() && self::isFileExists($path))
			throw new FileAlreadyExistsException($path);

		if (!self::getTransparency())
			$this->putBackground(self::$backgroundColor);
		// php >= 5.1.2
		if(!imagePng(self::getResource(), $path, AcImagePNG::getQuality()))
			throw new FileNotSaveException($path);

		return $this;
	}

 /**
	 * Сохраняет изображение в формате GIF
	 *
	 * @param string путь, по которому сохранится изображение
	 * @return AcImage
	 *
	 * @throws UnsupportedFormatException
	 * @throws FileAlreadyExistsException
	 * @throws FileNotSaveException
	 */

	public function saveAsGIF($path)
	{
		if(!AcImageGIF::isSupportedGD())
			throw new UnsupportedFormatException();

		if (!self::getRewrite() && self::isFileExists($path))
			throw new FileAlreadyExistsException($path);;

		if(!self::getTransparency())
			$this->putBackground(self::$backgroundColor);

		if(!imagegif(self::getResource(), $path))
			throw new FileNotSaveException($path);

		return $this;
	}

	/**
	 * Вписывает изображение в рамки.
	 * Принимает размер (рамку) в которую вписывается изображение
	 * или высоту и ширину этой рамки.
	 *
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function resize() // Size $s || $width, $height
	{
		$args = func_get_args();
		if (count($args) == 2) // $width, $height
		{
			return $this->resize(new Size($args[0], $args[1]));
		}
		else if (count($args) == 1 && $args[0] instanceof Size)
		{
			$size = $args[0];
			$imageSize = $this->getSize()->getByFrame($size);

			$newImageResource = imagecreatetruecolor($imageSize->getWidth(), $imageSize->getHeight());
			imageAlphaBlending($newImageResource, false);
			imageSaveAlpha($newImageResource, true);
			imagecopyresampled($newImageResource, $this->getResource(), 0, 0, 0, 0, $imageSize->getWidth(),
				$imageSize->getHeight(), $this->getWidth(), $this->getHeight());
			$this->setResource($newImageResource);
			$this->setSize($imageSize);
			return $this;
		}
		throw new IllegalArgumentException();
	}

	/**
	 * Ужимает изображение по <i>ширине</i>.
	 *
	 * @param int ширина рамки
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function resizeByWidth($width)
	{
		if (!is_int($width) || $width <= 0)
			throw new IllegalArgumentException();

		return $this->resize($width, $this->getHeight());
	}

	/**
	 * Ужимает изображение по <i>высоте</i>.
	 *
	 * @param int высота рамки
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function resizeByHeight($height)
	{
		if (!is_int($height) || $height <= 0)
			throw new IllegalArgumentException();

		return $this->resize($this->getWidth(), $height);
	}

	/**
	 * Производит кроп изображения, то есть
	 * вырезает из него произвольную прямоугольную область.
	 * Метод принимает либо вырезаемый прямоугольник, либо его параметры.
	 *
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function crop() // Rectangle $rect || $x, $y, $w, $h
	{
		$a = func_get_args();

		if (count($a) == 4)
			$rect = new Rectangle($a[0], $a[1], $a[2], $a[3]);
		else if (count($a) == 1 && $a[0] instanceof Rectangle)
			$rect = $a[0];
		$rect = $rect->getIntersectsWith(new Rectangle(new Point(0, 0), $this->getSize()));

		if (!$rect)
			throw new IllegalArgumentException();

		$width = $rect->getWidth();
		$height = $rect->getHeight();
		$x = $rect->getLeft();
		$y = $rect->getTop();

		if ($width == 0 || $height == 0)
			throw new IllegalArgumentException();

		$newImageResource = imagecreatetruecolor($width, $height);
		imageAlphaBlending($newImageResource, false);
		imageSaveAlpha($newImageResource, true);
		imagecopyresampled($newImageResource, $this->getResource(), 0, 0, $x, $y, $width, $height, $width, $height);
		$this->setResource($newImageResource);
		$this->setSize($rect->getSize());
		return $this;
	}

	/**
	 * Вырезает произвольный квадрат из изображения.
	 * Метод может принимать вырезаемый прямоугольник, обязанный быть квадратом,
	 * либо параметры для создания такого прямоугольника.
	 *
	 * @throws IllegalArgumentException
	 * @return AcImage
	 */

	public function cropSquare() // Rectnagle $square || Point $p, $a || $x, $y, $a
	{
		$a = func_get_args();
		if (count($a) == 1 && $a[0] instanceof Rectangle && $a[0]->isSquare())
		{
			$square = $a[0];
		}
		else if (count($a) == 2 && $a[0] instanceof Point && is_int($a[1]))
		{
			$square = new Rectangle($a[0], new Size($a[1], $a[1]));
		}
		else if(count($a) == 3)
		{
			$square = new Rectangle(new Point($a[0], $a[1]), new Size($a[2], $a[2]));
		}
		else
		{
			throw new IllegalArgumentException();
		}

		if (!$square->isInner(new Rectangle(new Point(0, 0), $this->getSize())))
			throw new IllegalArgumentException();

		return $this->crop($square);
	}

	/**
	 * Вырезает центральную область изображения.
	 * Принимает высоту и ширину вырезаемой области.
	 *
	 * @param int|string ширина вырезаемой области
	 * @param int|string высота вырезаемой области
	 *
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function cropCenter($width, $height) // int, int || string, string || int, string || string, int
	{
		$result = self::parseCropCenterArg($width);
		$widthUnits = $result['units'];
		$width = $result['value'];

		$result = self::parseCropCenterArg($height);
		$heightUnits = $result['units'];
		$height = $result['value'];

		// true тогда и только тогда если слева и справа от xor разные значения
		if ($widthUnits == self::PROPORTION xor $heightUnits == self::PROPORTION)
			throw new IllegalArgumentException();

		if ($widthUnits == self::PERCENT)
			$width = self::percentToPixels($width, $this->getWidth());

		if ($heightUnits == self::PERCENT)
			$height = self::percentToPixels($height, $this->getHeight());

		if ($widthUnits == self::PROPORTION)
		{
			$size = $this->getSizeByProportion($width, $height);

			$width = $size->getWidth();
			$height = $size->getHeight();
		}

		$width = (int)min($width, $this->getWidth());
		$height = (int)min($height, $this->getHeight());

		$imageRect = new Rectangle(0, 0, $this->getWidth(), $this->getHeight());
		$cropRect = new Rectangle(0, 0, $width, $height);
		$cropRect = $cropRect->center($imageRect);

		return $this->crop($cropRect);
	}

	/**
	 * @ignore
	 */

	private static function percentToPixels($value, $imageSide)
	{
		return (int)round(($imageSide / 100 * $value));
	}

	/**
	 * @ignore
	 */

	private function getSizeByProportion($width, $height)
	{
		$imageWidth = $this->getWidth();
		$imageHeight = $this->getHeight();

		if($height / $imageHeight > $width / $imageWidth)
		{
			return new Size((int)round($imageHeight / $height * $width), $imageHeight);
		}
		else
		{
			return new Size($imageWidth, (int)round($imageWidth / $width * $height));
		}
	}

	/**
	 * @ignore
	 */

	private static function parseCropCenterArg($arg)
	{
		$pattern = '/^(\d+(\.\d+)*)(px|\%|pr)$/'; // in constants?

		$matches = array();
		if (is_int($arg))
		{
			$units = self::PIXELS;
			$value = $arg;
		}
		else if (preg_match($pattern, $arg, $matches))
		{
			$units = $matches[3]; // (px|\%|pr)
			$value = $matches[1]; // (\d+(\.\d+)*)
		}
		else
		{
			throw new IllegalArgumentException();
		}
		return array (
			'units' => $units,
			'value' => $value
		);
	}

	/**
	 * "Умное" создание миниатюр.
	 *
	 * @param int ширина
	 * @param int высота
	 * @param float коэффициент превышения.
	 * @throws IllegalArgumentException
	 * @return AcImage
	 */

	public function thumbnail($width, $height, $c = 2) // $width, $height, [$c]
	{
		if ($c <= 1 || !is_finite($c) || $width <= 0 || $height <= 0)
			throw new IllegalArgumentException();

		$size = new Size($width, $height);

		if($this->getSize()->lessThen($size))
			$size = $this->getSize();

		$imageSize = $this->getSize();

		$isRotate = false;
		if($size->getWidth() / $imageSize->getWidth() <= $size->getHeight() / $imageSize->getHeight())
		{
			$size->flip();
			$imageSize->flip();
			$isRotate = true;
		}

		$width = $size->getWidth();
		$height = $size->getHeight();

		$imageWidth = $imageSize->getWidth();
		$imageHeight = $imageSize->getHeight();

		$lim = (int)($c * $height);
		$newHeight = (int)($imageHeight * $width / $imageWidth);

		if ($imageWidth > $width)
		{
			if($newHeight <= $lim)
			{
				$size = new Size($width, $newHeight);
			}
			else
			{
				if($newHeight <= 2 * $lim)
				{
					$size = new Size((int)($imageWidth * $lim / $imageHeight), $lim);
				}
				else
				{
					$size = new Size((int)($width / 2), (int)($imageHeight * $width / $imageWidth));
				}
			}
		}
		else
		{
			if($imageHeight <= $lim)
			{
				$size = $this->getSize();
			}
			else
			{
				if($imageHeight <= 2 * $lim)
				{
					if($imageWidth * $lim / $imageHeight >= $width / 2)
					{
						$size = new Size((int)($imageWidth * $lim / $imageHeight), $lim);
					}
					else
					{
						$size = new Size((int)($width / 2), (int)($imageHeight * $width / ($imageWidth * 2)));
					}
				}
				else
				{
					$size = new Size((int)($width / 2), (int)($imageHeight * $width / ($imageWidth * 2)));
				}
			}
		}
		if ($isRotate)
		{
			$size->flip();
			$imageSize->flip();
		}
		return $this->resize($size);
	}

	/**
	 * Наносит лого на изображение.
	 *
	 * @param mixed
	 * @param int номер угла, в котором будет размещенно лого<br />
	 * 0 1<br />
	 * 2 3
	 *
	 * @see AcImage::TOP_LEFT
	 * @see AcImage::TOP_RIGHT
	 * @see AcImage::BOTTOM_LEFT
	 * @see AcImage::BOTTOM_RIGHT
	 *
	 * @return AcImage
	 * @throws IllegalArgumentException
	 */

	public function drawLogo($logo, $corner = null) // string $logo [$corner] || AcImage $logo [$corner]
	{
		if (is_null($corner))
			$corner = self::$cornerLogo;

		if (!AcImage::isCorrectCorner($corner))
			throw new IllegalArgumentException();

		if (is_string($logo))
			$logo = AcImage::createImage($logo);

		if (!($logo instanceof AcImage))
			throw new IllegalArgumentException();

		$maxWidthLogo = (int)($this->getWidth() * self::$maxProportionLogo);
		$maxHeightLogo = (int)($this->getHeight() * self::$maxProportionLogo);

		$logo->resize($maxWidthLogo, $maxHeightLogo);

		if (!self::getTransparency())
			$logo->putBackground(self::$backgroundColor);

		imagealphablending($this->getResource(), true);
		$logoSize = $logo->getSize();

		$location = $this->getLogoPosition($corner, $logoSize->getWidth(), $logoSize->getHeight());
		imagecopy($this->getResource(), $logo->getResource(), $location->getX(), $location->getY(), 0, 0,
						$logoSize->getWidth(), $logoSize->getHeight());

		return $this;
	}

	/**
	 * @ignore
	 */

	private function getLogoPosition($corner, $width, $height)
	{
		$paddingX = $this->getWidth() * self::$paddingProportionLogo;
		$paddingY = $this->getHeight() * self::$paddingProportionLogo;


		if ($corner == self::BOTTOM_RIGHT || $corner == self::BOTTOM_LEFT)
			$y = $this->getHeight() - $paddingY - $height;
		else
			$y = $paddingY;

		if ($corner == self::BOTTOM_RIGHT  || $corner == self::TOP_RIGHT)
			$x = $this->getWidth() - $paddingX - $width;
		else
			$x = $paddingX;

		return new Point((int)$x, (int)$y);
	}

	/**
	 * @ignore
	 */

	private static function isCorrectCorner($corner)
	{
		return in_array($corner, self::$correctCorners);
	}

	/**
	 * Проверяет, существует ли файл.
	 *
	 * @param string путь к файлу
	 * @return boolean
	 */

	public static function isFileExists($filePath)
	{
		if (@file_exists($filePath))
			return true;

		if(!preg_match("|^http(s)?|", $filePath))
			return false;

		$headers = @get_headers($filePath);
		if(preg_match("|200|", $headers[0]))
			return true;

		return false;
	}

	/**
	 * Проверяет, является ли файл изображением.
	 *
	 * @param string путь к файлу
	 * @return boolean
	 */

	public static function isFileImage($filePath)
	{
		if (!self::isFileExists($filePath))
			return false;

		$imageInfo = @getimagesize($filePath);
		return is_array($imageInfo);
	}

	/**
	 * @ignore
	 */

	protected function putBackground()
	{
		$newImageResource = imagecreatetruecolor($this->getWidth(), $this->getHeight());
		imagefill($newImageResource , 0, 0, self::getBackgroundColor()->getCode());
		imagecopyresampled($newImageResource , $this->getResource(), 0, 0, 0, 0,
			$this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
		$this->setResource($newImageResource);
	}

	/**
	 * @param boolean
	 * @throws IllegalArgumentException
	 */

	public static function setRewrite($mode)
	{
		if (!is_bool($mode))
			throw new IllegalArgumentException();

		self::$rewrite = $mode;
	}

	public static function getRewrite()
	{
		return self::$rewrite;
	}

	public function getImageInfo()
	{
		if (!isset($this->imageInfo))
			$this->imageInfo = @getimagesize($this->getFilePath());

		return $this->imageInfo;
	}

	/**
	 * Возвращает две первые цифры
	 * версии php, разделённые точкой.
	 * Например: 5.2, 5.3
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */

	public static function getShortPHPVersion()
	{
		$matches = array();
		preg_match("@^\d\.\d@", phpversion(), $matches);
		return $matches[0];
	}

	public static function isSupportedGD()
	{
		return function_exists('gd_info');
	}

	/**
	 *
	 * Возвращает результат работы функции gd_info()
	 * или false если библиотека gd не доступна
	 *
	 * @return array|bool
	 */

	public static function getGDinfo()
	{
		if (!self::isSupportedGD())
			return false;

		if (!isset(self::$gdInfo))
			self::$gdInfo = gd_info();

		return self::$gdInfo;
	}

	public function getFilePath()
	{
		return $this->filePath;
	}

	private function setSize(Size $s)
	{
		$this->size = $s;
	}

	public function getSourceImage()
	{
		return $this->sourceImage;
	}

	public function getMimeType()
	{
		$imageInfo = $this->getImageInfo();
		return $imageInfo['mime'];
	}

	public function getSize()
	{
		return clone $this->size;
	}

	public function getWidth()
	{
		return $this->getSize()->getWidth();
	}

	public function getHeight()
	{
		return $this->getSize()->getHeight();
	}

	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @ignore
	 */

	protected function setResource($resource)
	{
		return $this->resource = $resource;
	}

	// static getters and setters

	/**
	 *
	 * @param int качество изображения от 0 до 100
	 * @throws IllegalArgumentException
	 */

	public static function setQuality($q)
	{
		$q = (int)$q;
		if (!is_integer($q) || $q <= 0 || $q > 100)
			throw new IllegalArgumentException();

		self::$quality = $q;
	}

	public static function getQuality()
	{
		return self::$quality;
	}

	/**
	 *
	 * @param boolean
	 * @throws IllegalArgumentException
	 */

	public static function setTransparency($mode)
	{
		if(!is_bool($mode))
			throw new IllegalArgumentException();

		self::$transparency = $mode;
	}

	public static function getTransparency()
	{
		return self::$transparency;
	}

	public static function setBackgroundColor() // $color || $r, $r, $b || $code
	{
		$a = func_get_args();
		if (count($a) == 1)
		{
			if ($a[0] instanceof AcColor)
			{
				self::$backgroundColor = $a[0];
			}
			else
			{
				self::$backgroundColor = new AcColor($a[0]);
			}
		}
		else if (count($a) == 3)
		{
			self::$backgroundColor = new AcColor($a[0], $a[1], $a[2]);
		}
		else
		{
			throw new IllegalArgumentException();
		}
	}

	public static function getBackgroundColor()
	{
		if (is_integer(self::$backgroundColor))
			self::$backgroundColor = new AcColor(self::$backgroundColor);

		return self::$backgroundColor;
	}

	public function getCornerLogo()
	{
		return self::$cornerLogo;
	}

	/**
	 *
	 * @param int номер угла изображения<br />
	 * 0 1<br />
	 * 2 3
	 *
	 * @see AcImage::TOP_LEFT
	 * @see AcImage::TOP_RIGHT
	 * @see AcImage::BOTTOM_RIGHT
	 * @see AcImage::BOTTOM_LEFT
	 *
	 * @throws IllegalArgumentException
	 */

	public function setCornerLogo($corner)
	{
		if(!self::isCorrectCorner($corner))
			throw new IllegalArgumentException();

		self::$cornerLogo = $corner;
	}

	/**
	 *
	 * @param float от 0 <= $maxPropotionsLogo < 1
	 * @throws IllegalArgumentException
	 */

	public static function setMaxProportionLogo($maxPropotionsLogo)
	{
		if (!is_float($maxPropotionsLogo) || $maxPropotionsLogo > 1 ||
						$maxPropotionsLogo <= 0)
			throw new IllegalArgumentException();

		self::$maxProportionLogo = $maxPropotionsLogo;
	}

	public static function getMaxProportionLogo()
	{
		return self::$maxProportionLogo;
	}

	/**
	 *
	 * @param float от 0 <= $paddingProportionLogo < 1
	 * @throws IllegalArgumentException
	 */

	public static function setPaddingProportionLogo($paddingProportionLogo)
	{
		if (!is_float($paddingProportionLogo) || $paddingProportionLogo > 1 ||
						$paddingProportionLogo <= 0)
			throw new IllegalArgumentException();

		self::$paddingProportionLogo = $paddingProportionLogo;
	}

	public static function getPaddingProportionLogo()
	{
			return self::$paddingProportionLogo;
	}
}
?>