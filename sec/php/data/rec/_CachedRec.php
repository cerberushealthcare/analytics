<?php
require_once "php/data/LoginSession.php";
//
abstract class CachedRec extends Rec {
  //
  /**
   * Set field value and autosave record to session if changed
   */
  public function set($fid, $value) {
    if ($this->$fid != $value) {
      $this->$fid = $value;
      $this->save();
    }
    return $this;
  }
  /**
   * Save record to session 
   */
  public function save() {
    if (! isset($this->_cacheKey))
      $this->_cacheKey = static::getCacheKey();
    SessionCache::set($this->_cacheKey, $this);
    return $this;
  }
  /**
   * Fetch record from session if exists, else create new
   * @param string $id (optional)
   * @return static
   */
  static function fetch() {
    $key = static::getCacheKey();
    $me = SessionCache::get($key);
    if ($me == null) {
      $me = new static();
      $me->_cacheKey = $key;
    }
    return $me;
  }
  /**
   * @return static if cached, null otherwise
   */
  static function isCached() {
    $me = SessionCache::get(static::getCacheKey());
    return $me;
  }
  /**
   * Clear from session
   */
  static function clear() {
    $key = static::getCacheKey();
    SessionCache::clear($key);
  }
  //
  protected static function getCacheKey() {
    return get_called_class();
  }
}
