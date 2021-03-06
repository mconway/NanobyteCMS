<?php
    class Mod_UserProfile{
    	private $dbh;
		
		public function __construct($id=null){
			$this->dbh = DBCreator::GetDbObject();
			if(isset($id)){
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
		}
		
		public function checkOnline(){
			$Core = BaseController::getCore();
			$Core->user->getAccessTime($this->uid);
			return $Core->user->access_time >= time() - 300 ? 'online' : 'offline';
		}
		
		public function commit(){
			$Core = BaseController::getCore();
			//better to make empty profile on user creation
			$query = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_user_profiles WHERE uid=:uid");
			$query->execute(array(':uid'=>$this->uid));
			if($query->rowCount()==1){
				$sql = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user_profiles SET avatar=:av, location=:loc, about=:about, facebook=:fb, twitter=:twit WHERE uid=:uid");
			}else{
				$sql = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user_profiles (avatar,location,about,facebook,twitter,uid) VALUES (:av, :loc, :about, :fb, :twit, :uid)");
			}
			$sql->bindParam(':av', $this->avatar);
			$sql->bindParam(':loc', $this->location);
			$sql->bindParam(':about', $this->about);
			$sql->bindParam(':fb', $this->facebook);
			$sql->bindParam(':twit', $this->twitter);
			$sql->bindParam(':uid', $this->uid);
			try{
				$sql->execute();
				if ($sql->rowCount() == 1){
					$Core->SetMessage('Profile Updated!', 'info');
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
			array_push($tabs,$Core->l('About Me','user/profiles/'.$this->uid.'/about/ajax'));
			$Core->smarty->assign($this->showProfile());
			if($this->uid == $Core->user->uid || $Core->authUser('edit user profiles')){
				array_push($tabs,$Core->l('Edit Profile','user/profiles/'.$this->uid.'/edit/ajax'));
			}
			if(isset($Core->args[2])){
				switch($Core->args[2]){
					case 'about':
						$vars = $this->showProfile();
						$content = $vars['about'];
						break;							
					case 'edit':
						$form_vals = $this->edit();
						if(isset($Core->args[3]) && $Core->args[3] == 'image'){
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
			$Core->smarty->assign('tabs',$tabs);
			return $content;
		}
	
		public function edit(){
			$element_array = array('name'=>'editprofile','method'=>'post','action'=>'user/profiles/'.$this->uid.'/edit');
			$element_array['defaults'] = array(
				'avatar'=>$this->avatar,
				'location'=>$this->location,
				'facebook'=>$this->facebook,
				'twitter'=>$this->twitter,
				'about'=>$this->about
			);
			$element_array['elements'] = array(
				array('type'=>'header','name'=>'','label'=>'User Profile Information'),
				array('type'=>'file','name'=>'avatar','label'=>'Upload Avatar','options'=>array('id'=>'image')),
				array('type'=>'text','name'=>'location','label'=>'Location','options'=>array('size'=>25, 'maxlength'=>15)),
				array('type'=>'text','name'=>'facebook','label'=>'Facebook ID','options'=>array('size'=>25, 'maxlength'=>20)),
				array('type'=>'text','name'=>'twitter','label'=>'Twitter Username','options'=>array('size'=>25, 'maxlength'=>20)),
				array('type'=>'textarea','name'=>'about','label'=>'About Me','options'=>array('rows'=>10,'cols'=>40,'id'=>'ckeditor')),
				array('type'=>'submit','name'=>'submit','value'=>'Save Changes')
			);
			$element_array['callback'] = array($this,'saveData');

			//send the form to smarty
			return BaseController::generateForm($element_array);
		}
		
		public function install(){
			//this is the 'magic' query	"INSERT INTO ".DB_PREFIX."_user_profiles (uid) SELECT uid FROM ".DB_PREFIX."_user WHERE `username`=:name;";
		}
		
		public function saveData($values){
			$this->location = $values['location'];
			$this->about = strip_tags($values['about'],ALLOWED_HTML_TAGS);
			$this->facebook = $values['facebook'];
			$this->twitter = $values['twitter'];
			$this->commit();
			
			return;
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