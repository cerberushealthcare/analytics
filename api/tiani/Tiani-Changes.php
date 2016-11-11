<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TianiCreatePatient
 *
 * @author Rob Thamer
 */
class Tiani {

//put your code here

    public $host;
    public $stateID;
    public $domainID;
    public $adt;

    function Tiani($host="10.10.20.101:8181", $stateID=null, $domainID=null, $adt=null) {
        $this->host = $host;
    }
    public function retrievePatientDocumentList($patient, $provider){
        $returnval=array();
        $patientResponse = $this->managePatientRecord($patient, $provider);
        $response = $this->queryDoc();
        if ($response->return->responseData != null)
          $this->retrieveAllDocumentsList($response->return->responseData, $returnval);



   
        return $returnval;

    }
    public function retrievePatientDocument($patient, $provider, $document){

$patientResponse = $this->managePatientRecord($patient, $provider);
        
     return($this->retrieveDocument($document));
    }

    public function managePatientRecord($patient, $provider) {
//Login
        if ($this->stateID == null) {
            $this->Login($provider->username, $provider->password, $provider->org);
            $this->LoginOrg($provider->org, $provider->role);
            $this->getADTInfo();
        }
//Search via PID - attempting to get around name change issues

        $patRes = $this->queryPatient($patient);
        var_dump($patRes);
        if ($this->patientExists($patRes)) {
//Update
            $response = $this->updatePatient($patient, $patRes);
        } else {
            $demoOnlyRes = $this->queryPatient($patient, false);
            if (is_array($demoOnlyRes->return->responseData))
                throw new Exception("Potential duplicate patient.  Refer to logs for error");
            else if ($this->patientExists($demoOnlyRes))
//Update
                $response = $this->updatePatient($patient, $demoOnlyRes);
            else {
//Create
                $response = $this->createPatient($patient);
            }
        }

        return $response;
    }


    public function submitCCRDocument($data, $patient, $provider) {

        $patientResponse = $this->managePatientRecord($patient, $provider);
        $uploadResponse = $this->uploadDoc($data);

        $patient = $patientResponse->return->responseData;
//TESTING ONLY
// $this->adt->
        $docQuery = $this->queryDoc();
        if (strpos($docQuery->return->responseDetail->listWarning, "no XdsQuery performed")) {
            $this->submitDoc($uploadResponse, $patient, $provider, "CCR");
        }
//Iterate
        else {
            $docs = array();
            $olddoc = null;
            $docNumber = -1;
            $counter = 0;
//Pass by reference due to recursion`
            $this->retrieveAllDocumentsList($docQuery->return->responseData, $docs);
            foreach ($docs as $doc) {
                if ($doc->authorPerson == $provider->firstName . " " . $provider->lastName && !is_bool(strpos($doc->description, "CCR"))) {
                    $docNumber = $counter;
                }
                $counter++;
            }

            if ($docNumber == -1) {
//Create
                $this->submitDoc($uploadResponse, $patient, $provider, "CCR");
            } else {
//Update
                $this->submitDoc($uploadResponse, $patient, $provider, $folderName, true, $docs[$docNumber], false);
            }
        }
    }

    public function submitReferralDocument($data, $patient, $provider) {

        $patientResponse = $this->managePatientRecord($patient, $provider);

        if (is_array($data)) {
            $uploadResponse = array();
            foreach ($data as $elem)
                array_push($uploadResponse, $this->uploadDoc($elem));
        }else
            $uploadResponse = $this->uploadDoc($data);
        $patient = $patientResponse->return->responseData;
//TESTING ONLY

        $this->submitDoc($uploadResponse, $patient, $provider, "Referral", false, null, true);

//Iterate
    }

    private function patientExists($patResp) {

        if ($patResp->return->responseData != null)
            return true;
        else
            return false;
    }

    private function Login($username, $password, $organization) {

        $params->arg0->user->uid = $username;
        $params->arg0->user->userPassword = $password;
//org will need to be in a configuration store
        $params->arg0->user->loginOrganisation->organisationDN = "o=" . $organization . ",o=spirit,o=at";
        $params->arg0->user->loginOrganisation->organisationName = $organization;
        $params->arg0->dataMap->entry->value->data = "";
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSLogin?wsdl");
        $response = $client->usrLogin($params);
        if (isset($fault)) {
            print "Error: " . $fault;
        }
        $this->stateID = $response->return->stateID;
    }

    private function Logout() {

        $params->arg0->stateID = $this->stateID;
//org will need to be in a configuration store
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSLogin?wsdl");
        $response = $client->usrLogout($params);
        if (isset($fault)) {
            print "Error: " . $fault;
        }
    }

    private function LoginOrg($organization, $role) {
        $params->arg0->stateID = $this->stateID;
//org will need to be in a configuration store
        $params->arg0->user->loginOrganisation->organisationDN = "o=" . $organization . ",o=spirit,c=at";
        $params->arg0->user->loginOrganisation->organisationName = $organization;
        $params->arg0->user->loginRole = $role;
        $params->arg0->dataMap->entry->value->data = "";
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSLogin?wsdl");
        $response = $client->loginOrganisationRole($params);
    }

    private function queryPatient($patient, $pidOnly=true) {
        $params->arg0->stateID = $this->stateID;
        $params->arg0->xcpd = "";
        if (!$pidOnly) {

            $params->arg0->requestData->birthdate = $patient->birth . "T";
            $params->arg0->requestData->sex = $patient->gender;
            $params->arg0->requestData->familyName = $patient->lastName;
            $params->arg0->requestData->givenName = $patient->firstName;
        } else {
            if ($this->adt != null)
                $params->arg0->requestData->pid = $this->adt;
            $params->arg0->requestData->pid->patientID = $patient->patientId;
            var_dump($params->arg0->requestData);
        }

        /* $params->requestData->pid->domain->authUniversalID = $info->domainId;
          $params->requestData->pid->domain->authUniversalIDType = "ISO";
          $params->requestData->pid->ehrPIDType = "6";
          $params->requestData->pid->patientID = $info->patientID;
          $params->requestData->pid->patientIDType = "RRI";
         *
         */
         $params->arg0->dataMap->entry->value->data = "";
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSPdqCons?wsdl");
        try{
        $response = $client->queryPatients($params);
        }catch(SoapFault $e)
        {
            echo $e->faultcode;
        }

        

        return($response);
    }

    private function getADTInfo() {

        $params->arg0->stateID = $this->stateID;
        $params->arg0->dataMap->entry->value->data = "";
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSAdtSrc?wsdl");
        $response = $client->getAdtInfo($params);

        $parsedResponse->domain->authUniversalID = $response->return->locWorkDom->code;
        $parsedResponse->domain->authUniversalIDType = $response->return->locWorkDom->classCode;
        $parsedResponse->ehrPIDType = "6";
        $parsedResponse->patientIDType = "RRI";

        $this->adt = $parsedResponse;

        /*   <locWorkDom>
          <classCode>ISO</classCode>
          <classification>1000.901.1.1.3.1</classification>
          <code>2.16.17.710.778.1000.901.1.1.3.1</code>
          <codingScheme>PRIM_ID</codingScheme>
          <display>Org1 RRI</display>
          </locWorkDom>


          <pid>
          <!--Optional:-->
          <domain>
          <!--Optional:-->
          <!--Optional:-->
          <authUniversalID>2.16.17.710.778.1000.901.1.1.3.1</authUniversalID>
          <!--Optional:-->
          <authUniversalIDType>ISO</authUniversalIDType>
          </domain>
          <!--Optional:-->
          <ehrPIDType>6</ehrPIDType>
          <!--Optional:-->
          <patientID>010656</patientID>
          <!--Optional:-->
          <patientIDType>RRI</patientIDType>
          </pid > */
    }

    private function buildPatientRequest($patient, $queryRequest=null) {
        if ($queryRequest != null)
            $params->arg0->requestData = $queryRequest->return->responseData;
        $params->arg0->requestData->birthdate = date("Y-m-d",strtotime($patient->birth));
        $params->arg0->requestData->sex = $patient->gender;

        $params->arg0->requestData->familyName = $patient->lastName;
        $params->arg0->requestData->givenName = $patient->firstName;
        $params->arg0->requestData->homePhone = $patient->_addressPrimary->phone;

        $params->arg0->requestData->state = $patient->_addressPrimary->state;
        $params->arg0->requestData->streetAddress = trim($patient->_addressPrimary->addr1 . " " . $patient->_addressPrimary->addr2 . " " . $patient->_addressPrimary->addr3);
        $params->arg0->requestData->zip = $patient->_addressPrimary->zip;
        $params->arg0->requestData->city = $patient->_addressPrimary->city;
        $params->arg0->requestData->country = $patient->_addressPrimary->country;
        $params->arg0->requestData->emailHome = $patient->_addressPrimary->email;
        $params->arg0->requestData->businessPhone = $patient->_addressPrimary->phoneCell;
        $params->arg0->requestData->secondCity = $patient->_addressEmergency->city;
        $params->arg0->requestData->secondCountry = $patient->_addressEmergency->country;
        $params->arg0->requestData->secondState = $patient->_addressEmergency->state;
        $params->arg0->requestData->secondStreetAddress = trim($patient->_addressEmergency->addr1 . " " . $patient->_addressEmergency->addr2 . " " . $patient->_addressEmergency->addr3);
        $params->arg0->requestData->secondZip = $patient->_addressEmergency->zip;

        return $params;

        /*    <!--Optional:-->
          <deathIndicator />
          <!--Optional:-->
          <deathdate />
          <!--Optional:-->
          <degree />
          <!--Optional:-->
          <dominant />
          <!--Optional:-->
          <drivingLicense />
          <!--Optional:-->
          <duplicateType />
          <!--Optional:-->
          <emailBusiness />
          <!--Optional:-->
          <!--Optional:-->
          <ethnicGroup />
          <!--Optional:-->


          <!--Optional:-->
          <language />
          <!--Optional:-->
          <lastUpdtateTime />
          <!--Optional:-->
          <maritalStatus />
          <!--Optional:-->
          <matchScore />
          <!--Optional:-->
          <middleName />
          <!--Optional:-->
          <militaryStatus />
          <!--Optional:-->
          <motherFamilyName />
          <!--Optional:-->
          <motherGivenName />
          <!--Optional:-->
          <motherID />
          <!--Optional:-->
          <motherMiddleName />
          <!--Optional:-->
          <motherNamePrefix />
          <!--Optional:-->
          <motherNameSuffix />
          <!--Optional:-->
          <multipleBirth />
          <!--Optional:-->
          <namePrefix>Mr.</namePrefix>
          <!--Optional:-->
          <nameSuffix />
          <!--Optional:-->
          <nameTypeCode />
          <!--Optional:-->
          <nationality />
          <!--Zero or more repetitions:-->

          <!--Optional:-->
          <pk />
          <!--Optional:-->

          <religion />
          <!--Optional:-->

          <!--Optional:-->
          <secondDegree />
          <!--Optional:-->
          <secondFamilyName />
          <!--Optional:-->
          <secondGivenName />
          <secondMiddleName />
          <!--Optional:-->
          <secondNamePrefix />
          <!--Optional:-->
          <secondNameSuffix />
          <!--Optional:-->
          <secondNameTypeCode />
          <!--Optional:-->

          <!--Optional:-->

          <!--Optional:-->
          <ssnNumber />

          </requestData>
         */
    }

    private function createPatient($patient) {

        $params = $this->buildPatientRequest($patient);
        $params->arg0->stateID = $this->stateID;
        $params->arg0->xcpd = "";
        $this->adt->patientID = $patient->patientId;
        $params->arg0->requestData->pid = $this->adt;
        $params->arg0->dataMap->entry->value->data = "";
        var_dump($params);
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSAdtSrc?wsdl");
        $response = $client->insertPatient($params);
        var_dump($response);
        return($response);
    }

    private function updatePatient($patient, $queryRequest) {


        $params = $this->buildPatientRequest($patient, $queryRequest);
        $params->arg0->stateID = $this->stateID;
        $params->arg0->xcpd = "";
        $this->adt->patientID = $patient->patientId;

        $params->arg0->requestData->pid = $this->adt;
        $params->arg0->dataMap->entry->value->data = "";
        var_dump($params);
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSAdtSrc?wsdl");
        $response = $client->updatePatient($params);
        var_dump($response);
        return($response);
    }

    private function retrieveAllDocumentsList($docResSet, &$returnval) {


        foreach ($docResSet as $docs)
//Single docs and folders at level, non-arrays
            if (is_array($docs))
                foreach ($docs as $d) {
                    $this->documentIdentify($d, $returnval);
                } else {

                $this->documentIdentify($docs, $returnval);
            }
    }

    private function documentIdentify($d, &$returnval) {
        if ($d instanceOf stdClass && $d->loaderUri != null && $d->childDocuments == null) {
            echo("Here");
            array_push($returnval, $d);
        } else if (($d instanceOf stdClass && is_bool($d->createFolder)) || $d->childDocuments != null) {
            echo("Here");
            $this->retrieveAllDocumentsList($d, $returnval);
        }
    }

    private function retrieveDocument($document) {
        $params->arg0->stateID = $this->stateID;
        $params->arg0->dataMap->entry->value->data = "";
//NEED TO LOOK AT BASE XMLDOC name
        $params->arg0->requestData = $document;
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSXdsCons?wsdl");
        $response = $client->retrieveDocument($params);
        return($response);
    }


    private function queryDoc() {
        $params->arg0->stateID = $this->stateID;
//NEED TO LOOK AT BASE XMLDOC name
        $params->arg0->requestData = $this->adt;
        $params->arg0->dataMap->entry->value->data = "";
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSXdsCons?wsdl");
        $response = $client->queryDocuments($params);
        return($response);
    }

    private function uploadDoc($blob) {
        $params->arg0->stateID = $this->stateID;
        $params->arg0->document = $blob;
$params->arg0->dataMap->entry->value->data = "";
        $option = array('trace' => 1);
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSXdsSrc?wsdl", $option);
        $response = $client->uploadFile($params);
        var_dump($client->__getLastRequest());
        return($response);
    }

    private function submitDoc($uploadResponse, $patient, $provider, $folderName, $overwrite = false, $originalDoc = null, $createFolder = true) {
        $params->arg0->stateID = $this->stateID;
$params->arg0->dataMap->entry->value->data = "";
//patient
        $params->arg0->patient = $patient;
//Need to define metadata from clicktate
        if (!is_array($uploadResponse)) {
            $ur = array();
            $ur[0] = $uploadResponse;

            $uploadResponse = $ur;
        }

        $counter = 0;
        foreach ($uploadResponse as $ur) {
            $mimeType = $uploadResponse[$counter]->return->mimeType;
            if ($mimeType == null)
                throw new Exception("Unknown Doc type error");
            $params->arg0->srcSubmission->documents[$counter]->authorPerson = $provider->firstName . " " . $provider->lastName;
            /* $params->arg0->srcSubmission->documents[$counter]->authorDepartment = $provider->department;
              $params->arg0->srcSubmission->documents[$counter]->authorRole = "";
              $params->arg0->srcSubmission->documents[$counter]->authorInstitution = "";
              $params->arg0->srcSubmission->documents[$counter]->authorSpeciality = ""; */


            $params->arg0->srcSubmission->documents[$counter]->classCode->nodeRepresentation
            = "60591-5";
            $params->arg0->srcSubmission->documents[$counter]->classCode->schema = "2.16.840.1.113883.6.1";
            $params->arg0->srcSubmission->documents[$counter]->classCode->value
            = "Patient Summary";
            $params->arg0->srcSubmission->documents[$counter]->confidentialityCode->nodeRepresentation = "n";

            $params->arg0->srcSubmission->documents[$counter]->confidentialityCode->schema = "Connect-a-thon confidentialityCodes";
            $params->arg0->srcSubmission->documents[$counter]->confidentialityCode->value
            = "Normal";
            $params->arg0->srcSubmission->documents[$counter]->practiceSettingCode->nodeRepresentation = $provider->department;
            $params->arg0->srcSubmission->documents[$counter]->practiceSettingCode->schema = "Connect-a-thon practiceSettingCodes";
            $params->arg0->srcSubmission->documents[$counter]->practiceSettingCode->value = $provider->department;
            $params->arg0->srcSubmission->documents[$counter]->typeCode->nodeRepresentation = "60591-5";
            $params->arg0->srcSubmission->documents[$counter]->typeCode->schema = "2.16.840.1.113883.6.1";
            $params->arg0->srcSubmission->documents[$counter]->typeCode->value
            = "Patient Summary";
            $params->arg0->srcSubmission->documents[$counter]->languageCode = "EN-US";

          //  $params->arg0->srcSubmission->documents[$counter]->healthcareFacilityCode->nodeRepresentation = "C";
          //  $params->arg0->srcSubmission->documents[$counter]->healthcareFacilityCode->schema = "Connect-a-thon confidentialityCodes";
           // $params->arg0->srcSubmission->documents[$counter]->healthcareFacilityCode->value = "Celebrity";
            $params->arg0->srcSubmission->documents[$counter]->loaderUri = $uploadResponse[$counter]->return->fileID;
            $params->arg0->srcSubmission->documents[$counter]->mimeType = $mimeType;
            $params->arg0->srcSubmission->documents[$counter]->patientId = $this->adt->patientID;
            if ($mimeType == "application/pdf") {

                $params->arg0->srcSubmission->documents[$counter]->formatCode->nodeRepresentation = "PDF/IHE 1.x";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->schema = "Connect-a-thon formatCodes";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->value = "PDF/IHE 1.x";
                $params->arg0->srcSubmission->documents[$counter]->name = "Referral Document";
            } else if ($mimeType == "image/jpeg") {

                $params->arg0->srcSubmission->documents[$counter]->formatCode->nodeRepresentation = "Generic Image";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->schema = "Connect-a-thon formatCodes";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->value = "Generic Image";
                $params->arg0->srcSubmission->documents[$counter]->name = "Image Document";
            } else if ($mimeType == "text/xml") {
                $params->arg0->srcSubmission->documents[$counter]->formatCode->nodeRepresentation= "urn:ihe:iti:xds-sd:pdf:2008";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->value="Scanned Documents PDF";
                $params->arg0->srcSubmission->documents[$counter]->formatCode->schema = "1.3.6.1.4.1.19376.1.2.3";
                $params->arg0->srcSubmission->documents[$counter]->description = "Patient Summary";
                $params->arg0->srcSubmission->documents[$counter]->name = "Patient Summary";
            }
            $counter++;
        }
        $params->arg0->srcSubmission->submissionSet->authorPerson = $provider->firstName . " " . $provider->lastName;
        $params->arg0->srcSubmission->submissionSet->contentTypeCode->nodeRepresentation = "Communication";
        $params->arg0->srcSubmission->submissionSet->contentTypeCode->schema = "Connect-a-thon contentTypeCodes";
        $params->arg0->srcSubmission->submissionSet->contentTypeCode->value = "Communication";
        $params->arg0->srcSubmission->submissionSet->description = "CCR/CCD upload";
        $params->arg0->srcSubmission->folder->createFolder = $createFolder;
        $params->arg0->srcSubmission->folder->name = $folderName;


        if ($mimeType == "application/pdf") {


            $params->arg0->srcSubmission->folder->codeList->nodeRepresentation = "34140-4";
            $params->arg0->srcSubmission->folder->codeList->value = "Referral Notes";
            $params->arg0->srcSubmission->folder->codeList->schema = "LOINC";
        } else if ($mimeType == "image/jpeg") {
            $params->arg0->srcSubmission->folder->codeList->nodeRepresentation = "34140-4";
            $params->arg0->srcSubmission->folder->codeList->value = "Referral Notes";
            $params->arg0->srcSubmission->folder->codeList->schema = "LOINC";
        } else if ($mimeType == "text/xml") {
 //           $params->arg0->srcSubmission->folder->codeList->nodeRepresentation = "51900-9";
 //           $params->arg0->srcSubmission->folder->codeList->value = "Continuity of Care";
 //           $params->arg0->srcSubmission->folder->codeList->schema = "LOINC";
            $params->arg0->srcSubmission->folder->codeList->nodeRepresentation = "60591-5";
            $params->arg0->srcSubmission->folder->codeList->value = "epSOS Patient Summary";
            $params->arg0->srcSubmission->folder->codeList->schema = "LOINC";
        }



        $option = array('trace' => 1);
        $client = new SoapClient("http://" . $this->host . "/SpiritEhrWsRemoting/EhrWSXdsSrc?wsdl", $option);

        if ($overwrite == false)
            try {
                $response = $client->submit($params);
            } catch (SoapFault $fault) {
// <xmp> tag displays xml output in html
                echo 'Request : <br/><xmp>', $client->__getLastRequest(), '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage();
            } else {

            $params->arg0->srcSubmission->folder = null;
            $params->arg0->oldDocument = $originalDoc;

            $params->arg0->srcSubmission->documents->patientId = $originalDoc->patientId;

            try {
                $response = $client->replace($params);
            } catch (SoapFault $fault) {
                // <xmp> tag displays xml output in html
                echo 'Request : <br/><xmp>', $client->__getLastRequest(), '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage();
            }
        }

        return($response);
    }

}

?>
