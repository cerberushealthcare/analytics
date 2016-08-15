<?php
require_once 'app/util.php';
//
class RecSort {
  public $fids;
  //
  const ASC = 1;
  const DESC = -1;
  //
  /**
   * @param ($recs, 'fid', '-fid',..) where '-fid' indicates DESC
   *        fid may be recursive, e.g. 'UserStub.userId'
   */
  static function sort(/*$recs, 'fid', 'fid',..*/) {
    $args = func_get_args();
    $recs = array_shift($args);
    $me = new static();
    $me->setFids($args);
    usort($recs, array($me, 'compare'));
    return $recs;
  }
  //
  public function __construct() {
    $fids = func_get_args();
    if (! empty($fids))
      $this->setFids($fids);
  }
  protected function setFids($fids) {
    $this->fids = array();
    foreach ($fids as $fid_) {
      if (! is_array($fid_))
        $fid_ = array($fid_);
      foreach ($fid_ as $fid) {
        $fid = explode('-', trim($fid));
        if (count($fid) == 2)
          $this->fids[$fid[1]] = RecSort::DESC;
        else
          $this->fids[$fid[0]] = RecSort::ASC;
      }     
    }
  }
  public function compare($r1, $r2) {
    $a = array();
    foreach ($this->fids as $fid => $dir) {
      $v1 = getr($r1, $fid);
      $v2 = getr($r2, $fid);
      if (is_string($v1) || is_string($v2))
        $icmp = $dir * strnatcasecmp($v1, $v2);
      else
        $icmp = $dir * bccomp($v1, $v2);
      if ($icmp != 0)
        return $icmp;
    }
    return 0;
  }
}
