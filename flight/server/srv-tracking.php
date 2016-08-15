<?php
set_include_path('../');
require_once 'server/server.php';
require_once 'app/c/tracking/Tracking.php';
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
