<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';   
//
/**
 * Diagnosis v2.5.1
 * @author Warren Hornsby
 */
class DG1 extends HL7SequencedSegment {
  //
  public $segId = 'DG1';
  public $seq;  // 1: Set ID - DG1 (SI)
  public $codingMethod;  // 2: Diagnosis Coding Method (ID)
  public $code = 'CE';  // 3: Diagnosis Code - DG1 (CE)
  public $desc;  // 4: Diagnosis Description (ST)
  public $date = 'TS';  // 5: Diagnosis Date/Time (TS)
  public $type;  // 6: Diagnosis Type (IS)
  public $cat;  // 7: Major Diagnostic Category (CE)
  public $relatedGrp;  // 8: Diagnostic Related Group (CE)
  public $drgApproval;  // 9: DRG Approval Indicator (ID)
  public $drgGrouperReview;  // 10: DRG Grouper Review Code (IS)
  public $outlierType;  // 11: Outlier Type (CE)
  public $outlierDays;  // 12: Outlier Days (NM)
  public $outlierCost;  // 13: Outlier Cost (CP)
  public $grouperType;  // 14: Grouper Version And Type (ST)
  public $priority;  // 15: Diagnosis Priority (ID)
  public $clinician;  // 16: Diagnosing Clinician (XCN)
  public $class;  // 17: Diagnosis Classification (IS)
  public $conf;  // 18: Confidential Indicator (ID)
  public $attestDate;  // 19: Attestation Date/Time (TS)
  public $diagId;  // 20: Diagnosis Identifier (EI)
  public $diagAction;  // 21: Diagnosis Action Code (ID)
  //
  static $_seq = 0;
}
class DG1_ADT extends DG1 {
  //
  static function all($fs) {
    if ($fs->Session->Diagnoses) {
      $us = array();
      static::resetSeq();
      foreach ($fs->Session->Diagnoses as $diag) 
        $us[] = static::fromDiag($fs, $diag);
      return $us;
    }
  }
  static function fromDiag($fs, $diag) {
    $me = self::asEmpty();
    $me->code = CE_ICD9::fromDiagnosis($diag);
    $me->date = TS::fromDate($fs->Session->date);
    $me->type = $fs->getDataCode('diagnosisType') ?: IS_DiagnosisType::asWorking(); 
    return $me;
  }
}
