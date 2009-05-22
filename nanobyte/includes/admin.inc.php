<?php
/**
*Admin will handle all functions for maintaining the backend of the site
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 05-May-2008
 */

 class Admin{
 
 	public static function DeleteObject($table, $field, $id){
		$DB = DBCreator::GetDbObject();
		$delete = $DB->prepare("delete from ".DB_PREFIX."_$table where `$field`=:id");
		$delete->bindParam(':id', $id);
		$delete->execute();
		if ($delete->rowCount()==1){
			return true;
		}else{
			return false;
		}
	}
	public static function EncodeConfParams($param){
		return base64_encode(str_rot13($param));
	}
	public static function WriteConfig($params){
		$cleanurl = $params['cleanurl'] ? 'true' : 'false';
		$perms = new Perms($params['defaultgroup']);
		$conf = <<<EOF
<?php
/*
 *This is the WiredCMS config file. 
 *This file is autogenerated at installation - and cannot be manually edited after install
 *To edit this file with the Web Form, log in as admin, go to the Admin page | Settings
 *
 *@author Mike Conway
 *@since Jun 3, 2008
*/
define("DB_USER", '{$params['dbuser']}');
define("DB_PASS", '{$params['dbpass']}');
define("DB_HOST", '{$params['dbhost']}');
define("DB_NAME", '{$params['dbname']}');
define("DB_PREFIX", '{$params['dbprefix']}');
define("PATH", '{$params['path']}');
define("SITE_NAME", '{$params['sitename']}');
define("SITE_SLOGAN", '{$params['siteslogan']}');
define("SITE_DOMAIN", '{$params['sitedomain']}');
define("UPLOAD_PATH", '{$params['uploadpath']}');
define("FILE_TYPES", '{$params['filetypes']}');
define("FILE_SIZE", '{$params['filesize']}');
define("CLEANURL", '{$params['cleanurl']}');
define("COMPRESS", '{$params['compress']}');
define("THEME_PATH", 'templates/{$params['themepath']}');
define("DEFAULT_GROUP", '{$perms->gid}');
define("SESS_TTL", '{$params['sessttl']}');
define("PEAR_PATH", '{$params['pearpath']}');
define("LIMIT", '{$params['limit']}');
define("HOME","{$params['home']}");
define("CMS_INSTALLED",true);
?>
EOF;
		file_put_contents('./includes/config.inc.php', $conf);
	}
}