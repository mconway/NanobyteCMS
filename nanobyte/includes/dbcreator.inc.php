<?php
/**
*DBCreator will create a PDO object that corresponds to database settings
*If more than one server/host or database is used, this will make the change 
*pretty seamless. 
*@author Mike Conway
*@copyright 2008, Mike Conway
*@since 01-May-2008
*/
//require_once './includes/config.php';
class DBCreator{
	/**
	*creates a PDO object - will not initialize duplicate objects
	*@param $database - The database to attach the PDO object to
	*@return - returns the PDO object for the given database. 
	*/
	public static function GetDbObject(){
		static $db_pdo;
		if (!isset($db_pdo)){
			$db_pdo = array();
		}
		if (array_key_exists(DB_NAME, $db_pdo) == false){
			$dsn = 'mysql:dbname=' . DB_NAME .';host='. DB_HOST;
			try {
				$pdo = new PDO($dsn, DB_USER, DB_PASS);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				die('Connection failed: ' . $e->getMessage());
			}
			$db_pdo[DB_NAME] = $pdo;
		}
		return $db_pdo[DB_NAME];
	}
}
?>