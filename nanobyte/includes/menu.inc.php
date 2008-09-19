<?php
/**
 *Queries database and creates/edits menu information
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 31-Aug-2008
 */

class Menu{
	private $dbh;
	public function __construct($name=null){
		$this->dbh = DBCreator::GetDbObject();
		if($name){
			$query = $this->dbh->prepare("select menus.mid, menus.name, links.id, links.linkpath, links.linktext, links.viewableby from ".DB_PREFIX."_menus AS menus LEFT JOIN ".DB_PREFIX."_menu_links AS links ON menus.mid=links.menu where menus.name=:name");
			$query->bindParam(':name',$name);
			$query->execute();
			$this->menu = $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	public function GetAll(){
		$query = $this->dbh->prepare("select mid, name from ".DB_PREFIX."_menus");
		$query->execute();
		$this->all = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	public function GetMenuName($mid){
		$query = $this->dbh->prepare("select name from ".DB_PREFIX."_menus where `mid`=:mid");
		$query->bindParam(':mid',$mid);
		$query->execute();
		$name = $query->fetch(PDO::FETCH_ASSOC);
		$this->name = $name['name'];
	}
	public function Commit($update=null){
		if($update){
			$query = $this->dbh->prepare("insert into ".DB_PREFIX."_menu_links (menu, linkpath, linktext, viewableby) values (:menu,:path,:text,:groups)");
			$query->bindParam(':menu',$update);
		}else{
			$query = $this->dbh->prepare("update ".DB_PREFIX."_menu_links set `linkpath`=:path, `linktext`=:text, `viewableby`=:groups where `id`=:id");
			$id = true;
		}
		foreach($this->data as $key=>$item){
			$item['viewableby'] = implode(',',$item['viewableby']);
			
			$query->bindParam(':path', $item['linkpath']);
			$query->bindParam(':text', $item['linktext']);
			$query->bindParam(':groups', $item['viewableby']);
			$id ? $query->bindParam(':id', $key) : null;
			try{
				$query->execute();
			}catch(PDOException $e){
				Core::SetMessage('Unable to update item #'.$key.'. Error: '.$e->getMessage());
			}
		}
	}
}
 ?>