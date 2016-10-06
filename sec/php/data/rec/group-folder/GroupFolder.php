<?php
require_once 'php/data/file/_File.php';
require_once 'php/data/rec/_Rec.php';
require_once 'php/data/rec/cryptastic.php';
//
/**
 * GroupFolder 
 * @author Warren Hornsby
 */
class GroupFolder {  /* can implement AutoEncrypt */
  //
  public $ugid;
  public $dir;
  //
  static $EXT_TO_MIME = array(
    'ai'   => 'application/postscript',
    'asf'  => 'video/x-ms-asf',
    'asx'  => 'video/x-ms-asf',
    'avi'  => 'video/x-msvideo',
    'bmp'  => 'image/bmp',
    'doc'  => 'application/msword',
    'dvi'  => 'application/x-dvi',
    'eps'  => 'application/postscript',
    'gif'  => 'image/gif',
    'htm'  => 'text/html',
    'html' => 'text/html',
    'jpeg' => 'image/jpeg',
    'jpg'  => 'image/jpeg',
    'mov'  => 'video/quicktime',
    'mp2'  => 'audio/mpeg',
    'mp3'  => 'audio/mpeg',
    'mpe'  => 'video/mpeg',
    'mpeg' => 'video/mpeg',
    'mpg'  => 'video/mpeg',
    'mpga' => 'audio/mpeg',
    'pdf'  => 'application/pdf',
    'png'  => 'image/png',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'ps'   => 'application/postscript',
    'qt'   => 'video/quicktime',
    'ras'  => 'image/x-cmu-raster',
    'rgb'  => 'image/x-rgb',
    'rm'   => 'audio/x-pn-realaudio',
    'rtf'  => 'text/rtf',
    'swf'  => 'application/x-shockwave-flash',
    'tif'  => 'image/tiff',
    'tiff' => 'image/tiff',
    'txt'  => 'text/plain',
    'wm'   => 'video/x-ms-wm',
    'wma'  => 'audio/x-ms-wma',
    'wmv'  => 'video/x-ms-wmv',
    'xls'  => 'application/vnd.ms-excel',
    'xml'  => 'text/xml',
    'zip'  => 'application/zip');
  //
  const MIME_PDF = 'application/pdf';
  const MIME_XML = 'text/xml'; 
  //
  public function __construct($ugid, $dir) {
    if (! is_dir($dir))
      if (! mkdir($dir, 0, true)) 
        throw new GroupFolderException($dir, 'Unable to access directory');
    $this->dir = $dir;
    $this->ugid = $ugid;
  }
  /**
   * @param GroupUpload $upload
   * @return GroupFile
   */
  public function upload($upload, $returnAutoEncryptFile = false) {
    $filename = $this->getCompleteFilename($upload->name);
    $result = move_uploaded_file($upload->tmpName, $filename);
    $file = GroupFile::from($this, $upload->name);
    if ($this instanceof AutoEncrypt) {
      $afile = AutoEncryptFile::fromGroupFile($file)->save();
      if ($returnAutoEncryptFile)
        return $afile;
    }
    return $file;
  }
  /**
   * @param GroupUpload[] $uploads
   */
  public function uploadAll($uploads) {
    foreach ($uploads as $upload)
      static::upload($upload);
  }
  /**
   * @param GroupFile $file
   * @param string $mime 'image/jpeg' (optional, determined from extension if omitted) 
   */
  public function output($file, $mime = null) {
    if ($this instanceof AutoEncrypt) {
      $efile = AutoEncryptFile::fromGroupFile($file, $mime);
      $efile->output();
    } else {
      $file->output($mime);
    }
  }
  /**
   * @param GroupFile $file
   * @param string $mime 'image/jpeg' (optional, determined from extension if omitted) 
   */
  public function download($file, $mime = null) {
    //logit_r($file, 'download');
    if ($this instanceof AutoEncrypt)
      AutoEncryptFile::fromGroupFile($file, $mime)->download();
    else
      $file->download($mime);
  }
  /**
   * @param GroupFile $file
   */
  public function delete($file) {
    $file->delete();
  } 
  /**
   * @param string $filename
   * @return string
   */
  public function getHash($filename) {
    return sha1_file($filename);
  }
  /**
   * @param string $filename 'file.xml'
   * @return string 'complete\path\file.xml'
   */
  public function getCompleteFilename($filename) {
    //$abs =  realpath($_SERVER["DOCUMENT_ROOT"]) . "\\clicktate\\sec";
    //return "$abs\\$this->dir\\$filename";
    return "$this->dir\\$filename";
  }
  //
  /**
   * @param int $ugid (optional, omit to default)
   * @param string $dir subdirectory
   * @return GroupFolder
   */
  static function open($ugid = null, $dir = null) {
    if ($ugid == null) {
      global $login;
      $ugid = $login->userGroupId;
    }
    $root = static::getRoot($ugid);
    if ($dir)
      $root .= "\\$dir";
    return new static($ugid, $root);
  }
  protected static function getRoot($ugid) {
//    $root = "user-folders\\G$ugid";
    $root = MyEnv::$BASE_PATH . "\user-folders\\G$ugid";
    return $root;
  } 
  /**
   * @param $httpPostFile $_FILES field array('name'=>..,'type'=>..,'tmp_name'=>..,'error'=>..,'size'=>)
   * @return GroupUpload[]
   * @throws GroupUploadException
   */
  static function getUploads($httpPostFile) {
    $files = GroupUpload::fromHttpPostFile($httpPostFile);
    if (empty($files))
      throw new GroupUploadException(null, 'No files were selected');
    return $files;
  }
  /**
   * @param string $filename 'file.jpeg'
   * @return string 'image/jpeg'
   * @throws GroupFolderException
   */
  static function getMime($filename) {
    $a = explode('.', $filename);
    $ext = strtolower(end($a));
    $mime = geta(static::$EXT_TO_MIME, $ext);
    if ($mime == null)
      throw new GroupFolderException(null, "Unable to determine MIME of $filename");
    return $mime;
  }
  /**
   * @return string e.g. 'sec' or 'portal'
   */
  static function cwd() {
    return end(explode("\\", getcwd()));
  }
}
//
class GroupFolderException extends DisplayableException {
  public $dir;
  public function __construct($dir, $message) {
    $this->dir = $dir;
    $this->message = $message; 
  }
}
/**
 * GroupFile
 */
class GroupFile extends Rec {
  //
  public $filename;  // 'file.xml'
  //
  public $contents;  
  public $hash;
  public /*GroupFolder*/ $folder;
  //
  public function getJsonFilters() {
    return array(
      'contents' => JsonFilter::omit());
  }
  public function getCompleteFilename() {
    return $this->folder->getCompleteFilename($this->filename);
  }
  public function setContents($contents) {
    $this->contents = $contents;
    $this->hash = sha1($this->contents);
  }
  /**
   * @param string $password for decryption (optional)
   * @return string contents
   */
  public function readContents($password = null) {
    $contents = file_get_contents($this->getCompleteFilename());
    if ($password)
      $contents = MyCrypt::decrypt($contents, $password);
    $this->setContents($contents);
    return $contents;
  }
  /**
   * @param string $contents of file
   * @param string $password for encryption (optional)
   * @return GroupFile
   */
  public function save($contents, $password = null) {
    if ($password) 
      $contents = MyCrypt::encrypt($contents, $password);
    $this->setContents($contents);
    file_put_contents($this->getCompleteFilename(), $this->contents);
    return $this;
  }
  public function download($mime = null, $password = null) {
    $mime = ($mime) ? $mime : GroupFolder::getMime($this->filename);
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: $mime");
    header('Content-Disposition: attachment; filename="' . $this->filename . '"');
    $contents = static::readContents($password);
    echo $contents; 
    //readfile($this->getCompleteFilename(), true);
  }
  public function output($mime = null, $password = null, $contents = null) {
    $mime = ($mime) ? $mime : GroupFolder::getMime($this->filename);
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: $mime");
    //readfile($this->getCompleteFilename(), true);
    if ($contents == null)
      $contents = static::readContents($password);
    echo $contents;
  }
  public function delete() {
    unlink($this->getCompleteFilename());
  }
  //
  /**
   * @param GroupFolder $folder
   * @param string $filename 'file.xml'
   * @return GroupFile
   */
  static function from($folder, $filename) {
    $me = new static();
    $me->folder = $folder;
    $me->filename = $filename;
    return $me;
  }
}
/**
 * GroupImageFile 
 * Specal output handling for image uploads
 */
class GroupImageFile extends GroupFile {
  //
  public function output($mime = null, $rotation = 0, $maxh = 0, $maxw = 0, $contents = null) {
    $mime = ($mime) ? $mime : GroupFolder::getMime($this->filename);
    if ($mime == GroupFolder::MIME_PDF) 
      return parent::output($mime, null, $contents);
    if ($contents == null)
      $contents = $this->readContents();
    $image = $this->asImage($contents, $rotation, $maxh, $maxw);
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: $mime");
    if ($image == null)
      return parent::output($mime);
    $this->outputImage($image, $mime);
  }
  public function asImage($contents, $rot, $mh, $mw) {
    $image = null;
    ini_set("gd.jpeg_ignore_warning", 1);
    $image = imagecreatefromstring($contents);
    if ($image) {
      if ($rot) 
        $image = imagerotate($image, $rot, 0);
      if ($mh == 0 && $mw == 0)
        return $image;
      $cw = imagesx($image);
      $ch = imagesy($image);
      if ($mh == 0)
        $mh = $ch;
      if ($mw == 0)
        $mw = $cw;
      if ($cw <= $mw && $ch <= $mh)
        return $image;
      $h = $ch;
      $w = $cw;
      if ($w > $mw) {
        $r = $mw / $w;
        $w = $mw;
        $h = $h * $r;
      }
      if ($h > $mh) {
        $r = $mh / $h;
        $h = $mh;
        $w = $w * $r;
      }
      $resized = imagecreatetruecolor($w, $h);
      imagecopyresampled($resized, $image, 0, 0, 0, 0, $w, $h, $cw, $ch);
      return $resized;
    }
  }
  /*
  public function asImage($mime, $rot, $mh, $mw) {
    $image = null;
    ini_set("gd.jpeg_ignore_warning", 1);
    switch ($mime) {
      case 'image/gif':
        $image = imagecreatefromgif($this->getCompleteFilename());
        break;
      case 'image/jpeg':
        $image = imagecreatefromjpeg($this->getCompleteFilename());
        break;
      case 'image/bmp':
        $image = imagecreatefromwbmp($this->getCompleteFilename());
        break;
      case 'image/png':
        $image = imagecreatefrompng($this->getCompleteFilename());
        break;
    }
    if ($image) {
      if ($rot) 
        $image = imagerotate($image, $rot, 0);
      if ($mh == 0 && $mw == 0)
        return $image;
      $cw = imagesx($image);
      $ch = imagesy($image);
      if ($mh == 0)
        $mh = $ch;
      if ($mw == 0)
        $mw = $cw;
      if ($cw <= $mw && $ch <= $mh)
        return $image;
      $h = $ch;
      $w = $cw;
      if ($w > $mw) {
        $r = $mw / $w;
        $w = $mw;
        $h = $h * $r;
      }
      if ($h > $mh) {
        $r = $mh / $h;
        $h = $mh;
        $w = $w * $r;
      }
      $resized = imagecreatetruecolor($w, $h);
      imagecopyresampled($resized, $image, 0, 0, 0, 0, $w, $h, $cw, $ch);
      return $resized;
    }
  }
  */
  public function outputImage($image, $mime) {
    switch ($mime) {
      case 'image/gif':
        imagegif($image);
      case 'image/jpg':
      case 'image/jpeg':
      case 'image/pjpeg':
        imagejpeg($image);
      case 'image/bmp':
      case 'image/x-ms-bmp':
      case 'image/x-bmp':
        imagewbmp($image);
      case 'image/png':
        imagepng($image);
    }
  }
}
/**
 * GroupUpload
 */
class GroupUpload extends Rec {
  //
  public $name;     // 'original.jpg'
  public $type;     // 'image/jpeg'
  public $tmpName;  // 'C:\Windows\temp\phpE74.tmp'
  public $error;    // 0
  public $size;     // 23308
  //
  const M = 1000000;
  const K = 1000;
  //
  static $TYPE_IMAGES = array(
    'image/jpeg',
    'image/pjpeg',
    'image/bmp',
    'image/x-windows-bmp',
    'image/gif',
    'image/x-png',
    'image/png');
  //
  public function __construct() {
    $args = func_get_args(); 
    call_user_func_array(array('Rec', '__construct'), $args);
    $this->validate();
  }
  public function validate() {
    if ($this->error)
      $this->throwFromError();
    $types = static::getValidTypes();
    if ($types) {
      if (! in_array($this->type, $types))
        throw new GroupUploadException($this, static::getInvalidTypeMessage());
      if (! in_array(GroupFolder::getMime($this->name), $types))
        throw new GroupUploadException($this, "File extension is not of an allowable type.");
    }
    $max = static::getMaxSize();
    if ($max)
      if ($this->size > $max)
        throw new GroupUploadException($this, "$this->name is too large; files must be less than " . static::formatSize($max) . " in size.");      
  }
  private function throwFromError() {
    switch ($this->error) {
      case '1':
      case '2':
        throw new GroupUploadException($this, "$this->name: file size exceeds limit.");
      default:
        throw new GroupUploadException($this, "Unable to upload file at this time. Error: $this->error");
    }
  }
  /**
   * @return array(GroupUpload,..)
   */
  static function asUploads() {
    $uploads = static::fromHttpPostFile(current($_FILES));
    if (empty($uploads)) 
      throw new GroupUploadException(null, 'Please select a file for upload.');
    return $uploads;      
  }
  /**
   * @return GroupUpload
   */
  static function asUpload() {
    $recs = static::asUploads();
    return current($recs);
  }
  //
  protected static function getValidTypes() {
    // return array('image/jpeg',..)
  }
  protected static function getMaxSize() {
    return static::M;  // default 1M
  }
  protected static function getInvalidTypeMessage() {
    return 'File is not of an allowable type.';
  }
  //
  private static function fromHttpPostFile($f) {
    $recs = array();
    arrayifyEach($f);
    for ($i = 0, $j = count($f['name']); $i < $j; $i++) {
      if ($f['name'][$i]) {
        $name = '0_' . str_replace(' ', '_', $f['name'][$i]);  // to ensure names do not begin with dash or contain spaces or commas, which messes with bat commands
        $name = str_replace(';', '_', $name);  
        $name = str_replace(',', '_', $name);  
        $recs[] = new static($name, $f['type'][$i], $f['tmp_name'][$i], $f['error'][$i], $f['size'][$i]);
      }
    }
    return $recs;
  }
  private static function formatSize($i) {
    if ($i < static::M) 
      return round($i / static::K, 2) . 'K';
    else
      return round($i / static::M, 2) . 'M';
  }
}
class GroupUpload_Image extends GroupUpload {
  //
  public $name;     // 'original.jpg'
  public $type;     // 'image/jpeg'
  public $tmpName;  // 'C:\Windows\temp\phpE74.tmp'
  public $error;    // 0
  public $size;     // 23308
  //
  public $width;
  public $height;
  public $mime;     // 'image/jpeg'
  //
  public static function asUpload($prefix = null) {
    //
    $me = parent::asUpload();
    if ($prefix)
      $me->name = $prefix . '-' . $me->name;
    $info = getimagesize($me->tmpName);
    $me->width = $info[0];
    $me->height = $info[1];
    $me->mime = $info['mime'];
    return $me;
  } 
  //
  protected static function getValidTypes() {
    return self::$TYPE_IMAGES;
  }  
  protected static function getInvalidTypeMessage() {
    return 'File is not of an allowable type. Only image files (e.g. JPEG, GIF, PGN) may be used.';
  }
  protected static function getMaxSize() {
    return 2 * static::M;
  }
}
class GroupUpload_Xml extends GroupUpload {
  //
  protected static function getValidTypes() {
    return array('text/xml');
  }  
}
//
class GroupUploadException extends DisplayableException {
  public $uploadFile;
  public function __construct($uploadFile, $message) {
    $this->upload = $uploadFile;
    $this->message = $message; 
  }
}
class AutoEncryptFile extends File implements AutoEncrypt {
  //
  static function fromGroupFile(/*GroupFile*/$gf, $mime = null) {
    return static::from($gf->folder, $gf->filename, $mime);
  }
  static function from(/*GroupFolder*/$folder, $filename, $mime = null) {
    return static::create($folder->dir, $filename, $mime);
  }
  static function create($base, $filename, $mime = null) {
    $me = new static();
    $me->setFilename($filename, $base)->setMime($mime);
    $me->read();
    return $me;
  }
}