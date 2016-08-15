<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Auditing.php';
require_once 'php/c/reporting/Reporting.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php
class ReportCriteria_Audit extends ReportCriteria {
  //
  static function from($ugid) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->name = 'Audit Report';
    $me->type = self::TYPE_AUDIT;
    $me->countBy = self::COUNT_BY_PATIENT;
    return $me;
  }
  static function forImmun($ugid, $cid, $immunId) {
    $me = static::from($ugid);
    $me->Rec = new RepCrit_Audit();
    $me->Rec->clientId = RepCritValue::asEquals($cid);
    $me->Rec->recName = RepCritValue::asEquals('Immun');
    $me->Rec->recId = RepCritValue::asEquals('287'); 
    return $me;
  }
}
switch ($_GET['t']) {
  case '1':
    $rec = AuditRec::fetch(39326);
    p_r($rec);
    p_r($rec->hash());
    exit;
  case '2':
    $rec = AuditRec::fetch(39326);
    $rec->after = 'changed';
    p_r($rec);
    p_r($rec->hash());
  case '3':
    $rec = AuditRec::fetch(39327);
    p_r($rec);
    p_r($rec->hash());
  case '10':
    $ugid = 1;
    $cid = 3817;
    $immunId = 287;
    $rc = ReportCriteria_Audit::forImmun($ugid, $cid, $immunId);
    p_r($rc);
    exit;
  case '11':
    $ugid = 1;
    $cid = 3817;
    $immunId = 287;
    $rc = ReportCriteria_Audit::forImmun($ugid, $cid, $immunId);
    $rc->load();
    p_r($rc);
    exit;
  case '12':
    $cid = 3817;
    $immunId = 287;
    $recs = Reporting::fetchImmunAudits($cid, $immunId);
    p_r($recs);
  case '13':
    $cid = 3817;
    $immunId = 256;
    $recs = Reporting::fetchImmunAudits($cid, $immunId);
    p_r($recs);
}
?>
</html>


