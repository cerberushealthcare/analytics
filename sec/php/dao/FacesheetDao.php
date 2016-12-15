<?php
p_i('FacesheetDao');
//
class FacesheetDao {
  //
  const NO_AUDIT = false;
  const NO_HISTORY = false;
  const INCLUDE_DEFS = true;
  //
  const TID_IMMUN = 12;  // Messaging template
  
   /*
	Utilize the private methods and return a test object that can be used in our tests.
   */
   public function testFacesheet($clientId, $section) {
	   
	   switch ($section) {
		   case 1:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_ALL);
				static::loadClientHistory($fs, $clientId);
		   break;
		   case 2:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_ALLERGIES);
				static::loadAllergies($fs, $clientId);
		   break;
		   case 3:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_DIAGNOSES);
				static::loadDiagnoses($fs, $clientId);
		   break;
		   case 4:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_MEDS);
				static::loadMeds($fs, $clientId);
		   break;
		   case 5:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_VITALS);
				static::loadVitals($fs, $clientId);
		   break;
		   case 6:
				$fs = new JFacesheet($clientId, JFacesheet::CONTAINS_TRACKING);
				static::loadTracking($fs, $clientId);
		   break;
		   default:
				$result = null;
		   break;
	   }
	   
	   return $fs;
   }
   
   /**
   * Assemble entire facesheet
   * @param int $clientId
   * @param bool $audit
   * @return JFacesheet
   */
  public static function getFacesheet($clientId, $audit = true) {
//    if ($audit) { 
//      AuditDao::logReview($clientId, AuditDao::ENTITY_FACESHEET);
//    }
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_ALL);
    FacesheetDao::loadDocs($fs);
    FacesheetDao::loadClient($fs, $clientId, false);
    FacesheetDao::loadAllergies($fs, $clientId);
    $stale = FacesheetDao::loadMeds($fs, $clientId, false);
    Proc_ACE::record($clientId, $fs->activeMeds);
    Proc_ACEOrARB::record($clientId, $fs->activeMeds);
    Proc_BetaBlocker::record($clientId, $fs->activeMeds);
    FacesheetDao::loadDiagnoses($fs, $clientId);
    FacesheetDao::loadVitals($fs, $clientId);
    FacesheetDao::loadMedhx($fs, $clientId);
    //FacesheetDao::loadHm($fs, $fs->client);  // can remove once we have in/out_data for procedures
	
	try {
		//FacesheetDao::loadProcedures($fs, $clientId); //When we call this procedure, a fatal error occurs, "class desc not found". Might have to do with the OracleColumnWords function which converts DESC to DESC_....
		//FacesheetDao::loadCds($fs, $clientId); //This one too
		FacesheetDao::loadSurghx($fs, $clientId);
		FacesheetDao::loadFamhx($fs, $clientId, FacesheetDao::INCLUDE_DEFS);
		FacesheetDao::loadSochx($fs, $clientId);
		FacesheetDao::loadImmuns($fs, $clientId);
		FacesheetDao::loadTracking($fs, $clientId);
	}
	catch (Exception $e) {
	  Logger::debug('ERROR in FacesheetDao.php: ' . $e->getMessage());
	  
	}
    
    global $login;
    if ($login->cerberus) {
      require_once 'php/c/patient-billing/CerberusBilling.php';      
      $fs->superbills = CerberusBilling::getSuperbillStubs($clientId);
    }
    $fs->portalUser = PortalUsers::getFor($clientId);
    //$fs->unreviewed = Messaging_DocStubReview::getUnreviewedThreads($clientId);
    if ($stale) {
      if ($fs->procedures) {
        foreach ($fs->procedures as $proc) {
          if ($proc->ipc == Proc_NewCropRefresh::$IPC)
            $stale = false;
        }
      }
      if ($stale)
        Proc_NewCropRefresh::record($clientId);
    }
    $fs->medsNcStale = $stale;
    static::loadClientHistory($fs, $clientId);
    logit_r($fs);
    return $fs;
  }
  /**
   * Messaging facesheet (client, allergies, meds, vitals)
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getMsgFacesheet($clientId) {
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_MSG);
    FacesheetDao::loadClient($fs, $clientId, FacesheetDao::NO_HISTORY);
    FacesheetDao::loadAllergies($fs, $clientId, FacesheetDao::NO_HISTORY);
    FacesheetDao::loadMeds($fs, $clientId, FacesheetDao::NO_HISTORY);
    FacesheetDao::loadVitals($fs, $clientId);
    $fs->portalUser = PortalUsers::getFor($clientId);
    return $fs;
  }
  /**
   * Client facesheet with meds 
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getMedClientHistory($clientId) {
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_CLIENT);
    FacesheetDao::loadClient($fs, $clientId, true);
    FacesheetDao::loadMeds($fs, $clientId, true);
    return $fs;
  }
  /**
   * Client facesheet with med/allergies (e.g. after applying NewCrop updates)
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getClientActiveMedsAllergies($clientId) {
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_CLIENT);
    FacesheetDao::loadClient($fs, $clientId, false);
    FacesheetDao::loadMeds($fs, $clientId);
    FacesheetDao::loadAllergies($fs, $clientId, false);
    return $fs;
  }
  public static function reconcileAllergies($cid, $allers) {
    Allergies::reconcile($cid, $allers);
    $fs = static::getFacesheetAllergy($cid);
    return $fs;
  }
  /**
   * Client facesheet with legacy med/allergies/diagnoses for NewCrop click thru
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getLegacyClickThru($clientId) {
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_CLIENT);
    FacesheetDao::loadClient($fs, $clientId, FacesheetDao::NO_HISTORY);
    FacesheetDao::loadDiagnoses($fs, $clientId);
    FacesheetDao::loadMeds($fs, $clientId, FacesheetDao::NO_HISTORY);
    FacesheetDao::loadAllergies($fs, $clientId, FacesheetDao::NO_HISTORY);
    if ($fs->activeMeds)
      foreach ($fs->activeMeds as $med)
        if ($med->source == JDataMed::SOURCE_NEWCROP) {
          $fs->activeMeds = null;
          break;
        }
    $fs->allergies = FacesheetDao::filterInactive($fs->allergies);
    if ($fs->allergies)
      foreach ($fs->allergies as $allergy) 
        if ($allergy->source == JDataAllergy::SOURCE_NEWCROP) {
          $fs->allergies = null;
          break;
        }
    return $fs;
  }
  /**
   * Med facesheet
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getMedHistory($clientId) {
    LoginDao::authenticateClientId($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_MEDS);
    FacesheetDao::loadMeds($fs, $clientId);
    return $fs;
  }
  /**
   * Allergy facesheet
   * @param int $clientId
   * @return JFacesheet
   */
  public static function getFacesheetAllergy($clientId) {
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_ALLERGIES);
    FacesheetDao::loadAllergies($fs, $clientId);
    return $fs;
  }
  
  /**
   * Health Maintenance facesheet
   * @param int $clientId
   * @return JFacesheet
   */
    public static function getFacesheetHm($clientId) {
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_HM);
    $client = Clients::get($clientId);
    FacesheetDao::loadHm($fs, $client);
    return $fs;
  }
  /**
   * Med/Surg History facesheet
   * @param int $clientId
   * @return JFacesheet
   */
    public static function getFacesheetHist($clientId, $getHistProcs = false) {
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_HIST);
    FacesheetDao::loadHist($fs, $clientId, $getHistProcs); 
    return $fs;
  }
  /**
   * Get med history (med picker)
   * @param int $clientId
   * @return array('name'=>JDataMed,..)
   */
  public static function getMedPickerHistory($clientId) {
    LoginDao::authenticateClientId($clientId);
    return Meds::getByName($clientId);
  }
  /**
   * Refresh active meds/allergies by pulling NewCrop current status  
   * @param int $clientId
   * @param string $clientUid
   * @throws SoapResultException if result not 'OK'
   */
  public static function refreshFromNewCrop($clientId, $withAudits = true) {
    global $login;
    logit_r('refreshfromnc');
    LoginDao::authenticateClientId($clientId);
    $newcrop = new NewCrop();
    $current = $newcrop->pullCurrentMedAllergyU1($clientId);
    logit_r($current, 'current from newcrop');
    $date = nowYYYYMMDD();
    $noinsurance = Proc_FormularyNotChecked::recordIfNoInsurance($clientId, $date);
    Meds::rebuildFromNewCrop($clientId, $current, $withAudits, $noinsurance);
    Allergies::rebuildFromNewCrop($clientId, $current);
    if ($login->env != Env::ENV_LOCAL) {
      $mus = $newcrop->pullDailyMuReport($date);
      logit_r($mus, 'mus');
      Proc_Admin_MU2::save($date, $mus);
      logit_r('mus saved');
    }
  }
  /**
   * Summarize NewCrop updates from NewCrop since supplied time 
   * @param int $clientId
   * @param string $since 'yyyy-mm-dd hh:mm:ss'
   * @return array(
   *   'nc.add'=>array(JDataMed,..), 
   *   'nc.dc'=>array(JDataMed,..), 
   *   'nc.rx'=>array(JDataMed,..)) 
   */
  public static function getNewCropAuditsSince($clientId, $since) {
    return Meds::getNewCropAudits($clientId, $since);
  }
  /**
   * Get vitals with chronological age
   * @param JClient $client
   * @return [Vital,..]
   */
  public static function getGraphingVitals($client) {
    $vitals = Vitals::getActive($client->clientId);
    logit_r($vitals, 'getGraphingVitals');
    foreach ($vitals as &$vital) {
      $cage = chronAge($client->birth, $vital->date);
      $vital->cagey = $cage['y'] + ($cage['m'] / 12);
      $vital->cagem = $cage['m'] + (12 * $cage['y']) + ($cage['d'] / 30);
      //$vital->ht = $vital->htIn;
      //$vital->wt = $vital->wtLbs;
      $vital->htcm = $vital->getHtCm();
      $vital->htin = $vital->getHtIn();
      $vital->wtkg = $vital->getWtKg();
      $vital->wtlb = $vital->getWtLb();
      $vital->hccm = $vital->getHcCm();
      $vital->hcin = $vital->getHcIn();
      $gw = weeksBetween($client->birth, $vital->date) + (40 - intval($client->gestWeeks));
      if ($gw < 80)
        $vital->gw = $gw;
    }
    return array_values($vitals);
  }
  /**
   * Get questions for building vitals entry
   * @return array(prop=>JQuestion,..)
   */
  public static function getVitalQuestions() {
    return Vitals::getQuestions();
  }
  /**
   * Get questions for history category
   * @param string $cat
   * @return array(dsync=>JQuestion,..) 
   */
  public static function getHxQuestions($cat) {
    return DataDao::fetchQuestionsForPartialDataSync(1, "$cat.");
  }
  /**
   * Get questions for social history
   * @param string $cat
   * @return array(dsync=>JQuestion,..) 
   */
    public static function getSochxQuestions() {
    //return DataDao::fetchQuestionsForPartialDataSync(1, 'sochx.');
    return DataDao::fetchQuestionsForSocHx();
  }
  /**
   * Get questions for family history
   * @return array(puid=>              // puid is dsync prefix and field is dsync suffix, e.g. "+male"=>["status"=>JQuestion,"deathAge"=>JQuestion,..] 
   *   array(field=>JQuestion,..),..)
   */
  public static function getFamhxQuestions() {
    $m = DataDao::fetchQuestionsForFamHx('+male.');  
    $f = DataDao::fetchQuestionsForFamHx('+female.');
        return array(
        '+male' => $m,
        '+female' => $f);
  }
  /**
   * Get allergy question
   * @return JQuestion
   */
  public static function getAllergyQuestion() {
    return Allergies::getQuestion();
  }
  private static function loadAllergies(&$fs, $clientId, $includeHistory = true) {
    $fs->allergies = nullEmpty(Allergies::getAll($clientId));
    $active = FacesheetDao::filterInactive($fs->allergies);
    logit_r($active, 'active allergies');
    $fs->activeAllers = (empty($active)) ? array() : array_keys($active);
    if ($includeHistory) 
      $fs->allergiesHistory = Allergies::getHistoryByDate($clientId, $fs->activeMeds);
  }
  private static function loadMeds(&$fs, $clientId, $includeHistory = true) {
    global $login;
    $actives = Meds::getActive($clientId);   
    $fs->meds = nullEmpty(Meds::getAll($clientId, $actives));
    $fs->activeMeds = nullEmpty($actives);
    if ($includeHistory) {
      $fs->medsHistByDate = Meds::getHistoryByDate($clientId, $fs->activeMeds);
      //$fs->medsHistByMed = Meds::getHistoryByName($clientId, $fs->activeMeds);
    }
    if ($login->isErx()) {
      $fs->medsLastReview = Meds::getReviewHistory($clientId);
      $stale = MedsNewCrop::areAllNcStale($fs->activeMeds);
      return $stale; 
    }
  }
  public static function saveMed($m) {
    $med = Meds::save($m);
    $fs = FacesheetDao::getMedHistory($med->clientId);
    $fs->updatedMed = $med->name;
    return $fs;
  }
  public static function deleteLegacyMeds($clientId) {
    Meds::deactivateLegacy($clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_MEDS);
    FacesheetDao::loadMeds($fs, $clientId);
    FacesheetDao::loadAllergies($fs, $clientId, false);
    return $fs;
  }
  public static function printRxForMeds($meds) {
    $med = Meds::auditRxPrint($meds);
    $fs = FacesheetDao::getMedHistory($med->clientId);
    $fs->updatedMed = $med->name;
    return $fs;
  }
  public static function deactivateMed($id) {
    $med = Meds::deactivate($id);
    $fs = FacesheetDao::getMedHistory($med->clientId);
    $fs->updatedMed = $med->name;
    return $fs;
  }
  public static function deactivateMeds($ids) {
    $med = Meds::deactivateMultiple($ids);
    $fs = FacesheetDao::getMedHistory($med->clientId);
    $fs->updatedMed = $med->name;
    return $fs;
  }
  public static function saveAllergy($a) {
    $allergy = Allergies::save($a);
    return FacesheetDao::getFacesheetAllergy($allergy->clientId);
  }
  public static function deactivateAllergy($id) {
    $allergy = Allergies::deactivate($id);
    return FacesheetDao::getFacesheetAllergy($allergy->clientId);
  }
  public static function deactivateAllergies($ids) {
    $allergy = Allergies::deactivateMultiple($ids);
    return FacesheetDao::getFacesheetAllergy($allergy->clientId);
  }
  public static function deleteLegacyAllergies($clientId) {
    Allergies::deactivateLegacy($clientId);
    return FacesheetDao::getFacesheetAllergy($clientId);
  }
  
  public static function saveVital($v) {
    $vital = Vitals::save($v);
    logit_r($vital,'vital123');
    return FacesheetDao::getFacesheetVitals($vital->clientId);
  }

  public static function saveDiagnosis($d) {
    $diag = Diagnoses::save($d);
    $fs = new JFacesheet($diag->clientId, JFacesheet::CONTAINS_DIAGNOSES);
    FacesheetDao::loadDiagnoses($fs, $diag->clientId);
    return $fs;
  }
  
  public static function setDiagnosisNone($cid) {
    Diagnoses::setNoneActive($cid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_DIAGNOSES);
    FacesheetDao::loadDiagnoses($fs, $cid);
    return $fs;
  }
  
  public static function reconcileDiagnoses($cid, $diags) {
    Diagnoses::reconcile($cid, $diags);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_DIAGNOSES);
    FacesheetDao::loadDiagnoses($fs, $cid);
    return $fs;
  }
  
  public static function setMedsNone($cid) {
    Meds::setNoneActive($cid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_MEDS);
    FacesheetDao::loadMeds($fs, $cid);
    FacesheetDao::loadAllergies($fs, $cid, false);
    return $fs;
  }
  
  public static function saveReviewed($cid, $meds) {
    Meds::saveAsReviewed($cid, $meds);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_MEDS);
    FacesheetDao::loadMeds($fs, $cid);
    FacesheetDao::loadAllergies($fs, $cid, false);
    return $fs;
  }
    
  public static function deleteDiagnosis($id) {
    $clientId = Diagnoses::delete($id);
    if ($clientId) {
      $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_DIAGNOSES);
      FacesheetDao::loadDiagnoses($fs, $clientId);
      return $fs;
    }
  } 
  
  public static function saveSochx($cid, $dsyncs) {
    LoginDao::authenticateClientId($cid);
    DataDao::saveDataSyncs($cid, $dsyncs);
    //AuditDao::log($cid, AuditDao::ENTITY_FACESHEET, null, AuditDao::ACTION_UPDATE);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_SOCHX);
    FacesheetDao::loadSochx($fs, $cid);
    $nonNullRecs = $fs->sochx->nonNullRecs;
    DataDao::saveDataSync($cid, "sochx", jsonencode($nonNullRecs));  // save the records with values to sochx datasync (psHx.*psHx include question)
    if (in_array("Drug Use History", $nonNullRecs)) {
      DataDao::saveDataSync($cid, "detDrugHx", '["LIMITED DRUG HISTORY"]');  
    }
    if (in_array("Tobacco", $nonNullRecs)) {
      if (static::hasSmokingData($dsyncs))
        Proc_SmokingHxRecorded::record($cid);
    }
    return $fs;
  }
  private static function hasSmokingData($dsyncs) {
    $qs = array('exposure','freq','neverStatus','ppd','recode','uses');
    foreach ($qs as $q)
      if (static::hasData($dsyncs, $q))
        return true;
  }
  private static function hasData($dsyncs, $field) {
    $value = get($dsyncs, "sochx.tob.$field");
    if ($value && $value != '[]')
      return true;
  }
  
  public static function saveHx($cid, $cat, $rec, $returnFs = true) {
    LoginDao::authenticateClientId($cid);
    DataDao::saveDataSyncs($cid, $rec);
    if ($returnFs) {
      return FacesheetDao::rebuildFsForHxCat($cid, $cat);
    }
  }
  
  public static function saveFamhx($cid, $rec) {
    LoginDao::authenticateClientId($cid);
    DataDao::saveDataSyncs($cid, $rec);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }

  public static function saveFamhx_asAdopted($cid) {
    global $login;
    LoginDao::authenticateClientId($cid);
    DataSync_Fam::save_asAdopted($login->userGroupId, $cid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }
  
  public static function saveFamhx_asUnknown($cid) {
    global $login;
    LoginDao::authenticateClientId($cid);
    DataSync_Fam::save_asUnknown($login->userGroupId, $cid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }
  
  public static function saveFamhx_asClear($cid) {
    global $login;
    LoginDao::authenticateClientId($cid);
    DataSync_Fam::save_asClear($login->userGroupId, $cid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }
  
  public static function removeFamhx($cid, $puid) {
    LoginDao::authenticateClientId($cid);
    DataDao::removeDataSyncFamPuid($cid, JDataSyncFamGroup::SUID_FAM, $puid);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }
  
  private static function rebuildFsForHxCat($cid, $cat) {
    //AuditDao::log($cid, AuditDao::ENTITY_FACESHEET, null, AuditDao::ACTION_UPDATE);
    if ($cat == JDataSyncProcGroup::CAT_MED) {
      $fs = new JFacesheet($cid, JFacesheet::CONTAINS_MEDHX);
      FacesheetDao::loadMedhx($fs, $cid);
    } else {
      $fs = new JFacesheet($cid, JFacesheet::CONTAINS_SURGHX);
      FacesheetDao::loadProcedures($fs, $cid);
      FacesheetDao::loadSurghx($fs, $cid);
    }
    return $fs;
  }
  
  public static function saveHxs($cid, $cat, $recs) {
    $j = count($recs) - 1;
    for ($i = 0; $i <= $j; $i++) {
      $fs = FacesheetDao::saveHx($cid, $cat, $recs[$i], $i == $j);
    }
    return $fs;
  }

  public static function copyToMedHx($o) {
    //Diagnoses::copyToMedHx($name);
    $diag = Diagnoses::save($o);
    $cid = $o->clientId;
    $recs = DataDao::fetchDataSyncProcGroup('pmhx', $cid);
    $procs = ($recs) ? $recs->procs : array();
    $procs[] = $o->text;
    $procs = json_encode(array_distinct($procs));
    FacesheetDao::saveHxProcs($cid, 'pmhx', $procs);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_DIAGNOSES);
    FacesheetDao::loadDiagnoses($fs, $cid);
    return $fs;
  }
    
  public static function saveHxProcs($cid, $cat, $procs) {
    LoginDao::authenticateClientId($cid);
    DataDao::saveDataSyncs($cid, array($cat => $procs));
    return FacesheetDao::rebuildFsForHxCat($cid, $cat);
  }
  /*
   * Assign family HX relatives (selected options of SUID question)  
   */
  public static function saveFamHxSopts($cid, $suid, $sopts) {
    LoginDao::authenticateClientId($cid);
    DataDao::saveDataSync($cid, $suid, $sopts);
    $fs = new JFacesheet($cid, JFacesheet::CONTAINS_FAMHX);
    FacesheetDao::loadFamhx($fs, $cid);
    return $fs;
  }
  
  public static function saveHmInt($h) {
    $hm = JDataHm::getHm($h->id);
    $hm->interval = $h->int;
    $hm->every = $h->every;
    JDataHm::save($hm, true);
    return FacesheetDao::getFacesheetHm($hm->clientId);
  }

  public static function saveHm($h) {
    global $login;
    if ($h->id != null) {
      $hm = JDataHm::getHm($h->id);
      if ($hm->sessionId == null) {  // updating facesheet item
        $hm->nextText = $h->nextText;
        $hm->nextTimestamp = strtotime($h->nextSort);
      } else {  // updating history item
        $hm->dateText = $h->dateText;
        $hm->dateSort = $h->dateSort;
        $hm->results = $h->results;
      }
    } else {
      LoginDao::authenticateClientId($h->clientId);
      $hm = new JDataHm(null,
          $login->userGroupId,
          $h->clientId,
          0,
          $h->type,
          $h->procId,
          $h->proc,
          $h->dateText,
          $h->dateSort,
          $h->results,
          null,
          null,
          true,
          null,
          null,
          null);
    }
    JDataHm::save($hm, true);
    return FacesheetDao::getFacesheetHm($hm->clientId);
  }

  public static function addFacesheetHm($h) {
    LoginDao::authenticateClientId($h->clientId);
    FacesheetDao::saveBlankFacesheetHm($h->clientId, $h->proc);
    return FacesheetDao::getFacesheetHm($h->clientId);
  }

  public static function deactivateHm($id, $returnFs = true) {
    $hm = JDataHm::getHm($id);
    $hm->active = false;
    JDataHm::save($hm, true);
    if ($returnFs) {
      return FacesheetDao::getFacesheetHm($hm->clientId);
    }
  }

  public static function deactivateHms($a) {
    for ($i = 0; $i < count($a); $i++) {
      $fs = FacesheetDao::deactivateHm($a[$i], $i == (count($a) - 1));
    }
    return $fs;
  }

  public static function deactivateDiagnosis($id, $returnFs = true) {
    $d = JDataDiagnosis::getDiagnosis($id);
    $d->active = false;
    JDataDiagnosis::save($d, true);
    if ($returnFs) {
      $fs = new JFacesheet($d->clientId, JFacesheet::CONTAINS_DIAGNOSES);
      FacesheetDao::loadDiagnoses($fs, $d->clientId);
      return $fs;
    }
  }

  public static function deactivateDiagnoses($a) {
    for ($i = 0; $i < count($a); $i++) {
      $fs = FacesheetDao::deactivateDiagnosis($a[$i], $i == (count($a) - 1));
    }
    return $fs;
  }
  public function getFacesheetVitals($clientId) {
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_VITALS);
    FacesheetDao::loadVitals($fs, $clientId);
    return $fs;
  }
  
  public static function deactivateVital($id) {
    $vital = Vitals::deactivate($id);
    return FacesheetDao::getFacesheetVitals($vital->clientId);
  }

  // Docs of practice
  private static function loadDocs(&$fs) {
    global $login;
    $fs->docs = UserDao::getDocsOfGroup($login->userGroupId);
  }

  // Client and history
  private static function loadClient(&$fs, $clientId, $includeHistory = true) {
    $fs->client = Clients::get($clientId);
    if ($includeHistory) {
      static::loadClientHistory($fs, $clientId);
    }
  }
  private static function loadClientHistory(&$fs, $clientId) {
    $fs->docstubs = Documentation::getAll($clientId);
    //$appt = DocStub::getNextAppt($fs->docstubs);
    $appt = Scheduling::getNextAppt($clientId);
    logit_r($appt, 'next appt');
    if ($appt) {
      //$appt->_date = formatInformalDate($appt->timestamp);
      $appt->name = $appt->getLabel();
      $fs->workflow->appt = $appt;
    }
    
    /*
    $fs->clientHistory = SchedDao::getJClientHistory($clientId);
    $hist = $fs->clientHistory;
    $fs->workflow->appt = null;
    $fs->workflow->docs = array();
    $iDocs = 0;
    foreach ($hist->all as &$a) {
      if ($a->type == JHistoryRef::TYPE_APPT) {
        if (isTodayOrFuture($a->date)) {
          $fs->workflow->appt = $hist->appts[$a->id];
        }
      } else if ($a->type == JHistoryRef::TYPE_SESSION) {
        $s = $hist->sessions[$a->id];
        if (! $s->closed && $iDocs < 2) {
          $fs->workflow->docs[] = $s;
          $iDocs++;
        }
      }
      if ($fs->workflow->appt != null && $iDocs == 3) {
        return;
      }
    }
    */
  }

  public static function saveClientNotes($clientId, $note) {
    Clients::updateNotes($note, $clientId);
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_CLIENT);
    FacesheetDao::loadClient($fs, $clientId);
    return $fs;
  }

  // Procedures
  private static function loadProcedures(&$fs, $clientId, $includeHistory = true) {
    $fs->procedures = nullempty(Procedures::getAll($clientId));
  }
  
  // Diagnoses
  private static function loadDiagnoses(&$fs, $clientId, $includeHistory = true) {
    $fs->diagnoses = nullempty(Diagnoses::getAll($clientId));
    if ($includeHistory) 
      $fs->diagnosesHistory = Diagnoses::getHistory($clientId);
  }

  private static function getDiagnoses($clientId) {
    FacesheetDao::addFacesheetDiagnoses($clientId);
    return JDataDiagnosis::getActiveFacesheetDiagnoses($clientId);
  }
  
  // Social History
  private static function loadSochx(&$fs, $clientId) {
    $fs->sochx = DataDao::fetchDataSyncGroup(JDataSyncGroup::GROUP_SOCHX, $clientId);
  }
  
  // Med/Surg/Fam History
  private static function loadMedhx(&$fs, $clientId) {
    $fs->medhx = DataDao::fetchDataSyncProcGroup(JDataSyncProcGroup::CAT_MED, $clientId);
  }
  private static function loadSurghx(&$fs, $clientId) {
    $fs->surghx = DataDao::fetchDataSyncProcGroup(JDataSyncProcGroup::CAT_SURG, $clientId);
  }
  private static function loadFamhx(&$fs, $clientId, $includeDefs = false) {
    $fs->famhx = DataDao::fetchDataSyncFamGroup(JDataSyncFamGroup::SUID_FAM, $clientId, $includeDefs);
  }
  
  /*
   * Return JQuestion for requested category's proc list definition 
   */
  public static function getProcQuestion($cat) {
    return DataDao::fetchDataSyncQuestion($cat, 1);
  }
  public static function getSuidQuestion($suid) {
    return DataDao::fetchDataSyncQuestion($suid, 1);  
  }
  
  private static function loadHist(&$fs, $clientId, $needProcs = true) {
    $fs->histCats = array();
    FacesheetDao::addHistCat($fs->histCats, JDataSyncProcGroup::CAT_MED, $clientId, $needProcs);
    FacesheetDao::addHistCat($fs->histCats, JDataSyncProcGroup::CAT_SURG, $clientId, $needProcs);
  }
  
  private static function addHistCat(&$histCats, $cat, $clientId, $needProcs) {
    // $procs = ($needProcs) ? FacesheetDao::buildHistProcs($cat) : null;
    $pq = ($needProcs) ? DataDao::fetchDataSyncQuestion($cat, 1) : null;
    $recs = DataDao::fetchDataSyncProcGroup($cat, $clientId);
    $histCats[$cat] = new JHistCat($cat, $recs, $pq);
  }
  
  // Clinical Decision Support
  private static function loadCds(&$fs, $cid) {
    $fs->ipcHms = HealthMaint::getForClient($cid);
  }

  // Health Maintenance
  private static function loadHm(&$fs, $client) {
    $fs->hmProcs = FacesheetDao::buildHmProcs($client);
    $fs->hms = FacesheetDao::filterInactive(FacesheetDao::rebuildFacesheetHms($client->clientId, $fs->hmProcs));
    $fs->hmsHistory = JDataHm::getHmsHistory($client->clientId);
  }

  // Build facesheet records for health maintenance
  private static function rebuildFacesheetHms($clientId, $procs) {
    FacesheetDao::buildHmsHistory($clientId, $procs);
    $histRecs = JDataHm::getHistHmsByProc($clientId);
    $faceRecs = JDataHm::getFacesheetHms($clientId);
    //logit_r($procs, 'before procs');
    foreach ($procs as $procId => $proc) {
      $face = (isset($faceRecs[$proc->name])) ? $faceRecs[$proc->name] : null;
      $hist = (isset($histRecs[$proc->name])) ? $histRecs[$proc->name] : null;
      if ($hist != null) {
        if ($face == null) {
          $face = JDataHm::copy($hist);
          $face->sessionId = null;
          $face->active = true;
          $face->setHmNextDate($proc);
          $faceRecs[$face->proc] = JDataHm::save($face, true, FacesheetDao::NO_AUDIT);
        } else {
          if (compareDates($hist->updated, $face->updated) > 0 ||  // hist is newer
              JDataHm::areDifferent($face, $hist)) {  // needs to be refreshed
            $face->setDate($hist->dateText, $hist->dateSort);
            $face->dateSort = $hist->dateSort;
            $face->results = $hist->results;
            $face->active = true;
            $face->setHmNextDate($proc);
            $faceRecs[$face->proc] = JDataHm::save($face, true, FacesheetDao::NO_AUDIT);
          }
        }
      } else {
        if ($proc->apply) {
          if ($face == null) {
            $faceRecs[$proc->name] = FacesheetDao::saveBlankFacesheetHm($clientId, $proc, FacesheetDao::NO_AUDIT);
            $face = $faceRecs[$proc->name]; 
          }
          $face->setHmDueNow();
          $faceRecs[$face->proc] = JDataHm::save($face, true, FacesheetDao::NO_AUDIT);
        } else {
          if ($face != null && $face->dateText) {  // history was removed, clear out facesheet rec  
            $face->setDate(null, null);
            $face->results = null;
            $faceRecs[$face->proc] = JDataHm::save($face, true, FacesheetDao::NO_AUDIT);
          }
        }
      }
    }
    //logit_r($faceRecs, 'after procs');
    return $faceRecs;
  }

  private static function saveBlankFacesheetHm($clientId, $proc, $audit = true) {
    global $login;
    //logit("saveblank");
    //logit_r($proc);
    $face = new JDataHm(null,
        $login->userGroupId,
        $clientId,
        null,
        1,  // TODO: type
        $proc->id,
        $proc->name,
        null,
        null,
        null,
        null,
        null,
        true,
        null,
        null,
        null);
    return JDataHm::save($face, null, $audit);
  }

  // Build health maintenance history with session=0 recs
  private static function buildHmsHistory($clientId, $procs) {
    $sessRecs = JDataHm::getUnreadSessionHms($clientId);     // session>0 + active=null
    $histRecs = JDataHm::getHistHmsByProcDateKey($clientId); // session=0
    $lastKey = null;
    //logit_r($sessRecs);
    foreach ($sessRecs as &$sess) {
//      if ($sess->proc == null && $sess->procId) {  // assign proc name if null
//        $sess->proc = $procs[$sess->procId]->name;
//      }
      $key = $sess->buildProcDateKey();  // "procId,dateSort"
      if ($key != $lastKey) {
        $lastKey = $key;
        if (isset($histRecs[$key])) {
          $hist = $histRecs[$key];
          if (compareDates($sess->updated, $hist->updated) > 0) {  // session is newer
            $hist->setDate($sess->dateText, $sess->dateSort);
            $hist->results = $sess->results;
            $hist->active = true;
            JDataHm::save($hist, true, FacesheetDao::NO_AUDIT);
          }
        } else {
          $hist = JDataHm::copy($sess);
          $hist->sessionId = 0;
          $hist->active = true;
          JDataHm::save($hist, true, FacesheetDao::NO_AUDIT);
        }
      }
      $sess->active = false;
      JDataHm::save($sess, true, FacesheetDao::NO_AUDIT);
    }
  }

  /*
   * Builds procs from multi-options of data sync question
   * Returns [proc,..]
   */
  private static function buildHistProcs($cat) {
    $qid = DataDao::fetchDataSyncQid($cat, 1);
    $q = TemplateReaderDao::getQuestion($qid, true);
    $procs = array();
    for ($i = $q->mix; $i < count($q->options); $i++) {
      $procs[] = TemplateReaderDao::pvOptText($q->options[$i]);
    }
    return $procs;
  }

  private static function buildHmProcs($client) {
    $procs = LookupDao::getDataHmProcs($client->clientId);
    $icds = JDataDiagnosis::getActiveDiagnosesIcd($client->clientId);
    foreach ($procs as &$proc) {
      $proc->apply = JDataHm::shouldApply($proc, $client, $icds);
    }
    return $procs;
  }

  /**
   * Immunizations
   */
  private static function loadImmuns(&$fs, $clientId) {
    $fs->immuns = Immuns::getActive($clientId);
    $fs->immunPid = Immuns::getPid();
    if (! isset($fs->client))
      $fs->client = Clients::get($clientId); 
    if ($fs->client->ageYears <= 19) {
      require_once 'php/c/immun-cds/ImmunCds.php';
      $fs->immunCd = ImmunCds::get($clientId);
    }
  }
  /**
   * @param $rec: data object from facesheet 
   * @return JFacesheet populated with only immunizations
   */
  public static function saveImmun($rec) {
    $immun = Immuns::save($rec);
    $fs = new JFacesheet($immun->clientId, JFacesheet::CONTAINS_IMMUN);
    FacesheetDao::loadImmuns($fs, $immun->clientId);
    return $fs;
  }
  /**
   * @param int $id 
   * @return JFacesheet populated with only immunizations
   */
  public static function deleteImmun($id) {
    $clientId = Immuns::delete($id);
    if ($clientId) {
      $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_IMMUN);
      FacesheetDao::loadImmuns($fs, $clientId);
      return $fs;
    }
  }
  /**
   * Order Tracking
   */
  private static function loadTracking(&$fs, $clientId) {
    $fs->tracking = OrderEntry::getActiveItems($clientId);
  }
  /**
   * @param int $clientId
   * @return JFacesheet with tracking only
   */
  public static function getTrackingFacesheet($clientId) {
    $fs = new JFacesheet($clientId, JFacesheet::CONTAINS_TRACKING);
    FacesheetDao::loadTracking($fs, $clientId);
    return $fs;
  }
  
  /*
   * Vitals
   */
  private static function loadVitals(&$fs, $clientId) {
    $fs->vitals = nullEmpty(Vitals::getActive($clientId));
    if ($fs->vitals && $fs->workflow) {
      $fs->workflow->vital = null;
      foreach ($fs->vitals as &$v) {
        if (isToday($v->date)) {
          $fs->workflow->vital = $v;
          return;
        }
      }
    }
  }

  /** 
   * Static helpers
   */
  public static function filterInactive($recs) {
    if ($recs) { 
      foreach ($recs as $key => $rec) {
        if (! $rec->active) {
          unset($recs[$key]);
        }
      }
    }
    return $recs;
  }
  public static function overrideIfNotBlank($a, $b) {
    return (isBlank($b)) ? $a : $b;
  }
  public static function isSessNewer($sess, $face) {  // true if session record is newer than facesheet record
    $cd1 = compareDates($sess->date, $face->date, true);  // DOS
    $cd2 = compareDates($sess->updated, $face->updated);
    return ($cd1 > 0 || ($cd1 == 0 && $cd2 > 0));  // only compare update dates if DOS are same
  }
  // Sync history records with active setting of facesheet records
  public static function syncActive($histRecs, $faceRecs, $keyField) {  // faceRec association key field
    foreach ($histRecs as &$histRec) {
      $histRec->active = $faceRecs[$histRec->$keyField]->active;
    }
    return $histRecs;
  }
}
function nullEmpty($o) {
  return (empty($o)) ? null : $o;
}
class DataSync_Fam extends SqlRec {
  //
  public $dataSyncId;
  public $userGroupId;
  public $clientId;
  public $dsyncId;
  public $dsync;
  public $dateSort;
  public $sessionId;
  public $value;
  public $active;
  public $dateUpdated;
  //
  public function getSqlTable() {
    return 'data_syncs';
  }
  public function fetchForAudit() {
    $c = new static();
    $c->userGroupId = $this->userGroupId;
    $c->clientId = $this->clientId;
    $c->dsyncId = $this->dsyncId;
    return static::fetchOneBy($c);
  }
  //
  static function save_($ugid, $cid, $value) {
    $me = static::asCriteria($ugid, $cid);
    $me->dateSort = nowNoQuotes();
    $me->value = '["' . $value . '"]';
    $me->active = 1;
    $me->dateUpdated = nowNoQuotes();
    $me->save($ugid, SaveModes::INSERT_ON_DUPE_UPDATE);
    return $me;
  }
  static function save_asAdopted($ugid, $cid) {
    return static::save_($ugid, $cid, 'Adopted; family history is unknown');
  }
  static function save_asUnknown($ugid, $cid) {
    return static::save_($ugid, $cid, 'Family History is Unknown');
  }
  static function save_asClear($ugid, $cid) {
    $c = static::asCriteria($ugid, $cid);
    $me = static::fetchOneBy($c);
    if ($me) 
      static::delete($me);
  }
  static function asCriteria($ugid, $cid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->dsyncId = 'famHx';
    $c->dsync = 'famHx';
    return $c;
  }
}
//
require_once "php/dao/_util.php";
require_once 'php/data/LoginSession.php';
require_once "php/dao/SchedDao.php";
//require_once "php/dao/AuditDao.php";
require_once "php/data/json/JFacesheet.php";
require_once "php/data/json/JDataHm.php";
require_once "php/data/json/JHistCat.php";
require_once "php/data/json/JWorkflow.php";
require_once "php/data/json/JDataValidator.php";
require_once "php/data/json/JDataSyncGroup.php";
require_once "php/data/json/JDataSyncProcGroup.php";
require_once "php/data/json/JDataSyncFamGroup.php";
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Allergies.php';
require_once 'php/data/rec/sql/Vitals.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/Immuns.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/c/reporting/Reporting.php';
require_once 'php/c/health-maint/HealthMaint.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/PortalUsers.php';
require_once 'php/c/scheduling/Scheduling.php';
