<?php
require_once 'Lang_Files.php';
require_once 'Lang_Sql.php';
//
class LangImport {
  //
  static function exec() {
    $langs = LangFile::open();
    $langs = IsoLang_I::out($langs);
    LangSql::write($langs);
  }
}
