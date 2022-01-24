<?php

require_once 'api_common.php';

verify_access (0);
verify_parameters (['username', 'password', 'usertype']);

if (!($g_userRecords->user_add ($g_object['username'],
                                $g_object['password'],
                                $g_object['usertype']))) {

   echo util_rsp_error (-1002, 'Failed to add user: ' .
                               $g_object['username'] .
                               ':' .
                               $g_object['usertype']);
   exit (0);
}

echo util_rsp_success ();

?>
