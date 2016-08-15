<?php
require_once 'ApiException.php';
require_once './dao/ApiDao.php';
require_once './dao/data/ApiLogin.php';
require_once './dao/data/ApiSelectPatient.php';
require_once './dao/data/ApiPatient.php';
require_once './dao/data/ApiPatientMap.php';
require_once './dao/data/ApiPollStatus.php';
require_once './dao/data/ApiPractice.php';
require_once './dao/data/ApiUser.php';
require_once './dao/data/Xml.php';
require_once './dao/ApiLogger.php';
set_include_path('../sec/');
require_once 'php/data/LoginSession.php';
/**
 * Cerberus web service handler
 */
class Cerberus {
  //
  private $dao;
  private $tiani;
  /**
   * Constructor
   */
  public function __construct() {
    $this->dao = new ApiDao(ApiDao::PARTNER_CERBERUS);
    //$this->tiani = new Tiani();
  }
  /**
   * Process REST request
   * @param Rest $rest
   * @return 'Status,Response'
   * @throws CerberusApiException
   */
  public function request($rest) {
    try {
      log2_r($rest, 'Cerberus::request');
      $op = strtolower(Data::get($rest->data, 'operation'));
      unset($rest->data['operation']);
      if ($op == 'login') { 
        $response = $this->requestLogin($rest);
      } else {
        $sessionId = Data::get($rest->data, 'sessionId');
        if ($sessionId == null) 
          throw new ApiException('Session required.');
        ApiDao::requireLogin($sessionId);
        switch ($op) {
          case 'practice':
            $response = $this->requestPractice($rest);
            break;
          case 'patient':
            $response = $this->requestPatient($rest);
            break;
          case 'mappatient':
            $response = $this->requestMapPatient($rest);
            break;
          case 'user':
            $response = $this->requestUser($rest);
            break;
          case 'pollstatus':
            $response = $this->requestPollStatus($rest);
            break;
          case 'selectpatient':
            $response = $this->requestSelectPatient($rest);
            break;
          case 'patientinfo':
            $response = $this->requestPatientInfo($rest);
            break;
          default:
            header('Request not recognized.', true, 405);
            return;        
        }
      }
      return $response;
    } catch (ApiException $e) {
      throw new CerberusApiException($e->getMessage());
    } catch (Exception $e) {
      throw new CerberusApiException($e->getMessage());
    }
  }
  /**
   * Retrieve patient info by CLIENT_ID
   * @param Rest $rest
   * @return '<patient>...</patient>'
   */
  public function requestPatientInfo($rest) {
    $cid = $rest->data['id'];
    $patient = $this->dao->getPatient($cid);
    $xml = new Xml($patient);
    return $xml->out();
  }
  /**
   * Select patient request
   * @param Rest $rest
   * @return 'OK,url'
   * @throws CerberusApiException
   */
  public function requestSelectPatient($rest) {
    $selectPatient = new ApiSelectPatient($rest->data);
    $url = $this->dao->selectPatient($selectPatient);
    return $this->response('OK', $url);
  }
  /**
   * Login request
   * @param Rest $rest
   * @return 'OK,session_id,unread_count,unreviewed_count,msg_url,review_url,doc_url,status_url,pharm_url,scan_url,track_url,report_url'
   */
  public function requestLogin($rest) {
    $login = new ApiLogin($rest->data);
    $params = $this->dao->login($login);
    return $this->response('OK', $params);
  }
  /**
   * Poll status request 
   * @param Rest $rest
   * @return 'OK,unread_count,unreviewed_count,status,status_all,pharm,pharm_all'
   */
  public function requestPollStatus($rest) {
    $pollStatus = new ApiPollStatus($rest->data);
    $params = $this->dao->pollStatus($pollStatus);
    return $this->response('OK', $params);
  }
  /**
   * User request
   * @param Rest $rest
   * @return 'OK,user_id' 
   */
  public function requestUser($rest) {
    $user = new ApiUser($rest->data);
    $id = $this->dao->saveUser($user);
    return $this->response('OK', $id);
  }
  /**
   * Practice request   
   * @param Rest $rest
   * @return 'OK,ugid' 
   */
  public function requestPractice($rest) {
    $practice = new ApiPractice($rest->data);
    $ugid = $this->dao->savePractice($practice);
    return $this->response('OK', $ugid);
  }
  /**
   * Patient request   
   * @param Rest $rest
   * @return 'OK,cid'
   */
  public function requestPatient($rest) {
    $patient = new ApiPatient($rest->data);
    log2_r($patient, 'ApiPatient');
    $patient->patientId = $this->dao->savePatient($patient);
    //$this->tiani->managePatientRecord($patient, Tiani::getTestProvider());
    return $this->response('OK', $patient->patientId);
  }
  //
  public function requestMapPatient($rest) {
    $patient = new ApiPatientMap($rest->data);
    log2_r($patient, 'ApiPatientMap');
    $patient->patientId = $this->dao->mapPatient($patient);
    //$this->tiani->managePatientRecord($patient, Tiani::getTestProvider());
    return $this->response('OK', $patient->patientId);
    
  }
  /**
   * Format response
   * @param string $status: 'OK'; 'ERROR'
   * @param(opt) string $data: 'data-to-return'
   *              array $data: ['data','to','return'] 
   * @return 'status,data,to,return'
   */
  public function response($status, $data = array()) {
    if (! is_array($data)) {
      $data = array($data);
    }
    array_unshift($data, $status);
    return implode(',', $data);
  }
}
/**
 * Cerberus API Exception
 */
class CerberusApiException extends ApiException {
  function getResponse() {
    return "ERROR," . $this->getMessage();
  }
}
