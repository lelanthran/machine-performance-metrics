<?php

require_once 'api_common.php';

verify_access (0);

$userlist = $g_userRecords->user_list ();
if (count ($userlist) <= 0) {
   echo util_rsp_error (-1001, 'Failed to load userlist');
   exit (0);
}

echo util_rsp_success_table ($userlist, 0, 0);

?>
