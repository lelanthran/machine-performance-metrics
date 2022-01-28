<?php
require_once 'UserRecords.php';

// ///////////////////////////////////////////////////////////////////////

if (!(isset ($_COOKIE['mpm_sessionid']))) {
   echo util_rsp_error (-1, 'Not logged in');
   exit (0);
}

// ///////////////////////////////////////////////////////////////////////

$session_record = $g_userRecords->session_find ($_COOKIE['mpm_sessionid']);
$g_sess_user_name = $session_record[1];
$g_sess_user_type = $session_record[6];

function verify_access (int $level) :void {
   global $g_sess_user_name;
   global $g_sess_user_type;

   if (!isset ($g_sess_user_type) || !isset ($g_sess_user_name)
      || $g_sess_user_type < 0 || $g_sess_user_type > $level) {
      echo util_rsp_error (-2, "Access denied for user $g_sess_user_name");
      exit (0);
   }

}

// ///////////////////////////////////////////////////////////////////////

$g_body = file_get_contents("php://input");
$g_object = json_decode($g_body, true);

function verify_parameters (array $params) :void {
   global $g_object;
   foreach ($params as $param) {
      if (!(isset ($g_object[$param]))) {
         echo util_rsp_error (-3, "Missing parameter: $param");
         exit (0);
      }
   }
}

function get_optional_param (string $pname) :string {
   global $g_object;
   if ((isset ($g_object[$pname]))) {
      return $g_object[$pname];
   }
   return "";
}

header ("Content-type: application/json");

?>
