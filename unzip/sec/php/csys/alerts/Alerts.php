<?php
require_once 'php/data/Html.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_MessagingRecs.php';
//
class Alerts {
  //
  static function sendGlassBroken($cid) {
    global $login;
    $admins = UserGroups::getAllAdmins();
    $client = ClientStub::fetch($cid);
    $thread = Thread_GlassBroke::from($login, $admins, $client);
    return static::saveThread($thread);
  }
  //
  protected static function saveThread($thread) {
    Dao::begin();
    try {
      $thread->save();
      Dao::commit();
      return $thread;
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
}
//
class Thread_Alert extends MsgThreadRec {
  //
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;
  public $status;
  public $priority;
  public $subject;
  public /*Post_Alert*/ $Post;
  //
  public function save() {
    parent::save();
    $this->Post->save($this->threadId);
  }
  //
  static function create($ugid, $creatorId, $subject, $clientId, $Post) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $clientId;
    $me->creatorId = $creatorId;
    $me->creator = 'System Alert';
    $me->dateCreated = nowNoQuotes();
    $me->type = static::TYPE_ALERT;
    $me->status = static::STATUS_OPEN;
    $me->priority = static::PRIORITY_NORMAL;
    $me->subject = $subject;
    $me->Post = $Post;
    return $me; 
  }
}
class Post_Alert extends MsgPostRec {
  //
  public $postId;
  public $threadId;
  public $action;
  public $dateCreated;
  public $authorType;
  public $authorId;
  public $author;
  public $body;
  public $sendTo;
  public /*Inbox_Alert[]*/ $Inboxes;
  //
  public function save($threadId) {
    $this->threadId = $threadId;
    parent::save();
    Inbox_Alert::saveAll($this->Inboxes, $this->threadId, $this->postId);
  }
  //
  static function from(/*User_Any[]*/$users, /*Html*/$html) {
    $body = $html->out();
    $names = array();
    foreach ($users as $user)
      $names[] = $user->name;
    $sendTo = implode(';', $names);
    $Inboxes = Inbox_Alert::createAll($users);
    return static::create($body, $sendTo, $Inboxes); 
  }
  static function create($body, $sendTo, $Inboxes) {
    $me = new static();
    $me->action = static::ACTION_CREATE;
    $me->dateCreated = nowNoQuotes();
    $me->authorType = static::AUTHOR_TYPE_ALERT;
    $me->authorId = 0;
    $me->author = 'System Alert';
    $me->body = $body;
    $me->sendTo = $sendTo;
    $me->Inboxes = $Inboxes;
    return $me;
  }
}
class Inbox_Alert extends MsgInboxRec {
  //
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  //
  static function create($recipient) {
    $me = new static();
    $me->recipient = $recipient;
    $me->isRead = static::IS_UNREAD;
    return $me;
  }
  static function createAll(/*User_Any*/$users) {
    $us = array();
    foreach ($users as $user) 
      $us[] = static::create($user->userId);
    return $us;
  }
  static function saveAll($us, $threadId, $postId) {
    foreach ($us as $me) {
      $me->threadId = $threadId;
      $me->postId = $postId;
      $me->save();
    }
  }
}
//
class Thread_GlassBroke extends Thread_Alert {
  //
  static function from($login, $admins, $client) {
    $Post = Post_GlassBroke::from($login, $admins, $client);
    return parent::create($login->userGroupId, $login->userId, 'Glass Broken Alert', $client->clientId, $Post);
  }
}
class Post_GlassBroke extends Post_Alert {
  //
  static function from($login, $admins, $client) {
    $name = $client->getFullName();
    $dob = formatDate($client->birth);
    $user = $login->User;
    $now = nowTimestamp();
    $html = Html::create()
      ->p_()
        ->br("A restricted chart was opened for the following patient:")
        ->br()
        ->br("Name: $name")
        ->br("DOB: $dob")
        ->br()
        ->br("This chart was opened by:")
        ->br()
        ->br("User: $user->name")
        ->br("Login ID: $login->uid")
        ->br("Time: $now")
        ->_()
      ->_();
    return parent::from($admins, $html);
  }
}
