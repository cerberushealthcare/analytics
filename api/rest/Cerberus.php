<?php
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'ApiException.php';
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'dao/ApiDao.php';
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'dao/data/ApiLogin.php';
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'dao/data/ApiSelectPatient.php';
require_once 'dao/data/ApiPatient.php';
require_once 'dao/data/ApiPatientMap.php';
require_once 'dao/data/ApiPollStatus.php';
require_once 'dao/data/ApiPractice.php';
require_once 'dao/data/ApiUser.php';
require_once 'dao/data/Xml.php';
//require_once 'dao/ApiLogger.php';
require_once 'php/data/LoginSession.php';
require_once 'batch/_batch.php'; //Simply include this in order to use blog()
require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt


//These two needed for clinical file import.
require_once 'php/data/rec/group-folder/GroupFolder_ClinicalImport.php';
require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
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
	blog('Cerberus.php: Entered request');
    try {
      log2_r($rest, 'Cerberus::request');
      $op = strtolower(Data::get($rest->data, 'operation'));
      unset($rest->data['operation']);
	  log2('op is ' . $op);
      if ($op == 'login') {
        $response = $this->requestLogin($rest);
      } else {
		
		/*try {
			$sessionId = Data::get($rest->data, 'sessionId');
		}
		catch (Exception $e) {
			throw new ApiException('Session required.');
		}*/
		
		blog('analytics/api/Cerberus.php: The op is ' . $op);
        //ApiDao::requireLogin($sessionId);
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
		  case 'ccdupload':
			$response = $this->requestCCD_UPLOAD($rest);
			break;
		  case 'ccdupload_custom':
			$response = $this->requestCCD_UPLOAD_test($rest);
			break;
          default:
            header('Request not recognized.', true, 405);
            return;        
        }
      }
	  echo 'return response';
      return $response;
    } catch (ApiException $e) {
		echo 'ApiException';
      throw new CerberusApiException($e->getMessage());
    } catch (Exception $e) {
		echo 'Exception';
      throw new CerberusApiException($e->getMessage());
    }
  }
  
   /**
   * Login request
   * @param Rest $rest
   * @return 'OK,session_id,unread_count,unreviewed_count,msg_url,review_url,doc_url,status_url,pharm_url,scan_url,track_url,report_url'
   */
  public function requestCCD_UPLOAD($rest) {
	blog('rest/Cerberus.php: CCDUPLOAD triggered.');
	try {
		try {
			$sessionId = Data::get($rest->data, 'sessionId');
		}
		catch (Exception $e) {
			throw new ApiException('Session required.');
		}
		
		//Hacky workaround: When we call this process from a web service, $_GET is empty. And some of the functions below, most notably the clinical file upload process,
		//depends on $_GET having certain values, otherwise it will choke.
		
		//To work around this, populate the $_GET variables it needs manually if we detect that they're empty. If they're empty it means we must be using a web service.
		if (!isset($_GET['userGroupId'])) {
			blog('User group ID is empty! Set the GET var to ' .  Data::get($rest->data, 'userGroupId'));
			$_GET['userGroupId'] = Data::get($rest->data, 'userGroupId');
			blog('rest/Cerberus.php:GET userGroupID is now ' .  $_GET['userGroupId']);
		}
		
		echo 'rest/Cerberus.php:Entered requestCCDUpload session ID is ' . $sessionId;
		//$sessionId = $rest->data['sessionId'];
		//LoginSession::login('mm', 'clicktate1');
		
		$fileQualifiedPath = MyEnv::$BASE_PATH . '/analytics/' . $rest->data['filepath'] . $rest->data['filename'];
		
		blog('rest/Cerberus.php:Looking at the file path ' . $fileQualifiedPath);
		
		if (!file_exists($fileQualifiedPath)) {
			throw new RuntimeException('File does not exist: ' . htmlentities($fileQualifiedPath));
		}
		
		$file = new ClinicalFile;
		
		//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
		$file->setContent(file_get_contents($fileQualifiedPath));
		$file->setFilename($rest->data['filename']);

		if (gettype($file) !== 'object') {
			throw new RuntimeException("Can't import anything that isn't a <b>ClinicalFile</b> object. I got a(n) " . gettype($file));
		}
		
		blog('rest/Cerberus.php: Importing file name ' . $rest->data['filename'] . '. Path is ' . $fileQualifiedPath);
		try {
			$result = ClinicalImporter::importFromFile($file);
			blog('cerberus.php: Successfully imported ' . $fileQualifiedPath . '!!');
		}
		catch (Exception $e) {
			
			throw new RuntimeException('cerberus.php: Could not import the file: ' . $e->getMessage());
		}
		blog('Imported ' . $fileQualifiedPath . ' successfully!');
		echo 'OK. ' . $rest->data['sessionId'];
	}
	catch (Exception $e) {
		//$errorLog->log($e->getMessage()) for debugging
		blog('cerberus.php ERROR: Could not import the file: ' . $e->getMessage());
		echo 'ERROR, ' . $e->getMessage();
	}
	blog('------');
  }
  
  
  
   /**
	 Mainly used for a test.
   * Login request
   * @param Rest $rest
   * @return 'OK,session_id,unread_count,unreviewed_count,msg_url,review_url,doc_url,status_url,pharm_url,scan_url,track_url,report_url'
   */
  public function requestCCD_UPLOAD_test($rest) {
	Logger::debug('rest/Cerberus.php: CCDUPLOAD TEST triggered.');
	try {
		try {
			$sessionId = Data::get($rest->data, 'sessionId');
		}
		catch (Exception $e) {
			throw new ApiException('Session required.');
		}
		
		//Hacky workaround: When we call this process from a web service, $_GET is empty. And some of the functions below, most notably the clinical file upload process,
		//depends on $_GET having certain values, otherwise it will choke.
		
		//To work around this, populate the $_GET variables it needs manually if we detect that they're empty. If they're empty it means we must be using a web service.
		if (!isset($_GET['userGroupId'])) {
			Logger::debug('User group ID is empty! Set the GET var to ' .  Data::get($rest->data, 'userGroupId'));
			$_GET['userGroupId'] = Data::get($rest->data, 'userGroupId');
			Logger::debug('rest/Cerberus.php:GET userGroupID is now ' .  $_GET['userGroupId']);
		}
		
		echo 'rest/Cerberus.php:Entered requestCCDUpload_test session ID is ' . $sessionId;
		//$sessionId = $rest->data['sessionId'];
		//LoginSession::login('mm', 'clicktate1');
		
		$fileQualifiedPath = MyEnv::$BASE_PATH . '/analytics/' . $rest->data['filepath'] . $rest->data['filename'];
		
		Logger::debug('rest/Cerberus.php:Looking at the file path ' . $fileQualifiedPath);
		
		if (!file_exists($fileQualifiedPath)) {
			throw new RuntimeException('File does not exist: ' . htmlentities($fileQualifiedPath));
		}
		
		$file = new ClinicalFile;
		
		//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
		$file->setContent(file_get_contents($fileQualifiedPath));
		$file->setFilename($rest->data['filename']);

		if (gettype($file) !== 'object') {
			throw new RuntimeException("Can't import anything that isn't a <b>ClinicalFile</b> object. I got a(n) " . gettype($file));
		}
		
		Logger::debug('rest/Cerberus.php: Importing file name ' . $rest->data['filename'] . '. Path is ' . $fileQualifiedPath);
		try {
			$result = ClinicalImporter::importFromFile($file);
			blog('cerberus.php: Successfully imported ' . $fileQualifiedPath . '!!');
		}
		catch (Exception $e) {
			throw new RuntimeException('cerberus.php: Could not import the file: ' . $e->getMessage());
		}
		echo 'OK. ' . $rest->data['sessionId'];
	}
	catch (Exception $e) {
		//$errorLog->log($e->getMessage()) for debugging
		echo 'ERROR, ' . $e->getMessage();
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
	//echo 'requestLogin for rest triggered.';
			
	$login = new ApiLogin($rest->data);
	Logger::debug('rest/Cerberus:273: this is ' . var_dump($this, true));
	$params = $this->dao->login($login, true);
	echo 'requestLogin in Cerberus.php: done';
    return $this->response('OK....', $params);
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
    return "Cerberus.php ERROR," . $this->getMessage();
  }
}
