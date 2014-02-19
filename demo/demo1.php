<?php

/**
 * Произвольный кроп
 */

require_once '../AcImage.php';

$filePath = 'img/4na3.jpg';
$savePath = 'out/'.rand(0, 1000).'.jpg';
//$filePath = 'img/16na9.jpg';

$x = 100;
$y = 50;

$width = 200;
$height = 150;

$image = AcImage::createImage($filePath);

$image
	->crop($x, $y, $width, $height)
	->save($savePath);

?>

<h3>Оригинал</h3>
<img src="<?=$filePath; ?>" />

<h3>Кроп</h3>
<img src="<?=$savePath; ?>" />
