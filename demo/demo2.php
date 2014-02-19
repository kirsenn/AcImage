<?php

/**
 * Квадратный кроп
 */

require_once '../AcImage.php';

//$filePath = 'img/4na3.jpg';
$savePath = 'out/'.rand(0, 1000).'.jpg';
$filePath = 'img/16na9.jpg';

$x = 100;
$y = 50;

$side = 200;

$image = AcImage::createImage($filePath);

$image
	->cropSquare($x, $y, $side)
	->save($savePath);

?>

<h3>Оригинал</h3>
<img src="<?=$filePath; ?>" />

<h3>Квадратный кроп</h3>
<img src="<?=$savePath; ?>" />