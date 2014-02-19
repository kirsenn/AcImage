<?php

/*
 * Умное создание миниатюр
 */

require_once '../AcImage.php';

//$filePath = 'img/visokaya.jpg';
$savePath = 'out/'.rand(0, 1000).'.jpg';
$filePath = 'img/dlinnaya.jpg';


$width = 400;
$height = 300;

$image = AcImage::createImage($filePath);

$image
	->thumbnail($width, $height)
	->save($savePath);

?>

<h3>Оригинал</h3>
<img src="<?=$filePath; ?>" />

<h3>Умная, просто генеальная, миниатюра =)</h3>
<div style="background: url(<?=$savePath; ?>) center gray no-repeat; width: <?=$width; ?>; height: <?=$height; ?>">
</div>