<?php
require_once 'php/data/LoginSession.php';
require_once "php/dao/SchedDao.php";
require_once "php/dao/SessionDao.php";
require_once "php/delegates/JsonDelegate.php";
require_once "php/data/json/JAjaxMsg.php"; 
//
if (isset($_GET["action"])) {
  $_GP = &$_GET;
} else {
  $_GP = &$_POST;
}
$action = $_GP['action'];
if (isset($_GP["obj"])) {
  $_GP["obj"] = stripslashes($_GP["obj"]);
  $obj = $_GP["obj"];
}
if (isset($_GP["id"])) {
  $id = $_GP["id"];
}
logit("serverSched.php?" . implode_with_keys("&", $_GP));
try {
  LoginSession::verify_forServer();
  switch ($action) {
  
    // Searches Clients on supplied criteria
    // Calls clientsCallback(o), o=JClients
    case "search":  // returns JClients
      $login::requires($login->Role->Patient->demo);
      $clients = SchedDao::searchClients($_GP["uid"], $_GP["ln"], $_GP["fn"], $_GP["ad"], $_GP["ph"], $_GP["em"], $_GP["cu"]);
      $m = new JAjaxMsg("clients", $clients->out());  
      break;
      
    case "searchById":  // returns JClients
      $clients = array();
      $clients[] = SchedDao::getJClient($_GP["id"]);
      $j = new JClients($clients);
      $m = new JAjaxMsg("clients", $j->out());  
      break;
  
    // Get a specific Sched-Client joined row
    // Calls schedCallback(o), o=JSched
    case "getSched":
      $sched = Scheduling::get($_GP['id']);
      if ($sched) {
        if ($sched->isEvent())
          $m = new JAjaxMsg("event", $sched->toJson());
        else
          $m = new JAjaxMsg("sched", $sched->toJson());
      }
      /*
      $sched = SchedDao::getJSched($_GP["id"]);
      if ($sched->client) {
        $m = new JAjaxMsg("sched", $sched->out());
      } else {
        $m = new JAjaxMsg("event", $sched->out());
      }
      */
      break;
      
    case "debug":
      $sched = Scheduling::get($_GP['id']);
      echo '<pre>';
      print_r($sched);
      exit;
    case "debug2":
      $sched = SchedDao::getJSched($_GP["id"]);
      echo '<pre>';
      print_r($sched);
      exit;

    // Get a specific Client with client events
    // Calls clientCallback(o), o=JClient
    case "getClient":
      $client = SchedDao::getJClientWithEvents($_GP["id"]);
      $m = new JAjaxMsg("client", $client->out());
      break;
  
    // Get a specific Client without client events
    // Calls eventlessClientCallback(o), o=JClient
    case "getEventlessClient":
      $client = SchedDao::getJClient($_GP["id"]);
      $m = new JAjaxMsg("eventlessClient", $client->out());
      break;
      
    case "saveClientAddress":
      $a = jsondecode($obj);
      SchedDao::saveAddress($a, $id);
      $client = SchedDao::getJClient($id);
      $m = new JAjaxMsg("saveClient", $client->out());
      break;
          
    case "saveClientICard":
      $c = jsondecode($obj);
      SchedDao::saveClientICard($c);
      $client = SchedDao::getJClient($id);
      $m = new JAjaxMsg("saveClient", $client->out());
      break;
        
    case "saveClient":
      $c = jsondecode($obj);
      try {
        $client = SchedDao::saveClient($c);
        $m = new JAjaxMsg("saveClient", $client->out());
      } catch (ClientUidExistsException $e) {
        $m = new JAjaxMsg("addClientUidExists", $e->getMessage());
      } catch (Exception $e) {
        $m = new JAjaxMsg("error", $e->getMessage());
      }
      break;
          
    case "savePatient":  // same as above for PatientEditor.js
  //    $c = jsondecode($obj);
  //    try {
  //      $client = SchedDao::saveClient($c);
  //      $m = new JAjaxMsg("savePatient", $client->out());
  //    } catch (Exception $e) {
  //      $m = JAjaxMsg::constructError($e);
  //    }
      break;
  
    case "savePatientAddress":
  //    $j = jsondecode($obj);
  //    SchedDao::saveAddress($j->address, $j->id);
  //    $client = SchedDao::getJClient($j->id);
  //    $m = new JAjaxMsg("saveClient", $client->out());
      break;
          
    case "savePatientIcard":
  //    $j = jsondecode($obj);
  //    SchedDao::saveClientICard($j->icard);
  //    $client = SchedDao::getJClient($j->id);
  //    $m = new JAjaxMsg("saveClient", $client->out());
      break;
      
    // Check if Client with that UID in that group already exists
    // Calls addClientUidExistsCallback(o),
    // o=JClient if a record found, else null
    case "checkClientUid":  
      $client = SchedDao::getJClientByUid($_GP["uid"], $_GP["ugid"]);
      if ($client != null) {
        $m = new JAjaxMsg("addClientUidExists", $client->out());
      }
      break;  
    
    // Add Client record
    // Calls addClientCallback(o)
    // o=same JClient, with newly-generated ID
    case "addClient":
      $client = JClient::constructFromJson($obj);
      $clientId = null;
      try {
        $clientId = SchedDao::addClient($client);
      } catch (ClientUidExistsException $e) {
        $m = new JAjaxMsg("addClientUidExists", $e->getMessage());
      } catch (Exception $e) {
        $m = new JAjaxMsg("error", $e->getMessage());
      }
      if ($clientId != null) {
        $client->clientId = $clientId;
        $m = new JAjaxMsg("addClient", $client->out());
      }
      break;
      
    // Update Client record
    // Calls updateClientCallback()
    case "updateClient":
      $client = JClient::constructFromJson($obj);
      SchedDao::updateClient($client);
      $m = new JAjaxMsg("updateClient", null);
      break;
          
    // Add (null ID) or update (non-null iD) Sched record
    // Calls saveSchedCallback()
    case "saveSched":
      $appt = Scheduling::save(jsondecode($obj));
      $m = new JAjaxMsg("saveSched", jsonencode($appt));
      /*
      $sched = JSched::constructFromJson($obj);
      if ($sched->id == null) {
        $sched = SchedDao::addSched($sched);
      } else {
        SchedDao::updateSched($sched);
      }
      $m = new JAjaxMsg("saveSched", $sched->out());
      */
      break;
    
    case "saveSchedEvent":
      $appt = Scheduling::save(jsondecode($obj));
      $m = new JAjaxMsg("saveSchedEvent", jsonencode($appt));
      /*
      $sched = JSched::constructFromJson($obj);
      $sched->clientId = 0;
      if ($sched->id == null) {
        $sched = SchedDao::addSched($sched);
      } else {
        $sched = SchedDao::updateSched($sched);
      }
      $m = new JAjaxMsg("saveSchedEvent", $sched->out());
      */
      break;
      
    // Delete Sched record
    // Calls deleteSchedCallback()
    case "deleteSched":
      Scheduling::delete($id);
      //SchedDao::deleteSched($id);
      $m = new JAjaxMsg("deleteSched", null);
      break;
  
    // Delete Sched record + all repeats
    // Calls deleteSchedCallback()
    case "deleteSchedRepeats":
      Scheduling::delete($id, true);
      //SchedDao::deleteSched($id, true);
      $m = new JAjaxMsg("deleteSched", null);
      break;
      
    // Delete Sched record
    // Calls deleteClientCallback()
    case "deleteClient":
      SchedDao::deleteClient($id);
      $m = new JAjaxMsg("deleteClient", null);
      break;
      
    //case "test":
    //  $id = SessionDao::addSession(1, 1, 1663, null, "30-Oct-2009", null, 1, 1801);
    //  $m = new JAjaxMsg("null", null);
    //  break;
      
    // Add a session to an appt Sched
    case "addSchedSession":
      $s = jsondecode($obj);
      $id = SessionDao::addSession($s->ugid, $s->tid, $s->cid, $s->kid, $s->dos, $s->tpid);
      $m = new JAjaxMsg("addSession", $id);
      break;
    
   // Add a session and return JSession (deprecated, see below)
    case "addSession":
      $s = jsondecode($obj);
      $id = SessionDao::addSession($s->ugid, $s->tid, $s->cid, $s->kid, $s->dos, $s->tpid, $s->st, $s->sid, $s->ovfs); 
      $s = JsonDao::buildJSession($id, true);
      $m = new JAjaxMsg("addSession", $s->out());
      break;
              
   // Add a session and return JSession (same as above without ugid)
    case "newSession":
      $s = jsondecode($obj);
      logit_r($s, 'new session');
      $id = SessionDao::addSession(null, get($s, 'tid'), get($s, 'cid'), get($s, 'kid'), get($s, 'dos'), get($s, 'tpid'), get($s, 'st'), get($s, 'sid'), get($s, 'ovfs'));
      logit_r($id, 'adding session'); 
      $s = JsonDao::buildJSession($id, true);
      $m = new JAjaxMsg("newSession", $s->out());
      break;
              
      default:
      $m = new JAjaxMsg("unknown action:", $action);
  }
    
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
if ($m != null)
  echo $m->out();
