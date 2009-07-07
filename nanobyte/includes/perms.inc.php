<?php

class Perms{
	private $dbh;
	
	public  function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		if ($id){
			$query = $this->dbh->prepare("select name, comments, permissions from ".DB_PREFIX."_groups where `gid`=:id");
			$query->execute(array(':id'=>$id));
			$info = $query->fetch(PDO::FETCH_ASSOC);
			$this->gid = $id;
			$this->name = $info['name'];
			$this->comments = $info['comments'];
			$this->permissions = $info['permissions'];
		}
	}
	
	public function addGroup($params){
		$insert = $this->dbh->prepare("insert into ".DB_PREFIX."_groups (name, comments) values (:name, :comm)");
		$insert->bindParam(':name',$params['name']);
		$insert->bindParam(':comm',$params['comments']);
		$insert->execute();
		if ($insert->rowCount() == 1){
			$this->__construct($this->dbh->lastInsertId());
			return true;
		}else{
			return false;
		}
	}
	
	public function commit(){
		global $core;
		foreach($this->data as $key=>$role){
			$perm = implode(',',$role);
			$query = $this->dbh->prepare("update ".DB_PREFIX."_groups set `permissions`=:perm where `name`=:name");
			$query->bindParam(':perm', $perm);
			$query->bindParam('name', $key);
			try{
				$query->execute();
			}catch(PDOException $e){
				$core->SetMessage('Unable to update '.$key.'. Error: '.$e->getMessage());
			}
		}
	}
	
	public function getAll(){
		$groups = $this->dbh->prepare("select * from ".DB_PREFIX."_groups");
		$groups->execute();
		$all = $groups->fetchAll(PDO::FETCH_ASSOC);
		$this->all = $all;
	}
	
	public function getNames(){
		$namesQ = $this->dbh->prepare("select gid, name from ".DB_PREFIX."_groups");
		$namesQ->execute();
		$all = $namesQ->fetchAll(PDO::FETCH_ASSOC);
		foreach($all as $name){
				$this->names[$name['gid']] = $name['name'];
		}
	}
	
	public function getPermissionsList(){
		$permsQ = $this->dbh->prepare("select category, description from ".DB_PREFIX."_perms");
		$permsQ->execute();
		return $permsQ->fetchAll(PDO::FETCH_ASSOC);
	}
	
}
