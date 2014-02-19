<?php

/*
 * Нанесение лого
 */

require_once '../AcImage.php';

//$filePath = 'img/4na3.jpg';
$savePath = 'out/'.rand(0, 1000).'.jpg';
$filePath = 'img/16na9.jpg';

$image = AcImage::createImage($filePath);

$image
	->drawLogo('img/logo.png')
	->save($savePath);

?>

<h3>Оригинал</h3>
<img src="<?=$filePath; ?>" />

<h3>С лого</h3>
<img src="<?=$savePath; ?>" />
