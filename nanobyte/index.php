<?php
/*
 * Index page logic
 * All page requests are directed here
 * Since 5/2008
 */
require_once './includes/core.inc.php'; // Require the core class - this will allo auto loading of all other classes
require_once './includes/smarty/libs/Smarty.class.php'; // Include smarty
$modsEnabled = array(); // We start off with an empty array of enabled mods. This gets populated in Start Session
Core::StartSession();
$smarty = new Smarty(); // Create a new smarty object
BaseController::AddCss('templates/css/style.css'); // Add the main CSS styles fo inclusion
$smarty->assign('sitename',SITE_NAME); // Assign the Site Name
$smarty->assign('feedurl', Core::url('rss')); // Set the RSS URL
$smarty->assign('siteslogan', SITE_SLOGAN); // Assign the Site Slogan
BaseController::GetMenu($smarty); // Get the Site Menu
if (strpos($_GET['page'], 'home') !== false){  // If the page is 'home', unset it, since this is the index page
	unset($_GET['page']);
} 
if($_GET['page']){ // this will take the page argument and run the functions to display the correct page.
	$args = explode('/', $_GET['page']); // Creates an array for sub pages and actions
	if($_POST['actions']){ // If any actions have been set using POST, add these to the args array
		$action = explode('/',$_POST['actions']);
		foreach ($action as $act){
			$args[] = $act;
		}
	}
	$script = array_shift($args); // The first bucket in the $args array is going to be the actual page we want to view
	//$class = $script.'Controller'; //for php 5.3.0
	if (file_exists('./'.$script.'.php')){ // If there is a file for the requested page - include it
		include_once('./'.$script.'.php');
		$script($args);
	}elseif(array_key_exists($script, $modsEnabled)){ // If a file doesnt exists, check to see if it is an enabled mod - and include it
		call_user_func(array('Mod_'.$script, 'Display'));
		BaseController::GetHTMLIncludes();
		$smarty->assign('file', '../modules/'.$script.'/modules.'.$script.'.tpl'); //user smarty file association
		$smarty->display('index.tpl');
	}else{ // if its not a file or enabled mod - display a 404 error
		header("HTTP/1.1 404 Not Found");
		$error = new Error(404,$script);
		$smarty->assign('error_code',$error->error_code);
		$smarty->assign('explanation',$error->explanation);
		$smarty->assign('server_url',$error->server_url);
		BaseController::GetHTMLIncludes();
		$smarty->display('error.tpl');
	}
}else{ // If there are no args
	if (!isset($_SESSION['hash'])){ // display the login form if needed
		Core::SetMessage('Not Logged In', 'status');
		$smarty->assign('noSess', true);
	}
	BaseController::DisplayMessages($smarty); // Get any messages
	PostController::DisplayPosts($smarty); // Get published posts
	BaseController::GetHTMLIncludes(); // get CSS and JS scripts
	$smarty->display('index.tpl'); // Display the Index page
}
?>
