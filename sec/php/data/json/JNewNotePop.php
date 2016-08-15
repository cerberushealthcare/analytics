<?php
require_once "php/data/json/_util.php";

// UI elements for new note pop
class JNewNotePop {

  private $templates;  // JHtmlCombo[]
  private $presets;  // {tid:JHtmlCombo[]}
  private $standards;  // {tid:JSessionStub}
  private $sendTos;  // JHtmlCombo[]
  
  public function __construct($templates, $presets, $standards, $sendTos) {
    $this->templates = $templates;
    $this->presets = $presets;
    $this->standards = $standards;
    $this->sendTos = $sendTos;
  }
  public function out() {
    return cb(qqj("templates", $this->templates)
        . C . qqj("presets", $this->presets)
        . C . qqaa("standards", $this->standards)
        . C . qqj("sendTos", $this->sendTos)
    );
  }  
}
?>