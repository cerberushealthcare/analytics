<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/Client.php';
require_once 'php/data/rec/sql/UserLogin.php';
//
/**
 * Messages
 * DAO for MsgThread, MsgPost, MsgInbox
 * @author Warren Hornsby
 */
class Messages {
  //
  /**
   * @return int total unread
   */
  public static function getMyUnreadCt() {
    global $myLogin;
    return MsgInbox::countUnread($myLogin->userId);    
  }
  /**
   * @return array(
   *   'recips'=>array(UserRecip,..),
   *   'sections'=>array(SectionMsg,..)) 
   */
  public static function getListsAsJson() {
    $lists = array(
      'sections' => SectionMsg::fetchAll(),
      'recips' => Messages::getMyRecipients());
    return jsonencode($lists);
  }
  /**
   * @return array(MsgThread(+MsgInbox),..)
   */
  public static function getMyInboxThreads() {
    global $myLogin;
    $inboxes = MsgInbox::fetchAllForInbox($myLogin->userId);
    return MsgThread::fetchAllByInboxes($inboxes);
  }
  /**
   * @return array(MsgThread(+MsgInbox),..)
   */
  public static function getMySentThreads() {
    global $myLogin;
    $inboxes = MsgInbox::fetchAllForSent($myLogin->userId);
    return MsgThread::fetchAllByInboxes($inboxes);
  }
  /**
   * @param int $clientId
   * @return array(mtid=>MsgThread,..)
   */
  public static function getThreadsForClient($clientId) {
    return MsgThread::fetchAllByClient($clientId);    
  }
  /**
   * @param int $mtid
   * @return MsgThread(+MsgInbox,MsgPosts)
   */
  public static function openThread($mtid) {
    global $myLogin;
    $thread = Messages::getThread($mtid);
    if ($thread) {
      $thread->MsgPosts = MsgPost::fetchByThread($thread);
      $thread->_unreadCt = MsgInbox::countUnread($myLogin->userId);
      MsgInbox::saveAsRead($thread);
    }
    return $thread;
  }
  /**
   * @param int $mtid
   * @param [int,..] $sendTos 
   * @param string $body
   * @param string $data
   */
  public static function postReply($mtid, $sendTos, $body, $data) {
    logit_r($sendTos, 'post sendtos');
    $sendTo = Messages::buildSendToNames($sendTos);
    $thread = Messages::getThread($mtid);
    if ($thread) {
      $post = Messages::addPost(MsgPost::ACTION_REPLY, $thread, $sendTo, $body, $data);
      $thread->MsgInbox->saveAsSent($post);
      MsgInbox::saveAsUnreadFor($sendTos, $post);
    }
  }
  /**
   * @param int $mtid
   * @param string $body
   * @param string $data
   */
  public static function postComplete($mtid, $body, $data) {
    $thread = Messages::getThread($mtid);
    if ($thread) {
      $post = Messages::addPost(MsgPost::ACTION_CLOSE, $thread, null, $body, $data);
      $thread->MsgInbox->saveAsClose($post);
    }
  }
  /**
   * @param int $cid
   * @param int $priority MsgThread::PRIORITY
   * @param string $subject
   * @param [int,..] $sendTos
   * @param string $body
   * @param string $data
   */
  public static function newThread($cid, $priority, $subject, $sendTos, $body, $data) {
    global $myLogin;
    $sendTo = Messages::buildSendToNames($sendTos);
    $thread = MsgThread::fromUi($myLogin->userId, $myLogin->name, $myLogin->userGroupId, $cid, $priority, $subject);
    $thread->save();
    $post = Messages::addPost(MsgPost::ACTION_CREATE, $thread, $sendTo, $body, $data);
    MsgInbox::createForThreadCreator($post);
    MsgInbox::saveAsUnreadFor($sendTos, $post);
  }
  //
  /*
   * Retrieves MsgThread+MsgInbox and authenticates access 
   */
  private static function getThread($mtid) {
    global $myLogin;
    $thread = MsgThread::fetch($mtid);
    if ($thread) {
      $inbox = MsgInbox::fetchByRecip($myLogin->userId, $thread);
      if ($inbox == null) 
        if ($thread->creatorId != $myLogin->userId)
          if ($access > MsgInbox::ACCESS_READ || $thread->Client == null)
            LoginDao::throwSecurityError("accesss $access mtid", $thread->threadId);
      $thread->MsgInbox = $inbox;
    }
    return $thread;
  }
  /*
   * Get all recipients I can send messages to
   */
  private static function getMyRecipients($mapped = false) {
    global $myLogin;
    return UserRecip::fetchAllByUgid($myLogin->userGroupId, $mapped);
  }
  /*
   * Given [id,..] return 'name;..'
   */
  private static function buildSendToNames($sendTos) {
    if ($sendTos == null) 
      return null;
    global $myLogin;
    $recipients = UserRecip::fetchAllByUgid($myLogin->userGroupId, true);
    logit_r($recipients, 'recips');
    $s = array();
    foreach ($sendTos as &$id) 
      $s[$id] = Messages::getRecipientName($recipients, $id);
    return implode($s, ';');
  }
  /*
   * Return recipient name if valid
   */
  private static function getRecipientName($recipients, $id) {
    $r = geta($recipients, $id);
    if ($r == null) 
      throw new InvalidSendToException("Invalid sendto: $id");
    return $r->name;
  }
  /*
   * Add post from UI
   */
  private static function addPost($action, $thread, $sendTos, $body, $data) {
    global $myLogin;
    $post = MsgPost::fromUi($myLogin->userId, $myLogin->name, $action, $thread->threadId, $sendTos, $body, $data);
    $post->save();
    return $post;
  }  
}
//
/**
 * Message Thread
 */
class MsgThread extends SqlRec {
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
  public /*Client*/ $Client;
  public /*MsgInbox*/ $MsgInbox;
  public /*[MsgPost]*/ $MsgPosts;
  public $_closed;
  public $_unreadCt;
  //
  const TYPE_GENERAL = 0;
  //
  const STATUS_OPEN = 1;
  const STATUS_CLOSED = 2;
  //
  const PRIORITY_NORMAL = 0;
  const PRIORITY_STAT = 9;
  public static $PRIORITIES = array(
    MsgThread::PRIORITY_NORMAL => 'Normal',
    MsgThread::PRIORITY_STAT => 'STAT');
  //
  const POST_ACTION_CLOSE = MsgPost::ACTION_CLOSE;
  //  
  public function getSqlTable() {
    return 'msg_threads';
  }
  public function getJsonFilters() {
    return array(
      'dateCreated' => JsonFilter::informalTime());
  }
  public function toJsonObject() {
    $o = parent::toJsonObject();
    $o->_closed = ($this->isClosed());
    return $o;
  }
  public function isClosed() {
    return $this->status == MsgThread::STATUS_CLOSED;
  }
  public function getLastPost() {
    $post = null;
    if ($this->MsgPosts) 
      $post = $this->MsgPosts[0]; 
    return $post;
  }
  //
  public static function getStaticJson() {
    return Rec::getStaticJson('MsgThread');
  }
  /**
   * @param int $mtid
   * @return MsgThread->Client
   */
  public static function fetch($mtid) {
    $rec = new MsgThread($mtid);
    $rec->Client = new Client();
    return SqlRec::fetchOneBy($rec);
  }
  /**
   * @param int $cid
   * @return array(mtid=>MsgThread,..)
   */
  public static function fetchAllByClient($cid) {
    $rec = new MsgThread();
    $rec->clientId = $cid;
    $recs = SqlRec::fetchAllBy($rec, new RecSort('-dateCreated'), null, 'threadId');
    return $recs;
  }
  /**
   * @param [Inbox,..] $inboxes
   * @return array(MsgThread(+MsgInbox),..)
   */
  public static function fetchAllByInboxes($inboxes) {
    $threads = array();
    foreach ($inboxes as &$inbox) {
      $thread = MsgThread::fetch($inbox->threadId);
      $thread->MsgInbox = $inbox;
      if (! $thread->isClosed() || $thread->MsgInbox->isRead == MsgInbox::IS_UNREAD) 
        $threads[] = $thread;
    }
    Rec::sort($threads, new RecSort('MsgInbox->isRead', '-priority', '-MsgInbox->postId'));
    return $threads;
  }
  /**
   * @return MsgThread 
   */
  public static function fromUi($creatorId, $creator, $ugid, $cid, $priority, $subject) {
    $thread = new MsgThread();
    $thread->userGroupId = $ugid;
    $thread->clientId = $cid;
    $thread->creatorId = $creatorId;
    $thread->creator = $creator;
    $thread->type = MsgThread::TYPE_GENERAL;
    $thread->status = MsgThread::STATUS_OPEN;
    $thread->priority = $priority;
    $thread->subject = $subject;
    return $thread;
  }
}
/**
 * Message Post 
 */
class MsgPost extends SqlRec implements NoUserGroup {
  //
  public $postId;
  public $threadId;
  public $action;
  public $dateCreated;
  public $authorId;
  public $author;
  public $body;
  public $sendTo;
  public $data;
  //
  const ACTION_CREATE = 0;
  const ACTION_REPLY = 1;
  const ACTION_CLOSE = 9;
  //
  public function getSqlTable() {
    return 'msg_posts';
  }
  public function getJsonFilters() {
    return array(
      'dateCreated' => JsonFilter::informalDate());
  }
  //
  /**
   * @param int $mpid
   * @return MsgPost
   */
  public static function fetch($mpid) {
    return SqlRec::fetch($mpid, 'MsgPost');
  }
  /**
   * @param MsgThread $thread
   * @return array(MsgPost,..)
   */
  public static function fetchByThread($thread) {
    $rec = new MsgPost();
    $rec->threadId = $thread->threadId;
    return SqlRec::fetchAllBy($rec, new RecSort('-postId'));
  }
  /**
   * @return MsgPost
   */
  public static function fromUi($authorId, $author, $action, $mtid, $sendTos, $body, $data) {
    $post = new MsgPost();
    $post->threadId = $mtid;
    $post->action = $action;
    $post->authorId = $authorId;
    $post->author = $author;
    $post->body = $body;
    $post->sendTo = $sendTos; 
    $post->data = $data;
    return $post;
  }
}
/**
 * Message Inbox
 */
class MsgInbox extends SqlRec implements NoUserGroup {
  //
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  public /*MsgPost*/ $MsgPost;
  //
  const IS_UNREAD = '0';
  const IS_READ = '1';
  const IS_SENT = '2';
  const IS_CLOSED = '9';
  //
  const ACCESS_READ = 0;
  const ACCESS_POST = 1;
  //
  public function getSqlTable() {
    return 'msg_inbox';
  }
  /**
   * @param MsgPost $post just added
   */
  public function saveAsSent($post) {
    if ($this->isRead == MsgInbox::IS_READ) {
      $this->postId = $post->postId;
      $this->isRead = MsgInbox::IS_SENT;
      $this->save();
    }
  }
  /**
   * @param MsgPost $post just added
   */
  public function saveAsClose($post) {
    $this->isRead = MsgInbox::IS_CLOSED;
    $this->save();
  }
  //
  public static function getStaticJson() {
    return Rec::getStaticJson('MsgInbox');
  }
  /**
   * @param int $userId
   * @return int total unread
   */
  public static function countUnread($userId) {
    $rec = new MsgInbox();
    $rec->recipient = $userId;
    $rec->isRead = MsgInbox::IS_UNREAD;
    return count(SqlRec::fetchAllBy($rec));    
  }
  /**
   * @param int $userId
   * @return array(MsgInbox,..)
   */
  public static function fetchAllForInbox($userId) {
    return MsgInbox::fetchAllByIsRead($userId, CriteriaValue::lessThan(MsgInbox::IS_SENT));
  }
  /**
   * @param int $userId
   * @return array(MsgInbox,..)
   */
  public static function fetchAllForSent($userId) {
    return MsgInbox::fetchAllByIsRead($userId, MsgInbox::IS_SENT);
  }
  /**
   * @param int $userId
   * @param MsgThread $thread
   * @return MsgInbox
   */
  public static function fetchByRecip($userId, $thread) {
    $inbox = new MsgInbox();
    $inbox->recipient = $userId;
    $inbox->threadId = $thread->threadId;
    return SqlRec::fetchOneBy($inbox);
  }
  /**
   * Create (or update) inboxes for recipients 
   * @param [$id,..] $sendTos
   * @param MsgPost $post 
   * @return MsgInbox for self, if sent to self
   */
  public static function saveAsUnreadFor($sendTos, $post) {
    global $myLogin;
    $mtid = $post->threadId;
    $mpid = $post->postId;
    foreach ($sendTos as &$id) {
      $inbox = MsgInbox::fetchOrCreate($mtid, $id);
      $inbox->postId = $mpid;
      $inbox->isRead = MsgInbox::IS_UNREAD;
      $inbox->save();
    }
  }
  /**
   * Mark thread's inbox as read
   * @param MsgThread $thread
   */
  public static function saveAsRead(&$thread) {
    $inbox = &$thread->MsgInbox;
    if ($inbox && $inbox->isRead == MsgInbox::IS_UNREAD) { 
      $post = $thread->getLastPost();
      if ($post) 
        $inbox->postId = $post->postId;
      $inbox->isRead = MsgInbox::IS_READ;
      $inbox->save();
    }
  }
  /**
   * Create inbox for new thread creator
   * @param MsgPost $post creation post
   */
  public static function createForThreadCreator($post) {
    $inbox = MsgInbox::_create($post->threadId, $post->authorId);
    $inbox->postId = $post->postId;
    $inbox->isRead = MsgInbox::IS_SENT;
    $inbox->save();
  }
  //
  private static function fetchAllByIsRead($userId, $isRead) {
    $rec = new MsgInbox();
    $rec->recipient = $userId;
    $rec->isRead = $isRead;
    $rec->MsgPost = new MsgPost();
    return SqlRec::fetchAllBy($rec);
  }
  private static function fetchOrCreate($mtid, $id) {
    $inbox = SqlRec::fetchOneBy(MsgInbox::_create($mtid, $id));
    return ($inbox) ? $inbox : MsgInbox::_create($mtid, $id);
  }
  private static function _create($mtid, $id) {
    return new MsgInbox(null, $id, $mtid);
  }
}
/**
 * User Recipient
 */
class UserRecip extends SqlRec implements ReadOnly {
  //
  public $userId;
  public $uid;
  public $name;
  public $active;
  public $userGroupId;
  public $userType;
  //
  public function getSqlTable() {
    return 'users';
  }
  //
  /**
   * @param int$ugid
   * @param bool $mapped
   */
  public static function fetchAllByUgid($ugid, $mapped) {
    $c = new UserRecip();
    $c->userGroupId = $ugid;
    $c->active = 1;
    $fid = ($mapped) ? 'userId' : null;
    return SqlRec::fetchAllBy($c, new RecSort('name'), null, $fid);
  }
}
/** 
 * Section for Messaging
 */
class SectionMsg extends SqlRec implements ReadOnly {
  //
  public $sectionId;
  public $templateId;
  public $name;
  public /*[ParMsg]*/ $ParMsgs; 
  //
  public function getSqlTable() {
    return 'template_sections';
  }
  //
  /**
   * @return array(
   *   tsid=>array(SectionMsg,..))
   */
  public static function fetchAll() {
    $c = new SectionMsg();
    $c->templateId = 25;
    $recs = SqlRec::fetchAllBy($c, new RecSort('sectionId'), null, 'sectionId');
    $sections = array();
    foreach ($recs as &$rec) { 
      $rec->ParMsgs = ParMsg::fetchAllFor($rec->sectionId);
      if (count($rec->ParMsgs) == 0) 
        unset($recs[$rec->sectionId]);
    }
    return $recs;
  }
}
/** 
 * Par for Messaging
 */
class ParMsg extends SqlRec implements ReadOnly {
  //
  public $parId;
  public $sectionId;
  public $desc;
  public $current;
  public $major;
  //
  public function getSqlTable() {
    return 'template_pars';
  }
  // 
  public static function fetchAllFor($sectionId) {
    $c = new ParMsg();
    $c->sectionId = $sectionId;
    $c->current = 1;
    $c->major = 1;
    return SqlRec::fetchAllBy($c, new RecSort('desc'), null, 'parId');
  } 
}
