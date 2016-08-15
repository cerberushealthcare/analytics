<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Session Base Class
 * @author Warren Hornsby
 */
abstract class SessionRec extends SqlRec implements AutoEncrypt {
  /*
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateCreated;
  public $dateUpdated;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $billed;
  public $schedId;
  public $data;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $html;
  public $title;
  public $standard;
  public $noteDate;
  public $stubType;
  public $stubId;
  public $stubName;
  */
  //
  public function getSqlTable() {
    return 'sessions';
  }
  public function getEncryptedFids() {
    return array('dateCreated','dateService','dateClosed','noteDate','data','html','title');
  }  
  public function toJsonObject(&$o) {
    $o->label = $this->getLabel();
  }
  public function getHtml() {
    return ($this->closed == 2) ? $this->data : $this->html;
  }
  //
  public function formatTitle() {
    $s = $this->title;
    if ($this->isClosed())
      $s .= ' (Signed)';
    return $s;
  }
  public function isClosed() {
    return $this->closed > 0;
  }
  public function getLabel() {
    $label = $this->title;
    if ($this->closed)
      $label .= " (Signed)";
    return $label;
  }
}
