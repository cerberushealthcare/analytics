<?php
require_once 'php/data/curl/GetQuery.php';
//
class InfoButtonQuery extends GetQuery {
  //
  public $mainSearchCriteria_v_cs;
  public $mainSearchCriteria_v_c;
  public $knowledgeResponseType = 'application/json';
  //
  const CS_ICD9 = '2.16.840.1.113883.6.103';
  const CS_RXCUI = '2.16.840.1.113883.6.88';
  const CS_LOINC = '2.16.840.1.113883.6.1';
  //
  static function /*obj*/searchDiag($icd) {
    return static::create(static::CS_ICD9, $icd)->submit(); 
  }
  static function /*obj*/searchMed($rxcui) {
    return static::create(static::CS_RXCUI, $rxcui)->submit(); 
  }
  static function /*obj*/searchLab($loinc) {
    return static::create(static::CS_LOINC, $loinc)->submit();
  }
  //
  public function submit() {
    $response = parent::submit(static::getUrl());
    $json = $this->parse($response->body);
    return $json;
  }
  //
  static function create($cs, $c) {
    $me = new static();
    $me->mainSearchCriteria_v_cs = $cs;
    $me->mainSearchCriteria_v_c = $c;
    return $me;
  }
  protected static function getUrl() {
    return 'http://apps2.nlm.nih.gov/medlineplus/services/mpconnect_service.cfm';
  }
  protected function getFormFid($fid) {
    return str_replace('_', '.', $fid);
  }
  protected function parse($body) {
    $o = jsondecode($body);
    $this->flatten($o->feed);
    return $o->feed;
  }
  protected function flatten(&$obj) {
    if ($obj) {
      foreach ($obj as $fid => $e) {
        if (is_object($e)) {
          if (! is_null(get($e, '_value')))
          	$obj->$fid = $e->_value;
          else
            $this->flatten($e);
        } else if (is_array($e)) {
          $this->flatten($e);
        }
      }
    }
  }
}
/**
 * Example output
 * stdClass Object
(
    [xsi] => http://www.w3.org/2001/XMLSchema-instance
    [base] => http://www.nlm.nih.gov/medlineplus/
    [lang] => en
    [title] => MedlinePlus Connect
    [subtitle] => MedlinePlus Connect results for ICD-9-CM 272.1
    [author] => stdClass Object
        (
            [name] => National Library of Medicine
            [uri] => http://www.nlm.nih.gov
        )

    [updated] => 2014-10-11T17:10:12Z
    [category] => Array
        (
            [0] => stdClass Object
                (
                    [scheme] => mainSearchCriteria.v.c
                    [term] => 272.1
                )

            [1] => stdClass Object
                (
                    [scheme] => mainSearchCriteria.v.cs
                    [term] => 2.16.840.1.113883.6.103
                )

            [2] => stdClass Object
                (
                    [scheme] => mainSearchCriteria.v.dn
                    [term] => 
                )

            [3] => stdClass Object
                (
                    [scheme] => InformationRecipient
                    [term] => PAT
                )

        )

    [id] => 
    [entry] => Array
        (
            [0] => stdClass Object
                (
                    [lang] => en
                    [title] => Cholesterol
                    [link] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [title] => Cholesterol
                                    [rel] => alternate
                                    [type] => html
                                    [href] => http://www.nlm.nih.gov/medlineplus/cholesterol.html
                                )

                        )

                    [id] => tag: nlm.nih.gov, 2014-11-10:/medlineplus/cholesterol.html
                    [updated] => 2014-10-11T17:10:12Z
                    [summary] => 
Also called:   HDL, Hypercholesterolemia, Hyperlipidemia, Hyperlipoproteinemia, LDL

Cholesterol is a waxy, fat-like substance that occurs naturally in all parts of the body. Your body needs some cholesterol to work properly. But if you have too much in your blood, it can combine with other substances in the blood and stick to the walls of your arteries. This is called plaque. Plaque can narrow your arteries or even block them.

High levels of cholesterol in the blood can increase your risk of heart disease. Your cholesterol levels tend to rise as you get older. There are usually no signs or symptoms that you have high blood cholesterol, but it can be detected with a blood test. You are likely to have high cholesterol if members of your family have it, if you are overweight or if you eat a lot of fatty foods.

You can lower your cholesterol by exercising more and eating more fruits and vegetables. You also may need to take medicine to lower your cholesterol.

NIH: National Heart, Lung, and Blood Institute

 
Apolipoprotein B100
Apolipoprotein CII
Cholesterol - drug treatment
Cholesterol and lifestyle
Familial combined hyperlipidemia
Familial dysbetalipoproteinemia
Familial hypercholesterolemia
High blood cholesterol levels
How to take statins
Talk with Your Health Care Provider about High Cholesterol (Agency for Healthcare Research and Quality) - PDF
Understanding cholesterol results
VLDL test

                )

        )

)
 */