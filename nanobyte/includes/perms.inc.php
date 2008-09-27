<?php

class Perms{
	private $DB;
	function __construct($name=null){
		$this->DB = DBCreator::GetDbObject();
		if ($name){
			$query = $this->DB->prepare("select gid, permissions from ".DB_PREFIX."_groups where `name`=:name");
			$query->bindParam('name',$name);
			$query->execute();
			$info = $query->fetch(PDO::FETCH_ASSOC);
			$this->gid = $info['gid'];
			$this->name = $name;
			$this->permissions = $info['permissions'];
		}
	}
	
	public function GetAll(){
		$groups = $this->DB->prepare("select * from ".DB_PREFIX."_groups");
		$groups->execute();
		$all = $groups->fetchAll(PDO::FETCH_ASSOC);
		$this->all = $all;
	}
	
	public function GetNames(){
		$namesQ = $this->DB->prepare("select name from ".DB_PREFIX."_groups");
		$namesQ->execute();
		$all = $namesQ->fetchAll(PDO::FETCH_ASSOC);
		foreach($all as $name){
				$this->names[$name['name']] = $name['name'];
		}
	}
	public function GetPermissionsList(){
		$permsQ = $this->DB->prepare("select category, description from ".DB_PREFIX."_perms");
		$permsQ->execute();
		return $permsQ->fetchAll(PDO::FETCH_ASSOC);
	}
	public function Commit(){
		foreach($this->data as $key=>$role){
			$perm = implode(',',$role);
			$query = $this->DB->prepare("update ".DB_PREFIX."_groups set `permissions`=:perm where `name`=:name");
			$query->bindParam(':perm', $perm);
			$query->bindParam('name', $key);
			try{
				$query->execute();
			}catch(PDOException $e){
				Core::SetMessage('Unable to update '.$key.'. Error: '.$e->getMessage());
			}
		}
	}
	public function AddGroup($params){
		$insert = $this->DB->prepare("insert into ".DB_PREFIX."_groups (name, comments) values (:name, :comm)");
		$insert->bindParam(':name',$params['name']);
		$insert->bindParam(':comm',$params['comments']);
		$insert->execute();
		if ($insert->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
}
