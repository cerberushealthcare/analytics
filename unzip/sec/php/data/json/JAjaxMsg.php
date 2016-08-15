<?php
require_once "php/data/json/_util.php";
require_once "php/data/rec/ExceptionRec.php";

/*
 * Ajax return value wrapper
 * Returns {
 *    'id':id,
 *    'obj':value
 *   }
 */
class JAjaxMsg {
	public $id;
	public $obj;
	/**
	 * @param string $id
	 * @param string $json
	 */
	public function __construct($id, $json) {
		$this->id = $id;
		$this->obj = $json;
	}
	public function out() {
		return cb(qq("id", $this->id) . C . qqo("obj", $this->obj));
	}
	/*
	 * Returns {
	 *    'id':'error',
	 *    'obj':{
	 *      'type':errorClass, 
	 *      'message':errorMessage}
	 *   }
	 */
	static function constructError($e) {
	  if ($e instanceof SessionInvalidException)
	    return new static('save-timeout', 'null');
	  $ex = ExceptionRec::from($e);
	  return new JAjaxMsg('error', $ex->toJson());
	}
	static function asNull() {
	  $j = new self('null', null);
	  return $j->out();
	}
	static function asTimeout() {
    $m = new self('save-timeout', 'null');
    return $m->out();
	}
}
?>