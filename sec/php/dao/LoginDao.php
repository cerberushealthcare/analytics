<?php
require_once "php/dao/_util.php";
/*
 * Login and authentication
 */
class LoginDao {
    
  // Ensures userId passed as DAO argument is valid
  public static function authenticateUserId($userId) {
    AuthCache::user($userId, function($userId) {
      global $login;
      if (! $login->admin)
        if ($userId != $login->userId) 
          LoginDao::throwSecurityError('ui', $userId);
    });
  }
  public static function throwSecurityError($code, $id) {
    global $login;
	ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();
    throw new SecurityException("Access not allowed: $code($id) uid($login->userId) ugid($login->userGroupId).");
  }
  // Group authentications
  // If authenticated, user can make updates to group entities
  public static function authenticateUserGroupId($userGroupId) {
    global $login;
	ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();
	Logger::debug('LoginDao::authenticateUserGroupId: user group ID is ' . $userGroupId . ', login UGID is ' . $login->userGroupId);
    if ($userGroupId == null)
      LoginDao::throwSecurityError('ugi', $userGroupId);
    if (! $login->admin)
      if ($userGroupId != $login->userGroupId) 
        LoginDao::throwSecurityError('ugi', $userGroupId);
  }
  public static function authenticateUserGroupIdWithin($tableName, $pkColumn, $pkValue) {
    AuthCache::ugidWithin($tableName, $pkColumn, $pkValue, function($tableName, $pkColumn, $pkValue) {
      LoginDao::authenticateUserGroupId(fetchField("SELECT user_group_id FROM $tableName WHERE $pkColumn ='$pkValue'"));
    });
  }
  public static function authenticateTrackItemId($trackItemId) {
    LoginDao::authenticateUserGroupIdWithin('track_items', 'track_item_id', $trackItemId);
  }
  public static function authenticateSupportUserId($userId) {
    LoginDao::authenticateUserGroupIdWithin("users", "user_id", $userId);
  }
  public static function authenticateSchedId($schedId) {
    LoginDao::authenticateUserGroupIdWithin("scheds", "sched_id", $schedId);
  }
  public static function authenticateClientId($clientId) {
    LoginDao::authenticateUserGroupIdWithin("clients", "client_id", $clientId);
  }
  public static function authenticateDataAllergy($id) {
    LoginDao::authenticateUserGroupIdWithin("data_allergies", "data_allergies_id", $id);
  }
  public static function authenticateDataHm($id) {
    LoginDao::authenticateUserGroupIdWithin("data_hm", "data_hm_id", $id);
  }
  public static function authenticateDataHist($id) {
    LoginDao::authenticateUserGroupIdWithin("data_hists", "data_hist_id", $id);
  }
  public static function authenticateDataMed($id) {
    LoginDao::authenticateUserGroupIdWithin("data_meds", "data_med_id", $id);
  }
  public static function authenticateDataVitals($id) {
    LoginDao::authenticateUserGroupIdWithin("data_vitals", "data_vitals_id", $id);
  }
  public static function authenticateDataImmun($id) {
    LoginDao::authenticateUserGroupIdWithin("data_immuns", "data_immun_id", $id);
  }
  public static function authenticateDataDiagnosis($id) {
    LoginDao::authenticateUserGroupIdWithin("data_diagnoses", "data_diagnoses_id", $id);
  }
  public static function authenticateTemplatePresetId($templatePresetId) {  // returns templateId
    return AuthCache::getset(__METHOD__, func_get_args(), function() use ($templatePresetId) {
      $row = fetch("SELECT user_group_id, template_id FROM template_presets WHERE template_preset_id=" . $templatePresetId);
      LoginDao::authenticateUserGroupId($row["user_group_id"]);
      return $row["template_id"];
    });
  }

  // Admin authentication
  // If authenticated, user can update
  public static function authenticateSessionId($sessionId) {  // returns clientId
    return AuthCache::getset(__METHOD__, func_get_args(), function() use ($sessionId) {
      $row = fetch("SELECT client_id, user_group_id FROM sessions WHERE session_id=" . $sessionId);
      LoginDao::authenticateUserGroupId($row["user_group_id"]);
      $cached = $sessionId;
      return $row["client_id"];
    });
  }
  public static function authenticateTemplateId($templateId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($templateId) {
      $row = fetch("SELECT user_id FROM templates WHERE template_id=" . $templateId);
      LoginDao::authenticateUserId($row["user_id"]);
    });
  }
  public static function authenticateSectionId($sectionId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($sectionId) {
      $row = fetch("SELECT t.user_id FROM templates t, template_sections s WHERE t.template_id=s.template_id AND s.section_id=" . $sectionId);
      LoginDao::authenticateUserId($row["user_id"]);
    });
  }
  public static function authenticateGroupId($groupId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($groupId) {
      $row = fetch("SELECT t.user_id FROM templates t, template_sections s, template_groups g WHERE t.template_id=s.template_id AND s.section_id=g.section_id AND g.group_id=" . $groupId);
      LoginDao::authenticateUserId($row["user_id"]);
    });
  }
  public static function authenticateParId($parId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($parId) {
      $row = fetch("SELECT t.user_id FROM templates t, template_sections s, template_pars p WHERE t.template_id=s.template_id AND s.section_id=p.section_id AND p.par_id=" . $parId);
      LoginDao::authenticateUserId($row["user_id"]);
    });
  }
  public static function authenticateQuestionId($questionId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($questionId) {
      $row = fetch("SELECT t.user_id FROM templates t, template_sections s, template_pars p, template_questions q WHERE t.template_id=s.template_id AND s.section_id=p.section_id AND q.par_id=p.par_id AND q.question_id=" . $questionId);
      LoginDao::authenticateUserId($row["user_id"]);
    });
  }
  public static function authenticateThreadId($threadId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($threadId) {
      $row = fetch("SELECT user_group_id FROM msg_threads WHERE thread_id=$threadId");
      LoginDao::authenticateUserGroupId($row["user_group_id"]);
    });
  }

  // Reader authentication
  // If authenticated, user can read
  public static function authenticateReadTemplateId($templateId) {  // returns template name
    return AuthCache::getset(__METHOD__, func_get_args(), function() use ($templateId) {
      $row = fetch("SELECT public, user_group_id, name FROM templates WHERE template_id=" . $templateId);
      if ($templateId == 34) {
        global $login;
        if ($login->userGroupId == 2484)
          return $row['name'];
      }
      if (! LoginDao::toBool($row["public"])) {
        LoginDao::authenticateUserGroupId($row["user_group_id"]);
      }
      return $row["name"];
    });
  }
  public static function authenticateReadSectionId($sectionId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($sectionId) {
      $row = fetch("SELECT t.public, t.user_group_id FROM templates t, template_sections s WHERE t.template_id=s.template_id AND s.section_id=" . $sectionId);
      if (! LoginDao::toBool($row["public"])) 
        LoginDao::authenticateUserGroupId($row["user_group_id"]);
    });
  }
  public static function authenticateReadGroupId($groupId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($groupId) {
      $row = fetch("SELECT t.public, t.user_group_id FROM templates t, template_sections s, template_groups g WHERE t.template_id=s.template_id AND s.section_id=g.section_id AND g.group_id=" . $groupId);
      if (! LoginDao::toBool($row["public"])) 
        LoginDao::authenticateUserGroupId($row["user_group_id"]);
    });
  }
  public static function authenticateReadParId($parId) {
    AuthCache::getset(__METHOD__, func_get_args(), function() use ($parId) {
      $row = fetch("SELECT t.public, t.user_group_id FROM templates t, template_sections s, template_pars p WHERE t.template_id=s.template_id AND s.section_id=p.section_id AND p.par_id=" . $parId);
      if (! LoginDao::toBool($row["public"])) 
        LoginDao::authenticateUserGroupId($row["user_group_id"]);
    });
  }

  // Deactivate user
  private static function deactivate($userId, $expireReason) {
    $sql = "UPDATE users SET active=0, trial_expdt=0, expire_reason=" . $expireReason . " WHERE user_id=" . $userId;
    $res = query($sql);
  }

  public static function toBool($fld) {
    return ($fld == 1);
  }

  public static function generateHash($plainText, $salt = null) {
    if ($salt === null) {
      $salt = substr(md5(uniqid(rand(), true)), 0, 9);
    } else {
      $salt = substr($salt, 0, 9);
    }
    return $salt . sha1($salt . $plainText);
  }
}