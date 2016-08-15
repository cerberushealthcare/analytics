<?php
require_once 'php/data/xml/ccd/datatypes_base.php';
//
class CS_LanguageType extends CS {
  //
  static function asUsEnglish() {
    return new self('en-US');
  }
}
class CS_Status extends CS {
  //
  static function fromMed($med) {
    if ($med->active)
      return self::asActive();
    else
      return self::asCompleted();
  }
  static function asActive() {
    return new self('active');
  }
  static function asCompleted() {
    return new self('completed');
  }
  static function asAborted() {
    return new self('aborted');
  }
  static function asCancelled() {
    return new self('cancelled');
  }
}
?>