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
/**
 * Created on May 9, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Core{
 	
	public $mods_enabled = array();
	public $smarty;
	public $json_obj;
	public $ajax;
	public $args;
	public $user;
	
	public function __construct($start_session=false){
		if($start_session===true){
			//Start the session and create any objects we need
			$this->StartSession();
		}
		if(CMS_INSTALLED=='1'){
			$this->EnabledMods();
			$this->user = array_key_exists('user',$_SESSION) ? new User($_SESSION['user']) : new User(0);
		}
		//Create the JSON Object
		$this->json_obj = new Json();
		//Create the Smary Object and set parameters
		$this->smarty = new Smarty();
		$this->smarty->template_dir = THEME_PATH;
		$this->smarty->force_compile = true;
		
		$this->parseArgs();
		
		//Determine if we are using AJAX, then remove it from the array and resort it
		$this->ajax = in_array('ajax',$this->args) ? true : false;
		if($this->ajax==true){
			unset($this->args[array_search('ajax',$this->args)]);
			$this->args = array_values($this->args);
		}
		
		
	}
	
	/**
	 * Magic get method for private variables
	 * @return mixed
	 * @param mixed $name
	 */
	public function __get($name){
//		echo get_class($this);
		if(get_class() != "Core"){
			var_dump(get_class());
		}
		return $this->$name;
	}

	/**
	 * Magic set method for private variables
	 * @return void
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value){
		$this->$name = $value;
	}
	
 	public function arrayPathInsert(&$array, $path, $value){
		$path_el = explode('|', $path);
		$arr_ref =& $array;
		$count = count($path_el);
		for($i=0; $i<$count; $i++){
			$arr_ref =& $arr_ref[$path_el[$i]];
		}
		$arr_ref = $value;
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
	
	public function authUser($perm){
		
		foreach($this->user->permissions->permissions as $permission){
			file_put_contents('log.txt',var_export($permission->description,true)." ".count($this->user->permissions->permissions)."\n",FILE_APPEND);
			if ($permission->perm_id==$perm || $permission->description==$perm){
				return true;
			}
			continue;
		}
		return false;
	}
	
	public function checkAlias($alias){
		$dbh = DBCreator::GetDBObject();
		$query = $dbh->prepare("SELECT path FROM ".DB_PREFIX."_url_alias WHERE alias=?");
		$query->execute(array(0=>$alias));
		$result = $query->fetch();
		if ($query->rowCount() == 1){
			return $result[0];
		}else{
			return false;
		}
	}
	
	public function decodeConfParams($param){
 		return str_rot13(base64_decode($param));
 	}

	public function enabledMods(){
		$mods_list = Module::GetEnabled('module');
		foreach($mods_list as $mod){
			$this->mods_enabled[$mod['name']] = true;
			$m = new Module($mod['name']);
			//this is case sensetive. needs to be fixed?
//			require_once($mod->modpath.'mod_'.$mod->name.'.php');
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

	public function getSettings($setting){
		$dbh = DBCreator::GetDbObject();
		$query = $dbh->prepare("SELECT value FROM ".DB_PREFIX."_settings WHERE setting=:set");
		$query->execute(array(':set'=>$setting));
		if($query->rowCount()==1){
			$tmp = $query->fetch(PDO::FETCH_ASSOC);
			return $tmp['value'];
		}else{
			return false;
		}
	}

	public function isEnabled($module){
		if(!is_array($this->mods_enabled)){
			var_dump($this->mods_enabled, $module);
		}
		if(array_key_exists(strtolower($module), $this->mods_enabled)){
			return true;
		}
		return false;
	}

	public function isSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
 		}
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
	
	public function parseArgs(){
		// If the page is 'home' or blank, set it to the HOME defined constant
		if (!array_key_exists('page',$_GET) || strpos($_GET['page'], 'home') !== false){
			$_GET['page'] = HOME;
		} 
		//Creates an array of arguments to pass to specific pages
		$this->args = explode('/', $_GET['page']); 
		//If any actions have been set using POST, add these to the args array
		if(array_key_exists('actions',$_POST)){ 
			$action = explode('/',$_POST['actions']);
			foreach ($action as $a){
				$this->args[] = $a;
			}
		}
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
	
	public function startSession(){
		if(CMS_INSTALLED=='1'){
			$sess = new SessionManager();
		}
		session_name("nanobytecms");
		session_set_cookie_params(SESS_TTL);
	    session_start();
		set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_PATH); 
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
	
	/**
	 * Modified Var_Dump. Default behavior wraps var_dump in <pre> tags
	 * Wraps argument in <pre> tags. If return is true, returns the data dump. If use_pre is false, will only return var_dump with newlines
	 * @return mixed
	 * @param mixed $mixed
	 * @param boolean $return[optional]
	 * @param boolean $use_pre[optional]
	 */
	public function vardump(&$mixed,$return=false,$use_pre=true){
		ob_start();
		var_dump($mixed);
		$return_val = ob_get_clean();
		if($use_pre===true){
			$return_val = "<pre>".$return_val."</pre>";
		}
		if($return===true){
			return $return_val;
		}
		echo $return_val;
	} 

 }
 //ini_set('zlib.output_compression', 'On');

?>
