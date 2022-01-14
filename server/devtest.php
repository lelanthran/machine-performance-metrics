<?php

require 'user.php';

$output = 'Clean page';

function get_cookie ($name) {
   $value = $_COOKIE[$name];
   if ($value === null) {
      $value = '';
   }
   return $value;
}

function perform_login_request ($user, $pass) {
   global $g_userRecords;
   $sess_id = $g_userRecords->user_auth ($user, $pass);
   setcookie ('mpm_sessionid', $sess_id);
   return "SessionID: $sess_id";
}

function reload_session () {
   global $g_userRecords;
   $record = $g_userRecords->session_find (get_cookie ('mpm_sessionid'));
   $output = 'full session record:<br><br>' . print_r ($record, true);
   return $output;
}

switch ($_REQUEST['action']) {
   case 'login_request_valid':
      $output = perform_login_request ('admin', '12345');
      break;

   case 'login_request_invalid':
      $output = perform_login_request ('admin', '2345');
      break;

   case 'load_session':
      $output = reload_session ();
      break;

   default:
   case 'nothing':   $output = "No action specified";
                     break;
}

?>
<html>
   <body>
   <ul>
      <li><a href=devtest.php?action=login_request_valid>Valid login</a></li>
      <li><a href=devtest.php?action=login_request_invalid>Invalid login</a></li>
      <li><a href=devtest.php?action=load_session>Load session</a></li>
   </ul>
      <tt>
<?php
echo $output;
?>
      </tt>
   </body>
</html>
