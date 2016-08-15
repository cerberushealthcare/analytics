<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/rec/group-folder/GroupFolder_Faces.php';
//
if (isset($_GET['id'])) {
  $folder = GroupFolder_Faces::open();
  $folder->output($_GET['id']);
}