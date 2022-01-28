<?php
require_once 'UserRecords.php';

setcookie ('mpm_sessionid', $sessid);
header ('Location: login.php');

?>

