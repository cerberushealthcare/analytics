<?php
abstract class SoapRec extends DomData {
  //
  abstract protected static function getUrl($host);
  //
  protected static function getUpdate1Url($host) {
    return "$host/Update1.asmx?WSDL";
  }
  //
  protected static function fromArray($recs, $class) {
    if ($recs) 
      foreach ($recs as &$rec) 
        $rec = new $class($rec);
    return $recs;
  }
  protected static function call($url, $param) {
    $soap = self::getSoapClient($url);
    $method = get_class($param);
    $response = $soap->$method($param);
    return $response;
  }
  protected static function getSoapClient($url) {
    $orig = error_reporting();
    error_reporting(0);
    $soap = new SoapClient($url);
    error_reporting($orig);
    if ($soap) 
      return $soap;
    else
      throw new Exception('Unable to create SOAP client');
  }
  /*
   * Extract response objects from NewCrop SOAP return:
   *   <GetPatientAllergyHistoryResponse>  // $responseNodeName
   *     <result>                          // use for $responseNodeName if $useXmlResponse=true 
   *       <Status>OK</Status>
   *       <XmlResponse>..</XmlResponse>   // $useXmlResponse=true to get from here 
   *       ..
   *     </result>
   *     <patientAllergyDetail>            // $detailNodeName
   *       <PatientAllergyDetail>          // $arrayNodeName, if you want to return an array of these
   *         ..
   *       </PatientAllergyDetail>
   *     </patientAllergyDetail>
   */
  protected static function getResponseRecs($response, $responseNodeName, $detailNodeName, $arrayNodeName = null, $useXmlResponse = false) {
    $return = null;
    if ($response != null) {
      $response = $response->$responseNodeName;
      $result = ($useXmlResponse) ? $response : $response->result;
      SoapResultException::throwIfNotOk($result);
      if ($useXmlResponse) {
        $xmlResponse = DomData::parseXml(base64_decode($result->XmlResponse));
        $detail = get($xmlResponse, $detailNodeName);                
      } else {
        $detail = get($response, $detailNodeName);
      }
      if ($detail) {
        if ($arrayNodeName == null) 
          $return = $detail;
        else {
          $records = get($detail, $arrayNodeName);
          $return = (is_array($records)) ? $records : array($records);
        }
      }
    } 
    return $return;
  }
}
?>