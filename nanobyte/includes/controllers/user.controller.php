<?php

class UserController extends BaseController{
	
	public static function addPerm($perm){
		foreach($_POST['user'] as $u){
			$user = new User($u);
			$user->permissions->addUserToGroup($user->uid,$perm);
		}
	}
	
	public static function admin(){
		$Core = parent::getCore();
		
		$content = '';
		if(isset($Core->args[1])){
			switch($Core->args[1]){
				case 'edit':
					$Core->smarty->assign('form',self::Edit($Core->args[2]));
					$content = $Core->smarty->fetch('form.tpl');
					if(isset($_POST['submit'])){
						$Core->json_obj->callback = 'nanobyte.closeParentTab';
						$Core->json_obj->args = 'input[name=submit][value=Save Changes]';
					}
					$Core->json_obj->title = "Edit user";
					break;
				case 'add':
					$Core->smarty->assign('form',self::RegForm($Core,true));
					$content = $Core->smarty->fetch('form.tpl');
					$Core->json_obj->title = "Add new user";
					break;
				case 'select':
					switch($Core->args[2]){
						case 'delete':
							self::DeleteUserRequest($Core);
							break;
						default: 
							if(isset($_POST['user'])){
								self::AddPerm($Core->args[2]);
								$Core->setMessage('Group "'.ucfirst($Core->args[2]).'" has been added to the selected users!','info');
								$Core->json_obj->callback = 'nanobyte.changeGroup';
								$Core->json_obj->args = implode('|',$_POST['user']);
							}else{
								$Core->setMessage('You must select a user!','error');
							}
							break;
					}
					break;
				case 'list':
					$Core->smarty->assign(self::ListUsers($Core));
					$content = $Core->smarty->fetch('list.tpl'); 
					break;
				case 'details':
					$content = self::GetDetails($Core->args[2]);
					break;
				case 'email':
					$email = new Email();
					$Core->smarty->assign('form',parent::emailForm('admin/user/email',$email->getEmailData('register')));
					$content = $Core->smarty->fetch('form.tpl');
				default: 
					$tabs = array($Core->l('Users','admin/user/list'),$Core->l('Groups','admin/group/list'));
					$Core->smarty->assign('tabs',$tabs);
					if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
					break;
			}
		}else{
			$tabs = array(Core::l('Users','admin/user/list'),Core::l('Groups','admin/group/list'),Core::l('Email','admin/user/email'));
			$Core->smarty->assign('tabs',$tabs);
			if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		}
		$Core->json_obj->content = $content;
	}
	
	public static function checkEmail($email, $confirm){
		$Core = parent::getCore();
		if($email != $confirm){
			$Core->setMessage('Your email addresses did not match!', 'error');
			return false;
		}elseif(!UserController::DomainExists($email)){
			$Core->setMessage('Invalid Email Address', 'error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function checkPass($pass, $confirm=null){
		$Core = parent::getCore();
		if($pass == '' || strlen($pass) < 6){
			$Core->SetMessage('Passwords must be at least 6 characters and cannot contain spaces.','error');
			return false;
		}elseif ($confirm !== null && $pass !== $confirm){
			$Core->SetMessage('Your passwords did not match', 'error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function checkUser($user){
		$Core = parent::getCore();
		if ($user == '' || strlen($user) < 4){
			$Core->etMessage('Usernames must be at least 4 Characters, and cannot contain spaces.','error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function deleteUserRequest(){
		$Core = parent::getCore();
 		if (isset($_GET['uid'])){
 			$delUser[] = $_GET['uid'];
 		}elseif(isset($_POST['user'])){
 			$delUser = $_POST['user'];
 		}
 		if(isset($delUser)){
	 		foreach($delUser as $delete){
	 			if ($Core->user->uid != $delete){
	 				$Core->json_obj->callback = 'nanobyte.deleteRows';
 					if (Admin::deleteObject('user', 'uid', $delete) === true){
						$Core->setMessage('User '.$delete.' has been deleted!', 'info');
						$Core->json_obj->args .= $delete."|";
					} else {
						$Core->setMessage('Unable to delete user '.$delete.' , an error has occurred.', 'error');
					}
 				}else{
 					$Core->setMessage('You are not allowed to delete yourself!', 'error');
	 			}
 			}	
 		}else{
 			$Core->setMessage('You must choose a user(s) to delete!', 'error');
 		}
	}
	
	public static function display(){
		$Core = parent::getCore();
		$content = "";
		if(isset($Core->args[0])){
//			if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
			switch($Core->args[0]){ // What sub page are we trying to view
				case 'details': // view user details - THIS IS NOT THE PROFILE PAGE
					$content = self::GetDetails($Core->args[1]);
					break;
				case 'edit':
					if ($Core->authUser($user, 'edit user accounts') || $Core->user->uid === $Core->args[1]){
						self::editUser($Core->args[1]);
						$content = $Core->smarty->fetch('form.tpl');
					}else{
						$Core->setMessage('You do not have access to this page!','error');
					}
					parent::displayMessages();
					parent::getHTMLIncludes();
					$Core->smarty->display('index.tpl');
					break;	
				case 'commit': // Save User Details
					if (isset($_POST['commit'])){
						self::EditUser($Core->args[1]);
						parent::Redirect();
					}elseif(isset($_POST['delete'])){
						AdminController::DeleteUserRequest();
						parent::Redirect();
					}
					break;
				case 'login': // Log in a user
					self::Login($_POST['user'], $_POST['pass']);
					if($Core->user->success===true){
						$Core->setMessage('Authentication Successful!','info');
						$Core->json_obj->callback = 'reload';
					}else{
						$Core->json_obj->callback = 'reset';
						$Core->setMessage('Username or Password is incorrect','error');
					}
					break;
					
				case 'logout': //Logout and destroy a user session
					self::Logout();
					parent::Redirect(HOME);
					break;
				case 'register': // Sign up as a new user
					if(isset($_POST['submit'])){
						$Core->json_obj->callback = 'nanobyte.closeParentTab';
						$Core->json_obj->args = 'input[name=submit][value=Submit]';
					}
					$Core->smarty->assign('form',self::RegForm($Core));
					$content = $Core->smarty->fetch('form.tpl');
					break;
				case 'reset_pw':
					$Core->smarty->assign('form',self::ResetPassword($Core->user));
					$content = $smarty->fetch('form.tpl');
					$Core->json_obj->callback = 'Dialog';
					$Core->json_obj->title = 'Reset Password';
					break;
				case 'profiles':
				default:
					if ($Core->user->uid == 0){ //User is not logged in
						$Core->smarty->assign('noSess', true);
					}
					if($Core->authUser('view user profiles') && is_numeric($Core->args[0])){
						$content = self::showprofile($Core->args[0]);
					}
					break;
			}
		}else{
			if ($Core->user->uid == 0){ //User is not logged in
				$Core->smarty->assign('noSess', true);
			}else{
				if($Core->authUser('view user profiles')){
					$content =  self::showProfile($Core->user->uid);
				}
				
			}
		}
		if(!$Core->ajax){
			parent::DisplayMessages(); // Get any messages
			parent::GetHTMLIncludes(); // Get CSS and Script Files
			$Core->smarty->display('user.tpl'); // Display the Page
		}else{
			$Core->json_obj->content = $content;
			$Core->json_obj->messages = parent::DisplayMessages();
			print json_encode($Core->json_obj);
		}
	}
	
	public static function domainExists($email,$record = 'MX') {  
   		list($user,$domain) = explode('@',$email);  
    	if (checkdnsrr($domain,$record)){
    		return true;
    	}else{
    		return false;
    	}
    } 
	
	public static function edit($id){
		$Core = parent::getCore();
		$edituser = new User($id);
//		$profile = new UserProfile($edituser->uid);
		//create the form object 
		$form = new HTML_QuickForm('edituser','post','admin/user/edit/'.$edituser->uid);
		//set form default values
		$form->setdefaults(array(
			'name'=>$edituser->name, 
			'joined'=>date('G:i m/d/Y T',$edituser->joined),
			'email'=>$edituser->email,
//			'avatar'=>$profile->avatar,
//			'location'=>$profile->location,
//			'about'=>$profile->about
		));
		//create form elements
		$form->addElement('header','','User Account Details');
		$form->addElement('text', 'name', 'Username', array('size'=>25, 'maxlength'=>15, 'readonly'=>'readonly'));
		$form->addElement('text', 'joined', 'Joined', array('size'=>25, 'maxlength'=>15, 'readonly'=>'readonly'));
		$form->addElement('password', 'password', 'Password', array('size'=>25, 'maxlength'=>10));
		$form->addElement('password', 'confirm', 'Confirm Password', array('size'=>25, 'maxlength'=>10));
		$form->addElement('text', 'email', 'Email', array('size'=>25, 'maxlength'=>50));
		
		$form->addElement('submit', 'submit', 'Save Changes');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		//add form rules
		$form->addRule('email', 'Please enter a valid email address', 'required');
		$form->addRule('email', 'Please enter a valid email', 'email', true);
		$form->addRule(array('password','confirm'),'The passwords you have entered do not match','compare');
		//If the form has already been submitted - validate the data
		if(isset($_POST['submit']) && $form->validate()){
			$values = $form->exportValues();
			if($values['password']){
				$edituser->pwchanged = $values['password'];
			} 
			if($edituser->email != $values['email']){
				$edituser->email = $values['email'];
			}
			if ($edituser->commit() === true){
				$Core->SetMessage('Your Information has been updated!','info');
			}else{
				$Core->SetMessage('Your Information was not updated!','error');
			}

//			parent::Redirect();
			return;
		}
		//send the form to smarty
		return $form->toArray();
	}
	
	public static function getDetails($id){
		$user = new User($id);
		return $user->uid .'|'. $user->name .'|'.$user->email.'|'.$user->joined.'|'.$user->group;
	}
	
	public static function listUsers(){
		$Core = parent::getCore();
		$smarty = $Core->smarty;
		$user = $Core->user;
		$page = isset($Core->args[2]) ? $Core->args[2] : 1;
		$perms = new Perms();
		$perms->GetNames();
		//create list
		$user->Read(parent::GetStart($page,10), 10, $page); //array of objects
		$list = array();
		$options = array(
			'image' => '16',
			'class' => 'action-link-tab'
		);
		foreach ($user->output['items'] as $key=>$u){
			$options['title'] = "Details for - ".$u['username'];
			$actions = Core::l('info','content/'.$u['uid'],$options).' | ';
			$options['title'] = "Editing - ".ucfirst($u['username']);
			$actions .= Core::l('info','admin/user/edit/'.$u['uid'],$options);
			array_push($list, array(
				'id'=>$u['uid'], 
				'name'=>Core::l($u['username'], 'user/'.$u['uid']), 
				'email'=>$u['email'], 
				'group'=>$perms->names[$u['group_id']],
				'actions'=>Core::l('info','admin/user/details/'.$u['uid'],$options).' | '.Core::l('edit','admin/user/edit/'.$u['uid'],$options)
			));
		}
		//create the actions options
		$actions = array(
			'delete' => 'Delete',
			'Add to Group:' => $perms->names
		);
		$extra = 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submit" value="Go!"/>';
		$options = array(
			'image' => '24',
			'class' => 'action-link-tab',
			'title' => 'Add New User'
		);
		$links = array('add'=>Core::l('add','admin/user/add',$options));
		// bind the params to smarty
		
		$smartyArray = array(
			'pager'=>parent::Paginate($user->output['limit'], $user->output['nbItems'], 'admin/user/list/', $page),
			'sublinks'=>$links,
			'cb'=>true,
			'formAction'=>'admin/user/select',
			'actions'=>$actions,
			'extra'=>$extra,
			'list'=>$list,
		);
		
		return $smartyArray;
	}
	
	public static function login($username,$pass){
		$Core = parent::getCore();
		$Core->user->Login($_POST['user'], $pass);
	}
	
	public static function logout(){
		$Core = parent::getCore();
		$Core->user->Logout();
		if (!isset($_SESSION['hash'])){
			$Core->SetMessage('You are now logged out', 'status');
		}else{
			$Core->SetMessage('You are still logged in! Please try again.', 'error');
		}
	}
	
	public static function newUser($username, $pw, $cp, $e, $ce){
		$Core = parent::getCore();
		$user = $Core->user;
		if (self::CheckUser($username) && self::CheckPass($pw, $cp) && self::CheckEmail($e, $ce)){
			$newUser = $user->CreateUser($username, $pw, $e);
			if (!$newUser){
				$Core->SetMessage ('This username Already Exists', 'error');
				return false;
			}else{
				$Core->SetMessage('New user has been created!', 'info');
				UserController::Redirect();
				return true;
			}
		}else{
			$Core->SetMessage('Error Creating User. Please try again later.', 'error');
			return false;
		}
	}
	
	public static function regForm($form_action='user/register',$redirect=null){
		$Core = parent::getCore();
		//create the form object 
		$form = new HTML_QuickForm('newuser','post',$form_action);
		//create form elements
		$form->addElement('header','','Create new Account');
		$form->addElement('text', 'name', 'Username', array('size'=>25, 'maxlength'=>15));
		$form->addElement('password', 'password', 'Password', array('size'=>25, 'maxlength'=>10));
		$form->addElement('password', 'confirm', 'Confirm Password', array('size'=>25, 'maxlength'=>10));
		$form->addElement('text', 'email', 'Email', array('size'=>25, 'maxlength'=>50));
		$form->addElement('text', 'cemail', 'Confirm Email', array('size'=>25, 'maxlength'=>50));
		$form->addElement('submit', 'submit', 'Submit');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		//add form rules
		$form->addRule('name', 'A username is required.', 'required');
		$form->addRule('email', 'A valid Email address is required.', 'required');
		$form->addRule('cemail', 'Please confirm your email address.', 'required');
		$form->addRule('password', 'A password is required.', 'required');
		$form->addRule('confirm', 'Please confirm your password', 'required');
		$form->addRule('email', 'Please enter a valid email address', 'email', true);
		$form->addRule(array('password','confirm'),'The passwords you have entered do not match','compare');
		$form->addRule(array('email','cemail'),'The emails you have entered do not match','compare');
		//If the form has already been submitted - validate the data
		if(array_key_exists('submit',$_POST) && $form->validate()){
			$newUser = new User();
			if($newUser->Create($form->exportValues())){
				return true;
			}
			return false;
//			parent::Redirect();
		}
		//send the form to smarty
		return $form->toArray(); 
	}
	
	public static function resetPassword(){
		$Core = parent::getCore();
		$user = $Core->user;
		$form = new HTML_QuickForm('resetpw','post','user/reset_pw');
		//create form elements
		$form->addElement('header','','Request New Password');
		$form->addElement('text', 'username', 'Username', array('size'=>25, 'maxlength'=>50));
		$form->addElement('text', 'email', 'Email Address', array('size'=>25, 'maxlength'=>50));
		$form->addElement('submit', 'submit', 'Submit');
		if(array_key_exists('submit',$_POST) && $form->validate()){
			$user->SetUsername($form->exportValue('username'));
			$user->SetEmail($form->exportValue('email'));
			if($user->ValidateEmail()){
				$user->ResetPassword();
			}
		}
		return $form->toArray();
	}

	public static function showProfile($id){
		$Core = parent::getCore();
		if($Core->isEnabled('UserProfile')){
			$profile = new Mod_UserProfile($id);
			return $profile->display();
		}else{
			return array('content'=>'User profiles are not enabled.');
		}
	}

}
?>
