<?php
require_once "php/dao/_util.php";
require_once "php/dao/MatchSorter.php";
require_once "php/data/db/Template.php";
require_once "php/data/db/Section.php";
require_once "php/data/db/Group.php";
require_once "php/data/db/Par.php";
require_once "php/data/db/Question.php";
require_once "php/data/db/Option.php";

/*
 * Template reading
 */
class TemplateReaderDao {

  public static function test() {
    $conn = batchopen();
    $res = batchquery("SELECT par_id, `desc` FROM template_pars where section_id=12 and `desc` like '%diagnoses' order by `desc`");
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      TemplateReaderDao::test2($row["par_id"]);
    }
    batchclose($conn);
  }

  public static function test2($pid) {
    $res = batchquery("SELECT o.option_id, o.desc, o.text from template_questions q, template_options o where q.par_id=" . $pid . " and o.question_id=q.question_id ORDER BY o.sort_order");
    $row = mysql_fetch_array($res, MYSQL_ASSOC);
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $text =  ($row["text"] != null) ? $row["text"] : $row["desc"];
      $label =  $row["desc"] . (($row["text"] != null) ? " (" . $row["text"] . ")" : "");
      $sql = "SELECT COUNT(*) as count FROM template_options o, template_questions q, template_pars p"
          . " WHERE o.question_id=q.question_id AND q.par_id=p.par_id AND p.section_id=12 AND p.current=1 AND p.inject_only<>1"
          . " AND p.par_id<>" . $pid . " AND (o.desc=" . q($text, true) . " OR o.text=" . q($text, true) . ")";
      $a = batchfetch($sql);
      if ($a["count"] > 0) {
        echo $label . "<br/>";
      }
    }
  }

  // Get array of templates authorized for user
  public static function getMyTemplates() {

    $dtos = array();
    $rows = TemplateReaderDao::getMyTemplatesAsRows();
    while ($row = mysql_fetch_array($rows, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildTemplate($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  public static function getMyTemplatesAsRows() {
    global $login;
    if ($login->admin) {
      $where = "";
    } else {
      $or = ($login->userGroupId == 2484 || $login->userGroupId == 2) ? 'OR template_id=34 ' : '';
      $where = "WHERE public=1 " . $or . "OR user_group_id=" . $login->userGroupId;
    }
    return query("SELECT template_id, user_id, uid, name, public, date_created, date_updated, `desc`, title FROM templates " . $where . " ORDER BY user_group_id desc, template_id");
  }

  // Get array of sections for a template
  public static function getSections($templateId) {

    $res = query("SELECT section_id, template_id, uid, name, `desc`, sort_order, title FROM template_sections WHERE template_id=" . $templateId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildSection($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get array of groups for a section
  public static function getGroups($sectionId) {

    $res = query("SELECT group_id, section_id, uid, major, sort_order, `desc` FROM template_groups WHERE section_id=" . $sectionId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildGroup($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get array of paragraphs for a group
  public static function getGroupPars($groupId) {

    $res = query("SELECT p.par_id, p.section_id, p.uid, p.major, p.sort_order, p.`desc`, p.no_break, p.inject_only, p.date_effective FROM template_group2par gp, template_pars p WHERE gp.par_id=p.par_id AND gp.group_id=" . $groupId . " ORDER BY gp.sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildPar($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get array of paragraphs for a section
  public static function getPars($sectionId) {

    $res = query("SELECT par_id, section_id, uid, major, sort_order, `desc`, no_break, inject_only, date_effective, in_data_type, in_data_table, in_data_cond, current, dev FROM template_pars WHERE section_id=" . $sectionId . " ORDER BY `desc`, date_effective DESC");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildPar($row);
      $dto->current = ($row["current"] == "1");
      // $dto->published = TemplateReaderDao::lastUpdatedParJson($dto->id);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get array of questions for a paragraph
  public static function getQuestions($parId) {
    return TemplateReaderDao::getQuestionsWhere("par_id=$parId", null);
  }

  public static function getQuestionsWhere($where, $innerJoin = "") {
    $res = query("SELECT q.question_id, q.par_id, q.uid, q.`desc`, q.bt, q.at, q.btms, q.atms, q.btmu, q.atmu, q.list_type, q.no_break, q.test, q.actions, q.defix, q.mix, q.mcap, q.mix2, q.mcap2, q.img, q.sort_order, q.sync_id, q.out_data, q.in_data_actions, q.dsync_id, q.billing FROM template_questions q $innerJoin"
        . " WHERE $where ORDER BY q.sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dtos[] = TemplateReaderDao::buildQuestion($row);
    }
    return $dtos;
  }

  // Get array of questions for a paragraph
  public static function getQuestionsWithOptions($parId) {

    $res = query("SELECT question_id FROM template_questions WHERE par_id=" . $parId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::getQuestion($row["question_id"], true);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get array of options for a question
  public static function getOptions($questionId) {

    $res = query("SELECT " . Option::SQL_FIELDS . " FROM template_options WHERE question_id=" . $questionId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = Option::fromRow($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  // Get specific template
  public static function getTemplate($templateId, $withChildren = false) {

    $template = TemplateReaderDao::buildTemplate(fetch("SELECT template_id, user_id, uid, name, public, date_created, date_updated, `desc`, title FROM templates WHERE template_id=" . $templateId));
    if ($template != null) {
      LoginDao::authenticateUserId($template->userId);
      if ($withChildren) {
        $template->sections = TemplateReaderDao::getSections($templateId);
      }
    }
    return $template;
  }

  // Get specific section
  public static function getSection($sectionId, $withChildren = false) {

    $section = TemplateReaderDao::buildSection(fetch("SELECT section_id, template_id, uid, name, `desc` , sort_order, title FROM template_sections WHERE section_id=" . $sectionId));
    if ($section != null) {
      LoginDao::authenticateSectionId($section->id);
      if ($withChildren) {
        //$section->groups = TemplateReaderDao::getGroups($sectionId);
        $section->pars = TemplateReaderDao::getPars($sectionId);
      }
    }
    return $section;
  }

  public static function fetchSectionByUid($tid, $suid) {
    return fetch("SELECT section_id, name FROM template_sections WHERE template_id=" . $tid . " AND uid=" . quote($suid));
  }

  // Get specific group
  public static function getGroup($groupId, $withChildren = false) {

    $group = TemplateReaderDao::buildGroup(fetch("SELECT group_id, section_id, uid, major, sort_order, `desc` FROM template_groups WHERE group_id=" . $groupId));
    if ($section != null) {
      LoginDao::authenticateGroupId($group->id);
      if ($withChildren) {
        $group->pars = TemplateReaderDao::getGroupPars($groupId);
      }
    }
    return $group;
  }

 // Get specific paragraph
  public static function getPar($parId, $withChildren = false, $withGrandchildren = false) {

    $par = TemplateReaderDao::buildPar(fetch("SELECT par_id, section_id, uid, major, sort_order, `desc`, no_break, inject_only, date_effective, in_data_type, in_data_table, in_data_cond, dev FROM template_pars WHERE par_id=" . $parId));
    if ($par != null) {
      //LoginDao::authenticateParId($par->id);
      if ($withChildren) {
        if (! $withGrandchildren) {
          $par->questions = TemplateReaderDao::getQuestions($parId);
        } else {
          $par->questions = TemplateReaderDao::getQuestionsWithOptions($parId);
        }
      }
    }
    return $par;
  }

  // Build static HTML for par preview
  // Returns {id:#,desc:"desc",html:"html"}
  public static function parPreview($pid, $tid, $noteDate = null, $showAllOpts = false) {
    JsonDao::defaultNoteDate($noteDate);
    $html = array();
    $pars = TemplateReaderDao::pvGetPars($tid, $pid, $noteDate);
    if (count($pars) == 0) {
      return null;
    }
    $desc = $pars[0]->desc;
    foreach ($pars as &$p) {
      TemplateReaderDao::pvAddPar($html, $p, $showAllOpts);
    }
    $j = array(
        "id" => $pid,
        "desc" => $desc,
        "html" => implode("", $html));
    logit_r($j);
    return jsonencode($j);
  }
  private static function pvGetPars($tid, $pid, $noteDate) {
    $p = TemplateReaderDao::getPar($pid, true, true);
    $sid = $p->sectionId;
    $pars = array($p);
    $pd = JsonDao::getParsedParData($tid, $noteDate, 0, $pid);
    foreach ($pd["injections"] as &$injection) {
      $pid = $injection["pid"];
      $p = TemplateReaderDao::getPar($pid);
      if ($p->sectionId == $sid) {
        $pars[] = TemplateReaderDao::getPar($pid, true, true);
      }
    }
    return $pars;
  }
  private static function pvAddPar(&$html, $p, $showAllOpts) {
    $html[] = "<p>";
    foreach ($p->questions as &$q) {
      TemplateReaderDao::pvAddQuestion($html, $q, $showAllOpts);
    }
    $html[] = "</p>";
  }
  private static function pvAddQuestion(&$html, $q, $showAllOpts) {
    if ($q->type == 5) {
      return;
    }
    if ($q->type == 6 && ! $showAllOpts) {
      $html[] = " <span class='but'>";
    } else if ($q->test && strpos($q->test, "*") === false) {
      $html[] = "<span class='test'>";
    } else {
      $html[] = "<span>";
    }
    if ($q->bt) {
      $html[] = TemplateReaderDao::pvFilter($q, $q->bt);
    }
    if (count($q->options) > 0) {
      if ($showAllOpts) {
        $six = 0;
        if ($q->mix == 1) {
          $six = 1;
          if ($q->btms) {
            $html[] = " " . $q->btms;
          }
        } else {
          if (count($q->options) == 2) {
            $t0 = $q->options[0]->text;
            $t1 = $q->options[1]->text;
            if (substr($t1, -1, 1) == "." && substr($t0, -1, 1) != ".") {
              $q->options[1]->text = $t0;
              $q->options[0]->text = substr($t1, 0, -1);
            }
          }
        }
        if ($q->type != 6) {
          $html[] = " <span class='o'>";
          $html[] = TemplateReaderDao::pvStringOpts($q, $six, true);
          $html[] = "</span>";
        }
      } else {
        $o = $q->options[$q->defix];
        $isMulti = (! is_null($q->mix) && $q->defix >= $q->mix);
        if ($q->btms && $isMulti) {
          $html[] = TemplateReaderDao::pvFilter($q, $q->btms);
        }
        if ($q->type != 6) {
          $html[] = " <span class='o'>";
        }
        $html[] = TemplateReaderDao::pvFilter($q, TemplateReaderDao::pvOptText($o), false);
        if ($q->type != 6) {
          $html[] = "</span>";
        }
      }
    }
    if ($q->at) {
      $html[] = TemplateReaderDao::pvFilter($q, $q->at);
    }
    TemplateReaderDao::pvAddBreak($html, $q->break);
    $html[] = "</span>";
  }
  public static function pvOptText($o) {
    return is_null($o->text) ? $o->uid : $o->text;
  }
  private static function pvFilter($q, $text, $prespace = true) {
    if (strpos($text, "{all}") !== false) {
      $text = str_replace("{all}", TemplateReaderDao::pvStringOpts($q, $q->mix), $text);
    }
    if ($prespace) {
      $text = " " . $text;
    }
    return $text;
  }
  private static function pvStringOpts($q, $ix, $pipe = false) {
    $a = array();
    $lix = count($q->options) - 1;
    $orix = $lix - 1;
    for ($i = $ix; $i <= $lix; $i++) {
      $a[] = TemplateReaderDao::pvOptText($q->options[$i]);
      if ($pipe) {
        if ($i < $lix) {
          $a[] = " / ";
        }
      } else {
        if ($i == $orix) {
          $a[] = " or ";
        } else if ($i < $lix) {
          $a[] = ", ";
        }
      }
    }
    return implode("", $a);
  }
  private static function pvAddBreak(&$html, $break) {
    switch ($break) {
      case 0:
        $html[] = ". ";
        return;
      case 1:
        return;
      case 2:
        $html[] = ":";
        return;
      case 3:
        $html[] = ";";
        return;
      case 4:
        $html[] = ".<br/>";
        return;
      case 5:
        $html[] = ".<br/><br/>";
        return;
      case 6:
        $html[] = "<br/>";
        return;
      case 7:
        return;
    }
  }

  // for batch only
  public static function getTemplateBatch($templateId) {

    $conn = batchopen();
    $template = TemplateReaderDao::buildTemplate(batchfetch("SELECT template_id, user_id, uid, name, public, date_created, date_updated, `desc`, title FROM templates WHERE template_id=" . $templateId));
    if ($template != null) {
      $template->sections = TemplateReaderDao::getSections2($templateId);
    }
    batchclose($conn);
    return $template;
  }
  public static function getSections2($templateId) {

    $res = batchquery("SELECT section_id, template_id, uid, name, `desc`, sort_order, title FROM template_sections WHERE template_id=" . $templateId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::buildSection($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // for batch only!
  public static function getSectionBatch($sectionId, $includeQuestions = true, $startPid = null) {

    $conn = batchopen();
    $section = TemplateReaderDao::buildSection(batchfetch("SELECT section_id, template_id, uid, name, `desc` , sort_order, title FROM template_sections WHERE section_id=" . $sectionId));
    if ($section != null) {
      $section->pars = TemplateReaderDao::getPars2($sectionId, $includeQuestions, $startPid);
    }
    batchclose($conn);
    return $section;
  }
  public static function getPars2($sectionId, $includeQuestions, $startPid) {

    if ($startPid == null) {
      $sql = "SELECT par_id FROM template_pars WHERE section_id=$sectionId AND current=1 ORDER BY inject_only, major DESC, sort_order";
    } else {
      $sql = "SELECT par_id FROM template_pars WHERE section_id=$sectionId AND current=1 AND par_id>=$startPid ORDER BY inject_only, major DESC, sort_order";
    }
    $res = batchquery($sql);
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::getPar2($row["par_id"], $includeQuestions);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  public static function getPar2($parId, $includeQuestions) {

    $par = TemplateReaderDao::buildPar(batchfetch("SELECT par_id, section_id, uid, major, sort_order, `desc`, no_break, inject_only, date_effective, in_data_type, in_data_table, in_data_cond FROM template_pars WHERE par_id=" . $parId));
    if ($par != null && $includeQuestions) {
      $par->questions = TemplateReaderDao::getQuestionsWithOptions2($parId);
    }
    return $par;
  }
  public static function getQuestionsWithOptions2($parId) {

    $res = batchquery("SELECT question_id FROM template_questions WHERE par_id=" . $parId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = TemplateReaderDao::getQuestion2($row["question_id"]);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  public static function getQuestion2($questionId) {

    $question = TemplateReaderDao::buildQuestion(batchfetch("SELECT question_id, par_id, uid, `desc`, bt, at, btms, atms, btmu, atmu, list_type, no_break, test, actions, defix, mix, mcap, mix2, mcap2, img, sort_order, sync_id, out_data, in_data_actions, dsync_id, billing FROM template_questions WHERE question_id=" . $questionId));
    if ($question != null) {
      $question->options = TemplateReaderDao::getOptions2($questionId);
    }
    return $question;
  }
  public static function getOptions2($questionId) {

    $res = batchquery("SELECT " . Option::SQL_FIELDS . " FROM template_options WHERE question_id=" . $questionId . " ORDER BY sort_order");
    $dtos = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = Option::fromRow($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }

  // Get specific question
  public static function getQuestion($questionId, $withChildren = false, $withInTable = false) {
    $question = TemplateReaderDao::buildQuestion(fetch("SELECT question_id, par_id, uid, `desc`, bt, at, btms, atms, btmu, atmu, list_type, no_break, test, actions, defix, mix, mcap, mix2, mcap2, img, sort_order, sync_id, out_data, in_data_actions, dsync_id, billing FROM template_questions WHERE question_id=" . $questionId));
    if ($question != null) {
      //LoginDao::authenticateQuestionId($question->id);
      if ($withChildren) {
        $question->options = TemplateReaderDao::getOptions($questionId);
      }
      //if ($withInTable) {
        $par = TemplateReaderDao::getPar($question->parId);

        $question->inTable = $par->inTable;
        $question->sectionId = $par->sectionId;
      //}
    }
    return $question;
  }

  // Get JQuestion version of specific question
  public static function getJQuestion($questionId) {
    return JsonDao::buildJQuestion(TemplateReaderDao::getQuestion($questionId));
  }


  public static function wildcard($text) {
    $a = explode(" ", trim($text));
    return "%" . implode("%", $a) . "%";
  }

  /*
   * General template search, return:
   * {sname:}  // "History of Present Illness"
   *   []        // pars
   *     id        // pid
   *     desc      // par desc
   */
  public static function templateSearch($tid, $suid, $text) {
    if ($tid == 1) {
      return TemplateReaderDao::medNoteSearch($suid, $text);
    }
    $suids = array();
    $srow = TemplateReaderDao::fetchSectionByUid($tid, $suid);
    $pars = TemplateReaderDao::sectionSearch($srow["section_id"], $text);
    TemplateReaderDao::mnsAddSection($suids, $suid, $srow["name"], $pars);
    return $suids;
  }

  /*
   * Search for med note template, return:
   * {sname:}  // "History of Present Illness"
   *   []        // pars
   *     id        // pid
   *     desc      // par desc
   *     iodescs   // for "hpi" and "impr", concat'd matching impression option descs (default impression option always included)
   */
  public static function medNoteSearch($suid, $text) {
    $suids = array();
    if ($text != "") {
      $imprs = TemplateReaderDao::imprSearch($text, true);
      $hpis = TemplateReaderDao::hpiSearch($text, $imprs);
      switch ($suid) {
        case "hpi":
          TemplateReaderDao::mnsAddHpi($suids, $hpis);
          TemplateReaderDao::mnsAddImpr($suids, $imprs);
          break;
        case "impr":
          TemplateReaderDao::mnsAddImpr($suids, $imprs);
          TemplateReaderDao::mnsAddHpi($suids, $hpis);
          break;
        default:
          $srow = TemplateReaderDao::fetchSectionByUid(1, $suid);
          $pars = TemplateReaderDao::sectionSearch($srow["section_id"], $text);
          TemplateReaderDao::mnsAddSection($suids, $suid, $srow["name"], $pars);
          if (count($hpis) > 0) TemplateReaderDao::mnsAddHpi($suids, $hpis);
          if (count($imprs) > 0) TemplateReaderDao::mnsAddImpr($suids, $imprs);
          break;
      }
    } else {  // no search requested, return all
      $srow = TemplateReaderDao::fetchSectionByUid(1, $suid);
      $pars = TemplateReaderDao::sectionSearch($srow["section_id"], $text);
      TemplateReaderDao::mnsAddSection($suids, $suid, $srow["name"], $pars);
    }
    return $suids;
  }
  private static function mnsAddSection(&$suids, $suid, $sname, $pars) {
    $ps = array();
    $suid = strtoupper($suid);
    foreach ($pars as &$par) {
      $p = new stdClass();
      $p->id = $par->id;
      $p->desc = $par->desc;
      $p->suid = $suid;
      $ps[] = $p;
    }
    $suids[$sname] = $ps;
  }
  private static function mnsAddHpi(&$suids, $hpis) {
    $pars = array();
    foreach ($hpis as &$hpi) {
      $par = new stdClass();
      $par->id = $hpi->id;
      $par->desc = $hpi->desc;
      $par->iodescs = $hpi->impr->iodescs;
      $par->suid = "HPI";
      $pars[] = $par;
    }
    $suids["History of Present Illness"] = $pars;
  }
  private static function mnsAddImpr(&$suids, $imprs) {
    $pars = array();
    foreach ($imprs as &$impr) {
      $par = new stdClass();
      $par->id = $impr->id;
      $par->desc = $impr->desc;
      $par->iodescs = $impr->q->iodescs;
      $par->suid = "IMPR";
      $pars[] = $par;
    }
    $suids["Impression"] = $pars;
  }

  /*
   * Generic section search, return:
   * []
   *   id    // par id
   *   desc  // par desc
   */
  public static function sectionSearch($sid, $text) {
    $search = splitJoin($text, " ", "|");
    $pattern = "/" . $search . "/i";
    $pars = array();
    if ($text != "") {
      $sql = "SELECT par_id, pdesc, COUNT(*) AS ct FROM ("
          . " SELECT p.par_id, p.uid AS puid, p.desc AS pdesc, q.question_id, o.desc AS odesc, o.text AS otext"
          . " FROM template_options o, template_questions q, template_pars p, template_sections s"
          . " WHERE o.question_id=q.question_id AND q.par_id=p.par_id AND p.section_id=s.section_id "
          . " AND s.section_id=" . $sid . " AND p.current=1 AND p.inject_only<>1"
          . " ORDER BY p.par_id, q.sort_order, o.sort_order) a"
          . " WHERE a.pdesc RLIKE '". $search . "' OR a.odesc RLIKE '" . $search ."' OR a.otext RLIKE '" . $search ."'"
          . " GROUP BY par_id, pdesc"
          . " ORDER BY ct DESC";
    } else {
      $sql = "SELECT p.par_id, p.desc AS pdesc FROM template_pars p"
          . " WHERE section_id=" . $sid . " AND p.current=1 AND p.inject_only<>1"
          . " ORDER BY p.major DESC, p.desc";
    }
    $res = query($sql);
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $par = new stdClass();
      $par->id = $row["par_id"];
      $par->desc = $row["pdesc"];
      $pars[] = $par;
    }
    return $pars;
  }

  /*
   * Impression search for med note, return:
   * []
   *   id   // impr pid
   *   desc // "Syncope"
   *   q
   *     id
   *     hmi      // high match option index
   *     iodescs  // concatenated option text
   *     opts[]   // just the matching options
   *       ix
   *       id
   *       desc   // "Cough syncope"
   *   hpis[]
   *     id    // hpi pid
   *     desc  // "Syncope follow up"
   */
  public static function imprSearch($text, $includeHpis, $highMatchesOnly = 1) {
    $search = splitJoin($text, " ", "|");
    $pattern = "/" . $search . "/i";
    $pars = array();
    $sql = "SELECT par_id, pdesc, puid, question_id FROM ("
        . " SELECT p.par_id, p.uid AS puid, p.desc AS pdesc, q.question_id, o.desc AS odesc, o.text AS otext"
        . " FROM template_options o, template_questions q, template_pars p"
        . " WHERE o.question_id=q.question_id AND q.par_id=p.par_id AND p.section_id=12 AND p.current=1 AND p.inject_only<>1 AND q.out_data IS NOT NULL"
        . " ORDER BY p.par_id, q.sort_order, o.sort_order) a"
        . " WHERE a.pdesc RLIKE '". $search . "' OR a.odesc RLIKE '" . $search ."' OR a.otext RLIKE '" . $search ."'"
        . " GROUP BY par_id";
    $res = query($sql);
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $question = TemplateReaderDao::getQuestion($row["question_id"], true);
      $par = new stdClass();
      $par->id = $row["par_id"];
      $par->desc = $row["pdesc"];
      $par->q = new stdClass();
      $par->q->id = $question->id;
      $par->q->hm = 0;  // high matches
      $par->q->hmi = 0;  // high matches index
      $par->q->opts = array();
      $defix = $question->defix;
      $defopt = TemplateReaderDao::imsBuildOpt($defix, $question->options[$defix]);
      $iodescs = array();
      TemplateReaderDao::imsAddImpr($iodescs, $defopt->desc);
      for ($ix = 0; $ix < count($question->options); $ix++) {
        $option = $question->options[$ix];
        $desc = ($option->text) ? $option->text : $option->desc;
        $matches = MatchSorter::countMatches($pattern, $desc);
        if ($matches > 0) {
          $opt = TemplateReaderDao::imsBuildOpt($ix, $option);
          if ($matches > $par->q->hm) {
            $par->q->hm = $matches;
            $par->q->hmi = count($par->q->opts);
          }
          $par->q->opts[] = $opt;
          if ($ix != $defix) {
            TemplateReaderDao::imsAddImpr($iodescs, $opt->desc);
          }
        }
      }
      if (count($par->q->opts) == 0) {
        $par->q->opts[] = $defopt;
      }
      $par->q->iodescs = implode(" / ", $iodescs);
      if ($includeHpis) {  // include mappable HPIs that conditionlessly inject impression
        $uid = $row["puid"];
        $sql = "SELECT p.par_id, p.desc AS pdesc"
            . " FROM template_questions q, template_pars p"
            . " WHERE q.par_id=p.par_id AND p.section_id=10 AND p.current=1 AND p.inject_only<>1"
            . " AND q.actions LIKE '%inject(impr." . $uid . ")%' AND q.actions NOT LIKE '%{inject(impr." . $uid . ")%'"
            . " ORDER BY p.par_id, q.sort_order";
        $res2 = query($sql);
        $hpis = array();
        while ($row = mysql_fetch_array($res2, MYSQL_ASSOC)) {
          $hpi = new stdClass();
          $hpi->id = $row["par_id"];
          $hpi->desc = $row["pdesc"];
          $hpis[] = $hpi;
        }
        $par->hpis = $hpis;
      }
      $pars[] = $par;
    }
    $sorter = new MatchSorter($pattern, $highMatchesOnly);
    if ($sorter->words > 1) {
      foreach ($pars as &$par) {
        $sorter->add($par, $par->id);
        foreach ($par->q->opts as &$opt) {
          $sorter->tally($par->id, $opt->desc);
        }
      }
      $pars = $sorter->sort();
    }
    return $pars;
  }
  private static function imsBuildOpt($ix, $option) {
    $opt = new stdClass();
    $opt->ix = $ix;
    $opt->id = $option->id;
    $opt->desc = ($option->text) ? $option->text : $option->desc;
    return $opt;
  }
  private static function imsAddImpr(&$iodescs, $text) {
    $key = strtoupper($text);
    if (array_key_exists($key, $iodescs)) {
      return;
    }
    $iodescs[$key] = $text;
  }

  /*
   * HPI search for med note, return:
   * []
   *   id    // hpi pid
   *   desc
   *   impr
   *     id       // impr pid
   *     desc
   *     iodescs  // concat impr descs
   */
  public static function hpiSearch($text, $pars = null) {  // $pars=result of imprSearch
    $search = splitJoin($text, " ", "|");
    $pattern = "/" . $search . "/i";
    if (is_null($pars)) {
      $pars = TemplateReaderDao::imprSearch($text, true);
    }
    $sorter = new MatchSorter($pattern, 0, false);
    foreach ($pars as &$impr) {
      $opt = $impr->q->opts[$impr->q->hmi];
      foreach ($impr->hpis as &$hpi) {
        $hpi->impr = new stdClass();
        $hpi->impr->id = $impr->id;
        $hpi->impr->desc = $impr->desc;
        $hpi->impr->iodescs = $impr->q->iodescs;
        $text = $hpi->desc . " " . $opt->desc;
        $sorter->add($hpi, $hpi->id);
        $sorter->tally($hpi->id, $text);
      }
    }
    return $sorter->sort();
  }

  // JSON cache functions
  public static function fetchParJson($parId) {
    $row = fetch("SELECT json FROM template_parjson WHERE par_id=" . $parId);
    if (! $row) return null;
    return $row["json"];
  }
  public static function lastUpdatedParJson($parId) {
    $row = fetch("SELECT date_updated FROM template_parjson WHERE par_id=" . $parId);
    if (! $row) return null;
    return $row["date_updated"];
  }
  public static function insertParJson($parId, $json) {
    query("INSERT INTO template_parjson VALUES(" . $parId . "," . quote($json, true) . ", NULL)");
  }

  public static function buildTemplate($row) {
    if (! $row) return null;
    return new Template($row["template_id"], $row["user_id"], $row["uid"], $row["name"], $row["public"], $row["date_created"], $row["date_updated"], $row["desc"], $row["title"]);
  }
  public static function buildSection($row) {
    if (! $row) return null;
    return new Section($row["section_id"], $row["template_id"], $row["uid"], $row["name"], $row["desc"], $row["sort_order"], $row["title"]);
  }
  public static function buildGroup($row) {
    if (! $row) return null;
    return new Group($row["group_id"], $row["section_id"], $row["uid"], $row["major"], $row["sort_order"], $row["desc"]);
  }
  public static function buildPar($row) {
    if (! $row) return null;
    return new Par($row["par_id"], $row["section_id"], $row["uid"], $row["major"], $row["sort_order"], $row["desc"], $row["no_break"], $row["inject_only"], $row["date_effective"], $row["in_data_table"], $row["in_data_type"], $row["in_data_cond"], $row["dev"]);
  }
  public static function buildQuestion($row) {
    if (! $row) return null;
    return new Question($row["question_id"], $row["par_id"], $row["uid"], $row["desc"], $row["bt"], $row["at"], $row["btms"], $row["atms"], $row["btmu"], $row["atmu"], $row["list_type"], $row["no_break"], $row["test"], $row["actions"], $row["defix"], $row["mix"], $row["mcap"], $row["mix2"], $row["mcap2"], $row["img"], $row["sort_order"], $row["sync_id"], $row["out_data"], $row["in_data_actions"], $row["dsync_id"], $row['billing']);
  }
}
?>