<?php
require_once "inc/requireLogin.php";
require_once 'php/data/rec/group-folder/GroupFolder_Ccd.php';
require_once 'php/data/xml/ClinicalXmls.php';
require_once 'inc/uiFunctions.php';
//
$id = $_GET['id'];
$cid = geta($_GET, 'cid');
$file = GroupFile_Ccd::from($id);
$xml = ClinicalXmls::parse($file->readContents());
logit_r($xml, 'xml');
?>
<html>
<head>
<title>Clinical Record</title>
<link rel='stylesheet' type='text/css' href='js/_ui/DocPreview.css' />
<style>
BODY {
font-family:Arial;
font-size:10pt;
}
TABLE {
  border-collapse:collapse;
  margin-bottom:1em;
  font-size:10pt;
}
H2 {
  margin:0;
  padding:0 0 0.2em 0;
  font-size:13pt;
}
TH {
  text-align:left;
  padding:2px 4px;
  vertical-align:bottom;
  background-color:#e0e0e0;
}
TD {
  vertical-align:top;
}
TABLE.demo TH {
  width:70px;
  vertical-align:top;
  text-align:right;
}
</style>
</head>
<body>
<div class='ViewXml'>
<? echo $xml->asHtml($cid) ?>
</div>
</body>
</html>
<script>
//window.print();
</script>
