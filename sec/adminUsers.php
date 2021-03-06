<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Users_Admin.php';
//
LoginSession::verify_forServer()->requires($login->admin);
?>
<html>
  <head>
    <style>
BODY {
  font-family:Calibri;
  font-size:10pt;
}
H2, H3 {
  margin-bottom:0.3em;
}
TABLE {
  font-family:Calibri;
  font-size:10pt;
  margin-bottom:1em;
  border-collapse:collapse;
}
TH {
  text-transform:uppercase;
  background-color:#DBD7CD;
  padding:2px 4px;
}
TD {
  vertical-align:top;
  border:1px solid #c0c0c0;
  padding:2px 4px;
  white-space:nowrap;
}
A {
  text-decoration:none;
  border-bottom:1px dotted;
}
A:hover {
  color:red;
  border-bottom:1px solid red;
}
A.loginsByUid {
  font-family:Consolas;
  font-size:10pt;
}
TABLE.v {
  width:100%;
}
TABLE.v TH {
  text-align:right;
  width:10%;
}
TABLE.v TD {
  width:90%;
  border:none;
}
    </style>
  </head>
  <body>
<?php 
$action = $_GET['a'];
$value = isset($_GET['v']) ? $_GET['v'] : null;
switch ($action) {
  case 'searchName':
    echo "<h2>Searching names for '$value'</h2>";
    $recs = Users_Admin::searchByName($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'searchUid':
    echo "<h2>Searching IDs for '$value'</h2>";
    $recs = Users_Admin::searchByUid($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'usersByBillCode':
    echo "<h2>Active Subscribers by Bill Code '$value'</h2>";
    $recs = Users_Admin::getActiveByBillCode($value);
    echo Users_Admin::asHtmlTable($recs);
    echo '<h3>All Bill Codes</h3>';
    echo Users_Admin::getBillCodeAnchors();
    break;
  case 'usersActiveSubs':
    $s = UserAdmin::$SUBSCRIPTIONS[$value];
    echo "<h2>Active Subscribers '$s'</h2>";
    $recs = Users_Admin::getActiveSubscribers($value);
    echo Users_Admin::asHtmlTable($recs);
    echo '<h3>All Subscriptions</h3>';
    echo Users_Admin::getSubscriptionAnchors();
    break;
  case 'usersActiveTrials':
    echo "<h2>Active Trials</h2>";
    $recs = Users_Admin::getActiveTrials();
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'loginsByIp':
    echo "<h2>Logins by IP $value</h2>";
    $recs = Users_Admin::getLoginsByIp($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'loginsByUid':
    echo "<h2>Logins by $value</h2>";
    $recs = Users_Admin::getLoginsByUid($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'loginsByDate':
    echo "<h2>Logins on $value</h2>";
    $recs = Users_Admin::getLoginsByDate($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'counts':
    echo "<h2>Trial Counts by Date</h2>";
    $recs = Users_Admin::getCreatedCounts();
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'usersByCreateDate':
    echo "<h2>Users Created on $value</h2>";
    $recs = Users_Admin::getUsersByCreateDate($value);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case 'userByUid':
    $user = Users_Admin::getUserByUid($value);
    $name = ($user) ? $user->name : $value;
    echo "<h2>User '$name'</h2>";
    echo Users_Admin::asHtmlVTable($user);
    if ($user) {
//      echo "<h3>Usage</h3>";
//      $rec = Usage::fetch($user);
//      echo Users_Admin::asHtmlVTable($rec);
      echo "<h3>Billing</h3>";
      $rec = BillInfoStub::fetch($user->userId);
      echo Users_Admin::asHtmlVTable($rec);
    }
    break;
  case 'usersByUgid':
    $ug = UserGroupAdmin::fetch($value);
    $name = ($ug) ? $ug->name : $value;
    $recs = Users_Admin::getUsersByUgid($value);
    echo "<h2>Users in Group '$name'</h2>";
    echo Users_Admin::asHtmlTable($recs);
    break;
}
?>
  </body>
</html>
