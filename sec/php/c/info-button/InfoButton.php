<?php
require_once 'php/c/info-button/InfoButton_Query.php';
//
/**
 * Info Button Caller
 * @author Warren Hornsby
 */
class InfoButton {
  //
  static function searchDiag($icd) {
    return InfoButtonQuery::searchDiag($icd);
  }
  static function searchMed($rxcui) {
    return InfoButtonQuery::searchMed($rxcui);
  }
  static function searchLab($loinc) {
    return InfoButtonQuery::searchDiag($loinc);
  }
}