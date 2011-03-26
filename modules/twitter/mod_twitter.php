<?php
    class Mod_Twitter{
    	
		public function __construct(){
			$this->getXML('');
		}
		
		public function install(){
			Module::regBlock(array('name'=>'TwitterStatus', 'module'=>'Twitter', 'options'=>''));
		}
		
		public function uninstall(){
			
		}
		
		public function getXML($url){
			if(empty($url)){
				$url = "http://twitter.com/users/mconway03.xml";
			}
			$xml_obj = simplexml_load_string(file_get_contents($url));
			foreach($xml_obj as $var=>$val){
				if(strpos($var,'-')==false){
					continue;
				}
				$new_var = str_replace('-','_',$var);
				$xml_obj->{$new_var} = $val;
			}
			return $xml_obj;
		}
		
		public static function TwitterStatus_Block(){
			$block = new Block_TwitterStatus;
			return $block;
		}
		
    }
	
	class Block_TwitterStatus extends Mod_Twitter{
		function __construct(){
			global $Core;
			$twit_user = $this->getXML('http://api.twitter.com/1/statuses/user_timeline.xml?screen_name=zerobytesolns');
			$twit_limit = $this->getXML("http://twitter.com/account/rate_limit_status.xml");
			$smarty_vars = array('twitter'=>$twit_user,'twit_limit'=>$twit_limit);
			$Core->smarty->assign($smarty_vars);
			$this->template = '../../modules/twitter/templates/twitterstatus.tpl';
			//$this->title = $twit_user->status[0]->user->name."'s Status";
			$this->title = "Twitter";
		}
	}
?>