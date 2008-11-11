<?php
/*
 * Index page logic
 * All page requests are directed here
 * Since 5/2008
 */
 
//require the Core and Smarty Classes
require_once './includes/core.inc.php';
require_once './includes/contrib/smarty/libs/Smarty.class.php';
require_once './includes/contrib/geshi/geshi.php';

// We start off with an empty array of enabled mods. This gets populated in Start Session
$modsEnabled = array(); 

//Start the session and create any objects we need
Core::StartSession();
$smarty = new Smarty();

// Add the main CSS styles for inclusion
BaseController::AddCss('templates/css/style.css'); 

//Add Global JS Files
//BaseController::AddJs('includes/contrib/tiny_mce/tiny_mce.js'); 
//BaseController::AddJs('templates/js/index.js');
 
//Assign Global Site Variables to Smarty
$smarty->assign('sitename',SITE_NAME);
$smarty->assign('feedurl', Core::url('rss'));
$smarty->assign('siteslogan', SITE_SLOGAN);

//Set Blocks
@include_once('blocks/usersonline.block.php');
$block = new UsersOnlineBlock();
$smarty->assign('block1',$block->template);

//Create a new User, or use an Already logged in User Object from the Session, then update teh access time
$user = $_SESSION['user'] ? unserialize($_SESSION['user']) : new User(0);
if($user->uid != 0){
	$user->SetAccessTime();
}
if (!isset($_SESSION['hash'])){
	$smarty->assign('noSess', true);
}
// Get the Site Menu, If the page is 'home', unset it, since this is the index page
MenuController::GetMenu('main',$user->group);
if (strpos($_GET['page'], 'home') !== false){
	unset($_GET['page']);
} 

//Take the page argument and run the functions to display the correct page.
if($_GET['page']){ 
	//Creates an array of arguments to pass to specific pages
	$args = explode('/', $_GET['page']); 
	//If any actions have been set using POST, add these to the args array
	if($_POST['actions']){ 
		$action = explode('/',$_POST['actions']);
		foreach ($action as $act){
			$args[] = $act;
		}
	}
	//The first bucket in the $args array is going to be the actual page we want to view
	$script = array_shift($args); 
	//$class = $script.'Controller'; //for php 5.3.0
	//If there is a file for the requested page - include it
	if (file_exists('./'.$script.'.php')){ 
		include_once('./'.$script.'.php');
		$script($args);
	//If a file doesnt exist, check to see if it is an enabled module - and include it
	}elseif(array_key_exists($script, $modsEnabled)){ 
		call_user_func(array('Mod_'.$script, 'Display'));
		BaseController::GetHTMLIncludes();
		$smarty->assign('file', '../modules/'.$script.'/modules.'.$script.'.tpl'); //user smarty file association
		$smarty->display('index.tpl');
	//If it's not a file or enabled mod - display a 404 error
	}else{
		header("HTTP/1.1 404 Not Found");
		$error = new Error(404,$script);
		$smarty->assign('error_code',$error->error_code);
		$smarty->assign('explanation',$error->explanation);
		$smarty->assign('server_url',$error->server_url);
		BaseController::GetHTMLIncludes();
		$smarty->display('error.tpl');
	}
//If there are no args
}else{
	//Display the login form if needed (This should probably be changed)

	//Add the Messages, Posts and Includes to smarty and display the results.
	BaseController::DisplayMessages(); 
	PostController::DisplayPosts(1);
	BaseController::GetHTMLIncludes();
	
	// To be used like this
	//$parser = new CodeParser;
	//$output = $parser->parse('<code> hi hi hi </code>');
	
	//print_r ($output);

	$smarty->display('index.tpl');
}
?>
