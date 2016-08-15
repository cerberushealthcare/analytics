<?php
require_once 'php/data/json/_util.php';

class JDataImmun {
  //
  public $id;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;     // '2009-05-20'
  public $dateText; // 'Today'
  public $dateCal;  // '02-Jun-2009'
  public $name;
  public $manufac;
  public $tradeName;
  public $lot;
  public $dateExp;
  public $dateVis;
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  public $comment;
  public $updated;
  //
  const SQL_FIELDS = 'data_immun_id, user_group_id, client_id, session_id, date_given, name, manufac, trade_name, lot, date_exp, date_vis, dose, route, site, admin_by, comment, date_updated';
  /**
   * Constructor
   */
  public function __construct($id, $userGroupId, $clientId, $sessionId, $date, $name, $manufac, $tradeName, $lot, $dateExp, $dateVis, $dose, $route, $site, $adminBy, $comment, $dateUpdated) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->sessionId = $sessionId;
    $this->date = dateToString($date);
    $this->dateText = formatInformalDate($date, true);
    $this->dateCal = formatDate($date);
    $this->name = $name;
    $this->manufac = $manufac;
    $this->tradeName = $tradeName;
    $this->lot = $lot;
    $this->dateExp = formatConsoleDate($dateExp);
    $this->dateVis = formatConsoleDate($dateVis);
    $this->dose = $dose;
    $this->route = $route;
    $this->site = $site;
    $this->adminBy = $adminBy;
    $this->comment = $comment;
    $this->dateUpdated = $dateUpdated;
  }
  /**
   * @return string JSON
   */
  public function out() {
    $out = '';
    $out = nqq($out, 'id', $this->id);
    $out = nqq($out, 'clientId', $this->clientId);
    $out = nqq($out, 'sessionId', $this->sessionId);
    $out = nqq($out, 'date', $this->date);
    $out = nqq($out, 'dateText', $this->dateText);
    $out = nqq($out, 'dateCal', $this->dateCal);
    $out = nqq($out, 'name', $this->name);
    $out = nqq($out, 'manufac', $this->manufac);
    $out = nqq($out, 'tradeName', $this->tradeName);
    $out = nqq($out, 'lot', $this->lot);
    $out = nqq($out, 'dateExp', $this->dateExp);
    $out = nqq($out, 'dateVis', $this->dateVis);
    $out = nqq($out, 'dose', $this->dose);
    $out = nqq($out, 'route', $this->route);
    $out = nqq($out, 'site', $this->site);
    $out = nqq($out, 'adminBy', $this->adminBy);
    $out = nqq($out, 'comment', $this->comment);
    return cb($out);
  }
  private function q($s) {
    return quote($s);
  }
  private function qd($s) {
    return quoteDate($s);
  }
  /**
   * @return string Built SQL UPDATE statement 
   */
  public function getSqlUpdate() {
    $sql = <<<eos
UPDATE data_immuns SET 
date_given={$this->qd($this->date)},
name={$this->q($this->name)},
trade_name={$this->q($this->tradeName)},
manufac={$this->q($this->manufac)},
lot={$this->q($this->lot)},
date_exp={$this->qd($this->dateExp)},
date_vis={$this->qd($this->dateVis)},
dose={$this->q($this->dose)},
route={$this->q($this->route)},
site={$this->q($this->site)},
admin_by={$this->q($this->adminBy)},
comment={$this->q($this->comment)}
WHERE data_immun_id=$this->id
eos;
    return $sql;
  }
  /**
   * @return string Built SQL INSERT statement
   */
  public function getSqlInsert() {
    $sql = <<<eos
INSERT INTO data_immuns VALUE(
NULL,
{$this->userGroupId},
{$this->clientId},
NULL,
{$this->qd($this->date)},
{$this->q($this->name)},
{$this->q($this->tradeName)},
{$this->q($this->manufac)},
{$this->q($this->lot)},
{$this->qd($this->dateExp)},
{$this->qd($this->dateVis)},
{$this->q($this->dose)},
{$this->q($this->route)},
{$this->q($this->site)},
{$this->q($this->adminBy)},
{$this->q($this->comment)},
NULL)
eos;
    return $sql;
  }
  public static function fromRows($res, $assocBy = null) {
    $immuns = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataImmun::fromRow($row);
      if ($assocBy != null) {
        $immuns[$row[$assocBy]] = $rec;
      } else {
        $immuns[] = $rec;
      }
    }
    return $immuns;
  }
  /**
   * Static creator
   * @param $row: from DATA_IMMUN
   * @return JDataImmun
   */
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataImmun(
      $row['data_immun_id'],
      $row['user_group_id'],
      $row['client_id'],
      $row['session_id'],
      $row['date_given'],
      $row['name'],
      $row['manufac'],
      $row['trade_name'],
      $row['lot'],
      $row['date_exp'],
      $row['date_vis'],
      $row['dose'],
      $row['route'],
      $row['site'],
      $row['admin_by'],
      $row['comment'],
      $row['date_updated']
      );
  }
  /**
   * Static creator
   * @param $rec: from facesheet save data object
   * @return JDataImmun
   */
  public static function fromFacesheet($rec, $ugid) {
    return new JDataImmun(
      get($rec, 'id'),
      $ugid,
      $rec->cid,
      null,
      $rec->dateGiven,
      $rec->name,
      get($rec, 'manufac'),
      get($rec, 'tradeName'),
      get($rec, 'lot'),
      get($rec, 'dateExp'),
      get($rec, 'dateVis'),
      get($rec, 'dose'),
      get($rec, 'route'),
      get($rec, 'site'),
      get($rec, 'adminBy'),
      get($rec, 'comment'),
      null);
  }
  /**
   * SQL functions
   */
  public static function save($dto, $audit = true) {
    if ($dto->id != null) {
      LoginDao::authenticateDataImmun($dto->id);
      $sql = $dto->getSqlUpdate();
      query($sql);
      if ($audit) 
        AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_IMMUN, $dto->id, AuditDao::ACTION_UPDATE, null, formatDate($dto->date));
    } else {
      LoginDao::authenticateClientId($dto->clientId);
      $sql = $dto->getSqlInsert();
      $dto->id = insert($sql);
      if ($audit) 
        AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_IMMUN, $dto->id, AuditDao::ACTION_CREATE, null, formatDate($dto->date));
    }
    return $dto;
  }
  public static function delete($id, $audit = true) {
    LoginDao::authenticateDataImmun($dto->id);
    query("DELETE FROM data_immuns WHERE data_immun_id=" . $id);
    if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_IMMUN, $dto->id, AuditDao::ACTION_DELETE);
  }
  public static function getFacesheetImmuns($clientId) {
    $sql = "SELECT " . JDataImmun::SQL_FIELDS . " FROM data_immuns WHERE client_id=" . $clientId . " AND session_id IS NULL ORDER BY date_given DESC";
    return JDataImmun::fromRows(query($sql));
  }
}
?>