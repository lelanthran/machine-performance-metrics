<?php

require_once 'api_common.php';

verify_access (0);
verify_parameters (['username']);

if (!($g_userRecords->user_del ($g_object['username']))) {
   echo util_rsp_error (-1005, 'Failed to remove user: ' .  $g_object['username']);
   exit (0);
}

echo util_rsp_success ();

?>

