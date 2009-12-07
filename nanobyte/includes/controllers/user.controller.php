<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
class UserController extends BaseController{
	
	public static function add(){
		$Core = BaseController::getCore();
		$form = self::RegForm();
		$Core->smarty->assign('form',$form);

		$Core->json_obj->title = "Add new user";
		return $Core->smarty->fetch('form.tpl');
	}
	
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
			if($Core->args[1] == 'list'){
				$Core->smarty->assign(self::ListUsers($Core));
				$content = $Core->smarty->fetch('list.tpl'); 
			}elseif(method_exists('UserController',$Core->args[1])){
				$content = call_user_func(array('UserController',$Core->args[1]));
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
	
	public static function commit(){
		$Core = BaseController::getCore();
		if (isset($_POST['commit'])){
			self::EditUser($Core->args[1]);
			parent::Redirect();
		}elseif(isset($_POST['delete'])){
			AdminController::DeleteUserRequest();
			parent::Redirect();
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
	
	public static function details(){
		$Core = BaseController::getCore();
		return self::GetDetails($Core->args[1]);
	}
	
	public static function display(){
		$Core = parent::getCore();
		$content = "";
		if(isset($Core->args[0]) && !empty($Core->args[0])){
			if(method_exists('UserController', $Core->args[0])){
				$content = call_user_func(array('UserController',$Core->args[0]));
			}
		}else{
			if ($Core->user->uid == 0){ //User is not logged in
				$Core->smarty->assign('noSess', true);
			}else{
				if($Core->authUser('view user profiles')){
					$content =  self::showProfile($Core->user->uid);
				}else{
					$Core->setMessage("You do not have permission to view this profile!","error");
				}
			}
		}
		if(!$Core->ajax){
			parent::DisplayMessages(); // Get any messages
			parent::GetHTMLIncludes(); // Get CSS and Script Files
			$Core->smarty->assign('content',$content);
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
	
	/* Not sure why I had this.
//		$Core = BaseController::getCore();
//		if ($Core->authUser($Core->user, 'edit user accounts') || $Core->user->uid === $Core->args[1]){
//			self::editForm($Core->args[1]);
//			return $Core->smarty->fetch('form.tpl');
//		}else{
//			$Core->setMessage('You do not have access to this page!','error');
//		}
//		parent::displayMessages();
//		parent::getHTMLIncludes();
//		$Core->smarty->display('index.tpl');
//		return;
	 */
	
	public static function edit(){
		$Core = parent::getCore();
		$Core->smarty->assign('form',self::editForm($Core->args[2]));
		if(isset($_POST['submit'])){
			$Core->json_obj->callback = 'nanobyte.closeParentTab';
			$Core->json_obj->args = 'input[name=submit][value=Save Changes]';
		}
		$Core->json_obj->title = "Edit user";
		return $Core->smarty->fetch('form.tpl');
	}
	
	public static function editForm($id){
		$Core = parent::getCore();
		$edituser = new User($id);
//		$profile = new UserProfile($edituser->uid);
		//create the form object
		$element_array = array('name'=>'edituser','method'=>'post','action'=>'admin/user/edit/'.$edituser->uid);
		//set form default values
		$element_array['defaults']=array(
			'name'=>$edituser->name, 
			'joined'=>date('G:i m/d/Y T',$edituser->joined),
			'email'=>$edituser->email,
//			'avatar'=>$profile->avatar,
//			'location'=>$profile->location,
//			'about'=>$profile->about
		);
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'User Account Details'),
			array('type'=>'text', 'name'=>'name', 'label'=>'Username', 'options'=>array('size'=>25, 'maxlength'=>15, 'readonly'=>'readonly')),
			array('type'=>'text', 'name'=>'joined','label'=>'Joined', 'options'=>array('size'=>25, 'maxlength'=>15, 'readonly'=>'readonly')),
			array('type'=>'password', 'name'=>'password', 'label'=>'Password', 'options'=>array('size'=>25, 'maxlength'=>10)),
			array('type'=>'password', 'name'=>'confirm', 'label'=>'Confirm Password', 'options'=>array('size'=>25, 'maxlength'=>10)),
			array('type'=>'text', 'name'=>'email','label'=> 'Email', 'options'=>array('size'=>25, 'maxlength'=>50)),
			
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save Changes')
		);
		
		//add form rules
		$element_array['rules'] = array(
			array('required','email'),
			array('required','password'),
			array('required','confirm'),
			array('match',array('password','confirm'))
		);
		
		//apply form prefilters
		$element_array['filters'] = array(
			array("__ALL__","trim"),
			array("__ALL__","strip_tags")
		);
		
		$element_array['callback'] = array($edituser,'commit');
		
		//If the form has already been submitted - validate the data
//		if(isset($_POST['submit']) && $form->validate()){
//			$values = $form->exportValues();
//			if($values['password']){
//				$edituser->pwchanged = $values['password'];
//			} 
//			if($edituser->email != $values['email']){
//				$edituser->email = $values['email'];
//			}
//			if ($edituser->commit() === true){
//				$Core->SetMessage('Your Information has been updated!','info');
//			}else{
//				$Core->SetMessage('Your Information was not updated!','error');
//			}
//
////			parent::Redirect();
//			return;
//		}
		//send the form to smarty
		return parent::generateForm($element_array);
	}
	
	public static function email(){
		$Core = parent::getCore();
		$email = new Email();
		$Core->smarty->assign('form',parent::emailForm('admin/user/email',$email->getEmailData('register')));
		if(isset($_POST['submit'])){
			$Core->setMessage("Your settings were saved successfully!","info");
			return;
		}
		return $Core->smarty->fetch('form.tpl');
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
				'name'=>Core::l($u['username'], 'user/profiles/'.$u['uid']), 
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
	
	public static function login(){
		$Core = parent::getCore();
		$Core->user->Login($_POST['user'], $_POST['pass']);
		if($Core->user->success===true){
			$Core->setMessage('Authentication Successful!','info');
			//$Core->json_obj->callback = 'reload';
			parent::Redirect();
		}else{
			$Core->json_obj->callback = 'reset';
			$Core->setMessage('Username or Password is incorrect','error');
		}
		return;
	}
	
	public static function logout(){
		$Core = parent::getCore();
		$Core->user->Logout();
		if (!isset($_SESSION['hash'])){
			$Core->SetMessage('You are now logged out', 'status');
		}else{
			$Core->SetMessage('You are still logged in! Please try again.', 'error');
		}
		parent::Redirect(HOME);
		return;
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
	
	public static function profiles(){
		$Core = BaseController::getCore();
		if ($Core->user->uid == 0){ //User is not logged in
			$Core->smarty->assign('noSess', true);
		}
		if($Core->authUser('view user profiles') && is_numeric($Core->args[1])){
			return self::showProfile($Core->args[1]);
		}
		return;
	}
	
	public static function regForm($form_action='user/register',$redirect=null){
		$Core = parent::getCore();
		//create the form object 
		$element_array = array('name'=>'newuser','method'=>'post','action'=>$form_action);
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'User Account Details'),
			array('type'=>'text', 'name'=>'name', 'label'=>'Username', 'options'=>array('size'=>25, 'maxlength'=>15)),
			array('type'=>'password', 'name'=>'password', 'label'=>'Password', 'options'=>array('size'=>25, 'maxlength'=>10)),
			array('type'=>'password', 'name'=>'confirm', 'label'=>'Confirm Password', 'options'=>array('size'=>25, 'maxlength'=>10)),
			array('type'=>'text', 'name'=>'email','label'=> 'Email', 'options'=>array('size'=>25, 'maxlength'=>50)),
			array('type'=>'text', 'name'=>'cemail','label'=> 'Confirm Email', 'options'=>array('size'=>25, 'maxlength'=>50)),
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save Changes')
		);
		
		//add form rules
		$element_array['rules'] = array(
			array('required','name'),
			array('required','email'),
			array('required','cemail'),
			array('required','password'),
			array('required','confirm'),
			array('match',array('password','confirm')),
			array('match',array('email','cemail'))
		);
		
		//apply form prefilters
		$element_array['filters'] = array(
			array("__ALL__","trim"),
			array("__ALL__","strip_tags")
		);

		//If the form has already been submitted - validate the data
		
		$element_array['callback'] = array(new User(),'create');
//		if(array_key_exists('submit',$_POST) && $form->validate()){
//			$newUser = new User();
//			if($newUser->Create($form->exportValues())){
//				return true;
//			}
//			return false;
////			parent::Redirect();
//		}
		//send the form to smarty
		return parent::generateForm($element_array); 
	}
	
	public static function register(){
		$Core = BaseController::getCore();
		$form = self::RegForm();
		if(isset($_POST['submit']) && is_bool($form)){
			$Core->json_obj->callback = 'nanobyte.closeParentTab';
			$Core->json_obj->args = 'input[name=submit][value=Save Changes]';
		}else{
			$Core->smarty->assign('form',$form);
			$content = $Core->smarty->fetch('form.tpl');
		}
	}
	
	public static function reset_pw(){
		$Core = parent::getCore();
		$Core->smarty->assign('form',self::ResetPassword($Core->user));
		$Core->json_obj->callback = 'Dialog';
		$Core->json_obj->title = 'Reset Password';
		return $Core->smarty->fetch('form.tpl');
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

	public static function select(){
		$Core = parent::getCore();
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
