<?php
class Military {
  
  public $value;  // e.g. 1400
  public $hour;  // e.g. 14
  public $min;  // e.g. 0
  
  // Helpers
  public $standardHour;  // e.g. 2
  public $elapsed;  // elapsed time since midnight in minutes

  public function __construct($value) {
    $this->value = $value;
    $this->hour = Military::div($value, 100);
    $this->min = $value % 100;
    $this->setHelpers();
  }
  
  private function setHelpers() {
    $this->standardHour = $this->hour;
    if ($this->standardHour > 12) {
      $this->standardHour -= 12;
    }
    $this->elapsed = $this->hour * 60 + $this->min;
  }

  public function formattedMin() {
    return sprintf("%02d", $this->min);
  }
  
  public function amPm() {
    return ($this->value >= 1200) ? "PM" : "AM";
  }
  
  public function formatted() {
    return $this->standardHour . ":" . $this->formattedMin() . " " . $this->amPm();
  }
  
  // Duration in minutes between two Military objects (this and that)
  public function minus($that) {
    return $this->elapsed - $that->elapsed;
  }
  
  public function increment($minutes) {
    $this->min += $minutes;
    while ($this->min >= 60) {
      $this->min -= 60;
      $this->hour++;
    }
    if ($this->hour >= 24) {
      $this->hour = $this->hour - 24;
    }
    $this->value = $this->hour * 100 + $this->min;
    $this->setHelpers();
  }

  // STATIC FUNCTIONS
  
  static function asNow() {
    $value = intval(date("Gi"));
    return new Military($value);
  }
  
  // Integer division
  static function div($x, $y) {
    return ($x - ($x % $y)) / $y;
  }
  
  // Military from standard time
  static function valueFromStandard($hr, $min, $amPm) {
    $v = $hr * 100;
    if ($hr == 12) {
      if ($amPm == "AM") {
        $v = $v - 1200;
      }
    } else {
      if ($amPm == "PM") {
        $v = $v + 1200;
      }
    }
    return $v + $min;
  }
  
}
?>