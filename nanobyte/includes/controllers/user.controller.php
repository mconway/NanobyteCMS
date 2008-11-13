<?php

class UserController extends BaseController{
	public function ListUsers(){
		global $smarty;
		//create list
		$userList = User::GetUserList(); //array of objects
		$theList = array();
		$options['image'] = '16';
		foreach ($userList as $key=>$user){
			if ($key!='nbItems' && $key!='final' && $key!='limit'){
				$theList[] = array(
					'id'=>$user->uid, 
					'name'=>Core::l($user->name, 'user/'.$user->uid), 
					'email'=>$user->email, 
					'group'=>$user->group,
					'actions'=>Core::l('info','user/details/'.$user->uid,$options).' | '.Core::l('edit','admin/users/edit/'.$user->uid,$options)
				);
			}
		}
		$perms = new Perms();
		$perms->GetNames();
		//create the actions options
		$actions = array('delete' => 'Delete');
		$actions['Add to Group:'] = $perms->names;
		$extra = 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>';
		$options['image'] = '24';
		$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/users/add',$options));
		// bind the params to smarty
		$smarty->assign('sublinks',$links);
		$smarty->assign('cb',true);
		$smarty->assign('self','admin/users');
		$smarty->assign('actions',$actions);
		$smarty->assign('extra', $extra);
		$smarty->assign('list', $theList);
		return $smarty;
	}
	public static function EditUser($id){
		global $smarty;
		$edituser = new User($id);
		$profile = new UserProfile($edituser->uid);
		$tablinks = array('User Account', 'User Profile');
		//create the form object 
		$form = new HTML_QuickForm('edituser','post','admin/users/commit/'.$edituser->uid);
		//set form default values
		$form->setdefaults(array(
			'name'=>$edituser->name, 
			'joined'=>date('G:i m.d.y T',$edituser->joined),
			'email'=>$edituser->email,
			'avatar'=>$profile->avatar,
			'location'=>$profile->location,
			'about'=>$profile->about
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
	public static function RegForm($redirect=null){
		global $smarty;
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
			$form->process(array('User','CreateUser'));
			Core::SetMessage('Your user account has been created!','info');
			BaseController::Redirect();
			exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
	}
	public static function Logout(){
			User::Logout();
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
	public static function Login($user, $pass){
		$user = User::Login($user, $pass);
		if ($user == false){
			Core::SetMessage('Username or Password is incorrect','error');
		}
	}
	public static function NewUser($user, $pw, $cp, $e, $ce){
		if (UserController::CheckUser($user) && UserController::CheckPass($pw, $cp) && UserController::CheckEmail($e, $ce)){
			$newUser = User::CreateUser($user, $pw, $e);
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
		print $user->uid .'|'. $user->name .'|'.$user->email.'|'.$user->joined.'|'.$user->roles;
	}
	public static function AddPerm($perm){
		foreach($_POST['users'] as $u){
			$user = new User($u);
			$user->group = $perm;
			$user->commit();
		}
	}
	public static function ShowProfile($id){
		global $smarty;
		$profile = new UserProfile($id);
		$smarty->assign('name',$profile->name);
		$smarty->assign('email',$profile->email);
		$smarty->assign('avatar',$profile->avatar);
		$smarty->assign('location',$profile->location);
		$smarty->assign('lastlogin',date('G:i m.d.y T',$profile->lastlogin));
		$smarty->assign('about',$profile->about);
		$smarty->assign('file','userprofile.tpl');
		$smarty->assign('online',$profile->online);
	}
	public static function CheckOnline($id){
		$accesstime = User::GetAccessTime($id);
		return $accesstime >= time() - 300 ? 'online' : 'offline';
	}
	
}
?>
