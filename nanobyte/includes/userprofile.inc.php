<?php

Class UserProfile{
	private $dbh;
	public function __construct($id){
		$this->dbh = DBCreator::GetDbObject();
		$query = $this->dbh->prepare("select user.username, user.email, user.lastlogin, profile.avatar, profile.location, profile.about from ".DB_PREFIX."_user AS user LEFT JOIN ".DB_PREFIX."_user_profiles AS profile ON user.uid = profile.uid where profile.uid=:id"); 
		$query->bindParam(':id', $id);
		$query->execute();
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$this->uid = $id;
		$this->name = $row['username'];
		$this->email = $row['email'];
		$this->avatar = $row['avatar'];
		$this->location = $row['location'];
		$this->about = $row['about'];
		$this->lastlogin = $row['lastlogin'];
		$this->online = UserController::CheckOnline($id);
	}
	public function Commit(){
		//better to make empty profile on user creation
		$sql = $this->dbh->prepare("update ".DB_PREFIX."_user_profiles set `avatar`=:av, `location`=:loc, `about`=:about where `uid`=:uid");
		$sql->bindParam(':av', $this->avatar);
		$sql->bindParam(':loc', $this->location);
		$sql->bindParam(':about', $this->about);
		$sql->bindParam(':uid', $this->uid);
		try{
			$sql->execute();
			if ($sql->rowCount() == 1){
				return true;
			}else{
				return false;
			}
		}catch(PDOException $e){
			Core::SetMessage('Error updating user profile: ' . $e->getMessage(), 'error');
		}
		

	}
}

?>