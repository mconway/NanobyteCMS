<?php

class UserController extends BaseController{
	
	public static function ListUsers(&$smarty,&$user,$page=1){
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
			'class' => 'action-link-tab'
		);
		$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/user/add',$options));
		// bind the params to smarty
		$smartyArray = array(
			'pager'=>BaseController::Paginate($user->output['limit'], $user->output['nbItems'], 'admin/user/list/', $page),
			'sublinks'=>$links,
			'cb'=>true,
			'self'=>'admin/user/select',
			'actions'=>$actions,
			'extra'=>$extra,
			'list'=>$list
		);

		$smarty->assign($smartyArray);
	}
	
	public static function Edit($id){
		global $smarty;
		$edituser = new User($id);
//		$profile = new UserProfile($edituser->uid);
		$tablinks = array('User Account', 'User Profile');
		//create the form object 
		$form = new HTML_QuickForm('edituser','post','admin/users/commit/'.$edituser->uid);
		//set form default values
		$form->setdefaults(array(
			'name'=>$edituser->name, 
			'joined'=>date('G:i m.d.y T',$edituser->joined),
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

		$form->addElement('header','','User Profile Information');
		$form->addElement('file', 'avatar', 'Upload Avatar');
		$form->addElement('text', 'location', 'Location',array('size'=>25, 'maxlength'=>15));
		$form->addElement('textarea', 'about', 'About Me',array('rows'=>20,'cols'=>60));
		
		$form->addElement('submit', 'commit', 'Save Changes');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		//add form rules
		$form->addRule('email', 'Please enter a valid email address', 'required');
		$form->addRule('email', 'Please enter a valid email', 'email', true);
		$form->addRule(array('password','confirm'),'The passwords you have entered do not match','compare');
		//If the form has already been submitted - validate the data
		if($form->validate()){
			$values = $form->exportValues();
			//print_r($values);
			if($values['password']){
				$edituser->pwchanged = $values['password'];
			} 
			if($edituser->email != $values['email']){
				$edituser->email = $values['email'];
			}
			$avatar = $form->getElementValue('avatar');
			if(!empty($avatar['name'])){
				$image = BaseController::HandleImage($avatar,'100');
				$profile->avatar = $image;
			}
			$profile->location = $values['location'];
			$profile->about = $values['about'];
			
			//$form->process(array($edituser,'commit'));
			//$form->process(array($profile,'commit'));
			if ($edituser->commit() === true || $profile->commit() === true){
				Core::SetMessage('Your Information has been updated!','info');
			}else{
				Core::SetMessage('Your Information was not updated!','error');
			}

			BaseController::Redirect();
			exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray());
		$smarty->assign('tabbed', $tablinks);
	}
	
	public static function RegForm(&$argsArray,$redirect=null){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		
		//create the form object 
		$form = new HTML_QuickForm('newuser','post','user/register/');
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
		if($form->validate()){
//			$form->process(array($user,'Create'));
//			var_dump($form->exportValues());
			$newUser = new User();
			$newUser->Create($form->exportValues());
//			var_dump($newUser);
			Core::SetMessage('Your user account has been created!','info');
			$jsonObj->callback = 'nanobyte.addRow';
			$jsonObj->args = $newUser;
//			var_dump($jsonObj->args);
			//BaseController::Redirect();
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
	}
	
	public static function Logout(){
		global $user;
			$user->Logout();
			if (!$_SESSION['hash']){
				Core::SetMessage('You are now logged out', 'status');
			}else{
				Core::SetMessage('You are still logged in! Please try again.', 'error');
			}
	}
	
	public static function CheckUser($user){
		if ($user == '' || strlen($user) < 4){
			Core::SetMessage('Usernames must be at least 4 Characters, and cannot contain spaces.','error');
			return false;
		}else{
			return true;
		}
	}
	
	public static function CheckPass($pass, $confirm=null){
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
	
	public static function CheckEmail($email, $confirm){
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
	
	public static function DomainExists($email,$record = 'MX') {  
   		list($user,$domain) = split('@',$email);  
    	if (checkdnsrr($domain,$record)){
    		return true;
    	}else{
    		return false;
    	}
    } 
	
	public static function Login($username,$pass){
		global $user;
		$user->Login($_POST['user'], $pass);
	}
	
	public static function NewUser($username, $pw, $cp, $e, $ce){
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
	
	public static function GetDetails($id){
		$user = new User($id);
		return $user->uid .'|'. $user->name .'|'.$user->email.'|'.$user->joined.'|'.$user->roles;
	}
	
	public static function AddPerm($perm){
		foreach($_POST['user'] as $u){
			$user = new User($u);
			$user->group = $perm;
			$user->commit();
		}
	}
	
	public static function Admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		$content = '';
		switch($args[1]){
			case 'edit':
				$jsonObj->title = 'Edit User';
				self::Edit($args[2]);
				$content = $smarty->fetch('form.tpl');
				$jsonObj->callback = 'Dialog';
				break;	
			case 'add':
				$jsonObj->callback = 'Dialog';
				$jsonObj->title = 'Add new User';
				self::RegForm($argsArray,true);
				$content = $smarty->fetch('form.tpl');

				break;
			case 'commit': // Save User Details
				if (isset($_POST['commit'])){
					self::Edit($args[2]);
					parent::Redirect();
				}
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
				self::ListUsers($smarty, $user, $args[2]);
				$content = $smarty->fetch('list.tpl'); 
				break;
			case 'details':
				$content = self::GetDetails($args[2]);
				$jsonObj->callback = 'Dialog';
				break;
			default: 
				$tabs = array(Core::l('Users','admin/user/list'),Core::l('Groups','admin/group/list'));
				$smarty->assign('tabs',$tabs);
				if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
				break;
		}
		$jsonObj->content = $content;
	}

	public static function DeleteUserRequest(&$user,&$jsonObj){
 		if (isset($_GET['uid'])){
 			$delUser[] = $_GET['uid'];
 		}elseif(isset($_POST['user'])){
 			$delUser = $_POST['user'];
 		}
 		if(isset($delUser)){
	 		foreach($delUser as $delete){
	 			if ($user->uid != $delete){
	 				$jsonObj->callback = 'nanobyte.deleteRows';
 					if (Admin::DeleteObject('user', 'uid', $delete) === true && Admin::DeleteObject('user_profiles', 'uid', $delete)){
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

	public static function Display(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
			switch($args[0]){ // What sub page are we trying to view
				case 'details': // view user details - THIS IS NOT THE PROFILE PAGE
					$content = self::GetDetails($args['1']);
					break;
				case 'edit':
					if (Core::AuthUser($user, 'edit user accounts') || $user->uid === $args[1]){
						self::EditUser($args[1]);
						$content = $smarty->fetch('form.tpl');
					}else{
						Core::SetMessage('You do not have access to this page!','error');
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
						Core::SetMessage('Authentication Successful!','info');
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
					self::RegForm($argsArray);
					$content = $smarty->fetch('form.tpl');
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

}
?>
