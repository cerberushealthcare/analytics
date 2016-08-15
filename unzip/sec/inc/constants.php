<?php
class ConstantsWriter {
  //
  static function write() {
    $includeLookups = func_get_args();
    echo '<script type="text/javascript" language="JavaScript">';
    self::asGlobal('Address');
    self::asGlobal('Client');
    self::asGlobal('Diagnosis');
    self::asGlobal('Ipc');
    self::asGlobal('ProcResult');
    self::asClass('RepCritRec');
    self::asClass('RepCritValue');
    self::asClass('RepCritJoin');
    self::asLookups($includeLookups);
    echo '</script>';
  } 
  static function asClass($class) {
    if (self::exists($class)) 
      echo "$class.constants(" . self::getStatics($class) . ");";
  }
  static function asGlobal($class) {
    if (self::exists($class))
      echo "C_$class=" . self::getStatics($class) . ";";
  }
  static function asLookups($include) {
    if ($include) {
      global $login;
      $lookups = array();
      if (self::lookupIncluded($include, $class = 'LookupAreas'))
        self::addLookup($lookups, $class);
      if (self::lookupIncluded($include, $class = 'LookupScheduling')) {
        self::addLookup($include, $lookups, $class, 'getApptTypes');
        self::addLookup($include, $lookups, $class, 'getStatuses');
        self::addLookup($include, $lookups, $class, 'getProfileFor', $login->userId);
      }
      if (! empty($lookups)) 
        echo "C_Lookups=" . LookupRec::getJsonListsFromArray($lookups);
    }
  }
  static function addLookup(&$lookups, $class, $method = 'get', $arg = null) {
    $recs = self::getLookup($class, $method, $arg);
    if ($recs) 
      $lookups[] = $recs;
  }  
  static function getLookup($class, $method, $arg) {
    return $class::$method($arg);
  }
  static function getStatics($class) {
    return $class::getStaticJson();
  }
  static function exists($class) {
    return class_exists($class, false);
  }
  static function lookupIncluded($includeLookups, $class) {
    return in_array($class, $includeLookups);
  }
}
?>
