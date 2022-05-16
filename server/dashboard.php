<?php
require_once 'session.php';
?>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="mpm.css">
  <link rel="stylesheet" href="defaultLiveTableClasses.css">
  <script type="text/javascript" src="mpm.js"></script>
  <script type="text/javascript" src="LiveTable.js"></script>
  <script>
  function setFirstTab () {
    var adminTab    = document.getElementById('btnAdministration');
    var operationsTab = document.getElementById('btnOperations');
    var reportsTab  = document.getElementById('btnReports');
    var chartsTab   = document.getElementById('btnCharts');
    var queriesTab  = document.getElementById('btnQueries');
    if (reportsTab != null) {
      reportsTab.click ();
      return;
    }
    if (chartsTab != null) {
      chartsTab.click ();
      return;
    }
    if (queriesTab != null) {
      queriesTab.click ();
      return;
    }
    if (operationsTab != null) {
      operationsTab.click ();
      return;
    }
    if (adminTab != null) {
      adminTab.click ();
      return;
    }
  }
  </script>
</head>
<body onload="setFirstTab();">

  <h3>MPM Dashboard</h3>

  <div id="busyMessage" class="overlay">

    <!-- Overlay content -->
    <div class="overlay-content">
      <p id=busyMessageContent > </p>
      <p id=busyMessageContentDots > </p>
    </div>

  </div>
  <div class="tab">
<?php
$tabcfg = [
  array (2, 'Reports', 'Reports', ''),
  array (2, 'Charts', 'Charts', ''),
  array (2, 'Queries', 'Queries', ''),
  array (1, 'Operations', 'Operations', ''),
  array (0, 'Administration', 'Administration', ''),
];

foreach ($tabcfg as $tab) {
  $level  = $tab[0];
  $id     = $tab[1];
  $name   = $tab[2];
  if ($g_sess_user_type <= $level) {
    echo "    <button id=btn$id class='tablinks' onclick=\"openTab(event, '$id')\">$name</button>\n";
  }
}
echo "\n<script>\nconst userLevel = $g_sess_user_type;\n</script>\n";
?>
    <span style="display: flex; justify-content: flex-end;">
      <button class=tablinks onclick='window.location.href="logout.php";'>Logout</button>
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

if (userLevel == 0) {
  async function getUserList () {
    var retval = await callAPI_Verbose ('Getting userlist', 'admin_userlist.php', 'POST');
    return retval;
  }

  async function updateUserRecord (row) {
    var obj = { "userid": row[0], "username": row[1], "usertype": row[3] };

    console.log (obj);
    var retval = await callAPI_Verbose ('Saving user ' + row[1], 'admin_usermod.php', 'POST', obj);
    return true;
  }

  async function deleteUserRecord (row) {
    var obj = { "username": row[1] };
    var retval = await callAPI_Verbose ('Deleting ' + row[1], 'admin_userdel.php', 'POST', obj);
    if (retval.error_code!=0) {
      alert ("Failed to delete record " + obj.username);
      return false;
    }
    return true;
  }

  var userAdminTable = new LiveTable (getUserList, updateUserRecord, deleteUserRecord);
  userAdminTable.recUpdateFunc = updateUserRecord;
  userAdminTable.recRemoveFunc = deleteUserRecord;
  userAdminTable.setFieldSpec (0, 'ID');
  userAdminTable.setFieldSpec (2, 'READONLY');
  userAdminTable.setFieldSpec (3, 'ENUM:Administrator:Operator:Standard');
  userAdminTable.recordInlineEditFunc = function (row) {
    window.open ('usermod.php?username=' + row[1] + "&userid=" + row[0]);
    return true;
  }
  userAdminTable.recordInlineDeleteFunc = function (row) {
    return deleteUserRecord (row);
  }

  userAdminTable.parentNodeId  = 'Administration';
  document.getElementById ('btnAdministration').renderFunc = function () {
    userAdminTable.render ();
  }
}

</script>

  <div id="Operations" class="tabcontent">
    <h3>Operations</h3>
    <p><button onclick='window.open ("credsadd.php")'>Create new Login</button>
<script>

if (userLevel >= 0 && userLevel <= 1) {
  async function getMachineList () {
    var retval = await callAPI_Verbose ('Getting machine list', 'ops_machinelist.php', 'POST');
    return retval;
  }

  var machineOpsTable = new LiveTable (getMachineList);
  machineOpsTable.setFieldSpec (0, 'LINK:credsmod.php');
  machineOpsTable.setFieldSpec (1, 'LINK:machineview.php');
  machineOpsTable.setFieldSpec (2, 'READONLY');

  machineOpsTable.parentNodeId  = 'Operations';


  document.getElementById ('btnOperations').renderFunc = function () {
    machineOpsTable.render ();
  }
}

</script>

  </div>

  <div id="Reports" class="tabcontent">
    <h3>Reports</h3>
    <p>Reports stuff goes here</p>
    <p>TODO: Display a list of actions (open in new window):
      <ul>
        <li>Table of alerts + link to full table.</li>
        <li>Search for report</li>
        <li>Open report-creator</li>
        <li>Display 4 of the trending reports for all users</li>
        <li>Display quicklinks based on what this user used most</li>
      </ul>
    </p>
  </div>

  <div id="Charts" class="tabcontent">
    <h3>Charts</h3>
    <p>Charts stuff goes here</p>
    <p>TODO: Display a list of actions (open in new window):
      <ul>
        <li>Search for Charts</li>
        <li>Open chart-creator</li>
        <li>Display 4 of the trending Charts for all users</li>
        <li>Display quicklinks based on what this user used most</li>
      </ul>
    </p>
  </div>

  <div id="Queries" class="tabcontent">
    <h3>Queries</h3>
    <p>Queries stuff goes here</p>
    <p>TODO:
      <ul>
        <li>Search for Queries</li>
        <li>Query-creator:</li>
          <ul>
            <li>Text area for SQL</li>
            <li>Div for results table</li>
            <li>button to save results</li>
            <li>button to save query </li>
          </ul>
        <li>Display 4 of the trending Queries for all users</li>
        <li>Display quicklinks based on what this user used most</li>
      </ul>
    </p>
    <div id=query_lhs>
    </div>
    <div id=query_rhs>
    </div>
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
