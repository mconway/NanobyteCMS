<?php
class ConfigurationManager{
	
	private static $_configs = array();
	
	public static function getConfig($fileName){
		if(!isset(self::$_configs[$fileName]))
			self::$_configs[$fileName] = new Configuration($fileName);
			
		return self::$_configs[$fileName];
	}
	
}