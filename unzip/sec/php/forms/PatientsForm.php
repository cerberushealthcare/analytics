<?php
require_once "php/forms/PagingForm.php";
require_once "php/dao/SchedDao.php";
require_once "php/forms/utils/CommonCombos.php";

class PatientsForm extends PagingForm {

  // Form props
  public $rows;  // PatientRow[]
  public $popNew;  // true to show new patient pop
  public $popId;  // not null to show existing patient pop
  public $luPatientsView;  // LOOKUP table
  
  public function __construct($baseUrl) {
    $this->initialize($baseUrl, "date", -1, 15);
    $this->luPatientsView = LookupDao::getPatientsView();
    $this->paging->maxRows = 20;  //$this->luPatientsView->pp;
    $this->setFormProps();
    $this->readPage();
  }
  
  private function setFormProps() {
    $this->popNew = (Form::getFormVariable("pn") == "1");
    $this->popId = Form::getFormVariable("pid");
  }
  
  public function readPage() {
    $this->rows = array();
    $clients = SchedDao::getClients($this->paging);
    $this->setRecordCount($clients);
    for ($i = 0; $i < $this->recordCount; $i++) {
      $this->rows[] = new PatientRow($i, $clients[$i]);
    }
  }
  
  public function searchingText() {
    if (! $this->isSearching()) {
      return;
    }
    $a = "Showing: <em>";
    $f1 = $this->paging->filter1->name;
    $f2 = ($this->paging->filter2 != null) ? $this->paging->filter2->name : "";
    if ($f1 == "first_name" && $f2 == "last_name") {
      $a .= "Names beginning with \"" . $this->paging->filter1->value . " " . $this->paging->filter2->value . "\"</em>";
    } else { 
      if ($f1 == "uid") {
        $a .= "IDs";      
      } else if ($f1 == "first_name") {
        $a .= "First names";
      } else {
        $a .= "Last names";
      }
      $a .= " beginning with \"" . $this->paging->filter1->value . "\"</em>";
    }
    return $a;
  }
}

class PatientRow extends OffsettingRow {
  
  public $client;  // Client
  
  // Derived
  public $event;
  public $eventAnchor;
  //public $clientAddressLine; 
  
  public function __construct($rowIndex, $client) {
    $this->setTrClass($rowIndex);
    $this->client = $client;
    //if ($client->shipAddress == null) {
    //  $this->clientAddressLine = "";
    //} else {
    //  $this->clientAddressLine = $client->shipAddress->buildAddressLine();
    //}
    $this->event = $client->events[0];
    $this->buildAnchor($client);
    logit_r($this, 'patientrow');
  }
  
  private function buildAnchor($client) {
    if ($client->events == null || sizeof($client->events) == 0) {
      return "";
    }
    $e = $this->event;
    $text = $e->name;
    if ($e->type == JClientEvent::TYPE_CLIENT || $e->aHref == null) {
      $this->eventAnchor = $text;
    } else {
      $a = new Anchor($e->aHref, $text);
      $this->eventAnchor = $a->html($e->aClass);
    }
  }
}
?>