<?php
    class Mod_UserProfile{
    	private $dbh;
		
		public function __construct($id){
			$this->dbh = DBCreator::GetDbObject();
			$query = $this->dbh->prepare("select user.username, user.email, user.lastlogin, profile.avatar, profile.location, profile.about, profile.facebook, profile.twitter from ".DB_PREFIX."_user AS user LEFT JOIN ".DB_PREFIX."_user_profiles AS profile ON user.uid = profile.uid where profile.uid=:id"); 
			$query->bindParam(':id', $id);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$this->uid = $id;
			$this->name = $row['username'];
			$this->email = $row['email'];
			$this->avatar = $row['avatar'];
			$this->location = $row['location'];
			$this->about = $row['about'];
			$this->lastlogin = $row['lastlogin'];
			$this->facebook = $row['facebook'];
			$this->twitter = $row['twitter'];
			$this->online = $this->checkOnline();
		}
		
		public function checkOnline(){
			$Core = BaseController::getCore();
			$Core->user->getAccessTime($this->uid);
			return $Core->user->access_time >= time() - 300 ? 'online' : 'offline';
		}
		
		public function commit(){
			$Core = BaseController::getCore();
			//better to make empty profile on user creation
			$sql = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user_profiles SET avatar=:av, location=:loc, about=:about, facebook=:fb, twitter=:twit WHERE uid=:uid");
			$sql->bindParam(':av', $this->avatar);
			$sql->bindParam(':loc', $this->location);
			$sql->bindParam(':about', $this->about);
			$sql->bindParam(':fb', $this->facebook);
			$sql->bindParam(':twit', $this->twitter);
			$sql->bindParam(':uid', $this->uid);
			try{
				$sql->execute();
				if ($sql->rowCount() == 1){
//					$Core->SetMessage('Profile Updated!', 'info');
					return true;
				}else{
					return false;
				}
			}catch(PDOException $e){
				$Core->SetMessage('Error updating user profile: ' . $e->getMessage(), 'error');
			}
		}
	
		public function display(){
			$Core = BaseController::getCore();
			$tabs = array();
			$content = '';
			if(is_numeric($Core->args[0])){
				array_push($tabs,$Core->l('About Me','user/'.$Core->args[0].'/about/ajax'));
				$Core->smarty->assign($this->showProfile());
				if($Core->args[0] == $Core->user->uid || $Core->authUser('edit user profiles')){
					array_push($tabs,$Core->l('Edit Profile','user/'.$Core->args[0].'/edit/ajax'));
				}
				if(isset($Core->args[1])){
					switch($Core->args[1]){
						case 'about':
							$vars = $this->showProfile();
							$content = "<h3>About Me:</h3><br />".$vars['about'];
							break;							
						case 'edit':
							$form_vals = $this->edit();
							if(isset($Core->args[2]) && $Core->args[2] == 'image'){
								$Core->json_obj->args = BaseController::handleImage($_FILES[key($_FILES)],'80');
								$Core->json_obj->callback = 'changeAvatar';
								$this->avatar = $Core->json_obj->args['thumb'];
								$this->commit();
								break;
							}
							if(!isset($_POST['submit'])){
								$Core->smarty->assign('form',$form_vals);
								$content = $Core->smarty->fetch('form.tpl');
							}
					}
				}

			}else{
				$Core->setMessage('You have specified an invalid user!', 'error');
			}
			$Core->smarty->assign('tabs',$tabs);
			return $content;
		}
	
		public function edit(){
			$form = new HTML_QuickForm('editprofile','post','user/'.$this->uid.'/edit');
			$form->setDefaults(array(
				'avatar'=>$this->avatar,
				'location'=>$this->location,
				'facebook'=>$this->facebook,
				'twitter'=>$this->twitter,
				'about'=>$this->about
			));
			 	
			$form->addElement('header','','User Profile Information');
			$form->addElement('file', 'avatar', 'Upload Avatar', array('id'=>'image'));
			$form->addElement('text', 'location', 'Location',array('size'=>25, 'maxlength'=>15));
			$form->addElement('text', 'facebook', 'Facebook ID',array('size'=>25, 'maxlength'=>20));
			$form->addElement('text', 'twitter', 'Twitter Username',array('size'=>25, 'maxlength'=>20));
			$form->addElement('textarea', 'about', 'About Me',array('rows'=>10,'cols'=>40,'id'=>'ckeditor'));
			$form->addElement('submit', 'submit', 'Save Changes');

			if(isset($_POST['submit']) && $form->validate()){
				$values = $form->exportValues();
				$this->location = $values['location'];
				$this->about = strip_tags($values['about'],ALLOWED_HTML_TAGS);
				$this->facebook = $values['facebook'];
				$this->twitter = $values['twitter'];
				$this->commit();
				
				return;
			}
			//send the form to smarty
			return $form->toArray();
		}
		
		public function install(){
			//this is the 'magic' query	"INSERT INTO ".DB_PREFIX."_user_profiles (uid) SELECT uid FROM ".DB_PREFIX."_user WHERE `username`=:name;";
		}
		
		public function showProfile(){
			$array =  array(
				'name'=>$this->name,
//				'email'=>$this->email,
				'avatar'=>$this->avatar,
				'location'=>$this->location,
				'lastlogin'=>date('G:i m.d.y T',$this->lastlogin),
				'about'=>$this->about,
				'file'=>'userprofile.tpl',
				'online'=>$this->online
			);
			
			if(!empty($this->facebook)){
				$array['facebook'] = "http://www.facebook.com/profile.php?id=".$this->facebook;
			}
			if(!empty($this->twitter)){
				$array['twitter'] = "http://www.twitter.com/".$this->twitter;
			}
			
			return $array;
		}
	
		public function uninstall(){
			
		}
    }
?>