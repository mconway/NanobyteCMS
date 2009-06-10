<?php

class BaseController{
	public static function redirect($page=null,$ajax=false){
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
	
	public static function displayMessages(){
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
	
	public static function getStart($page,$limit){
		if (!$page || $page == ""){$page = 1; } 
		$start = (($page - 1) * $limit) ;
		return $start;
	}
	
	public static function verifyFile($file){
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
	
	public static function addCss($file=null, $media='all'){
		static $cssFiles = array();
		if($file != null){
			$cssFiles[] = $file;
		}
		return $cssFiles;
	}
	
 	public static function addJs($file=null){
 		static $jsFiles;
		if($file != null){
			$jsFiles[] = $file;
		}
		return $jsFiles;
 	}
	
	public static function getHTMLIncludes(){
		global $smarty;
		$includes = '';
		if(COMPRESS){
			$includes = "<link type='text/css' rel='stylesheet' href='".self::CompressFiles(self::AddCss(),'css')."' />\n";
			$includes .= "<script type='text/javascript' src='".self::CompressFiles(self::AddJs(),'js')."'></script>\n";
//			var_dump(self::CompressFiles(self::AddCss(),'css'),self::CompressFiles(self::AddJs(),'js'),$includes);
			$smarty->assign('includes',$includes);
		}else{
			$css = self::AddCss();
			foreach($css as $c){
				$includes .= "<link type='text/css' rel='stylesheet' href='{$c}' />\n";
			}
			$js = self::AddJs();
			foreach($js as $j){
				$includes .= "<script type='text/javascript' src='{$j}'></script>\n";
			}
			$smarty->assign('includes',$includes);
			
		}
	}
	
	public static function handleImage($image,$resize=null){
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
	
	public static function resizeImage($image, $thumb_x){
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
	
	public static function getThemeList(){
		$dirs = glob('templates/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$dir = str_replace('templates/','',$dir);
			$theme[$dir] = ucfirst($dir); 
		}
		return $theme;
	}
	
	public static function compressFiles($fileArray,$type,$output=false){
		$cachedir = UPLOAD_PATH . 'cache';
	// Determine last modification date of the files
		$lastmodified = 0;
		while(list(,$element) = each($fileArray)){
			$path = realpath($element);
			$lastmodified = max($lastmodified, filemtime($path));
		}
	
	// Send Etag hash
		$hash = $lastmodified . '-' . md5(implode(',',$fileArray));
//		header ("Etag: \"" . $hash . "\"");
	
	// First time visit or files were modified

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
		
		// Get contents of the files
		$contents = '';
		reset($fileArray);
		while (list(,$element) = each($fileArray)) {
			$path = realpath($element);
//				var_dump($path);
			$contents .= file_get_contents($path);
		}
		
		// Send Content-Type
//			header ("Content-Type: text/" . $type);
		$encoding = self::getEncodeType();
//		if (isset($encoding) && $encoding != 'none' && $output===true) 
//		{
////				// Send compressed contents
////				$contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
//			$contents = gzcompress($contents, 9);
//			header ("Content-Encoding: " . $encoding);
//			header ('Content-Length: ' . strlen($contents));
//			echo $contents;
//			return;
//		} 
//			else 
//			{
//				// Send regular contents
//				header ('Content-Length: ' . strlen($contents));
//				echo $contents;
//			}

			// Store cache
		if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
			fwrite($fp, preg_replace('!^\s*/\*.*?\*/!sm','',$contents));
			fclose($fp);
		}
		
		return $cachedir . '/' . $cachefile;
	}

	public static function getCacheFile($file, $type){
		$encoding = self::getEncodeType();
		if (file_exists($file)) {
//			if ($fp = fopen($file, 'rb')) {
				if ($encoding != 'none') {
//					header ("Content-Encoding: " . $encoding);
				}
			
				header ("Content-Type: text/" . $type);
				header ("Content-Length: " . filesize($file));
	
				echo utf8_decode(file_get_contents($file));
//				fclose($fp);
//				return;
//			}
		}
	}

	public static function getEncodeType(){
		// Determine supported compression method
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

		// Determine used compression method
		return $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');
	}

	public static function getThemeIncludes(){
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

	public static function emailForm($method,$email=array('id'=>'','subject'=>'','body'=>'','function'=>'')){
		$form = new HTML_QuickForm('email','post',$method);
		//set form default values
		$form->setdefaults(array(
			'id'=>$email['id'],
			'subject'=>$email['subject'],
			'body'=>$email['body'],
			'function'=>$email['function']
		));
		//create form elements
		$form->addElement('header','',"Email Details for {$email['function']}");
		$form->addElement('hidden','id');
		$form->addElement('text', 'subject', 'Subject', array('size'=>63, 'maxlength'=>50));
		$form->addElement('textarea', 'body', 'Body',array('rows'=>15,'cols'=>60));
		$form->addElement('text', 'function', 'Function', array('size'=>63, 'maxlength'=>50));
		$form->addElement('submit','submit','Submit');
		if(isset($_POST['submit']) && $form->validate()){
			$emailObj=new Email();
			return $emailObj->setEmailData($form->exportValues());
		}
		return $form->toArray();
	}

}
