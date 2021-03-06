<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
class BaseController{
	private static $Core;
	
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

	public static function autoload($c,$show_message=true){
		$found_class = false;
		$c = strtolower($c);
		if(!empty($c) && $c != 'controller'){
			if(strcasecmp(substr($c,0,4),'mod_')!=0){
		 		if(file_exists("includes/".strtolower($c).".inc.php") && require_once("./includes/".strtolower($c).".inc.php")) {
		 			return true;
		    	}elseif (file_exists("includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php") && require_once("includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php")) {
		    		return true;
		 		}
			}
			if(!$found_class){
				$Core = self::getCore();
				if(array_key_exists(str_ireplace('controller','',str_ireplace('mod_','',$c)),$Core->mods_enabled)){
					if(substr($c,0,4)!=='mod_'){
						$c = 'mod_'.str_ireplace('controller','',$c);
					}
					if(file_exists("./modules/".str_ireplace('mod_','',$c)."/".strtolower($c).".php") && require_once("./modules/".str_ireplace('mod_','',$c)."/".strtolower($c).".php")) {
						return true;
			 		}
				}else{
		      		if($show_message){
		      			$Core->setMessage("Could not load class '{$c}'",'error');
					}
		      		return false;
		   		}
			}
		}
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
	
	public static function displayMessages(){
		$Core = self::$Core;
		if(isset($_SESSION['messages'])){
			$messages = $Core->getMessages();
			$Core->smarty->assign('messages', $messages);
			return $Core->smarty->fetch('messages.tpl');
		}else{
			return false;
		}
	}
	
	public static function emailForm($action,$email=array('id'=>'','subject'=>'','body'=>'','function'=>'')){
		
		$element_array = array('name'=>'email','method'=>'post','action'=>$action);
		//set form default values
		$element_array['defaults'] =array(
			'id'=>$email['id'],
			'subject'=>$email['subject'],
			'body'=>$email['body'],
			'function'=>$email['function']
		);
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>"Email Details for {$email['function']}"),
			array('type'=>'hidden','name'=>'id'),
			array('type'=>'text', 'name'=>'subject', 'label'=>'Subject', 'options'=>array('size'=>63, 'maxlength'=>50)),
			array('type'=>'textarea', 'name'=>'body', 'label'=>'Body','options'=>array('rows'=>15,'cols'=>60)),
			array('type'=>'text', 'name'=>'function', 'label'=>'Function', 'options'=>array('size'=>63, 'maxlength'=>50)),
			array('type'=>'submit','name'=>'submit','value'=>'Submit')
		);
		$element_array['callback'] = array(new Email(),'setEmailData');
		return self::generateForm($element_array);
	}
	
	public static function generateForm($element_array){
		$element_array['name'] = isset($element_array['name'])?$element_array['name']:'form';
		$element_array['method'] = isset($element_array['method'])?$element_array['method']:'post';
		$element_array['action'] = isset($element_array['action'])?$element_array['action']:'';
		
		$form = new Form(array('name'=>$element_array['name'],'method'=>$element_array['method'],'action'=>$element_array['action']));
		
		if(isset($element_array['defaults'])&&!empty($element_array['defaults'])){
			$form->setDefaults($element_array['defaults']); 
		}
		
		if(isset($element_array['elements'])&&!empty($element_array['elements'])){
			foreach($element_array['elements'] as $element){
				$form->addElement($element);
			}
		}
		
		if(isset($element_array['filters'])){
			foreach($element_array['filters'] as $filter){
				$form->addFilter($filter[0], $filter[1]);
			}
		}
		
		if(isset($element_array['rules'])){
			foreach($element_array['rules'] as $rule){
				$form->addRule($rule[0], $rule[1]);
			}
		}
		
		if(isset($_POST['submit'])&&$form->validate()){
			if(isset($element_array['callback'])){
				if($form->process($element_array['callback'])==true){
					return true;
				}
			}
		}

		return $form;
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

	public static function getCore(){
		if (!isset(self::$Core)) {
		    self::$Core = new Core(true);
		}
		return self::$Core;
	}

	public static function getEncodeType(){
		// Determine supported compression method
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

		// Determine used compression method
		return $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');
	}
	
	public static function getHTMLIncludes(){
		$Core = self::$Core;
		$includes = '';
		if(COMPRESS){
			$includes = "<link type='text/css' rel='stylesheet' href='".self::CompressFiles(self::AddCss(),'css')."' />\n";
			$includes .= "<script type='text/javascript' src='".self::CompressFiles(self::AddJs(),'js')."'></script>\n";
//			var_dump(self::CompressFiles(self::AddCss(),'css'),self::CompressFiles(self::AddJs(),'js'),$includes);
			$Core->smarty->assign('includes',$includes);
		}else{
			$css = self::AddCss();
			foreach($css as $c){
				$includes .= "<link type='text/css' rel='stylesheet' href='{$c}' />\n";
			}
			$js = self::AddJs();
			foreach($js as $j){
				$includes .= "<script type='text/javascript' src='{$j}'></script>\n";
			}
			$Core->smarty->assign('includes',$includes);
			
		}
	}
	
	public static function getStart($page,$limit){
		if (!$page || $page == ""){$page = 1; } 
		$start = (($page - 1) * $limit) ;
		return $start;
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
	
	public static function getThemeList(){
		$dirs = glob('templates/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$dir = str_replace('templates/','',$dir);
			$theme[$dir] = ucfirst($dir); 
		}
		return $theme;
	}
	
	public static function handleImage($image,$resize=false){
		$filename = $image['name'];
		$error = BaseController::VerifyFile($image);
		if ($error == false){
			if (self::$Core->FileUpload($image) == true){
				if ($resize){
					$images = self::ResizeImage($image, $resize);
		//			return '<a class="postImage" href="'.$images['orig'].'"><img src="'.$images['thumb'].'"/></a>';
					
				}
				else{
					$images['orig'] = UPLOAD_PATH.$filename;
		//			return '<img src="'.UPLOAD_PATH.$filename.'" />';
				}
				return $images;
			}else{
				self::$Core->SetMessage('There was an error during the file upload.  Please try again.','error');
			}
		}else{
//			self::$Core->SetMessage($error, 'status');
		}
		return;
		
	}
	
	public static function paginate($limit=LIMIT,$total,$filePath,$currentPage) {
		$Core = self::$Core;
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
						$pagination .= ($i==$currentPage) ? $Core->l($i, '#')." | "
						: $Core->l($i,$filePath.$i)." | ";
					}
					$pagination .= ($currentPage<$allPages-4) ? " ... " : " ";
				} else {
					$pagination .= " ... ";
				}
			}
		} else {
			for($i=1; $i<$allPages+1; $i++) {
				$pagination .= ($i==$currentPage) ? $Core->l($i, '#') ." | "
				: $Core->l($i,$filePath.$i)." | ";
			}
		}
		//Set the pager links
		$first = $Core->l('FIRST', $filePath.'1', array('title'=>'Go to the first page','class'=>'pager','image'=>'24'));
		$prev = $Core->l('Prev',$filePath.($currentPage-1), array('title'=>'Back one page','class'=>'pager','image'=>'24'));
		$next = $Core->l('Next',$filePath.($currentPage + 1), array('title'=>'Forward one page','class'=>'pager','image'=>'24'));
		$last = $Core->l('LAST', $filePath.$allPages, array('title'=>'Go to last page','class'=>'pager','image'=>'24'));
		
//		if($currentPage>1 && $currentPage<$allPages){
			$output .= $first.' '.$prev.' '.$pagination.' '.$next.' '.$last;
//		}
//		elseif ($currentPage>1){
//			$output .= $first.' '.$prev.' '.$pagination;
//		}
//		elseif ($currentPage<$allPages){
//			$output .= $pagination .' '.$next.' '.$last;
//		}
	
		return $output;
	}

	public static function redirect($page=null){
		$Core = self::$Core;
		if ($page && !$Core->ajax){ 
			header("location: " . $Core->url($page), true, 303);
		}elseif ($page && $Core->ajax){ 
//			header("location: " . Core::Url($page).'/ajax', true, 303);
			$Core->json_obj->callback = 'nanobyte.redirect';
			$Core->json_obj->args = $Core->Url($page);
			return;
		}elseif(!isset($page) && !$Core->ajax){
			header("location: ".$_SERVER['HTTP_REFERER'], true, 303); 
		}elseif(!isset($page) && $Core->ajax){
//			header("location: ".$_SERVER['HTTP_REFERER'].'/ajax', true, 303); 
			$Core->json_obj->callback = 'nanobyte.redirect';
			$Core->json_obj->args = $_SERVER['HTTP_REFERER'];
			return;
		}
		exit;
	}
	
	public static function resizeImage($image, $thumb_x){
		if(!isset($image['path'])){
			$imagepath = UPLOAD_PATH.$image['name'];
		}else{
			$imagepath = $image['path'];
		}
		
		$uri = array();
		//open original (uploaded) image, based on type.
		switch($image['type']) {
			case 'image/jpeg':
			case 'image/pjpeg':
			case 'image/jpg':
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
				Core::SetMessage('Unknown Image Format or MIME Type: '.$image['type'],'error');
		}
		$base = UPLOAD_PATH . $name;
		if (isset($orig)){
			//fetch the size of the original and overlay images,and calculate the size of the new image and thumb.
			$orig_x = imagesx($orig);
			$orig_y = imagesy($orig);
			$thumb_y = round(($orig_y * $thumb_x) / $orig_x);
			
			// create the thumb image, and scale the original into it. try to keep transparent bg
			$thumb = imagecreatetruecolor($thumb_x, $thumb_y);
			$clr_trnsprnt = imagecolortransparent($orig, imagecolorallocate($orig,0,0,0));
			$i_trnprt   = imagecolorallocate($thumb, $clr_trnsprnt['red'], $clr_trnsprnt['green'], $clr_trnsprnt['blue']);
			
//			imagefill($thumb, 0, 0, $i_trnprt);
	 
	        // Set the background color for new image to transparent
	        imagecolortransparent($thumb, $i_trnprt);
	
//			imagealphablending($thumb, false);
			imagecopyresampled($thumb, $orig, 0, 0, 0, 0, $thumb_x, $thumb_y, $orig_x, $orig_y);

			//write the 2 images to disk.
			imagepng($thumb, $base . '-thumb.png');
			$uri['orig'] = $imagepath;
			$uri['thumb'] = $base . '-thumb.png';
		}else{
			self::$Core->SetMessage('Unable to Open '.$image['name'].' for modification','error');
		}

	    return $uri;
	}
	
	public static function split(&$v, $k, $delim){
		$v = explode($delim,$v);
	}
	
	public static function strleft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2)); 
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
			$error = 'The file you attempted to upload is too large';
		}
		// Check if we can upload to the specified path, if not, inform the user.
		if(!is_writable(UPLOAD_PATH)){
			$error = 'You cannot upload to the specified directory, please CHMOD it to 777.';
		}
		return isset($error) ? $error : false;
	}
}
 spl_autoload_register(array("BaseController","autoload"));
?>