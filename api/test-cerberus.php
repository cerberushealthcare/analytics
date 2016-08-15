<?php
require_once 'rest/Rest.php';
require_once 'rest/ApiException.php';
require_once 'rest/Cerberus.php';
//
//$rest = new Rest();
//$post_data = "operation=PRACTICE&practiceId=37&name=BetaTest 1&addr1=4635 Post Road&addr2=Suite 3&addr3=&city=East Greenwich&state=RI&zip=02818&phone=4013988000&fax=4013988006&email=&url=";
//$post_data = "operation=SELECTPATIENT&patientId=489&practiceId=37&sessionId=mm945hj06toeq71q3dcpofal63&page=FACESHEET&agency_clientId=";
//$post_data = "patientId=401&active=1&operation=PATIENT&patientCode=wgh001&practiceId=37&lastName=Hornsby&firstName=Test&gender=M&birth=04/23/1985&primaryaddr1=509 MACON AVE&primaryaddr2=&primaryaddr3=&primarycity=LOUISVILLE&primarystate=KY&primaryzip=40207&primaryphone=(502) 930-0777&primaryphonecell=&primaryemail=&emergencylastname=SAUER&emergencyfirstname=CHUCK&emergencyphone=(401) 965-9806 &rxname=&rxaddr1=&rxaddr2=&rxaddr3=&rxcity=&rxstate=&rxzip=&rxphone=&rxfax=&inspriplan=pl123&insprigroup=gr987&inspripolicy=SAU489&insprisubscriber=WHISKEY R SAUER &insprinamecard=&inspridateeff=01/01/2009&insprinote=&inssecplan=&inssecgroup=&inssecpolicy=&inssecsubscriber=&inssecnamecard=&inssecdateeff=&inssecnote=&custom1=&custom2=&custom3=&note=";


//$post_data = "operation=LOGIN&practiceId=37&userId=demodoctor&password=demo";
$sid = "sessionId=iv34402e3mknl987ktddlgusq1";
$post_data = "$sid&operation=PATIENT&patientCode=SAUWH000&practiceid=37&lastName=SAUER&firstName=WHISKEY&gender=M&birth=09%2F05%2F1955&primaryaddr1=69+MAIN+STREET&primaryaddr2=&primaryaddr3=&primarycity=EAST+GREENWICH&primarystate=RI&primaryzip=02818&primaryphone=(401)+444-5567&primaryphonecell=&primaryemail=sauerw%40aol%2Ecom&emergencyname=CHUCK+SAUER&emergencyphone=(401)+965-9806+&rxname=CVS&rxaddr1=2250+New+London+Turnpike&rxaddr2=&rxaddr3=&rxcity=West+Greenwich&rxstate=RI&rxzip=02818&rxphone=(401)+821-2060+&rxfax=(401)+821-2062+&inspriplan=pl123&insprigroup=gr987&inspripolicy=SAU489&insprisubscriber=WHISKEY+E+SAUER+I&insprinamecard=&inspridateeff=01%2F01%2F2009&insprinote=&inssecplan=&inssecgroup=&inssecpolicy=&inssecsubscriber=&inssecnamecard=&inssecdateeff=&inssecnote=&custom1=&custom2=&custom3=&note=&patientId=4891&active=1&language=1&familyRelease=fred&releasePref=2&releaseData=barney";

//$post_data = "$sid&practiceId=37&page=TRACKING&agency_clientId=&operation=SELECTPATIENT&patientId=401";
//$post_data ="$sid&operation=USER&userId=peggym&practiceId=37&name=Peggy+Mckinney+&role=support&homephone=&cellphone=&email=&license=&licensestate=&dea=&npi=&password=Medical1!";
//$post_data ="$sid&operation=USER&userId=peggym&practiceId=37&name=Peggy+Mckinney+&role=support&homephone=&cellphone=&email=&license=CHANGED&licensestate=&dea=&npi=";
//$post_data ="$sid&operation=USER&userId=peggym&practiceId=37&name=Peggy+Mckinney+&role=support&homephone=&cellphone=&email=&license=CHANGED&licensestate=&dea=&npi=&password=clicktate1";


//$post_data = "$sid&operation=PATIENT&patientCode=RAWJO000&practiceid=37&lastName=RAWLINGS&firstName=JOHNNY&gender=M&birth=05%2F18%2F2011&primaryaddr1=2205+CASCADE+WAY&primaryaddr2=&primaryaddr3=&primarycity=LEXINGTON&primarystate=KY&primaryzip=40515&primaryphone=(888)+825-4258&primaryphonecell=&primaryemail=info%40clicktate%2Ecom&emergencyname=DAD+RAWLINGS&emergencyphone=&rxname=&rxaddr1=&rxaddr2=&rxaddr3=&rxcity=&rxstate=&rxzip=&rxphone=&rxfax=&inspriplan=&insprigroup=&inspripolicy=&insprisubscriber=&insprinamecard=&inspridateeff=&insprinote=&inssecplan=&inssecgroup=&inssecpolicy=&inssecsubscriber=&inssecnamecard=&inssecdateeff=&inssecnote=&custom1=&custom2=&custom3=&note=&patientId=204494&active=1&ethnicity=2&language=3&familyRelease=&releasePref=1&releaseData=&livingWill=0&POA=0&race=01&primaryPhys=462";
//$post_data = "$sid&operation=SELECTPATIENT&patientId=489&practiceId=37&page=FACESHEET&agency_clientId=";
//$post_data = "$sid&practiceId=37&operation=POLLSTATUS";
//$post_data = "$sid&operation=PATIENTINFO&id=489";
try {
  $rest->data = explode_with_keys($post_data);
  $server = new Cerberus();
} catch (Exception $e) {
  echo $e->getMessage();
}
try {
  $response = $server->request($rest);
  header("Cache-Control: no-cache, must-revalidate"); 
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
  header("Pragma: no-cache");
  session_cache_limiter("nocache");
  echo $response;
} catch (ApiException $e) {
  echo $e->getResponse();
} catch (Exception $e) {
  echo $e->getMessage();
  //header('Request not recognized.', true, 405);
}
function explode_with_keys($string)
{
       $output=array();
       $array=explode('&', $string);
       foreach ($array as $value) {
               $row=explode("=",$value);
               $output[$row[0]]=urldecode($row[1]);
       }
       return $output;
} 
?>