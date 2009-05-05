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
		 		$smarty->assign('links', MenuController::GetMenu('admin',$user->group));  // Get the Admin Menu
		 		$jsonObj = new Json();
				switch($args[0]){  // What sub page are we trying to view
		 		//Users Sub Page (Administer Users)
					case 'users':
						//What action is being performed. This is the args array passed from index.php
						$tabs = array(Core::l('Users','admin/users/list'),Core::l('Groups','admin/groups/list'));
						$smarty->assign('tabs',$tabs);
						if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
						switch($args[1]){
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
							case 'select':
								switch($args[2]){
									case 'delete':
										AdminController::DeleteUserRequest();
										break;
									default: 
										if(isset($_POST['users'])){
											UserController::AddPerm($args[2]);
											Core::SetMessage('Group "'.ucfirst($args[2]).'" has been added to the selected users!','info');
										}else{
											Core::SetMessage('You must select a user!','error');
										}
										break;
								}
								break;
							case 'list':
								UserController::GetList(); 
								$content = $smarty->fetch('list.tpl'); 
								break;
						}
						break;
					//administer permissions
					case 'groups':
						$perms = new Perms();
						switch($args[1]){
							case 'add':
								AdminController::AddGroup();
								$content = $smarty->fetch('form.tpl');
								break;
							case 'edit':
								if(isset($_POST['submit'])){
							 		AdminController::WriteGroups($perms);
								}
								AdminController::EditGroups($perms);
								$content = $smarty->fetch('list.tpl');
								break;
							case 'list': 
								$perms->GetAll();
								AdminController::ListPerms($perms);
								$content = $smarty->fetch('list.tpl');
								break;
							case 'select':
								switch($args[2]){
									case 'delete':
										AdminController::DeleteGroup();
										break;
									default: 
										break;
								}
								break;
						}
						break;
					//Posts Sub Page (administer posts)
					case 'content':
						$contents = new Content();
						$contents->GetTypes();
						$tabs = array();
						foreach($contents->types as $key=>$tab){
						 array_push($tabs, Core::l($tab,'admin/content/'.$key));
						}
						array_push($tabs,Core::l('Comments','admin/content/comments'));
						array_push($tabs,Core::l('Settings','admin/content/settings'));
						if(is_numeric($args[1])){
							Core::SetMessage('Numeric!');
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
								case 'settings':
									$content = '';
									if($args[2]=='addtype'){
										
										$smarty->assign('form',ContentController::Form_Settings_AddType());
										$content .= $smarty->fetch('form.tpl');
									}
									$options['id'] = 'addtype';
									$content .= Core::l('Add Content Type', 'admin/content/settings/addtype', $options);
									break;
							}
						}
						//If File is not set, get the post list and display
						$smarty->assign('tabs',$tabs);
						if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
						if (!$content){ 
							$content =  $smarty->fetch('list.tpl');
						}
		 				break;
					// Modules Sub Page
		 			case 'modules':
					case 'blocks':
						//What action is being performed.
						$tabs = array();
						array_push($tabs, Core::l('Modules','admin/modules/list'));
						array_push($tabs, Core::l('Blocks','admin/blocks/list'));
						$smarty->assign('tabs',$tabs);
						if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
						switch($args[1]){ 
							//We call the same function for Disable and Enable.
							case 'enable':  
							case 'disable':
								ModuleController::UpdateStatus($args[2], $args[1]);
								break;
							// Default is to display the module list
							case 'list':
								$func = 'List'.$args[0];
								ModuleController::$func();
								$content = $smarty->fetch('list.tpl');
								break;
						}
						break;
					//Menus Sub Page
					case 'menus':
						$tabs = array(Core::l('Menus','admin/menus/list'));
						$smarty->assign('tabs',$tabs);
						$smarty->assign('showID',true);
						if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
						switch($args[1]){
							case 'add':
								if(isset($_POST['submit'])){
									MenuController::WriteMenu($args[2]);
									//BaseController::Redirect('admin/menus');
//									exit;
								}
								if($args[2]){ //Menu is specified, therefore we are adding an item to it
									MenuController::AddMenuItem();
									$content = $smarty->fetch('list.tpl');
								}else{ //no menu is specified, so lets create a new one
									MenuController::AddMenu();
									$content = $smarty->fetch('form.tpl');
								}
								
								break;
							case 'delete':
								if($args[2] && $args[3]){
									Admin::DeleteObject('menu_links','id',$args[3]);
									BaseController::Redirect();
								}elseif($args[2]){
									$menu = new Menu($args[2]);
									$menu->Delete();
								}
								break;
							case 'edit':
								if($args[2]){
									if (isset($_POST['submit'])){
										MenuController::WriteMenu();
										Core::SetMessage('Your changes have been saved.','info');
									}
									MenuController::ListMenuItems($args[2]);
									$content = $smarty->fetch('list.tpl');
								}else{
									Core::SetMessage('You must specify a menu!', 'error');
								}
								break;
							case 'list':
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
						$tabs = array(Core::l('Site Statistics','admin/stats/list'));
						$smarty->assign('tabs',$tabs);
						if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
						switch($args[1]){
							case 'list':
								unset($args[array_search('ajax',$args)]);
								AdminController::ListStats($args[2]); // Get the stats list
								$content = $smarty->fetch('list.tpl'); // Display the list
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
						$smarty->assign('users',Mod_Users::NewUsers());
						AdminController::BrowserGraph();
						$content = $smarty->fetch('admin.main.tpl');
						break;
				}
				if(!$ajax){
					BaseController::AddJs('templates/'.THEME_PATH.'/js/admin.js');
					BaseController::DisplayMessages(); // Get any messages
					BaseController::GetHTMLIncludes(); // Get CSS and Script Files
					$smarty->assign('content',$content);
					$smarty->display('admin.tpl'); // Display the Admin Page
				}else{
					$jsonObj->content = $content;
					$jsonObj->messages = BaseController::DisplayMessages();
					print json_encode($jsonObj);
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