<?php
require_once "php/forms/Form.php";
require_once "php/data/ui/Anchor.php";
require_once "php/data/ui/Tag.php";
require_once "php/dao/SchedDao.php";
require_once "php/forms/utils/CommonCombos.php";
require_once "php/data/Military.php";
require_once 'php/data/rec/sql/Dashboard.php';
//
class SchedForms {
  //
  public /*SchedForm[]*/$forms;
  public /*SchedForm*/$form;  // first one
  public $userId;
  public $docs;
  //
  const USERID_ALL = -1;
  //
  static function create() {
    $userId = Form::getFormVariable("u");
    if ($userId == null)
      $userId = Dashboard::getKeys()->apptDoctor;
    $me = new static();
    $me->userId = $userId;
    $me->docs = static::getDocs();
    $me->forms = static::makeForms($userId, $me->docs);
    $me->form = $me->forms[0];
    if ($me->userId == null)
      $me->userId = $me->form->userId;
    return $me;
  }
  static function getDocs() {
    $docs = CommonCombos::docs();
    $names = LookupDao::getSchedNames();
    if ($names) {
      foreach ($names as $id => $name) {
        if (isset($docs[$id]))
          $docs[$id] = $name;
      }
      asort($docs);
    }
    if (count($docs) > 1)
      $docs[-1] = '(Show All)';
    return $docs;
  }
  static function makeForms($userId, $docs) {
    $forms = array();
    if ($userId != static::USERID_ALL) {
      $forms[] = new SchedForm($userId, $docs);
    } else {
      foreach ($docs as $id => $name) {
        if ($id != static::USERID_ALL)
          $forms[] = new SchedForm($id, $docs);
      }
    }
    return $forms;
  }
}

class SchedForm extends Form {

  // Form props
  public $view;  // default VIEW_DAY
  public $userId;  // of doctor whose schedule we're on
  public $date;
  public $popId;  // SCHED to auto-pop
  public $popAsEdit;  // true to auto-edit popId
  public $popCal;  // true to show calendar
  public $sid;  // auto-search client ID (to default search results)
  public $schedProfileAsJson;  // for customizing

  // Retrieved schedule
  public $page;  // SchedPage, schedule from DAO
  public $title;  // Tag, page title
  public $columnHeads;  // ColHead[], one for each column in each slot, e.g. "Monday, June 23"
  public $rows;  // SchedRow[], array of schedule rows
  public $anchorPrev;  // Anchor, previous link
  public $anchorNext;  // Anchor, next link

  // Combos
  public $sexes;
  public $states;
  //public $slotTimeHrs;
  //public $slotTimeMins;
  //public $slotTimeAmPm;
  public $slotLengthHrs;
  public $slotLengthMins;
  public $phoneTypes;
  public $statuses;
  public $types;
  public $docs;  // other doctors in the practice
  public $templates;
  public $templatePresets;
  public $noTemplatePresets;  // boolean

  // Helpers
  public $slots;  // Array of SchedSlots included in the view

  const VIEW_DAY_SINGLE_USER = 0;
  const VIEW_WEEK_SINGLE_USER = 1;
  const VIEW_DAY_MULTI_USERS = 2;

  const FEMALE = "F";
  const MALE = "M";

  public function __construct($userId = null, $docs = null) {
    $this->docs = $docs;
    $this->loadCombos();
    $this->setFormProps($userId);
    $this->readSched();
    //$this->buildSlotTimes();
    $this->buildSlotLengths();
  }

  private function setFormProps($userId = null) {
    global $login;

    // Assign default user ID: for doctors, self; for others, first doctor in practice
    if ($this->docs == null)
      $this->docs = CommonCombos::docs();
    if ($userId) {
      $docUserId = $userId;
    } else if ($login->User->isDoctor()) {
      $docUserId = $login->userId;
    } else {
      $docUserId = key($this->docs);
    }
    $this->view = Form::getFormVariable("v", SchedForm::VIEW_DAY_SINGLE_USER);
    $this->userId = $userId ?: Form::getFormVariable("u", $docUserId);
    $this->date = dateToString(Form::getFormVariable("d", date("Y-m-d")));
    $this->popId = Form::getFormVariable("pop");
    $this->popAsEdit = Form::getFormVariable("pe", "null");
    $this->sid = Form::getFormVariable("sid");
    if ($this->popId != null) {
      $s = SchedDao::getJSched($this->popId);
      if ($userId == null)
        $this->userId = $s->userId;
      $this->date = dateToString($s->date);
    }
    $this->popCal = (Form::getFormVariable("pc") == "1");
    $this->schedProfileAsJson = LookupDao::getSchedProfileAsJson($this->userId);
  }

  private function readSched() {
    if ($this->view == SchedForm::VIEW_DAY_SINGLE_USER) {
      $this->page = SchedDao::getSchedPageForDay($this->date, $this->userId);
      $ts = strtotime($this->date);
      $this->anchorPrev = $this->buildAnchor(strtotime("-1 day", $ts));
      $this->anchorNext = $this->buildAnchor(strtotime("1 day", $ts));
    } else if ($this->view == SchedForm::VIEW_WEEK_SINGLE_USER) {
      $this->page = SchedDao::getSchedPageForWeek($this->date, $this->userId);
      $ts = strtotime($this->page->days[0]->date);
      $this->anchorPrev = $this->buildAnchor(strtotime("-1 week", $ts));
      $this->anchorNext = $this->buildAnchor(strtotime("1 week", $ts));
    }
    $this->title = $this->buildTitle($this->date);
    $this->columnHeads = $this->buildColumnHeads();
    $this->rows = $this->buildRows();
  }

  private function loadCombos() {
    $this->sexes = CommonCombos::sexes();
    $this->states = CommonCombos::states();
    $this->phoneTypes = CommonCombos::phoneTypes();
    $this->statuses = CommonCombos::schedStatus();
    $this->types = CommonCombos::apptTypes();
    //$this->docs = CommonCombos::docs();
    //$this->docs[-1] = '(Show All)';
    $this->templates = CommonCombos::myTemplates();
    $o = CommonCombos::myTemplatePresets();
    $this->templatePresets = $o["combo"];
    $this->noTemplatePresets = $o["noPresets"];
  }

  private function buildSlotTimes() {
    $hrs = array();
    $mins = array();
    $ampm = array();
    foreach ($this->slots as $slot) {
      $m = new Military($slot->military);
      // TODO optimize?
      $hrs[$m->standardHour] = $m->standardHour;
      $min = $m->formattedMin();
      $mins[$min] = $min;
    }
    $ampm["AM"] = "AM";
    $ampm["PM"] = "PM";
    $this->slotTimeHrs = $hrs;
    $this->slotTimeMins = $mins;
    $this->slotTimeAmPm = $ampm;
  }

  private function buildSlotLengths() {
    $start = intval($this->page->profile->slotStart) / 100;
    $end = intval($this->page->profile->slotEnd) / 100;
    $length = ($end - $start) * 12;
    $hrs = array();
    $mins = array();
    $hr = 0;
    $min = 0;
    $hrs[$hr] = $this->plural($hr, "hour");
    $mins[$min] = $this->plural($min, "minute");
    for ($i = 0; $i < $length; $i++) {
      $min += 5;
      if ($min >= 60) {
        $hr++;
        $min -= 60;
      }
      $hrs[$hr] = $this->plural($hr, "hour");
      $mins[$min] = $this->plural($min, "minute");
    }
    $this->slotLengthHrs = $hrs;
    $this->slotLengthMins = $mins;
  }

  private function plural($value, $noun) {
    if ($value == 1) {
      return "1 " . $noun;
    } else {
      return $value . " " . $noun . "s";
    }
  }

  private function buildAnchor($ts) {
    $href = $this->formatUrl($this->view, date("Y-m-d", $ts), $this->userId);
    if ($this->view == SchedForm::VIEW_WEEK_SINGLE_USER) {
      $text = "Week of " . $this->formatDate(date("Y-m-d", $ts), 2);
    } else {
      $text = $this->formatDate(date("Y-m-d", $ts), 4);
    }
    return new Anchor($href, $text);
  }

  private function buildTitle($date) {
    $title = new Tag();
    if ($this->view == SchedForm::VIEW_WEEK_SINGLE_USER) {
      $title->text = "Week of " . $this->formatDate($this->page->days[0]->date, 2) . "-" . $this->formatDate($this->page->days[sizeof($this->page->days) - 1]->date, 2);
    } else {
      $title->text = $this->formatDate($date);
      if ($this->page->days[0]->today) {
        $title->class = "today";
      }
    }
    return $title;
  }

  // $fmt=0, returns "Monday, June 30, 2008"
  // $fmt=1, returns "Mon 6/30"
  // $fmt=2, returns "Jun 30"
  // $fmt=3, returns "Monday, June 30"
  // $fmt=4, returns "2008-06-30"
  private function formatDate($date, $fmt = 0) {
    $ts = strtotime($date);
    if ($fmt == 0 || $fmt == 4) {
      $dow = date("l", $ts);
      $month = date("F", $ts);
      $day = date("j", $ts);
      $year = date("Y", $ts);
      $text = $dow . ", " . $month . " " . $day;
      if ($fmt == 0) {
        $text .= ", " . $year;
      }
      return $text;
    } else if ($fmt == 1) {
      $dow = substr(date("l", $ts), 0, 3);
      $month = date("n", $ts);
      $day = date("d", $ts);
      return $dow . " " . $month . "/" . $day;
    } else if ($fmt == 2) {
      $month = date("M", $ts);
      $day = date("j", $ts);
      return $month . " " . $day;
    } else {
      $month = date("m", $ts);
      $day = date("d", $ts);
      $year = date("Y", $ts);
      return $year . "-" . $month . "-" . $day;
    }
  }

  private function buildRows() {
    $slots = array();  // this and next accumulates SchedSlots for easy access when they are spread across bicolumns
    $slots2 = array();
    $rows = array();
    $first = true;
    $offH = false;
    $offM = false;
    $slotEnd = $this->page->profile->slotEnd;
    $slotSize = $this->page->profile->slotSize;
    $status = LookupDao::getSchedStatus();
    $bicolumnSlotOffset = 0;
    if ($this->view == SchedForm::VIEW_DAY_SINGLE_USER) {
      // Split single-user day view into bicolumns
      $bicolumnSlotOffset = Military::div(($slotEnd - $this->page->profile->slotStart) / 2 + 50, 100) * 100;
      $slotEnd = $this->page->profile->slotStart + $bicolumnSlotOffset;
    }
    $milEnd = new Military($slotEnd);
    $milStart = new Military($this->page->profile->slotStart);
    $totalSlots = $milEnd->minus($milStart) / $slotSize;
    $slotCt = 0;
    for ($h = $this->page->profile->slotStart; $h < $slotEnd; $h += 100) {
      for ($m = 0; $m < 60; $m += $this->page->profile->slotSize) {
        $slotCt++;
        $slotsLeft = $totalSlots - $slotCt + 1;
        $military = $h + $m;
        $trClass = "";
        if ($first) {
          $trClass = "first";
          $first = false;
        } else {
          if ($offH) {
            $trClass = "off-h ";
          }
          if ($offM) {
            $trClass .= "off-m";
          }
        }
        $row = new SchedRow($trClass);
        $row->slot = new SchedSlot($military, $this->page->profile->slotSize);
        $slots[] = $row->slot;
        if ($bicolumnSlotOffset > 0) {
          // Call twice for each split column
          $columns = $this->buildColumns($military, -1, $slotsLeft, $status);  // -1 indicates special width
          $military2 = $military + $bicolumnSlotOffset;
          if ($military2 < $this->page->profile->slotEnd) {
            $columns2 = $this->buildColumns($military2, -1, $slotsLeft, $status);
            $column2 = $columns2[0];
            $column2->slot = new SchedSlot($military2, $this->page->profile->slotSize);  // create a SchedSlot for second column (to render hour/min)
            $slots2[] = $column2->slot;
            $columns[] = $column2;
          }
          $row->columns = $columns;
       } else {
          $slotsLeft =
          $row->columns = $this->buildColumns($military, sizeof($this->columnHeads), $slotsLeft, $status);
        }
        $rows[] = $row;
        $offM = ! $offM;
      }
      $offH = ! $offH;
    }
    $this->handleSpills($rows);
    $this->handleConflicts($rows);
    $this->slots = array_merge($slots, $slots2);  // save helper array
    return $rows;
  }

  private function handleSpills($rows) {

    // For bicolumn view, check for appoints in first column that spill over to second column
    // If so, create clones of these and append first row, second column
    if ($this->view == SchedForm::VIEW_DAY_SINGLE_USER) {
      foreach ($rows as $row) {
        $appts = $row->columns[0]->appts;
        foreach ($appts as $appt) {
          if ($appt->spilledDuration > 0) {
            $clone = clone $appt;
            $clone->duration = $appt->spilledDuration;
            $clone->aText .= " (con't)";
            $rows[0]->columns[1]->appts[] = $clone;
          }
        }
      }
    }
  }

  private function handleConflicts($rows) {

    // Check each slot of each column/row for appts that share same slot
    // Calculate what each appt's conflictCount and conflictIndex should be for proper rendering
    // This should be done AFTER spills have been handled
    $conflictRows = $this->buildConflictRows($rows);
    for ($r = 0; $r < sizeof($rows); $r++) {
      $row = $rows[$r];
      $conflictRow = $conflictRows[$r];
      for ($c = 0; $c < sizeof($row->columns); $c++) {
        $col = $row->columns[$c];
        $conflictCol = $conflictRow->cols[$c];
        foreach ($col->appts as $appt) {
          // Copy colum's appt to first available spot in conflict column's appt array
          for ($a = 0; $a < sizeof($conflictCol->appts); $a++) {
            if ($conflictCol->appts[$a] == null) {
              break;
            }
          }
          if ($a < sizeof($conflictCol->appts)) {
            $conflictCol->appts[$a] == $appt;
          } else {
            $conflictCol->appts[] = $appt;
          }
          $appt->conflictIndex = $a;
          $appt->conflictCount = sizeof($conflictCol->appts);
          // Ensure each appt in the row have same conflictCount
          foreach ($conflictCol->appts as $cAppt) {
            $cAppt->conflictCount = sizeof($conflictCol->appts);
          }
          // Copy this appt to col->appts[$a] thruout rows of duration
          for ($i = 1; $i < $appt->duration; $i++) {
            $cRow = $conflictRows[$r + $i];
            $cCol = $cRow->cols[$c];
            if (sizeof($cCol->appts) < $appt->conflictCount) {  // make room if spot not available
              for ($j = sizeof($cCol->appts); $j < $appt->conflictCount; $j++) {
                $cCol->appts[] = null;
              }
            }
            $cCol->appts[$a] = $appt;
          }
        }
      }
    }
  }

  private function buildConflictRows($rows) {
    $conflictRows = array();
    foreach ($rows as $row) {
      $conflictRows[] = new ConflictRow($row);
    }
    return $conflictRows;
  }

  private function buildColumnHeads() {
    $heads = array();
    $first = true;
    if ($this->view == SchedForm::VIEW_DAY_MULTI_USERS) {
      $day = $this->page->days[0];
      foreach ($day->users as $user) {
        $heads[] = new ColHead($user->userId, $day->date, $first, false, $this->formatUrl(SchedForm::VIEW_DAY_SINGLE_USER, $day->date, $user->userId), $user->name);
        $first = false;
      }
    } else if ($this->view == SchedForm::VIEW_DAY_SINGLE_USER) {
        $day = $this->page->days[0];
        $user = $day->users[0];
        $head = new ColHead($user->userId, $day->date, $first, false, $this->formatUrl(SchedForm::VIEW_WEEK_SINGLE_USER, $day->date, $user->userId), "View entire week");
        $heads[] = $head;
    } else {
      foreach ($this->page->days as $day) {
        $user = $day->users[0];
        $heads[] = new ColHead($user->userId, $day->date, $first, $day->today, $this->formatUrl(SchedForm::VIEW_DAY_SINGLE_USER, $day->date, $user->userId), $this->formatDate($day->date, 1));
        if ($day->today) {
          $this->title->class = "today";
        }
        $first = false;
      }
    }
    return $heads;
  }

  private function formatUrl($view, $date, $userId) {
    $url = "schedule.php?v=" . $view . "&d=" . $date . "&u=" . $userId;
    if ($this->sid) {
      $url .= "&sid=" . $this->sid;
    }
    return $url;
  }

  // Returns current URL without doc ID
  public function formatCurrentUrl($withDate = true, $withSid = true) {
    $url = "schedule.php?v=" . $this->view . "&u=" . $this->userId;
    if ($withSid && $this->sid) {
      $url .= "&sid=" . $this->sid;
    }
    if ($withDate) {
      $url .=  "&d=" . $this->date;
    }
    return $url;
  }

  private function buildColumns($military, $colTotal, $slotsLeft, $status) {
    $cols = array();
    if ($this->view == SchedForm::VIEW_DAY_MULTI_USERS) {
      $day = $this->page->days[0];
      foreach ($day->schedUsers as $user) {
        $cols[] = $this->buildColumn($user, $military, $colTotal, $slotsLeft, $status);
      }
    } else {
      foreach ($this->page->days as $day) {
        $user = $day->users[0];
        $cols[] = $this->buildColumn($user, $military, $colTotal, $slotsLeft, $status);
      }
    }
    return $cols;
  }

  private function buildColumn($user, $military, $colTotal, $slotsLeft, $status) {
    $column = new SchedSlotColumn($colTotal, $status);
    foreach ($user->scheds as $sched) {
      $diff = $sched->timeStart - $military;
      if ($diff >= 0 && $diff < $this->page->profile->slotSize) {  // ensure sched's time start falls in slot's time span
        $column->addAppt($sched, $this->page->profile->slotSize, $slotsLeft, get($this->page->profile, 'labelFormat', 0));
      }
    }
    return $column;
  }
}

// Schedule table row
class SchedRow {

  public $trClass;  // e.g. "off-h offm"
  public $slot;  // SchedSlot

  // Collections
  public $columns;  // SchedSlotColumn[]

  public function __construct($trClass) {
    $this->trClass = $trClass;
  }
}

// One row for each timeslot
class SchedSlot {

  public $military;  // e.g. 1400
  public $hour;  // e.g. 2
  public $min;  // e.g. "00"
  public $amPm;
  public $rowSpan;  // for hour's TH cell, if applicable for row (otherwise zero)
  public $formatted;

  public function __construct($military, $slotSize) {
    $m = new Military($military);
    $this->military = $military;
    $this->formatted = $m->formatted();
    $this->hour = $m->standardHour;
    $this->min = $m->formattedMin();
    $this->amPm = $m->amPm();
    $this->columns = array();
    if ($m->min == 0) {
      $this->rowSpan = 60 / $slotSize;
    } else {
      $this->rowSpan = 0;
    }
  }
}

// One column for each SchedUser
class SchedSlotColumn {

  public $colTotal;  // used for calculating col width
  public $slot;  // SchedSlot (non-null only for second day view column)
  public $style;

    // Collections
  public $appts;  // collection of SchedSlotAppt (reformatted Sched) that start at this timeslot

  private $status;

  public function __construct($colTotal, $status) {
    $this->colTotal = $colTotal;
    $this->status = $status;
    $this->appts = array();
    $this->style = $this->calcStyle();
  }

  public function addAppt($sched, $slotSize, $slotsLeft, $labelFormat) {

    // Determine "visible" duration (don't allow overlap beyond schedule page
    $duration = $sched->duration / $slotSize;
    $leftoverDuration = 0;
    if ($duration > $slotsLeft) {
      $leftoverDuration = $duration - $slotsLeft;
      $duration = $slotsLeft;
    }
    $appt = new SchedSlotAppt($sched, $duration, $leftoverDuration, $this->colTotal, $this->status, $labelFormat);
    $this->appts[] = $appt;
    return $appt;
  }

  public static function calcWidth($colTotal) {
    switch ($colTotal) {
      case 1:
        return 676;
        break;
      case 2:
        return 331;
        break;
      case 3:
        return 216;
        break;
      case 4:
        return 158;
        break;
      case 5:
        return 124;
        break;
      case 6:
        return 101;
        break;
      case 7:
        return 85;
        break;
      case -1:  // bicolumn single day view
        return 291;
        break;
    }
  }

  private function calcStyle() {
    $width = SchedSlotColumn::calcWidth($this->colTotal) + 4 - 10;  // 4px for padding around appts, -10 to account for box padding
    return "width:" . $width . "px";
  }
}

class SchedSlotAppt {

  public $sched;  // Sched
  public $duration;  // in slotSize units, e.g. 1 (for 30 minute)
  public $spilledDuration;  // leftover duration that cannot be rendered within allowable slots
  public $colTotal;  // used for calculating slot width
  public $conflictCount;  // number of appt spaces sharing this slot
  public $conflictIndex;  // shared appt space index for this appt

  // Helpers
  public $aClass;  // for schedule's A tag
  public $aText;  // text of schedule's A tag

  private $status;  // sched status lookup

  public function __construct($sched, $duration, $spilledDuration, $colTotal, $status, $labelFormat) {
    $this->sched = $sched;
    $this->status = $status;
    $this->duration = $duration;
    $this->spilledDuration = $spilledDuration;
    $this->colTotal = $colTotal;
    $this->aClass = $this->calcAClass();
    $this->aText = $this->calcAText($labelFormat);
  }

  public function getAStyle() {
    $height = 18 + ($this->duration - 1) * 24;
    $width = SchedSlotColumn::calcWidth($this->colTotal) * 0.75;
    if ($this->sched->Client) {
      $color = "#dedede";
      if ($this->sched->status != "") {
        if (isset($this->status[$this->sched->status]->bcolor)) {
          if ($this->status[$this->sched->status]->active) {
            $color = $this->status[$this->sched->status]->bcolor;
          }
        }
      }
      $style = "background-color:" . $color;
    }
    if ($this->conflictCount > 1) {
      $splitWidth = round(($width - 10) / $this->conflictCount) - 1;
      $splitMargin = ($splitWidth + 6) * $this->conflictIndex;
      $style .= ";height:" . $height . "px;width:" . $splitWidth . "px;margin-left:" . $splitMargin . "px;";
    } else {
      $style .= ";height:" . $height . "px;width:" . $width . "px;";
    }
    return $style;
  }
  private function calcAClass() {
    // return ($this->sched->client->sex) ? "appt male" : "appt female";
    //$s = $this->sched->status;
    //if ($s == null || $s == Sched::STATUS_LATE_MINOR_SEEN || $s == Sched::STATUS_LATE_SEVERE_SEEN || $s == Sched::STATUS_ARRIVED) {
    //  return "appt";
    //} else {
    //  return "appt cancelled";
    //}
    if ($this->sched->Client) {
      return "appt";
    } else {
      return "appt unavail";
    }
  }
  private function calcAText($labelFormat) {
    if ($this->sched->Client) {
      if ($labelFormat == '1') {
        $c = $this->sched->Client;
        $lbl = "<b>" . $c->lastName . ", " . substr($c->firstName, 0, 1) . "</b>";
        $phone = getr($c, 'Address.phone1');
        if ($phone) {
          $lbl .= " <small>(" . $phone . ")</small>";
        }
        return $lbl;
      } else {
        return $this->sched->Client->getFullName();
      }
    } else {
      return $this->sched->comment;
    }
  }
}

class ColHead {

  public $userId;
  public $date;
  public $class;
  public $anchor;

  // $first and $sel are boolean
  public function __construct($userId, $date, $first, $sel, $href, $text) {
    $this->userId = $userId;
    $this->date = $date;
    $class = $first ? "head first" : "head";
    if ($sel) {
      $class .= " sel";
    }
    $this->class = $class;
    $this->anchor = new Anchor($href, $text);
  }
}

class ConflictRow {

  public $cols;  // ConflictCol[]

  // Construct from SchedRow
  public function __construct($row) {
    $this->cols = array();
    foreach ($row->columns as $column) {
      $this->cols[] = new ConflictCol();  // reserve space for each column
    }
  }
}

class ConflictCol {

  public $appts;  // pointers to SchedForm's SchedSlotAppt entries

  public function __construct() {
    $this->appts = array();
  }
}