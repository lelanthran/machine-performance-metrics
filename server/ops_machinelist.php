<?php

require_once 'api_common.php';

verify_access (1);

$machinelist = util_agent_list ();
if (count ($machinelist) <= 0) {
   echo util_rsp_error (-2001, 'Failed to load machinelist');
   exit (0);
}

echo util_rsp_success_table ($machinelist, 0, 0);

?>
