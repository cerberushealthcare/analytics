<?php
function log2($msg) {
//return;
  $ts = date("Y-m-d H:i:s");
  $fp = fopen("out.txt", "a");
  fputs($fp, $ts . " " . $msg . "\n");
  fclose($fp);
}
function log2_r($o, $caption = null) {
//return;
  log2((($caption) ? "=== $caption === " : '') . print_r($o, true) . (($caption) ? "=== /$caption ===" : ''));
}