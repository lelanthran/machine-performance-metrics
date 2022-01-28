<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="mpm.css">
  <script type="text/javascript" src="mpm.js"></script>
</head>
   <body>
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
   <h1> Add new user </h1>
   <table>
      <tr>
         <td>Email:</td><td><input id=username placeholder='user@domain.com' /></td>
      </tr>
      <tr>
         <td>Password:</td><td><input id=password placeholder='password' type=password /></td>
      </tr>
      <tr>
         <td>Password:</td><td><input id=password placeholder='cpassword' type=password /></td>
      </tr>
      <tr>
         <td>UserType:</td><td><select id=usertype />
                                 <option value=Administrator>Administrator</option>
                                 <option value=Operator>Operator</option>
                                 <option value=Standard selected=true >Standard user</option>
                                </select></td>
      </tr>
      <tr>
         <td colspan=2><button onclick='createUser ();'>Create user</button></td>
      </tr>
   </table>
   <div>
      <p id=result_message> </p>
   </div>
<script>
async function createUser () {
   var user = document.getElementById ('username').value;
   var password = document.getElementById ('password').value;
   var usertype = document.getElementById ('usertype').value;

   var obj = {
   "username":    user,
   "password":    password,
   "cpassword":   password,
   "usertype":    usertype
   };

   var result = await callAPI_Verbose ('Creating user', 'admin_useradd.php', 'POST', obj);

   document.getElementById ('result_message').innerHTML = result.error_message;
   if (result.error_code == 0) {
      document.getElementById ('result_message').innerHTML = 'Added ' + usertype + ' ' + user;
   }
}

</script>
   </body>
</html>
