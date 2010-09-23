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
	
/**
 * Created Aug 6th
 * XML structure based on that of Mike Branski's Modularity Documents
 * conf params: Author (email and URL) | Title | Version | Status | Site | Description
 * 
 * 	call_user_func(array('Foo', 'bar') , $params );
 *		Database: Module | Status 
*/

class Module{
	
	private $dbh;
	public $modpath;
	
	function __construct($path=null){
//		$Core = BaseController::getCore();
		$this->dbh = DBCreator::GetDbObject();
		if(isset($path)){
			//set up some generic queries
			$this->select = "SELECT * FROM ".DB_PREFIX."_modules WHERE module=:mod";
			$this->insert = "INSERT INTO ".DB_PREFIX."_modules (name, module, status) values (:name, :mod, :status)";
			$this->modify = "UPDATE ".DB_PREFIX."_modules set status=status XOR 1 WHERE module=:mod";
			$this->name = str_replace('modules/', '', $path);
			$this->modpath = './modules/'.$this->name.'/';
			if (file_exists($this->modpath.$this->name.'.xml')){
				$this->conf = simplexml_load_file($this->modpath.$this->name.'.xml');
				if ($this->GetStatus()){
					return true;
				}else{
					$this->Add();
					$this->status = 0;
				}
				//if (!empty($this->conf->author->attributes())){
					//do something with the attributes
				//}
			}else{
	//			$Core->SetMessage('Configuration file '.$this->modpath.$this->name.'.xml is unreadable or does not exist!', 'error');
			}
		}

		
	}
	
	public function add(){
		$Core = BaseController::getCore();
		$qSelect = $this->dbh->prepare($this->select);
		$qSelect->bindParam(':mod', $this->modpath);
		$qSelect->execute();
		if($qSelect->rowCount() == 0){
			try{
				$qInsert = $this->dbh->prepare($this->insert);
				$qInsert->bindParam(':name', $this->name);
				$qInsert->bindParam(':mod', $this->modpath);
				$qInsert->bindValue(':status', 0);
				$qInsert->execute();
			}catch(PDOException $e){
				$Core->SetMessage('Could not add Module '.$this->conf->title.' to the database. Error: '.$e->getMessage(), 'error');
			}
		}else{
			return false;
		}
	}
	
	public function commit(){
		//set status to 1/0
		$qUpdate = $this->dbh->prepare($this->modify);
		$qUpdate->bindValue(':mod',$this->modpath);
		$qUpdate->execute();
		if($qUpdate->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public function createFolders($folder_array){
		foreach($folder_array as $folder){
			mkdir($_SERVER['DOCUMENT_ROOT'].PATH.UPLOAD_PATH.$folder);
		}
	}
	
	public function disableBlocks(){
		$query = $this->dbh->prepare("SELECT id FROM ".DB_PREFIX."_blocks WHERE providedby=:parent AND status=:status");
		try{
			$query->execute(array(':parent'=>ucfirst($this->name),'status'=>'1'));
			while($row = $query->fetch(PDO::FETCH_OBJ)){
				$this->updateBlockStatus($row->id);
			}
		}catch(PDOException $e){
			$Core->SetMessage('Could not disable Blocks. Error: '.$e->getMessage(), 'error');
		}

	}
	
	public function getStatus(){
		$qSelect = $this->dbh->prepare("SELECT status FROM ".DB_PREFIX."_modules WHERE module=:mod");
		$qSelect->bindParam(':mod',$this->modpath);
		$qSelect->execute();
		if($qSelect->rowCount() == 1){
			$row = $qSelect->fetch();
			$this->status = $row[0];
			return true;
		}else{
			return false;
		}
	}
	
	public static function getEnabled($type){
		$dbh = DBCreator::GetDbObject();
		$sort = $type =='block' ? "ORDER BY position DESC" : "";
		$qSelect = $dbh->prepare("SELECT name FROM ".DB_PREFIX."_{$type}s WHERE status=1 {$sort}");
		$qSelect->execute();
		return $qSelect->fetchAll(PDO::FETCH_ASSOC);
	} 
	
	public static function getBlocks($enabled=null){
		$where = $enabled ? "WHERE status=1" : "";
		$dbh = DBCreator::GetDbObject();
		$qSelect = $dbh->prepare("SELECT * FROM ".DB_PREFIX."_blocks $where ORDER BY position, weight ASC");
		$qSelect->execute();
		return $qSelect->fetchAll(PDO::FETCH_ASSOC);
	}

	public function moveBlock(&$args){
		$weight = ($args[1] == 'up' || $args[1]=='down') ? "weight={$args[3]}" : '';
		$position = $args[1] == 'position' ? "position={$args[3]}" : '';
		
		$query = "UPDATE ".DB_PREFIX."_blocks SET {$weight}{$position} WHERE id={$args[2]}";
		$run = $this->dbh->prepare($query);
		$run->execute();
		if($run->rowCount()==1){
			return true;
		}else{
			return false;
		}
	}

	public static function regBlock($params){
		$Core = BaseController::getCore();
		$dbh = DBCreator::GetDbObject();
		$qselect = $dbh->prepare("SELECT name FROM ".DB_PREFIX."_blocks WHERE name=:name");
		$qselect->bindParam(':name',$params['name']);
		$qselect->execute();
		if($qselect->rowCount() == 0){
			try{
				$qInsert = $dbh->prepare("INSERT INTO ".DB_PREFIX."_blocks (name, providedby, position, status, options) values (:name, :mod, :pos, :stat, :opt)");
				$qInsert->bindParam(':name', $params['name']);
				$qInsert->bindParam(':mod', $params['module']);
				$qInsert->bindValue(':pos', 0);
				$qInsert->bindValue(':stat', 0);
				$qInsert->bindValue(':opt', $params['options']);
				$qInsert->execute();
			}catch(PDOException $e){
				$Core->SetMessage('Could not add Block '.$params['name'].' to the database. Error: '.$e->getMessage(), 'error');
			}
		}
	}

	public function updateBlockStatus($id){
		$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_blocks set status=status XOR 1 WHERE id=:id");
		$query->execute(array(':id'=>$id));
		if($query->rowCount()==1){
			return true;
		}else{
			return false;
		}
	}
	
}


