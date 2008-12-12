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
	global $user;
	//check to see if this is an AJAX request
	global $ajax;
	//Check to see if the user is logged in
	if(isset($_SESSION['hash'])){
		 if ($_SESSION['hash'] == $user->SessionHash()){
		 	// Check user permissions
		 	if (Core::AuthUser($user, 'access admin pages')){
		 		$smarty->assign('page', $args[0]); //Set Page Name
		 		$smarty->assign('links', AdminController::GetAdminMenu());  // Get the Admin Menu
				switch($args[0]){  // What sub page are we trying to view
		 		//Users Sub Page (Administer Users)
					case 'users':
						//What action is being performed. This is the args array passed from index.php
						switch($args[1]){
							case 'delete':
								AdminController::DeleteUserRequest();
								break;
							case 'edit':
								UserController::Edit($args[2]);
								$content = $smarty->fetch('form.tpl');
								break;	
							case 'add':
								UserController::RegForm(true);
								$content = $smarty->fetch('form.tpl');
								break;
							case 'commit': // Save User Details
								if (isset($_POST['commit'])){
									UserController::Edit($args[2]);
									BaseController::Redirect();
								}elseif(isset($_POST['delete'])){
									AdminController::DeleteUserRequest();
									BaseController::Redirect();
								}
								break;
							default:
								if(isset($_POST['users'])){
									UserController::AddPerm($args[1]);
									Core::SetMessage('Group "'.ucfirst($args[1]).'" has been added to the selected users!','info');
								}
								break;
						}
						//If file is not set, get the user list and display
						if (!$content){ 
							UserController::GetList(); 
							$content = $smarty->fetch('list.tpl'); 
						}
						break;
					//Posts Sub Page (administer posts)
					case 'content':
						$contents = new Content();
						$contents->GetTypes();
						$tabs = $contents->types;
						$tabs['comments'] = 'Comments';
						if(is_numeric($args[1])){
							ContentController::GetList($args[1],$args[2]);
						}else{
							switch($args[1]){
								case 'comments':
									CommentsController::GetList($args[2]); // should be passing page #
									break;
								case 'delete':
									ContentController::Delete();
									break;
								case 'add':
									ContentController::Form();
									$content = $smarty->fetch('form.tpl');
									break;
								case 'edit':
									if(!$args[2]){
										Core::SetMessage('You did not specify content to edit!','error');
										BaseController::Redirect('admin/posts');
									}else{
										ContentController::Edit($args[2]);
										$content = $smarty->fetch('form.tpl');
									}
									break;	
							}
						}
						//If File is not set, get the post list and display
						$smarty->assign('tabs',$tabs);
						if (!$content){ 
							$content =  $smarty->fetch('list.tpl');
						}
		 				break;
					// Modules Sub Page
		 			case 'modules':
					case 'blocks':
						//What action is being performed.
						$tabs = array('modules'=>'Modules', 'blocks'=>'Blocks');
						$smarty->assign('tabs',$tabs);
						switch($args[1]){ 
							//We call the same function for Disable and Enable.
							case 'enable':  
							case 'disable':
								ModuleController::UpdateStatus($args[2], $args[1]);
								break;
							// Default is to display the module list
							default:
								$func = 'List'.$args[0];
								ModuleController::$func();
								$content = $smarty->fetch('list.tpl');
								break;
						}
						break;
					//Menus Sub Page
					case 'menus':
						switch($args[1]){
							case 'add':
								if(isset($_POST['submit'])){
									MenuController::WriteMenu($args[2]);
									BaseController::Redirect('admin/menus');
									exit;
								}
								if($args[2]){ //Menu is specified, therefore we are adding an item to it
									MenuController::AddMenuItem();
								}else{ //no menu is specified, so lets create a new one
									MenuController::AddMenu();
								}
								$smarty->assign('file','list.tpl');
								break;
							case 'delete':
								if($args[2] && $args[3]){
									Admin::DeleteObject('menu_links','id',$args[3]);
									BaseController::Redirect();
								}elseif($args[2]){
									//MenuController::DelMenu($args[2]);
								}
								break;
							case 'edit':
								if($args[2]){
									if (isset($_POST['submit'])){
										MenuController::WriteMenu();
									}
									MenuController::ListMenuItems($args[2]);
									$content = $smarty->fetch('list.tpl');
								}else{
									Core::SetMessage('You must specify a menu!', 'error');
								}
								break;
							default:
								MenuController::ListMenus();
								$content = $smarty->fetch('list.tpl');
						}
						break;
					// Settings Sub Page
		 			case 'settings':
		 				AdminController::ShowConfig();
						$content =  $smarty->fetch('form.tpl');
		 				break;
					// Stats Sub Page
		 			case 'stats':
						AdminController::ListStats($args['1']); // Get the stats list
						$content = $smarty->fetch('list.tpl'); // Display the list
						break;
					//administer permissions
					case 'perms':
						$perms = new Perms();
						switch($args[1]){
							case 'add':
								AdminController::AddGroup();
								$content = $smarty->fetch('form.tpl');
								break;
							case 'delete':
								AdminController::DeleteGroup();
								break;
							case 'edit':
								if(isset($_POST['submit'])){
							 		AdminController::WriteGroups($perms);
								}
								AdminController::EditGroups($perms);
								$content = $smarty->fetch('list.tpl');
								break;
							default: 
								$perms->GetAll();
								AdminController::ListPerms($perms);
								$content = $smarty->fetch('list.tpl');
								break;
						}
						break;
					//Logs Subpage
					case 'logs':
					//this is just a placeholder until I have a better idea
						//Check to see if the HTML_QuickFoms PEAR package is installed
						@include 'HTML/QuickForm.php';
						if(!class_exists('HTML_QuickForm', false)){
							Core::SetMessage('HTML QuickForms is not installed!','error');
						}else{
							Core::SetMessage('HTML Quickforms is Installed.','info');
						}
						break;
			
					// When no sub page is specified display default
					default:
						if (isset($_POST['save'])){
							PostController::SavePost();
						}
						//BaseController::NewUsers();
						AdminController::BrowserGraph();
						$content = $smarty->fetch('admin.main.tpl');
						break;
				}
				BaseController::AddJs('templates/'.THEME_PATH.'/js/admin.js');
				BaseController::DisplayMessages(); // Get any messages
				BaseController::GetHTMLIncludes(); // Get CSS and Script Files
				if(!$ajax){
					$smarty->assign('content',$content);
					$smarty->display('admin.tpl'); // Display the Admin Page
				}else{
					print $content;
				}			
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