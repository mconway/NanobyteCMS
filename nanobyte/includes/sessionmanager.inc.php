<?php
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
      $this->life_time = get_cfg_var("session.gc_maxlifetime");
      $this->DB = DBCreator::GetDbObject('wb_test');
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
      $sql = $this->DB->prepare("select `session_data` from `cms_sessions` where `session_id` = :id and `expires` > :time");
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
      $sql = $this->DB->prepare("replace `cms_sessions` (`session_id`,`session_data`,`expires`) values(:id, :data, :time)");
      $sql->bindParam(':id',$id);
      $sql->bindParam(':data',$data);
      $sql->bindParam(':time',$time);
      $sql->execute();
      return TRUE;
   }

   function destroy( $id ) {
      // Build query
      $sql = $this->DB->prepare("delete from `cms_sessions` where `session_id` = :id");
      $sql->bindParam(':id',$id);
      $sql->execute();
      return true;
   }

   function gc() {
      // Garbage Collection
      // Build DELETE query.  Delete all records who have passed the expiration time
      $sql = $this->DB->prepare("delete from `cms_sessions` where `expires` < UNIX_TIMESTAMP();");
      $sql->execute();
      // Always return TRUE
      return true;
   }
}
?>
