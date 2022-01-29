<?php
require_once 'session.php';
?>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="mpm.css">
  <script type="text/javascript" src="mpm.js"></script>
<?php
$g_user_record = null;

if (isset ($_REQUEST['username'])) {
   $g_user_record = $g_userRecords->user_find ($_REQUEST['username']);
}

if (isset ($_REQUEST['userid'])) {
   $g_user_record = $g_userRecords->user_find ($_REQUEST['userid']);
}

echo "<script>\n";
echo "const userId = " . $g_user_record[0] . ";\n";
echo "const userName = '" . $g_user_record[1] . "';\n";
echo "const userType = " . $g_user_record[6] . ";\n";
echo "</script>\n";
?>
</head>
   <body onload='populateFields()';>
  <div id="busyMessage" class="overlay">

    <!-- Button to close the overlay navigation -->
    <a href="javascript:void(0)" class="closebtn" onclick="updateBusyMessage('Still busy')">Update busy message</a>
    <a href="javascript:void(0)" class="closebtn" onclick="endBusyMessage()">End busy message</a>

    <!-- Overlay content -->
    <div class="overlay-content">
      <p id=busyMessageContent > </p>
      <p id=busyMessageContentDots > </p>
    </div>

  </div>
   <h1> Edit existing user </h1>
   <table>
      <tr>
         <td>Email:</td><td><input id=username placeholder='user@domain.com' /></td>
      </tr>
      <tr>
         <td>UserType:</td><td><select id=usertype />
                                 <option value=Administrator>Administrator</option>
                                 <option value=Operator>Operator</option>
                                 <option value=Standard selected=true >Standard user</option>
                                </select></td>
      </tr>
      <tr rowspan=2> <td><p/> </td></td></tr>
      <tr>
         <td colspan=2>
            <input type=checkbox id='checkbox_pwchange' onclick='toggleNewPassword()'/>
            Set new password</td>
      </tr>
      <tr>
         <td id=password_label class=disabled_label>New Password:</td>
         <td><input id=password placeholder='password' type=password disabled=true/></td>
      </tr>
      <tr>
         <td id=cpassword_label class=disabled_label>Confirm password:</td>
         <td><input id=cpassword placeholder='password' type=password disabled=true/></td>
      </tr>
      <tr>
         <td colspan=2><button onclick='editUser ();'>Save changes</button></td>
      </tr>
   </table>
   <div>
      <p id=result_message> </p>
   </div>
<script>
function populateFields () {
   var email = document.getElementById ('username');
   var usertype = document.getElementById ('usertype');
   email.value = userName;
   usertype.options[userType].selected = true;
}

function toggleNewPassword () {
   var pwd = document.getElementById ('password');
   var cpwd = document.getElementById ('cpassword');
   var pwdlabel = document.getElementById ('password_label');
   var cpwdlabel = document.getElementById ('cpassword_label');
   if (document.getElementById ('checkbox_pwchange').checked) {
      pwd.disabled = false;
      cpwd.disabled = false;
      pwdlabel.classList = "";
      cpwdlabel.classList = "";
   } else {
      pwd.disabled = true;
      cpwd.disabled = true;
      pwdlabel.classList = "disabled_label";
      cpwdlabel.classList = "disabled_label";
   }
}

async function editUser () {
   var user = document.getElementById ('username').value;
   var password = document.getElementById ('password').value;
   var cpassword = document.getElementById ('cpassword').value;
   var usertype = document.getElementById ('usertype').value;

   if (document.getElementById ('checkbox_pwchange').checked == false) {
      password = '';
      cpassword = '';
   }

   var obj = {
   "userid":               userId,
   "username":             user,
   "password":             password,
   "confirmed_password":   cpassword,
   "usertype":             usertype
   };

   var result = await callAPI_Verbose ('Saving changes', 'admin_usermod.php', 'POST', obj);

   document.getElementById ('result_message').innerHTML = result.error_message;
   if (result.error_code == 0) {
      document.getElementById ('username').value = "";
      document.getElementById ('password').value = "";
      document.getElementById ('cpassword').value = "";
      document.getElementById ('usertype').options[0].selected;
      document.getElementById ('result_message').innerHTML = 'Saved changes to ' + usertype + ' ' + user;
   }
}

</script>
   </body>
</html>

