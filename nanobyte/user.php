<?php
/*
 * User page logic
 * All pages that start with user/ are directed here.
 * @param $args - array retrieved from GET and POST data. Post data is from $_POST['actions'] only
 * Since 5/2008
 */
function User($args){
global $smarty; // Get the Smarty object
global $user;
global $ajax;
switch($args[0]){ // What sub page are we trying to view
		case 'details': // view user details - THIS IS NOT THE PROFILE PAGE
			UserController::GetDetails($args['1']);
			break;
		case 'edit':
			if (Core::AuthUser($user, 'edit user accounts') || $user->uid === $args[1]){
				UserController::EditUser($args[1]);
				$content = $smarty->fetch('form.tpl');
			}else{
				Core::SetMessage('You do not have access to this page!','error');
			}
			BaseController::DisplayMessages();
			BaseController::GetHTMLIncludes();
			$smarty->display('index.tpl');
			break;	
		case 'commit': // Save User Details
			if (isset($_POST['commit'])){
				UserController::EditUser($args[1]);
				BaseController::Redirect();
			}elseif(isset($_POST['delete'])){
				AdminController::DeleteUserRequest();
				BaseController::Redirect();
			}
			break;
		case 'login': // Log in a user
			if (isset($_POST['login'])){
				UserController::Login($_POST['user'], $_POST['pass']);
				BaseController::Redirect(null,$ajax);
			}else{
				BaseController::Redirect('user',$ajax);
			}
			break;
		case 'logout': //Logout and destroy a user session
			UserController::Logout();
			BaseController::Redirect('home');
			break;
		case 'register': // Sign up as a new user
			UserController::RegForm();
			$content = $smarty->fetch('form.tpl');
			//BaseController::DisplayMessages();
			//BaseController::GetHTMLIncludes();
			//$smarty->display('user.tpl');
			break;
		case 'profiles':
			UserController::ShowProfile($args[1]);
			BaseController::DisplayMessages(); // Get Messages
			BaseController::GetHTMLIncludes(); //Get CSS and Scripts
			$smarty->display('user.tpl'); // Display the Page
			break;
		default: // If no sub page is specified
			if ($user->uid == 0){ //User is not logged in
				$smarty->assign('noSess', true);
			}else{
				UserController::ShowProfile($user->uid);
			}
			break;
	}
	BaseController::DisplayMessages(); // Get Messages
	BaseController::GetHTMLIncludes(); //Get CSS and Scripts
	if(!$ajax){
		$smarty->display('user.tpl'); // Display the Page
	}
}

?>
