<?php
require_once 'server.php';
require_once 'php/data/rec/sql/PortalMessaging.php'; 
//
try {
  switch ($action) {
    //
    case 'getMyUnreadCt':
      $ct = PortalMessaging::getMyUnreadCt();
      jam($ct);
    case 'getMyInboxThreads':
      $threads = PortalMessaging::getMyInboxThreads();
      jam($threads);
    case 'openThread':
      $thread = PortalMessaging::openThread($_GET['id']);
      jam($thread);
    case 'postReply':
      PortalMessaging::postReply($obj->mtid, $obj->sendTos, $obj->body, $obj->file);
      jam();
    case 'newThread':
      $thread = PortalMessaging::newThread($obj->subject, $obj->sendTos, $obj->body, $obj->file);
      jam($thread); 
  }
} catch (Exception $e) {
  p_r($e);
  jamerr($e);
}
