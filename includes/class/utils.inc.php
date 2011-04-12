<?php
class Utils{
	/**
	 * Autoload function - dynamically loads all other classes
	 * @param string $c
	 */
	public static function autoload($c){
		try{
			$core = Core::getCore();
			$rdi = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $core->config->rootDir. DIRECTORY_SEPARATOR . 'Class');
		}catch(Exception $e){
			if(!file_exists('index.php')) // check to see if we are in the root folder.
				throw $e;
			$rdi = new RecursiveDirectoryIterator("Class");
		}
		$rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::SELF_FIRST);
		
		while($rii->valid()){
			if(!$rii->isDot()){
				if(strtolower(substr($rii->key(),strrpos($rii->key(),DIRECTORY_SEPARATOR)+1)) == strtolower($c) . '.inc.php'){
					require_once($rii->key());
					break;
				}
			}
			$rii->next();
		}
	}
	
 	public static function arrayPathInsert(&$array, $path, $value){
		$path_el = explode('|', $path);
		$arr_ref =& $array;
		$count = count($path_el);
		for($i=0; $i<$count; $i++){
			$arr_ref =& $arr_ref[$path_el[$i]];
		}
		$arr_ref = $value;
    }
	
	public static function arraySearchRecursive($needle, $haystack, $inverse = false, $limit = 1) {
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

	public static function createTable($structure){
		$dbh = DBCreator::GetDBObject();
		foreach($structure as $table=>$fields){
			$query = "CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_{$table} (\n";
			$cnt = count($fields);
			if(isset($fields['key'])){
				$cnt--;
			}
			for($i=0;$i<$cnt;$i++){
				$query .= "{$fields[$i][0]} {$fields[$i][1]}";
				if(isset($fields[$i][2])){
					$query .= "({$fields[$i][2]})";
				}
				if(isset($fields[$i][3]) && $fields[$i][3] === true){
					$query .=" NOT NULL";
				}
				if(isset($fields[$i][4]) && $fields[$i][4] === true){
					$query .=" AUTO_INCREMENT";
				}
				if($i < $cnt-1){
					$query .= ",\n";
				}
			}
			if(isset($fields['key'])){
				$query .= ",\nPRIMARY KEY ({$fields['key']})";
			}
			$query .= ")";
			$create_table = $dbh->prepare($query);
			$create_table->execute();
		}
	}
		
	public static function decodeConfParams($param){
 		return str_rot13(base64_decode($param));
 	}
	
	public static function getRoute(){
		//GET['page'] = '/controller/page';
		
		$route = new Route();
		$config = ConfigurationManager::getConfig(Core::getConfigFile());
		
		// If the page is 'home' or blank, set it to the HOME defined constant
		$args = (!isset($_GET['page'])) ? null : explode('/', $_GET['page']);
		
		//If any actions have been set using POST, add these to the args array
		if(array_key_exists('actions',$_POST)){ 
			$action = explode('/',$_POST['actions']);
			foreach ($action as $a)
				$args[] = $a;
		}
		
		if (is_null($args) || strtolower($args[0]) == "home")
			$route->setController($config->defaultHome);
		else
			$route->setController(array_shift($args));
			
		if(count($args) > 0)
			$route->setPage(array_shift($args));

		$route->setArgs($args);
		
		return $route;
	}
 	
	public static function isSingle($item){
 		if (count($item) == 1){
 			return true;
 		}else{
 			return false;
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
	public static function vardump(&$mixed,$return=false,$use_pre=true){
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