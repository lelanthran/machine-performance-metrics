<?php
require_once 'UserRecords.php';

$session_record = $g_userRecords->session_find ($_COOKIE['mpm_sessionid']);
$g_sess_user_name = $session_record[1];
$g_sess_user_type = $session_record[6];

if (strlen ($g_sess_user_name) <= 2) {
  header ('Location: login.php');
}

?>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="mpm.css">
  <link rel="stylesheet" href="defaultLiveTableClasses.css">
  <script type="text/javascript" src="mpm.js"></script>
  <script type="text/javascript" src="LiveTable.js"></script>
</head>
<body onload="document.getElementById('btnAdministration').click()">

  <h3>MPM Dashboard</h3>

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
  <div class="tab">
  <button onclick='startBusyMessage ("Busy")'> start busy message</button>
<?php
$tabcfg = [
  array (0, 'Administration', 'Administration', ''),
  array (1, 'Operations', 'Operations', ''),
  array (2, 'Reports', 'Reports', ''),
];

foreach ($tabcfg as $tab) {
  $level  = $tab[0];
  $id     = $tab[1];
  $name   = $tab[2];
  if ($g_sess_user_type <= $level) {
    echo "    <button id=btn$id class='tablinks' onclick=\"openTab(event, '$id')\">$name</button>\n";
  }
}
?>
    <span style="display: flex; justify-content: flex-end;">
<?php
  echo "      <button class='tablinks' onclick=\"openTab(event, 'Settings')\">$g_sess_user_name</button>\n";
?>
    </span>
  </div>

  <div id="Administration" class="tabcontent">
    <h3>Administration</h3>
    <p><button onclick='window.open ("useradd.php")'>Create new user</button>
    </p>
  </div>

<script>

async function getUserList () {
  var retval = await callAPI ('Getting userlist', 'admin_userlist.php', 'POST');
  return retval;
}

function updateUserRecord (row) {
  console.log (`Updating ${row}`);
  return false;
}

function deleteUserRecord (row) {
  console.log (`deleting ${row}`);
}

var userAdminTable = new LiveTable (getUserList, updateUserRecord, deleteUserRecord);
userAdminTable.setFieldSpec (0, 'ID');
userAdminTable.setFieldSpec (2, 'CHECKBOX');
userAdminTable.setFieldSpec (3, 'ENUM:Administrator:Operator:Standard');
userAdminTable.recordInlineEditFunc = function (row) {
  console.log ("Inline editing function: " + row);
  return true;
}
userAdminTable.recordInlineDeleteFunc = function (row) {
  return deleteUserRecord (row);
}

// userAdminTable.tableClassList = "default_tableClass";
// userAdminTable.theadClassList = "default_theadClass";
// userAdminTable.tbodyClassList = "default_tbodyClass";
// userAdminTable.trOddClassList = "default_trOddClass";
// userAdminTable.trEvenClassList = "default_trEvenClass";
// userAdminTable.uneditableRowClassList = "default_uneditableRowClass";
// userAdminTable.editableRowClassList = "default_editableRowClass";
// userAdminTable.sortBtnClassList = "default_sortBtn";
// userAdminTable.changedRowClassList = "default_changedRowClass";

userAdminTable.parentNodeId  = 'Administration';
userAdminTable.render ();

</script>

  <div id="Operations" class="tabcontent">
    <h3>Operations</h3>
    <p>Operations stuff goes here.</p> 
    <p>TODO: Display a list of actions (open in new window):
      <ul>
        <li>Machine filter</li>
        <li>Add new machine</li>
      </ul>
    </p>
  </div>

  <div id="Reports" class="tabcontent">
    <h3>Reports</h3>
    <p>Reports stuff goes here</p>
    <p>TODO: Display a list of actions (open in new window):
      <ul>
        <li>Table of alerts + link to full table.</li>
        <li>Search for report</li>
        <li>Open query-tool</li>
        <li>Display 4 of the trending reports for all users</li>
        <li>Display quicklinks based on what this user used most</li>
      </ul>
    </p>
  </div>

  <div id="Settings" class="tabcontent">
    <h3>Settings</h3>
    <p>Settings stuff goes here</p>
    <p>TODO: Display a list of actions (open in new window):
      <ul>
        <li>Change password</li>
      </ul>
    </p>
  </div>

</body>
</html>
