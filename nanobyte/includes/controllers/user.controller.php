<?php

class UserController extends BaseController{
	
	public static function addPerm($perm){
		foreach($_POST['user'] as $u){
			$user = new User($u);
			$user->group = $perm;
			$user->commit();
		}
	}
	
	public static function admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
		
		$content = '';
		if(isset($args[1])){
			switch($args[1]){
				case 'edit':
					$smarty->assign('form',self::Edit($args[2]));
					$content = $smarty->fetch('form.tpl');
					if(isset($_POST['submit'])){
						$jsonObj->callback = 'nanobyte.closeParentTab';
						$jsonObj->args = 'input[name=submit][value=Save Changes]';
					}
					$jsonObj->title = "Edit user";
					break;
				case 'add':
					$smarty->assign('form',self::RegForm($core,true));
					$content = $smarty->fetch('form.tpl');
					$jsonObj->title = "Add new user";
					break;
				case 'select':
					switch($args[2]){
						case 'delete':
							self::DeleteUserRequest($user,$jsonObj);
							break;
						default: 
							if(isset($_POST['user'])){
								self::AddPerm($args[2]);
								Core::SetMessage('Group "'.ucfirst($args[2]).'" has been added to the selected users!','info');
								$jsonObj->callback = 'nanobyte.changeGroup';
								$jsonObj->args = implode('|',$_POST['user']);
							}else{
								Core::SetMessage('You must select a user!','error');
							}
							break;
					}
					break;
				case 'list':
					$smarty->assign(self::ListUsers($smarty, $user, $args[2]));
					$content = $smarty->fetch('list.tpl'); 
					break;
				case 'details':
					$content = self::GetDetails($args[2]);
					break;
				case 'email':
					$email = new Email();
					$smarty->assign('form',parent::emailForm('admin/user/email',$email->getEmailData('register')));
					$content = $smarty->fetch('form.tpl');
				default: 
					$tabs = array(Core::l('Users','admin/user/list'),Core::l('Groups','admin/group/list'));
					$smarty->assign('tabs',$tabs);
					if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
					break;
			}
		}else{
			$tabs = array(Core::l('Users','admin/user/list'),Core::l('Groups','admin/group/list'),Core::l('Email','admin/user/email'));
			$smarty->assign('tabs',$tabs);
			if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		}
		$jsonObj->content = $content;
	}
	
	public static function checkEmail($email, $confirm){
		if($email != $confirm){
			Core::SetMessage('Your email addresses did not match!', 'error');
			return false;
		}elseif(!UserController::DomainExists($email)){
			Core::SetMessage('Invalid Email Address', 'error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function checkPass($pass, $confirm=null){
		if($pass == '' || strlen($pass) < 6){
			Core::SetMessage('Passwords must be at least 6 characters and cannot contain spaces.','error');
			return false;
		}elseif ($confirm !== null && $pass !== $confirm){
			Core::SetMessage('Your passwords did not match', 'error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function checkUser($user){
		if ($user == '' || strlen($user) < 4){
			Core::SetMessage('Usernames must be at least 4 Characters, and cannot contain spaces.','error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function deleteUserRequest(&$user,&$jsonObj){
 		if (isset($_GET['uid'])){
 			$delUser[] = $_GET['uid'];
 		}elseif(isset($_POST['user'])){
 			$delUser = $_POST['user'];
 		}
 		if(isset($delUser)){
	 		foreach($delUser as $delete){
	 			if ($user->uid != $delete){
	 				$jsonObj->callback = 'nanobyte.deleteRows';
 					if (Admin::DeleteObject('user', 'uid', $delete) === true){
						Core::SetMessage('User '.$delete.' has been deleted!', 'info');
						$jsonObj->args .= $delete."|";
					} else {
						Core::SetMessage('Unable to delete user '.$delete.' , an error has occurred.', 'error');
					}
 				}else{
 					Core::SetMessage('You are not allowed to delete yourself!', 'error');
	 			}
 			}	
 		}else{
 			Core::SetMessage('You must choose a user(s) to delete!', 'error');
 		}
	}
	
	public static function display(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
			switch($args[0]){ // What sub page are we trying to view
				case 'details': // view user details - THIS IS NOT THE PROFILE PAGE
					$content = self::GetDetails($args['1']);
					break;
				case 'edit':
					if ($core->AuthUser($user, 'edit user accounts') || $user->uid === $args[1]){
						self::EditUser($args[1]);
						$content = $smarty->fetch('form.tpl');
					}else{
						$core->SetMessage('You do not have access to this page!','error');
					}
					parent::DisplayMessages();
					parent::GetHTMLIncludes();
					$smarty->display('index.tpl');
					break;	
				case 'commit': // Save User Details
					if (isset($_POST['commit'])){
						self::EditUser($args[1]);
						parent::Redirect();
					}elseif(isset($_POST['delete'])){
						AdminController::DeleteUserRequest();
						parent::Redirect();
					}
					break;
				case 'login': // Log in a user
					self::Login($_POST['user'], $_POST['pass']);
					if($user->success===true){
						$core->SetMessage('Authentication Successful!','info');
						$content = 'reload';
					}else{
						Core::SetMessage('Username or Password is incorrect','error');
					}
					break;
					
				case 'logout': //Logout and destroy a user session
					self::Logout();
					parent::Redirect(HOME);
					break;
				case 'register': // Sign up as a new user
					if(isset($_POST['submit'])){
						$jsonObj->callback = 'nanobyte.closeParentTab';
						$jsonObj->args = 'input[name=submit][value=Submit]';
					}
					$smarty->assign('form',self::RegForm($argsArray));
					$content = $smarty->fetch('form.tpl');
					break;
				case "reset_pw":
					$smarty->assign('form',self::ResetPassword($user));
					$content = $smarty->fetch('form.tpl');
					$jsonObj->callback = 'Dialog';
					$jsonObj->title = 'Reset Password';
					break;
//				case 'profiles':
//					self::ShowProfile($args[1]);
//					parent::DisplayMessages(); // Get Messages
//					parent::GetHTMLIncludes(); //Get CSS and Scripts
//					$smarty->display('user.tpl'); // Display the Page
//					break;
				default: // If no sub page is specified
					if ($user->uid == 0){ //User is not logged in
						$smarty->assign('noSess', true);
					}else{
//						self::ShowProfile($user->uid);
					}
					break;
			}	
			if(!$ajax){
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$smarty->assign('content',$content);
				$smarty->display('user.tpl'); // Display the Page
			}else{
				$jsonObj->content = $content;
				$jsonObj->messages = parent::DisplayMessages();
				print json_encode($jsonObj);
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
				Core::SetMessage('Your Information has been updated!','info');
			}else{
				Core::SetMessage('Your Information was not updated!','error');
			}

//			BaseController::Redirect();
			return;
		}
		//send the form to smarty
		return $form->toArray();
	}
	
	public static function getDetails($id){
		$user = new User($id);
		return $user->uid .'|'. $user->name .'|'.$user->email.'|'.$user->joined.'|'.$user->group;
	}
	
	public static function listUsers(&$smarty,&$user,$page=1){
		$perms = new Perms();
		$perms->GetNames();
		//create list
		$user->Read(BaseController::GetStart($page,10), 10, $page); //array of objects
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
				'group'=>$perms->names[$u['gid']],
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
			'pager'=>BaseController::Paginate($user->output['limit'], $user->output['nbItems'], 'admin/user/list/', $page),
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
		global $user;
		$user->Login($_POST['user'], $pass);
	}
	
	public static function logout(){
		global $user;
			$user->Logout();
			if (!isset($_SESSION['hash'])){
				Core::SetMessage('You are now logged out', 'status');
			}else{
				Core::SetMessage('You are still logged in! Please try again.', 'error');
			}
	}
	
	public static function newUser($username, $pw, $cp, $e, $ce){
		global $user;
		if (self::CheckUser($username) && self::CheckPass($pw, $cp) && self::CheckEmail($e, $ce)){
			$newUser = $user->CreateUser($username, $pw, $e);
			if (!$newUser){
				Core::SetMessage ('This username Already Exists', 'error');
				return false;
			}else{
				Core::SetMessage('New user has been created!', 'info');
				UserController::Redirect();
				return true;
			}
		}else{
			Core::SetMessage('Error Creating User. Please try again later.', 'error');
			return false;
		}
	}
	
	public static function regForm(&$core,$redirect=null){
		
		//create the form object 
		$form = new HTML_QuickForm('newuser','post','user/register');
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
				$core->SetMessage('Your user account has been created!','info');
			}
//			BaseController::Redirect();
		}
		//send the form to smarty
		return $form->toArray(); 
	}
	
	public static function resetPassword(&$user){
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

}
?>
