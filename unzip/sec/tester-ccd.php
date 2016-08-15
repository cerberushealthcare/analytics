<?php
require_once 'php/data/LoginSession.php';
//
LoginSession::verify_forServer();
//
$cid = 1695;
$cid = 1658;
require_once 'php/data/rec/sql/Facesheets.php';
require_once 'php/data/file/client-pdf/FacesheetPdf.php';
$fs = Facesheet_Complete::fetch($cid);
$pdf = FacesheetPdf::create($fs);
$pdf->download();
exit;

p_r($fs);
echo FacesheetHtml::create($fs)->out();

exit;
//

require_once 'php/data/rec/sql/HL7_ClinicalDocuments.php';
require_once 'php/data/xml/ClinicalXmls.php';

require_once 'php/dao/DataDao.php';
require_once 'php/data/json/JDataSyncFamGroup.php';
$recs = DataDao::fetchDataSyncGroup(JDataSyncGroup::GROUP_SOCHX, $cid);
p_r($recs);
$recs = DataDao::fetchDataSyncProcGroup(JDataSyncProcGroup::CAT_MED, $cid);
p_r($recs);
$recs = DataDao::fetchDataSyncFamGroup(JDataSyncFamGroup::SUID_FAM, $cid, false);
p_r($recs);


require_once 'php/data/rec/sql/_DataHxRecs.php';
$recs = SocHx::fetchAll($cid);
p_r($recs);
$recs = MedHx::fetchAll($cid);
p_r($recs);
$recs = FamHx::fetchAll($cid);
p_r($recs);
exit;

$ccd = HL7_ClinicalDocuments::buildFull($cid);
$xml = ClinicalXmls::parse($ccd->toXml(true));
p_r($xml);
