<?php
p_i('Allergies');
require_once 'php/data/rec/sql/_FsDataRec.php';
require_once 'php/data/rec/sql/AllergiesLegacy.php';
require_once 'php/data/rec/sql/AllergiesNewCrop.php';
//
/**
 * Allergy DAO (wrapper for AllergiesLegacy and AllergiesNewCrop) 
 * @author Warren Hornsby
 */
class Allergies {
  /**
   * Build face recs from unprocessed session history
   * @param int cid
   */
  static function rebuildFromSessions($cid) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Build face recs from NewCrop status
   * @param int $cid
   * @param object $current @see NewCrop::pullCurrentMedAllergy()
   */
  static function rebuildFromNewCrop($cid, $current) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  static function reconcile($cid, $allers) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Get active and inactive facesheet records
   * @param int $cid
   * @return array(FaceAllergy,..)
   */
  static function getAll($cid) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Get active facesheet records
   * @param int $cid
   * @return array(FaceAllergy,..)
   */
  static function getActive($cid) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Get facesheet records by name
   * @param int $cid
   * @return array(name=>FaceAllergy,..)
   */
  static function getByName($cid) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Get history by date
   * @param int $cid
   * @param [FaceAllergy,..] $actives (optional, to sync history active flags)
   * @return array(SessionAllergy,..)
   */
  static function getHistoryByDate($cid, $actives = null) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Get allergy UI question
   * @return JQuestion
   */
  static function getQuestion() {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Save record from UI
   * @param stdClass $o JSON object
   * @return Allergy
   */
  static function save($o) {
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Deactivate record from UI
   * @param int $id
   * @return Allergy
   */
  static function deactivate($id) { 
    $args = func_get_args();
    return call_user_func_array(self::getDao(__FUNCTION__), $args);
  }
  /**
   * Deactivate records from UI
   * @param [int,..] $id
   * @return Allergy last one deactivated
   */
  static function deactivateMultiple($ids) {
    $recs = null;
    foreach ($ids as $id) 
      $rec = self::deactivate($id);
    return $rec;
  }
  /**
   * Deactivate all legacy-sourced allergies
   * @param int $cid
   */
  static function deactivateLegacy($cid) {
    $recs = FaceAllergyNc::fetchAllActive($cid);
    foreach ($recs as $rec) 
      if ($rec->isSourceLegacy()) 
        $rec->deactivate();
  }
  //
  static function getDaoClass($isErx) {
    return ($isErx) ? 'AllergiesNewCrop' : 'AllergiesLegacy';
  }
  //
  private static function getDao($method) {
    global $login;
    return array(self::getDaoClass($login->isErx()), $method);
  }
}
