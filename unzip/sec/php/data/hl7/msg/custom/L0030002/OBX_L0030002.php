<?php
class OBX_L0030002 extends OBX {
  //
  /* valueType */
  const VALUETYPE_NUMERIC = 'NM';
  const VALUETYPE_STRING = 'ST';
  /* abnormal */
  const ABNORMAL_HIGH = 'H';
  const ABNORMAL_LOW = 'L';
  static $ABNORMALS = array(
    self::ABNORMAL_HIGH => 'High', 
    self::ABNORMAL_LOW => 'Low');
  /* resultStatus */
  const RESULTSTATUS_FINAL = 'F';
  const RESULTSTATUS_CORRECTION = 'C';
  const RESULTSTATUS_PENDING = 'I';
  static $RESULTSTATUSES = array(
  self::RESULTSTATUS_FINAL => 'Final results',
  self::RESULTSTATUS_CORRECTION => 'Record is a correction and replaces a final result',
  self::RESULTSTATUS_PENDING => 'Specimen in lab; results pending');
}
