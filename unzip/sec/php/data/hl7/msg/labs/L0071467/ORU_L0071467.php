<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0071467 - Health Network Labs
 */
class ORU_L0071467 extends ORU_Lab {
  //
  /* Segments */
  public $Header;
  public $Software = 'SFT';
  public $PatientId = 'PID_L0071467';
  public $Eof = 'FTS';
  //
  public function getUgid() {
    if (MyEnv::isLocal())
      return 3;
    else
      return 2666/*healthnetlabs*/;
  }
}
class PID_L0071467 extends PID_Lab {
  //
  public $CommonOrder = 'ORC_L0071467';
  public $ObsRequest = 'OBR_L0071467[]';
}
class ORC_L0071467 extends ORC_Lab {
  //
}
class OBR_L0071467 extends OBR_Lab {
  //
  public $Observation = 'OBX_L0071467[]';
  //
  protected function asProc($client, $map) {
    $proc = parent::asProc($client, $map);
    $comments = $this->getProcComments();
    if (! empty($proc->comments))
      $proc->comments .= '<br>' . $comments;
    else
      $proc->comments = $comments;
    return $proc;
  }
  public function getProcComments() {
    $c = array();
    if (! empty($this->orderProvider))
      $c[] = 'Ordering Physician: ' . $this->orderProvider->asFormatted();
    if (! empty($this->resultCopyTo))
      $c[] = 'Copy To: ' . $this->getCopyTos();
    if (! empty($this->obsDateTime))
      $c[] = 'Specimen Collected: ' . $this->obsDateTime->asFormatted();
    if (! empty($this->specimenReceived))
      $c[] = 'Specimen Received: ' . $this->specimenReceived->asFormatted();
    $performers = $this->getPerformers();
    if (! empty($performers)) {
      $c[] = 'Performing Labs: ';
      foreach ($performers as $id => $lab) 
        $c[] = '  ' . $id . ' - ' . $lab;
    } 
    return implode('<br>', $c);
  }
  protected function getProcXrefOrder() {
    return implode('|', array(
      $this->placerOrderNo,
      get($this->serviceId, 'id'),
      get($this->obsDateTime, 'time'))); 
  }
  protected function getPerformers() {
    $map = array();
    foreach ($this->Observation as $obx) {
      if (! empty($obx->producerId)) 
        $map[$obx->producerId->id] = $obx->producerId->text;
    }
    return $map;
  }
  protected function getCopyTos() {
    $c = array();
    foreach ($this->resultCopyTo as $copyTo) {
      if ($copyTo instanceof XCN)
        $c[] = $copyTo->asFormatted();
    }
    return implode('<br>         ', $c);
  }
}
class OBX_L0071467 extends OBX_Lab {
  //
  protected function asProcResult($proc, $index, $map) {
    $result = parent::asProcResult($proc, $index, $map);
    $comments = $this->getResultComments();
    if (! empty($result->comments))
      $result->comments .= '<br>' . $comments;
    else
      $result->comments = $comments;
    return $result;
  }
  public function getResultComments() {
    if (! empty($this->producerId))
      return 'Performing Lab: ' . $this->producerId->id;
  }
}