<?php
include_once("class.db.php");
include_once("class.record.php");
include_once("class.recordBio.php");
include_once("class.encryption.php");
		 
$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$BIO = new recordBio($DB);
$ENC = new encryption();

// {FILE}|{WIDTH}|{HEIGHT} //
//echo $_GET['img'];
$imgBase = $ENC->decrypt($_GET['img']);
//echo "BASE:".$imgBase."<br>\n";
$imgParts = explode("|", $imgBase);
//print_r($imgParts);


$x=$imgParts[1]; 
$y=$imgParts[2];
$img_path = $imgParts[0];
$ratio_thumb=$x/$y; // ratio thumb		
$thumb = imagecreatetruecolor($x, $y);
$source = imagecreatefromjpeg($img_path);
//list($owidth, $oheight) = getimagesize($image_path);

list($xx, $yy) = getimagesize($img_path); // original size
$ratio_original=$xx/$yy; // ratio original

if ($ratio_original>=$ratio_thumb) {
	$yo=$yy; 
	$xo=ceil(($yo*$x)/$y);
	$xo_ini=ceil(($xx-$xo)/2);
	$xy_ini=0;
} else {
	$xo=$xx; 
	$yo=ceil(($xo*$y)/$x);
	$xy_ini=ceil(($yy-$yo)/2);
	$xo_ini=0;
}
imagecopyresampled($thumb, $source, 0, 0, $xo_ini, $xy_ini, $x, $y, $xo, $yo);	
//imagecopyresampled($thumb, $source, 0, 0, 0, 0, $x, $y, $xo, $yo);

$stamp = imagecreatefrompng('ka-logo-watermark.png');
// Set the margins for the stamp and get the height/width of the stamp image
$marge_right = 5;
$marge_bottom = 5;
$sx = imagesx($stamp);
$sy = imagesy($stamp);
// Copy the stamp image onto our photo using the margin offsets and the photo 
// width to calculate positioning of the stamp. 
imagecopy($thumb, $stamp, imagesx($thumb) - $sx - $marge_right, imagesy($thumb) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));


//ob_start(); // Let's start output buffering.
header('Content-type: image/jpg');
imagejpeg($thumb);
imagedestroy($im);
 //This will normally output the image, but because of ob_start(), it won't.
//$contents = ob_get_contents(); //Instead, output above is saved to $contents
//ob_end_clean(); //End the output buffer.
//$dataUri = "data:image/jpeg;base64," . base64_encode($contents); 
//return $dataUri;
?>