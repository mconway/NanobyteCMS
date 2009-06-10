<?php
/**
*User will make a PDO database connection to retrieve user info
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 01-May-2008
 */

class User{
	private $dbh;
	private $email;
	private $group;
	private	$joined;
	private $name;
	private $password;
	public $patterns;
	private $permissions;
	private $salt;
	private $uid;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
//		$this->patterns = array('%u'=>$this->name,'%p'=>$this->uePassword);
		if($id==0){
			$this->uid = 0;
			$this->name = 'Guest';
			$perms = $this->dbh->query("select name, permissions from ".DB_PREFIX."_groups where `gid`=3")->fetch(PDO::FETCH_ASSOC);
			$this->permissions = array_flip(explode(',' , $perms['permissions']));
			$this->group = $perms['name'];
		}elseif(isset($id)){
			$user = $this->dbh->prepare("SELECT u.uid, u.username, u.email, u.joined, u.password, p.name, p.permissions FROM ".DB_PREFIX."_user AS u LEFT JOIN ".DB_PREFIX."_groups AS p ON u.gid=p.gid WHERE `uid`=:id");
			$user->execute(array(':id'=>$id));
			$row = $user->fetch(PDO::FETCH_ASSOC);
			$this->uid = $row['uid'];
			$this->name = $row['username'];
			$this->email = $row['email'];
			$this->joined = $row['joined'];
			$this->password = $row['password'];
			$this->salt = substr($row['password'], 3);	
			$this->permissions = array_flip(explode(',' , $row['permissions']));
			$this->group = $row['name'];
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $name
	 */
	public function __get($name){
		return $this->$name;
	}

	/**
	 * 
	 * @return 
	 * @param object $name
	 * @param object $value
	 */
	public function __set($name,$value){
		$this->$name = $value;
	}
	
	/**
	 * 
	 * @return 
	 */
	public function commit(){
		if($this->pwchanged){
			$this->password = $this->GetPassword($this->name, $this->pwchanged);
		}
		$sql = $this->dbh->prepare("update ".DB_PREFIX."_user set `password`=:pass, `email`=:e where `uid`=:uid");
		$sql->execute(array(':pass'=>$this->password,':e'=>$this->email,':uid'=>$this->uid));
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $array
	 */
	public function create($array){
		global $core;
		$pw = $this->GetPassword($array['name'],$array['password']);
		$result = $this->dbh->prepare("SELECT username, email FROM ".DB_PREFIX."_user WHERE username=:u OR email=:email");
		$result->execute(array(':u'=>$array['name'],':email'=>$array['email']));
		if ($result->rowCount() >= 1){
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if($row['username'] == $array['name']){
				$core->SetMessage('Error creating User: The username you have chosen already exists.');
				return false;
			}elseif($row['email'] == $array['email']){
				$core->SetMessage('Error creating User: The email you have entered is already in our system.');
				return false;
			}
		}else{
			$insert = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user (username, password, email, joined, gid) VALUES (:u, :p, :e, :t, :g)");	
//			$profileq = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user_profiles (uid) SELECT uid FROM ".DB_PREFIX."_user WHERE `username`=:name");
			try{
				$insert->execute(array(':u'=>$array['name'],':p'=>$pw,':e'=>$array['email'],':t'=>time(),':g'=>DEFAULT_GROUP));
				if($insert->rowCount()==1){
					$this->__construct($this->dbh->lastInsertId());
					$this->uePassword = $array['password'];
					$email = new Email();
					$email->addRecipient($this->email);
					$emailData = $email->getEmailData('register');
					$patterns = array('/%u/','/%p/');
					$email->setSubject($emailData['subject']);
//					$body = preg_replace($patterns,$this->patterns,$emailData['body']);
//					var_dump($this->patterns);
					$email->setBody(preg_replace($patterns,array($this->name,$this->uePassword),$emailData['body']));
					$email->sendMessage();
				}
//				$profileq->execute(array(':name'=>$array['name']));
			} catch (PDOException $e) {
				$core->SetMessage('Error creating User: ' . $e->getMessage().". Please contact the Webmaster");
			}
			return true;
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $length[optional]
	 * @param object $level[optional]
	 */
	public function generatePassword($length=6,$level=4){
		list($usec, $sec) = explode(' ', microtime());
		srand((float) $sec + ((float) $usec * 100000));
		if($level==4){
			$level = rand(1,3);
		}
		$validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
		$validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";
		
		$password  = "";
		$counter   = 0;
		
		while ($counter < $length) {
			$actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);
			
			// All character must be different
			if (!strstr($password, $actChar)) {
				$password .= $actChar;
				$counter++;
			}
		}
		return $password;
	}
	
	/**
	 * 
	 * @return 
	 */
	public function getAccessTime(){
		$query = $this->dbh->prepare("SELECT `online` FROM ".DB_PREFIX."_user where `uid`=:uid");
		$query->execute(array(':uid'=>$id));
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$this->accessTime = $row['online'];
	}

	/**
	 * 
	 * @return 
	 * @param object $username
	 * @param object $pass
	 *  returns 40 Char PW hash salted with Username--- DO NOT EVER CHANGE THIS!!!
	 */	
	private function getPassword($username, $pass){
		return sha1($pass . substr(str_rot13(strtolower($username)), 0, 3));
	}

	/**
	 * 
	 * @return 
	 * @param object $username
	 * @param object $pass
	 */
	public function login($username, $pass){
		$pass = $this->GetPassword($username,$pass);
		$result = $this->dbh->prepare("SELECT uid, password FROM ".DB_PREFIX."_user WHERE `username`=:u");
		$result->execute(array(':u'=>$username));
		$row = $result->fetch(PDO::FETCH_ASSOC);
		if ($pass == $row['password']){
			$user = new self($row['uid']);
			$_SESSION['user'] = $user->uid;
			$_SESSION['hash'] = $user->SessionHash();
			$logintime = $this->dbh->prepare("update ".DB_PREFIX."_user set `lastlogin`=:login where `uid`=:uid");
			$logintime->execute(array(':login'=>time(),':uid'=>$user->uid));
			$this->success=true;
			return true;
		}else{
			return false;
		}	
	}
	
	/**
	 * 
	 * @return 
	 */
	public function logout(){
		$sname = session_name();
		$_SESSION = array();
		session_destroy();
	}
	
	/**
	 * 
	 * @return 
	 * @param object $start[optional]
	 * @param object $limit[optional]
	 */
	public function read($start=0, $limit=15){
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_user ORDER BY joined DESC LIMIT {$start},{$limit}";
		$this->output = array();
		$this->output['items'] = $this->dbh->query($query)->fetchAll();
		//get the row count
		$cntRows = $this->dbh->query('SELECT found_rows() AS rows')->fetch(PDO::FETCH_OBJ)->rows;
		$this->output['final'] = ($cntRows >($start+$limit)) ? $start+$limit : $cntRows;
		$this->output['limit'] = $limit;
		$this->output['nbItems'] = $cntRows;
	}
	
	/**
	 * 
	 * @return 
	 */
	public function resetPassword(){
		$genPw = $this->generatePassword();
		$pw = $this->getPassword($this->username, $genPw);
		$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user SET password=:pw WHERE username=:un");
		$query->execute(array(':pw'=>$pw,':un'=>$this->username));
		if($query->rowCount()==1){
			$email = new Email();
			$email->setSubject("Your password has been reset");
			$email->setBody("Your password has been reset. your new password is: ".$genPw);
			$email->addRecipient($this->email);
			$email->sendMessage();
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public function sessionHash(){
		return md5($this->name . $this->salt);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function setAccessTime(){
		$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user set `online`=:time where `uid`=:uid");
		$query->execute(array(':time'=>time(),':uid'=>$this->uid));		
	}
	
	/**
	 * 
	 * @return 
	 */
	public function validateEmail(){
		$query = $this->dbh->prepare("SELECT email FROM ".DB_PREFIX."_user WHERE email=:email AND username=:username");
		$query->execute(array(':email'=>$this->email,':username'=>$this->username));
		if($query->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
}
?>