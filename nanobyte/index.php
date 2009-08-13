<?php
/**
 * Index page logic
 * All page requests are directed here. The correct Classes and functions are called automagically to display the page
 * Since 5/1/2008
 */
ini_set('display_messages',E_ALL);
//require the Core and Smarty Classes
//require_once './includes/core.inc.php';
require_once './includes/controllers/base.controller.php';
require_once './includes/contrib/smarty/libs/Smarty.class.php';
require_once './includes/contrib/geshi/geshi.php';
require_once './includes/config.inc.php';

// Make the Core Object (Ghetto Bootstrap)
$Core = BaseController::getCore();
//$perms = new Perms(1);
//echo $Core->vardump($perms); exit;

// Add the main CSS styles for inclusion
BaseController::AddCss('includes/js/jquery.jcarousel.css');
BaseController::AddCss('includes/js/tango/skin.css');
BaseController::AddCss('includes/js/jquery.tooltip.css');

//Add Global JS Files
BaseController::AddJs('includes/js/jquery.js');
BaseController::AddJs('includes/js/livequery.js');
BaseController::AddJs('includes/js/jquery-ui-1.7.2.custom.js');
BaseController::AddJs('includes/js/jquery.jcarousel.js');
BaseController::AddJs('includes/js/pause.js');
BaseController::AddJs('includes/js/ajaxfileupload.js');
BaseController::AddJs('includes/js/jquery.tooltip.js');
//BaseController::AddJs('includes/js/jquery.qtip.js');
BaseController::AddJs('includes/js/nanobyte.js');
BaseController::AddJs('includes/contrib/nicedit/nicEdit.js');
//BaseController::AddJs('http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js');

//Include Theme Specified CSS and JS
BaseController::GetThemeIncludes();

//Assign Global Site Variables to Smarty
$Core->smarty->assign('sitename',SITE_NAME);
$Core->smarty->assign('logo',SITE_LOGO);
$Core->smarty->assign('feedurl', $Core->url('rss'));
$Core->smarty->assign('siteslogan', SITE_SLOGAN);
if(isset($_GET['file']) && isset($_GET['type']) && COMPRESS){
	BaseController::getCacheFile($_GET['file'],$_GET['type']);
	exit;
}elseif(isset($_GET['file']) && isset($_GET['type'])){
	if(($_GET['type']=='js'&&substr($_GET['file'],-2)==$_GET['type'])||($_GET['type']=='css'&&substr($_GET['file'],-3)==$_GET['type'])){
		if(file_exists($_GET['file'])){
			header ("Content-Type: text/" . $_GET['type']);
			$fp = fopen($_GET['file'],'r');
			fpassthru($fp);
			fclose($fp);
		}
	}else{
		die('You cannot access this file directly!');
	}
	exit;
}
//if(SITE_DISABLED===true){
//	if(!isset($_GET['page']) || $_GET['page']!=='admin'){
//		$Core->smarty->display('site_disabled.tpl');
//		exit;	
//	}
//}
if(!CMS_INSTALLED){
	if(array_key_exists('page',$_GET)){ 
		//Creates an array of arguments to pass to specific pages
		$Core->args = explode('/', $_GET['page']); 
		$Core->ajax = in_array('ajax',$Core->args) ? true : false;
		array_shift($Core->args); 
	}

	//Determine if we are using AJAX, then remove it from the array and resort it
	if($Core->ajax==true){
		unset($Core->args[array_search('ajax',$Core->args)]);
		$Core->args = array_values($Core->args);
	}
	$class = 'InstallController';
	call_user_func(array($class,'Display'),array(&$Core));
}else{
	//Create a new User, or use an Already logged in User Object from the Session, then update teh access time
	
	if($Core->user->uid != 0){
		$Core->user->setAccessTime();
	}
	
	//Get Blocks
	ModuleController::getBlocks(true);
	
	if (!isset($_SESSION['hash'])){
		$Core->smarty->assign('noSess', true);
	}
	// Get the Site Menu
//	echo $Core->vardump($Core->user->group);
	$Core->smarty->assign('menu',MenuController::getMenu('main',$Core->user->group));
	
	// If the page is 'home' or blank, set it to the HOME defined constant
	if (!array_key_exists('page',$_GET) || strpos($_GET['page'], 'home') !== false){
		$_GET['page'] = HOME;
	} 
	//Take the page argument and run the functions to display the correct page.
	if(array_key_exists('page',$_GET)){ 
		//Creates an array of arguments to pass to specific pages
		$Core->args = explode('/', $_GET['page']); 
		//If any actions have been set using POST, add these to the args array
		if(array_key_exists('actions',$_POST)){ 
			$action = explode('/',$_POST['actions']);
			foreach ($action as $a){
				$Core->args[] = $a;
			}
		}
		//The first bucket in the $Core->args array is going to be the actual page we want to view
		$script = array_shift($Core->args); 
		
		//Determine if we are using AJAX, then remove it from the array and resort it
		$Core->ajax = in_array('ajax',$Core->args) ? true : false;
		if($Core->ajax==true){
			unset($Core->args[array_search('ajax',$Core->args)]);
			$Core->args = array_values($Core->args);
		}
		
		//$class = $script.'Controller'; //for php 5.3.0
		//If there is a file for the requested page - include it
		if(BaseController::autoload($script.'Controller',false)){
			$class = $script.'Controller';
		//If it's not a file or enabled mod - display a 404 error
		}else{
			$alias = $Core->checkAlias($script);
			if ($alias){
				$class = $alias.'Controller';
			}else{
				header("HTTP/1.1 404 Not Found");
				$error = new Error(404,$script);
				$Core->smarty->assign('error_code',$error->error_code);
				$Core->smarty->assign('explanation',$error->explanation);
				$Core->smarty->assign('server_url',$error->server_url);
				BaseController::getHTMLIncludes();
				$jsonObj->content = $Core->smarty->fetch('error.tpl'); //this needs a controller
				if($Core->ajax){
					print json_encode($Core->json_obj);
				}else{
					print $Core->json_obj->content;
				}
			}
		}
		if(isset($class)){
			call_user_func(array($class,'Display'));
		}else{
			BaseController::displayMessages(); 
			BaseController::getHTMLIncludes();
			$Core->smarty->display('error.tpl');
		}
	//If there are no args
	}else{
		//Add the Messages, Posts and Includes to smarty and display the results.
		BaseController::displayMessages(); 
		BaseController::getHTMLIncludes();
		$Core->smarty->display('index.tpl');
	}
}

?>
