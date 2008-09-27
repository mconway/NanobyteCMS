<?php
/*
 * Created Aug 6th
 * XML structure based on that of Mike Branski's Modularity Documents
 * conf params: Author (email and URL) | Title | Version | Status | Site | Description
 * 
 * 	call_user_func(array('Foo', 'bar') , $params );
		
 *		Database: Module | Status 
*/

class Module{
	private $DB;
	public $modpath;
	function __construct($path){
		$this->DB = DBCreator::GetDbObject();
		$this->select = "SELECT * FROM ".DB_PREFIX."_modules WHERE `module`=:mod";
		$this->insert = "INSERT INTO ".DB_PREFIX."_modules (`name`, `module`, `status`) values (:name, :mod, :status)";
		$this->modify = "UPDATE ".DB_PREFIX."_modules set `status`=status XOR 1 WHERE `module`=:mod";
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
			Core::SetMessage('Configuration file '.$this->modpath.$this->name.'.xml is unreadable or does not exist!', 'error');
		}
	}
	
	public function Commit(){
		//set status to 1/0
		$qUpdate = $this->DB->prepare($this->modify);
		$qUpdate->bindValue(':mod',$this->modpath);
		$qUpdate->execute();
	}
	
	public function Add(){
		$qSelect = $this->DB->prepare($this->select);
		$qSelect->bindParam(':mod', $this->modpath);
		$qSelect->execute();
		if($qSelect->rowCount() == 0){
			try{
				$qInsert = $this->DB->prepare($this->insert);
				$qInsert->bindParam(':name', $this->name);
				$qInsert->bindParam(':mod', $this->modpath);
				$qInsert->bindValue(':status', 0);
				$qInsert->execute();
			}catch(PDOException $e){
				Core::SetMessage('Could not add Module '.$this->conf->title.' to the database. Error: '.$e->getMessage(), 'error');
			}
		}else{
			return false;
		}
	}
	
	public function GetStatus(){
		$qSelect = $this->DB->prepare("SELECT `status` FROM ".DB_PREFIX."_modules WHERE `module`=:mod");
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
	
	public static function GetEnabled(){
		$dbh = DBCreator::GetDbObject();
		$qSelect = $dbh->prepare("SELECT name FROM ".DB_PREFIX."_modules WHERE `status`=1");
		$qSelect->execute();
		return $qSelect->fetchAll(PDO::FETCH_ASSOC);
	} 
}


