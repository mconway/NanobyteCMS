<?php
//print_r(get_loaded_extensions());

    class Install{
    	
		public function __construct(){
			$this->requirements = array(
				array(
					'name'=>'HTML_Quickform',
					'type'=>'pear',
					'path'=>'HTML/QuickForm.php',
					'provider'=>'PEAR',
					'perms'=>'N/A'
				),
				array(
					'name'=>'Write Permissions for Config file',
					'type'=>'fileperm',
					'path'=>'includes/config.inc.php',
					'provider'=>'',
					'perms'=>'666'
				)
			);
			$this->continue = true;
		}
		
		public function CheckRequirements(){
			$this->pear = trim(shell_exec("pear list")); //need to regex this
			$false  ="<center><img src='".THEME_PATH."/images/0-25.png'/></center>";
			$true = "<center><img src='".THEME_PATH."/images/1-25.png'/></center>";
			$warn = "<center><img src='".THEME_PATH."/images/2-25.png'/></center>";
			foreach($this->requirements as &$req){
				switch($req['type']){
					case 'file':
						if(file_status($req['path'])){
							$req['status'] = $true;
						}else{
							$req['status'] = $false;
							$this->continue = false;
						}
						break;	
					case 'fileperm':
						clearstatcache();
						if($req['perms'] >= substr(fileperms($req['path']),-3)){
							$req['status'] = $true;
						}else{
							$req['status'] = $false;
							$this->continue = false;
						}
						break;
					case 'pear':
						if(stripos($this->pear,$req['name'])){
							if(@include($req['path'])){
								$req['status'] = $true;
							}else{
								$req['status'] = $warn;
								$this->continue = false;
							}
							
						}else{
							$req['status'] = $false;
							$this->continue = false;
						}
						break;
				}
			}
		}
		
		public function installDB(){
			$this->dbh = DBCreator::GetDbObject();
			$patterns = array(
				'/\*\*\*DBPREFIX\*\*\*/'
			);
			$replace = array(
				DB_PREFIX
			);
			$tmp = file_get_contents('includes/sql/nanobyte.mysql');
			$sql = preg_replace($patterns,$replace,$tmp);
			$create_query = $this->dbh->query($sql);
			if($create_query->errorCode()=='00000'){
				return true;
			}
			return false;
		}
		
    }
?>