<?php 
//module install function copies all .tpl files to templates folder?
class Mod_Gallery{

	function Display() {
	BaseController::AddCss('modules/gallery/CSS/style.css');
	BaseController::AddJs('modules/gallery/JS/unpacked.js');
	// Get the pictures
	global $smarty;
		$albums = array();
		$album_dir = dir(UPLOAD_PATH . "gallery");
		$i = 0;
		while($file = $album_dir->read()) {
			if($file != "." && $file != ".." && $file != ".svn") {
				$images = array();
				$sub_dir = dir(UPLOAD_PATH . "gallery/" .$file);
				while($image = $sub_dir->read()) {
					if($image != "." && $image != ".." && $image != ".svn" && !preg_match('/_tn_/', $image)) array_push($images, $image);
				}
				$sub_dir->close();
				array_push($albums, array('id' => $i++, 'name' => $file, 'path' => UPLOAD_PATH . "gallery/".$file, 'images' => $images, 'length' => count($images)));
		  	}
		}
		$album_dir->close();
		$smarty->assign('albums',$albums);
	}
	


}
