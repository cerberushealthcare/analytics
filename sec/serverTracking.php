<?php
require_once 'server.php';
require_once 'php/data/rec/sql/OrderEntry.php'; 
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->track);
  switch ($action) {
    case 'getOpen':
    case 'getUnsched':
    case 'getClosed':
    case 'getSched':
      $action .= 'Items';
      $recs = OrderEntry::$action(get($obj, 'cid'));
      AjaxResponse::out($action, $recs);
      break;
    case 'get':
      $rec = OrderEntry::get($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit; 
    case 'update':
      $item = OrderEntry::saveItem($obj);
      AjaxResponse::out($action, $item);
      break;
    case 'order': /* Generate from ordersheet */
      $orderItems = $obj;
      $trackCatItems = OrderEntry::order($orderItems);
      AjaxResponse::out($action, $trackCatItems);
      break;
    case 'saveOrder': /* Save from ordersheet */
      OrderEntry::saveOrder($obj);
      AjaxResponse::out($action);
      break;
    case 'saveOrderByIp': /* Save from AddByIpPop */
      OrderEntry::saveOrderSingle($obj);
      AjaxResponse::out($action);
      break;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
