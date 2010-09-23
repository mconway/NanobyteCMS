<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
//print_r(get_loaded_extensions());

    class Install{
    	
		public function __construct(){
			$this->requirements = array(
//				array(
//					'name'=>'HTML_Quickform',
//					'type'=>'pear',
//					'path'=>'HTML/QuickForm.php',
//					'provider'=>'PEAR',
//					'perms'=>'N/A'
//				),
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