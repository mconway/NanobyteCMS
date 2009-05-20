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
			$this->name = $name;
		}
	}
	
	/*
	array(1) {
	  [0]=>
	  array(1) {
		[1]=>
		array(1) {
		  [0]=>
		  string(3) "mid"
		}
	  }
	}
	*/
	
	public function GetAll(){
		global $core;
		$query = $this->dbh->prepare("select mid, name, canDelete, parent_id from ".DB_PREFIX."_menus ORDER BY parent_id ASC");
		$query->execute();
		$results = $query->fetchAll(PDO::FETCH_ASSOC);
//		$this->all = array('0'=>array('name'=>'Nanobyte Root', 'id'=>0, 'parent id'=>'N/A', 'can delete'=>0));
		$this->all = $results;
//var_dump($results);
//		foreach($results as $row){
//			if(!isset($this->all[$row['parent_id']])){
//				if($row['parent_id'] > 0){
////					$result = $core->ArraySearchRecursive($row['parent_id'],$this->all);
////					//var_dump($result);
////					while($foundArray == false){
////						
////					}
//				}
//				
//				$this->all[$row['parent_id']] = array();
//			}
//			array_push($this->all[$row['parent_id']],$row);
//		}
		
//		foreach($results as $row){
//			if(!array_key_exists($row['parent_id'],$this->all)){
//				if($row['parent_id'] === '0'){
//					$this->all[$row['parent_id']] = array();
//				}else{ //loop through until it finds it's parent
//					for($i=0;!$foundParent;$i++){
//						 
//					}
				}
//				
//			}
//			if(!array_key_exists('children',$this->all[$row['parent_id']])){
//				$this->all[$row['parent_id']]['children'][$row['mid']] = array(
//					'name'=>$row['name'], 
//					'id'=>$row['mid'], 
//					'parent id'=>$row['parent_id'], 
//					'can delete'=>$row['canDelete']
//				);
//			}
//		}
//	}
	
	public function GetMenuName($mid){
		$query = $this->dbh->prepare("select name from ".DB_PREFIX."_menus where `mid`=:mid");
		$query->bindParam(':mid',$mid);
		$query->execute(array(':mid'=>$mid));
		$name = $query->fetch(PDO::FETCH_ASSOC);
		$this->name = $name['name'];
	}
	
	public function Commit($update=null){
		global $core;
		if($update){
			$query = $this->dbh->prepare("insert into ".DB_PREFIX."_menu_links (menu, linkpath, linktext, viewableby, class, styleid) values (:menu,:path,:text,:view,:class,:sid)");
		}else{
			$query = $this->dbh->prepare("update ".DB_PREFIX."_menu_links set `linkpath`=:path, `linktext`=:text, `viewableby`=:view, `class`=:class, `styleid`=:sid where `id`=:id");
			$id = true;
		}
		foreach($this->data as $key=>$item){
			if($item['class']==null){
				$item['class']='';
			}
			if($item['styleid']==null){
				$item['styleid']='';
			}
			$item['viewableby'] = implode(',',$item['viewableby']);
			$array = array(':path'=>$item['linkpath'],':text'=>$item['linktext'],':view'=>$item['viewableby'],':class'=>$item['class'],'sid'=>$item['styleid']);
			$id==true ? $array['id']=$key : $array[':menu']=$update;
			try{
				$query->execute($array);
			}catch(PDOException $e){
				$core->SetMessage('Unable to update item #'.$key.'. Error: '.$e->getMessage());
			}
		}
	}

	public function Create(){
		global $core;
		$query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_menus SET name=:name,canDelete=1");
		$query->execute(array(':name'=>$_POST['name']));
		if($query->rowCount()==1){
			$core->SetMessage('The menu was created successfully!','info');
		}else{
			$core->SetMessage('There was an error creating this menu','error');
		}
	}
	
	public function Delete(){
		global $core;
		$query = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_menus WHERE name=:name");
		$query->execute(array(':name'=>$this->name));
		if($query->rowCount()==1){
			$core->SetMessage('The menu was deleted successfully!','info');
		}else{
			$core->SetMessage('There was an error deleting this menu','error');
		}
	}
}
 ?>