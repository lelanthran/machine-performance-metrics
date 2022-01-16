<?php

require_once 'UserRecords.php';
require_once 'DBConnection.php';
require_once 'mpm_server.creds';

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

function insert_random_metric () {
   // TODO: Incomplete
   return 'inserting';
}

switch ($_REQUEST['action']) {
   case 'login_request_valid':
      $output = perform_login_request ('admin', '12345');
      break;

   case 'login_request_invalid':
      $output = perform_login_request ('admin', '2345');
      break;

   case 'remove_user':
      $output = bool_to_string (util_agent_remove ('agent1'));
      break;

   case 'add_user':
      $output = util_agent_add ('agent1', 'pass1');
      break;

   case 'load_session':
      $output = reload_session ();
      break;

   case 'basic_pgro':
      $output = print_r ($g_dbconn_ro->query
                           ('Select 25 * $1 as answer', array ('2')), true);
      break;

   case 'basic_pgrw':
      $output = print_r ($g_dbconn_rw->query
                           ('Select count(*) from tbl_metrics where id > $1', array ('0')), true);
      break;

   case 'insert_metric':
      $output = insert_random_metric ();
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
      <li><a href=devtest.php?action=remove_user>Remove Machine User/pass</a></li>
      <li><a href=devtest.php?action=add_user>Add Machine User/pass</a></li>
      <li><a href=devtest.php?action=load_session>Load session</a></li>
      <li><a href=devtest.php?action=basic_pgro>Test RO db statement</a></li>
      <li><a href=devtest.php?action=basic_pgrw>Test RW db statement</a></li>
      <li><a href=devtest.php?action=insert_metric>Insert Random metric</a></li>
   </ul>
      <tt>
<?php
echo $output;
?>
      </tt>
   </body>
</html>
