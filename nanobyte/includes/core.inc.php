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
		if($c == 'HTML_QuickForm'){
			@include 'HTML/QuickForm.php';
			$foundClass = true;
		}elseif(strcasecmp(substr($c,0,4),'Mod_')!=0){
	 		if(file_exists("includes/".strtolower($c).".inc.php") && require_once("./includes/".strtolower($c).".inc.php")) {
	 			return true;
	    	}elseif (file_exists("includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php") && require_once("includes/controllers/".strtolower(str_ireplace('controller','',$c)).".controller.php")) {
	    		return true;
	 		}else{
	      		$foundClass = false;
	   		}
		}
		if(array_key_exists(str_ireplace('controller','',str_ireplace('Mod_','',$c)),$core->modsEnabled) && !$foundClass){
			if(substr($c,0,4)!=='Mod_'){
				$c = 'Mod_'.str_ireplace('Controller','',$c);
			}
			if(file_exists("./modules/".str_ireplace('Mod_','',$c)."/".strtolower($c).".php") && require_once("./modules/".str_ireplace('Mod_','',$c)."/".strtolower($c).".php")) {
				return true;
	 		}
		}
		if(!$foundClass){
      		if($showMessage){
      			$core->SetMessage("Could not load class '{$c}'",'error');
			}
      		return false;
   		}
	}
	
	public function decodeConfParams($param){
 		return str_rot13(base64_decode($param));
 	}

	public function startSession(){
		$sess = new SessionManager();
		session_set_cookie_params(SESS_TTL);
	    session_start();
		$this->EnabledMods();
		if(array_key_exists('Stats', $this->modsEnabled)){
			$stats = new Mod_Stats();
			$stats->commit();
		}
		set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_PATH); 
		
	}
	
 	public function l($text, $path, $options=array()){
		//return an HTML string
		 if (CLEANURL){
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.$path : SITE_DOMAIN.'/'.$path;
 		}else{
 			$url = PATH != '' ? SITE_DOMAIN.'/'.PATH.'index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
		if(isset($options['image'])){
			$text = '<img src="'.THEME_PATH.'/images/'.strtolower($text).'-'.$options['image'].'.png" alt="'.$text.'"/>';
		}
		$class = isset($options['class']) ? $options['class'] : '';
		$id = isset($options['id']) ? $options['id'] : '';
		$title = isset($options['title']) ? $options['title'] : '';
		$link = "<a href='{$url}' class='{$class}' id='{$id}' title='{$title}'>{$text}</a>";
		return $link;
	}
	
	public function setMessage($message=null, $type='status'){
		if (!isset($_SESSION['messages'])){
			$_SESSION['messages'] = array();
		}
		if (!isset($_SESSION['messages'][$type])){
			$_SESSION['messages'][$type] = array();
		}
		$_SESSION['messages'][$type][] = $message;
		return $_SESSION['messages'];
	}
	
	public function getMessages($type=null, $clear=true){
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
	
	public function authUser($user, $perm){
			if (array_key_exists($perm, $user->permissions)){
				return true;
			}else{
				return false;
			}
	}

 	public function isSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
 		}
 	}
	
 	public function url($path){
 		if (CLEANURL){
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.$path : SITE_DOMAIN.'/'.$path;;
 		}else{
 			//$url = explode('/',$path);
 			//$script = array_shift($url);
 			//$page = implode('/', $url);
 			return PATH != '' ? SITE_DOMAIN.'/'.PATH.'index.php?page='.$path : SITE_DOMAIN.'/index.php?page='.$path;
 		}
 	}
	
	public function fileUpload($file){
		$filename = $file['name']; // Get the name of the file (including file extension).
		if(move_uploaded_file($file['tmp_name'],UPLOAD_PATH . $filename)){
    		return true;
   		}else{
   			return false;
		}
	}
	
	public function enabledMods(){
		$modsList = Module::GetEnabled('module');
		foreach($modsList as $mod){
			$this->modsEnabled[$mod['name']] = true;
			$m = new Module($mod['name']);
			require_once($m->modpath.'Mod_'.$m->name.'.php');
		}
	}
	
	public function checkAlias($alias){
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
 
	public function arraySearchRecursive($needle, $haystack, $inverse = false, $limit = 1) {
		# Settings
		$path = array ();
		$count = 0;
		# Check if inverse
		if($inverse == true){
			$haystack = array_reverse ($haystack, true);
        }
		# Loop
		foreach($haystack as $key => $value){
			# Check for return
			if ($count > 0 && $count == $limit){
				return $path;
			}
			# Check for val
			if($value === $needle){
				# Add to path
				$path[] = $key;
				# Count
				$count++;
			}elseif(is_array($value)){
				# Fetch subs
				$sub = $this->ArraySearchRecursive($needle, $value, $inverse, $limit);
				# Check if there are subs
				if (count ($sub) > 0) {
					# Add to path
					$path[$key] = $sub;
					# Add to count
					$count += count ($sub);
				}
			}
		}
//		return implode('|',$path);
		return $path;
	}
 
 	public function array_path_insert(&$array, $path, $value){
		$path_el = explode('|', $path);
		$arr_ref =& $array;
		$count = count($path_el);
		for($i=0; $i<$count; $i++){
			$arr_ref =& $arr_ref[$path_el[$i]];
		}
		$arr_ref = $value;
    }
 	
	public function saveSettings($value,$setting){
		$dbh = DBCreator::GetDbObject();
		$query = $dbh->prepare("UPDATE ".DB_PREFIX."_settings SET value=:val WHERE setting=:set");
		$query->execute(array(':val'=>$value,':set'=>$setting));
		if($query->rowCount()==1){
			return true;
		}else{
			return false;
		}
	}
	
 }
 //ini_set('zlib.output_compression', 'On');
 spl_autoload_register(array('Core',"autoload"));
?>
