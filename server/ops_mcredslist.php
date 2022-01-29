<?php

require_once 'api_common.php';

verify_access (1);

$mcredslist = util_agent_creds_list ();
if (count ($mcredslist) <= 0) {
   echo util_rsp_error (-2002, 'Failed to load mcredslist');
   exit (0);
}

echo util_rsp_success_table ($mcredslist, 0, 0);

?>
