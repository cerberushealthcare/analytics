<?php
	error_reporting(E_ALL); ini_set('display_errors', '1');
require_once 'php/data/LoginSession.php';
require_once 'php/data/xml/_XmlRec.php';
require_once 'php/data/xml/clinical/ccr/ContinuityCareRecord.php';
require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
require_once 'php/data/xml/ccd/samples/ClinicalFile.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php
$xml = <<<eos
<person id='400112807'>
	<name>
		<first>Meg</first>
		<last>Hornsby</last>
	</name>
	<gender code='c'>F</gender>
	<spouse id='403191989'>Warren</spouse>
	<children>
		<child>
			<name>Lizzie</name>
		</child>
		<child>
			<name>Emma</name>
		</child>
	</children>
</person>
eos;
class Person extends XmlRec {
  public $_id;
  public $name = 'Name';
  public $gender;
  public $spouse = 'Spouse';
  public $children = 'Children';
}
class Name extends XmlRec {
  public $first;
  public $last;
}
class Spouse extends XmlRec {
  public $_id;
  public $_inner;
}
class Children extends XmlRec {
  public $child = 'Child[]';
}
class Child extends XmlRec {
  public $name;
}
switch ($_GET['t']) {
  case '1':
    $o = XmlRec::parse($xml);
    p_r($o);
    exit;
  case '2':
    $p = Person::fromXml($xml);
    p_r($p);
    $p->debug('person');
    exit;
  case '10':
    $xml = CcrFile::fetch();
    p_r($xml);
    exit;
  case '11':
    $xml = CcrFile::fetch();
    $ccr = ContinuityCareRecord::fromXml($xml);
    //p_r($ccr);
    //p_r($ccr->DateTime->asSqlDateTime());
    //p_r($ccr->Body->Alerts->get(), 'first');
    //p_r($ccr->Body->Medications);
    p_r($ccr->Body->Results);
    exit;
    $med = $ccr->Body->Medications->first('Medication');
    p_r($med);
    exit;
    p_r($ccr->Body->Alerts);
    exit;
    p_r($ccr->Body->Procedures);
    exit;
  case '12':
    $xml = CcrFile::fetch();
    $ccr = ContinuityCareRecord::fromXml($xml);
    p_r($ccr->getPatient());
    exit;
  case '13':
    $rec = Diagnosis_Ci::create(3, 15818, '500.0', '2012-01-02', 'Festivus', true);
    p_r($rec);
    exit;
  case '20':
    $xml = CcrFile::fetch();
    $client = ClinicalImporter::fromCcr($xml);
    p_r($client);
    exit;
  case '21':
    $xml = CcrFile::fetch();
    $client = ClinicalImporter::fromCcr($xml, 15821/*Allison Allscripts*/);
    p_r($client);
    exit;
  case '30':
    $filename = '0_ccr.xml';
    $folder = GroupFolder_ClinicalImport::open();
    $file = $folder->getFile($filename);
    p_r($file);
    exit;
  case '31':
    $filename = '0_ccr.xml';
    $cid = 15821;
    $import = ClinicalImporter::import_asUpdate($filename, $cid);
    p_r($import);
    exit;
  case '41':
    $filename = '0_healthvault.xml';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    p_r($import);
    exit;
  case '42':
    $filename = '0_healthvault.xml';
    $import = ClinicalImporter::import_asUpdate($filename, 15824/*Hernandez, Jane*/);
    //p_r($import);
    exit;
  case '43':
    $filename = '0_CompleteProfile.xml';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    //p_r($import);
    exit;
  case '50':
    $filename = '0_ccd.xml';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    p_r($import);
    exit;
  case '51':
    $filename = '0_SampleCCDDocument.xml';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    p_r($import);
    exit;
  case '60':
    $filename = '0_ccr2.xml';
    $import = ClinicalImporter::import_asUpdate($filename, 15827);
    p_r($import);
    exit;
  case '61':
    $filename = '0_ccr.xml';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    p_r($import);
    exit;
    
  case '100':
    $filename = '0_CCDA_Ambulatory.xml';
    $import = ClinicalImporter::import_asUpdate($filename, 4037);
    //p_r($import->Client);
    exit;
  case '101':
    $filename = '0_CCR_SAMPLE1.XML';
    $import = ClinicalImporter::import_asUpdate($filename, null);
    //
    exit;
}
?>
</html>
