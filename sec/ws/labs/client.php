<?php
$url = "https://www.clicktate.com/cert/sec/ws/labs/LabServices.wsdl";
$client = new SoapClient($url);
$msg = <<<eos
MSH|^~\&||Amazing Clinical^24D0404292^CLIA|MIDOH|MI|201107280941||ORU^R01|20080320031629921238|P|2.3.1
PID|||246^^^^^Columbia Valley Memorial Hospital&01D0355944&CLIA~95101100001^^^^^MediLabCo-Seattle&45D0470381&CLIA||sara^blaunt^Q^Jr|Clemmons|19900602|F||W|2166WellsDr^AptB^Seattle^WA^98109||^^^^^206^6793240|||M|||423523049||U|N
PV1|1||||||9999^Account^Test^^^^^PHYID||||||||U||||||||||||||||||||||||
ORC|RE||||P
OBR|1||SER122145|^^^78334^Chemistry, serum sodium^meq/l|||201107280941||||||||BLDV|^Welby^M^J^Jr^Dr^MD|^WPN^PH^^^206^4884144||||||||F
OBX|1|NM|2951-2^SODIUM,SERUM^LN^230-007^Na&CLIA|1|141|meq/l|135-146||||F|||||^Doe^John|||||||Oakton Crest Laboratories|5570 Eden Street^^Oakland^California^94607|8006315250|
OBR|2||SER122145|^^^27760-3^POTASSIUM,SERUM^LN^230-006^K^CLIA|1|4.5|meq/l|3.4-5.3|||201107280941
OBX|2|NM|22760-3^POTASSIUM,SERUM^LN^230-006^K^CLIA|1|4.5|meq/l|3.4-5.3|N|||F|||||^Doe^John|||||||Oakton Crest Laboratories|5570 Eden Street^^Oakland^California^94607|8006315250|||201107280941
eos;
$xml = new stdClass();
$xml->credentials = new stdClass();
$xml->credentials->id = 'id';
$xml->credentials->password = 'pw';
$xml->account = new stdClass();
$xml->account->providerId = 'provider';
$xml->message = $msg;
//print_r('<pre>');
//print_r($xml);
$out = $client->postMessage($xml);
print_r($out);
