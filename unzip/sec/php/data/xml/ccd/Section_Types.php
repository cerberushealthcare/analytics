<?php
require_once 'php/data/xml/ccd/POCD_MT000040.php';
//
/**
 * Section types
 */
class Section_Meds extends POCD_Section {
  // 
  public function __construct($meds) {
    $this->templateId = InfrastructureRoot_templateId::asSectionMeds();
    $this->code = new CE_LOINC('10160-0', 'History of Medication Use');
    $this->title = ST::asText('Medications');
    if (empty($meds) || current($meds) instanceof NoneActiveMed) {
      $this->text = new Text_Meds();
      $this->add(Entry::asNoMeds());
    } else { 
      $this->text = new Text_Meds($meds);
      foreach ($meds as $med) 
        $this->add(Entry::fromMed($med));
    } 
  }
}
class Section_MedsAdmin extends POCD_Section {
  // 
  public function __construct($meds) {
    $this->templateId = InfrastructureRoot_templateId::asSectionMedsAdmin();
    $this->code = new CE_LOINC('29549-3', 'Medications Administered');
    $this->title = ST::asText('Medications Administered');
    if (empty($meds) || current($meds) instanceof NoneActiveMed) {
      $this->text = new Text_Meds();
      $this->add(Entry::asNoMeds());
    } else { 
      $this->text = new Text_Meds($meds);
      foreach ($meds as $med) 
        $this->add(Entry::fromMed($med));
    } 
  }
}
class Section_Alerts extends POCD_Section {
  // 
  public function __construct($allers) {
    $this->templateId = InfrastructureRoot_templateId::asSectionAlerts();
    $this->code = new CE_LOINC('48765-2', 'Allergies, Adverse Reactions, Alerts');
    $this->title = ST::asText('Allergies and Adverse Reactions');
    $this->text = new Text_Allerts($allers);
    foreach ($allers as $aller) 
      $this->add(Entry::fromAller($aller));
  } 
}
class Section_Vitals extends POCD_Section {
  // 
  public function __construct($vitals) {
    $this->templateId = InfrastructureRoot_templateId::asSectionVitals();
    $this->code = new CE_LOINC('8716-3', 'Vital Signs');
    $this->title = ST::asText('Vital Signs');
    $this->text = new Text_Vitals($vitals);
    foreach ($vitals as $vital) 
      $this->add(Entry::fromVital($vital)); 
  }
}
class Section_Encounters extends POCD_Section {
  // 
  public function __construct($procs, $ug) {
    $this->templateId = InfrastructureRoot_templateId::asSectionEncounters();
    $this->code = new CE_LOINC('46240-8', 'Encounters');
    $this->title = ST::asText('Encounters');
    $this->text = new Text_Encounters($procs);
    /*
    foreach ($sessions as $session) {
      $session->UserGroup = $ug;
      $this->add(Entry::fromSession($session));
    } 
    */
  }
}
class Section_Immuns extends POCD_Section {
  // 
  public function __construct($immuns) {
    $this->templateId = InfrastructureRoot_templateId::asSectionImmuns();
    $this->code = new CE_LOINC('11369-6', 'History of Immunizations');
    $this->title = ST::asText('Immunizations');
    $this->text = new Text_Immuns($immuns);
    foreach ($immuns as $immun) 
      $this->add(Entry::fromImmun($immun)); 
  }
}
class Section_Problems extends POCD_Section {
  //
  public function __construct($diags, $code = null) {
    $this->templateId = InfrastructureRoot_templateId::asSectionProblems();
    $this->code = ($code) ? code : CE_LOINC_ProblemContent::asProblemList();
    $this->title = ST::asText('Problems');
    $this->text = new Text_Problems($diags);
    foreach ($diags as $diag) {
      $this->add(Entry::fromDiag($diag));
    } 
  }
}
class Section_Procedures extends POCD_Section {
  //
  public function __construct($procs, $ugid) {
    $this->templateId = InfrastructureRoot_templateId::asSectionProcedures();
    $this->code = new CE_LOINC('47519-4', 'History of Procedures');
    $this->title = ST::asText('Procedures');
    $this->text = new Text_Procedures($procs);
    if ($procs) {
      foreach ($procs as $proc) 
        $this->add(Entry::fromProc($proc));
    } else {
      $this->add(Entry::asNullProc($ugid));
    } 
  }
}
class Section_PlanOfCare extends POCD_Section {
  //
  public function __construct($fs) {
    $this->templateId = InfrastructureRoot_templateId::asSectionPlanOfCare();
    $this->code = new CE_LOINC('18776-5', 'Plan of Care');
    $this->title = ST::asText('Plan of Care');
    $plan = get($fs, 'PlanOfCare');
    $pending = get($fs, 'PendingTests');
    $future = get($fs, 'FutureTests'); 
    $followups = get($fs, 'Followups');
    $referrals = get($fs, 'Referrals');
    $this->text = new Text_PlanOfCare($plan, $pending, $future, $followups, $referrals);
    foreach ($plan as $proc) 
      $this->add(Entry::fromPlanOfCare($proc));
    foreach ($pending as $track) 
      $this->add(Entry::asPlanTest($track));
    foreach ($future as $track) 
      $this->add(Entry::asPlanTest($track));
    foreach ($followups as $track)
      $this->add(Entry::asPlanEncounter($track));
    foreach ($referrals as $track)
      $this->add(Entry::asPlanEncounter($track));
  } 
}
class Section_ReasonForVisit extends POCD_Section {
  //
  public function __construct($proc) {
    $this->templateId = InfrastructureRoot_templateId::asSectionReasonForVisit();
    $this->code = new CE_LOINC('29299-5', 'Reason for Visit');
    $this->title = ST::asText('Reason for Visit');
    $this->text = new Text_ReasonForVisit($proc);
  }
}
class Section_ReasonForReferral extends POCD_Section {
  //
  public function __construct($trackItem) {
    $this->templateId = InfrastructureRoot_templateId::asSectionReasonForReferral();
    $this->code = new CE_LOINC('42349-1', 'Reason for Referral');
    $this->title = ST::asText('Reason for Referral');
    $this->text = new Text_ReasonForReferral($trackItem);
  }
}
class Section_FunctionalStatus extends POCD_Section {
  //
  public function __construct($procs) {
    $this->templateId = InfrastructureRoot_templateId::asSectionFunctionalStatus();
    $this->code = new CE_LOINC('47420-5', 'Functional Status');
    $this->title = ST::asText('Functional Status');
    $this->text = new Text_Functional($procs);
    foreach ($procs as $proc) 
      $this->add(Entry::fromFunctional($proc)); 
  }
}
class Section_Results extends POCD_Section {
  //
  public function __construct($procs) {
    $this->templateId = InfrastructureRoot_templateId::asSectionResults();
    $this->code = new CE_LOINC('30954-2', 'Relevant Diagnostic Tests and/or Laboratory Data');
    $this->title = ST::asText('Results');
    $this->text = new Text_Results($procs);
    foreach ($procs as $proc)  
      $this->add(Entry::fromProcResults($proc));
  }
}
class Section_Instructions extends POCD_Section {
  //
  public function __construct($instructs, $pda) {
    $this->templateId = InfrastructureRoot_templateId::asSectionInstructions();
    $this->code = new CE_LOINC('69730-0', 'Instructions');
    $this->title = ST::asText('Instructions / Patient Decision Aids');
    $this->text = new Text_Instructions($instructs, $pda);
  }
}
class Section_Social extends POCD_Section {
  //
  public function __construct($ugid, $smoking) {
    $this->templateId = InfrastructureRoot_templateId::asSectionSocial();
    $this->code = new CE_LOINC('29762-2', 'Social History');
    $this->title = ST::asText('Social History');
    //$this->text = new Text_Social($socs);
    $this->text = new Text_Smoking($smoking);
    $this->add(Entry::fromSmoke($smoking));
  }
}
/**
 * Text <table> definitions 
 */
class Text extends XmlRec {
  public $table;
  //
  public static function denull($value, $with = '') {
    return (empty($value)) ? $with : $value;
  }
}
class Text_Meds extends Text {
  //
  public function __construct($meds = null) {
      $this->table = new TextTable('Medication', 'Instructions', 'Date(s)', 'Status');
    if (empty($meds)) {
      $this->table->add('Patient not on any medication');
    } else {
      foreach ($meds as $med) {
        $this->table->add($med->name, $med->text, formatDate($med->date), $med->formatActive());
        $td->_ID = TEXT_REF::getForMed($med);
      }
    }
  }
}
class Text_Allerts extends Text {
  //
  public function __construct($allers) {
    $this->table = new TextTable('Substance', 'Reactions', 'Status');
    if (empty($allers)) {
      $this->table->add('None Known');
    } else {
      foreach ($allers as $aller) {
        $this->table->add($aller->agent, $aller->formatReactions(), $aller->formatActive());
        $this->table->td(0)->_ID = TEXT_REF::getForAller($aller);
      }
    }
  }
}
class Text_Vitals extends Text {
  //
  public function __construct($vitals, $metric = false) {
    if ($metric)
      $this->table = new TextTable('Date/Time', 'Pulse (BPM)', 'Resp (BPM)', 'Blood Pressure (mmHg)', 'Temp (F)', 'Weight (kg)', 'Height (cm)', 'BMI');
    else
      $this->table = new TextTable('Date/Time', 'Pulse (BPM)', 'Resp (BPM)', 'Blood Pressure (mmHg)', 'Temp (F)', 'Weight (lb)', 'Height (in)', 'BMI');
    foreach ($vitals as $vital)
      if ($metric)
        $this->table->add(formatDateTime($vital->date), $vital->pulse, $vital->resp, $vital->getBp(), $vital->temp, $vital->getWtKg(), $vital->getHtCm(), $vital->bmi);
      else
        $this->table->add(formatDateTime($vital->date), $vital->pulse, $vital->resp, $vital->getBp(), $vital->temp, $vital->getWtLb(), $vital->getHtIn(), $vital->bmi);
  }
}
class Text_Encounters extends Text {
  //
  public function __construct($procs) {
    $this->table = new TextTable('Date', 'Diagnoses');
    foreach ($procs as $proc) {
      $diags = array(); 
      foreach ($proc->Diagnoses as $diag) {
        $diags[] = $diag->text;
      }
      $this->table->add(formatDate($proc->date), implode(', ', $diags));
      $this->table->td(0)->_ID = TEXT_REF::getForProc($proc);
    }
  }
}
class Text_Immuns extends Text {
  //
  public function __construct($immuns) {
    $this->table = new TextTable('Vaccine', 'Date');
    foreach ($immuns as $immun) 
      $this->table->add($immun->name, formatApproxDate($immun->dateGiven));
  }
}
class Text_Problems extends Text {
  //
  public function __construct($diags) {
    $this->table = new TextTable('Diagnosis', 'Code', 'Date', 'Status');
    foreach ($diags as $diag) {
      $date = formatApproxDate($diag->date);
      if ($diag->dateClosed)
        $date .= ' - ' . formatApproxDate($diag->dateClosed);
      $td = $this->table->add($diag->text, $diag->snomed, $date, $diag->formatActive());
      $td->_ID = TEXT_REF::getForDiag($diag);
    }
  }
}
class Text_Procedures extends Text {
  //
  public function __construct($procs) {
    $this->table = new TextTable('Procedure', 'Date');
    if ($procs) {
      foreach ($procs as $proc) { 
        $this->table->add($proc->Ipc->name, formatApproxDate($proc->date));
        $this->table->td(0)->_ID = TEXT_REF::getForProc($proc);
      }
    } else {
      $this->table->add('No known procedures');
    }
  }
}
class Text_PlanOfCare extends XmlRec {
  //
  public function __construct($plan, $pending, $future, $followups, $referrals) {
    $this->list = array();
    $this->addCarePlan($plan);
    $this->addPendingTests($pending);
    $this->addFutureTests($future);
    $this->addAppts($followups, $referrals);
  }
  //
  protected function addCarePlan($procs) {
    $table = new TextTable('Date', 'Activity', 'Plan/Goal');
    if ($procs) {
      foreach ($procs as $proc) { 
        $table->add(formatApproxDate($proc->date), $proc->Ipc->name, $proc->comments);
        $table->td(1)->_ID = TEXT_REF::getForPlanOfCare($proc);
      }
    } else {
      $table->add('No known plan');
    }
    $this->list[] = TextList::create($table);
  }
  protected function addPendingTests($pending) {
    if ($pending) {
      $table = new TextTable('Test/Procedure');
      foreach ($pending as $track) {
        $table->add($track->Ipc->desc);
        $table->td(0)->_ID = TEXT_REF::getForTrackItem($track);
      }
      $this->list[] = TextList::create($table, 'Diagnostic Tests Pending');
    }
  }
  protected function addFutureTests($future) {
    if ($future) {
      $table = new TextTable('Date','Test/Procedure');
      foreach ($future as $track) {
        $table->add(formatDateTime($track->schedDate), $track->Ipc->desc);
        $table->td(1)->_ID = TEXT_REF::getForTrackItem($track);
      }
      $this->list[] = TextList::create($table, 'Future Scheduled Tests');
    }
  }
  protected function addAppts($followups, $referrals) {
    if ($followups || $referrals) {
      $table = new TextTable('Date','Type','Location');
      if ($referrals) {
        foreach ($referrals as $track) {
          $table->add(formatDateTime($track->schedDate), 'Referral', (isset($track->Provider) ? $track->Provider->formatName() . ' - ' : '') . (isset($track->Address) ? $track->Address->format() : ''));
          $table->td(0)->_ID = TEXT_REF::getForTrackItem($track);
        }
      }
      if ($followups) {
        foreach ($followups as $track) {
          $table->add(formatDateTime($track->schedDate), 'Follow-Up', (isset($track->Provider) ? $track->Provider->formatName() . ' - ' : '') . (isset($track->Address) ? $track->Address->format() : ''));
          $table->td(0)->_ID = TEXT_REF::getForTrackItem($track);
        }
      }
      $this->list[] = TextList::create($table, 'Future Appointments');
    }
  }
}
class Text_ReasonForVisit extends Text {
  //
  public function __construct($proc) {
    $this->table = new TextTable();
    $td = $this->table->add($proc->comments);
    $td->_ID = TEXT_REF::getForProc($proc);
  }
}
class Text_ReasonForReferral extends Text {
  //
  public function __construct($trackItem) {
    $this->table = new TextTable();
    $td = $this->table->add($trackItem->trackDesc);
    //$td->_ID = TEXT_REF::getForTrackItem($trackItem);
  }
}
class Text_Instructions extends Text {
  //
  public function __construct($instructs, $pda) {
    $this->table = new TextTable();
    if ($instructs) {
      foreach ($instructs as $i => $instruct) {
        $td = $this->table->add($instruct);
        $td->_ID = TEXT_REF::getForInstruct($i);
      }
    }
    if ($pda) {
      $this->table->add('PATIENT DECISION AIDS:');
      foreach ($pda as $i => $instruct) {
        $td = $this->table->add($instruct);
        $td->_ID = TEXT_REF::getForPda($i);
      }
    }
    for ($i = 0; $i < 3; $i++) {
      $this->table->add(' ');
    }
  }
}
class Text_Functional extends Text {
  //
  public function __construct($procs) {
    $this->table = new TextTable('Date', 'Status', 'Comments');
    foreach ($procs as $proc) { 
      $this->table->add(formatApproxDate($proc->date), $proc->Ipc->desc, $proc->comments);
      $this->table->td(1)->_ID = TEXT_REF::getForProc($proc);
    }
  }
}
class Text_Smoking extends Text {
  //
  public function __construct($proc) {
    $this->table = new TextTable('Date', 'Status', 'Comments');
    $this->table->add(formatApproxDate($proc->date), $proc->Ipc->name, $proc->comments);
    $this->table->td(1)->_ID = TEXT_REF::getForProc($proc);
  }
}
class Text_Social extends Text {
  //
  public function __construct($socs) {
    $this->table = new TextTable('Name', 'Comments');
    foreach ($socs as $soc) { 
      $this->table->add($soc->name, $soc->_values);
      $this->table->td(0)->_ID = TEXT_REF::getForSocial($soc);
    }
  }
}
class Text_Results extends Text {
  //
  public function __construct($procs) {
    $this->table = new TextTable('Test', 'Result', 'Date Performed');
    foreach ($procs as $proc) {
      if ($proc->isPanel()) {
        $td = $this->table->add($proc->Ipc->name);
        $td->_ID = TEXT_REF::getForProc($proc);
        $bullet = ' - ';
      } else {
        $bullet = '';
      }
      foreach ($proc->ProcResults as $result) { 
        $this->table->add($bullet . $result->Ipc->name, $result->summarizeResult($proc, $result, false), formatDate($proc->date));
        $this->table->td(0)->_ID = TEXT_REF::getForResult($result);
      }
    }
  }
}
//
class TextList extends XmlRec {
  //
  static function create($table, $title = null) {
    $me = new static();
    if ($title)
      $me->caption = $title;
    $me->item->table = $table;
    return $me;
  }
}
class TextSimpleList extends XmlRec {
  //
  static function create($item) {
    $me = new static();
    $me->item = $item;
    return $me;
  }
}
class TextTable extends XmlRec { 
  public $_border = '1';
  public $_width = '100%';
  //
  public $thead;
  public $tbody;
  //
  /**
   * @param 'col header 1','col header 2',..
   */
  public function __construct() {
    $ths = func_get_args();
    if ($ths)
      $this->thead = new TextTableThead($ths);
    $this->tbody = new TextTableTbody();
  }
  /**
   * @param 'col text 1','col text 2',..
   * @return last TD added
   */
  public function add() {
    $tds = func_get_args();
    $td = $this->tbody->add($tds);
    if (count($tds) == 1 && get($this, 'thead')) 
      $td->_colspan = count($this->thead->tr->th);
    return $td;
  }
  public function td($i) {
    $tr = end($this->tbody->tr);
    return $tr->td[$i];
  }
}
class TextTableThead extends XmlRec {
  public $tr;
  //
  public function __construct($ths) {
    $this->tr = new TextTableTheadTr($ths);
  }
}
class TextTableTheadTr extends XmlRec {
  public $th;
  //
  public function __construct($ths) {
    $this->th = $ths;
  }
}
class TextTableTbody extends XmlRec {
  public $tr;
  //
  public function __construct() {
    $this->tr = array();
  }
  public function add($tds) {
    $tr = new TextTableTbodyTr($tds);
    $this->tr[] = $tr;
    return end($tr->td);
  }
}
class TextTableTbodyTr extends XmlRec {
  public $td;
  //
  public function __construct($tds) {
    $this->td = array();
    foreach ($tds as $td) 
      $this->td[] = new TextTableTd($td); 
  }
}
class TextTableTd extends XmlRec {
  public $_;
  //
  public function __construct($td) {
    $this->_ = Text::denull($td, '&nbsp;');
  }
}
