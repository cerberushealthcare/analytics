<?php
require_once 'php/data/xml/_XmlRec.php';
require_once 'php/data/rec/sql/Clients.php';
//
/**
 * Clinical XML Readers
 * @author Warren Hornsby
 */
class ClinicalXmls {
  //
  function parse($xml) {
    if (strpos($xml, 'ContinuityOfCareRecord'))
      return ClinicalXml_Ccr::parse($xml);
    if (strpos($xml, 'ClinicalDocument'))
      return ClinicalXml_Ccd::parse($xml);
    throw new XmlParseException('XML document type not recognized.');
  }
}
abstract class ClinicalXml0 extends XmlRec {
  public $_type;
  public $_root;
  //
  abstract public function asHtml();
  abstract protected function format();
  //
  static function parse($xml) {
    $me = new static();
    $me->_root = parent::parse($xml);
    $me->onparse($me->_root);
    $me->format();
    return $me;
  }
  //
  protected function onparse($root) {}
  protected function sanitize() {
    unset($this->_root);
  } 
}
/**
 * CCD Document
 */
class ClinicalXml_Ccd extends ClinicalXml0 {
  public $_type = 'CCD';
  public $_root;
  //
  public function asHtml($client = null, $limitEncounters = 3, $demoOnly = false) {
    logit_r($client, 'asHtml');
    $h = array();
    $h[] = '<div class="CCD">';
    $h[] = $this->getPatientHtml($client);
    if (! $demoOnly) {
      $h[] = $this->getCareTeam();
      $htmls = $this->getSectionHtmls($limitEncounters);
      foreach ($htmls as $title => $html) {
        $h[] = "<h2>$title</h2>";
        $h[] = $html;
      }
    }
    $h[] = '</div>';
    return implode('', $h);
  }
  public function getBody() {
    return $this->_root->component->structuredBody;
  }
  public function getBodyComponents() {
    $body = $this->getBody();
    return arrayify($body->component);
  }
  public function getName($name) {
    $a = array();
    if (get($name, 'prefix'))
      $a[] = $name->prefix;
    if (get($name, 'given')) {
      $b = $name->given;
      if (is_array($b))
        $a[] = join(' ', $b);
      else
        $a[] = $b;
    }
    if (get($name, 'family'))
      $a[] = $name->family;
    if (get($name, 'suffix'))
      $a[] = $name->suffix;
    return implode(' ', $a);
  }
  public function getAddr($addr, $tele = null) {
    $a = array();
    if ($addr) {
      if (get($addr, 'streetAddressLine')) {
        $b = $addr->streetAddressLine;
        if (is_array($b))
          $a[] = join(' ', $b);
        else
          $a[] = $b;
      }
      $a[] = get($addr, 'city');
      $a[] = get($addr, 'state');
      $a[] = get($addr, 'postalCode');
      $a[] = get($addr, 'country');
    }
    if ($tele && get($tele, '_value'))
      $a[] = $tele->_value;
    return implode(' ', $a);  
  }
  public function getPatientHtml($client = null) {
    if ($client)
      $client->setChronAge();
    $h = array();
    $role = $this->_root->recordTarget->patientRole;
    $addr = $role->addr;
    $px = $role->patient;
    //$h[] = "<b>Name:</b> " . join(' ', $a);
    $h[] = '<h2>' .$this->getName($px->name) . '</h2>';
    $h[] = '<table class="demo">';
    $h[] = "<tr><th width='15%' style='text-align:right'><b>Gender: </b></th><td>" . getr($px, 'administrativeGenderCode._code') . '</td></tr>';
    $dob = formatDate($px->birthTime);
    if ($client) 
      $dob .= ' (Age: ' . $client->age . ')';
    $h[] = "<tr><th style='text-align:right'><b>Birth: </b></th><td>$dob</td></tr>";
    $h[] = "<tr><th style='text-align:right'><b>Address: </b></th><td>" . $this->getAddr($addr) . '</td></tr>';
    if (get($role, 'telecom')) {
      $telecom = $role->telecom;
      if (is_object($role->telecom))
        $telecom = get($telecom, '_value');
      if ($telecom)
        $h[] = "<tr><th style='text-align:right'><b>Phone: </b></th><td>" . $telecom . '</td></tr>';
    }
    if ($client) {
      if ($client->ICards && ! empty($client->ICards)) {
        if (! isset($client->ICards[0]->_empty))
          $h[] = "<tr><th style='text-align:right'><b>Ins: </b></th><td>" . $this->getICardHtml($client->ICards[0]) . '</td></tr>';
        if (count($client->ICards) > 1 && ! isset($client->ICards[1]->_empty))
          $h[] = "<tr><th style='text-align:right'><b>Alt Ins: </b></th><td>" . $this->getICardHtml($client->ICards[1]) . '</td></tr>';
      }
      if (! empty($client->cdata1))
        $h[] = '<tr><th style="text-align:right"><b>Custom 1: </b></th><td>' . $client->cdata1 . '</td></tr>';
      if (! empty($client->cdata2))
        $h[] = '<tr><th style="text-align:right"><b>Custom 2 :</b></th><td>' . $client->cdata2 . '</td></tr>';
      if (! empty($client->cdata3))
        $h[] = '<tr><th style="text-align:right"><b>Custom 3: </b></th><td>' . $client->cdata3 . '</td></tr>';
      if ($client->race) 
        $h[] = '<tr><th style="text-align:right"><b>Race: </b></th><td>' . Client::$RACES[$client->race] . '</td></tr>';
      if ($client->ethnicity) 
        $h[] = '<tr><th style="text-align:right"><b>Ethnicity: </b></th><td>' . Client::$ETHNICITIES[$client->ethnicity] . '</td></tr>';
      if (get($client, 'Language')) 
        $h[] = '<tr><th style="text-align:right"><b>Language: </b></th><td>' . $client->Language->engName . '</td></tr>';
    }
    $h[] = '</table>';
    return implode('', $h);  
  }
  public function getICardHtml($c) {
    $s = array();
    if ($c->planName) 
      $s[] = 'Plan(' . $c->planName . ')';
    if ($c->groupNo) 
      $s[] = 'Group(' . $c->groupNo . ')';
    if ($c->subscriberNo) 
      $s[] = 'Policy(' . $c->subscriberNo . ')';
    if ($c->subscriberName) 
      $s[] = 'Subscriber(' . $c->subscriberName. ')';
    if ($c->subscriberName) 
      $s[] = 'Name on Card(' . $c->nameOnCard . ')';
    if ($c->dateEffective) 
      $s[] = 'Effective(' . formatDate($c->dateEffective) . ')';
    return implode(' ' , $s);
  }
  public function getCareTeam() {
    $h = array();
    $h[] = '<br><h2>Care Team</h2>';
    $h[] = '<table border="1" width="100%"><tr><th>Provider</th><th>Type</th><th>Address</th></tr>';
    $docs = array();
    foreach (arrayify($this->_root->documentationOf->serviceEvent->performer) as $doc) {
      logit_r($doc, 'doc123');
      if (getr($doc, 'assignedEntity.assignedPerson.name')) {
        $name = $this->getName($doc->assignedEntity->assignedPerson->name); 
        $docs[$name] = $doc;
      }
    }
    foreach ($docs as $name => $doc) {
      $h[] = '<tr><td>' . $name . '</td><td>' . getr($doc, 'functionCode._code') . '</td><td>' . $this->getAddr($doc->assignedEntity->addr, get($doc->assignedEntity, 'telecom')) . '</td></tr>';
    }
    $h[] = '</table>';
    return implode(' ', $h);
  }
  /*
  public function getCareTeam() {
    $h = array();
    $h[] = '<br><h2>Care Team</h2>';
    $pcp = $this->_root->documentationOf->serviceEvent->performer->assignedEntity->assignedPerson;
    $h[] = '<table class="demo">';
    $h[] = "<tr><th width='30%' style='text-align:right'><b>Primary Physician:</b></th><td>" . $this->getName($pcp->name) . '</td></tr>';
    $org = $this->_root->documentationOf->serviceEvent->performer->assignedEntity->representedOrganization;
    $h[] = "<tr><th width='30%' style='text-align:right'>Facility: </th><td>" . $org->name . '</td></tr>';
    $h[] = "<tr><th width='30%' style='text-align:right'>Address: </th><td>" . $this->getAddr($org->addr) . '</td></tr>';
    if (get($org, 'telecom'))
      $h[] = "<tr><th width='30%' style='text-align:right'>Phone: </th><td>" . $org->telecom . '</td></tr>';
    $h[] = '</table>';
    return implode(' ', $h);
  }
  */
  public function getSectionHtmls($limitEncounters, $demoOnly = false) {  // array('title' => html,..)
    $secs = $this->getSections();
    $recs = array();
    foreach ($secs as $sec) {
      if (static::isEncountersSection($sec)) {
        $sec->title = 'Encounters';
        static::limitRows($sec->text, $limitEncounters);
      }
      $recs[$sec->title] = $this->getTextHtml($sec->text);
    }
    return $recs;
  }
  public function getSections() {
    $comps = $this->getBodyComponents();
    $secs = array();
    foreach ($comps as $comp)
      if (isset($comp->section))
        $secs[] = $comp->section;
    return $secs;
  }
  public function getTextHtml($text) {
    return XmlRec::buildHtml($text, true);
  }
  //
  protected function format() {}
  private static function isEncountersSection($s) {
    return getr($s, 'code._code') == '46240-8';
  }
  private static function limitRows(&$text, $max) {
    $tr = getr($text, 'table.tbody.tr');
    if (is_array($tr))
      $text->table->tbody->tr = array_slice($text->table->tbody->tr, 0, $max);
  }
}
/**
 * Continuity of Care
 */
class ClinicalXml_Ccr extends ClinicalXml0 {
  public $Patient;
  public $Problems;
  public $Meds;
  public $Alerts;
  public $Results;
  //
  public $_type = 'CCR';
  public $_root;
  public $_actors;
  //
  public function asHtml() {
    $h = array();
    $h[] = '<div class="CCR">';
    foreach ($this as $fid => $values) {
      if (! empty($values)) {
        $values = arrayify($values);
        foreach ($values as $value) 
          if ($value instanceof CxHtml) 
            $h[] = $value->asHtml();
      }
    }
    $h[] = '</div>';
    return implode('', $h);
  }
  //
  public function getr($rec, $field, $suffix = '', $prefix = '') {
    $text = getr($rec, $field);
    if ($text && is_string($text)) 
      return "$prefix$text$suffix";
  }
  public function getTypeConcat($rec, $field = null) {
    if ($field) 
      $rec = getr($rec, $field);
    if ($rec) {
      $type = $this->getr($rec, 'Type.Text', ':');
      if ($type) 
        unset($rec->Type);
      return $this->concata($type, $this->concat($rec));
    }
  }
  public function getActor($actorId) {
    return geta($this->_actors, $actorId);
  }
  public function getPerson($actorId) {
    $actor = $this->getActor($actorId);
    if ($actor)
      return getr($actor, 'Person');
  }
  public function getPersonName($person) {
    $rec = get($person, 'Name');
    if ($rec) {
      if (isset($rec->DisplayName))
        return $rec->DisplayName;
      else
        return $this->concat($rec);
    }
  }
  public function getDescription($rec) {
    return $this->concat($rec, 'Description');
  }
  public function getId($rec) {
    $rec = getr($rec, 'IDs');
    if ($rec) {
      return $this->concata(
        getr($rec, 'Type.Text'),
        getr($rec, 'ID'),
        $this->getSourceCite($rec));
    }
  }
  public function getAddress($rec) {
    return $this->getTypeConcat($rec, 'Address');
  }
  public function getSourceCite($rec) {
    $name = $this->getSourceName($rec);
    if ($name) 
      return "(Source: $name)";
  }
  public function getSourceName($rec) {
    $actor = getr($rec, 'Source.Actor');
    if ($actor) 
      return $this->getActorName($actor);
  }
  public function getActorName($rec) {
    $id = getr($rec, 'ActorID');
    $person = $this->getPerson($id);
    if ($person) 
      $id = $this->getPersonName($person);
    $role = $this->getr($rec, 'ActorRole.Text', ')', '(');
    return $this->concata($id, $role);
  }
  public function getDateTimes($rec) {
    $rec = getr($rec, 'DateTime');
    if ($rec) {
      $recs = arrayify($rec);
      foreach ($recs as &$rec) {
        $rec->ExactDateTime = $this->getExactDate($rec);
        $rec = $this->getTypeConcat($rec) . '<br>';
      }
      return $this->concat($recs);
    }
  }
  public function getExactDate($rec) {
    $rec = getr($rec, 'ExactDateTime');
    if ($rec) 
      return formatDateTime($rec);
  }
  public function getBirth($person) {
    $rec = getr($person, 'DateOfBirth');
    $dob = $this->getExactDate($rec);
    return ($dob) ? $dob : $this->concat($rec);
  }
  public function getProductName($rec) {
    $rec = getr($rec, 'Product');
    if ($rec) {
      $name = $this->getr($rec, 'ProductName.Text', ')', '(');
      $brand = getr($rec, 'BrandName.Text');
      if (! empty($name) || ! empty($brand))
        return $this->concata($brand, $name);
      else 
        return $this->getTextOrCode(getr($rec, 'ProductName')); 
    }
  }
  public function getMedDirections($rec) {
    $recs = getr($rec, 'Directions.Direction');
    if ($recs) {
      $this->cleanr($recs, array('InternalCCRLink', 'DirectionSequencePosition'));
      return $this->concat($recs);
    }
  }
  public function getDescOrCode($rec) {
    $rec = getr($rec, 'Description');
    if ($rec) 
      return $this->getTextOrCode($rec);
  }
  public function getTextOrCode($rec) {
    if ($rec) {
      $text = getr($rec, 'Text');
      if ($text)
        return $text;
      else
        return $this->getCode($rec);
    }
  }
  public function getAlertReaction($rec) {
    $rec = getr($rec, 'Reaction');
    if ($rec) 
      return $this->concata(
        $this->getr($rec, 'Description.Text'),
        $this->getr($rec, 'Severity.Text', ')', '('));
  }
  public function getCode($rec) {
    $rec = getr($rec, 'Code');
    if ($rec)
      return $this->concata(
        getr($rec, 'CodingSystem'),
        getr($rec, 'Value')); 
  }
  //
  public function concat($node, $field = null) {
    if ($field)  
      $node = getr($node, $field);
    $a = array();
    if (! is_null($node))
      if (is_scalar($node)) 
        $a[] = $node;
      else 
        foreach ($node as $e) 
          $a[] = $this->concat($e);
    return empty($a) ? null : implode(' ', $a);
  }
  public function concata() {
    $args = func_get_args();
    return $this->concat($args);
  }
  public function cleanr(&$node, $fields) {
    if (! is_null($node))
      if (! is_scalar($node)) 
        foreach ($node as $fid => $e) 
          if (is_string($fid) && array_search($fid, $fields) !== false) 
            unset($node->$fid);
          else if (! is_scalar($e)) 
            $this->cleanr($e, $fields);
  }
  //
  protected function onparse($root) {
    $this->_actors = $this->buildActorsMap($root);
  }
  protected function format() {
    $this->Patient = CxPatient_Ccr::from($this, $this->_root);
    $this->Problems = CxProblem_Ccr::from($this, $this->_root->Body);
    $this->Meds = CxMed_Ccr::from($this, $this->_root->Body);
    $this->Alerts = CxAlert_Ccr::from($this, $this->_root->Body);
    $this->Results = CxResult_Ccr::from($this, $this->_root->Body);
    $this->sanitize();
  }
  protected function sanitize() {
    parent::sanitize();
    unset($this->_actors);
  }
  //
  private function buildActorsMap($root) {
    $recs = array();
    $actors = getr($root, 'Actors');
    if ($actors) {
      foreach ($actors->Actor as $actor) 
        $recs[$actor->ActorObjectID] = $actor;
    }
    return $recs; 
  }
}
abstract class CxHtml {
  public function asHtml() {
    $h = array();
    $h[] = $this->htmlHead();
    $h[] = '<table>';
    foreach ($this as $fid => $value) {
      if (is_scalar($value)) {
        $h[] = $this->htmlRow($fid, $value);
      } else if (! empty($value)) {
        $values = arrayify($value);
        $h[] = '<tr><th></th><td class="CxSub">';
        foreach ($values as $value) 
          $h[] = $value->asHtml();
        $h[] = '</td></tr>';
      }
    }
    $h[] = '</table>';
    return implode('', $h);
  }
  //
  protected function htmlHead() {
    return '<h2>' . $this->getMyName() . '</h2>';
  }
  protected function htmlRow($fid, $value) {
    $h = array();
    $h[] = '<tr>';
    $h[] = '<th>' . $this->htmlFid($fid) . '</th>';
    $h[] = '<td>' . $this->htmlValue($fid, $value) . '</td>';
    $h[] = '</tr>';
    return implode('', $h);
  }
  protected function htmlFid($fid) {
    return $this->camelToFriendly($fid);
  }
  protected function htmlValue($fid, $value) {
    return $value;
  }
  protected function getMyName() {
    $a = split('_', get_called_class());
    return substr($a[0], 2);
  }
  protected function camelToFriendly($fid) {
    $func = create_function('$c', 'return " $c[1]";');
    return substr(preg_replace_callback('/([A-Z])/', $func, ucfirst($fid)), 1);
  }
}
class CxPatient extends CxHtml {
  public $id;
  public $name;
  public $birth;
  public $gender;
  public $altId;
  public $address;
}
class CxPatient_Ccr extends CxPatient {
  static function from($xml, $root) {
    $me = new static();
    $me->id = getr($root, 'Patient.ActorID');
    if ($me->id) {
      $actor = $xml->getActor($me->id);
      $person = getr($actor, 'Person');
      if ($person) {
        $me->name = $xml->getPersonName($person);
        $me->birth = $xml->getBirth($person);
        $me->gender = getr($person, 'Gender.Text');
        $me->altId = $xml->getId($actor);
        $me->address = $xml->getAddress($actor); 
      }
    }
    return $me;
  }
}
class CxProblem {
  public $dates;
  public $type;
  public $desc;
  public $source;
  public $status;
}
class CxProblem_Ccr extends CxProblem {
  static function from($xml, $body) {
    $mes = array();
    $problems = arrayify(getr($body, 'Problems.Problem'));
    foreach ($problems as $problem)
      $mes[] = static::fromProblem($xml, $problem);
    return $mes;
  }
  static function fromProblem($xml, $rec) {
    $me = new static();
    $me->dates = $xml->getDateTimes($rec);
    $me->type = getr($rec, 'Type.Text');
    $me->desc = $xml->getDescription($rec);
    $me->source = $xml->getSourceName($rec);
    $me->status = getr($rec, 'Status.Text');
    return $me;  
  }
}
class CxMed extends CxHtml {
  public $dates;
  public $name;
  public $strength;
  public $form;
  public $qty;
  public $directions;
  public $refills;
  public $source;
  public $status;
} 
class CxMed_Ccr extends CxMed {
  static function from($xml, $body) {
    $mes = array();
    $meds = arrayify(getr($body, 'Medications.Medication'));
    foreach ($meds as $med)
      $mes[] = static::fromMed($xml, $med);
    return $mes;
  }
  static function fromMed($xml, $rec) {
    $me = new static();
    $me->dates = $xml->getDateTimes($rec);
    $me->name = $xml->getProductName($rec);
    $me->strength = $xml->concat($rec, 'Product.Strength');
    $me->form = $xml->concat($rec, 'Product.Form.Text');
    $me->qty = $xml->concat($rec, 'Quantity');
    $me->directions = $xml->getMedDirections($rec);
    $me->source = $xml->getSourceName($rec);
    $me->status = getr($rec, 'Status.Text');
    return $me;
  }
}
class CxAlert extends CxHtml {
  public $dates;
  public $type;
  public $desc;
  public $reaction;
  public $source;
  public $status;
} 
class CxAlert_Ccr extends CxAlert {
  static function from($xml, $body) {
    $mes = array();
    $alerts = arrayify(getr($body, 'Alerts.Alert'));
    foreach ($alerts as $alert)
      $mes[] = static::fromAlert($xml, $alert);
    return $mes;
  }
  static function fromAlert($xml, $rec) {
    $me = new static();
    $me->dates = $xml->getDateTimes($rec);
    $me->type = getr($rec, 'Type.Text');
    $me->desc = $xml->getDescOrCode($rec);
    $me->reaction = $xml->getAlertReaction($rec);
    $me->source = $xml->getSourceName($rec);
    $me->status = getr($rec, 'Status.Text');
    return $me;
  }
}
class CxResult extends CxHtml {
  public $dates;
  public $desc;
  public $source;
  public $Tests;
}
class CxResult_Ccr extends CxResult {
  static function from($xml, $body) {
    $mes = array();
    $results = arrayify(getr($body, 'Results.Result'));
    foreach ($results as $result)
      $mes[] = static::fromResult($xml, $result);
    return $mes;
  }
  static function fromResult($xml, $rec) {
    $me = new static();
    $me->dates = $xml->getDateTimes($rec);
    $me->desc = $xml->getDescOrCode($rec);
    $me->source = $xml->getSourceName($rec);
    $me->Tests = CxTest_Ccr::from($xml, $rec);
    return $me;
  }
}
class CxTest extends CxHtml {
  public $dates;
  public $type;
  public $desc;
  public $result;
  public $normal;
  public $flag;
  public $source;
}
class CxTest_Ccr extends CxTest {
  static function from($xml, $result) {
    $mes = array();
    $tests = arrayify(getr($result, 'Test'));
    foreach ($tests as $test)
      $mes[] = static::fromTest($xml, $test);
    return $mes;
  }
  static function fromTest($xml, $rec) {
    $me = new static();
    $me->dates = $xml->getDateTimes($rec);
    $me->type = getr($rec, 'Type.Text');
    $me->desc = $xml->getDescOrCode($rec);
    $me->result = $xml->concat($rec, 'TestResult');
    $me->normal = $xml->concat($rec, 'Normal');
    $me->flag = getr($rec, 'Flag.Text');
    $me->source = $xml->getSourceName($rec);
    return $me;
  }
}
