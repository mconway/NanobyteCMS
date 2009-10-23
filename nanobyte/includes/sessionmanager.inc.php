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
/*
 * Created on May 22, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * from http://www.devshed.com/c/a/PHP/Storing-PHP-Sessions-in-a-Database/
 * require_once("sessions.php");
 * $sess = new SessionManager();
 * session_start();
 */
 class SessionManager {
   var $life_time;
   private $DB;
   function __construct() {
      // Read the maxlifetime setting from PHP
      $this->life_time = SESS_TTL > 0 ? SESS_TTL : 10800;
      $this->DB = DBCreator::GetDbObject();
      // Register this object as the session handler
      session_set_save_handler( 
        array( &$this, "open" ), 
        array( &$this, "close" ),
        array( &$this, "read" ),
        array( &$this, "write"),
        array( &$this, "destroy"),
        array( &$this, "gc" )
      );
   }

   function open( $save_path, $session_name ) {
      global $sess_save_path;
      $sess_save_path = $save_path;
      // Don't need to do anything. Just return TRUE.
      return true;
   }

   function close() {
      return true;
   }

   function read($id) {
      // Set empty result
      $data = '';
      // Fetch session data from the selected database
      $time = time();
      $sql = $this->DB->prepare("SELECT session_data FROM ".DB_PREFIX."_sessions WHERE session_id = :id and expires > :time");
      $sql->bindParam(':id',$id);
      $sql->bindParam(':time',$time);
      $sql->execute();                           
      $a = $sql->rowCount();
      if($a > 0) {
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $data = $row['session_data'];
      }
      return $data;
   }

   function write( $id, $data ) {
      // Build query                
      $time = time() + $this->life_time;
      $sql = $this->DB->prepare("REPLACE ".DB_PREFIX."_sessions (session_id,session_data,expires) values(:id, :data, :time)");
      $sql->bindParam(':id',$id);
      $sql->bindParam(':data',$data);
      $sql->bindParam(':time',$time);
      $sql->execute();
      return true;
   }

   function destroy( $id ) {
      // Build query
      $sql = $this->DB->prepare("DELETE from ".DB_PREFIX."_sessions where session_id = :id");
      $sql->bindParam(':id',$id);
      $sql->execute();
      return true;
   }

   function gc() {
      // Garbage Collection
      // Build DELETE query.  Delete all records who have passed the expiration time
      $sql = $this->DB->prepare("DELETE FROM ".DB_PREFIX."_sessions where expires < UNIX_TIMESTAMP();");
      $sql->execute();
      // Always return TRUE
      return true;
   }
}
?>
