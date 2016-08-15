<?php
require_once "php/dao/_util.php";
require_once "php/dao/LookupDao.php";

class LookupAdminDao {

  public static function getLevels() {
    return array(
        LookupDao::LEVEL_APP   => "Application",
        LookupDao::LEVEL_GROUP => "Group",
        LookupDao::LEVEL_USER  => "User"
        );
  }

  /*
   * Returns [[field=>value,field=>value,..],..]
   */
  public static function getAppLookupData() {
    $sql = "SELECT t.name AS TABLE_NAME, d.* FROM lookup_tables t, lookup_data d WHERE t.name NOT LIKE '*%' AND t.lookup_table_id=d.lookup_table_id AND d.level='A' ORDER BY t.name, d.instance";
    return fetchArray($sql);
  }

  /*
   * Returns [ldid=>[field=>value,field=>value,..],ldid=>..]
   */
  public static function getLookupDataForTable($ltid) {
    $sql = LookupAdminDao::buildLookupSql($ltid, LookupDao::LEVEL_APP, "'APPLICATION'")
        . " UNION " . LookupAdminDao::buildLookupSql($ltid, LookupDao::LEVEL_GROUP, "g.name", "INNER JOIN user_groups g ON g.user_group_id=d.level_id")
        . " UNION " . LookupAdminDao::buildLookupSql($ltid, LookupDao::LEVEL_USER, "u.name", "INNER JOIN users u ON u.user_id=d.level_id")
        . " ORDER BY level, level_name, instance";
    $rows = fetchArray($sql, "LOOKUP_DATA_ID");
    foreach ($rows as &$row) {
      $row["DATA"] = jsondecode($row["DATA"]);
    }
    return $rows;
  }

  /*
   * Save object from UI to database
   * Returns ltid
   */
  public static function saveLookupData($o) {
    $o->DATA = jsonencode($o->DATA);
    if ($o->LOOKUP_DATA_ID == null) {
      LookupAdminDao::insertLookupData($o);
    } else {
      LookupAdminDao::updateLookupData($o);
    }
    return $o->LOOKUP_TABLE_ID;
  }

  /*
   * Delete requested ldid from database
   */
  public static function deleteLookupData($id) {
    $sql = "DELETE FROM lookup_data WHERE lookup_data_id=$id";
    query($sql);
  }

  /*
   * User lookups
   */
  public static function getUsersByName($name) {
    $sql = LookupAdminDao::actionSelect()
        . "WHERE u.name LIKE '%$name%' ORDER BY u.user_group_id, u.user_id";
    return fetchArray($sql);
  }
  public static function getUsersByUgid($ugid) {
    $sql = LookupAdminDao::actionSelect()
        . "WHERE u.user_group_id=$ugid ORDER BY u.user_id";
    return fetchArray($sql);
  }
  public static function getUsersByDateCreated($d) {
    $sql = LookupAdminDao::actionSelect()
        . "WHERE u.date_created LIKE '$d%' ORDER BY date_created";
    return fetchArray($sql);
  }
  public static function getUserById($id) {
    $sql = LookupAdminDao::actionSelect()
        . "WHERE u.user_id=$id";
    return fetch($sql);
  }
  public static function getGroup($ugid) {
    $sql = "SELECT * FROM user_groups WHERE user_group_id=$ugid";
    return fetch($sql);
  }

  /*
   * User stats
   */
  public static function getUserCountByDateCreated() {
    $sql = "SELECT DATE_FORMAT(date_created,'%d-%b-%Y %a') AS created, SUBSTR(date_created,1,10) AS created2, CONCAT('<a href=\"serverAdm.php?action=usersByCreated&d=',SUBSTR(date_created,1,10),'\">',SUBSTR('******************************************************',1,COUNT(*)),' ',COUNT(*),'</a>') AS count FROM users GROUP BY created ORDER BY created2 DESC";
    return fetchSimpleArray($sql, "count", "created");
  }

  /*
   * SUbpeona
   */
  public static function getSubp() {
    $sql = "SELECT CONCAT('<a href=\"new-console.php?sid=',s.session_id,'\" target=\"_blank\">',s.session_id,' ',c.last_name,', ',c.first_name,'</a>') AS url FROM sessions s INNER JOIN clients c ON c.client_id=s.client_id WHERE s.user_group_id=391 ORDER BY s.session_id";
    return fetchSimpleArray($sql, "url");
  }

  /*
   * Template lookups
   */
  public static function getZeroOpts($tid, $sid) {
    $sql = "SELECT CONCAT('<a href=\"adminQuestion.php?id=', q.question_id, '&sid=$sid&tid=$tid&',CURTIME(),'\" target=\"_blank\">impr.', p.uid, '.', q.uid,'</a>') AS url, CONCAT('impr.', p.uid, '.', q.uid) AS question, COUNT(o.option_id) AS options FROM template_questions q INNER JOIN template_pars p ON q.par_id=p.par_id AND p.section_id=$sid and p.current=1 LEFT JOIN template_options o ON o.question_id=q.question_id GROUP BY question HAVING options=0";
    return fetchSimpleArray($sql, "url");
  }
  public static function getIcds() {
    $sql = <<<eos
select distinct o.uid, count(*) as ct from template_options o
inner join template_questions q on q.question_id=o.question_id inner join template_pars p on q.par_id=p.par_id
where q.out_data is not null and p.section_id=12 and p.current=1 and o.icd_code is null
group by o.uid;
eos;
    return fetchArray($sql);
  }

  private function actionSelect() {
    return "SELECT u.user_group_id, concat('<a href=\"serverAdm.php?action=usersByGroup&g=',u.user_group_id,'\">',ug.name,'</a>') AS user_group_nm, concat('<b>',u.name,'</b>') as name_________, uid as uid__________, user_id as user_id______, email as email________, phone1 as phone________, subscription as subscription_, active as active_______, trial_expdt as trial_expdt__, user_type as user_type____, date_created as date_created_, license_state, license as license______, dea as dea__________, npi as npi__________, expiration as expiration___, expire_reason,"
        . " concat("
        . "'<a href=\"serverAdm.php?action=confirmp&a=setCtUgid&id=',u.user_group_id,'\">AdminLogin</a> ',"
        . "'<a href=\"serverAdm.php?action=confirm&a=resetPw&d=reset+password&id=',u.user_id,'\">ResetPW</a> ',"
        . "'<a href=\"serverAdmMerge.php?action=find&ugid=',u.user_group_id,'\">MergePatients</a> ',"
        . "'<a href=\"serverAdm.php?action=confirm&a=extendTrial&d=extend+trial&id=',u.user_id,'\">ExtendTrial</a> ',"
        . "'<a href=\"serverAdm.php?action=confirmCancel&id=',u.user_id,'\">Deactivate</a> '"
        . ") AS actions______"
        . " FROM users u INNER JOIN user_groups ug ON u.user_group_id=ug.user_group_id LEFT JOIN addresses a ON a.table_code='U' AND a.table_id=u.user_id ";
  }

  /*
   * Set UGID of CTADMIN
   */
  public static function setCtUgid($ugid) {
    global $login;
    if ($login->admin) {
      $login->userGroupId = $ugid;
      $login->save();
      query("UPDATE users SET user_group_id=$ugid WHERE user_id=" . $login->userId);
      return LookupAdminDao::getUsersByUgid($ugid);
    }
  }

  /*
   * Reset user password to "clicktate1"
   */
  public static function resetPw($id) {
    $row = LookupAdminDao::getUserById($id);
    $now = nowNoQuotes();
    if ($row) {
      query("UPDATE users SET pw='4a50f8bf6d9de49df785571098fe3efb37420e4ff1ab53b26', pw_expires='$now' WHERE user_id=$id");
      return LookupAdminDao::getUserById($id);
    }
  }

  /*
   * Deactivate (and cancel billing, if applicable)
   */
  public static function deactivate($id, $expireReason) {
    $row = LookupAdminDao::getUserById($id);
    if ($row) {
      query("UPDATE users SET active=0, expire_reason=$expireReason WHERE user_id=$id");
      $row = LookupAdminDao::getUserById($id);
      if (LookupAdminDao::getNextBillDate($id)) {
        query("UPDATE billinfo SET next_bill_date='2050-01-01' WHERE user_id=$id");
        $row["next_billdate"] = LookupAdminDao::getNextBillDate($id);
      }
    }
    return $row;
  }

  private static function getNextBillDate($id) {
    return fetchField("SELECT next_bill_date FROM billinfo WHERE user_id=$id");
  }

  /*
   * Extend user's trial (1 month from today)
   */
  public static function extendTrial($id) {
    $row = LookupAdminDao::getUserById($id);
    if ($row && $row["subscription_"] == 0) {
      $dt = nowUnix();
      $dt = date("Y-m-d", mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 14, date("Y", $dt)));
      query("UPDATE users SET active=1, trial_expdt='$dt', expiration=null, expire_reason=null WHERE user_id=$id");
      return LookupAdminDao::getUserById($id);
    }
  }

  /*
   * popAdmin: Search test/actions of questions for supplied "suid.puid" reference
   * Returns [
   *    "Tests"=>[fetchArray],
   *    "Actions"=>[fetchArray],
   *   ]
   */
  public static function searchTestActionRefs($pref) {
    $a = array();
    $a["Tests"] = fetchArray(LookupAdminDao::sqlSearchQuestions("test", $pref));
    $a["Actions"] = fetchArray(LookupAdminDao::sqlSearchQuestions("actions", $pref));
    return $a;
  }

  /*
   * popAdmin: Search for occurrences of supplied question sync
   * Returns [fetchArray]
   */
  public static function searchQSyncs($sync) {
    return fetchArray(LookupAdminDao::sqlSearchQuestions("sync_id", $sync));
  }

  private function sqlSearchQuestions($field, $for, $exact = false) {
    $value = ($exact) ? "= '$for'" : "LIKE '%$for%'";
    return "SELECT t.template_id AS tid, s.section_id AS sid, q.question_id AS qid, p.uid as puid, p.current, p.date_effective, CONCAT(s.uid,'.',p.uid,'.',q.uid) AS qref, CONCAT(s.uid,'.',p.uid) AS pref, q.`desc`, q.$field AS field"
        . " FROM template_questions q INNER JOIN template_pars p ON q.par_id=p.par_id INNER JOIN template_sections s ON p.section_id=s.section_id INNER JOIN templates t ON t.template_id=s.template_id"
        . " WHERE q.$field $value"
        . " ORDER BY s.sort_order, p.uid, p.date_effective DESC, q.sort_order";
  }

  private static function insertLookupData($o) {
    $sql = "INSERT INTO lookup_data VALUES(null, '$o->LEVEL', $o->LEVEL_ID, $o->LOOKUP_TABLE_ID, $o->INSTANCE, '$o->DATA')";
    insert($sql);
  }

  private static function updateLookupData($o) {
    $sql = "UPDATE lookup_data SET data='$o->DATA' WHERE lookup_data_id=$o->LOOKUP_DATA_ID";
    query($sql);
  }

  private static function buildLookupSql($ltid, $level, $levelNameValue, $innerJoin = "") {
    return "SELECT $levelNameValue AS LEVEL_NAME, d.* FROM lookup_data d $innerJoin WHERE d.lookup_table_id=$ltid AND d.level='$level'";
  }
}
?>