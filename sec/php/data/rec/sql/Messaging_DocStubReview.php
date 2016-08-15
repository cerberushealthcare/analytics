<?php
require_once 'php/data/rec/sql/_MessagingRecs.php';
require_once 'php/data/rec/sql/Documentation.php';
//
/**
 * Messaging for Doc Stub Review
 * Tracks documentation review (signing)
 * @author Warren Hornsby
 */
class Messaging_DocStubReview {
  //
  /**
   * @return int
   */
  static function getUnreviewedCt() {
    global $login;
    $ct = MsgInbox_Stub::countUnreviewed($login->userId);
    return $ct;
  }
  /**
   * @param int cid (optional)
   * @return array(MsgThread_Stub,..)
   */
  static function getUnreviewedThreads($cid = null) {
    global $login;
    //$inboxes = MsgInbox_Stub::fetchAllUnreviewed($login->userId);
    //$recs = MsgThread_Stub::fetchAllByInboxes($login->userGroupId, $inboxes, $cid);
    $recs = MsgThread_Stub::fetchAllUnreviewed($login->userGroupId, $login->userId, $cid);
    return $recs;
  }
  /**
   * @param DocStub $stub
   * @return MsgThread_Stub
   */
  static function getThreadForStub($stub) {
    global $login;
    $rec = MsgThread_Stub::fetchByStub($stub, $login->userGroupId, $login->userId);
    return $rec;
  }
  static function getThread_byProc($procId) {
    $stub = new DocStub(DocStub::TYPE_RESULT, $procId);
    return static::getThreadForStub($stub);
  }
  static function getThread_byScan($scanIndexId) {
    $stub = new DocStub(DocStub::TYPE_SCAN, $scanIndexId);
    return static::getThreadForStub($stub); 
  }
  /**
   * @param int $to USER_ID
   * @param DocStub $stub
   * @param int $priority MsgThread::PRIORITY 
   * @return MsgThread_Stub
   */
  static function createThread($to, $stub, $priority = MsgThread::PRIORITY_NORMAL) {
    global $login;
    $stub = DocStub::fetch_byStub($stub);
    if ($stub) {
      $thread = MsgThread_Stub::fetchByStub($stub, $login->userGroupId);
      if ($thread)
        throw new ThreadExistsException("Thread $thread->threadId already exists for stub.");
      //static::deleteThread($stub);
      $client = ClientStub::fetch($stub->cid);
      Dao::begin();
      try {
        $thread = MsgThread_Stub::create($login->userGroupId, $login->userId, $login->User->name, $stub, $client, $priority);
        static::addReviewPost($thread, $to);
        Dao::commit();
        return MsgThread_Stub::fetch($login->userGroupId, $thread->threadId);
      } catch (Exception $e) {
        Dao::rollback();
        throw $e;
      }
    }
  }
  static function addReviewPost($thread, $to) {
    global $login;
    $sendTo = UserGroups::lookupUser($to);
    $post = MsgPost_Stub::create_asRequest($thread, $login->userId, $login->User->name, $sendTo);
    MsgInbox_Stub::create($thread, $post, $to);
  }
  static function createThread_asScan($scanIndexId, $to) {
    try {
      $stub = new DocStub(DocStub::TYPE_SCAN, $scanIndexId);
      static::createThread($to, $stub);
    } catch (ThreadExistsException $e) {  // thread already exists, we're good
    }
  }
  static function createThread_asProc($procId, $to) {
    try {
      $stub = new DocStub(DocStub::TYPE_RESULT, $procId);
      logit_r($stub, 'stub!');
      static::createThread($to, $stub);
    } catch (ThreadExistsException $e) {  // thread already exists, we're good
    }
  }
  static function createThreads_fromProcs($procs, $to) {
    foreach ($procs as $proc)
      static::createThread_asProc($proc->procId, $to);
  }
  static function deleteThread($thread) {
    if ($thread) {
      $inbox = get($thread, 'Inbox');
      $posts = get($thread, 'Posts');
      if ($inbox)
        SqlRec::delete($inbox);
      if ($posts)
        SqlRec::deleteAll($posts);
      SqlRec::delete($thread);
    }
  }
  /** Forward for review
   */
  static function forward($stubType, $stubId, $userId) {
    global $login;
    $stub = DocStub::fetch($stubType, $stubId);
    $thread =  MsgThread_Stub::fetchByStub($stub, $login->userGroupId);
    if ($thread == null) 
      $thread = static::createThread($userId, $stub);
    else
       static::addReviewPost($thread, $userId);
    return MsgThread_Stub::fetch($login->userGroupId, $thread->threadId, $login->userId, true);
  }
  /**
   * @param int $threadId
   * @return MsgThread_Stub
   */
  static function postReviewed($threadId) {
    global $login;
    $userId = $login->userId;
    $thread = MsgThread_Stub::fetchForSigning($login->userGroupId, $threadId, $userId);
    if ($thread == null) 
      throw new Exception("No unread thread $threadId user $userId");
    if ($thread->Stub == null)
      throw new Exception("No stub for thread $threadId user $userId");
    Dao::begin();
    try {
      //DocStub::postSignature($thread->Stub, $userId); future use to sign sessions
      MsgPost_Stub::create_asReviewed($thread, $userId, $login->User->name);
      $thread->Inbox->save_asReviewed();
      $thread->save_asClosed();
      Dao::commit();
      return MsgThread_Stub::fetch($login->userGroupId, $threadId, $login->userId, true);
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
}
class MsgThread_Stub extends MsgThreadRec implements NoAudit {
  //
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;  // TYPE_STUB_REVIEW 
  public $status;
  public $priority;
  public $subject;
  public $stubType;
  public $stubId;
  public /*DocStub*/ $Stub;
  public /*MsgPost_Stub[]*/ $Posts;
  public /*MsgInbox_Stub*/ $Inbox;
  public /*ClientStub*/ $Client;
  //
  public function attachStub() {
    $this->Stub = DocStub::fetch($this->stubType, $this->stubId);
  }
  public function save_asClosed() {
    $this->status = static::STATUS_CLOSED;
    $this->dateClosed = nowNoQuotes();
    $this->save();
  }
  //
  static function asJoin_requiresUnreviewed($userId, $stubType) {  // when searching for unreviewed stubs
    $c = new static();
    $c->stubType = $stubType;
    $c->Inbox = CriteriaJoin::requires(MsgInbox_Stub::asCriteria_unreviewed($userId));
    $c->Client = new ClientStub();
    return CriteriaJoin::requires($c, 'stubId');
  }
  static function asJoin_optionalUnreviewed($userId, $stubType) {  // to indicate which stubs are unreviewed (e.g. facesheet)
    $c = new static();
    $c->stubType = $stubType;
    $c->Inbox = CriteriaJoin::optional(MsgInbox_Stub::asCriteria_unreviewed($userId));
    return CriteriaJoin::optional($c, 'stubId');
  }
  static function asJoin($stubType) {
    $c = new static();
    $c->stubType = $stubType;
    $c->Inbox = MsgInbox_Stub::asUnreviewedJoin(null);
    return CriteriaJoin::optional($c, 'stubId');
  }
  static function asScanJoin() {
    return static::asJoin(DocStub::TYPE_SCAN);
  }
  static function create($ugid, $creatorId, $creator, $stub, $client, $priority) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->creatorId = $creatorId;
    $me->creator = $creator;
    $me->type = static::TYPE_STUB_REVIEW;
    $me->status = static::STATUS_OPEN;
    $me->priority = $priority;
    $me->subject = $stub->lookupType();
    if ($client) {
      $me->clientId = $client->clientId;
      $me->subject .= ': ' . $client->getFullName();
    } 
    $me->stubType = $stub->type;
    $me->stubId = $stub->id;
    $me->save();
    return $me;
  }
  static function fetch($ugid, $threadId, $userId = null, $attachStub = false) {
    $c = new static();
    $c->threadId = $threadId;
    $c->Posts = MsgPost_Stub::asJoin();
    $c->Client = new ClientStub();
    if ($userId)
      $c->Inbox = MsgInbox_Stub::asJoin($userId); 
    $rec = static::fetchOneBy($c);
    if ($rec && $attachStub)
      $rec->attachStub();
    return $rec;
  }
  static function fetchByStub($stub, $ugid, $userId = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->stubType = $stub->type;
    $c->stubId = $stub->id;
    $c->Posts = MsgPost_Stub::asJoin();
    if ($userId)
      $c->Inbox = MsgInbox_Stub::asJoin($userId); 
    $rec = static::fetchOneBy($c);
    return $rec;
  }
  static function fetchForPreview($rec, $userId = null) {  // e.g. DocScan
    $c = new static();
    $c->userGroupId = $rec->userGroupId;
    $c->stubType = $rec->getDocStubType();
    $c->stubId = $rec->getPkValue();
    $c->Posts = MsgPost_Stub::asJoin();
    if ($userId)
      $c->Inbox = MsgInbox_Stub::asJoin($userId);
    $rec = static::fetchOneBy($c);
    return $rec;
  }
  static function fetchForSigning($ugid, $threadId, $userId) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->threadId = $threadId;
    $c->Inbox = MsgInbox_Stub::asUnreviewedJoin($userId);
    $rec = static::fetchOneBy($c);
    if ($rec->Inbox == null)
      return null;
    if ($rec)
      $rec->attachStub();
    return $rec;
  }
  static function fetchAllUnreviewed($ugid, $userId, $cid = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Inbox = CriteriaJoin::requires(MsgInbox_Stub::asCriteria_unreviewed($userId));
    $c->clientId = $cid;
    $c->Posts = MsgPost_Stub::asJoin();
    $c->Client = new ClientStub();
    return static::fetchAllBy($c);
  }
  static function fetchAllByInboxes($ugid, $inboxes, $cid = null) {
    $recs = array();
    foreach ($inboxes as $inbox)
      $recs[] = static::fetchByInbox($ugid, $inbox, $cid);
    return array_filter($recs);
  }
  static function fetchByInbox($ugid, $inbox, $cid) {
    $rec = static::fetch($ugid, $inbox->threadId);
    if ($cid && $rec->clientId != $cid)
      return;
    $rec->Inbox = $inbox;
    if ($cid)
      $rec->attachStub();
    return $rec;
  }
}
class MsgPost_Stub extends MsgPostRec implements NoAudit {
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
  public function getJsonFilters() {
    return array(
      'dateCreated' => JsonFilter::editableDateTime());
  }
  //
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optionalAsArray($c);
  }
  static function create($thread, $userId, $name, $sendTo, $action) {
    $me = new static();
    $me->threadId = $thread->threadId;
    $me->action = $action;
    $me->dateCreated = nowNoQuotes();
    $me->authorId = $userId;
    $me->author = $name;
    $me->sendTo = $sendTo;
    $me->save();
    return $me;
  }
  static function create_asReviewed($thread, $userId, $name) {
    return static::create($thread, $userId, $name, null, static::ACTION_REVIEWED);
  }
  static function create_asRequest($thread, $userId, $name, $sendTo) {
    return static::create($thread, $userId, $name, $sendTo, static::ACTION_CREATE);
  }
}
class MsgInbox_Stub extends MsgInboxRec implements NoAudit {
  //
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  //
  public function save_asReviewed() {
    $this->isRead = static::IS_REVIEWED;
    $this->save();
    return $this;
  }
  public function save_asClosed() {
    $this->isRead = static::IS_CLOSED;
    $this->save();
    return $this;
  }
  //
  static function create($thread, $post, $recipient) {
    $me = new static();
    $me->recipient = $recipient;
    $me->threadId = $thread->threadId;
    $me->postId = $post->postId;
    $me->isRead = static::IS_UNREVIEWED;
    $me->save();
    return $me;
  }
  static function countUnreviewed($userId) {
    $c = static::asCriteria($userId, static::IS_UNREVIEWED);
    return static::count($c);
  }
  static function fetchAllUnreviewed($userId) {
    $c = static::asCriteria($userId, static::IS_UNREVIEWED);
    return static::fetchAllBy($c);
  } 
  static function asJoin($userId) {
    $c = static::asCriteria($userId, null);
    return CriteriaJoin::requires($c);
  }
  static function asUnreviewedJoin($userId) {
    $c = static::asCriteria_unreviewed($userId);
    return CriteriaJoin::optional($c);
  }
  static function closeAll($threadId) {
    $c = new static();
    $c->threadId = $threadId;
    $c->isRead = static::IS_UNREVIEWED;
    $recs = static::fetchAllBy($c);
    foreach ($recs as $rec)
      $rec->save_asClosed();
  }
  static function asCriteria_unreviewed($userId) {
    return static::asCriteria($userId, static::IS_UNREVIEWED);
  }
  //
  private static function asCriteria($userId, $isRead) {
    $c = new static();
    $c->recipient = $userId;
    $c->isRead = $isRead;
    return $c;
  }
}
class ThreadExistsException extends Exception {}
 