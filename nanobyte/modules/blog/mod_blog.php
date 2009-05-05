<?php
    class Mod_Blog{
    	
		public static function Install(){
			//register block
			Module::RegBlock(array('name'=>'PostsByDate', 'module'=>'Blog', 'options'=>''));
			//add content type
			$content = new Mod_Content();
			$content->RegisterContentType('Blog Post');
		}
		
		public static function PostsByDate_Block(){
			return new Block_PostsByDate();
		}
		
		public function Display(){
			
		}
		
		public function Admin(){
			
		}
	
	}
	
	class Block_PostsByDate extends Mod_Blog{
		function __construct(){
			global $smarty;
			//BaseController::AddJs('includes/js/ui/ui.datepicker.js');
			BaseController::AddJs('modules/posts/js/posts.js');
			//BaseController::AddCss('modules/posts/css/datepicker.css');
			$this->template = '../../modules/posts/templates/postsbydate.tpl';
		}
	}
	
?>