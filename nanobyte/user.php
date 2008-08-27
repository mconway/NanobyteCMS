<?php
/*
 * User page logic
 * All pages that start with user/ are directed here.
 * @param $args - array retrieved from GET and POST data. Post data is from $_POST['actions'] only
 * Since 5/2008
 */
function User($args){
global $smarty; // Get the Smarty object
switch($args[0]){ // What sub page are we trying to view
		case 'details': // view user details - THIS IS NOT THE PROFILE PAGE
			UserController::GetDetails($args['1']);
			break;
		case 'edit': // Edit User Details
			if (isset($_POST['commit'])){
				UserController::EditUser($smarty);
				BaseController::Redirect();
			}elseif(isset($_POST['delete'])){
				AdminController::DeleteUserRequest();
				BaseController::Redirect();
			}
			break;
		case 'login': // Log in a user
			if (isset($_POST['login'])){
				UserController::Login($_POST['user'], $_POST['pass']);
				BaseController::Redirect();
			}else{
				BaseController::Redirect('user');
			}
			break;
		case 'logout': //Logout and destroy a user session
			UserController::Logout();
			BaseController::Redirect('home');
			break;
		case 'register': // Sign up as a new user
			UserController::RegForm();
			$smarty->assign('file','form.tpl');
			BaseController::DisplayMessages($smarty);
			BaseController::GetHTMLIncludes();
			$smarty->display('user.tpl');
			break;
		default: // If no sub page is specified
			if (isset($_SESSION['hash'])){
				$a = unserialize($_SESSION['user']);
				if ($_SESSION['hash'] == $a->SessionHash()){
					if (Core::AuthUser($a, 'admin')){
						Core::SetMessage('Session Hash:'. $_SESSION['hash']);
						Core::SetMessage($a->uid);
					}
				}
			}else{
				Core::SetMessage('Not Logged In', 'status');
				$smarty->assign('noSess', true);
			}
			BaseController::DisplayMessages($smarty); // Get Messages
			BaseController::GetHTMLIncludes(); //Get CSS and Scripts
			$smarty->display('user.tpl'); // Display the Page
			break;
	}
}

?>
