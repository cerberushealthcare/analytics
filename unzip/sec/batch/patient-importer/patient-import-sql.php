<?php
set_include_path('../../');
/**
 * Batch import SQL builder
 */
$args = arguments($argv);
$folder = $args[0];
$class = $args[1];
echo "BUILD IMPORT SQL\n";
echo "Folder: $folder\n";
echo "Class: $class\n";
require_once "php/data/csv/client-import/$folder/$class.php";
$file = new $class();
echo "Loading...\n";
$file->load();
echo count($file->recs) . " record(s) loaded.\n";
echo "Building SQL statements...\n";
$lines = $file->getSqlStatements();
$filename = "out/" . $folder . ".sql";
$fp = @fopen($filename, "w");
foreach ($lines as $line) 
  fwrite($fp, $line . "\n");
fclose($fp);
echo count($lines) . " line(s) written to $filename.";
//
function arguments($argv){
  array_shift($argv);
  $out = array();
  foreach ($argv as $arg){
    if (substr($arg,0,2) == '--'){
      $eqPos = strpos($arg,'=');
      if ($eqPos === false){
        $key = substr($arg,2);
        $out[$key] = isset($out[$key]) ? $out[$key] : true;
      } else {
        $key = substr($arg,2,$eqPos-2);
        $out[$key] = substr($arg,$eqPos+1);
      }
    } else if (substr($arg,0,1) == '-'){
      if (substr($arg,2,1) == '='){
        $key = substr($arg,1,1);
        $out[$key] = substr($arg,3);
      } else {
        $chars = str_split(substr($arg,1));
        foreach ($chars as $char){
          $key = $char;
          $out[$key] = isset($out[$key]) ? $out[$key] : true;
        }
      }
    } else {
      $out[] = $arg;
    }
  }
  return $out;
}
?>