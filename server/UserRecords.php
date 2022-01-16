<?php

declare (strict_types=1);

require_once 'util.php';

// User type = dictionary of username, sessionid, session_expiry, salt,
// pwhash, usertype

class UserRecords {
   const USERTYPE_ADMIN    =  0;
   const USERTYPE_OPERATOR =  1;
   const USERTYPE_STANDARD =  2;

   private $internal_allusers = null;

   private function internal_user_loaddb () :void {
      $fcontents = file_get_contents ("userdb.ser");
      if ($fcontents === false) {
         $this->internal_allusers = null;
      } else {
         $this->internal_allusers = unserialize ($fcontents);
      }
   }

   private function internal_user_savedb () :bool {
      if (file_put_contents ('userdb.ser', serialize ($this->internal_allusers), LOCK_EX) === false)
         return false;
      else
         return true;
   }

   static public function pwhash (string $username, string $salt, string $passwd) :string {
      return hash ('sha256', $username . ':' . $salt . ':' . $passwd);
   }

   public function __construct () {
      $this->internal_user_loaddb ();
      if ($this->internal_allusers == null) {
         $this->internal_allusers = array ();
         $this->user_add ('admin', '12345', UserRecords::USERTYPE_ADMIN);
         $this->internal_user_savedb ();
      }
   }

   /* TODO: This must use SQL wildcards */
   function user_match (string $search_expr) :array {
      $retval = array ();
      foreach ($this->internal_allusers as $rec) {
         if (strstr ($rec[0], "$username")!==false) {
            array_push ($retval);
         }
      }
      return $retval;
   }

   function user_find (string $username) :array {
      foreach ($this->internal_allusers as $rec) {
         if (strcmp ($rec[0], "$username")===0) {
            return $rec;
         }
      }
      return array ('', '', 0, '', '', PHP_INT_MAX);
   }

   function session_find (string $sess_id) :array {
      $now = time ();
      foreach ($this->internal_allusers as $rec) {
         if (strcmp ($rec[1], $sess_id)===0 && $now < $rec[2]) {
            return $rec;
         }
      }
      return array ('', '', 0, '', '', PHP_INT_MAX);
   }

   function user_del (string $username) :void {
      $i = 0;
      for ($i=0; $i<count ($this->internal_allusers); $i++) {
         if ((strcmp ($this->internal_allusers[$i][0], $username))===0) {
            unset ($this->internal_allusers[$i]);
            $this->internal_allusers = array_values ($this->internal_allusers);
            break;
         }
      }
   }

   function user_add (string $username, string $passwd, int $user_type) :void {
      $salt = util_randstring (32);
      $record = array (
         // username, session, expiry, salt, pwhash, user_type
         $username, '', 0, $salt, UserRecords::pwhash ($username, $salt, $passwd), $user_type
      );
      array_push ($this->internal_allusers, $record);
   }

   function user_mod (string $username, string $session, int $expiry,
                      string $salt, string $pwhash, int $user_type) :bool {
      $record = $this->user_find ($username);
      if ($record != null) {
         $record[1] = $session;
         $record[2] = $expiry;
         $record[3] = $salt;
         $record[4] = $pwhash;
         $record[5] = $user_type;
         $this->user_del ($username);
         array_push ($this->internal_allusers, $record);
         $this->internal_user_savedb ();
         return true;
      }
      return false;
   }

   function user_auth (string $username, string $passwd) : string {
      $sess_id = '';
      $record = $this->user_find ($username);
      if ($record == null)
         return '';

      $provided_hash = UserRecords::pwhash ($username, $record[3], $passwd);

      if ($provided_hash === $record[4]) {
         $sess_id = util_randstring (32);
         $sess_expiry = time () + (60 * 60 * 5);
         if (($this->user_mod ($username, $sess_id, $sess_expiry,
                               $record[3], $record[4], $record[5]))===true) {
            return $sess_id;
         }
      }
      return '';
   }
}

$g_userRecords = new UserRecords ();

?>

