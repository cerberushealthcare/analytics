<?php
require_once 'php/data/file/_PdfHtmlFile.php';
//
abstract class ClientPdfFile extends PdfHtmlFile {
  //
  /**
   * @param Client $client
   * @param string $title
   * @param string $dos SQL-formatted (optional)
   * @param string $groupName (optional)
   */
  public function setHeader(/*Client*/$client, $title, $dos = null, $groupName = null) {
    if ($dos)
      $title .= ' (' . formatFullDate($dos) . ')';
    if ($groupName == null) {
      global $login;
      $groupName = $login->User->UserGroup->name;
    }
    $html = Html::create()
      ->br($client->getFullName() . ' (DOB ' . formatConsoleDate($client->birth) . ')')
      ->br($title)
      ->br($groupName)
      ->br('Date Printed: ' . formatNowTimestamp())
      ->out();
    return parent::setHeader($html);
  }
  //
  static function create() {
    $me = parent::create()->withPaging(); 
    return $me;
  }
}

