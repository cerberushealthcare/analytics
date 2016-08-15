<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/_MessagingRecs.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/email/Email.php';
//
/**
 * Messaging
 * DAO for MsgThread, MsgPost, MsgInbox
 * @author Warren Hornsby
 */
class Messaging {
  //
  /**
   * @return int total unread
   */
  static function getMyUnreadCt() {
    global $login;
    return MsgInbox::countUnread($login->userId);    
  }
  /**
   * @return array(
   *   'recips'=>array(UserRecip,..),
   *   'sections'=>array(SectionMsg,..)) 
   */
  static function getListsAsJson() {
    $lists = array(
      'sections' => SectionMsg::fetchAll(),
      'recips' => Messaging::getMyRecipients());
    return jsonencode($lists);
  }
  static function /*string('[UserRecip,..]')*/getMyRecipsAsJson() {
    return jsonencode(static::getMyRecipients());
  }
  /**
   * @return array(MsgThread(+MsgInbox),..)
   */
  static function getMyInboxThreads() {
    global $login;
    //$inboxes = MsgInbox::fetchForInbox($login->userId);
    //return MsgThread::fetchAllByInboxes($inboxes);
    return MsgThread::fetchAllForInbox($login->userGroupId, $login->userId);
  }
  /**
   * @return array(MsgThread(+MsgInbox),..)
   */
  static function getAnotherInboxThreads($userId) {
    global $login;
    return MsgThread::fetchAllForOtherInbox($login->userGroupId, $userId);
  }
  /**
   * @return array(MsgThread(+MsgInbox),..)
   */
  static function getMySentThreads() {
    global $login;
    $inboxes = MsgInbox::fetchAllSent($login->userId);
    return MsgThread::fetchAllByInboxes($inboxes);
  }
  /**
   * @param int $clientId
   * @return array(mtid=>MsgThread,..)
   */
  static function getThreadsForClient($clientId) {
    return MsgThread::fetchAllByClient($clientId);    
  }
  /**
   * @param int $mtid
   * @param int $forUserId (optional; to open another's patient message)
   * @return MsgThread(+MsgInbox,MsgPosts)
   */
  static function openThread($mtid, $forUserId) {
    global $login;
    $thread = Messaging::getThread($mtid, $forUserId);
    if ($thread) {
      $thread->MsgPosts = MsgPost::fetchByThread($thread);
      $thread->_unreadCt = MsgInbox::countUnread($login->userId);
      MsgInbox::saveAsRead($thread);
      if ($thread->clientId)
        Auditing::logReviewRec($thread);
    }
    return $thread;
  }
  /**
   * Post reply to existing thread
   * @param int $mtid
   * @param [int,..] $sendTos (optional, if portalUserId supplied) 
   * @param string $body
   * @param string $data
   * @param int $portalUserId (optional)
   * @param DocStub $stub (optional)
   * @param string $email (for optional notification)
   */
  static function postReply($mtid, $sendTos, $body, $data, $portalUserId, $stub, $email) {
    $sendTo = Messaging::buildSendToNames($sendTos, $portalUserId);
    $thread = Messaging::getThread($mtid);
    if ($thread) {
      Dao::begin();
      try {
        $post = Messaging::addPost(MsgPost::ACTION_REPLY, $thread, $sendTo, $body, $data, $stub, $email);
        if ($thread->MsgInbox)
          $thread->MsgInbox->saveAsSent($post);
        MsgInbox::saveAsUnreadFor($sendTos, $post);
        if ($portalUserId) {
          require_once 'php/data/rec/sql/PortalMessaging.php';
          PortalInbox::saveAsUnreadFor($portalUserId, $post);
        }
        Dao::commit();
      } catch (Exception $e) {
        Dao::rollback();
        throw $e;
      }
    }
  }
  /**
   * Add a post and complete thread
   * @param int $mtid
   * @param string $body
   * @param string $data
   */
  static function postComplete($mtid, $body, $data, $stub) {
    $thread = Messaging::getThread($mtid);
    if ($thread) {
      Dao::begin();
      try {
        $post = Messaging::addPost(MsgPost::ACTION_CLOSE, $thread, null, $body, $data, $stub);
        $thread->MsgInbox->saveAsClose($post);
        $thread->saveAsClose();
        Dao::commit();       
      } catch (Exception $e) {
        Dao::rollback();
        throw $e;
      }
    }
  }
  /**
   * Create a new thread and send first post
   * @param int $cid
   * @param int $priority MsgThread::PRIORITY
   * @param string $subject
   * @param [int,..] $sendTos
   * @param string $body
   * @param string $data
   * @param string $email (for optional notification)
   * @param string $dateActive (optional)
   */
  static function newThread($cid, $priority, $subject, $sendTos, $body, $data, $portalUserId, $stub, $email, $dateActive) {
    global $login;
    Dao::begin();
    try {
      $sendTo = Messaging::buildSendToNames($sendTos, $portalUserId);
      $thread = MsgThread::revive($login->userId, $login->User->name, $login->userGroupId, $cid, $priority, $subject, $portalUserId);
      $thread->save();
      $post = Messaging::addPost(MsgPost::ACTION_CREATE, $thread, $sendTo, $body, $data, $stub, $email);
      MsgInbox::createForThreadCreator($post);
      MsgInbox::saveAsUnreadFor($sendTos, $post, $dateActive);
      if ($portalUserId) {
        require_once 'php/data/rec/sql/PortalMessaging.php';
        PortalInbox::saveAsUnreadFor($portalUserId, $post);  // TODO: add dateActive
      }
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
  /**
   * Complete a new thread without recipients (quick documentation for patient) 
   * @param int $cid
   * @param int $priority MsgThread::PRIORITY
   * @param string $subject
   * @param string $body
   * @param string $data
   */
  static function newThreadComplete($cid, $priority, $subject, $body, $data, $stub) {
    global $login;
    $thread = MsgThread::revive($login->userId, $login->User->name, $login->userGroupId, $cid, $priority, $subject);
    Dao::begin();
    try {
      $thread->saveAsClose();
      $post = Messaging::addPost(MsgPost::ACTION_CLOSE, $thread, null, $body, $data, $stub);
      $inbox = MsgInbox::createForThreadCreator($post);
      $inbox->saveAsClose($post);
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
  //
  /*
   * Retrieves MsgThread+MsgInbox and authenticates access 
   */
  private static function getThread($mtid, $forId = null) {
    global $login;
    $userId = $forId ?: $login->userId;
    $thread = MsgThread::fetch($mtid);
    if ($thread) {
      $inbox = MsgInbox::fetchByRecip($userId, $thread);
      if ($inbox == null) 
        if ($thread->creatorId != $login->userId)
          if (! $login->admin && $thread->ClientStub == null)
            LoginDao::throwSecurityError("access $access mtid", $thread->threadId);
      $thread->MsgInbox = $inbox;
    }
    return $thread;
  }
  /*
   * Get all recipients I can send messages to
   */
  private static function getMyRecipients($mapped = false) {
    global $login;
    return UserRecip::fetchAllByUgid($login->userGroupId, $mapped);
  }
  /*
   * Given [id,..] return 'name;..'
   */
  private static function buildSendToNames($sendTos, $portalUserId = null) {
    $s = array();
    if ($sendTos) { 
      global $login;
      $recipients = UserRecip::fetchAllByUgid($login->userGroupId, true);
      foreach ($sendTos as &$id) 
        $s[$id] = self::getRecipientName($recipients, $id);
    }
    if ($portalUserId) 
      $s[] = self::getPortalUserName($portalUserId);
    return empty($s) ? null : implode($s, ';');
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
  private static function getPortalUserName($id) {
    require_once 'php/data/rec/sql/PortalUsers.php';
    $rec = PortalUser_A::fetch($id);
    return $rec->Client->getFullName();
  }
  /*
   * Add post from UI
   */
  private static function addPost($action, $thread, $sendTos, $body, $data, $stub = null, $email = null) {
    global $login;
    $post = MsgPost::revive(MsgPost::AUTHOR_TYPE_OFFICE, $login->userId, $login->User->name, $action, $thread->threadId, $sendTos, $body, $data, $stub);
    $post->save();
    if (! empty($email)) 
      Email_PortalNotify::send($email);
    return $post;
  }  
}
//
/**
 * Message Thread
 */
class MsgThread extends MsgThreadRec {
  //
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;  // TYPE_GENERAL
  public $status;
  public $priority;
  public $subject;
  public $stubType;
  public $stubId;
  public /*ClientStub*/ $ClientStub;
  public /*MsgInbox*/ $MsgInbox;
  public /*[MsgPost]*/ $MsgPosts;
  public $_closed;
  public $_unreadCt;
  //
  const POST_ACTION_CLOSE = MsgPost::ACTION_CLOSE;
  //  
  public function getJsonFilters() {
    return array(
      '_unreadCt' => JsonFilter::integer(),
      'dateCreated' => JsonFilter::informalDateTime());
  }
  public function toJsonObject(&$o) {
    $o->_closed = ($this->isClosed());
  }
  public function getAuditLabel() {
    return $this->getLabel();
  }
  public function getLabel() {
    $label = ($this->isStat()) ? MsgThread::PRIORITY_STAT . ': ' : '';
    $label .= $this->subject;
    return $label;
  }
  public function getDate() {
    return $this->dateToSend ?: $this->dateCreated;
  }
  public function getLastPost() {
    $post = null;
    if ($this->MsgPosts) 
      $post = end($this->MsgPosts); 
    return $post;
  }
  public function saveAsClose() {
    $this->status = static::STATUS_CLOSED;
    $this->save();
  }
  //
  /**
   * @param int $mtid
   * @return MsgThread(+Client)
   */
  static function fetch($mtid) {
    $rec = new static($mtid);
    $rec->ClientStub = new ClientStub();
    return parent::fetchOneBy($rec);
  }
  static function fetchWithPosts($mtid) {
    $thread = parent::fetch($mtid);
    $thread->MsgPosts = MsgPost::fetchByThread($thread);
    return $thread;
  }
  /**
   * @param int $cid
   * @return array(mtid=>MsgThread,..)
   */
  static function fetchAllByClient($cid) {
    $rec = new MsgThread();
    $rec->clientId = $cid;
    $recs = parent::fetchAllBy($rec, new RecSort('-dateCreated'), null, 'threadId');
    return $recs;
  }
  /**
   * @param [Inbox,..] $inboxes
   * @return array(MsgThread(+MsgInbox),..)
   */
  static function fetchAllByInboxes($inboxes) {
    $threads = array();
    foreach ($inboxes as &$inbox) {
      $thread = MsgThread::fetch($inbox->threadId);
      $thread->MsgInbox = $inbox;
      if (! $thread->isClosed() || $thread->MsgInbox->isUnread()) 
        $threads[] = $thread;
    }
    Rec::sort($threads, new RecSort('MsgInbox.isRead', '-priority', '-MsgInbox.postId'));
    return $threads;
  }
  static function fetchAllForInbox($ugid, $userId) {  // to replace fetchAllByInboxes
    $c = new static();
    $c->userGroupId = $ugid;
    $c->status = CriteriaValue::notEqualsNumeric(static::STATUS_CLOSED);
    $c->MsgInbox = CriteriaJoin::requires(MsgInbox::asCriteria_forInbox($userId));
    $c->ClientStub = new ClientStub();
    return static::fetchAllBy($c, new RecSort('MsgInbox.isRead', '-priority', '-MsgInbox.postId'));
  }
  static function fetchAllForOtherInbox($ugid, $userId) {  // read another's inbox, only for patient records
    $c = new static();
    $c->userGroupId = $ugid;
    $c->status = CriteriaValue::notEqualsNumeric(static::STATUS_CLOSED);
    $c->MsgInbox = CriteriaJoin::requires(MsgInbox::asCriteria_forInbox($userId));
    $c->ClientStub = CriteriaJoin::requires(new ClientStub());
    return static::fetchAllBy($c, new RecSort('MsgInbox.isRead', '-priority', '-MsgInbox.postId'));
  }
  /**
   * @return MsgThread 
   */
  static function revive($creatorId, $creator, $ugid, $cid, $priority, $subject, $portalUserId = null) {
    $thread = new MsgThread();
    $thread->userGroupId = $ugid;
    $thread->clientId = $cid;
    $thread->creatorId = $creatorId;
    $thread->creator = $creator;
    $thread->type = ($portalUserId) ? MsgThread::TYPE_PATIENT : MsgThread::TYPE_GENERAL;
    $thread->status = MsgThread::STATUS_OPEN;
    $thread->priority = $priority;
    $thread->subject = $subject;
    return $thread;
  }
}
/**
 * Message Post 
 */
class MsgPost extends MsgPostRec implements NoAudit {
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
  public $data;
  public $stubType;
  public $stubId;
  public $portalFile;
  //
  public function attachStub() {
    if ($this->stubType && $this->stubId) {
      global $login;
      $stub = DocStub::fetch($this->stubType, $this->stubId);
      $this->Stub = DocStub_Preview::fetch($stub, $login->userId);
    }
  }
  //
  /**
   * @param MsgThread $thread
   * @return array(MsgPost,..)
   */
  static function fetchByThread($thread) {
    $c = new static();
    $c->threadId = $thread->threadId;
    $recs = parent::fetchAllBy($c);
    foreach ($recs as &$rec)
      $rec->attachStub();
    return Rec::sort($recs, new RecSort('postId'));
  }
  /**
   * @return MsgPost
   */
  static function revive($authorType, $authorId, $author, $action, $mtid, $sendTos, $body, $data, $stub, $portalFile = null) {
    $post = new MsgPost();
    $post->threadId = $mtid;
    $post->action = $action;
    $post->authorType = $authorType;
    $post->authorId = $authorId;
    $post->author = $author;
    $post->body = $body;
    $post->sendTo = $sendTos; 
    $post->data = $data;
    if ($stub) {
      $post->stubType = $stub->type;
      $post->stubId = $stub->id;
    }
    $post->portalFile = $portalFile; 
    return $post;
  }
}
/**
 * Message Inbox
 * Natural key: $recipient, $threadId
 */
class MsgInbox extends MsgInboxRec implements NoAudit {
  //
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  public $dateActive;
  public /*MsgPost*/ $MsgPost;
  /*
  const IS_UNREAD = '0';
  const IS_READ = '1';
  const IS_SENT = '2';
  const IS_CLOSED = '9';
  */
  /**
   * @param MsgPost $post just added
   */
  public function saveAsSent($post) {
    if ($this->isRead == static::IS_READ) {
      $this->postId = $post->postId;
      $this->isRead = static::IS_SENT;
      $this->save();
    }
  }
  /**
   * @param MsgPost $post just added
   */
  public function saveAsClose($post) {
    $this->isRead = static::IS_CLOSED;
    $this->save();
    if ($this->threadId) {
      $sql = "UPDATE msg_inbox SET is_read=9 WHERE thread_id=$this->threadId AND is_read=0";
      Dao::query($sql);
    }
  }
  //
  /**
   * @param int $userId
   * @return int total unread
   */
  static function countUnread($userId) {
    $c = new MsgInbox();
    $c->recipient = $userId;
    $c->isRead = static::IS_UNREAD;
    $c->dateActive = static::getDateActiveCriteria();
    $c->Thread = MsgThread_Open::asJoin();
    return parent::count($c);
  }
  /**
   * @param int $userId
   * @return array(MsgInbox,..)
   */
  static function fetchForInbox($userId) {
    return static::fetchAllByIsRead($userId, Criteriavalue::lessThanOrEquals(static::IS_READ));
  }
  /**
   * @param int $userId
   * @return array(MsgInbox,..)
   */
  static function fetchAllUnread($userId) {
    return static::fetchAllByIsRead($userId, static::IS_UNREAD);
  }
  /**
   * @param int $userId
   * @return array(MsgInbox,..)
   */
  static function fetchAllSent($userId) {
    return static::fetchAllByIsRead($userId, static::IS_SENT);
  }
  /**
   * @param int $userId
   * @param MsgThread $thread
   * @return MsgInbox
   */
  static function fetchByRecip($userId, $thread) {
    $inbox = new MsgInbox();
    $inbox->recipient = $userId;
    $inbox->threadId = $thread->threadId;
    return parent::fetchOneBy($inbox);
  }
  /**
   * Create (or update) inboxes for recipients 
   * @param [$id,..] $sendTos
   * @param MsgPost $post 
   * @param string $dateActive (optional)
   * @return MsgInbox for self, if sent to self
   */
  static function saveAsUnreadFor($sendTos, $post, $dateActive = null) {
    $mtid = $post->threadId;
    $mpid = $post->postId;
    foreach ($sendTos as &$id) {
      $inbox = static::fetchOrCreate($mtid, $id);
      $inbox->postId = $mpid;
      $inbox->isRead = static::IS_UNREAD;
      $inbox->dateActive = formatFromDate($dateActive);
      $inbox->save();
    }
  }
  /**
   * Mark thread's inbox as read
   * @param MsgThread $thread
   */
  static function saveAsRead(&$thread) {
    $inbox = &$thread->MsgInbox;
    if ($inbox && $inbox->isUnread()) { 
      $post = $thread->getLastPost();
      if ($post) 
        $inbox->postId = $post->postId;
      $inbox->isRead = static::IS_READ;
      $inbox->save();
    }
  }
  /**
   * Create inbox for new thread creator
   * @param MsgPost $post creation post
   * @return MsgInbox
   */
  static function createForThreadCreator($post) {
    $inbox = static::_create($post->threadId, $post->authorId);
    $inbox->postId = $post->postId;
    $inbox->isRead = static::IS_SENT;
    $inbox->save();
    return $inbox;
  }
  static function asCriteria_forInbox($userId) {
    $c = new static();
    $c->recipient = $userId;
    $c->isRead = CriteriaValue::lessThanOrEqualsNumeric(static::IS_READ);
    $c->dateActive = static::getDateActiveCriteria();
    $c->MsgPost = new MsgPost();
    return $c;
  }
  //
  private static function getDateActiveCriteria() {
    return CriteriaValues::_or(
      CriteriaValue::isNull(),
      CriteriaValue::lessThanOrEquals(nowNoQuotes()));
  }
  private static function fetchAllByIsRead($userId, $isRead) {
    $rec = new MsgInbox();
    $rec->recipient = $userId;
    $rec->isRead = $isRead;
    $rec->MsgPost = new MsgPost();
    return parent::fetchAllBy($rec);
  }
  private static function fetchOrCreate($mtid, $id) {
    $inbox = parent::fetchOneBy(static::_create($mtid, $id));
    return ($inbox) ? $inbox : static::_create($mtid, $id);
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
  public $roleType;
  //
  public function getSqlTable() {
    return 'users';
  }
  public function getJsonFilters() {
    return array(
      'roleType' => JsonFilter::integer(),
      'userGroupId' => JsonFilter::omit());
  }
  //
  /**
   * @param int$ugid
   * @param bool $mapped
   */
  static function fetchAllByUgid($ugid, $mapped) {
    $c = new UserRecip();
    $c->userGroupId = $ugid;
    $c->active = 1;
    $fid = ($mapped) ? 'userId' : null;
    return parent::fetchAllBy($c, new RecSort('roleType', 'name'), null, $fid);
  }
}
class MsgThread_Open extends MsgThreadRec {
  //
  public $threadId;
  public $status;
  //
  static function asJoin() {
    $c = new static();
    $c->status = static::STATUS_OPEN;
    return CriteriaJoin::requires($c);
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
  static function fetchAll() {
    $c = new SectionMsg();
    $c->templateId = 25;
    $recs = parent::fetchAllBy($c, new RecSort('sectionId'), null, 'sectionId');
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
  static function fetchAllFor($sectionId) {
    $c = new ParMsg();
    $c->sectionId = $sectionId;
    $c->current = 1;
    $c->major = 1;
    return parent::fetchAllBy($c, new RecSort('desc'), null, 'parId');
  } 
}
//
class Email_PortalNotify extends Email {
  //
  public $subject = 'Patient Portal Notification';
  //
  static function send($to) {
    $e = new static();
    $e->to = $to;
    $e->html()
      ->p("This is a notification that you have received a message to your medical patient portal.")
      ->p_()->add("Please login to ")->a(MyEnv::$BASE_URL . 'portal')->add(' to read this message.')->_()
      ->p("If you have any problems logging in please contact your doctor's office.");
    $e->mail();
  }
}