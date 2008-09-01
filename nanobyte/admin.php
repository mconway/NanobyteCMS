<?php
/*
 * Admin page logic
 * All pages that start with admin/ are directed here.
 * @param $args - array retrieved from GET and POST data. Post data is from $_POST['actions'] only
 * Since 5/2008
 */
function admin($args){
//Get the Smarty Object
global $smarty;
//Check to see if the HTML_QuickFoms PEAR package is installed
//@include 'HTML/QuickForm.php';
//if(!class_exists('HTML_QuickForm', false)){
//	Core::SetMessage('HTML QuickForms is not installed!','status');
//}

//Check to see if the user is logged in
if(isset($_SESSION['hash'])){
	 $user = unserialize($_SESSION['user']);
	 if ($_SESSION['hash'] == $user->SessionHash()){
	 	// Check user permissions
	 	if (Core::AuthUser($user, 'access admin pages')){
	 		$smarty->assign('auth', 1); //Set auth to TRUE
	 		$smarty->assign('page', $args[0]); //Set Page Name
	 		$smarty->assign('links', AdminController::GetAdminMenu());  // Get the Admin Menu
	 		switch($args[0]){  // What sub page are we trying to view
	 		//Users Sub Page (Administer Users)
				case 'users':
					switch($args[1]){  // What action is being preformed. This can be through GET or POST
						case 'delete':
							AdminController::DeleteUserRequest();
							break;
						case 'edit':
							$editMe = new User($args[2]);
							UserController::EditUser($smarty, $editMe);
							$smarty->assign('file', 'form.tpl');
							break;	
						case 'add':
							UserController::RegForm(true);
							$smarty->assign('file','form.tpl');
							break;
						default:
							if(isset($_POST['users'])){
								UserController::AddPerm($args[1]);
								Core::SetMessage('Group "'.ucfirst($args[1]).'" has been added to the selected users!','info');
							}
							break;
					}
					$file = $smarty->get_template_vars('file'); //Find out if FILE has been set already
					if (!$file){ //If file is not set, get the user list and display
						UserController::ListUsers($smarty); 
						$smarty->assign('file', 'list.tpl'); 
					}
					break;
				//Posts Sub Page (administer posts)
				case 'posts':
					switch($args[1]){ // What Action is being performed? GET or POST
						case 'delete':
							PostController::DeletePostRequest();
							break;
						case 'add':
							PostController::PostForm();
							$smarty->assign('file', 'form.tpl');
							break;
						case 'edit':
							if(!$args[2]){
								Core::SetMessage('You did not specify a post to edit!','error');
								BaseController::Redirect('admin/posts');
							}else{
								PostController::EditPost($args[2]);
								$smarty->assign('file', 'form.tpl');
							}
							break;	
					}
					$file = $smarty->get_template_vars('file'); // Fild out if FILE is set
					if (!$file){ // If File is not set, get the post list and display
						PostController::ListPosts($smarty);
						$smarty->assign('file', 'list.tpl');
					}
	 				break;
				// Modules Sub Page
	 			case 'modules':
					switch($args[1]){ //What action is being performed. GET or POST
						case 'enable': // We call the same function for Disable and Enable. 
						case 'disable':
							ModuleController::UpdateStatus($args[2], $args[1]);
							break;
						default: // Default is to display the module list
							ModuleController::ListMods($smarty);
							$smarty->assign('file', 'list.tpl');
							break;
					}
					break;
				//Menus Sub Page
				case 'menus':
					switch($args[1]){
						case 'add':
						case 'delete':
						case 'edit':
							if($args[2]){
								MenuController::ListMenuItems($args[2]);
								$smarty->assign('file','list.tpl');
							}else{
								Core::SetMessage('You must specify a menu!', 'error');
							}
							break;
						default:
							MenuController::ListMenus();
							$smarty->assign('file','list.tpl');
					}
					break;
				// Settings Sub Page
	 			case 'settings':
	 				AdminController::ShowConfig();
					$smarty->assign('file','form.tpl');
	 				break;
				// Stats Sub Page
	 			case 'stats':
					AdminController::ListStats($smarty, $args['1']); // Get the stats list
					$smarty->assign('file','list.tpl'); // Display the list
					break;
				//administer permissions
				case 'perms':
				//args: add, edit, delete
					$perms = new Perms();
					switch($args[1]){
						case 'edit':
							if(isset($_POST['submit'])){
						 		AdminController::WriteGroups($perms);
							}
							AdminController::EditGroups($perms);
							$smarty->assign('file','list.tpl');
							break;
						default: 
							$perms->GetAll();
							AdminController::ListPerms($perms);
							$smarty->assign('file','list.tpl');
							break;
					}

					break;
				// When no sub page is specified display default
				default:
					if (isset($_POST['save'])){
						PostController::SavePost();
					}
					BaseController::NewUsers($smarty);
					$smarty->assign('file', 'admin.main.tpl');
					break;
			}
			BaseController::DisplayMessages($smarty); // Get any messages
			BaseController::GetHTMLIncludes(); // Get CSS and Script Files
			$smarty->display('admin.tpl'); // Display the Admin Page
			
		// If the user is not autorized AT ANY TIME - set a message and redirect them to the home page
		}else{
			Core::SetMessage('You do not have Permission to access this page!','error');
			BaseController::Redirect('home');
		}
	 }else{
	 	Core::SetMessage('You do not have Permission to access this page!','error');
	 	BaseController::Redirect('home');
	 }
}else{
	Core::SetMessage('You do not have Permission to access this page!','error');
	BaseController::Redirect('home');
}
}
?>