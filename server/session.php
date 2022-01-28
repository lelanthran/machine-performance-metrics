<?php
require_once 'UserRecords.php';

if (!isset ($_COOKIE['mpm_sessionid'])) {
  header ('Location: login.php');
}

$session_record = $g_userRecords->session_find ($_COOKIE['mpm_sessionid']);

$g_sess_user_id = $session_record[0];
$g_sess_user_name = $session_record[1];
$g_sess_user_type = $session_record[6];

if (strlen ($g_sess_user_name) <= 1) {
  header ('Location: login.php');
}

?>
