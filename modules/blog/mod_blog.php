<?php
    class Mod_Blog{
    	
		public static function install(){
			//register block
			Module::RegBlock(array('name'=>'PostsByDate', 'module'=>'Blog', 'options'=>''));
			//add content type
			$content = new Mod_Content();
			$content->RegisterContentType('Blog Post');
		}
		
		public static function uninstall(){
			$content = new Mod_Content();
			$content->UnregisterContentType('Blog Post');
		}
		
		public static function postsByDate_Block(){
			return new Block_PostsByDate();
		}
		
		public function display(){
			BlogController::display();
		}
		
		public function admin(){
			
		}
	
	}
	
	class BlogController extends BaseController{
		public static function display(){
			$Core = parent::getCore();
			//If we are not passing arguments, display default content
			if(empty($Core->args)){
				$Core->smarty->assign('posts',ContentController::displayContent(1));
			//Display a specific content post
			}elseif(!isset($Core->args[1]) || empty($Core->args[1])){
				self::View($Core->args[0]);
				$content = $Core->smarty->fetch('post.tpl');
			//Display comment
			}else{
				$comments = new Mod_Comments();
				$comments->commit(array('pid'=>$Core->args[0],'title'=>$_POST['title'], 'body'=>$_POST['body']));
			}
			//Return full page if ajax was not requested
			if(!$Core->ajax){
				parent::DisplayMessages();
				parent::GetHTMLIncludes(); // Get style and JS
				$Core->smarty->display('index.tpl'); //Display the page
			//Return json for ajax requests
			}else{
				$Core->json_obj->content = $content;
				$Core->json_obj->messages = parent::DisplayMessages();
				print json_encode($Core->json_obj);
			}
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