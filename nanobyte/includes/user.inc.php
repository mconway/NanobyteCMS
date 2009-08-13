<?php
/**
 * @author Mike Conway <mike@nanobytecms.com>
 * @copyright Copyright (c) 2009, Mike Conway
 * @since Version .01
 */

/**
 * Creates and stores a connection to the database and makes all database calls on the user table.
 * Handles password and account creation. Handles password resets and getting user data for display
 */
class User extends Core{
	
	/**
	 * @var object
	 */
	private $dbh;
	/**
	 * @var string
	 */
	private $email;
	/**
	 * @var string
	 */
	private $group;
	/**
	 * @var int
	 */
	private	$joined;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var array
	 */
	private $permissions;
	/**
	 * @var string
	 */
	private $salt;
	/**
	 * @var integer
	 */
	private $uid;
	
	/**
	 * Create the User object
	 * @return void
	 * @param string/integer $id[optional]
	 */
	public function __construct($id=null,&$Core=NULL){
		if(!is_object($Core)){
			$Core = new Core(false);
		}
		$this->dbh = DBCreator::GetDbObject();
		if($id==0){
			$this->uid = 0;
			$this->name = 'Guest';
			$this->permissions = new Perms(3);
			$this->group = $this->permissions->permissions[0]->name;
		}elseif(isset($id)){
			$dbh = $this->dbh->prepare("SELECT uid, username, email, joined, password, group_id FROM ".DB_PREFIX."_user LEFT JOIN ".DB_PREFIX."_user_groups ON uid=user_id WHERE uid=:id");
			$dbh->execute(array(':id'=>$id));
			$row = $dbh->fetch(PDO::FETCH_OBJ);
			$this->uid = $row->uid;
			$this->name = $row->username;
			$this->email = $row->email;
			$this->joined = $row->joined;
			$this->password = $row->password;
			$this->salt = substr($row->password, 3);	
			$this->permissions = new Perms($row->group_id);
			$this->group = $this->permissions->permissions[0]->name;
			if($Core->isEnabled('UserProfile')){
				$this->profile = new Mod_UserProfile($this->uid);
			}
			
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
	
	/**
	 * Save changed object data to the database
	 * @return bool
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
	 * Create a new user in the database
	 * Inserts a new user into the database. Generates an email informing the user of their registration.
	 * @return bool
	 * @param array $array
	 */
	public function create($array){
		$Core = BaseController::getCore();
		$pw = $this->GetPassword($array['name'],$array['password']);
		$result = $this->dbh->prepare("SELECT username, email FROM ".DB_PREFIX."_user WHERE username=:u OR email=:email");
		$result->execute(array(':u'=>$array['name'],':email'=>$array['email']));
		if ($result->rowCount() >= 1){
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if($row['username'] == $array['name']){
				$Core->SetMessage('Error creating User: The username you have chosen already exists.');
				return false;
			}elseif($row['email'] == $array['email']){
				$Core->SetMessage('Error creating User: The email you have entered is already in our system.');
				return false;
			}
		}else{
			$insert = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user (username, password, email, joined) VALUES (:u, :p, :e, :t)");	
//			$profileq = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_user_profiles (uid) SELECT uid FROM ".DB_PREFIX."_user WHERE `username`=:name");
			try{
				$insert->execute(array(':u'=>$array['name'],':p'=>$pw,':e'=>$array['email'],':t'=>time()));
				if($insert->rowCount()==1){
					$this->permissions->addUserToGroup($this->dbh->lastInsertId());
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
				$Core->SetMessage('Error creating User: ' . $e->getMessage().". Please contact the Webmaster");
			}
			return true;
		}
	}
	
	/**
	 * Generate a new password to replace a forgotten one
	 * Generates a new password for an account. Level 4 [default] will randomize the security level used during generation.
	 * @return string $password
	 * @param integer $length[optional]
	 * @param integer $level[optional]
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
	 * Get the last page access time of a user
	 * @return void
	 */
	public function getAccessTime(){
		$query = $this->dbh->prepare("SELECT `online` FROM ".DB_PREFIX."_user where `uid`=:uid");
		$query->execute(array(':uid'=>$id));
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$this->accessTime = $row['online'];
	}

	/**
	 * Generate 40 Char PW hash salted with Username--- DO NOT EVER CHANGE THIS!!!
	 * @return string $password
	 * @param string $username
	 * @param string $pass
	 */	
	private function getPassword($username, $pass){
		return sha1($pass . substr(str_rot13(strtolower($username)), 0, 3));
	}

	/**
	 * Log a user in with a given username and password
	 * Check the username and password given for login, and create a new user object with full information if successful
	 * @return bool
	 * @param string $username
	 * @param string $pass
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
	 * Log a user out
	 * Log a user out, destroy the User object and their session
	 * @return void
	 */
	public function logout(){
		$sname = session_name();
		$_SESSION = array();
		session_destroy();
	}
	
	/**
	 * Get data for all users in a database to display
	 * @return void
	 * @param integer $start[optional]
	 * @param integer $limit[optional]
	 */
	public function read($start=0, $limit=15){
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_user LEFT JOIN ".DB_PREFIX."_user_groups ON user_id=uid ORDER BY joined DESC LIMIT {$start},{$limit}";
		$this->output = array();
		$this->output['items'] = $this->dbh->query($query)->fetchAll();
		//get the row count
		$cntRows = $this->dbh->query('SELECT found_rows() AS rows')->fetch(PDO::FETCH_OBJ)->rows;
		$this->output['final'] = ($cntRows >($start+$limit)) ? $start+$limit : $cntRows;
		$this->output['limit'] = $limit;
		$this->output['nbItems'] = $cntRows;
	}
	
	/**
	 * Request a new password and email it to a user
	 * Called when a user forgot their password. Verifies the user's information and emails them a new password to use for login.
	 * @return bool
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
	 * Create an MD5 hash to verify user sessions
	 * Creates an MD5 hash with username and salt. This allows pages to verify the session has not been hijacked, as it compares to the user's login information.
	 * @return string
	 */
	public function sessionHash(){
		return md5($this->name . $this->salt);
	}
	
	/**
	 * Sets the last time a user accessed or requested a page
	 * @return void
	 */
	public function setAccessTime(){
		$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_user set `online`=:time where `uid`=:uid");
		$query->execute(array(':time'=>time(),':uid'=>$this->uid));		
	}
	
	/**
	 * Valiate the email given by a user is the same that was registered by that username
	 * @return bool
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