<?php
/*
 *This is the WiredCMS config file. 
 *This file is autogenerated at installation!
 *
 *@author Mike Conway
 *@copyright Mike Conway - Wiredbyte
 *@since Jun 3, 2008
 */
 $user = base64_encode(str_rot13('michael'));
 $pass = base64_encode(str_rot13('dathron'));
define("DB_USER", $user);
define("DB_PASSWORD", $pass);
define("DB_HOST",'db.wiredbyte.com');
define("DB_NAME", 'wb_test');
define("DB_PREFIX", 'cms');
define("PATH", 'WiredCMS'); //default dir
define("SITE_NAME", 'WiredCMS');
define("SITE_SLOGAN", 'The CMS for those who care');
?>
