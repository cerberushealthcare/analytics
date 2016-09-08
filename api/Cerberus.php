<?php
//echo file_get_contents("php://input");
//print_r($_REQUEST);
//exit;

/*$post = file_get_contents("php://input");
print_r($_POST);
echo $post;
echo $HTTP_RAW_POST_DATA;
print_r($_GET);*/
//echo fgets($post, 1024);
//exit;

set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'rest/Rest.php';
require_once 'rest/ApiException.php';
require_once 'rest/Cerberus.php';
require_once 'dao/ApiLogger.php';
//
log2('here');
$rest = new Rest();
$data = $rest->data;
$server = new Cerberus();

try {
  $response = $server->request($rest);
  log2($response);
  ob_clean();
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
  session_cache_limiter("nocache");
  echo $response;
} catch (ApiException $e) {
  log2('ApiException: ' . $e->getResponse());
  echo $e->getResponse();
} catch (Exception $e) {
  log2('Exception: ' . $e->getMessage());
  echo $e->getMessage();
  //header('Request not recognized.', true, 405);
}
