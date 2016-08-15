<?php
require_once "php/dao/_util.php";
require_once "php/data/json/_util.php";

class JDocView {

  public $id;
  public $dos;
  public $cid;
  public $cname;
  public $html;
  
  public function __construct($session) {
    $this->id = $session->id;
    $this->dos = $session->dos;
    $this->cid = $session->cid;
    $this->cname = $session->cname;
    if ($session->closed == 2) {
      $this->html = $session->actions;
    } else {
      $this->html = $session->html;
    }
  }
    
  public function out() {
    return cb(qq("id", $this->id)
        . C . qq("dos", $this->dos)
        . C . qq("cid", $this->cid)
        . C . qq("cname", $this->cname)
        . C . qq("html", $this->html)
        );
   }
}
?>