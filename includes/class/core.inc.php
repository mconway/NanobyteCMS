<?php
class Core{
	
	private static $_database;
	private static $_configFile;
	
	public static function getConfigFile(){
		return self::$_configFile;
	}
	
	public static function setConfigFile($fileName){ //maybe use config types.
		if(!file_exists($fileName))
			throw new Exception("Unable to find configuration file.");
		self::$_configFile = $fileName;
	}
	
	public static function setDatabase(PDO $database){
		if(isset(self::$_database))
			throw new Exception("Database is already set. This cannot be changed.");
		self::$_database = $database;
	}
	
}