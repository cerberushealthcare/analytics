<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/rec/group-folder/GroupFolder_SessionImages.php';
//
if (isset($_GET['id'])) {
  $folder = GroupFolder_SessionImages::open();
  $folder->output($_GET['id']);
}