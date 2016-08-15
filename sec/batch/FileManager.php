<?php
//
class FileManager_Sql extends FileManager {
  //
  protected $ext = 'sql';
  //
  public function write($line) {
    if (substr($line, -1, 1) != ';')
      $line .= ';';
    parent::write($line);
  }
}
/**
 * For building series of output files that auto-split when exceeding max lines
 * Filenames constructed as 'UG[ugid]_F[index]_[name]_[break].[ext]'
 * e.g. UG3_F1_clients_1.sql
 *      UG3_F1_clients_2.sql
 *      UG3_F2_facesheet_1.sql
 *      UG3_F3_procs_1.sql
 *      UG3_F3_procs_2.sql
 */
class FileManager {
  //
  protected $base;
  protected $ext = 'sql';
  protected $header;
  protected $name;
  protected $index;
  protected $lines;
  protected $max;
  protected $break;
  protected $fp;
  //
  /**
   * @param int $ugid
   * @param string $header (optional)
   */
  public function __construct($ugid, $header = null) {
    $this->base = "out/UG$ugid";
    $this->header = $header;
    $this->index = 0;
  }
  /**
   * Open next file in series, e.g. 'UG1_F4_sessions_1.sql'
   * @param string $name 'sessions'  
   * @param int $maxlines before breaking 
   */
  public function open($name, $maxlines = 0) {
    if ($this->fp)
      $this->close();
    $this->lines = 0;
    $this->name = $name; 
    $this->max = $maxlines;
    $this->index++;
    $this->break = $maxlines > 0 ? 1 : 0;
    $this->fp = $this->fpOpen();
  }
  /**
   * @param string $line
   */
  public function write($line) {
    if ($this->fp == null)
      return; 
    $this->lines++;
    if ($this->max && $this->lines >= $this->max)
      $this->next();
    $this->fpWrite($this->fp, $line);
  }
  public function close() {
    if ($this->fp == null)
      return;
    $this->fpClose($this->fp);
    $this->fp = null;  
  }
  //
  protected function next() {
    $this->fpClose($this->fp);
    $this->break++;
    $this->lines = 0;
    if ($this->break > 20) { 
      echo "Too many breaks.";
      exit;
    }
    $this->fp = $this->fpOpen();
  }
  protected function fpOpen() {
    $filename = $this->makeFilename();
    $fp = @fopen($filename, 'w');
    if ($this->header) 
      $this->fpWrite($fp, $this->header);
    return $fp;
  }
  protected function fpClose($fp) {
    fclose($fp);
  }
  protected function fpWrite($fp, $line) {
    fwrite($fp, $line . "\n");
  }
  protected function makeFilename() {
    $filename = $this->base . '_F' . $this->index . '_' . $this->name;
    if ($this->break > 0)
      $filename .= '_' . $this->break; 
    return $filename . '.' . $this->ext;
  }
}
