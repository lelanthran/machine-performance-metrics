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
   private $internal_maxid = 0;

   private function internal_user_loaddb () :void {
      $fcontents = file_get_contents ("userdb.ser");
      if ($fcontents === false) {
         $this->internal_allusers = null;
      } else {
         $this->internal_allusers = unserialize ($fcontents);
         foreach ($this->internal_allusers as $rec) {
            if ($rec[0] > $this->internal_maxid)
               $this->internal_maxid = $rec[0];
            $this->internal_maxid++;
         }
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
         $this->internal_maxid = 0;
         $this->user_add ('admin', '12345', UserRecords::USERTYPE_ADMIN);
         $this->internal_user_savedb ();
      }
   }

   function user_list () :array {
      $ret = array ();
      array_push ($ret, ['ID', 'User', 'Expiry', 'UserType']);
      foreach ($this->internal_allusers as $rec) {
         // id, username, session, expiry, salt, pwhash, user_type
         $record = array ($rec[0], $rec[1], $rec[3], util_userTypeString ($rec[6]));
         array_push ($ret, $record);
      }
      return ($ret);
   }

   /* TODO: This must use SQL wildcards */
   function user_match (string $search_expr) :array {
      $retval = array ();
      foreach ($this->internal_allusers as $rec) {
         if (strstr ($rec[1], "$username")!==false) {
            array_push ($retval);
         }
      }
      return $retval;
   }

   function user_find (string $username) :array {
      foreach ($this->internal_allusers as $rec) {
         if (strcmp ($rec[1], "$username")===0) {
            return $rec;
         }
      }
      return array (-1, '', '', 0, '', '', PHP_INT_MAX);
   }

   function session_find (string $sess_id) :array {
      $now = time ();
      foreach ($this->internal_allusers as $rec) {
         if (strcmp ($rec[2], $sess_id)===0 && $now < $rec[3]) {
            $expiry = time () + (60 * 60 * 5);
            if (($this->user_mod ($rec[1], $rec[2], $expiry,
                                  $rec[4], $rec[5], $rec[6]))===true) {
               return $rec;
            } else {
               error_log ("Failed to update the expiry field in user session");
            }
         }
      }
      error_log ("Failed to find session for [$sess_id]");
      return array (-1, '', '', 0, '', '', PHP_INT_MAX);
   }

   function user_del (string $username) :bool {
      $i = 0;
      for ($i=0; $i<count ($this->internal_allusers); $i++) {
         if ((strcmp ($this->internal_allusers[$i][1], $username))===0) {
            unset ($this->internal_allusers[$i]);
            $this->internal_allusers = array_values ($this->internal_allusers);
            return $this->internal_user_savedb ();
         }
      }
      return false;
   }

   function user_add (string $username, string $passwd, int $user_type) :bool {
      $existing = $this->user_find ($username);
      if (strlen ($existing[1]) > 1) {
         return false;
      }
      $salt = util_randstring (32);
      $record = array (
         // id, username, session, expiry, salt, pwhash, user_type
         $this->internal_maxid++, $username, '', 0, $salt,
         UserRecords::pwhash ($username, $salt, $passwd), $user_type
      );
      array_push ($this->internal_allusers, $record);
      $this->internal_user_savedb ();
      return true;
   }

   function user_mod (string $username, string $session, int $expiry,
                      string $salt, string $pwhash, int $user_type) :bool {
      $record = $this->user_find ($username);
      if ($record != null) {
         $record[2] = $session;
         $record[3] = $expiry;
         $record[4] = $salt;
         $record[5] = $pwhash;
         $record[6] = $user_type;
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

      $provided_hash = UserRecords::pwhash ($username, $record[4], $passwd);

      if ($provided_hash === $record[5]) {
         $sess_id = util_randstring (32);
         $sess_expiry = time () + (60 * 60 * 5);
         if (($this->user_mod ($username, $sess_id, $sess_expiry,
                               $record[4], $record[5], $record[6]))===true) {
            return $sess_id;
         }
      }
      return '';
   }
}

$g_userRecords = new UserRecords ();

?>

