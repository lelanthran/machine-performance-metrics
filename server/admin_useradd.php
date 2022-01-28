<?php

require_once 'api_common.php';

verify_access (0);
verify_parameters (['username', 'cpassword', 'password', 'usertype']);

if ((strcmp ($g_object['cpassword'], $g_object['password']))!==0) {
   echo util_rsp_error (-1008, "Passwords don't match: " .
                               $g_object['username'] .
                               ':' .
                               $g_object['usertype']);
}

$usertype = -1;

switch ($g_object['usertype']) {
case 'Administrator': $usertype = 0; break;
case 'Operator':      $usertype = 1; break;
case 'Standard':      $usertype = 2; break;
}


if (!($g_userRecords->user_add ($g_object['username'],
                                $g_object['password'],
                                $usertype))) {

   echo util_rsp_error (-1002, 'Failed to add user: ' .
                               $g_object['username'] .
                               ':' .
                               $g_object['usertype']);
   exit (0);
}

echo util_rsp_success ();

?>
