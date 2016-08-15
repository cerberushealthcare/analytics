<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/rec/group-folder/GroupFolder_Cqm.php';
//
if (isset($_GET['id'])) {
  $folder = GroupFolder_Cqm::open();
  $folder->download($_GET['id']);
}
