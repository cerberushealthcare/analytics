<?php
error_reporting(E_ALL); ini_set('display_errors', '1');
require_once 'server.php';
require_once 'c/tracking/Tracking.php';
//
switch ($action) {
  //
  case 'fetch':
    $airport = $_GET['a'];
    $lat = $_GET['t'];
    $long = $_GET['g'];
    $recs = Tracking::fetch($airport, $lat, $long);
    AjaxResponse::from($recs)->out();
    exit;
}
