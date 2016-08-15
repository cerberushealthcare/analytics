<?php
require_once 'php/data/rec/_Rec.php';
//
/**
 * Exception Record
 */
class ExceptionRec extends Rec {
  //
  public $type;
  public $message;
  public $data;
  //
  public function echoShowErrorJs() {
    echo "Page.showAjaxError(" . $this->toJson() . ")";
  }
  /**
   * @param Exception $e
   * @return ExceptionRec
   */
  static function from($e) {
    if ($e instanceof UnauthorizedException) 
      Logger::logException($e);
    if (! $e instanceof DisplayableException)
      $e = Logger::logException($e);  // Not meant for user eyes; log it and transform to UI-friendly
    $me = new self();
    $me->type = get_class($e);
    $me->message = $e->getMessage();
    $me->data = get($e, 'data');
    return $me;
  }
}
?>

