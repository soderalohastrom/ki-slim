<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: image/jpeg");
$im = imagecreatetruecolor(250, 35); 
$white = ImageColorAllocate($im, 255, 255, 255);
imagefill($im, 0, 0, $white);
$black = ImageColorAllocate($im, 0, 0, 0);
$start_x = 10;
$start_y = 25;
if(isset($_GET['font'])) {
	if($_GET['font'] == 1) {
		$font = dirname(__FILE__) .'/simpleSignature.ttf';
	} else {
		$font = dirname(__FILE__) .'/Geovana.ttf';
	}
} else {
	$font = dirname(__FILE__) .'/Geovana.ttf';
}
imagettftext($im, 16, 0, $start_x, $start_y, $black, $font, urldecode($_GET['s']));
imagejpeg($im, null, 100);
ImageDestroy($im);
?>