<?php
require_once 'php/data/file/client-pdf/_ClientPdfFile.php';
require_once 'php/data/rec/sql/_ClientDocRec.php';
require_once 'php/data/rec/sql/_ClientRec.php';
//
/**
 * Patient Documents
 * @author Warren Hornsby
 */
class PatientDocs {
  //
  static function /*ClientDoc*/get($id) {
    $doc = ClientDoc::fetch($id);
    return $doc;
  }
  static function /*CDoc_ReferralCard*/createReferralCard($cid, $html) {
    global $login;
    $doc = CDoc_ReferralCard::create($cid, $login->userId, $html)->save();
    return $doc;
  }
}
//
class ClientDoc extends ClientDocRec {
  //
  public $clientDocId;
  public $clientId;
  public $type;
  public $dateCreated;
  public $createdBy;
  public $html;
  //
  static $TYPE;
  //
  public function download($title, /*ClientStub*/$client) {
    if ($client == null)
      $client = ClientStub::fetch($this->clientId);
    $pdf = ClientDocPdf::from($client, $this);
    $pdf->download();
  }
  //
  static function create($cid, $type, $by, $html) {
    $me = new static();
    $me->clientId = $cid;
    $me->type = $type;
    $me->dateCreated = nowNoQUotes();
    $me->createdBy = $by;
    $me->html = $html;
    return $me;
  } 
}
class CDoc_ReferralCard extends ClientDoc {
  //
  static $TYPE = self::TYPE_REFERRAL_CARD;
  //
  public function download() {
    parent::download('Referral Card');
  }
  //
  static function create($cid, $by, $html) {
    return parent::create($cid, static::$TYPE, $by, $html);
  }
}
//
class ClientDocPdf extends ClientPdfFile {
  //
  static function from(/*Client*/$client, /*ClientDoc*/$doc) {
    $filename = static::makeFilename('CD', $doc->type, $doc->clientId);
    $title = $doc->getLabel();
    $me = parent::create()
      ->setHeader($client, $title)
      ->setBody($doc->html)
      ->setFilename($filename);
    return $me;
  }
}
