<?php
require_once '_ClientPdfFile.php';
//
class FacesheetPdf extends ClientPdfFile {
  //
  public function setHeader(/*Client*/$client, $groupName = null) {
    $title = 'Facesheet';
    return parent::setHeader($client, $title, null, $groupName); 
  }
  public function setBody(/*Facesheet*/$fs) {
    $html = FacesheetHtml::create($fs)->out();
    return parent::setBody($html);
  }
  public function setFilename(/*Client*/$client) {
    $filename = static::makeFilename_from($client);
    return parent::setFilename($filename);
  }
  //
  static function create(/*Facesheet*/$fs, $groupName = null) {
    $me = parent::create()
      ->setHeader($fs->Client, $groupName)
      ->setBody($fs)
      ->setFilename($fs->Client);
    return $me;
  }
  static function makeFilename_from($client) {
    return static::makeFilename($client->lastName, $client->firstName, $client->birth);
  }
}
class FacesheetHtml {
  //
  public $fs;
  //
  public function out() {
    $fs = $this->fs;
    $html = Html::create();
    $this->outHeader($html, $fs);
    $this->outMeds($html, $fs);
    return $html->out();
  }
  protected function outHeader($html, $fs) {
    $rec = $fs->Client;
    $html->p_()->h3($rec->getFullName())->table_()
      ->tr_()->th('Gender:')->td($rec->sex)->_()
      ->tr_()->th('Birth:')->td($rec->birth)->_()
      ->tr_()->th('Address:')->td($rec->Address_Home->format())->_()->_()->_();
  }
  protected function outMeds($html, $fs) {
    $meds = get($fs, 'Meds');
    if (! empty($meds)) {
      $html->p_()->h3('Medications')->table_()
        ->tr_()->th('Medication')->th('Instructions')->th('Date(s)')->th('Status')->_();
      foreach ($meds as $med)
        $this->outMed($html, $med);
      $html->_()->_();
    }
  }
  protected function outMed($html, $rec) {
    $html->tr_()->td($rec->name)->td($rec->text)->td(formatDate($rec->date))->td(static::active($rec->active))->_();
  }
  //
  static function create(/*Facesheet*/$fs) {
    $me = new static();
    $me->fs = $fs;
    return $me;
  }
  protected static function active($b) {
    return $b ? 'Active' : 'Inactive';
  }
}