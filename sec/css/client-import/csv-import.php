<?php
set_include_path('../');
require_once "inc/requireLogin.php";
require_once 'CsvImport.php';

/*
 * CSV Import Page
 */
if (! $myLogin->admin) {
  header("Location: welcome.php");
}
$filename = "etta.csv";
$ugid = 1775;
$start = '';
if (isset($_GET['a'])) {
  $start = $_GET['s'];
  $csv = new CsvImport($filename, $ugid);
  echo 'Rows imported: ' . count($csv->rows) . "<br><br>";
  foreach ($csv->rows as &$r) {
    $last = trim("{$r['Last Name']} {$r['Suffix']}");
    $first = trim("{$r['First Name']} {$r['Middle Name']}");
    $birth = date("Y-m-d", strtotime($r['Date of Birth']));
    $csv->addClient(
        $r['Patient Identifier'],
        $last,
        $first,
        $r['Gender'],
        $birth,
        $r['Comments'],
        $r['Address Line 1'],
        $r['Address Line 2'],
        $r['City'],
        $r['State'],
        $r['Zip'],
        $r['Home Phone'],
        $r['Work Phone'],
        $r['Mobile Phone'],
        $r['Email']);
  }
  $start = $csv->writeClients($start);
  if ($start == null) {
    echo '<br><br>** ALL DONE **';
    exit;
  }
}
echo <<<END
<br><br>
<form method='get' action='csv-import.php'>
  <input type='hidden' name='a' value='1'>
  Ready to import $filename for ugid=$ugid<br><br>
  Start after UID: <input type='text' name='s' value='$start'>
  <input type='submit' value='Begin'>
</form>  
END;
?>