<?php

require_once 'api_common.php';

verify_access (0);

$userlist = $g_userRecords->user_list ();
if (count ($userlist) <= 0) {
   echo util_rsp_error (-1001, 'Failed to load userlist');
   exit (0);
}

$userlist[0][2] = 'Session expiry';
$i = 0;
$now = time ();
for ($i = 1; $i<count($userlist); $i++) {
   if ($userlist[$i][2] <= $now) {
      $userlist[$i][2] = 'Expired';
   } else {
      $userlist[$i][2] = date (DATE_RFC822, $userlist[$i][2]);
   }
}

echo util_rsp_success_table ($userlist, 0, 0);

?>
