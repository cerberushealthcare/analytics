<?
require_once 'php/pdf/HTMLPDF.php';
require_once 'php/data/Html.php';
//

//
class HTMLPDF_Rec extends HTMLPDF {
  //
  /**
   * @param Rec_Pdf $rec
   */
  static function from($rec) {
    $me = static::asHelvetica($rec->getPdfTitle());
    //$me->setHTML($rec->get)
  }
  static function download_from($rec, $filename = null) {
    $me = static::from($rec);
    $me->outputAsDownload($rec->getPdfFilename());
  }
  //
  protected static function getHtmlHeader($rec) {
    $h = new Html();
    $client = $rec->getPdfClient();
    $date = formatLongDate($rec->getPdfDate() ?: nowNoQuotes());
    $h->br($client->getFullName())
      ->br($rec->getPdfTitle())
      ->br($date)
      ->br($fs->UserGroup->name)
      ->br('Date Produced: ' . $this->getDateProduced());
    return $h->out();
  }
  protected static function getTitle($rec) {
    
  }
}
//
class PDF_VisitSummary extends HTMLPDF {
  //
  /**
   * @param VisitSummary $rec
   */
  static function from($rec) {
    $me = static::asHelvetica('Visit Summary');
    $me->setHTML($rec->finalBody, $rec->finalHead);
    $me->setFilename($rec->getFilename());
    return $me;
  } 
}
class PDF_ScanIndex extends HTMLPDF_Rec {
  //
  /**
   * @param ScanIndex $rec
   */
  static function from($rec) {
    $me = static::asHelvetica('Visit Summary');
    $me->filename = 'I' . $rec->scanIndexId . 'pdf';'
    return $me;
  }
}
