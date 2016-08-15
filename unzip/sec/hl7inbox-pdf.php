<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/rec/sql/_Hl7InboxRec.php';
//
class Hl7InboxPdf extends Hl7InboxRec {
  //
  public $hl7InboxId;
  public $userGroupId;
  public $pdf;
  //
  static function fetch($id, $ugid) {
    $c = new static();
    $c->hl7InboxId = $id;
    $c->userGroupId = $ugid;
    return static::fetchOneBy($c); 
  }
}
//
if (isset($_GET['id'])) {
  $rec = Hl7InboxPdf::fetch($_GET['id'], $login->userGroupId);
  if ($rec) {
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: application/pdf");
    echo $rec->pdf;
  }
}
