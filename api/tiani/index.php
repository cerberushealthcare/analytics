<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        require_once('./Tiani.php');
        require_once('./ePrescribe.php');

        //$newClass = new ePrescribe();
      $newClass = new Tiani();
       /*   //Test patient
     */
       
       $patient->practiceId ="";
  $patient->patientId = "23432432332";
  $patient->patientCode ="";
  $patient->lastName="Jonesmany";
  $patient->firstName="Womanin";
  $patient->gender="F";
  $patient->birth="12/05/1945";

  $patient->active="true";
       
       // $patient->patientId = "DEMOPT1";
        //Test Doctor

        $provider->username = "Physician1";
        $provider->password = "Physician1";
        $provider->firstName = "Physicianette";
        $provider->lastName = "Two";
        $provider->department = "Endocrinology";
        $provider->org = "Org1";
        $provider->role = "Physician";

        $binary[0] = fread(fopen("./ExampleXML.xml", "r"), filesize("./ExampleXML.xml"));
        $binary[1] = fread(fopen("./ExampleXML.xml", "r"), filesize("./ExampleXML.xml"));
       
       // $newClass->submitCCRDocument($binary[0], $patient, $provider);
        $returnval = $newClass->retrievePatientDocumentList($patient, $provider);
       var_dump($newClass->retrievePatientDocument($patient, $provider, $returnval[0]));
        ?>
    </body>
</html>
