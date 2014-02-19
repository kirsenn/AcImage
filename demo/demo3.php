<?php

/*
 * Центральный пропорциональный скроп
 */

require_once '../AcImage.php';

//$filePath = 'img/4na3.jpg';
$savePath = 'out/'.rand(0, 1000).'.jpg';
$filePath = 'img/16na9.jpg';


$width = '4pr';
$height = '3pr';

$image = AcImage::createImage($filePath);

$image
	->cropCenter($width, $height)
	->save($savePath);

?>

<h3>Оригинал</h3>
<img src="<?=$filePath; ?>" />

<h3>Центральный пропорциональный кроп</h3>
<img src="<?=$savePath; ?>" />
