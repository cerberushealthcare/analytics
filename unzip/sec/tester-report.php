<?php
require_once 'php/data/LoginSession.php';
require_once 'php/c/reporting/Reporting.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php
switch ($_GET['t']) {
  case '1':
    $obj = "{\"userGroupId\":\"1\",\"reportId\":\"97\",\"name\":\"Test NQF\",\"type\":\"1\",\"comment\":null,\"countBy\":\"1\",\"Rec\":{\"uid\":{},\"lastName\":{},\"firstName\":{},\"sex\":{},\"age\":{},\"birth\":{},\"deceased\":{},\"race\":{},\"ethnicity\":{},\"language\":{},\"livingWill\":{},\"poa\":{},\"active\":{},\"Joins\":[{\"jt\":\"1\",\"table\":\"5\",\"Recs\":[{\"ipc\":{\"op\":\"30\",\"value\":\"918089\",\"text_\":\"Colonoscopy\"},\"date\":{},\"cat\":{},\"providerId\":{},\"addrFacility\":{},\"location\":{},\"value\":{},\"userId\":{},\"userGroupId\":{},\"Joins\":[],\"table_\":\"5\"}],\"ct\":1}],\"table_\":\"0\"},\"RecDenom\":null,\"IpcHm\":null,\"recs\":[],\"recsDenom\":[],\"app\":false}";
    $obj = jsondecode($obj);
    $obj->Rec->Joins[0]->Recs[0]->ipc->op = RepCritValue::OP_IN;
    $obj->Rec->Joins[0]->Recs[0]->ipc->value = '918089,917694';
    $obj->Rec->Joins[0]->Recs[0]->ipc->text_ = 'Colonoscopy,CMP';
    $rc = ReportCriteria::revive(1, $obj);
    p_r($rc);
    $rc->load();
    p_r($rc, 'after load');
    exit;
  case '2':
    $obj = "{\"userGroupId\":\"1\",\"reportId\":\"97\",\"name\":\"Test NQF\",\"type\":\"1\",\"comment\":null,\"countBy\":\"1\",\"Rec\":{\"uid\":{},\"lastName\":{},\"firstName\":{},\"sex\":{},\"age\":{},\"birth\":{},\"deceased\":{},\"race\":{},\"ethnicity\":{},\"language\":{},\"livingWill\":{},\"poa\":{},\"active\":{},\"Joins\":[{\"jt\":\"1\",\"ct\":1,\"table\":\"5\",\"Recs\":[{\"ipc\":{\"op\":\"32\",\"value\":\"918089,917694,917682\",\"text_\":\"Colonoscopy,CMP,BMP\"},\"date\":{},\"cat\":{},\"providerId\":{},\"addrFacility\":{},\"location\":{},\"value\":{},\"userId\":{},\"userGroupId\":{},\"Joins\":[],\"table_\":\"5\"}]}],\"table_\":\"0\"},\"RecDenom\":null,\"IpcHm\":null,\"recs\":[],\"recsDenom\":[],\"app\":false}";
    $obj = jsondecode($obj);
    p_r($obj);
    exit;
  case '3':
    $obj = "{\"userGroupId\":\"1\",\"reportId\":\"98\",\"name\":\"aaaa\",\"type\":\"1\",\"comment\":null,\"countBy\":\"1\",\"Rec\":{\"uid\":{},\"lastName\":{},\"firstName\":{},\"sex\":{},\"age\":{},\"birth\":{},\"deceased\":{},\"race\":{},\"ethnicity\":{},\"language\":{},\"livingWill\":{},\"poa\":{},\"active\":{},\"Joins\":[{\"jt\":\"10\",\"table\":\"5\",\"Recs\":[{\"ipc\":{\"op\":\"30\",\"value\":\"917694\",\"text_\":\"CMP\"},\"date\":{},\"cat\":{\"text_\":\"Labs\"},\"providerId\":{},\"addrFacility\":{},\"location\":{},\"value\":{},\"userId\":{},\"userGroupId\":{},\"Joins\":[],\"table_\":\"5\"},{\"icd\":{},\"text\":{\"op\":\"4\",\"value\":\"hypertension\"},\"date\":{},\"dateClosed\":{},\"active\":{},\"status\":{},\"Joins\":[],\"table_\":\"2\"}]}],\"table_\":\"0\"},\"RecDenom\":null,\"IpcHm\":null,\"recs\":[],\"recsDenom\":[],\"app\":false}";
    $obj = jsondecode($obj);
    $rc = ReportCriteria::revive(1, $obj);
    //p_r($rc);
    $rc->load();
    p_r($rc);
    exit;
  case '4':
    $obj = "{\"userGroupId\":\"1\",\"reportId\":\"98\",\"name\":\"aaaa\",\"type\":\"1\",\"comment\":null,\"countBy\":\"1\",\"Rec\":{\"uid\":{},\"lastName\":{},\"firstName\":{},\"sex\":{},\"age\":{},\"birth\":{},\"deceased\":{},\"race\":{},\"ethnicity\":{},\"language\":{},\"livingWill\":{},\"poa\":{},\"active\":{},\"Joins\":[{\"jt\":\"10\",\"table\":\"5\",\"Recs\":[{\"ipc\":{\"op\":\"30\",\"value\":\"917694\",\"text_\":\"CMP\"},\"date\":{},\"cat\":{\"text_\":\"Labs\"},\"providerId\":{},\"addrFacility\":{},\"location\":{},\"value\":{},\"userId\":{},\"userGroupId\":{},\"Joins\":[],\"table_\":\"5\"},{\"icd\":{},\"text\":{\"op\":\"4\",\"value\":\"hypertension\"},\"date\":{},\"dateClosed\":{},\"active\":{},\"status\":{},\"Joins\":[],\"table_\":\"2\"}]}],\"table_\":\"0\"},\"RecDenom\":null,\"IpcHm\":null,\"recs\":[],\"recsDenom\":[],\"app\":false}";
    $obj = jsondecode($obj);
    Reporting::save($obj);
    exit;
  case '5':
    $obj = '{"uid":{},"lastName":{},"firstName":{},"sex":{},"age":{},"birth":{},"deceased":{},"race":{},"ethnicity":{},"language":{},"livingWill":{},"poa":{},"active":{},"Joins":[{"jt":"12","table":"5","Recs":[{"ipc":{"text_":"BMP","value":"917682","op":"30"},"date":{},"cat":{},"providerId":{},"addrFacility":{},"location":{},"value":{},"userId":{},"userGroupId":{},"Joins":[],"table_":"5"},{"ipc":{"text_":"CMP","value":"917694","op":"30"},"date":{},"cat":{},"providerId":{},"addrFacility":{},"location":{},"value":{},"userId":{},"userGroupId":{},"Joins":[],"table_":"5"}]},{"jt":"12","table":"3","Recs":[{"name":{"op":"4","value":"proz"},"active":{},"drugSubclass":{},"Joins":[],"table_":"3"},{"name":{"op":"4","value":"add"},"active":{},"drugSubclass":{},"Joins":[],"table_":"3"}]}],"table_":"0"}';
    $obj = jsondecode($obj);
    p_r($obj);
    exit;
  case '6':
    $id = 98; 
    $r = Reporting::getReport($id);
    p_r($r);
    exit;
}
?>
</html>
