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
			$dsn = 'mysql:dbname='.DB_NAME.';host='.DB_HOST;
			try {
				$pdo = new PDO($dsn, Core::DecodeConfParams(DB_USER), Core::DecodeConfParams(DB_PASS));
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