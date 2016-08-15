<?php
// Returns current page minus directories, e.g. "sessions.php"
function currentPage() {
  $a = explode("/", $_SERVER["SCRIPT_NAME"]);
  return $a[count($a) - 1];
}
function currentUrl() {
  if (isset($_SERVER['REQUEST_URI'])) {
    $a = explode("/", $_SERVER["REQUEST_URI"]);
    return array_pop($a);
  } else {
    return '';
  }
}
function redirect($url) {
  $on = currentPage();
  logit_r($on, 'on');
  logit_r($url, 'url');
  if ($url != $on) {  
    header("Location: $url");
    exit;
  }
}
