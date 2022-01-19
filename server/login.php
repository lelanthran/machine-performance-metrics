<?php
require_once 'UserRecords.php';

$error_msg = '';

if (isset ($_POST['username'])) {
   $sessid = $g_userRecords->user_auth ($_POST['username'], $_POST['password']);
   setcookie ('mpm_sessionid', $sessid);
   if (strlen ($sessid)<=4) {
      $error_msg = 'Login failure';
   } else {
      header ('Location: dashboard.php');
   }
}

?>

<html>
   <body>

<?php
echo "<div class=login_error><p>$error_msg</p><div>";
?>

   <form action=login.php method=POST>
      <table class=login_table>
         <tr>
            <td>Username</td><td><input name=username width=15 /></td>
         </tr>
         <tr>
            <td>Password</td><td><input name=password type=password width=15 /></td>
         </tr>
         <tr>
            <td colspan=2><br></td>
         </tr>
         <tr>
            <td colspan=2><center><input type=submit value='Login'/></center></td>
         </tr>
      </table>
   </form>
   </body>
</html>

