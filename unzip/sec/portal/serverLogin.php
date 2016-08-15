<?php
require_once 'server.php';
require_once 'php/data/rec/sql/PortalUsers_Session.php'; 
//
try {
  switch ($action) {
    //
    case 'login':
      $me = PortalUsers_Session::login($obj->id, $obj->pw);
      //Proc_PortalAccess::record($me->userGroupId, $me->clientId);
      jam($me);
    case 'respond':
    	$me = PortalUsers_Session::respond($obj);
    	jam($me);
    case 'setPassword':
    	$me = PortalUsers_Session::setPassword($obj);
    	jam($me);
    case 'acceptTerms':
    	$me = PortalUsers_Session::acceptTerms();
    	jam($me);
  }
} catch (Exception $e) {
  jamerr($e);
}
?>
