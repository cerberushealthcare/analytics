<?php
set_include_path('../../');
require_once 'php/data/rec/sql/dao/Logger.php';
/*
 * Webservice Server
 * Labs "Post Message"
 * @author Warren Hornsby  
 */
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer('message.wsdl');
$server->addFunction('postMessage');
$server->handle();
function postMessage($msg) {
  //Logger::debug_r($msg, 'postMessage');
  return 'ERROR: Not Authorized';
}
