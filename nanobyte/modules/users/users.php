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
}
class Block_UsersOnline{
	function __construct(){
		global $smarty;
		BaseController::AddCss('modules/users/usersonline.css');
		$this->users = User::GetUsersOnline();
		$this->template = '../../modules/users/templates/usersonline.tpl';
		$smarty->assign('usersonline',$this->users);
		$count = count($this->users);
		$userstr = $count != 1 ? "$count users currently online:" : "$count user currently online:";
		$smarty->assign('userstr',$userstr);
	}
}
    
?>
