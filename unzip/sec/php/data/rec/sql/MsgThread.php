<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/Client.php';
/**
 * Message Thread 
 * Includes: MsgPost, MsgInbox
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
  public function toJsonObject() {
    $o = parent::toJsonObject();
    Rec::addDateTimeProps($o, array('dateCreated'));
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
  /**
   * @param int $mtid
   * @return MsgThread // +Client
   */
  public static function fetch($mtid) {
    $rec = new MsgThread($mtid);
    $rec->Client = new Client();
    return SqlRec::fetchOneBy($rec);
  }
  /**
   * @return int total unread
   */
  public static function fetchMyUnreadCt() {
    return MsgInbox::fetchMyCt();    
  }
  /**
   * Fetch thread for reading and update MsgInbox
   * @param int $mtid
   * @return MsgThread  // +Client,MsgInbox,MsgPosts
   * @throws SecurityException
   */
  public static function fetchForReading($mtid) {
    $thread = MsgThread::fetch($mtid);
    if ($thread) {
      $thread->MsgInbox = MsgInbox::fetchMineFor(MsgInbox::ACCESS_READ, $thread);
      $thread->MsgPosts = MsgPost::fetchByThread($mtid);
      $thread->_unreadCt = MsgInbox::fetchMyCt();
    }
    return $thread;
  }
  /**
   * @param int $cid
   * @return array(MsgThread,..)
   */
  public static function fetchAllByClient($cid) {
    $rec = new MsgThread();
    $rec->clientId = $cid;
    $recs = SqlRec::fetchAllBy($rec, new RecSort(array('dateCreated' => RecSort::DESC)), null, true);
    logit_r($recs,'fetchallbyclient');
    return $recs;
  }
  /**
   * @return array(MsgThread,..)
   */
  public static function fetchAllFromInbox() {
    return MsgThread::_fetchAllFromInbox(CriteriaValue::lessThan(MsgInbox::IS_SENT));
  }
  /**
   * @return array(MsgThread,..)
   */
  public static function fetchAllFromSent() {
    return MsgThread::_fetchAllFromInbox(MsgInbox::IS_SENT);
  }
  /**
   * Create new thread
   * @param stdClass $json from serialized JSON
   * @return MsgThread 
   */
  public static function newThread($json) {
    global $myLogin;
    $thread = new MsgThread();
    $thread->userGroupId = $myLogin->userGroupId;
    $thread->clientId = $json->cid;
    $thread->creatorId = $myLogin->userId;
    $thread->creator = $myLogin->name;
    $thread->type = MsgThread::TYPE_GENERAL;
    $thread->status = MsgThread::STATUS_OPEN;
    $thread->priority = $json->priority;
    $thread->subject = $json->subject;
    $thread->save();
    return MsgThread::_addPost(MsgPost::ACTION_CREATE, $thread->threadId, $json->to, $json->html, $json->data);
  }
  /**
   * Add post as reply 
   * @param stdClass $json from serialized JSON
   */
  public static function addPostReply($json) {
    return MsgThread::_addPost(MsgPost::ACTION_REPLY, $json->id, $json->to, $json->html, $json->data);
  }
  /**
   * Add post and complete thread 
   * @param stdClass $json from serialized JSON
   */
  public static function addPostComplete($json) {
    $thread = MsgThread::_addPost(MsgPost::ACTION_CLOSE, $json->id, null, $json->html, $json->data);
    $thread->status = MsgThread::STATUS_CLOSED;
    $thread->dateClosed = nowNoQuotes();
    $thread->save();
  }
  //
  private static function _addPost($action, $mtid, $sendTos, $body, $data) {
    $thread = MsgThread::fetch($mtid);
    if ($thread) {
      $thread->MsgInbox = MsgInbox::fetchMineFor(MsgInbox::ACCESS_POST, $thread);
      $post = MsgPost::fromUi($action, $mtid, $sendTos, $body, $data);
      $post->save();
      switch ($action) {
        case MsgPost::ACTION_CREATE:
          MsgInbox::saveForThreadCreator($post);
          MsgInbox::saveAsUnreadFor($sendTos, $post);
          break;
        case MsgPost::ACTION_REPLY:
          $thread->MsgInbox->saveAsSent($post);
          MsgInbox::saveAsUnreadFor($sendTos, $post);
          break;
        case MsgPost::ACTION_CLOSE:
          $thread->MsgInbox->saveAsClose($post);
          break;
      }
    }
    return $thread;
  }
  private static function _fetchAllFromInbox($isRead) {
    global $myLogin;
    $inboxes = MsgInbox::fetchMineByIsRead($isRead);
    $threads = array();
    foreach ($inboxes as &$inbox) {
      $thread = MsgThread::fetch($inbox->threadId);
      $thread->MsgInbox = $inbox;
      if (! $thread->isClosed() || $thread->MsgInbox->isRead == MsgInbox::IS_UNREAD) 
        $threads[] = $thread;
    }
//    print_r($inboxes);
//    exit;
    Rec::sort($threads, new RecSort(array(
      'MsgInbox->isRead' => RecSort::ASC,
      'priority' => RecSort::DESC,
      'MsgInbox->postId' => RecSort::DESC)));
    return $threads;
  }
}
/**
 * Message Post 
 */
class MsgPost extends SqlRec {
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
  public function toJsonObject() {
    $o = parent::toJsonObject();
    Rec::addDateTimeProps($o, array('dateCreated'));
    return $o;
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
   * @param string $mtid
   * @return array(MsgPost,..)
   */
  public static function fetchByThread($mtid) {
    $rec = new MsgPost();
    $rec->threadId = $mtid;
    return SqlRec::fetchAllBy($rec, new RecSort(array('postId' => RecSort::DESC)));
  }
  /**
   * @return MsgPost
   */
  public static function fromUi($action, $mtid, $sendTos, $body, $data) {
    global $myLogin;
    $post = new MsgPost();
    $post->threadId = $mtid;
    $post->action = $action;
    $post->authorId = $myLogin->userId;
    $post->author = $myLogin->name;
    $post->body = $body;
    $post->sendTo = MsgDao::buildSendToNames($sendTos);
    $post->data = $data;
    return $post;
  }
}
/**
 * Message Inbox
 */
class MsgInbox extends SqlRec {
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
  /**
   * @return int total unread
   */
  public static function fetchMyCt() {
    global $myLogin;
    $rec = new MsgInbox();
    $rec->recipient = $myLogin->userId;
    $rec->isRead = MsgInbox::IS_UNREAD;
    return count(SqlRec::fetchAllBy($rec));    
  }
  /**
   * @param int $isRead
   * @return array(MsgInbox,..) 
   */
  public static function fetchMineByIsRead($isRead) {
    global $myLogin;
    $rec = new MsgInbox();
    $rec->recipient = $myLogin->userId;
    $rec->isRead = $isRead;
    $rec->MsgPost = new MsgPost();
    return SqlRec::fetchAllBy($rec);
  }
  /**
   * Fetches (and updates) inbox record for thread read
   * @param int $access MsgInbox::ACCESS_
   * @param MsgThread $thread
   * @return MsgInbox
   */
  public static function fetchMineFor($access, $thread) {
    global $myLogin;
    $inbox = new MsgInbox();
    $inbox->recipient = $myLogin->userId;
    $inbox->threadId = $thread->threadId;
    $inbox = SqlRec::fetchOneBy($inbox);
    if ($inbox == null) 
      if ($thread->creatorId != $myLogin->userId)
        if ($access > MsgInbox::ACCESS_READ || $thread->Client == null)
          LoginDao::throwSecurityError("accesss $access mtid", $thread->threadId);
    if ($access == MsgInbox::ACCESS_READ)
      $inbox = MsgInbox::saveAsRead($inbox, $thread);
    return $inbox;
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
   * Create inbox for new thread creator
   * @param MsgPost $post creation post
   */
  public static function saveForThreadCreator($post) {
    global $myLogin;
    $inbox = MsgInbox::_create($post->threadId, $myLogin->userId);
    $inbox->postId = $post->postId;
    $inbox->isRead = MsgInbox::IS_SENT;
    $inbox->save();
  }
  //
  private static function saveAsRead($inbox, $thread) {
    if ($inbox && $inbox->isRead == MsgInbox::IS_UNREAD) { 
      $post = $thread->getLastPost();
      if ($post) 
        $inbox->postId = $post->postId;
      $inbox->isRead = MsgInbox::IS_READ;
      $inbox->save();
    }
    return $inbox;
  }
  private static function fetchOrCreate($mtid, $id) {
    $inbox = SqlRec::fetchOneBy(MsgInbox::_create($mtid, $id));
    return ($inbox) ? $inbox : MsgInbox::_create($mtid, $id);
  }
  private static function _create($mtid, $id) {
    return new MsgInbox(null, $id, $mtid);
  }
}
