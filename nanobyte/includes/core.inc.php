<?php
/*
 * Created on May 9, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Core{
	public static function autoload($c){
 		if(file_exists("./includes/".strtolower($c).".inc.php") && require_once("./includes/".strtolower($c).".inc.php")) {
 			return true;
    	} elseif (file_exists("./includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php") && require_once("./includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php")) {
    		return true;
 		}else {
      		Core::SetMessage("Could not load class '{$c}'",'error');
      		return false;
   		}
	}
	public static function DecodeConfParams($param){
 		return str_rot13(base64_decode($param));
 	}

	public static function StartSession(){
		$sess = new SessionManager();
		session_set_cookie_params(SESS_TTL);
	    session_start();
		self::EnabledMods();
		$stats = new Stats();
		$stats->commit();
		set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_PATH); 
		@include 'HTML/QuickForm.php';
		
	}
 	public static function l($text, $path, $options=null){
		//return an HTML string
		 if (CLEANURL){
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'/'.$path : SITE_DOMAIN.'/'.$path;
 		}else{
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'/index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
		if($options['image']){
			$text = '<img src="templates/'.THEME_PATH.'/images/'.strtolower($text).'-'.$options['image'].'.png" title="'.$text.'" alt="'.$text.'"/>';
		}
		$link = "<a href='{$url}' class='{$options['class']}' id='{$options['id']}'>{$text}</a>";
		return $link;
	}
	public static function SetMessage($message=null, $type='status'){
		if (!isset($_SESSION['messages'])){
			$_SESSION['messages'] = array();
		}
		if (!isset($_SESSION['messages'][$type])){
			$_SESSION['messages'][$type] = array();
		}
		$_SESSION['messages'][$type][] = $message;
		return $_SESSION['messages'];
	}
	public static function GetMessages($type=null, $clear=true){
		$messages = $_SESSION['messages'];
		if ($type){
			if ($clear){
				unset($_SESSION['messages'][$type]);
			}
			if (isset($messages[$type])){
				return array($type => $messages[$type]);
			}
		}else{
			if ($clear){
				unset($_SESSION['messages']);
			}
			return $messages;
		}
		return array();
	}
	public static function AuthUser($user, $perm){
			if (array_key_exists($perm, $user->permissions)){
				return true;
			}else{
				return false;
			}
	}

 	public static function IsSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
 		}
 	}
 	public static function Url($path){
 		if (CLEANURL){
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.'/'.$path : SITE_DOMAIN.'/'.$path;;
 		}else{
 			//$url = explode('/',$path);
 			//$script = array_shift($url);
 			//$page = implode('/', $url);
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.'/index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
 	}
	public static function FileUpload($file){
		$filename = $file['name']; // Get the name of the file (including file extension).
		if(move_uploaded_file($file['tmp_name'],UPLOAD_PATH . $filename)){
    		return true;
   		}else{
   			return false;
		}
	}
	public static function EnabledMods(){
		//global $modsEnabled;
		$modsList = Module::GetEnabled('module');
		foreach($modsList as $mod){
			$modsEnabled[$mod['name']] = true;
			$m = new Module($mod['name']);
			require_once($m->modpath.$m->name.'.php');
		}
	}
 }
 //ini_set('zlib.output_compression', 'On');
 spl_autoload_register(array("Core","autoload"));
?>
