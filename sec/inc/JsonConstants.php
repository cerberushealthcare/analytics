<?php
class JsonConstants {
  //
  static function add($names) {
    $adds = array();
    $lookups = array();
    foreach ($names as $name) {
      $fn = "add_$name";
      static::$fn($adds, $lookups);
    }
    foreach ($adds as $add)
      echo $add . "\n";
    static::asLookups($lookups);
  }
  //
  static function add_Face(&$adds, &$lookups) {
    static::add_Client($adds);
    static::add_DocHistory($adds);
    static::add_Scanning($adds, $lookups);
    static::add_OrderEntry($adds);
    static::add_Portal($adds); 
    static::add_Proc($adds);
    static::add_Doctors($adds);
    static::add_Users($adds);
    static::add_Templates($adds);
    static::_global($adds, 'Diagnosis');
    static::_global($adds, 'IpcHm');
    static::_global($adds, 'IolEntry');
  }
  static function add_QuickConsole(&$adds, &$lookups) {
    static::add_Templates($adds);
  }
  static function add_Tracking(&$adds) {
    static::add_Address($adds);
    static::add_OrderEntry($adds);
    static::add_DocHistory($adds);
    static::add_Messaging($adds);
    static::add_Areas($adds);
    static::add_Templates($adds);
  }
  static function add_DocHistory(&$adds) {
    static::add_Messaging($adds);
    static::_global($adds, 'DocStub');
  }
  static function add_Reporting(&$adds, &$lookups) {
    static::add_Address($adds);
    static::add_Client($adds);
    static::add_Templates($adds);
    static::add_DrugSubclasses($adds);
    static::add_Proc($adds);
    static::add_Users($adds);
    static::add_Areas($adds);
    static::_global($adds, 'Diagnosis');
    static::_global($adds, 'SessionMedNc');
    static::_global($adds, 'IpcHm');
    static::_class($adds, 'ReportCriteria');
    static::_class($adds, 'RepCritRec');
    static::_class($adds, 'RepCrit_Client');
    static::_class($adds, 'RepCrit_Audit');
    static::_class($adds, 'RepCritValue');
    static::_class($adds, 'RepCritJoin');
    static::_class($adds, 'AuditRec');
    static::_global($adds, 'Templates', 'Sessions', 'getTemplateJsonList');
    static::_global($adds, 'Children', 'UserGroups', 'getChildrenJsonList');
    $lookups[] = 'LookupAreas';
  }
  static function add_Messaging(&$adds) {
    static::_global($adds, 'MsgThread');
    static::_global($adds, 'MsgPost');
    static::_global($adds, 'MsgInbox');
    static::_global($adds, 'UserRole');
  }
  static function add_Profile(&$adds) {
    static::add_Address($adds);
    static::add_Users($adds);
    static::_global($adds, 'UserGroup');
    static::_global($adds, 'NcUser');
    static::_global($adds, 'BillSource', 'BillSourceRec');
  }
  static function add_Scanning(&$adds, &$lookups) {
    static::add_OrderEntry($adds);
    static::add_Proc($adds);
    static::add_Doctors($adds);
    static::add_Address($adds);
    static::_global($adds, 'ScanIndex');
    static::add_Areas($adds);
    static::_global($adds, 'UserRole');
    $lookups[] = 'LookupAreas';
  }
  static function add_Templates(&$adds) {
    static::_global($adds, 'Question', 'TQuestion');
    static::_global($adds, 'Test');
    static::_global($adds, 'Action');
    static::_global($adds, 'Ipc');
  }
  static function add_Areas(&$adds) {
    static::_globalLevelRec($adds, 'Areas');
  }
  static function add_Doctors(&$adds) {
    static::_global($adds, 'Docs', 'UserGroups', 'getDocsJsonList');
  }
  static function add_Users(&$adds) {
    static::_global($adds, 'Users', 'UserGroups', 'getActiveUsersJsonList');
    static::_global($adds, 'UserRole');
  }
  static function add_OrderEntry(&$adds) {
    static::_global($adds, 'TrackItem');
    static::_global($adds, 'TrackEvent');
  }
  static function add_Portal(&$adds) {
    static::_global($adds, 'PortalUser');
  }
  static function add_Proc(&$adds) {
    static::_global($adds, 'ProcResult');
    static::_global($adds, 'Ipc');
  }
  static function add_DrugSubclasses(&$adds) {
    static::_global($adds, 'DrugSubclasses', 'DrugClasses', 'getSubclassJsonList');
  }
  static function add_Address(&$adds) {
    static::_global($adds, 'Address');
  }
  static function add_Client(&$adds) {
    static::_global($adds, 'Client');
  }
  //
  private static function _global(&$adds, $name, $class = null, $method = 'getStaticJson') {
    if ($class == null)
      $class = $name;
    if (! isset($adds[$name]))
      $adds[$name] = "C_$name=" . call_user_func("$class::$method") . ";";
  }
  private static function _class(&$adds, $name) {
    if (! isset($adds[$name]))
      $adds[$name] = "$name.constants(" . self::getStatics($name) . ');';
  }
  private static function _globalLevelRec(&$adds, $name) {
    static::_global($adds, $name, null, 'getListJson');
  }
  /*
   * Deprecated
   */
  static function write() {
    $includeLookups = func_get_args();
    echo '<script type="text/javascript" language="JavaScript">';
    self::asGlobal('Address');
    self::asGlobal('Client');
    self::asGlobal('Diagnosis');
    self::asGlobal('SessionMedNc');
    self::asGlobal('Ipc');
    self::asGlobal('IpcHm');
    self::asGlobal('ProcResult');
    self::asClass('AuditRec');
    self::asClass('ReportCriteria');
    self::asClass('RepCritRec');
    self::asClass('RepCrit_Client');
    self::asClass('RepCrit_Audit');
    self::asClass('RepCritValue');
    self::asClass('RepCritJoin');
    self::asLookups($includeLookups);
    echo '</script>';
  } 
  static function writeGlobals() {
    echo '<script type="text/javascript" language="JavaScript">';
    $names = func_get_args();
    foreach ($names as $name) 
      self::asGlobal($name);
    echo '</script>';
  }
  static function writeClasses() {
    echo '<script type="text/javascript" language="JavaScript">';
    $names = func_get_args();
    foreach ($names as $name) 
      self::asClass($name);
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
  static function asLevelRec($class) {
    if (self::exists($class)) 
      echo "C_$class=" . jsonencode($class::fetchList());
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
        echo "C_Lookups=" . LookupRec::getJsonListsFromArray($lookups) . ';';
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