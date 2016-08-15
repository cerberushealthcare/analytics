<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Session 
 */
class Session extends SqlRec {
  //
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
  //
  public function getSqlTable() {
    return 'sessions';
  }
}
