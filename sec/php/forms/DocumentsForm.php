<?php
require_once "php/forms/PagingForm.php";
require_once "php/dao/SchedDao.php";
require_once "php/dao/SessionDao.php";
require_once "php/data/db/User.php";
require_once "php/forms/utils/CommonCombos.php";
require_once "php/forms/utils/Paging.php";

class DocumentsForm extends PagingForm {

  // Form props
  public $rows;  // DocumentRow[]
  public $view;  // 0=notes, 1=templates
  public $users;  // combo
  public $userId;  // selected ID  
  public $pop;  // 0=new note, 1=open note
  public $meOnly;  // only show notes to myself
  public $noTemplates;  // don't allow templates  
  
  const VIEW_NOTES = "0";
  const VIEW_TEMPLATES = "1";
  const ALL_USERS = -1;
  
  public function __construct($baseUrl) {
    $this->setFormProps();
    //$sortKey = $this->isNotesView() ? "date_service date_updated" : "date_updated";
    $sortKey = "date_updated";
    $this->initialize($baseUrl . "?v=" . $this->view . "&u=" . $this->userId, $sortKey, -1, 15);
    // Set user filter
    if ($this->view == DocumentsForm::VIEW_NOTES) {
      if ($this->userId == DocumentsForm::ALL_USERS) { 
        $this->paging->filter2 = null;
      } else {
        $this->paging->setFilter(2, "send_to", Filter::EQ_EQUALS, $this->userId);
      }
    }
    $this->readPage();
  }
  
  private function setFormProps() {
    global $login;
    $this->pop = Form::getFormVariable("pop");
    $this->meOnly = false;//! $login->Role->Artifact->noteCreate;
    if ($this->meOnly) {
      $this->userId = $login->userId; 
    } else {
      $this->userId = Form::getFormVariable("u", DocumentsForm::ALL_USERS);
    }
    $this->noTemplates = ! $login->Role->Artifact->templates;
    if ($this->noTemplates) {
      $this->view = DocumentsForm::VIEW_NOTES;
    } else {
      $this->view = Form::getFormVariable("v", DocumentsForm::VIEW_NOTES);
    }
    $this->users = CommonCombos::usersInGroup($this->meOnly);
  }
  
  public function readPage() {
    global $login;
    $this->rows = array();
    if ($this->isNotesView()) {
      $stubs = SchedDao::getQuickJSessionStubs("WHERE s.user_group_id=" . $login->userGroupId . " AND s.date_updated>'2004-01-01' AND " . $this->paging->buildSql());
      $this->setRecordCount($stubs);
      for ($i = 0; $i < $this->recordCount; $i++) {
        $this->rows[] = new NoteRow($i, $stubs[$i]);
      }
    } else {
      $presets = SessionDao::getJTemplatePresets("AND " . $this->paging->buildSql());
      $this->setRecordCount($presets);
      for ($i = 0; $i < $this->recordCount; $i++) {
        $this->rows[] = new TemplateRow($i, $presets[$i]);
      }
    }
  }
  
  public function isNotesView() {
    return $this->view == DocumentsForm::VIEW_NOTES;
  }
  
  public function isTemplatesView() {
    return ! isNotesView();
  }
  
  public function isUnsignedView() {
    return $this->isNotesView() && ($this->paging->filter1 !== null);
  }
  
  public function isEveryoneView() {
    return $this->userId == -1;
  }
  
  public static function buildHistoryText($date, $by) {
    if ($date == null) {
      return "";
    }
    $s = $date;
    if ($by != null) {
      $s .= " (" . User0::getInits($by) . ")";
    }
    return $s;
  }
}

class NoteRow extends OffsettingRow {
  
  public $stub;
  public $noteAnchorHtml;
  public $createdText;
  public $updatedText;
  public $dos;
  
  public function __construct($rowIndex, $sessionStub) {
    $this->setTrClass($rowIndex);
    $this->stub = $sessionStub;
    $this->dos = formatShortDate($sessionStub->dateService);
    $this->createdText = DocumentsForm::buildHistoryText(formatShortTimestamp($sessionStub->dateCreated), $sessionStub->createdBy);
    $this->updatedText = DocumentsForm::buildHistoryText(formatShortTimestamp($sessionStub->dateUpdated), $sessionStub->updatedBy);
    $this->buildAnchor($sessionStub);
  }

  private function buildAnchor($stub) {
    $name = $stub->label;
    $class = "icon ";
    if ($stub->closed) {
      $class .= "no-edit-note";
    } else {
      $class .= "edit-note";
    }
    //$text = "<b>" . $name . "</b> (" . $stub->dateService . ")";
    $text = $name;
    $href = "javascript:go(" . $stub->id . ")";
    $a = new Anchor($href, $text);
    $this->noteAnchorHtml = $a->html($class);
  }
}

class TemplateRow extends OffsettingRow {

  public $preset;
  public $createdText;
  public $updatedText;
  
  public function __construct($rowIndex, $preset) {
    $this->setTrClass($rowIndex);
    $this->preset = $preset;
    $this->createdText = DocumentsForm::buildHistoryText($preset->dateCreated, $preset->createdBy);
    $this->updatedText = DocumentsForm::buildHistoryText($preset->dateUpdated, $preset->updatedBy);
  }
}
?>