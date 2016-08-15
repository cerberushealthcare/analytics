<?php
require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
require_once 'php/c/sessions/Sessions.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/_SchedRec.php';
require_once 'php/data/rec/sql/OrderEntry.php';
//
class Welcome extends Rec {
  //
  public $docUnreviewed;
  public $docUnsigned;
  public $msgUnread;
  public $apptToday;
  public $orderUnsched;
  //
  static function fetch() {
    global $login;
    $me = new static();
    if ($login->Role->Artifact->markReview)
      $me->docUnreviewed = Messaging_DocStubReview::getUnreviewedCt();
    if ($login->Role->Artifact->noteSign)
      $me->docUnsigned= Sessions::countUnsigned();
    $me->msgUnread = Messaging::getMyUnreadCt();
    $me->apptToday = SchedRec::countOpen($login->userGroupId);
    $me->orderUnsched = count(OrderEntry::getUnschedItems());
    return $me;
  }
}
