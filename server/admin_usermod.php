<?php

require_once 'api_common.php';

verify_access (0);
verify_parameters (['username', 'usertype']);
$password = $g_object['password'];
$cpassword = $g_object['confirmed_password'];

error_log ($password);
error_log ($cpassword);

if (strlen ($password) > 0) {
   if ((strcmp ($password, $cpassword))!=0) {
      echo util_rsp_error (-1003, "Passwords do not match");
      exit (0);
   }
}

$userspec = $g_object['userid'];
if ($userspec == null) {
   $userspec = strval ($g_object['username']);
}

$record = $g_userRecords->user_find ($userspec);
$uid = $record[0];
$username = $record[1];
if (isset ($g_object['username'])) {
   $username = $g_object['username'];
}

$session = '';
$expiry = 0;
$usertype = util_get_usertype_enum ($g_object['usertype']);

if (strlen ($password) > 0) {
   $salt = util_randstring (32);
   $pwhash = UserRecords::pwhash ($username, $salt, $password);
} else {
   $salt = $record[4];
   $pwhash = $record[5];
}

if ($uid <= 0) {
   echo util_rsp_error (-1004, "User doesn't exist: " . $g_object['username']);
   exit (0);
}

if (!($g_userRecords->user_mod ($uid, $username, $session, $expiry, $salt, $pwhash, $usertype))) {
   echo util_rsp_error (-1002, "Failed to modify user: $user");
   exit (0);
}

echo util_rsp_success ();

?>

