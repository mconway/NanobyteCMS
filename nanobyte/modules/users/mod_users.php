<?php
	class Mod_Users{
		
		public function Install(){
			//Module::NewPerm();
			Module::RegBlock(array('name'=>'UsersOnline', 'module'=>'Users', 'options'=>''));
		}
		
		public static function UsersOnline_Block(){
			$block = new Block_UsersOnline;
			return $block;
		}
		
		public static function NewUsers(){
	 		$dbh = DBCreator::GetDbObject();
	 		$sql = $dbh->prepare('select uid, username, joined from '.DB_PREFIX.'_user order by `joined` desc limit 5');
	 		$sql->execute();
	 		while ($row = $sql->fetch(PDO::FETCH_ASSOC)){
	 			$users[] = $row;
	 		}
	 		return $users;
	 	}
		
		public static function GetUsersOnline(){
			$dbh = DBCreator::GetDbObject();
			$query = $dbh->prepare("SELECT username FROM ".DB_PREFIX."_user WHERE online > :time");
			$query->bindValue(':time',time()-300);
			$query->execute();
			return $query->fetchAll(PDO::FETCH_COLUMN,0);
		}
		
		public function Uninstall(){
			
		}
	}
	class Block_UsersOnline extends Mod_Users{
		function __construct(){
			$Core = BaseController::getCore();
			BaseController::AddCss('modules/users/usersonline.css');
			$this->users = self::GetUsersOnline();
			$this->template = '../../modules/users/templates/usersonline.tpl';
			$Core->smarty->assign('usersonline',$this->users);
			$count = count($this->users);
			$userstr = $count != 1 ? "$count users currently online:" : "$count user currently online:";
			$Core->smarty->assign('userstr',$userstr);
			$this->title = 'Users Online';
		}
	}
	
	class Block_NewUsers extends Mod_Users{
		function __construct(){
			$this->users = self::NewUsers();
			$this->template = '../../modules/users/templates/newusers.tpl';
			$Core->smarty->assign('newusers',$this->users);
			$this->title = 'Users Online';
		}
	}
    
?>
