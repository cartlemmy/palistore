<?php

$imageFile = array_pop(explode("/",$_SERVER["REQUEST_URI"]));
$image = explode(".", $imageFile);
$ext = strtolower(array_pop($image));

$width = $height = false;
$res = $image[count($image)-1];
if (preg_match("/(\d+w|\d+h)/",$res)) {	
	array_pop($image);
	if (substr($res,-1) == "w") {
		$width = min(1024,(int)substr($res,0,-1));
	} else {
		$height = min(768,(int)substr($res,0,-1));
	}
}

$nameSafe = implode(".",$image);

$cfg = require("../inc/store/config.php");

$storeImgDir = getcwd();

chdir("../../");

if (!in_array($res,$cfg["allowedImageSize"])) {
	show404();
}
	
$slSetupMode = false;
$GLOBALS["slConfig"] = require("inc/config.php");

require("inc/initialize.php");

if ($res = $GLOBALS["slCore"]->db->select($cfg["table"]["item"],array("nameSafe"=>$nameSafe))) {
	$item = $GLOBALS["slCore"]->db->fetch($cfg["table"]["item"]);
	
	$c = explode(";",$item["image"],6);
	
	switch ($ext) {
		case "gif":
			$ctype="image/gif"; break;
			
		case "png":
			$ctype="image/png"; break;
			
		case "jpeg": case "jpg":
			$ctype="image/jpg"; break;
			
		default:
			show404();
	}
	$file = SL_DATA_PATH."/users/".$cfg["user"]."/file/image/".$c[3].".".array_pop(explode("/",$c[1]));
	
	if ($width || $height) {
		$cacheFile = $storeImgDir."/cache/".$imageFile;
		if (!(is_file($cacheFile) && filemtime($cacheFile) > filemtime($file))) {		
			switch ($ext) {
				case "gif":
					$im = imagecreatefromgif($file);
					break;
					 
				case "png":
					$im = imagecreatefrompng($file);
					break;
					
				case "jpeg": case "jpg":
					$im = imagecreatefromjpeg($file);
					break;
					
				default:
					show404();
			}
			
			
			if ($width) {
				$height = round($width * (imagesy($im) / imagesx($im)));
			} else {
				$width = round($height * (imagesx($im) / imagesy($im)));
			}
			$newIm = imagecreatetruecolor($width,$height);
			
			imagecopyresampled( 
				$newIm, $im, 
				0, 0, 0, 0,
				$width, $height, imagesx($im), imagesy($im)
			);

			switch ($ext) {
				case "gif":
					imagegif($newIm,$cacheFile);
					break;
					 
				case "png":
					imagepng($newIm,$cacheFile);
					break;
					
				case "jpeg": case "jpg":
					imagejpeg($newIm,$cacheFile);
					break;
					
				default:
					show404();
			}
			imagedestroy($im);
			imagedestroy($newIm);
		}		
		header('Content-type: '.$ctype);
		readfile($cacheFile);
	} else {
		header('Content-type: '.$ctype);
		readfile($file);
	}
} else {
	show404();
}

function show404() {
	global $storeImgDir;

	header('Content-type: image/png');
	readfile($storeImgDir."/404.png");
	
	exit();
}
