<?php

require_once 'api_common.php';

verify_access (0);
verify_parameters (['username', 'password', 'confirmed_password', 'user_type']);

if ((strcmp ($g_object['password'], $g_object['confirmed_password']))!=0) {
   echo util_rsp_error (-1003, "Passwords do not match");
   exit (0);
}

$record = $g_userRecords->user_find ($g_object['username']);
$uid = $record[0];
$username = $record[1];
$session = '';
$expiry = 0;
$salt = util_randstring (32);
$password = $g_object['password'];
$pwhash = UserRecords::pwhash ($username, $salt, $password);
$usertype = $g_object['user_type'];

if ($uid <= 0) {
   echo util_rsp_error (-1004, "User doesn't exist: " . $g_object['username']);
   exit (0);
}

if (!($g_userRecords->user_mod ($username, $session, $expiry, $salt, $pwhash, $usertype))) {
   echo util_rsp_error (-1002, "Failed to modify user: $user");
   exit (0);
}

echo util_rsp_success ();

?>

