<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
/**
*Admin will handle all functions for maintaining the backend of the site
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 05-May-2008
 */

 class Admin{
 
 	public static function deleteObject($table, $field, $id){
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
	
	public static function encodeConfParams($param){
		return base64_encode(str_rot13($param));
	}
	
	public static function writeConfig($params){
		$cleanurl = $params['cleanurl'] ? 'true' : 'false';
		$perms = new Perms($params['defaultgroup']);
		$conf = <<<EOF
<?php
/*
 *This is the Nanobyte configuration file. 
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
define("SITE_LOGO", '{$params['sitelogo']}');
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

define("CMS_INSTALLED",'{$params['cms_installed']}');

define("EMAIL_FROM",'{$params['from_name']}');
define("EMAIL_SUBJECT",'{$params['subject']}');
define("EMAIL_IS_HTML",'{$params['use_html']}');

define("SMTP_AUTH",'{$params['smtp_auth']}');
define("SMTP_SERVER",'{$params['smtp_host']}');
define("SMTP_PORT",'{$params['smtp_port']}');
define("SMTP_USER",'{$params['smtp_user']}');
define("SMTP_PASS",'{$params['smtp_pass']}');

define('ALLOWED_HTML_TAGS','{$params['allowed_html_tags']}');
?>
EOF;
		file_put_contents('./includes/config.inc.php', $conf);
	}

	public static function toggleCMSInstalled($flag){
		$tmp = file_get_contents('includes/config.inc.php');
		$bool = $flag===true ? '0' : '1';
		$tmp = str_replace('define("CMS_INSTALLED",\''.$bool.'\');','define("CMS_INSTALLED",\''.$flag.'\');',$tmp);
		file_put_contents('includes/config.inc.php',$tmp);
	}
}