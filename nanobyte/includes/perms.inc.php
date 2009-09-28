<?php

class Perms extends Core{
	private $dbh;
	private $permissions;
	private $comments;
	private $name;
	private $names = '';
	private $gid;
	
	public  function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		if(isset($id)){
			$select_query = $this->dbh->prepare("SELECT name, comments, perm_id, description FROM ".DB_PREFIX."_groups AS g LEFT JOIN ".DB_PREFIX."_group_perms AS gp ON gp.group_id=g.gid LEFT JOIN ".DB_PREFIX."_perms AS p ON p.id=gp.perm_id WHERE gid=:id");
			$select_query->execute(array(':id'=>$id));
			$this->permissions = $select_query->fetchAll(PDO::FETCH_OBJ);
			$this->gid = $id;
			$this->name = $this->permissions[0]->name;
			$this->comments = $this->permissions[0]->comments;
		}
	}
	
	/**
	 * Magic get method for private variables
	 * @return mixed
	 * @param mixed $name
	 */
	public function __get($name){
		return $this->$name;
	}

	/**
	 * Magic set method for private variables
	 * @return void
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value){
		$this->$name = $value;
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
	
	public function addUserToGroup($uid,$group=DEFAULT_GROUP){
		$select_query = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_user_groups WHERE user_id=:uid");
		try{
			$select_query->execute(array(':uid'=>$uid));
			if($select_query->rowCount()==1){
				$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user_groups SET group_id =:gid WHERE user_id=:uid");
			}else{
				$query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user_groups (user_id, group_id) VALUES (:uid,:gid)");
			}
			try{
				$query->execute(array(':uid'=>$uid,':gid'=>$group));
			}catch(PDOException $e){
		
			}
		}catch(PDOException $e){
			
		}
	}
	
	public function commit(){ // FIX THIS
		$Core = BaseController::getCore();
		
		$insert_query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_group_perms (perm_id,group_id) VALUES (:role,(SELECT gid FROM ".DB_PREFIX."_groups WHERE name=:name))");
		$delete_query = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_group_perms");
		$delete_query->execute();
		foreach($this->data as $group_name=>$roles){ //foreach permission, add or delete a row to cms_group_perms
			foreach($roles as $role){
				$params = array(':role'=>$role,':name'=>$group_name);
				try{
					$insert_query->execute($params);
				}catch(PDOException $e){
					$Core->setMessage('Unable to update '.$group_name.'. Error: '.$e->getMessage());
				}
			}
		}
	}
	
	public function createPerm($permissions,$cat='Nanobyte'){
		if(!is_array($permissions)){
			$permissions = array($permissions);
		}
		$insert_query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_perms (category, description) VALUES (:cat,:desc)");
		foreach($permissions as $p){
			try{
				$insert_query->execute(array(':cat'=>strtolower($cat),':desc'=>strtolower($p)));
			}catch(PDOException $e){
				
			}
		}
		
		$update_query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_groups SET permissions=CONCAT_WS(',',permissions,:p) WHERE name='admin'");
		try{
			$update_query->execute(array(':p'=>implode(",",$permissions)));
		}catch(PDOException $e){
			
		}
		
	}
	
	public function getAllGroups(){
		$groups = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_groups");
		$groups->execute();
		$all = $groups->fetchAll(PDO::FETCH_ASSOC);
		$this->groups = $all;
	}
	
	public function getNames(){
		$namesQ = $this->dbh->prepare("select gid, name from ".DB_PREFIX."_groups");
		$namesQ->execute();
		$all = $namesQ->fetchAll(PDO::FETCH_ASSOC);
		foreach($all as $name){
				$this->names[$name['gid']] = $name['name'];
		}
	}

	/**
	 * Query the database for all permissions for a specified group
	 * @return object Permissions
	 * @param int $id
	 */
	public function getPermissionsForGroup($id){
		$query = $this->dbh->prepare("SELECT gp.id, group_id, perm_id, description FROM ".DB_PREFIX."_group_perms AS gp LEFT JOIN ".DB_PREFIX."_perms AS p ON gp.perm_id=p.id WHERE group_id=:id");
		$query->execute(array(':id'=>$id));
		return $query->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function getPermissionsList(){
		$permsQ = $this->dbh->prepare("SELECT id,category, description FROM ".DB_PREFIX."_perms");
		$permsQ->execute();
		return $permsQ->fetchAll(PDO::FETCH_OBJ);
	}

}
