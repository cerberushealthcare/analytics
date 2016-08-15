<?php
require_once 'php/dao/MsgDao.php';
require_once 'php/data/rec/sql/_SqlRec.php';
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
