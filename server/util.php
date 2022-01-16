<?php
declare (strict_types=1);

require_once 'UserRecords.php';

function util_randstring (int $nbytes) :string {
   return bin2hex (random_bytes ($nbytes));
}

function bool_to_string (bool $val) :string {
   return $val===false ? 'FALSE' : 'TRUE';
}

function util_agent_remove (string $agent_name) :bool {
   global $g_dbconn_rw;

   $result = $g_dbconn_rw->query ('DELETE FROM tbl_user WHERE c_user=$1', array ($agent_name));
   return DBConnection::querySucceeded ($result);
}

function util_agent_add (string $agent_name, string $agent_password) :int {
   global $g_dbconn_rw;

   $salt = util_randstring (32);
   $hash = UserRecords::pwhash ($agent_name, $salt, $agent_password);

   $id = $g_dbconn_rw->query
      ('INSERT INTO tbl_user (c_user, c_salt, c_hash) VALUES ($1, $2, $3) RETURNING id;',
         array ($agent_name, $salt, $hash));

   if (DBConnection::querySucceeded ($id) === true) {
      return (int)$id[1][0];
   } else {
      return -1;
   }
}

?>
