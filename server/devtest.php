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
   // $body = file_get_contents ("php://input");
   // $object = json_decode ($body, true);
   $object = array (
   "MPM_USER"              => "lelanthran",
   "MPM_PASSWORD"          => "12345",
   "LOCAL_USER"            => "lelanthran",
   "KERNEL"                => "Linux lelanthran-desktop 5.4.0-92-generic #103-Ubuntu SMP Fri Nov 26 16:13:00 UTC 2021 x86_64 x86_64 x86_64 GNU/Linux",
   "TSTAMP"                => "2022-01-16T11:09:53+02:00",
   "HOSTNAME"              => "lelanthran-desktop",
   "MEMORY_TOTAL"          => "16383064",
   "MEMORY_USED"           => "4589080",
   "MEMORY_FREE"           => "16383064",
   "SWAP_TOTAL"            => "2097148",
   "SWAP_USED"             => "130048",
   "SWAP_FREE"             => "2097148",
   "LOADAVG"               => "0.31",
   "ARCH"                  => "x86_64",
   "CPU_COUNT"             => "8",
   "SOCKETS_OPEN"          => "1154",
   "IFSTATS_COLS"          => "eno0 wlx386b1cd61f5c virbr0 Total ",
   "IFSTATS_VALUES"        => "0.00 0.00 69.29 2.17 0.00 0.00 69.29 2.17",
   "DISKIO_UNITS"          => "MB/s",
   "DISKIO"                => "all 7.93 0.09 0.34 0.00 21319 81393 0",
   "DISKIO_TPS"            => "7.93",
   "DISKIO_READS"          => "0.09",
   "DISKIO_WRITES"         => "0.34",
   "DISKIO_DISCARDS"       => "0.00",
   "DISKIO_READ"           => "21319",
   "DISKIO_WRITE"          => "81393",
   "DISKIO_DISCARD"        => "0",
   "FS_COUNT"              => "3",
   "FS_DATA"               => "/dev/sda2,524272,4,524268,1%,/boot/efi$/dev/sda5,1424825072,215491696,1136886400,16%,/$/dev/sdb2,1952506704,1226793928,725712776,63%,/mnt/sdb2",
   "END"                   => "ignore"
   );

   return 'INCOMPLETE <br> ' . print_r ($object, true);
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
