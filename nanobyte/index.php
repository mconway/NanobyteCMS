<?php
/*
 * Index page logic
 * All page requests are directed here
 * Since 5/2008
 */
ini_set('display_messages',E_ALL);
//require the Core and Smarty Classes
require_once './includes/core.inc.php';
require_once './includes/contrib/smarty/libs/Smarty.class.php';
require_once './includes/contrib/geshi/geshi.php';
require_once './includes/config.inc.php';

// Make teh Core Object (Ghetto Bootstrap)
$core = new Core();

//Start the session and create any objects we need

$jsonObj = new Json();
$smarty = new Smarty();
$smarty->template_dir = THEME_PATH;
$smarty->force_compile = true;

// Add the main CSS styles for inclusion
BaseController::AddCss('includes/js/jquery.jcarousel.css');
BaseController::AddCss('includes/js/tango/skin.css');

//Add Global JS Files
BaseController::AddJs('includes/js/jquery.js');
BaseController::AddJs('includes/js/livequery.js');
BaseController::AddJs('includes/js/jquery.ui.js');
BaseController::AddJs('includes/js/jquery.jcarousel.js');
BaseController::AddJs('includes/js/pause.js');
BaseController::AddJs('includes/js/nanobyte.js');
BaseController::AddJs('includes/contrib/nicedit/nicEdit.js');
//BaseController::AddJs('http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js');

//Include Theme Specified CSS and JS
BaseController::GetThemeIncludes();

//Assign Global Site Variables to Smarty
$smarty->assign('sitename',SITE_NAME);
$smarty->assign('feedurl', $core->url('rss'));
$smarty->assign('siteslogan', SITE_SLOGAN);

//Get Blocks
ModuleController::GetBlocks();

//Create a new User, or use an Already logged in User Object from the Session, then update teh access time
$user = $_SESSION['user'] ? new User($_SESSION['user']) : new User(0);
if($user->uid != 0){
	$user->SetAccessTime();
}
if (!isset($_SESSION['hash'])){
	$smarty->assign('noSess', true);
}
// Get the Site Menu
$smarty->assign('menu',MenuController::GetMenu('main',$user->group));

// If the page is 'home' or blank, set it to the HOME defined constant
if (!array_key_exists('page',$_GET) || strpos($_GET['page'], 'home') !== false){
	$_GET['page'] = HOME;
} 
//Take the page argument and run the functions to display the correct page.
if(array_key_exists('page',$_GET)){ 
	//Creates an array of arguments to pass to specific pages
	$args = explode('/', $_GET['page']); 
	//If any actions have been set using POST, add these to the args array
	if(array_key_exists('actions',$_POST)){ 
		$action = explode('/',$_POST['actions']);
		foreach ($action as $a){
			$args[] = $a;
		}
	}
	//The first bucket in the $args array is going to be the actual page we want to view
	$script = array_shift($args); 
	
	//Determine if we are using AJAX, then remove it from the array and resort it
	$ajax = in_array('ajax',$args) ? true : false;
	if($ajax==true){
		unset($args[array_search('ajax',$args)]);
		$args = array_values($args);
	}
	
	//$class = $script.'Controller'; //for php 5.3.0
	//If there is a file for the requested page - include it
	if($core->autoload($script.'Controller',false)){
		$class = $script.'Controller';
	//If a file doesnt exist, check to see if it is an enabled module - and include it
//	}elseif(array_key_exists($script, $modsEnabled)){ 
//		$class = 'Mod_'.$script;
	//If it's not a file or enabled mod - display a 404 error
	}else{
		$alias = $core->CheckAlias($script);
		if ($alias){
			$class = $alias.'Controller';
		}else{
			header("HTTP/1.1 404 Not Found");
			$error = new Error(404,$script);
			$smarty->assign('error_code',$error->error_code);
			$smarty->assign('explanation',$error->explanation);
			$smarty->assign('server_url',$error->server_url);
			BaseController::GetHTMLIncludes();
			$jsonObj->content = $smarty->fetch('error.tpl'); //this needs a controller
			if($ajax){
				print json_encoded($jsonObj);
			}else{
				print $jsonObj->content;
			}
		}
	}
	call_user_func(array($class,'Display'),array(&$args,$ajax,&$smarty,&$user,&$jsonObj,&$core));
//If there are no args
}else{
	//Add the Messages, Posts and Includes to smarty and display the results.
	BaseController::DisplayMessages(); 
	BaseController::GetHTMLIncludes();
	$smarty->display('index.tpl');
}
?>
