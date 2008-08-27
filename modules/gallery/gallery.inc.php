<?php
if (isset($_GET['i'])){ //Thumbs
	$filename = $_GET['i'];
	$size = $_GET['size'];
	//Path info
	$path = explode(".", $_GET['i']); //Array of the Path + Extension
	$ext = array_pop($path); // File Extension
	$thumb = '../../' . implode($path); //Path minus extension - Ends in /
	if(!file_exists($thumb."_tn_".$size.".".$ext)) {
		// Set a maximum height and width
		$width  = $size;
		$height = $size;
		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($filename);
		if ($width && ($width_orig < $height_orig)) {
			$width = ($height / $height_orig) * $width_orig;
		} else {
			$height = ($width / $width_orig) * $height_orig;
		}
		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		$image   = imagecreatefromjpeg($filename);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
		imagejpeg($image_p, $thumb."_tn_".$size.".".$ext);
		imagedestroy($image);
		imageDestroy($image_p);
	}
	// Content type
	header('Content-type: image/jpeg');
	$fp = fopen($thumb."_tn_".$size.".".$ext, "r");
	echo fread($fp, filesize($thumb."_tn_".$size.".".$ext));
	fclose($fp);
}elseif (isset($_GET['path'])){ //Stripes
	$path = '../../' . $_GET['path'];
	$width = $_GET['width'];
	$height = $_GET['height'];
	//Look for images in directory
	$images = array();
	$dir = dir($path);
	while($file = $dir->read()) {
		if($file != "." && $file != ".." && $file != ".svn" && !preg_match('/_tn_/', $file)) {
			array_push($images, $file);
	  	}
	}
	$dir->close();
	$cachename = $path.'/cache_tn_'.md5(join('', $images))."_".$width."_".$height.".jpg";
	if(!file_exists($cachename)) {
		$stripe = imagecreatetruecolor($width * count($images), $height);
		
		$i = 0;
		foreach($images as $filename) {
			$image   = imagecreatefromjpeg($path."/".$filename);
			imagecopyresampled($stripe, $image, $i++ * $width, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
		}
		
		imagejpeg($stripe, $cachename);
		imagedestroy($stripe);
	}
	// Content type
	header('Content-type: image/jpeg');
	readfile($cachename);
}
?>