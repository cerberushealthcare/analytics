<?php
require_once 'php/data/LoginSession.php';
require_once 'php/pdf/HTMLPDF.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/rec/group-folder/GroupFolder_Scanning.php';
//
$login = LoginSession::verify_forServer();
$sfile = ScanFile::fetch(201);
$folder = GroupFolder_Scanning::open($login->userGroupId);
$gfile = GroupFile_Scanning::from($sfile->filename, $sfile->userGroupId);
$image = $gfile->asImage($sfile->mime);
ob_start();
$gfile->outputImage($image, $sfile->mime);
$contents = ob_get_contents();
ob_end_clean();
$data = "@" . base64_encode($contents);
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->Image($data);
$filename = "test.pdf";
$pdf->Output($filename, 'D');
exit;
//$html = '<img src="@' . $imgsrc . '" border="0" />';
//
//print_r($html);
//exit;
$htmlBody = $html;
$htmlHeader = null;
$filename = "test.pdf";
$author = null;
$title = null;
$font = 'helvetica';
$pdf = new HTMLPDF($font);
//$pdf->setDocInfo($author, $title);
$pdf->setHTML($htmlBody, $htmlHeader);
$pdf->Output($filename, 'D');
?>