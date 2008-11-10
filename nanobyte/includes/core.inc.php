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
 	public static function GetConf(){
 		require_once './includes/config.inc.php';
 		define("DB_USER", Core::DecodeConfParams($dbuser));
		define("DB_PASS", Core::DecodeConfParams($dbpass));
		define("DB_HOST", $dbhost);
		define("DB_NAME", $dbname);
		define("DB_PREFIX", $dbprefix);
		define("PATH", $defaultdir);
		define("SITE_NAME", $sitename);
		define("SITE_SLOGAN", $siteslogan);
		define("SITE_DOMAIN", $sitedomain);
		define("UPLOAD_PATH", $uploadpath);
		define("FILE_TYPES", $filetypes);
		define("FILE_SIZE", $filesize);
		if ($cleanurl === 'true'){
			define("CLEANURL", $cleanurl);
		}
 	} 
	public static function StartSession(){
		$sess = new SessionManager();
		session_set_cookie_params(3600);
	    session_start();
	    self::GetConf();
		self::EnabledMods();
		$stats = new Stats();
		$stats->commit();
		BaseController::AddJs('templates/js/jquery.js');
		set_include_path(get_include_path() . PATH_SEPARATOR . "/home/mconway/pear/php"); 
		@include 'HTML/QuickForm.php';
	}
 	public static function l($text, $path, $options=null){
		//return an HTML string
		 if (CLEANURL === 'true'){
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'/'.$path : SITE_DOMAIN.'/'.$path;
 		}else{
 			//$url = explode('/',$path);
 			//$script = array_shift($url);
 			//$page = implode('/', $url);
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'/index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
		if($options['image']){
			$text = '<img src="templates/images/'.strtolower($text).'-'.$options['image'].'.png" title="'.$text.'" alt="'.$text.'"/>';
		}
		$link = '<a href="'.$url.'">'.$text.'</a>';
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
 	public static function NewUsers(){
 		$DB = DBCreator::GetDbObject();
 		$sql = $DB->prepare('select uid, username, joined from '.DB_PREFIX.'_user order by `joined` desc limit 5');
 		$sql->execute();
 		while ($row = $sql->fetch(PDO::FETCH_ASSOC)){
 			$users[] = $row;
 		}
 		return $users;
 	}
 	public static function IsSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
 		}
 	}
 	public static function Url($path){
 		if (CLEANURL === 'true'){
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
		global $modsEnabled;
		$modsList = Module::GetEnabled();
		foreach($modsList as $mod){
			$modsEnabled[$mod['name']] = true;
			$m = new Module($mod['name']);
			require_once($m->modpath.$m->name.'.php');
		}
	}
 }
 spl_autoload_register(array("Core","autoload"));
?>
