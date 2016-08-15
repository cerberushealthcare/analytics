<?php
//
/** Cacheable paragraph plus custom others */
class MyPar extends Rec {
  //
  public $Par;  // already serialized into JSON
  public $Others;  // array('quid'=>array('custom1','custom2'),..)
  //
  public function toJson() {
    return '{"Par":' . stripslashes($this->Par) . ',"Others":' . jsonencode($this->Others) . '}';
  }
  //
  static function fetch($tid, $sid, $puid, $userId) {
    $me = new static();
    $me->Par = TParCache::get($sid, $puid);  
    $me->Others = TOther::fetchValueMap($userId, $tid, $sid, $puid);
    return $me;
  }
}
//
class TParCache extends ParCacheRec {
  //
  public $sectionId;
  public $parUid;
  public $json;
  //
  static function /*json*/get($sid, $puid) {
    $c = new static($sid, $puid);
    $me = static::fetchOneBy($c);
    if ($me == null) 
      $me = static::build($sid, $puid);
    return $me->json;
  }
  protected static function build($sid, $puid) {
    $par = TPar::fetchBy($sid, $puid);
    $me = new static();
    $me->sectionId = $sid;
    $me->parUid = $puid;
    $me->json = jsonencode($par);
    $me->save();
    return $me;
  }
}
