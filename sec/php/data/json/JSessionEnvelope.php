<?php
require_once "php/data/json/_util.php";

class JSessionEnvelope {

  public $id;
  public $dateService;
  public $sendTo;
  
  public function __construct($id, $dateService, $sendTo) {
    $this->id = $id;
    $this->dateService = $dateService;
    $this->sendTo = $sendTo;
  }
  
  public function out() {
    return cb(qq("id", $this->id) 
        . C . qq("dos", $this->dateService)
        . C . qq("sendTo", $this->sendTo) 
        );
  }

  public static function constructFromJson($json) {
    $o = jsondecode($json);
    $jSessionEnvelope = new JSessionEnvelope(
        $o->id,
        $o->dos,
        $o->st
        );
    return $jSessionEnvelope;
  }
}
?>