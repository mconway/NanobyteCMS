<?php

class BaseController{
	public static function Redirect($page=null){
		if ($page){ 
			header("location: " . Core::Url($page), true, 303);
		}else{
			header("location: ".$_SERVER['HTTP_REFERER'], true, 303); 
		}
	}
	public static function DisplayMessages($smarty){
		if(isset($_SESSION['messages'])){
			$messages = Core::getMessages();
			$smarty->assign('messages', $messages);
		}else{
			return false;
		}
	}
	public static function NewUsers($smarty){
		$users = Core::NewUsers();
		$smarty->assign('users', $users);
	}
	
	public static function strleft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2)); 
	}

	public static function paginate($limit,$total,$filePath,$currentPage) {
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
	public static function VerifyFile($file){
		$filename = $file['name'];
		$ext = substr($filename, strripos($filename, '.')); // Get the extension from the filename.
		$allowed_filetypes = explode(', ',FILE_TYPES);
		if(!in_array($ext,$allowed_filetypes)){
			$error = 'The file you attempted to upload is not allowed. Valid file types are:'.FILE_TYPES;
		}
		// Now check the filesize, if it is too large then inform the user.
		if(filesize($_FILES['userfile']['tmp_name']) > FILE_SIZE){
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
			$cssFiles[] = array('media'=>$media, 'file'=>$file);
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
		$css = self::AddCss();
		$smarty->assign('css', $css);
		$js = self::AddJs();
		$smarty->assign('js', $js);
	}
}
