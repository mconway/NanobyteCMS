<?php
/**
 * Index page logic
 * All page requests are directed here. The correct Classes and functions are called automagically to display the page
 * Since 5/1/2008
 */
ini_set('display_messages',E_ALL);
//require the Core and Smarty Classes
require_once './includes/controllers/base.controller.php';
require_once './includes/contrib/smarty/libs/Smarty.class.php';
require_once './includes/contrib/geshi/geshi.php';
if(!include_once './includes/config.inc.php'){
	echo "Unable to find configuration file";
	exit;
}

###SERVE CSS AND JS###
//Serve any JS and CSS files before we call anything we dont need to (faster)
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
##END CSS/JS BLOCK###

// Make the Core Object (Ghetto Bootstrap)
$Core = BaseController::getCore();

if($Core->isEnabled('stats')){
	$stats = new Mod_Stats();
	$stats->commit();
}

// Add the main CSS styles for inclusion
BaseController::addCss('includes/js/jquery.jcarousel.css');
BaseController::addCss('includes/js/tango/skin.css');
BaseController::addCss('includes/js/jquery.tooltip.css');

//Add Global JS Files
BaseController::addJs('includes/js/jquery.js');
BaseController::addJs('includes/js/livequery.js');
BaseController::addJs('includes/contrib/ckeditor/ckeditor.js');
BaseController::addJs('includes/js/jquery-ui-1.7.2.custom.js');
BaseController::addJs('includes/js/jquery.jcarousel.js');
BaseController::addJs('includes/js/pause.js');
BaseController::addJs('includes/js/ajaxfileupload.js');
BaseController::addJs('includes/js/jquery.tooltip.js');
BaseController::addJs('includes/js/nanobyte.js');
//BaseController::AddJs('http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js');

//Include Theme Specified CSS and JS
BaseController::getThemeIncludes();

//Assign Global Site Variables to Smarty
$Core->smarty->assign(array(
	'sitename'=>SITE_NAME,
	'logo'=>SITE_LOGO,
	'feedurl'=>$Core->url('rss'),
	'siteslogan'=>SITE_SLOGAN
));

//if(SITE_DISABLED===true){
//	if(!isset($_GET['page']) || $_GET['page']!=='admin'){
//		$Core->smarty->display('site_disabled.tpl');
//		exit;	
//	}
//}
if(!CMS_INSTALLED){
	array_shift($Core->args); 

	//Determine if we are using AJAX, then remove it from the array and resort it
	if($Core->ajax==true){
		unset($Core->args[array_search('ajax',$Core->args)]);
		$Core->args = array_values($Core->args);
	}
	$class = 'InstallController';
	call_user_func(array($class,'Display'));
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
	$Core->smarty->assign('menu',MenuController::getMenu('main',$Core->user->group));
	
	//The first bucket in the $Core->args array is going to be the actual page we want to view
	$script = array_shift($Core->args); 
	
	//Check to see if the page is an alias to an actual class
	$alias = $Core->checkAlias($script);
	if ($alias){
		$_GET['page'] = str_replace($script,$alias,$_GET['page']);
		$Core->parseArgs();
		$script = array_shift($Core->args);
	}
	
	//$class = $script.'Controller'; //for php 5.3.0
	//If there is a file for the requested page - include it
	if(BaseController::autoload($script.'Controller',false)){
		$class = $script.'Controller';
	//If it's not a file or enabled mod - display a 404 error
	}else{
		header("HTTP/1.1 404 Not Found");
		$error = new Error(404,$script);
		$Core->smarty->assign(array(
			'error_code'=>$error->error_code,
			'explanation'=>$error->explanation,
			'server_url'=>$error->server_url
		));
		BaseController::getHTMLIncludes();
		$jsonObj->content = $Core->smarty->fetch('error.tpl'); //this needs a controller
		if($Core->ajax){
			print json_encode($Core->json_obj);
		}else{
			print $Core->json_obj->content;
		}
	}
	if(isset($class)){
		call_user_func(array($class,'Display'));
	}else{
		BaseController::displayMessages(); 
		BaseController::getHTMLIncludes();
		$Core->smarty->display('error.tpl');
	}
}

?>
