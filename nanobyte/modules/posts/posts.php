<?php
class Mod_Posts{
	
	public static function Install(){
		//Module::NewPerm();
		Module::RegBlock(array('name'=>'PostsByDate', 'module'=>'Posts', 'options'=>''));
	}
	
	public static function PostsByDate_Block(){
		return new Block_PostsByDate();
	}
	
}

class Block_PostsByDate extends Mod_Posts{
	function __construct(){
		global $smarty;
		BaseController::AddJs('includes/js/ui/ui.datepicker.js');
		BaseController::AddJs('modules/posts/js/posts.js');
		BaseController::AddCss('modules/posts/css/datepicker.css');
		$this->template = '../../modules/posts/templates/postsbydate.tpl';
	}
}
?>
