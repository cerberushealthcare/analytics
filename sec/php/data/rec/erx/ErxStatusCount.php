<?php
require_once 'php/data/rec/erx/ErxStatus.php';
require_once 'php/newcrop/data/NCScript.php';
/**
 * ERX Status Count 
 */
class ErxStatusCount extends Rec {
  //
  public $statusCount;       // just mine
  public $statusCountAll;    // whole group
  public $pharmComCount;     // just mine
  public $pharmComCountAll;  // whole group
  public $lpId;
  public $lpName;
  //
  public function getStatusText() {
    if ($this->statusCountAll > 0) {
      //$text = "Status $this->statusCount/$this->statusCountAll";
      $text = "Status $this->statusCount";
    } else {
      $text = 'Status';
    }
    return $text;
  }
  public function getPharmText() {
    if ($this->pharmComCountAll > 0) 
      //$text = "Pharm $this->pharmComCount/$this->pharmComCountAll";
      $text = "Pharm $this->pharmComCount";
    else 
      $text = 'Pharm';
    return $text;
  }
  public function toJsonObject(&$o) {
    $o->_statusText = $this->getStatusText();
    $o->_statusColor = ($this->statusCount > 0) ? 'red' : '';
    $o->_pharmText = $this->getPharmText();
    $o->_pharmColor = ($this->pharmComCount > 0) ? 'red' : '';
  }
  /**
   * @param object $statuses @see NewCrop::pullAcctStatusDetails
   * @param object $pharmReqs @see NewCrop::pullRenewalRequests 
   * @return ErxStatusCount 
   */
  static function fromNewCrop($statuses, $pharmReqs) {
    global $login;
    $e = new ErxStatusCount();
    $erxUser = ErxUsers::getMe();
    $e->lpId = $erxUser->_lpId; 
    $e->lpName = $erxUser->_lpName; 
    $isLp = $erxUser->NcUser->isLp();
    if ($statuses) {
      $e->statusCountAll = 0;
      $e->statusCount = 0;
      foreach ($statuses as $status => $recs) {
        if ($recs) {
          $e->statusCountAll += count($recs);
          $isStaffSection = $status == ErxStatus::STATUS_STAFF_PROC;
          if (($isLp && ! $isStaffSection) || (! $isLp && $isStaffSection))  
            foreach ($recs as $rec) 
              if ($rec->ExternalDoctorId == $erxUser->_lpId)
                $e->statusCount++;
        }
      }
    } else {
      $e->statusCount = 0;
      $e->statusCountAll = 0;
    }
    if ($pharmReqs) {
      $e->pharmComCountAll = count($pharmReqs);
      $e->pharmComCount = 0;
      foreach ($pharmReqs as $req) {
        if ($req->ExternalDoctorId == $login->userId)
          $e->pharmComCount++;
      }
    } else {
      $e->pharmComCountAll = 0;
      $e->pharmComCount = 0;
    }
    return $e;
  }
}
