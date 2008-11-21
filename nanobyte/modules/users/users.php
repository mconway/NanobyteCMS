<?php
class Mod_Users{
	
	public static function Install(){
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
}
class Block_UsersOnline extends Mod_Users{
	function __construct(){
		global $smarty;
		BaseController::AddCss('modules/users/usersonline.css');
		$this->users = self::GetUsersOnline();
		$this->template = '../../modules/users/templates/usersonline.tpl';
		$smarty->assign('usersonline',$this->users);
		$count = count($this->users);
		$userstr = $count != 1 ? "$count users currently online:" : "$count user currently online:";
		$smarty->assign('userstr',$userstr);
	}
}

class Block_NewUsers extends Mod_Users{
	function __construct(){
		global $smarty;
		$this->users = self::NewUsers();
		$this->template = '../../modules/users/templates/newusers.tpl';
		$smarty->assign('newusers',$this->users);
	}
}
    
?>
