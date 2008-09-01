<?php
/**
*User will make a PDO database connection to retrieve user info
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 01-May-2008
 */

class User{
	private $password;
	function __construct($id){
		$DB = DBCreator::GetDbObject();
		if($id==0){
			$this->uid = 0;
			$this->name = 'Guest';
			
			$perms = $DB->prepare("select name, permissions from ".DB_PREFIX."_groups where `gid`=:id");
			$perms->bindValue(':id',3);
			$perms->execute();
			$grow = $perms->fetch(PDO::FETCH_ASSOC);
			$this->permissions = array_flip(explode(',' , $grow['permissions']));
			$this->group = $grow['name'];
		}else{
			
			$user = $DB->prepare("select * from ".DB_PREFIX."_user where `uid`=:id");
			$user->bindParam(':id', $id);
			$user->execute();
			$urow = $user->fetch(PDO::FETCH_ASSOC);
			$this->uid = $urow['uid'];
			$this->name = $urow['username'];
			$this->email = $urow['email'];
			$this->joined = $urow['joined'];
			$this->password = $urow['password'];
			$this->salt = substr($urow['password'], 3);
			
			$perms = $DB->prepare("select name, permissions from ".DB_PREFIX."_groups where `gid`=:id");
			$perms->bindParam(':id',$urow['gid']);
			$perms->execute();
			$grow = $perms->fetch(PDO::FETCH_ASSOC);
			$this->permissions = array_flip(explode(',' , $grow['permissions']));
			$this->group = $grow['name'];
		}

	}

	public static function CreateUser($uarray){
		$pw = User::GetPassword($uarray['name'],$uarray['password']);
		$DB = DBCreator::GetDbObject();
		$result = $DB->prepare("select username from ".DB_PREFIX."_user where `username`=:u");
		$result->bindParam(':u', $uarray['name']);
		$result->execute();
		if ($result->rowCount() == 1){
			return false;
		}else{
			try{
				$insert = $DB->prepare("insert into ".DB_PREFIX."_user (username, password, email, joined) values (:u, :p, :e, :t)");
				$insert->bindParam(':u',$uarray['name']);
				$insert->bindParam(':p',$pw);
				$insert->bindParam(':e',$uarray['email']);
				$insert->bindParam(':t',time());
				$insert->execute();
			} catch (PDOException $e) {
				die('Error creating User: ' . $e->getMessage());
			}
			return true;
		}
	}

	public static function Login($user, $pass){
		$pass = User::GetPassword($user,$pass);
		$DB = DBCreator::GetDbObject();
		$result = $DB->prepare("select uid, password from ".DB_PREFIX."_user where `username`=:u");
		$result->bindParam(':u', $user);
		$result->execute();
		$row = $result->fetch(PDO::FETCH_ASSOC);
		if ($pass == $row['password']){
			$user = new self($row['uid']);
			$_SESSION['user'] = serialize($user);
			$_SESSION['hash'] = $user->SessionHash();
			return $user;
		}else{
			return false;
		}	
	}
	
	public static function Logout(){
		$sname = session_name();
		$_SESSION = array();
		session_destroy();
	}
	
	public function SessionHash(){
		return md5($this->name . $this->salt);
	}
	
	public function Commit(){
		$DB = DBCreator::GetDbObject();
		if($this->pwchanged){
			$this->password = User::GetPassword($this->name, $this->pwchanged);
		}
		$pgroup = new Perms($this->group);
		$sql = $DB->prepare("update ".DB_PREFIX."_user set `password`=:pass, `email`=:e, `gid`=:gid where `uid`=:uid");
		$sql->bindParam(':pass', $this->password);
		$sql->bindParam(':e', $this->email);
		$sql->bindParam(':gid', $pgroup->gid);
		$sql->bindParam(':uid', $this->uid);
		$sql->execute();
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	// returns 40 Char PW hash salted with Username--- DO NOT EVER CHANGE THIS!!!
	private static function GetPassword($user, $pass){
		return sha1($pass . substr(str_rot13(strtolower($user)), 0, 3));
	}
}
?>