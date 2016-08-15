<?php
class Anchor {
  
  public $href;
  public $text;
  
  public function __construct($href, $text) {
    $this->href = $href;
    $this->text = $text;
  }
  
  public function html($className = "") {
    return "<a href='" . $this->href . "' class='" . $className . "'>" . $this->text . "</a>";
  }
}
?>