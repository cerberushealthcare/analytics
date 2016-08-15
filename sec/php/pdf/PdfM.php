<?php
require_once 'php/pdf/mpdf/mpdf.php';
//
class PdfM_Factory {
  //
  static function createMine() {
    global $login;
    switch ($login->userGroupId) {
      case 2645:
        return PdfM_BFEC::create();
      case 1467:
        return PdfM_Nar::create();
      default:
        return PdfM::create();
    }
  }
}
/*
 * Wrapper for mPDF
 */
class PdfM {
  //
  static function create() {
    $mpdf = new mPDF('c', 'letter', 0, 'Helvetica');
    $mpdf->SetDisplayMode('fullpage');
    $me = new static($mpdf);
    return $me;
  }
  // 
  private $mpdf;
  //
  public function __construct($mpdf) {
    $this->mpdf = $mpdf;
  }
  public function setHeader($html = null, $css = null/*additional styles*/) {
    $style = static::getStyle();
    $header = <<<eos
<htmlpageheader name='pagehead1'>
	<div id='header1'>$html</div>
</htmlpageheader>
<htmlpageheader name='pagehead'>
	<div id='header'>$html</div>
</htmlpageheader>
<style>
  $style
  $css
</style>
eos;
    $this->mpdf->WriteHTML($header);
    return $this;
  }
  public function setBody($html) {
    $this->mpdf->WriteHTML($html);
    return $this;
  }
  public function withPaging() {
    $this->mpdf->SetFooter('|Page {PAGENO} of {nb}|');
    return $this;
  }
  public function download($filename) {
    $this->mpdf->Output($filename, 'D');
  }
  public function save($filename) {
    $this->mpdf->Output($filename, 'F');
  }
  //
  protected static function getStyle($css = null) {
    $cssp = static::getPageStyle();
    $cssp1 = static::getPageStyle_page1();
    $cssh = static::getHeaderStyle();
    $cssh1 = static::getHeaderStyle_page1(); 
		$cssa = <<<eos
@page {
  header:html_pagehead;
  $cssp
}
@page :first {
  header:html_pagehead1;
  $cssp1
}
BODY {
	font-size:11pt;
  line-height:13pt;
}
DIV {
	margin:0;
	padding:0;
}
TABLE {
	width:100%;
	border-collapse:collapse;
}
TD {
  vertical-align:top;
}
DIV#header {
  font-size:9pt;
	text-align:right;
	line-height:11pt;
  $cssh;
}
DIV#header1 {
  font-size:9pt;
	text-align:right;
	line-height:11pt;
  $cssh1;
}
$css
eos;
    return $cssa;
  }
  protected static function getPageStyle($css = null) {
		return <<<eos
margin-left:10mm;
margin-right:10mm;
margin-top:38mm;
margin-bottom:30mm;
$css
eos;
  }
  protected static function getPageStyle_page1($css = null) {
    return static::getPageStyle($css);
  }
  protected static function getHeaderStyle($css = null) {
		return <<<eos
font-size:9pt;
text-align:right;
line-height:11pt;
$css
eos;
  }
  protected static function getHeaderStyle_page1($css = null) {
    return static::getHeaderStyle($css);
  }
}
/**
 * Bluegrass Family Extended Care (Dr. Richard)
 */
class PdfM_BFEC extends PdfM {
  //
  protected static function getPageStyle_page1() {
		return <<<eos
margin-left:10mm;
margin-right:10mm;
margin-top:50mm;
margin-bottom:30mm;
eos;
  }
  protected static function getHeaderStyle_page1() {
    $url = MyEnv::$PDF_URL . 'img/user/BFECLogo-13.png';
    $css = <<<eos
background:#ffffff url($url) no-repeat top center;";
height:137px;  
eos;
    return parent::getHeaderStyle($css);
  }
}
/**
 * Dr. Nar 
 */
class PdfM_Nar extends PdfM {
  //
  protected static function getPageStyle_page1() {
		return <<<eos
margin-left:10mm;
margin-right:10mm;
margin-top:90mm;
margin-bottom:30mm;
eos;
  }
  protected static function getHeaderStyle_page1() {
    $url = MyEnv::$PDF_URL . 'img/user/nar-logo-bw7.jpg';
    $css = <<<eos
background:#ffffff url($url) no-repeat top center;";
height:189px;  
padding-top:170px;
text-align:left;
eos;
    return parent::getHeaderStyle($css);
  }
}
