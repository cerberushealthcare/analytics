<?php
class ApiException extends Exception {
  /**
   * Provide REST response for error
   * @return string
   */
  function getResponse() {
    return $this->getMessage();
  }
}
?>