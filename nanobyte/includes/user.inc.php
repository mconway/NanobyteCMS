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
			$insert = $DB->prepare("insert into ".DB_PREFIX."_user (username, password, email, joined) values (:u, :p, :e, :t)");
			$insert->bindParam(':u',$uarray['name']);
			$insert->bindParam(':p',$pw);
			$insert->bindParam(':e',$uarray['email']);
			$insert->bindParam(':t',time());
				
			$profileq = $DB->prepare("insert into ".DB_PREFIX."_user_profiles (uid) SELECT uid FROM ".DB_PREFIX."_user WHERE `username`=:name");
			$profileq->bindParam(':name',$uarray['name']);
			try{
				$insert->execute();
				$profileq->execute();
			} catch (PDOException $e) {
				Core::SetMessage('Error creating User: ' . $e->getMessage().". Please contact the Webmaster");
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
			$logintime = $DB->prepare("update ".DB_PREFIX."_user set `lastlogin`=:login where `uid`=:uid");
			$logintime->bindParam(':login',time());
			$logintime->bindParam(':uid',$user->uid);
			$logintime->execute();
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
		
	public static function GetUserList($start=0, $limit=15){
		//return array for smarty
		$DB = DBCreator::GetDbObject();
		$result = $DB->prepare("select uid from ".DB_PREFIX."_user");
		$result = $DB->prepare("SELECT SQL_CALC_FOUND_ROWS `uid` FROM ".DB_PREFIX."_user ORDER BY joined DESC LIMIT {$start},{$limit}");
		//get the row count
		$cRows = $DB->prepare('SELECT found_rows() AS rows');
		$result->execute();
		$output = array();
		$cRows->execute();
        $nbItems = $cRows->fetch(PDO::FETCH_OBJ)->rows;
		if ($nbItems>($start+$limit)) $output['final'] = $start+$limit;
		else $output['final'] = $nbItems;
		$output['limit'] = $limit;
		$output['nbItems'] = $nbItems;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)){
			$output[$row['uid']] = new User($row['uid']); //Create an array of user objects
		}
		return $output;
	}
	
	public function SetAccessTime(){
		$dbh = DBCreator::GetDbObject();
		$query = $dbh->prepare("UPDATE ".DB_PREFIX."_user set `online`=:time where `uid`=:uid");
		$query->bindParam(':time',time());
		$query->bindParam(':uid',$this->uid);
		$query->execute();		
	}
	
	public static function GetAccessTime($id){
		$dbh = DBCreator::GetDbObject();
		$query = $dbh->prepare("SELECT `online` FROM ".DB_PREFIX."_user where `uid`=:uid");
		$query->bindParam(':uid',$id);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_ASSOC);
		return $row['online'];
	}
}
?>