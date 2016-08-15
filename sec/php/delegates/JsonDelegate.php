<?php
require_once "php/dao/JsonDao.php";
require_once "php/dao/SessionDao.php";

class JsonDelegate {

  // Return the default console map
  public static function jsonJDefaultMap($templateId, $noteDate = null) {
    $map = JsonDao::buildJDefaultMap($templateId, $noteDate);
    return $map->out();
  }

  public static function buildJSession($sessionId) {
    $s = JsonDao::buildJSession($sessionId);
    $s->meds = FacesheetDao::getMedPickerHistory($s->clientId);
    return $s;
  }

  public static function buildJTemplatePreset($tpid) {
    return SessionDao::getJTemplatePreset($tpid);
  }

  // Return a client session
  // NO LONGER USED
  public static function jsonJSession($sessionId) {
    $session = JsonDao::buildJSession($sessionId);
    return $session->out();
  }

  // Return a template
  public static function jsonJTemplate($templateId, $noteDate = null) {
    $template = JsonDao::buildJTemplate($templateId, $noteDate);
    return $template->out();
  }

  // Return a single JParInfo
  public static function jsonJParInfo($parId) {
    $par = JsonDao::oldBuildJParInfo($parId);
    return $par->out();
  }

  // Return an array of JParTemplates
  public static function jsonJParTemplates($sectionId) {
    $arr = JsonDao::buildJParTemplates($sectionId);
    return JsonDelegate::outArray($arr);
  }

  public static function getPars($id) {
    $pars = TemplateReaderDao::getPars($id);
    $a = array();
    foreach ($pars as $par) {
      $a[$par->id] = $par;
    }
    return jsonencode($a);
  }
  public static function getPars2($id) {
    $pars = TemplateReaderDao::getPars($id);
    return jsonencode($pars);
  }

  // Return just the question array portion of JParInfo
  public static function jsonJQuestions($parId) {
    $par = JsonDao::oldBuildJParInfo($parId);
    return JsonDelegate::outArray($par->questions);
  }

  // Return med search results
  public static function jsonJMeds($wildcard) {
    $meds = JsonDao::searchMeds($wildcard);
    return $meds->out();
  }

  // Return an array of JTemplates
  public static function jsonJTemplates() {
    $arr = JsonDao::buildJTemplates();
    return JsonDelegate::outArray($arr);
  }

  // Returns an associated array
  public static function outArray($arr) {
    $i = 0;
    $s = "{";
    foreach ($arr as $o) {
      $s .= "\"" . $o->id . "\":";
      $s .= $o->out();
      $i++;
      if ($i < count($arr)) $s .= ",";
    }
    return $s . "}";
  }

  public static function outSimpleArray($arr) {
    $s = "[";
    for ($i = 0; $i < sizeof($arr); $i++) {
      $s .= $arr[$i]->out();
      if ($i < sizeof($arr) - 1) $s .= ",";
    }
    return $s . "]";
  }
}
?>