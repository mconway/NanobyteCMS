<?php
class UsersOnlineBlock{
	function __construct(){
		global $smarty;
		BaseController::AddCss('blocks/usersonline.css');
		$this->users = User::GetUsersOnline();
		$this->template = '../blocks/usersonline.tpl';
		$smarty->assign('usersonline',$this->users);
		$count = count($this->users);
		$userstr = $count != 1 ? "$count users currently online:" : "$count user currently online:";
		$smarty->assign('userstr',$userstr);
	}
}
    
?>
