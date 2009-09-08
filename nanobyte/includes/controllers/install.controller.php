<?php
	class InstallController extends BaseController{
		
		const SCRIPT = 'var i = 25;
				$(document).ready(function(){
					$("#main").prepend(\'<br/><div id="progressbar"><div id="pb-text"><span>0</span><span>/4</span></div></div><br/>\');
					$("#progressbar").progressbar();
					$("form input[type=submit]").livequery(function(){
						$(this).click(increaseProgressBar)
					})
					$("#pb-text").css("position","absolute").css("left",$("#progressbar").offset().left+390+"px");
					$("#createdb").live("click",function(){
						nanobyte.submitForm($(this).parent("form"),$(this));
						increaseProgressBar();
						
					})
				});
				function increaseProgressBar(){
					$("#progressbar").progressbar("option", "value", i);
					i += 25;
					$("#pb-text").children("span:first-child").text((i/25)-1);	
				}
				';
		
		public static function Display(){
			$Core = parent::getCore();
			
			$Core->smarty->assign('extraScript',self::SCRIPT);
			
			$Install = new Install();
			if(isset($Core->args[0])){
				$content = call_user_func(array('self',$Core->args[0]),new Install());
			}else{
				$content = self::license();
			}
			$Core->smarty->assign('content',$content);
//			$jsonObj->content = $smarty->fetch('index.tpl');
			if(!$Core->ajax){
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$Core->smarty->display('index.tpl'); // Display the Page
			}else{
				$Core->json_obj->content = $content;
				$Core->json_obj->messages = parent::displayMessages();
				print json_encode($Core->json_obj);
			}
		}
		
		public static function license(){
			$Core = parent::getCore();
			$Core->smarty->assign('self','Beginning Nanobyte Installation');
			$Core->SetMessage('Beginning Installation','info');
			$content = <<<EOF
			The Nanobyte CMS has not been installed on this domain yet. You have been redirected to this page to begin the installation!
			Please begin by accepting the license agreement.<br/><br/><form name='license' method='post' action='install/step1'><textarea cols="80" rows="25" readonly>
EOF;
			$fh = fopen('license', 'r');
			$content.= fread($fh, filesize('license'));
			fclose($fh);
			$content .= "</textarea><br/>
			<label>I Agree</label> <input type='radio' name='agree' value='1' />  
			<label>I Disagree</label> <input type='radio' name='agree' value='0' /> <br/>
			<input type='submit' name='continue' value='Continue'/>";
			return $content;
		}
		
		public static function step1(&$Install){
			$Core = parent::getCore();
			if(array_key_exists('submit',$_POST)){
				if(array_key_exists('agree',$_POST) && $_POST['agree']==0){
					if($Core->ajax){
						$Core->json_obj->callback = 'nanobyte.redirect';
						$Core->json_obj->args = 'install/cancel';
						break;
					}else{
						BaseController::Redirect('install/cancel');
						break;
					}
				}
			}
			$Core->smarty->assign('formAction',"install/step2");

			$Install->CheckRequirements();
			if($Install->continue === true){
				$Core->smarty->assign('extra','<input type="submit" name="next" value="Next" />');
			}
			$Core->smarty->assign('list',$Install->requirements);
			$content = $Core->smarty->fetch('list.tpl');
			return $content;
		}
	
		public static function step2(&$Install){
			$Core = parent::getCore();
			$Core->smarty->assign('self',"Step 2 - Database Settings");
			
			AdminController::ShowConfig('install/step3');

			$content = $Core->smarty->fetch('form.tpl');
			return $content;
		}
	
		public static function step3(&$Install){
			$Core = parent::getCore();
			if(AdminController::ShowConfig('')!==true){ //save the posted data
				$Core->setMessage("An error occurred saving your settings, please try again.","error");
				return;
			}
			$content = "We will now create the database and required tables. Click the create database button to continue.<br /><form action='install/createDatabase'><input type='button' id='createdb' value='Create Database'/></form>";
			return $content;
		}
	
		public static function step4(){
			$Core = parent::getCore();
			$Core->smarty->assign('form',UserController::regForm('install/step4'));
			return $Core->smarty->fetch('form.tpl');
		}
	
		public static function createDatabase(&$Install){
			$Core = parent::getCore();
			if($Install->installDB()){
				$Core->setMessage("Database Installed!","info");
				return self::step4();
			}
		}
	}
?>