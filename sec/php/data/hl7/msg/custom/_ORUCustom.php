<?php
require_once 'php/data/hl7/msg/ORUMessage.php';
//
abstract class ORUCustom extends ORUMessage {
  //
  abstract public function getUgid();
}
 