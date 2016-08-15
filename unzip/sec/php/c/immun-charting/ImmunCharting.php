<?php
require_once 'Immun_Charts.php';
require_once 'Immun_Pdf.php';
//
class ImmunCharting {
  //
  static function downloadPdf($cid, $form, $until/*date*/ = null) {
    $asScheduled = empty($form) ? true : false;
    $chart = ImmunChart::fetch($cid, $asScheduled);
    $pdf = Immun_Pdf::create($chart, $form, $until);
    $pdf->download();
  }
  static function downloadSinglePdf($id) {
    $imm = Immun_C::fetch($id);
    $client = PStub_C::fetch($imm->clientId);
    $pdf = Pdf_ImmunSingle::from($client, $imm);
    $pdf->download();
  }
  static function getScheduled($cid) {
    $chart = ImmunChart::fetch_asScheduled($cid);
    return $chart;
  }
}
