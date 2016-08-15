<?php
require_once "php/data/json/_util.php";

class JQuestion {

  public $id;
  public $uid;
  public $desc;
  public $selected;
  public $unselected;
  public $default;
  public $multiIndex;
  public $multiCap;
  public $multiIndex2;
  public $multiCap2;
  public $image;
  public $sync;
  public $options;
  public $loix;  // last regular option index (prior to "other" options)
  public $dsync;
  public $icd;  // true if question has an ICD
  public $trackable;  // true if any options are trackable  
  
  public $type;
  public $cbo;
  public $cloning;
  
  const Q_TYPE_ALLERGY = 1;
  const Q_TYPE_CALC = 2;
  const Q_TYPE_CALENDAR = 3;
  const Q_TYPE_FREE = 4;
  const Q_TYPE_MED = 5;  
  const Q_TYPE_BUTTON = 6;
  const Q_TYPE_COMBO = 7;
  const Q_TYPE_DATA_HM = 8;
  const Q_TYPE_HIDDEN = 9;
  
  // Optional
  public $outData;
  public $quid;  // fully-qualified (suid.puid.quid)
  
  public function __construct($id, $uid, $desc, $selected, $unselected, $default, $multiIndex, $multiCap, $multiIndex2, $multiCap2, $image, $sync, $options, $loix, $dsync, $icd, $trackable) {

    // Note: JSON design allows for multiple defaults, whereas the current GUI/DB layout only allows one.
    $this->id = $id;
    $this->uid = $uid;
    $this->type = JQuestion::setType($uid);
    if ($this->type == JQuestion::Q_TYPE_ALLERGY || $this->type == JQuestion::Q_TYPE_COMBO) {
      $this->cbo = true;
    }
    if ($this->type == JQuestion::Q_TYPE_ALLERGY || $this->type == JQuestion::Q_TYPE_MED || $this->type == JQuestion::Q_TYPE_COMBO) {
      $this->cloning = true;
    }
    $this->desc = $desc;
    $this->selected = $selected;
    $this->unselected = $unselected;
    $this->default = $default;
    $this->multiIndex = $multiIndex;
    $this->multiCap = $multiCap;
    $this->multiIndex2 = $multiIndex2;
    $this->multiCap2 = $multiCap2;
    $this->image = $image;
    $this->sync = $sync;
    $this->options = $options;
    $this->loix = $loix;
    $this->dsync = $dsync;
    $this->icd = $icd;
    $this->trackable = $trackable;
    $this->other = $this->type == 0 && $loix > -1; 
  }
  public function out() {
    $out = "";
    $out = nqq($out, "id", $this->id);
    $out = nqq($out, "uid", $this->uid);
    $out = nqqo($out, "type", $this->type);
    $out = nqqo($out, "cbo", $this->cbo);
    $out = nqqo($out, "cloning", $this->cloning);
    $out = nqq($out, "desc", $this->desc);
    $out = nqqai($out, "sel", $this->selected);
    $out = nqqai($out, "unsel", $this->unselected);
    $out = nqqai($out, "def", $this->default);
    $out = nqqo($out, "mix", $this->multiIndex);
    $out = nqq($out, "mc", $this->multiCap);
    $out = nqq($out, "sync", $this->sync);
    $out = nqqa($out, "opts", $this->options);
    $out = nqqo($out, "loix", $this->loix);
    $out = nqq($out, "icd", $this->icd);
    $out = nqq($out, "out", $this->outData);
    $out = nqq($out, "quid", $this->quid);
    $out = nqq($out, "dsync", $this->dsync);
    $out = nqqo($out, "track", $this->trackable);
    $out = nqqo($out, "other", $this->other);
    return cb($out);    
  }
  public static function setType($uid) {  // set special question type
    $char1 = substr($uid, 0, 1);
    if ($char1 == "!") {
      return JQuestion::Q_TYPE_ALLERGY; 
    }
    if ($char1 == "#") {
      return JQuestion::Q_TYPE_CALC;
    }
    if ($char1 == "%") {
      return JQuestion::Q_TYPE_CALENDAR;
    }
    if ($char1 == "$") {
      return JQuestion::Q_TYPE_FREE;
    }
    if ($char1 == "@") {
      return JQuestion::Q_TYPE_MED;
    }
    if ($char1 == "*") {
      return JQuestion::Q_TYPE_BUTTON;
    }
    if ($char1 == "?") {
      return JQuestion::Q_TYPE_COMBO; 
    }
    if ($char1 == "^") {
      return JQuestion::Q_TYPE_DATA_HM; 
    }
    if ($char1 == "=") 
      return JQuestion::Q_TYPE_HIDDEN;
    return null;
  }
}
?>