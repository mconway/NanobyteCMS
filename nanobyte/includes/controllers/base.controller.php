<?php

class BaseController{
	public static function Redirect($page=null,$ajax=false){
		if ($page && !$ajax){ 
			header("location: " . Core::Url($page), true, 303);
		}elseif ($page && $ajax){ 
			header("location: " . Core::Url($page).'/ajax', true, 303);
		}elseif(!isset($page) && !$ajax){
			header("location: ".$_SERVER['HTTP_REFERER'], true, 303); 
		}elseif(!isset($page) && $ajax){
			header("location: ".$_SERVER['HTTP_REFERER'].'/ajax', true, 303); 
		}
		exit;
	}
	
	public static function DisplayMessages(){
		global $smarty;
		if(isset($_SESSION['messages'])){
			$messages = Core::getMessages();
			$smarty->assign('messages', $messages);
			return $smarty->fetch('messages.tpl');
		}else{
			return false;
		}
	}
	
	public static function strleft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2)); 
	}

	public static function paginate($limit=15,$total,$filePath,$currentPage) {
		$allPages = ceil($total/$limit);
		$pagination = "";
		$output = '';
		if ($allPages>10) {
			$maxPages = ($allPages>9) ? 9 : $allPages;
			if ($allPages>9) {
				if ($currentPage>=1&&$currentPage<=$allPages) {
					$pagination .= ($currentPage>4) ? " ... " : " ";
					$minPages = ($currentPage>4) ? $currentPage : 5;
					$maxPages = ($currentPage<$allPages-4) ? $currentPage : $allPages - 4;
					for($i=$minPages-4; $i<$maxPages+5; $i++) {
						$pagination .= ($i==$currentPage) ? Core::l($i, '#')
						: Core::l($i,$filePath.$i);
					}
					$pagination .= ($currentPage<$allPages-4) ? " ... " : " ";
				} else {
					$pagination .= " ... ";
				}
			}
		} else {
			for($i=1; $i<$allPages+1; $i++) {
				$pagination .= ($i==$currentPage) ? Core::l($i, '#')
				: Core::l($i,$filePath.$i);
			}
		}

		if($currentPage>1 && $currentPage<$allPages){
			$output .= Core::l('FIRST', $filePath.'1').' '.Core::l('<',$filePath.($currentPage-1)).' '.$pagination.' '. Core::l('>',$filePath.($currentPage + 1)).' '.Core::l('LAST', $filePath.$allPages);
		}
		elseif ($currentPage>1){
			$output .= Core::l('FIRST', $filePath.'1').' '.Core::l('<',$filePath.($currentPage-1)).' '.$pagination;
		}
		elseif ($currentPage<$allPages){
			$output .= $pagination .' '. Core::l('>',$filePath.($currentPage + 1)).' '.Core::l('LAST', $filePath.$allPages);
		}
	
		return $output;
	}
	
	public function GetStart($page,$limit){
		if (!$page || $page == ""){$page = 1; } 
		$start = (($page - 1) * $limit) ;
		return $start;
	}
	
	public static function VerifyFile($file){
		$filename = $file['name'];
		$ext = substr($filename, strripos($filename, '.')); // Get the extension from the filename.
		$allowed_filetypes = explode(', ',FILE_TYPES);
		if(!in_array($ext,$allowed_filetypes)){
			$error = 'The file you attempted to upload is not allowed. Valid file types are:'.FILE_TYPES;
		}
		// Now check the filesize, if it is too large then inform the user.
		if(filesize($file['tmp_name']) > FILE_SIZE){
			$error = 'The file you attempted to upload is too large.';
		}
		// Check if we can upload to the specified path, if not, inform the user.
		if(!is_writable(UPLOAD_PATH)){
			$error = 'You cannot upload to the specified directory, please CHMOD it to 777.';
		}
		return isset($error) ? $error : false;
	}
	
	public static function AddCss($file=null, $media='all'){
		static $cssFiles = array();
		if($file != null){
			$cssFiles[] = $file;
		}
		return $cssFiles;
	}
	
 	public static function AddJs($file=null){
 		static $jsFiles;
		if($file != null){
			$jsFiles[] = $file;
		}
		return $jsFiles;
 	}
	
	public static function GetHTMLIncludes(){
		global $smarty;
		if(COMPRESS){
			$smarty->assign(array('js'=>self::CompressFiles(self::AddJs(),'js'),'css'=>self::CompressFiles(self::AddCss(),'css')));
		}else{
			$smarty->assign(array('js'=>self::AddJs(),'css'=>self::AddCss()));
		}
	}
	
	public static function HandleImage($image,$resize=null){
		$filename = $image['name'];
		$error = BaseController::VerifyFile($image);
		if ($error == false){
			if (Core::FileUpload($image) == true){
				Core::SetMessage('Your file upload was successful, view the file <a href="' . UPLOAD_PATH . $filename . '" title="Your File">here</a>','info');
			}else{
				Core::SetMessage('There was an error during the file upload.  Please try again.','error');
			}
		}else{
			Core::SetMessage($error, 'error');
		}
		if ($resize){
			$images = self::ResizeImage($image, $resize);
			return '<a class="postImage" href="'.$images['orig'].'"><img src="'.$images['thumb'].'"/></a>';
		}else{
			return '<img src="'.UPLOAD_PATH.$filename.'" />';
		}
		
	}
	
	public static function ResizeImage($image, $thumb_x){
		$imagepath = UPLOAD_PATH.$image['name'];
		//open original (uploaded) image, based on type.
		switch($image['type']) {
			case 'image/jpeg':
			case 'image/pjpeg':
				$orig = imagecreatefromjpeg($imagepath);
				$name = str_replace('.jpg', '', strtolower($image['name']));
				break;
			case 'image/png':
				$orig = imagecreatefrompng($imagepath);
				$name = str_replace('.png', '', strtolower($image['name']));
				break;
			case 'image/gif':
				$orig = imagecreatefromgif($imagepath);
				$name = str_replace('.gif', '', strtolower($image['name']));
				break;
			case 'image/bmp':
				$orig = imagecreatefromwbmp($imagepath);
				$name = str_replace('.bmp', '', strtolower($image['name']));
				break;
			default:
				Core::SetMessage('Unknown File Format or MIME Type. The Your file is: '.$image['type'],'error');
		}
		$base = UPLOAD_PATH . $name;
		if ($orig){
			//fetch the size of the original and overlay images,and calculate the size of the new image and thumb.
			$orig_x = imagesx($orig);
			$orig_y = imagesy($orig);
			$thumb_y = round(($orig_y * $thumb_x) / $orig_x);
			
			// create the thumb image, and scale the original into it.
			$thumb = imagecreatetruecolor($thumb_x, $thumb_y);
			imagecopyresampled($thumb, $orig, 0, 0, 0, 0, $thumb_x, $thumb_y, $orig_x, $orig_y);

			//write the 2 images to disk.
			imagepng($thumb, $base . '-thumb.png');
			
		}else{
			Core::SetMessage('Unable to Open '.$image['name'].' for modification','error');
		}
		$uri['orig'] = $imagepath;
		$uri['thumb'] = $base . '-thumb.png';
	    return $uri;
	}
	
	public static function GetThemeList(){
		$dirs = glob('templates/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$dir = str_replace('templates/','',$dir);
			$theme[$dir] = ucfirst($dir); 
		}
		return $theme;
	}
	
	public static function CompressFiles($fileArray,$type){
		$cache 	  = true;
		$cachedir = UPLOAD_PATH . 'cache';
	// Determine last modification date of the files
		$lastmodified = 0;
		while(list(,$element) = each($fileArray)){
			$path = realpath($element);
//			if(($type == 'js' && substr($path, -3) != '.js') || ($type == 'css' && substr($path, -4) != '.css')){
//				header ("HTTP/1.0 403 Forbidden");
//				exit;	
//			}
			$lastmodified = max($lastmodified, filemtime($path));
		}
	
	// Send Etag hash
		$hash = $lastmodified . '-' . md5(implode(',',$fileArray));
//		header ("Etag: \"" . $hash . "\"");
	
//		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') {
//			// Return visit and no modifications, so do not send anything
//			header ("HTTP/1.0 304 Not Modified");
//			header ('Content-Length: 0');
//		}else{
			// First time visit or files were modified
			if($cache) 
			{
				// Determine supported compression method
				$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
				$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');
		
				// Determine used compression method
				$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');
		
				// Check for buggy versions of Internet Explorer
				if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && 
					preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
					$version = floatval($matches[1]);
					
					if ($version < 6)
						$encoding = 'none';
						
					if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) 
						$encoding = 'none';
				}
				
				// Try the cache first to see if the combined files were already generated
//				$cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');
				$cachefile = 'cache-' . $hash . '.' . $type;
//				if (file_exists($cachedir . '/' . $cachefile)) {
//					if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {
//	
//						if ($encoding != 'none') {
//							header ("Content-Encoding: " . $encoding);
//						}
//					
//						header ("Content-Type: text/" . $type);
//						header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));
//			
//						fpassthru($fp);
//						fclose($fp);
//						exit;
//					}
//				}
			}
		
			// Get contents of the files
			$contents = '';
			reset($fileArray);
			while (list(,$element) = each($fileArray)) {
				$path = realpath($element);
//				var_dump($path);
				$contents .= "\n\n" . file_get_contents($path);
			}
			
			// Send Content-Type
//			header ("Content-Type: text/" . $type);
//			
//			if (isset($encoding) && $encoding != 'none') 
//			{
//				// Send compressed contents
//				$contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
				$contents = gzcompress($contents, 9);
//				header ("Content-Encoding: " . $encoding);
//				header ('Content-Length: ' . strlen($contents));
//				echo $contents;
//			} 
//			else 
//			{
//				// Send regular contents
//				header ('Content-Length: ' . strlen($contents));
//				echo $contents;
//			}
	
			// Store cache
			
			if ($cache) {
				if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
					fwrite($fp, $contents);
					fclose($fp);
				}
//				var_dump($cachedir . '/' . $cachefile);
//			}
			return $cachedir . '/' . $cachefile;
		}
	}

	public static function GetThemeIncludes(){
		$themePathArray = explode('/',THEME_PATH);
		if (file_exists(THEME_PATH.'/'.$themePathArray[1].'.xml')){
			$xml = simplexml_load_file(THEME_PATH.'/'.$themePathArray[1].'.xml');
			foreach($xml->javascript as $js){
				self::AddJs(THEME_PATH.$js);
			}
			foreach($xml->css as $css){
				self::AddCss(THEME_PATH.$css);
			}
		}else{
			Core::SetMessage('Configuration file '.THEME_PATH.'/'.$themePathArray[1].'.xml is unreadable or does not exist!', 'error');
		}
	}
}
