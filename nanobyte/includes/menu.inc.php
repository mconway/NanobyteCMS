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
			$query = $this->dbh->prepare("select menus.mid, menus.name, links.id, links.linkpath, links.linktext, links.viewableby, links.class, links.styleid from ".DB_PREFIX."_menus AS menus LEFT JOIN ".DB_PREFIX."_menu_links AS links ON menus.mid=links.menu where menus.name=:name");
			$query->execute(array(':name'=>$name));
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
		$query->execute(array(':mid'=>$mid));
		$name = $query->fetch(PDO::FETCH_ASSOC);
		$this->name = $name['name'];
	}
	
	public function Commit($update=null){
		if($update){
			$query = $this->dbh->prepare("insert into ".DB_PREFIX."_menu_links (menu, linkpath, linktext, viewableby, class, styleid) values (:menu,:path,:text,:groups,:class,:sid)");
			$query->bindParam(':menu',$update);
		}else{
			$query = $this->dbh->prepare("update ".DB_PREFIX."_menu_links set `linkpath`=:path, `linktext`=:text, `viewableby`=:groups, `class`=:class, `styleis`=:sid where `id`=:id");
			$id = true;
		}
		foreach($this->data as $key=>$item){
			$item['viewableby'] = implode(',',$item['viewableby']);
			$id ? $query->bindParam(':id', $key) : null;
			try{
				$query->execute(array(':path'=>$item['linkpath'],':text'=>$item['linktext'],':groups'=>$item['viewableby'],':class'=>$item['class'],':sid'=>$item['styleid']));
			}catch(PDOException $e){
				Core::SetMessage('Unable to update item #'.$key.'. Error: '.$e->getMessage());
			}
		}
	}
}
 ?>