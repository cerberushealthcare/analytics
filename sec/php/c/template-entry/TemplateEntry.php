<?php
require_once 'TemplateEntry_Recs.php';
require_once 'TemplateEntry_MyPar.php';
//
/**
 * Template Entry
 * @author Warren Hornsby
 */
class TemplateEntry {
  //
  /** Get paragraph plus my custom others */
  static function /*MyPar*/getMyPar($tid, $sid, $puid) {
    global $login;
    $par = MyPar::fetch($tid, $sid, $puid, $login->userId);
    return $par;
  }
  static function /*MyPar*/getMyPar_OrderEntry() {
    return static::getMyPar(30/*orderEntry*/, 168/*orders*/, '+orders');
  }
  static function /*MyPar*/getMyPar_ApptCard() {
    return static::getMyPar(35/*apptCard*/, 183/*apptCard*/, 'apptCard');
  }
  /** Get paragraph */
  static function /*TPar*/getPar($pid) {
    $par = TPar::fetch($pid);
    return $par;
  }
  /** Get question past med history */
  static function /*TQuestion*/getPmhxQuestion() {
    require_once 'php/c/template-entry/TemplateEntry_Hx.php';
    global $login;
    $question = TQuestion_Pmhx::fetch($login->userId);
    return $question;
  }
  /** Get question past surg history */
  static function /*TQuestion*/getPshxQuestion() {
    require_once 'php/c/template-entry/TemplateEntry_Hx.php';
    global $login;
    $question = TQuestion_Pshx::fetch($login->userId);
    return $question;
  }
  /** Save others customized for a question */
  static function saveCustomOthers($json) {
    global $login;
    TOther::saveFromUi($login->userId, $json);
  }
  /** Get templates */
  static function /*TTemplate[]*/getTemplates() {
    global $login;
    $templates = TTemplate::fetchAll($login->userGroupId);
    return $templates;
  }
  /** Get map */
  static function /*Template_Map*/getMap($tid) {
    require_once 'php/data/rec/sql/Templates_Map.php';
    $map = Templates_Map::get($tid);
    return $map;
  }
  /** Get paragraph + conditionless injects in same section */
  static function /*TPar[]*/getParWithInjects($pid) {
    global $login;
    $pars = TPars::fetchAll($pid, $login->userId);
    return $pars;
  }

}