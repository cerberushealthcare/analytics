<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', '1');
require_once 'php/dao/_util.php';
require_once 'php/data/rec/sql/Templates_AdminSearch.php';
require_once 'php/data/rec/sql/Users_Admin.php';
require_once 'php/data/rec/sql/UserLoginReqs.php';
require_once 'php/data/rec/sql/UserLogins.php';
require_once 'php/data/rec/sql/UserStub.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/Procedures_Hm.php';
require_once 'php/data/rec/sql/Providers.php';
require_once 'php/c/reporting/Reporting.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/sql/LookupScheduling.php';
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/IProcCodes_Admin.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/data/rec/sql/IcdCodes.php';
require_once 'php/data/rec/sql/DrugClasses_Admin.php';
require_once 'php/data/rec/sql/PortalUsers.php';
require_once 'php/data/rec/sql/Templates_Map.php';
require_once 'php/data/rec/sql/LookupTemplates.php';
require_once 'php/data/rec/sql/LookupTemplates.php';
require_once 'php/data/rec/sql/Data_Medhx.php';
require_once 'php/data/rec/sql/UserRoles.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
require_once 'php/data/csv/report-download/ReportCsvFile.php';
require_once 'php/data/xml/ClinicalXmls.php';
require_once 'php/data/ftp/FtpFolder.php';
require_once 'php/data/rec/sql/cms/CmsReports.php';
require_once 'php/data/LoginSession.php';
require_once 'php/data/xml/pqri/PQRI.php';
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Facesheets.php';
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'php/data/rec/sql/Templates_IolEntry.php';
require_once 'php/data/rec/sql/Immuns.php';
require_once 'php/data/http/IpLookup.php';
require_once 'php/data/rec/cryptastic.php';
require_once 'php/data/rec/sql/Dashboard.php';
require_once 'php/data/rec/sql/UserManager.php';
require_once 'php/csys/alerts/Alerts.php';
LoginSession::verify_forServer();
//
?>
<html>
  <head>
    <script language="JavaScript1.2" src="js/ui.js"></script>
    <script language="JavaScript1.2" src="js/_ui/Templates.js"></script>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $text = 'renovascular hypertension';
    $text = 'abdominal pain';
    $text = 'edema';
    $text = 'coronary artery disease';
    $text = '384.1';
    $r = IcdCodes::search($text);
    print_r($r);
    break;
  case '2':
    $search = new SearchText('abdominal pain pain');
    print_r($search);
    $expr = $search->expr; 
    $pattern = "/" . $expr . "/i";
    $text = 'He has some serious abdominal pain going on with pain in his abdominal region.';
    //print_r($search->matchDistinct($text));
    print_r(jsondecode(jsonencode($search)));
    break;
  case '3':
    $user = UserLogin::fetch(46);
    print_r($user);
    if ($user->isPrimaryDoc()) 
      echo 'yep';
    break;
  case '4':
    $reqs = UserLoginReqs::getAllFor(1);
    print_r($reqs);
    break;
  case '5':
    $reqs = LoginReq::fetchAllActive();
    print_r($reqs);
    break;
  case '6':
    print_r(Messaging::getListsAsJson());
    break;
  case '7':
    print_r(Messaging::getMyUnreadCt());
    break;
  case '8':
    $dm = DataImmun::fetchForFacesheet('1656');
    $j = jsonencode($dm);
    print_r(jsondecode($j));
    break;
  case '9':
    $dm = DataImmun::fetchForFacesheet('1656');
    $d = $dm[0];
    print_r($d);
    $j = jsonencode($d);
    $j = jsondecode($j);
    print_r($j);
    $j->_dateGiven = 'Nov 1970';
    print_r($j);
    $d = DataImmun::fromJsonObject($j);
    print_r($d);
    break;
  case '10':
    $o = jsondecode('{"dataImmunId":1}');
    $d = new DataImmun($o);
    break;
  case '11':
    $c = Client::fetchWithDemo(1664);
    $j = jsonencode($c);
    print_r(jsondecode($j));
    break;
  case '12':
    $r = TrackItems::getOpen(1666);
    print_r($r);
    break;
  case '13':
    $s = '2010-11-23 16:00:01';
    $x = formatDateTime($s);
    print_r($x);
    print_r('<br>');
    $s = formatFromDateTime($x);
    print_r($s);
    print_r('<br>');
    $s = formatFromDateTime('2010-11-23');
    print_r($s);
    break;
  case '14':
    $u = new UserReport();
    $u->active = 1;
    $u->userType = 1;
    $u->subscription = 0;
    $users = SqlRec::fetchAllBy($u);
    foreach ($users as $user) {
      $user->AddressGroup = AddressReport::fetchByTable('G', $user->userGroupId, 0);
      $user->UsageYTD = UsageDetails::getUsageYTD($user->userId);
    }
    print_r($users);
    break;
  case '15':
    $user = UserReport::fetch($_GET['id']);
    $user->AddressGroup = AddressReport::fetchByTable('G', $user->userGroupId, 0);
    print_r($user);
    break;
  case '16':
    $reqs = UserLoginReqs::getAll();
    print_r($reqs);
    break;
  case '17':
    $o = '{"clientId":"1687","userGroupId":"1","uid":"234234","lastName":"Foot","firstName":"Big","sex":"F","birth":"09-Nov-1955","dateCreated":"2010-01-30 23:06:42","active":"1","notes":"ewrwer","dateUpdated":"2011-03-02 12:49:07","Address_Home":{"addressId":"4656","tableCode":"C","tableId":"1687","type":"0","addr1":"500 Main","city":"Lexington","state":"KY","zip":"40207","phone1":"502 999-1212","phone1Type":"1","csz":"Lexington, KY 40207"},"Address_Emergency":{"tableCode":"C","tableId":"1687","type":"2","csz":""},"Address_Spouse":{"tableCode":"C","tableId":"1687","type":"3","csz":""},"Address_Father":{"tableCode":"C","tableId":"1687","type":"6","csz":""},"Address_Mother":{"tableCode":"C","tableId":"1687","type":"5","csz":""},"Address_Rx":{"tableCode":"C","tableId":"1687","type":"4","csz":""},"ICards":[{"clientId":"1687","seq":"1"},{"clientId":"1687","seq":"2","planName":"23423"}],"age":55,"ageYears":55,"name":"Foot, Big","icard":{"clientId":"1687","seq":"1"},"icard2":{"clientId":"1687","seq":"2","planName":"23423"},"middleName":""}';
    $obj = jsondecode($o);
    $client = new Client($obj);
    print_r($client);
    break;
  case '18':
    $s = '19950102';
    print_r(formatDate($s));
    exit;
    $recs = Clients::search('Hornsby', 'W');
    print_r($recs);
    break;
  case '20':
    $meds = Meds::getActive('1674');
    print_r($meds);
    break;
  case '21':
    $meds = Meds::getHistory('1663');
    $meds = jsondecode(jsonencode($meds));
    print_r($meds);
    break;
  case '22':
    $recs = Users_Admin::searchUsersByName('hornsby');
    echo Users_Admin::asHtmlTable($recs);
    break;
  case '23':
    $recs = Users_Admin::getCreatedCounts();
    echo Users_Admin::asHtmlTable($recs);
    break;
  case '24':
    $recs = Users_Admin::getUsersByCreateDate('2010-10-15');
    echo Users_Admin::asHtmlTable($recs);
    break;
  case '25':
    $recs = Users_Admin::getLoginsByUser(1);
    echo Users_Admin::asHtmlTable($recs);
    break;
  case '26':
    $recs = Users_Admin::getLoginsByIp('192.168.1.1');
    echo Users_Admin::asHtmlTable($recs);
    break;
  case '27':
    $cid = 1666;
    $recs = Proc::fetchAll($cid);
    p_r($recs);
    exit;
  case '28':
    $cid = 1666;
    $recs = Proc::fetchAll2($cid);
    p_r($recs);
    exit;
  case '29':
    $recs = TemplateOrderEntry::getWithQuestions();
    print_r($recs);
    break;
  case '30':
    $sql = IProcCodes_Build::buildSqlScript();
    print_r($sql);
    break;
  case '31':
    $rec = IProcCodes::getByName('AV Repair');
    print_r($rec);
    $rec = IProcCodes::getByName('AAA Repair');
    print_r($rec);
    break;
  case '32': 
    $recs = Providers::getAll();
    print_r($recs);
    break;
  case '33':
    $recs = Reporting::patientsByIcd('786.');
    print_r($recs);
    break;
  case '34':
    $recs = Reporting::patientsByAge(2, 15);
    $recs = Reporting::patientsByAge(0, 1);
    print_r($recs);
    break;
  case '35':
    $recs = Reporting::patientsByMed('al');
    print_r($recs);
    break;
  case '36':
    $recs = Reporting::patientsByLocation(null, 'l');
    print_r($recs);
    break;
  case '37':
    $recs = Reporting::patientsByResults(null, null, 14);
    print_r($recs);
    break;
  case '38':
    $recs = Scanning::getIndexedToday();
    print_r($recs);
    $rec = $recs[0];
    $j = jsonencode($rec);
    print_r($j);
    $o = jsondecode($j);
    $o->areas = array(7, 8);
    print_r($o);
    $index = new ScanIndex($o);
    print_r($index);
    break;
  case '39':
    $recs = Scanning::getUnindexedFiles();
    p_r($recs);
    exit;
  case '40':
    $recs = UserGroups::getDocsJsonList();
    print_r($recs);
    print_r('<br>');
    $first = UserGroups::getFirstDoc();
    print_r($first);
    break;
  case '41':
    $rec = Clients::get('1666');
    //print_r($rec);
    $r = jsondecode(jsonencode($rec));
    print_r($r);
    break;
  case '42':
    //$recs = Documentation::getAll(1687);
    //$recs = jsonencode($recs);
    //print_r($recs);
    $rec = '{"type":4,"id":"84","date":"05-Aug-2011","timestamp":"2011-08-05 14:04:50","name":"Infectious Disease Referral","_type":"Order"}';
    $rec = jsondecode($rec);
    $rec = Documentation::preview($rec);
    p_r($rec);
    break;
  case '43':
    $rec = LookupRec::getJsonLists(LookupScheduling::getApptTypes(), LookupScheduling::getStatuses(), LookupScheduling::getProfileFor(1));
    $rec = jsondecode($rec);
    print_r($rec);
    break;
  case '44':
    $rec = LookupScheduling::getProfileFor(1);
    print_r($rec);
    break;
  case '45':
    $rec = QuestionIpc::fetch('23008');
    print_r($rec);
    break;
  case '46':
    $ids = array(686265, 686278);
    $qid = 23008;
    IprocCodes_Admin::copyToQuestion($ids, $qid);
    $rec = QuestionIpc::fetch('23008');
    print_r($rec);
    break;
  case '47':
    $pis = JsonDao::getJParInfosByPid(2302);
    print_r(jsondecode($pis));
    break;
  case '48':
    $date = '2010E';
    echo date("Y-m-d", strtotime($date));
    exit;
  case '49':
    //$recs = DrugClasses_Admin::getAll();
    //print_r($recs);
    $recs = DrugClasses::getSubclassJsonList(69);
    p_r($recs);
    exit;
  case '50':
    $recs = Reporting::test1();
    print_r($recs);
    exit;
  case '51':
    $recs = Reporting::test2();
    print_r($recs);
    exit;
  case '52':
    $recs = Reporting::test3();
    print_r($recs);
    exit;
  case '53':
    $recs = Reporting::test4();
    print_r($recs);
    exit;
  case '54':
    $rec = Reporting::test0();
    $rec = jsonencode($rec);
    print_r($rec);
    exit;
  case '55':
    $rec = '{"name":"Patient Report","Rec":{"uid":{"fid":"Patient ID"},"lastName":{"fid":"Last Name"},"firstName":{"fid":"First Name"},"sex":{"fid":"Sex"},"birth":{"fid":"Age"},"deceased":{"fid":"Deceased"},"race":{"fid":"Race"},"ethnicity":{"fid":"Ethnicity"},"language":{"fid":"Language"},"Joins":[{"jt":"1","table":"Allergies","Recs":[{"agent":{"fid":"Agent"},"active":{"fid":"Active"},"Joins":null,"_class":"Allergy_Rep"}]}],"_class":"Client_Rep"},"Joins":null}';
    $rec = jsondecode($rec);
    Reporting::generate($rec);
    exit;
  case '56':
    $report = '{"reportId":"20","name":"A Test Report","comment":"This is a test","Rec":{"uid":null,"lastName":null,"firstName":null,"sex":{"op":"30","value":"M","text_":"Male"},"birth":null,"deceased":{"text_":"Yes"},"race":{"text_":"American Native\/Alaskan Native"},"ethnicity":{"text_":"Hispanic Origin"},"language":null,"Joins":[{"jt":"1","table":"5","Recs":[{"ipc":{"op":"30","value":"842651","text_":"AAA Repair"},"date":null,"cat":null,"providerId":null,"addrFacility":null,"location":null,"Joins":null,"table_":"5","pid_":null}]}],"table_":"0","pid_":null},"Joins":null,"_tableName":"Patients"}';
    $recs = Reporting::generate(jsondecode($report));
    print_r($recs);
    exit;
  case '60':
    $recs = PortalUsers::getAll();
    print_r($recs);
    exit;
  case '61':
    $rec = '{"clientId":"1666","uid":"bb","pw":"bb","cq1":"What is your name?","ca1":"bb","cq2":"What is your quest?","ca2":"holy grail","cq3":"What is your favorite color?","ca3":"blue"}';
    $rec = PortalUsers::create(jsondecode($rec));
    print_r($rec);
    exit;
  case '70':
    $rec = IProcCodes::getEmptyCriteria();
    p_r($rec);
    p_r(jsonencode($rec));
    exit;
  case '71':
    $rc = IpcHm::getStaticJson();
    p_r($rc);
    exit;
  case '72':
    $rc = IpcHm::fetchTopLevel(710127, 1);
    p_r($rc);
    $crit = $rc->getRepCrit_asNumerator();
    $recs = $crit->fetchAll(1);
    $crit = $rc->getRepCrit_asDenominator();
    $recs = $crit->fetchAll(1);
    p_r($recs, 'denom');
    exit;
  case '73':
    $rc = IpcHm::fetchTopLevel(710127, 1);
    p_r($rc);
    $crit = $rc->getRepCrit_asNumerator();
    p_r($crit);
    $recs = $crit->fetchAll(1);
    print_r($recs);
    exit;
  case '74':
    $rc = IpcHm::fetch(710127, 1);
    print_r($rc);
    $crit = $rc->getRepCrit_asDenominator();  // pooh bear
    print_r($crit);
    $recs = $crit->fetchAll(1);
    print_r($recs);
    exit;
  case '75':
    require_once 'php/data/rec/sql/Procedures_Hm.php';
    $rc = IpcHm::fetch(1, 710127);
    print_r($rc);
    $applicable = $rc->isApplicable(1, 1706);  // buddy holly
    p_r('applicable=' . $applicable);
    exit;
  case '76':
    require_once 'php/data/rec/sql/Procedures_Hm.php';
    $recs = Procedures_Hm::getForClient(1658);
    print_r($recs);
    exit;
  case '80':
    p_();
    $t = Templates_Map::get(1, '2010-01-01 01:01:01');
    p_();
    //$t = jsonencode($t);
    p_r($t);
    exit;
  case '81':
    $t = JsonDao::oldbuildJDefaultMap(1);
    p_r($t);
    exit;
  case '82':
    $rec = LookupTemplates::get(1);
    p_r($rec);
    exit;
  case '83':
    $a = new stdClass();
    $a->fred = array('dum'=>12);
    $b = array();
    $b['fred'] = 'barney';
    p_r(getr_($a, 'fred.dum'), 'a');
    p_r(get_($b, 'fred2'), 'b');
    exit;
  case '84':
    $report = Reporting::generate(Reporting::getReport(37));
    $file = ReportCsvFile::from($report, $report->recs);
    $file->download();
    exit;
  case '90':
    $clients = IpcHm::fetchAllApplicableClients(918211, 1);  // mammogram
    $recs = jsonencode($clients);
    p_r(jsondecode($recs));
    exit;
  case '91':
    //$clients = IpcHm::fetchAllDueNowClients(918211, 1);  // mmammogram
    $clients = IpcHm::fetchAllDueNowClients(918089, 1);  // colonoscopy
    p_r($clients);
    exit;
  case '92':
    //$recs = IpcHm::fetchTopLevels(1, 3027);
    $recs = IpcHm::fetchClientLevels(1);
    p_r($recs);
    exit;
  case '93':
    $recs = IpcHm::fetchAllDueNowClients(842655, 1);
    p_r($recs);
    exit;
  case '94':
    $ipcs = IpcHm::fetchAllIpcs(1);
    p_r($ipcs);
    exit;
  case '95':
    $recs = Procedures_Hm::getAllDueNow(1);
    p_r($recs);
    exit;
  case '99':
    $recs = Procedures_Hm::getForClient(1667);
    p_r($recs);
    exit;
  case '100':
    $folder = UserFolder::open(1);
    exit;
  case '101':
    global $myLogin;
    $myLogin->toRecordCache('123', 'test');
  case '102':
    $cache = $myLogin->getRecordCache();
    p_r($cache);
    exit;
  case '103':
    $cache = $myLogin->getRecordCache();
    $cache->set('fred', 'barney');
    global $myLogin;
    $myLogin->save();
    p_r($cache);
    exit;
  case '104':
    p_r($myLogin->cache);
    SessionCache::set('fred', 'barney');
    p_r($myLogin->cache);
    break;
  case '105':
    p_r($myLogin->cache);
    p_r(Sessions::getTemplateJsonList(12));
    p_r($myLogin->cache);
    break;
  case '106':
    p_();
    for ($i = 0; $i < 60000; $i++) {
      LoginDao::authenticateUserGroupIdWithin('clients', 'client_id', 3027);
    }
    p_();
    exit;
  case '110':
    IcdCodes::search('obstructive pulmonary disease');
    exit;
  case '111':
    IcdCodes::search('coronary artery');
    exit;
  case '120':
    $rec = Medhx_Summary::fetch(1666);
    p_r($rec);
    exit;
  case '121':
    $rec = Medhx_Rec::fetchAll(1666);
    p_r($rec);
    exit;
  case '122':
    $recs = Data_Medhx::getAll(1666);
    p_r($recs);
    exit;
  case '130':
    $recs = IpcHm::fetchTopLevel(600010, 1);
    p_r($recs);
    exit;
  case '131':
    $recs = IpcHm::fetchAllApplicableClients(600010, 1);
    p_r($recs);
    exit;
  case '132':
    $recs = IpcHm_Client::fetchAll(1, 1687);  // big foot
    p_r($recs);
    exit;
  case '133':
    $recs = Procedures_Hm::getForClient(1687);  
    p_r($recs);
    exit;
  case '134':
    $recs = Procedures_Hm::getForClient(1272);  // pooh
    p_r($recs);
    exit;
  case '135':
    $recs = Procedures_Hm::getForClient(1666);  // buff
    p_r(jsondecode(jsonencode($recs)));
    exit;
  case '136':
    Procedures_Admin::saveSmokingHxRecorded(1666);
    exit;
  case '140':
    $role = PrimaryRole::asProvider('Admin');
    p_r($role);
    exit;
  case '141':
    $text= <<<eos
MSH|^~\&||Amazing Clinical^24D0404292^CLIA|MIDOH|MI|201107280941||ORU^R01|20080320031629921238|P|2.3.1
PID|||246^^^^^Columbia Valley Memorial Hospital&01D0355944&CLIA~95101100001^^^^^MediLabCo-Seattle&45D0470381&CLIA||sara^blaunt^Q^Jr|Clemmons|19900602|F||W|2166WellsDr^AptB^Seattle^WA^98109||^^^^^206^6793240|||M|||423523049||U|N
PV1|1||||||9999^Account^Test^^^^^PHYID||||||||U||||||||||||||||||||||||
ORC|RE||||P
OBR|1||SER122145|^^^78334^Chemistry, serum sodium^meq/l|||201107280941||||||||BLDV|^Welby^M^J^Jr^Dr^MD|^WPN^PH^^^206^4884144||||||||F
OBX|1|NM|2951-2^SODIUM,SERUM^LN^230-007^Na&CLIA|1|141|meq/l|135-146||||F|||||^Doe^John|||||||Oakton Crest Laboratories|5570 Eden Street^^Oakland^California^94607|8006315250|
OBR|2||SER122145|^^^27760-3^POTASSIUM,SERUM^LN^230-006^K^CLIA|1|4.5|meq/l|3.4-5.3|||201107280941
OBX|2|NM|22760-3^POTASSIUM,SERUM^LN^230-006^K^CLIA|1|4.5|meq/l|3.4-5.3|N|||F|||||^Doe^John|||||||Oakton Crest Laboratories|5570 Eden Street^^Oakland^California^94607|8006315250|||201107280941
eos;
    $pass = 'hogjaws';
    $c = new cryptastic();
    $key = getEncryptKey($c, $pass, true);
    $encrypt = $c->encrypt($text, $key);
    p_r($encrypt);
    $c = new cryptastic();
    $key = getEncryptKey($c, $pass, true);
    $decrypt = $c->decrypt($encrypt, $key);
    p_r($decrypt);
    exit;
  case '150':
    $xml= <<<eos
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd">
	<realmCode code="US"/>
	<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
	<templateId root="2.16.840.1.113883.3.27.1776" assigningAuthorityName="CDA/R2"/>
	<templateId root="2.16.840.1.113883.10.20.3" assigningAuthorityName="HL7/CDT Header"/>
	<templateId root="1.3.6.1.4.1.19376.1.5.3.1.1.1" assigningAuthorityName="IHE/PCC"/>
	<templateId root="2.16.840.1.113883.3.88.11.32.1" assigningAuthorityName="HITSP/C32"/>
	<id root="2.16.840.1.113883.3.72" extension="MU_Rev2_HITSP_C32C83_4Sections_MeaningfulEntryContent_NoErrors" assigningAuthorityName="NIST Healthcare Project"/>
	<code code="34133-9" displayName="Summarization of episode note" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
	<title/>
	<effectiveTime value="20101026130945"/>
	<confidentialityCode/>
	<languageCode code="en-US"/>
	<recordTarget>
		<patientRole>
			<id root="ProviderID" extension="PatientID" assigningAuthorityName="Provider Name"/>
			<addr use="HP">
				<!--HITSP/C83 recommends that a patient have at least one address element with a use attribute of HP, i.e. home primary-->
				<streetAddressLine>123 Noname Street</streetAddressLine>
				<streetAddressLine>Suite 500</streetAddressLine>
				<city>Denver</city>
				<state>CO</state>
				<postalCode>80202</postalCode>
				<country>USA</country>
			</addr>
			<telecom/>
			<patient>
				<name>
					<given>Gage</given>
					<given>P</given>
					<family>Phineas</family>
				</name>
				<!--HITSP/C83 requires patient administrative gender, e.g. M, F, I (indeterminate)-->
				<administrativeGenderCode code="M" displayName="Male" codeSystem="2.16.840.1.113883.5.1" codeSystemName="HL7 AdministrativeGender"/>
				<birthTime value="18230709"/>
				<!--HITSP/C83 requires patient marital status - if known, e.g. S, M, D-->
				<maritalStatusCode code="S" displayName="Single" codeSystem="2.16.840.1.113883.5.2" codeSystemName="HL7 Marital status"/>
				<!--HITSP/C32 requires patient languages spoken - if known.-->
				<languageCommunication>
					<templateId root="2.16.840.1.113883.3.88.11.83.2" assigningAuthorityName="HITSP/C83"/>
					<templateId root="1.3.6.1.4.1.19376.1.5.3.1.2.1" assigningAuthorityName="IHE/PCC"/>
					<languageCode code="en-US"/>
				</languageCommunication>
			</patient>
		</patientRole>
	</recordTarget>
	<author>
		<time value="20101026145730"/>
		<assignedAuthor>
			<id/>
			<addr/>
			<telecom/>
			<assignedPerson>
				<name>Staff</name>
			</assignedPerson>
			<representedOrganization>
				<name>Sean</name>
				<telecom/>
				<addr/>
			</representedOrganization>
		</assignedAuthor>
	</author>
	<custodian>
		<assignedCustodian>
			<representedCustodianOrganization>
				<id/>
				<name/>
				<telecom/>
				<addr/>
			</representedCustodianOrganization>
		</assignedCustodian>
	</custodian>
	<!--HITSP/C32 requires one or more support modules (i.e. participant) - if known; if not known, the participant element(s) may be removed in their entirety. However, many medical facilities require recording of the Next-of-Kin (NOK). The following shows how to represent that information in the participant element.-->
	<participant typeCode="IND">
		<templateId root="2.16.840.1.113883.3.88.11.83.3" assigningAuthorityName="HITSP/C83"/>
		<templateId root="1.3.6.1.4.1.19376.1.5.3.1.2.4" assigningAuthorityName="IHE/PCC"/>
		<time value="2010"/>
		<associatedEntity classCode="NOK">
			<code code="MTH" displayName="Mother" codeSystem="2.16.840.1.113883.5.111" codeSystemName="HL7 RoleCode (Personal Relationship subset)"/>
			<addr/>
			<telecom/>
			<associatedPerson>
				<name>Shimea Phineas</name>
			</associatedPerson>
		</associatedEntity>
	</participant>
	<component>
		<structuredBody>
			<component>
				<!--Allergies-->
				<section>
					<templateId root="2.16.840.1.113883.3.88.11.83.102" assigningAuthorityName="HITSP/C83"/>
					<templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.13" assigningAuthorityName="IHE PCC"/>
					<templateId root="2.16.840.1.113883.10.20.1.2" assigningAuthorityName="HL7 CCD"/>
					<!--Allergies/Reactions section template-->
					<code code="48765-2" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Allergies"/>
					<title>Allergies and Adverse Reactions</title>
					<text>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>Type</th>
									<th>Substance</th>
									<th>Reaction</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr ID="ALGSUMMARY_1">
									<td ID="ALGTYPE_1">Drug Allergy</td>
									<td ID="ALGSUB_1">Penicillin</td>
									<td ID="ALGREACT_1">Hives</td>
									<td ID="ALGSTATUS_1">Active</td>
								</tr>
								<tr ID="ALGSUMMARY_2">
									<td ID="ALGTYPE_2">Drug Intolerance</td>
									<td ID="ALGSUB_2">Aspirin</td>
									<td ID="ALGREACT_2">Wheezing</td>
									<td ID="ALGSTATUS_2">Active</td>
								</tr>
								<tr ID="ALGSUMMARY_3">
									<td ID="ALGTYPE_3">Drug Intolerance</td>
									<td ID="ALGSUB_3">Codeine</td>
									<td ID="ALGREACT_3">Nausea</td>
									<td ID="ALGSTATUS_3">Active</td>
								</tr>
							</tbody>
						</table>
					</text>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.6" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.3" assigningAuthorityName="IHE PCC"/>
							<!--Allergy act template -->
							<id root="36e3e930-7b14-11db-9fe1-0800200c9a66"/>
							<code nullFlavor="NA"/>
							<statusCode code="completed"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
								<high nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.18" assigningAuthorityName="CCD"/>
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.6" assigningAuthorityName="IHE PCC"/>
									<templateId root="2.16.840.1.113883.10.20.1.18"/>
									<!--Allergy observation template. NOTE that the HITSP/C83 requirement for code (i.e. allergy type) differs from the IHE PCC recommendation for code.-->
									<id root="4adc1020-7b14-11db-9fe1-0800200c9a66"/>
									<code code="416098002" codeSystem="2.16.840.1.113883.6.96" displayName="drug allergy" codeSystemName="SNOMED CT"/>
									<text>
										<reference value="#ALGSUMMARY_1"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low nullFlavor="UNK"/>
									</effectiveTime>
									<!--Note that IHE/PCC and HITSP/C32 differ in how to represent the drug, substance, or food that one is allergic to. IHE/PCC expects to see that information in <value> and HITSP/C32 expects to see it in <participant>.-->
									<value xsi:type="CD" code="70618" codeSystem="2.16.840.1.113883.6.88" displayName="Penicillin" codeSystemName="RxNorm">
										<originalText>
											<reference value="#ALGSUB_1"/>
										</originalText>
									</value>
									<participant typeCode="CSM">
										<participantRole classCode="MANU">
											<addr/>
											<telecom/>
											<playingEntity classCode="MMAT">
												<code code="70618" codeSystem="2.16.840.1.113883.6.88" displayName="Penicillin" codeSystemName="RxNorm">
													<originalText>
														<reference value="#ALGSUB_1"/>
													</originalText>
												</code>
												<name>Penicillin</name>
											</playingEntity>
										</participantRole>
									</participant>
								</observation>
							</entryRelationship>
						</act>
					</entry>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.6" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.3" assigningAuthorityName="IHE PCC"/>
							<!--Allergy act template -->
							<id root="eb936010-7b17-11db-9fe1-0800200c9a66"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.18" assigningAuthorityName="CCD"/>
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.6" assigningAuthorityName="IHE PCC"/>
									<!--Allergy observation template. NOTE that the HITSP/C83 requirement for code (i.e. allergy type) differs from the IHE PCC recommendation for code.-->
									<id root="eb936011-7b17-11db-9fe1-0800200c9a66"/>
									<code displayName="propensity to adverse reactions to drug" code="419511003" codeSystemName="SNOMED CT" codeSystem="2.16.840.1.113883.6.96"/>
									<text>
										<reference value="#ALGSUMMARY_2"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low nullFlavor="UNK"/>
									</effectiveTime>
									<!--Note that IHE/PCC and HITSP/C32 differ in how to represent the brug, substance, or food that one is allergic to. IHE/PCC expects to see that information in <value> and HITSP/C32 expects to see it in <participant>.-->
									<value xsi:type="CD" code="1191" codeSystem="2.16.840.1.113883.6.88" displayName="Aspirin" codeSystemName="RxNorm">
										<originalText>
											<reference value="#ALGSUB_2"/>
										</originalText>
									</value>
									<participant typeCode="CSM">
										<participantRole classCode="MANU">
											<addr/>
											<telecom/>
											<playingEntity classCode="MMAT">
												<code code="1191" codeSystem="2.16.840.1.113883.6.88" displayName="Aspirin" codeSystemName="RxNorm">
													<originalText>
														<reference value="#ALGSUB_2"/>
													</originalText>
												</code>
												<name>Aspirin</name>
											</playingEntity>
										</participantRole>
									</participant>
								</observation>
							</entryRelationship>
						</act>
					</entry>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.6" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.3" assigningAuthorityName="IHE PCC"/>
							<!--Allergy act template -->
							<id root="c3df3b61-7b18-11db-9fe1-0800200c9a66"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.18"/>
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.6" assigningAuthorityName="IHE PCC"/>
									<!--Allergy observation template. NOTE that the HITSP/C83 requirement for code (i.e. allergy or adverse reaction type) differs from the IHE PCC recommendation for code.-->
									<id root="c3df3b60-7b18-11db-9fe1-0800200c9a66"/>
									<code code="59037007" codeSystem="2.16.840.1.113883.6.96" displayName="drug intolerance" codeSystemName="SNOMED CT"/>
									<text>
										<reference value="#ALGSUMMARY_3"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low value="200512"/>
										<high value="200601"/>
									</effectiveTime>
									<!--Note that IHE/PCC and HITSP/C32 differ in how to represent the drug, substance, or food that one is allergic to. IHE/PCC expects to see that information in <value> and HITSP/C32 expects to see it in <participant>.-->
									<value xsi:type="CD" code="2670" codeSystem="2.16.840.1.113883.6.88" displayName="Codeine" codeSystemName="RxNorm">
										<originalText>
											<reference value="#ALGSUB_3"/>
										</originalText>
									</value>
									<participant typeCode="CSM">
										<participantRole classCode="MANU">
											<addr/>
											<telecom/>
											<playingEntity classCode="MMAT">
												<code code="2670" codeSystem="2.16.840.1.113883.6.88" displayName="Codeine" codeSystemName="RxNorm">
													<originalText>
														<reference value="#ALGSUB_3"/>
													</originalText>
												</code>
												<name>Codeine</name>
											</playingEntity>
										</participantRole>
									</participant>
								</observation>
							</entryRelationship>
						</act>
					</entry>
				</section>
			</component>
			<component>
				<!--Problems-->
				<section>
					<templateId root="2.16.840.1.113883.3.88.11.83.103" assigningAuthorityName="HITSP/C83"/>
					<templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.6" assigningAuthorityName="IHE PCC"/>
					<templateId root="2.16.840.1.113883.10.20.1.11" assigningAuthorityName="HL7 CCD"/>
					<!--Problems section template-->
					<code code="11450-4" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Problem list"/>
					<title>Problems</title>
					<text>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>Problem</th>
									<th>Effective Dates</th>
									<th>Problem Status</th>
								</tr>
							</thead>
							<tbody>
								<tr ID="PROBSUMMARY_1">
									<td ID="PROBKIND_1">Diabetes Mellitus, Type 2</td>
									<td>Jan 1990</td>
									<td ID="PROBSTATUS_1">Active</td>
								</tr>
								<tr ID="PROBSUMMARY_2">
									<td ID="PROBKIND_2">Hyperlipidemia</td>
									<td>Jan 1997</td>
									<td ID="PROBSTATUS_2">Resolved</td>
								</tr>
								<tr ID="PROBSUMMARY_3">
									<td ID="PROBKIND_3">Coronary Arteriosclerosis</td>
									<td>Mar 1999</td>
									<td ID="PROBSTATUS_3">Chronic</td>
								</tr>
								<tr ID="PROBSUMMARY_4">
									<td ID="PROBKIND_4">Essential Hypertension</td>
									<td>Jan 1997</td>
									<td ID="PROBSTATUS_4">Active</td>
								</tr>
							</tbody>
						</table>
					</text>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.7" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.2" assigningAuthorityName="IHE PCC"/>
							<!-- Problem act template -->
							<id root="6a2fa88d-4174-4909-aece-db44b60a3abb"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<!--Problem observation template - NOT episode template-->
									<id root="d11275e7-67ae-11db-bd13-0800200c9a66"/>
									<code code="64572001" displayName="Condition" codeSystem="2.16.840.1.113883.6.96" codeSystemName="SNOMED-CT"/>
									<text>
										<reference value="#PROBSUMMARY_1"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low value="199001"/>
									</effectiveTime>
									<value xsi:type="CD" displayName="Diabetes Mellitus, Type 2" code="44054006" codeSystemName="SNOMED" codeSystem="2.16.840.1.113883.6.96"/>
								</observation>
							</entryRelationship>
						</act>
					</entry>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.7" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.2" assigningAuthorityName="IHE PCC"/>
							<!-- Problem act template -->
							<id root="ec8a6ff8-ed4b-4f7e-82c3-e98e58b45de7"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<!--Problem observation template -->
									<id root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
									<code displayName="Condition" code="64572001" codeSystemName="SNOMED-CT" codeSystem="2.16.840.1.113883.6.96"/>
									<text>
										<reference value="#PROBSUMMARY_2"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low value="199701"/>
										<high nullFlavor="UNK"/>
									</effectiveTime>
									<value xsi:type="CD" displayName="Hyperlipidemia" code="55822004" codeSystemName="SNOMED" codeSystem="2.16.840.1.113883.6.96"/>
								</observation>
							</entryRelationship>
						</act>
					</entry>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.7" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.2" assigningAuthorityName="IHE PCC"/>
							<!-- Problem act template -->
							<id root="d11275e9-67ae-11db-bd13-0800200c9a66"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<!-- Problem observation template -->
									<id root="9d3d416d-45ab-4da1-912f-4583e0632000"/>
									<code displayName="Condition" code="64572001" codeSystemName="SNOMED-CT" codeSystem="2.16.840.1.113883.6.96"/>
									<text>
										<reference value="#PROBSUMMARY_3"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low value="199903"/>
										<high nullFlavor="UNK"/>
									</effectiveTime>
									<value xsi:type="CD" displayName="Coronary Arteriosclerosis" code="53741008" codeSystemName="SNOMED" codeSystem="2.16.840.1.113883.6.96"/>
								</observation>
							</entryRelationship>
						</act>
					</entry>
					<entry typeCode="DRIV">
						<act classCode="ACT" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.7" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.2" assigningAuthorityName="IHE PCC"/>
							<!-- Problem act template -->
							<id root="5a2c903c-bd77-4bd1-ad9d-452383fbfefa"/>
							<code nullFlavor="NA"/>
							<statusCode code="active"/>
							<effectiveTime>
								<low nullFlavor="UNK"/>
							</effectiveTime>
							<entryRelationship typeCode="SUBJ" inversionInd="false">
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="HL7 CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
									<!-- Problem observation template -->
									<id/>
									<code displayName="Condition" code="64572001" codeSystemName="SNOMED-CT" codeSystem="2.16.840.1.113883.6.96"/>
									<text>
										<reference value="#PROBSUMMARY_4"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime>
										<low value="199701"/>
										<high nullFlavor="UNK"/>
									</effectiveTime>
									<value xsi:type="CD" displayName="Essential Hypertension" code="59621000" codeSystemName="SNOMED" codeSystem="2.16.840.1.113883.6.96"/>
								</observation>
							</entryRelationship>
						</act>
					</entry>
				</section>
			</component>
			<component>
				<!--Medications-->
				<section>
					<templateId root="2.16.840.1.113883.3.88.11.83.112" assigningAuthorityName="HITSP/C83"/>
					<templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.19" assigningAuthorityName="IHE PCC"/>
					<templateId root="2.16.840.1.113883.10.20.1.8" assigningAuthorityName="HL7 CCD"/>
					<!--Medications section template-->
					<code code="10160-0" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="History of medication use"/>
					<title>Medications</title>
					<text>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>Medication</th>
									<th>Dose</th>
									<th>Form</th>
									<th>Route</th>
									<th>Sig Text</th>
									<th>Dates</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr ID="MEDSUMMARY_1">
									<td ID="MEDNAME_1">Albuterol inhalant</td>
									<td>2 puffs</td>
									<td>inhaler</td>
									<td>inhale</td>
									<td ID="SIGTXT_1">2 puffs QID PRN (as needed for wheezing)</td>
									<td>July 2005+</td>
									<td ID="MEDSTATUS_1">Active</td>
								</tr>
								<tr ID="MEDSUMMARY_2">
									<td ID="MEDNAME_2">clopidogrel (Plavix)</td>
									<td>75 mg</td>
									<td>tablet</td>
									<td>oral</td>
									<td ID="SIGTXT_2">75mg PO daily</td>
									<td>unknown</td>
									<td ID="MEDSTATUS_2">Active</td>
								</tr>
								<tr ID="MEDSUMMARY_3">
									<td ID="MEDNAME_3">Metoprolol</td>
									<td>25 mg</td>
									<td>tablet</td>
									<td>oral</td>
									<td ID="SIGTXT_3">25mg PO BID</td>
									<td>Nov 2007+</td>
									<td ID="MEDSTATUS_3">Active</td>
								</tr>
								<tr ID="MEDSUMMARY_4">
									<td ID="MEDNAME_4">prednisone (Deltasone)</td>
									<td>20 mg</td>
									<td>tablet</td>
									<td>oral</td>
									<td ID="SIGTXT_4">20mg PO daily</td>
									<td>Mar 28, 2000+</td>
									<td ID="MEDSTATUS_4">Active</td>
								</tr>
							</tbody>
						</table>
					</text>
					<entry typeCode="DRIV">
						<substanceAdministration classCode="SBADM" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.8" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>
							<!--Medication activity template -->
							<id root="cdbd5b05-6cde-11db-9fe1-0800200c9a66"/>
							<text>
								<reference value="#SIGTEXT_1"/>
							</text>
							<statusCode code="completed"/>
							<effectiveTime xsi:type="IVL_TS">
								<low nullFlavor="UNK"/>
								<high nullFlavor="UNK"/>
							</effectiveTime>
							<effectiveTime xsi:type="PIVL_TS" institutionSpecified="false" operator="A">
								<period value="24" unit="h"/>
							</effectiveTime>
							<!--The following route, dose and administrationUnit elements are HITSP/C83 Sig Components that are optional elements in a HITSP/C83 document. The dose and administrationUnit information is often inferable from the code (e.g. RxNorm code) of the consumable/manufacturedProduct. The route is often inferable from the administrativeUnit (e.g tablet implies oral route). -->
							<routeCode>
								<originalText>oral</originalText>
							</routeCode>
							<doseQuantity value="2.5" unit="mg"/>
							<administrationUnitCode>
								<originalText>tablet</originalText>
							</administrationUnitCode>
							<consumable>
								<manufacturedProduct>
									<templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>
									<!-- Product template -->
									<manufacturedMaterial>
										<code code="309362" codeSystem="2.16.840.1.113883.6.88" displayName="Glyburide 2.5 MG oral tablet" codeSystemName="RxNorm">
											<originalText>Diabeta<reference/>
											</originalText>
											<translation code="205875" codeSystem="2.16.840.1.113883.6.88" displayName="Glyburide" codeSystemName="RxNorm"/>
										</code>
										<name>Glyburide</name>
									</manufacturedMaterial>
								</manufacturedProduct>
							</consumable>
						</substanceAdministration>
					</entry>
					<entry typeCode="DRIV">
						<substanceAdministration classCode="SBADM" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.8" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>
							<!--Medication activity template -->
							<id root="cdbd5b01-6cde-11db-9fe1-0800200c9a66"/>
							<text>
								<reference value="#SIGTEXT_2"/>
							</text>
							<statusCode code="completed"/>
							<effectiveTime xsi:type="IVL_TS">
								<low value="20071121"/>
								<high nullFlavor="UNK"/>
							</effectiveTime>
							<effectiveTime xsi:type="PIVL_TS" institutionSpecified="false" operator="A">
								<period value="12" unit="h"/>
							</effectiveTime>
							<!--The following route, dose and administrationUnit elements are HITSP/C83 Sig Components that are optional elements in a HITSP/C83 document. The dose and administrationUnit information is often inferable from the code (e.g. RxNorm code) of the consumable/manufacturedProduct. The route is often inferable from the administrativeUnit (e.g tablet implies oral route). -->
							<routeCode>
								<originalText>oral</originalText>
							</routeCode>
							<doseQuantity value="10" unit="mg"/>
							<administrationUnitCode>
								<originalText>tablet</originalText>
							</administrationUnitCode>
							<consumable>
								<manufacturedProduct>
									<templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>
									<!-- Product template -->
									<manufacturedMaterial>
										<code code="617314" codeSystem="2.16.840.1.113883.6.88" displayName="Atorvastatin Calcium  10 MG oral tablet">
											<originalText>Lipitor<reference/>
											</originalText>
										</code>
										<name>Atorvastatin Calcium</name>
									</manufacturedMaterial>
								</manufacturedProduct>
							</consumable>
						</substanceAdministration>
					</entry>
					<entry typeCode="DRIV">
						<substanceAdministration classCode="SBADM" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.8" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>
							<!--Medication activity template -->
							<id root="cdbd5b03-6cde-11db-9fe1-0800200c9a66"/>
							<text>
								<reference value="#SIGTEXT_3"/>
							</text>
							<statusCode code="completed"/>
							<effectiveTime xsi:type="IVL_TS">
								<low value="20000328"/>
								<high nullFlavor="UNK"/>
							</effectiveTime>
							<effectiveTime xsi:type="PIVL_TS" operator="A" institutionSpecified="false">
								<period value="24" unit="h"/>
							</effectiveTime>
							<!--The following route, dose and administrationUnit elements are HITSP/C83 Sig Components that are optional elements in a HITSP/C83 document. The dose and administrationUnit information is often inferable from the code (e.g. RxNorm code) of the consumable/manufacturedProduct. The route is often inferable from the administrativeUnit (e.g tablet implies oral route). -->
							<routeCode>
								<originalText>oral</originalText>
							</routeCode>
							<doseQuantity value="20" unit="mg"/>
							<administrationUnitCode>
								<originalText>tablet</originalText>
							</administrationUnitCode>
							<consumable>
								<manufacturedProduct>
									<templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>
									<!-- Product template -->
									<manufacturedMaterial>
										<code code="200801" codeSystem="2.16.840.1.113883.6.88" displayName="Furosemide 20 MG oral tablet">
											<originalText>Lasix<reference/>
											</originalText>
										</code>
										<name>Furosemide</name>
									</manufacturedMaterial>
								</manufacturedProduct>
							</consumable>
						</substanceAdministration>
					</entry>
					<entry typeCode="DRIV">
						<substanceAdministration classCode="SBADM" moodCode="EVN">
							<templateId root="2.16.840.1.113883.3.88.11.83.8" assigningAuthorityName="HITSP C83"/>
							<templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7" assigningAuthorityName="IHE PCC"/>
							<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>
							<!--Medication activity template -->
							<id root="cdbd5b07-6cde-11db-9fe1-0800200c9a66"/>
							<text>
								<reference value="#SIGTEXT_4"/>
							</text>
							<statusCode code="completed"/>
							<effectiveTime xsi:type="IVL_TS">
								<low value="20000328"/>
								<high value="20000404"/>
							</effectiveTime>
							<effectiveTime xsi:type="PIVL_TS" operator="A" institutionSpecified="false">
								<period value="6" unit="h"/>
							</effectiveTime>
							<!--The following route, dose and administrationUnit elements are HITSP/C83 Sig Components that are optional elements in a HITSP/C83 document. The dose and administrationUnit information is often inferable from the code (e.g. RxNorm code) of the consumable/manufacturedProduct. The route is often inferable from the administrativeUnit (e.g tablet implies oral route). -->
							<routeCode>
								<originalText>oral</originalText>
							</routeCode>
							<doseQuantity value="10" unit="mEq"/>
							<administrationUnitCode>
								<originalText>tablet</originalText>
							</administrationUnitCode>
							<consumable>
								<manufacturedProduct>
									<templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>
									<!-- Product template -->
									<manufacturedMaterial>
										<code code="197454" codeSystem="2.16.840.1.113883.6.88" displayName="Potassium Chloride 10 mEq oral tablet">
											<originalText>Klor-Con<reference/>
											</originalText>
										</code>
										<name>Potassium Chloride</name>
									</manufacturedMaterial>
								</manufacturedProduct>
							</consumable>
						</substanceAdministration>
					</entry>
				</section>
			</component>
			<component>
				<!--Results-->
				<section>
					<templateId root="2.16.840.1.113883.3.88.11.83.122" assigningAuthorityName="HITSP/C83"/>
					<templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.28" assigningAuthorityName="IHE PCC"/>
					<!--Diagnostic Results section template-->
					<code code="30954-2" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Results"/>
					<title>Diagnostic Results</title>
					<text>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>&#160;</th>
									<th>March 23, 2000</th>
									<th>April 06, 2000</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="3">
										<content styleCode="BoldItalics">Hematology</content>
									</td>
								</tr>
								<tr>
									<td>HGB (M 13-18 g/dl; F 12-16 g/dl)</td>
									<td>13.2</td>
									<td>&#160;</td>
								</tr>
								<tr>
									<td>WBC (4.3-10.8 10+3/ul)</td>
									<td>6.7</td>
									<td>&#160;</td>
								</tr>
								<tr>
									<td>PLT (135-145 meq/l)</td>
									<td>123 (L)</td>
									<td>&#160;</td>
								</tr>
								<tr>
									<td colspan="3">
										<content styleCode="BoldItalics">Chemistry</content>
									</td>
								</tr>
								<tr>
									<td>NA (135-145meq/l)</td>
									<td>&#160;</td>
									<td>140</td>
								</tr>
								<tr>
									<td>K (3.5-5.0 meq/l)</td>
									<td>&#160;</td>
									<td>4.0</td>
								</tr>
								<tr>
									<td>CL (98-106 meq/l)</td>
									<td>&#160;</td>
									<td>102</td>
								</tr>
								<tr>
									<td>HCO3 (18-23 meq/l)</td>
									<td>&#160;</td>
									<td>35 (H)</td>
								</tr>
							</tbody>
						</table>
					</text>
					<!--HITSP/C83 requires Diagnostic Results to have both Procedure and Results, but gives no guidance as to how they should be related together. If Result requires a specimen, the Procedure to obtain the specimen could be under the specimen. This example simply groups a Procedure with the Results obtained from that Procedure. This is probably not the best way to do it, but satisfies the requirements of both IHE/PCC and HITSP/C83 for Diagnostic Results. -->
					<entry typeCode="DRIV">
						<organizer classCode="BATTERY" moodCode="EVN">
							<templateId root="2.16.840.1.113883.10.20.1.32"/>
							<!--Result organizer template -->
							<id root="7d5a02b0-67a4-11db-bd13-0800200c9a66"/>
							<code code="43789009" codeSystem="2.16.840.1.113883.6.96" displayName="CBC WO DIFFERENTIAL"/>
							<statusCode code="completed"/>
							<effectiveTime value="200003231430"/>
							<component>
								<procedure classCode="PROC" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.17" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.29" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.19" assigningAuthorityName="IHE PCC"/>
									<id/>
									<code code="43789009" codeSystem="2.16.840.1.113883.6.96" displayName="CBC WO DIFFERENTIAL">
										<originalText>Extract blood for CBC test<reference value="Ptr to text  in parent Section"/>
										</originalText>
									</code>
									<text>Extract blood for CBC test. Note that IHE rules require description and reference to go here rather than in originalText of code.<reference value="Ptr to text  in parent Section"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200003231430"/>
								</procedure>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="107c2dc0-67a5-11db-bd13-0800200c9a66"/>
									<code code="30313-1" codeSystem="2.16.840.1.113883.6.1" displayName="HGB"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200003231430"/>
									<value xsi:type="PQ" value="13.2" unit="g/dl"/>
									<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="8b3fa370-67a5-11db-bd13-0800200c9a66"/>
									<code code="33765-9" codeSystem="2.16.840.1.113883.6.1" displayName="WBC"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200003231430"/>
									<value xsi:type="PQ" value="6.7" unit="10+3/ul"/>
									<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="80a6c740-67a5-11db-bd13-0800200c9a66"/>
									<code code="26515-7" codeSystem="2.16.840.1.113883.6.1" displayName="PLT"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200003231430"/>
									<value xsi:type="PQ" value="123" unit="10+3/ul"/>
									<interpretationCode code="L" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
						</organizer>
					</entry>
					<entry typeCode="DRIV">
						<organizer classCode="BATTERY" moodCode="EVN">
							<templateId root="2.16.840.1.113883.10.20.1.32"/>
							<!--Result organizer template -->
							<id root="a40027e0-67a5-11db-bd13-0800200c9a66"/>
							<code code="20109005" codeSystem="2.16.840.1.113883.6.96" displayName="LYTES" codeSystemName="SNOMED CT"/>
							<statusCode code="completed"/>
							<effectiveTime value="200004061300"/>
							<component>
								<procedure classCode="PROC" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.17" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.29" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.19" assigningAuthorityName="IHE PCC"/>
									<id/>
									<code code="20109005" codeSystem="2.16.840.1.113883.6.96" displayName="LYTES" codeSystemName="SNOMED CT">
										<originalText>Extract blood for electrolytes tests<reference value="Ptr to text  in parent Section"/>
										</originalText>
									</code>
									<text>Extract blood for electrolytes tests. IHE rules require description and reference to go here rather than in originalText of code.<reference value="Ptr to text  in parent Section"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200004061300"/>
								</procedure>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!--Result observation template -->
									<id root="a40027e1-67a5-11db-bd13-0800200c9a66"/>
									<code code="2951-2" codeSystem="2.16.840.1.113883.6.1" displayName="NA"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200004061300"/>
									<value xsi:type="PQ" value="140" unit="meq/l"/>
									<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="a40027e2-67a5-11db-bd13-0800200c9a66"/>
									<code code="2823-3" codeSystem="2.16.840.1.113883.6.1" displayName="K"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200004061300"/>
									<value xsi:type="PQ" value="4.0" unit="meq/l"/>
									<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="a40027e3-67a5-11db-bd13-0800200c9a66"/>
									<code code="2075-0" codeSystem="2.16.840.1.113883.6.1" displayName="CL"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200004061300"/>
									<value xsi:type="PQ" value="102" unit="meq/l"/>
									<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
							<component>
								<observation classCode="OBS" moodCode="EVN">
									<templateId root="2.16.840.1.113883.3.88.11.83.15.1" assigningAuthorityName="HITSP C83"/>
									<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
									<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
									<!-- Result observation template -->
									<id root="a40027e4-67a5-11db-bd13-0800200c9a66"/>
									<code code="1963-8" codeSystem="2.16.840.1.113883.6.1" displayName="HCO3"/>
									<text>
										<reference value="PtrToValueInsectionText"/>
									</text>
									<statusCode code="completed"/>
									<effectiveTime value="200004061300"/>
									<value xsi:type="PQ" value="35" unit="meq/l"/>
									<interpretationCode code="H" codeSystem="2.16.840.1.113883.5.83"/>
								</observation>
							</component>
						</organizer>
					</entry>
				</section>
			</component>
		</structuredBody>
	</component>
</ClinicalDocument>
eos;
    $xml = ClinicalXmls::parse($xml);
    echo htmlentities($xml->asHtml());
    exit;
      case '151':
    $xml= <<<eos
<?xml version="1.0" encoding="utf-8"?>
<!-- edited with XMLSpy v2010 rel. 2 (http://www.altova.com) by Len Gallagher (NIST) -->
<ccr:ContinuityOfCareRecord xsi:schemaLocation="urn:astm-org:CCR CCRV1.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ccr="urn:astm-org:CCR">
  <ccr:CCRDocumentObjectID>CCR_AMBv11_DS01</ccr:CCRDocumentObjectID>
  <ccr:Language>
    <ccr:Text>US English</ccr:Text>
    <ccr:Code>
      <ccr:Value>en-US</ccr:Value>
    </ccr:Code>
  </ccr:Language>
  <ccr:Version>V1.0</ccr:Version>
  <ccr:DateTime>
    <ccr:ExactDateTime>2010-03-25T13:15:00-05:00</ccr:ExactDateTime>
  </ccr:DateTime>
  <ccr:Patient>
    <ccr:ActorID>PatientID_1</ccr:ActorID>
  </ccr:Patient>
  <ccr:From>
    <ccr:ActorLink>
      <ccr:ActorID>AuthorID_01</ccr:ActorID>
      <ccr:ActorRole>
        <ccr:Text>Personal Physician</ccr:Text>
      </ccr:ActorRole>
    </ccr:ActorLink>
  </ccr:From>
  <ccr:To>
    <ccr:ActorLink>
      <ccr:ActorID>RecipientID_01</ccr:ActorID>
      <ccr:ActorRole>
        <ccr:Text>Interested Party</ccr:Text>
      </ccr:ActorRole>
    </ccr:ActorLink>
  </ccr:To>
  <ccr:Purpose>
    <ccr:Description>
      <ccr:Text>Transfer of Care</ccr:Text>
    </ccr:Description>
  </ccr:Purpose>
  <ccr:Body>
    <ccr:Problems>
      <ccr:Problem>
        <ccr:CCRDataObjectID>ProbID_1</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>09/16/2009</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Finding</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Diabetes Mellitus, Type 2</ccr:Text>
          <ccr:Code>
            <ccr:Value>250.02</ccr:Value>
            <ccr:CodingSystem>ICD9-CM</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>44054006</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
      </ccr:Problem>
      <ccr:Problem>
        <ccr:CCRDataObjectID>ProbID_2</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Symptom</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Hyperlipidemia</ccr:Text>
          <ccr:Code>
            <ccr:Value>272.4</ccr:Value>
            <ccr:CodingSystem>ICD9-CM</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>55822004</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
      </ccr:Problem>
      <ccr:Problem>
        <ccr:CCRDataObjectID>ProbID_3</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Finding</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Coronary Artery Disease (CAD)</ccr:Text>
          <ccr:Code>
            <ccr:Value>414.01</ccr:Value>
            <ccr:CodingSystem>ICD9-CM</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>53741008</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Chronic</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
      </ccr:Problem>
      <ccr:Problem>
        <ccr:CCRDataObjectID>ProbID_4</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Diagnosis</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Hypertension, Essential</ccr:Text>
          <ccr:Code>
            <ccr:Value>401.9</ccr:Value>
            <ccr:CodingSystem>ICD9-CM</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>59621000</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
      </ccr:Problem>
    </ccr:Problems>
    <ccr:Alerts>
      <ccr:Alert>
        <ccr:CCRDataObjectID>AlertID_1</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>6/27/02</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Drug Allergy</ccr:Text>
          <ccr:Code>
            <ccr:Value>416098002</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Codeine allergy</ccr:Text>
          <ccr:Code>
            <ccr:Value>293597001</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Agent>
          <ccr:Products>
            <ccr:Product>
              <ccr:CCRDataObjectID />
              <ccr:Source />
              <ccr:Product>
                <ccr:ProductName>
                  <ccr:Text>Codeine</ccr:Text>
                  <ccr:Code>
                    <ccr:Value>2670</ccr:Value>
                    <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
                  </ccr:Code>
                </ccr:ProductName>
              </ccr:Product>
            </ccr:Product>
          </ccr:Products>
        </ccr:Agent>
        <ccr:Reaction>
          <ccr:Description>
            <ccr:Text>Hives, nausea</ccr:Text>
          </ccr:Description>
        </ccr:Reaction>
      </ccr:Alert>
      <ccr:Alert>
        <ccr:CCRDataObjectID>AlertID_2</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>3/25/04</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Drug Allergy</ccr:Text>
          <ccr:Code>
            <ccr:Value>416098002</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Indomethacin allergy</ccr:Text>
          <ccr:Code>
            <ccr:Value>293620004</ccr:Value>
            <ccr:CodingSystem>SNOMED CT</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Reaction>
          <ccr:Description>
            <ccr:Text>Nausea, dizziness, headache</ccr:Text>
          </ccr:Description>
        </ccr:Reaction>
      </ccr:Alert>
    </ccr:Alerts>
    <ccr:Medications>
      <ccr:Medication>
        <ccr:CCRDataObjectID>MedID_01</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>09/16/2009</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>glyburide</ccr:Text>
            <ccr:Code>
              <ccr:Value>205875</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:BrandName>
            <ccr:Text>Diabeta</ccr:Text>
          </ccr:BrandName>
          <ccr:Strength>
            <ccr:Value>2.5</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg</ccr:Unit>
            </ccr:Units>
          </ccr:Strength>
          <ccr:Form>
            <ccr:Text>Tablet</ccr:Text>
          </ccr:Form>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Frequency>
              <ccr:Value>1 tab Q AM PO</ccr:Value>
            </ccr:Frequency>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:PatientInstructions>
          <ccr:Instruction>
            <ccr:Text>1 tablet by mouth every morning</ccr:Text>
          </ccr:Instruction>
        </ccr:PatientInstructions>
      </ccr:Medication>
      <ccr:Medication>
        <ccr:CCRDataObjectID>MedID_02</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>atorvastatin calcium</ccr:Text>
            <ccr:Code>
              <ccr:Value>617314</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:BrandName>
            <ccr:Text>Lipitor</ccr:Text>
          </ccr:BrandName>
          <ccr:Strength>
            <ccr:Value>10</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg</ccr:Unit>
            </ccr:Units>
          </ccr:Strength>
          <ccr:Form>
            <ccr:Text>Tablet</ccr:Text>
          </ccr:Form>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Frequency>
              <ccr:Value>1 tab Q Day PO</ccr:Value>
            </ccr:Frequency>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:PatientInstructions>
          <ccr:Instruction>
            <ccr:Text>1 tablet by mouth every day</ccr:Text>
          </ccr:Instruction>
        </ccr:PatientInstructions>
      </ccr:Medication>
      <ccr:Medication>
        <ccr:CCRDataObjectID>MedID_03</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>furosemide</ccr:Text>
            <ccr:Code>
              <ccr:Value>200801</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:BrandName>
            <ccr:Text>Lasix</ccr:Text>
          </ccr:BrandName>
          <ccr:Strength>
            <ccr:Value>20</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg</ccr:Unit>
            </ccr:Units>
          </ccr:Strength>
          <ccr:Form>
            <ccr:Text>Tablet</ccr:Text>
          </ccr:Form>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Frequency>
              <ccr:Value>1 tab BID PO</ccr:Value>
            </ccr:Frequency>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:PatientInstructions>
          <ccr:Instruction>
            <ccr:Text>1 tablet by mouth 2 times per day</ccr:Text>
          </ccr:Instruction>
        </ccr:PatientInstructions>
      </ccr:Medication>
      <ccr:Medication>
        <ccr:CCRDataObjectID>MedID_04</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/05/2002</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Status>
          <ccr:Text>Active</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>potassium chloride</ccr:Text>
            <ccr:Code>
              <ccr:Value>628958</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:BrandName>
            <ccr:Text>Klor-Con</ccr:Text>
          </ccr:BrandName>
          <ccr:Strength>
            <ccr:Value>10</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mEq</ccr:Unit>
            </ccr:Units>
          </ccr:Strength>
          <ccr:Form>
            <ccr:Text>Tablet</ccr:Text>
          </ccr:Form>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Frequency>
              <ccr:Value>1 tab BID PO</ccr:Value>
            </ccr:Frequency>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:PatientInstructions>
          <ccr:Instruction>
            <ccr:Text>1 tablet by mouth 2 times per day</ccr:Text>
          </ccr:Instruction>
        </ccr:PatientInstructions>
      </ccr:Medication>
    </ccr:Medications>
    <ccr:Immunizations>
      <ccr:Immunization>
        <ccr:CCRDataObjectID>ImmunizID_01</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>03/25/2010</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>Hepatitis A, Adult</ccr:Text>
            <ccr:Code>
              <ccr:Value>52</ccr:Value>
              <ccr:CodingSystem>CVX</ccr:CodingSystem>
            </ccr:Code>
            <ccr:Code>
              <ccr:Value>798367</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:Manufacturer>
            <ccr:ActorID>ManufID_1</ccr:ActorID>
            <ccr:ActorRole>
              <ccr:Text>GLAXOSMITHKLINE</ccr:Text>
            </ccr:ActorRole>
          </ccr:Manufacturer>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Route>
              <ccr:Text>Intramuscular</ccr:Text>
              <ccr:Code>
                <ccr:Value>IM</ccr:Value>
              </ccr:Code>
            </ccr:Route>
            <ccr:Site>
              <ccr:Text>Left Arm</ccr:Text>
            </ccr:Site>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:SeriesNumber>HA165V1</ccr:SeriesNumber>
        <ccr:Reaction>
          <ccr:Description>
            <ccr:Text>None</ccr:Text>
          </ccr:Description>
        </ccr:Reaction>
      </ccr:Immunization>
      <ccr:Immunization>
        <ccr:CCRDataObjectID>ImmunizID_02</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>10/25/2009</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Product>
          <ccr:ProductName>
            <ccr:Text>Pneumococcal Polysaccharide Vaccine</ccr:Text>
            <ccr:Code>
              <ccr:Value>33</ccr:Value>
              <ccr:CodingSystem>CVX</ccr:CodingSystem>
            </ccr:Code>
            <ccr:Code>
              <ccr:Value>854977</ccr:Value>
              <ccr:CodingSystem>RxNorm</ccr:CodingSystem>
            </ccr:Code>
          </ccr:ProductName>
          <ccr:Manufacturer>
            <ccr:ActorID>ManufID_2</ccr:ActorID>
            <ccr:ActorRole>
              <ccr:Text>Merck</ccr:Text>
            </ccr:ActorRole>
          </ccr:Manufacturer>
        </ccr:Product>
        <ccr:Directions>
          <ccr:Direction>
            <ccr:Route>
              <ccr:Text>Intramuscular</ccr:Text>
              <ccr:Code>
                <ccr:Value>IM</ccr:Value>
              </ccr:Code>
            </ccr:Route>
            <ccr:Site>
              <ccr:Text>Right Arm</ccr:Text>
            </ccr:Site>
          </ccr:Direction>
        </ccr:Directions>
        <ccr:SeriesNumber>887765B</ccr:SeriesNumber>
        <ccr:Reaction>
          <ccr:Description>
            <ccr:Text>None</ccr:Text>
          </ccr:Description>
        </ccr:Reaction>
      </ccr:Immunization>
    </ccr:Immunizations>
    <ccr:Results>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_01</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Fasting Blood Glucose</ccr:Text>
          <ccr:Code>
            <ccr:Value>14771-0</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>Fasting Blood Glucose</ccr:Text>
            <ccr:Code>
              <ccr:Value>14771-0</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>178</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>70-100</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_02</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Creatinine</ccr:Text>
          <ccr:Code>
            <ccr:Value>14682-9</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>Creatinine</ccr:Text>
            <ccr:Code>
              <ccr:Value>14682-9</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>1.0</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>0.5-1.4</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_03</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>BUN</ccr:Text>
          <ccr:Code>
            <ccr:Value>14937-7</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>BUN</ccr:Text>
            <ccr:Code>
              <ccr:Value>14937-7</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>18</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>7-30</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_04</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Sodium</ccr:Text>
          <ccr:Code>
            <ccr:Value>2951-2</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>Sodium</ccr:Text>
            <ccr:Code>
              <ccr:Value>2951-2</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>141</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mEq/L</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>135-146</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mEq/L</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_05</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Total cholesterol</ccr:Text>
          <ccr:Code>
            <ccr:Value>14647-2</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>Total cholesterol</ccr:Text>
            <ccr:Code>
              <ccr:Value>14647-2</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>162</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>&lt; 200</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_06</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>HDL cholesterol</ccr:Text>
          <ccr:Code>
            <ccr:Value>14646-4</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>HDL cholesterol</ccr:Text>
            <ccr:Code>
              <ccr:Value>14646-4</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>43</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>&gt; 40</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_07</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>LDL cholesterol</ccr:Text>
          <ccr:Code>
            <ccr:Value>2089-1</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>LDL cholesterol</ccr:Text>
            <ccr:Code>
              <ccr:Value>2089-1</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>84</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>&lt; 100</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
      <ccr:Result>
        <ccr:CCRDataObjectID>ResultID_08</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>9/16/09</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Chemistry</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Triglycerides</ccr:Text>
          <ccr:Code>
            <ccr:Value>14927-8</ccr:Value>
            <ccr:CodingSystem>LOINC</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Test>
          <ccr:CCRDataObjectID />
          <ccr:Description>
            <ccr:Text>Triglycerides</ccr:Text>
            <ccr:Code>
              <ccr:Value>14927-8</ccr:Value>
              <ccr:CodingSystem>LOINC</ccr:CodingSystem>
            </ccr:Code>
          </ccr:Description>
          <ccr:Status>
            <ccr:Text>final result</ccr:Text>
          </ccr:Status>
          <ccr:Source />
          <ccr:TestResult>
            <ccr:Value>177</ccr:Value>
            <ccr:Units>
              <ccr:Unit>mg/dl</ccr:Unit>
            </ccr:Units>
          </ccr:TestResult>
          <ccr:NormalResult>
            <ccr:Normal>
              <ccr:Value>&lt; 150</ccr:Value>
              <ccr:Units>
                <ccr:Unit>mg/dl</ccr:Unit>
              </ccr:Units>
              <ccr:Source />
            </ccr:Normal>
          </ccr:NormalResult>
        </ccr:Test>
      </ccr:Result>
    </ccr:Results>
    <ccr:Procedures>
      <ccr:Procedure>
        <ccr:CCRDataObjectID>ProcID_1</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>7/26/06</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Surgical</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Excision of benign lesion on arm</ccr:Text>
          <ccr:Code>
            <ccr:Value>11401</ccr:Value>
            <ccr:CodingSystem>CPT-4</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>86.3</ccr:Value>
            <ccr:CodingSystem>ICD-9</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Completed</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Locations>
          <ccr:Location>
            <ccr:Actor>
              <ccr:ActorID>ClinicID_01</ccr:ActorID>
            </ccr:Actor>
          </ccr:Location>
        </ccr:Locations>
        <ccr:Substance>
          <ccr:Text>NA</ccr:Text>
        </ccr:Substance>
        <ccr:Method>
          <ccr:Text>NA</ccr:Text>
        </ccr:Method>
        <ccr:Position>
          <ccr:Text>NA</ccr:Text>
        </ccr:Position>
        <ccr:Site>
          <ccr:Text>Left Arm</ccr:Text>
        </ccr:Site>
      </ccr:Procedure>
      <ccr:Procedure>
        <ccr:CCRDataObjectID>ProcID_2</ccr:CCRDataObjectID>
        <ccr:DateTime>
          <ccr:ApproximateDateTime>
            <ccr:Text>05/15/03</ccr:Text>
          </ccr:ApproximateDateTime>
        </ccr:DateTime>
        <ccr:Type>
          <ccr:Text>Surgical</ccr:Text>
        </ccr:Type>
        <ccr:Description>
          <ccr:Text>Emergency Appendectomy</ccr:Text>
          <ccr:Code>
            <ccr:Value>44950</ccr:Value>
            <ccr:CodingSystem>CPT-4</ccr:CodingSystem>
          </ccr:Code>
          <ccr:Code>
            <ccr:Value>47.09</ccr:Value>
            <ccr:CodingSystem>ICD-9</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Description>
        <ccr:Status>
          <ccr:Text>Completed</ccr:Text>
        </ccr:Status>
        <ccr:Source>
          <ccr:Actor>
            <ccr:ActorID>AuthorID_01</ccr:ActorID>
          </ccr:Actor>
        </ccr:Source>
        <ccr:Locations>
          <ccr:Location>
            <ccr:Actor>
              <ccr:ActorID>ClinicID_01</ccr:ActorID>
            </ccr:Actor>
          </ccr:Location>
        </ccr:Locations>
        <ccr:Substance>
          <ccr:Text>NA</ccr:Text>
        </ccr:Substance>
        <ccr:Method>
          <ccr:Text>NA</ccr:Text>
        </ccr:Method>
        <ccr:Position>
          <ccr:Text>NA</ccr:Text>
        </ccr:Position>
        <ccr:Site>
          <ccr:Text>NA</ccr:Text>
        </ccr:Site>
      </ccr:Procedure>
    </ccr:Procedures>
  </ccr:Body>
  <ccr:Actors>
    <ccr:Actor>
      <ccr:ActorObjectID>PatientID_1</ccr:ActorObjectID>
      <ccr:Person>
        <ccr:Name>
          <ccr:BirthName>
            <ccr:Given>Jeffrey</ccr:Given>
            <ccr:Given></ccr:Given>
            <ccr:Family>Surrett</ccr:Family>
          </ccr:BirthName>
        </ccr:Name>
        <ccr:DateOfBirth>
          <ccr:ExactDateTime>1960-09-24T11:20:00-05:00</ccr:ExactDateTime>
        </ccr:DateOfBirth>
        <ccr:Gender>
          <ccr:Text>Male</ccr:Text>
          <ccr:Code>
            <ccr:Value>M</ccr:Value>
            <ccr:CodingSystem>HL7 AdministrativeGender</ccr:CodingSystem>
          </ccr:Code>
        </ccr:Gender>
      </ccr:Person>
      <ccr:IDs>
        <ccr:ID>999869999</ccr:ID>
        <ccr:IssuedBy>
          <ccr:ActorID>ClinicID_01</ccr:ActorID>
          <ccr:ActorRole>
            <ccr:Text>Medical Record Number</ccr:Text>
          </ccr:ActorRole>
        </ccr:IssuedBy>
        <ccr:Source />
      </ccr:IDs>
      <ccr:Address>
        <ccr:Line1>347 Grove Street</ccr:Line1>
        <ccr:City>Williamsport</ccr:City>
        <ccr:State>PA</ccr:State>
        <ccr:PostalCode>17701</ccr:PostalCode>
      </ccr:Address>
      <ccr:Telephone>
        <ccr:Value>+1-570-837-9933</ccr:Value>
      </ccr:Telephone>
      <ccr:Source />
    </ccr:Actor>
    <ccr:Actor>
      <ccr:ActorObjectID>AuthorID_01</ccr:ActorObjectID>
      <ccr:Person>
        <ccr:Name>
          <ccr:BirthName>
            <ccr:Given>Thomas</ccr:Given>
            <ccr:Family>Henry</ccr:Family>
            <ccr:Suffix>MD</ccr:Suffix>
          </ccr:BirthName>
        </ccr:Name>
      </ccr:Person>
      <ccr:Address>
        <ccr:Line1>5544 Sutter Street</ccr:Line1>
        <ccr:City>Williamsport</ccr:City>
        <ccr:State>PA</ccr:State>
        <ccr:PostalCode>17701</ccr:PostalCode>
      </ccr:Address>
      <ccr:Telephone>
        <ccr:Value>+1-301-975-3260</ccr:Value>
      </ccr:Telephone>
      <ccr:Source />
    </ccr:Actor>
    <ccr:Actor>
      <ccr:ActorObjectID>RecipientID_01</ccr:ActorObjectID>
      <ccr:Person>
        <ccr:Name>
          <ccr:BirthName>
            <ccr:Given>Interested</ccr:Given>
            <ccr:Family>AnyPerson</ccr:Family>
          </ccr:BirthName>
        </ccr:Name>
      </ccr:Person>
      <ccr:Source />
    </ccr:Actor>
    <ccr:Actor>
      <ccr:ActorObjectID>ManufID_1</ccr:ActorObjectID>
      <ccr:Organization>
        <ccr:Name>GLAXOSMITHKLINE</ccr:Name>
      </ccr:Organization>
      <ccr:Source />
    </ccr:Actor>
    <ccr:Actor>
      <ccr:ActorObjectID>ManufID_2</ccr:ActorObjectID>
      <ccr:Organization>
        <ccr:Name>Merck</ccr:Name>
      </ccr:Organization>
      <ccr:Source />
    </ccr:Actor>
    <ccr:Actor>
      <ccr:ActorObjectID>ClinicID_01</ccr:ActorObjectID>
      <ccr:Organization>
        <ccr:Name>Metropolitan Clinic</ccr:Name>
      </ccr:Organization>
      <ccr:Address>
        <ccr:Line1>5544 Sutter Street</ccr:Line1>
        <ccr:City>Williamsport</ccr:City>
        <ccr:State>PA</ccr:State>
        <ccr:PostalCode>17701</ccr:PostalCode>
      </ccr:Address>
      <ccr:Source />
    </ccr:Actor>
  </ccr:Actors>
</ccr:ContinuityOfCareRecord>
eos;
    $xml = ClinicalXmls::parse($xml);
    p_r($xml);
    exit;
  case '160':
    $r = CmsReport_NQF0013::from('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '161':
    $r = CmsReports::getNQF0421('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    //    $xml = PQRI::from($r);
    //    p_r($xml);
    exit;
  case '162':
    $r = CmsReports::getNQF0028a('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    $r = CmsReports::getNQF0028b('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '163':
    $r = CmsReports::getNQF0041('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '164':
    $r = CmsReports::getNQF0041('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '165':
    $r = CmsReports::getNQF0024('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '166':
    $r = CmsReports::getNQF0038('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '167':
    $r = CmsReports::getNQF0027('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '168':
    $r = CmsReports::getNQF0034('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '169':
    $r = CmsReports::getNQF0043('2011-01-01', '2012-01-01');
    p_r(jsondecode(jsonencode($r)));
    exit;
  case '200':
    $s = LoginSession::login('wghornsby','yukon1')->setUi(false);
    p_r($s);
    exit;
  case '201':
    LoginSession::verify();
    global $login;
    $j = jsondecode($login->asJson());
    //p_r($login, 'before');
    p_r($j, 'after');
    exit;
  case '202':
    LoginSession::verify();
    p_r($login);
    exit;
  case '203':
    LoginSession::verify();
    $login->Role->erx = 0;
    $login->Role->Patient->vitals = 0;
    $login->Role->Patient->track = 0;
    $login->json = null;
    $login->save();
    p_r($login->Role);
    exit;
  case '210':
    $recs = Templates_AdminSearch::search('2009');
    foreach ($recs as $rec) 
      p_r($rec->asLink());
    exit;
  case '220':
    $rec = ClientStub::fetchByEmrId('3608');
    p_r($rec);
    exit;
  case '230':
    $rec = Facesheet_Visit::from(3684, '2012-08-03');
    p_r($rec);
    exit;
  case '240':
    $labs = Lab::fetchAll_forSftpPolling();
    foreach ($labs as $lab) {
      $folder = FtpFolder::from($lab);
      $files = $folder->getIncoming();
      $folder->moveToOut($files); 
    }
    exit;
  case '241':
    HL7_Labs::import_fromFtp();
    exit;
  case '250':
    $par = Par2::fetch(2099);
    p_r($par);
    exit; 
  case '260':
    $map = Templates_IolEntry::getIols();
    p_r($map);
    exit; 
  case '261':
    $entry = Templates_IolEntry::getEntry('1377');
    p_r($entry);
    exit;
  case '262':
    $entry = Templates_IolEntry::getEntry('3209');
    p_r($entry);
    exit;
  case '270':
    $ip = '41.251.34.211';
    $ip = '24.248.167.226';
    $rec = IpLookup::fetch($ip);
    p_r($rec);
    exit;
  case '280':
    $s = 'click01';
    p_r(MyCrypt::encrypt('click01'));
    exit;
  case '281':
    $s = '7GystqtvVZgRCXqypt9ib3Mc1rUAZR7sVOQB/fdBxlAa0biFQL80yxqXM3ltWQt02IUoMhUIOn1whO7FSmpe0LB2uvvcA/IpJ+/bck51';
    p_r('>' . MyCrypt::decrypt($s) . '<');
    exit;
  case '290':
    $w = Dashboard::get();
    p_r($w);
    exit;
  case '291':
    $w = Dashboard::getAppts('2012-11-18');
    p_r($w);
    exit;
  case '292':
    $w = Dashboard::getMessages();
    p_r($w);
    exit;
  case '293':
    $w = DocStub::fetchAllUnreviewed(1, 1);
    p_r($w);
    exit;
  case '294':
    $w = Documentation::getUnreviewed();
    p_r($w);
    exit;
  case '300':
    $recs = UserManager::getMine();
    $recs = jsondecode(jsonencode($recs));
    p_r($recs);
    exit;
  case '310':
    $lots = Immuns::getLastLots();
    p_r($lots);
    exit;
  case '311':
    $pid = Immuns::getPid();
    print_r($pid);
    exit;
  case '312':
    $lots = Immuns::getParAndLots(2816);
    p_r($lots);
    exit;
  case '320':
    $users = UserGroups::getAllAdmins();
    p_r($users);
    exit;
  case '321':
    Alerts::sendGlassBroken(1658);
    exit;
}
function getEncryptKey($c, $pass) {
  $salt = 'lcd solutions'; 
  return $c->pbkdf2($pass, $salt, 1966, 32);
}
?>
</html>

