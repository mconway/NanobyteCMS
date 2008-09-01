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
			$query = $this->dbh->prepare("select linkpath, linktext, viewableby from ".DB_PREFIX."_menu_links where `menu`=:name");
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
}
 ?>