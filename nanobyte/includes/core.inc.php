<?php
/*
 * Created on May 9, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Core{
 	
	public function __construct(){
		$this->StartSession();
		$this->EnabledMods();
	}
	
	public static function autoload($c,$showMessage=true){
		global $core;
		if(substr($c,0,4)!=='Mod_'){
	 		if(file_exists("./includes/".strtolower($c).".inc.php") && require_once("./includes/".strtolower($c).".inc.php")) {
	 			return true;
	    	}elseif (file_exists("./includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php") && require_once("./includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php")) {
	    		return true;
	 		}else{
	      		$foundClass = false;
	   		}
		}
		if(array_key_exists(str_ireplace('controller','',str_ireplace('mod_','',$c)),$core->modsEnabled) && !$foundClass){
			if(substr($c,0,4)!=='Mod_'){
				$c = 'Mod_'.str_ireplace('Controller','',$c);
			}
			if(file_exists("./modules/".str_ireplace('mod_','',$c)."/".strtolower($c).".php") && require_once("./modules/".str_ireplace('mod_','',$c)."/".strtolower($c).".php")) {
				return true;
	 		}
		}else{
      		if($showMessage){
      			$this->SetMessage("Could not load class '{$c}'",'error');
			}
      		return false;
   		}
	}
	
	public function DecodeConfParams($param){
 		return str_rot13(base64_decode($param));
 	}

	public function StartSession(){
		$sess = new SessionManager();
		session_set_cookie_params(SESS_TTL);
	    session_start();
		self::EnabledMods();
		$stats = new Mod_Stats();
		$stats->commit();
		set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_PATH); 
		@include 'HTML/QuickForm.php';
		
	}
	
 	public function l($text, $path, $options=array()){
		//return an HTML string
		 if (CLEANURL){
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.$path : SITE_DOMAIN.'/'.$path;
 		}else{
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
		if(array_key_exists('image',$options)){
			$text = '<img src="'.THEME_PATH.'/images/'.strtolower($text).'-'.$options['image'].'.png" title="'.$text.'" alt="'.$text.'"/>';
		}
		$link = "<a href='{$url}' class='{$options['class']}' id='{$options['id']}'>{$text}</a>";
		return $link;
	}
	
	public function SetMessage($message=null, $type='status'){
		if (!isset($_SESSION['messages'])){
			$_SESSION['messages'] = array();
		}
		if (!isset($_SESSION['messages'][$type])){
			$_SESSION['messages'][$type] = array();
		}
		$_SESSION['messages'][$type][] = $message;
		return $_SESSION['messages'];
	}
	
	public function GetMessages($type=null, $clear=true){
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
	
	public function AuthUser($user, $perm){
			if (array_key_exists($perm, $user->permissions)){
				return true;
			}else{
				return false;
			}
	}

 	public function IsSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
 		}
 	}
	
 	public function Url($path){
 		if (CLEANURL){
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.$path : SITE_DOMAIN.'/'.$path;;
 		}else{
 			//$url = explode('/',$path);
 			//$script = array_shift($url);
 			//$page = implode('/', $url);
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.'index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
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
	
	public function EnabledMods(){
		$modsList = Module::GetEnabled('module');
		foreach($modsList as $mod){
			$this->modsEnabled[$mod['name']] = true;
			$m = new Module($mod['name']);
			require_once($m->modpath.'Mod_'.$m->name.'.php');
		}
	}
	
	public function CheckAlias($alias){
		$dbh = DBCreator::GetDBObject();
		$query = $dbh->prepare("SELECT `path` FROM ".DB_PREFIX."_url_alias WHERE `alias`=?");
		$query->execute(array(0=>$alias));
		$result = $query->fetch();
		if ($query->rowCount() == 1){
			return $result[0];
		}else{
			return false;
		}
	}
 }
 //ini_set('zlib.output_compression', 'On');
 spl_autoload_register(array("Core","autoload"));
?>
