<?php
require_once 'server.php';
require_once 'php/data/rec/sql/Snomeds.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    //
    case 'search':
      $text = geta($_GET, 'id');
      if (is_numeric($text))
        $recs = Snomeds::searchCid($text);
      else
        $recs = Snomeds::searchDesc($text);
      AjaxResponse::out($action, $recs);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  