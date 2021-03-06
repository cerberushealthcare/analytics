<?php
require_once 'php/pdf/mpdf/mpdf.php';
//
$html = <<<eos
<DIV id=head><P>X, X&nbsp;<BR/> 2624195&nbsp;<BR/> 08/22/1960&nbsp;<BR/> Heather Nobles PA-S&nbsp;<BR/> Baptist Internal Medicine and Pediatrics&nbsp;<BR/> 01/16/2013&nbsp;</P></DIV><HR/><DIV id=body><P><B><U>History of Present Illness</U></B></P><P>The patient present for a pre-operative examination prior to elective non-cardiovascular surgery. The specific surgery to be performed is a left total hip replacement to be performed using general anesthesia. He denies prior reactions to anesthesia. The patient does not have a history of coronary artery disease. In the past two years, he has had no prior cardiac testing. He denies a history of asthma. He denies a history of COPD. He does not smoke.</P><P>As relates to hypertension, the patient reports that he has had no headaches, chest pain, dyspnea, edema, syncope, blurred vision or palpitations. He is following a heart healthy diet. He does not exercise regularly. He states that he is taking his medication as prescribed. He is not having medication side effects. He does not check his blood pressures at home.</P><P>The patient presents for follow-up of hyperlipidemia. He is following a low fat diet. He reports that he is not exercising. He is taking Lipitor. The patient is taking his medication as prescribed. He reports no medication side effects, including muscle cramps, muscle inflammation, yellow skin and eyes, abdominal pain, headaches or weakness. He denies orthopnea, paroxysmal nocturnal dyspnea or dyspnea on exertion.</P><P>The patient is on Prevacid for his gastroesophageal reflux. The medication is taken on a regular basis and gives complete relief of the symptoms. He has not elevated the headboard of his bed or tried sleeping on multiple pillows. He reports no abdominal pain, belching, diarrhea, dysphagia, early satiety, heartburn, hoarseness, nausea, odynophagia, rectal bleeding, vomiting or weight loss. The GERD has no known aggravating factors.</P><P>The patient denies nocturia, decreased urine stream or painful urination. The patient is currently taking no medication for his BPH. The patient has not had treatment by a urologist. He does not have a family history of prostate cancer.</P><P><B><U>Past Medical History</U></B></P><P>The past medical history is significant for Hyperlipidemia, Hypertension and GERD. &nbsp;<BR/> &nbsp;</P><P>Past Surgical History&nbsp;<BR/>Hip Replacement Right (Jul 2005)&nbsp;<BR/>Wisdom Teeth Extraction (1978)&nbsp;<BR/>Right hip pinning (1974). &nbsp;<BR/>&nbsp;</P><P><B><U>Medications</U></B></P><P>Current medications include-<BR/>- amlodipine-benazepril 10 mg-40 mg capsule (TAKE 1 CAPSULE DAILY)<BR/>- atorvastatin 40 mg tablet (TAKE 1 TABLET AT BEDTIME)<BR/>- hydrochlorothiazide 50 mg tablet (TAKE 1 TABLET DAILY)<BR/>- lansoprazole 30 mg capsule,delayed release (TAKE 1 CAPSULE DAILY)<BR/>- metoprolol succinate ER 200 mg tablet,extended release 24 hr (TAKE 1 TABLET DAILY)</P><P><B><U>Allergies</U></B></P><P>Reviewed per Nurses Note</P><P><B><U>Family History</U></B></P><P>Family History: Father, Mother and 2 Brothers.</P><P>Mother: Living. Current Age: 80-85. Health Problems: None- Healthy.</P><P>Father: Deceased. Age of Death: 60-65. Health Problems: Malignancy.</P><P>Brother: Living. Current Age: 45-50. Health Problems: None- Healthy.</P><P>Brother: Living. Current Age: 40-45. Health Problems: None- Healthy.</P><P>There is no family history of anesthesia complications.</P><P><B><U>PsychoSocial History</U></B></P><P>Household Members: 1 Daughter, 1 Son and Wife.</P><P>Marital Status: Married.</P><P>Education Obtained: Some College.</P><P>Occupation: Design Tech. &nbsp;<BR/>&nbsp;</P><P>Religious Services: Attends. &nbsp;<BR/> Belief Description: Protestant. &nbsp;<BR/>&nbsp;</P><P>Seatbelt Usage: Yes. &nbsp;<BR/>&nbsp;</P><P>Tobacco Usage: Does Not Smoke. &nbsp;<BR/> Non-Smoking Status: Never Smoked. &nbsp;<BR/> Smokeless Tobacco: does not use.</P><P>Alcohol Usage: Does not drink alcohol. &nbsp;</P><P><B><U>Physical Examination</U></B></P><P>Vitals: Pulse: Pulse 80. Resp: 18. BP: 132 / 82 LUE. Temp: 98.8 Transcutaneous. Weight: 301 Pounds. Height: 71 inches. Body Mass Index: 42.</P><P>HEENT: Head- Normocephalic Atraumatic. Facies- Within normal limits. Pinnas- Normal texture and shape bilaterally. Canals- Normal bilaterally. TMs- Normal bilaterally. Nares- Patent bilaterally. Nasal Septum- is normal. There is no tenderness to palpation over the frontal or maxillary sinuses. Lids- Normal bilaterally. Conjunctiva- Clear bilaterally. Sclera- Anicteric bilaterally. Oropharynx- Moist with no lesions. Tonsils- No enlargement, erythema or exudate.</P><P>Neck: Thyroid- non enlarged, symmetric and has no nodules. No bruits are detected.</P><P>Lymph Nodes: Cervical- no enlarged lymph nodes noted. Clavicular- Deferred. Axillary- Deferred. Inguinal- Deferred.</P><P>Lungs: Auscultation- Clear to auscultation bilaterally. There are no retractions, clubbing or cyanosis. The Expiratory to Inspiratory ratio is equal.</P><P>Cardiovascular: There are no carotid bruits. Heart- Normal Rate with Regular rhythm and no murmurs. There are no gallops. There are no rubs. In the lower extremities there is no edema. The upper extremities do not have edema.</P><P>Abdomen: Soft, benign, non-tender with no masses, hernias, organomegaly or scars.</P><P><B><U>Laboratory Data</U></B></P><P><TABLE nobr="true" border=1><TBODY><TR><TH align=center colSpan=5 bgcolor=#A0A0A0><B>Lipid Profile</B></TH></TR><TR><TH align=center bgcolor=#A0A0A0><B>DATE</B></TH><TH align=center bgcolor=#A0A0A0><B>Chol</B></TH><TH align=center bgcolor=#A0A0A0><B>HDL</B></TH><TH align=center bgcolor=#A0A0A0><B>LDL</B></TH><TH align=center bgcolor=#A0A0A0><B>Trig</B></TH></TR><TR><TD align=center>01/16/2013</TD><TD align=center>163</TD><TD align=center>37</TD><TD align=center>85</TD><TD align=center>160</TD></TR><TR><TH align=center bgcolor=#A0A0A0><B>Norms</B></TH><TD align=center>< 200</TD><TD align=center>>55</TD><TD align=center><100</TD><TD align=center><150</TD></TR></TBODY></TABLE>&nbsp;</P><P>Urinalysis 01/16/2013: Color- yellow. SpGr- 1.015. pH- 5.0. Leukocyte Esterase- negative. Nitrite- negative. Protein- negative. Glucose- normal. Ketones- negative. Urobilinogen- normal. Bilirubin- negative. Blood- negative.</P><P>EKG 01/16/2013: Normal Sinus Rhythm with Normal Intervals and a Normal Axis. There is no chamber enlargement. There are no ST or T segment changes.</P><P><B><U>Radiology</U></B></P><P>The CXR is normal with no infiltrates, atelectasis, cardiomegaly or effusions.</P><P><B><U>Impression and Assessment</U></B></P><P>Pre-operative evaluation prior to a left total hip replacement.</P><P>Hypertension. (401.9)</P><P>Hyperlipidemia. (272.4)</P><P>Gastroesophageal Reflux Disease. (530.81)</P><P>Benign Prostatic Hypertrophy. (600.00)</P><P><B><U>Plan</U></B></P><P>Pre-operative Evaluation Plan: The patient is an acceptable risk for the proposed procedure under general or local anesthesia. The following tests were ordered: CBC, CMP, PT INR and PTT.</P><P>Gastroesophageal Reflux Disease Plan: The current plan was continued.</P><P>Hypertension Plan: The current plan was continued. The following tests were ordered: EKG and CXR PA and Lateral.</P><P>Hyperlipidemia Plan: The current plan was continued.</P><P>Benign Prostatic Hypertrophy Plan: The condition is stable. No change is needed in the current plan.</P><P></P><P><B><U>Return to Office</U></B></P><P>The patient was instructed to return for follow-up in 6 months. &nbsp;<BR/>The patient was instructed to return sooner if the condition changes, worsens, or doesn't resolve.</P></DIV><DIV id=sig><TABLE border=1><TR><TD align=center><b><i>Digitally Signed:</b></i><br><i>16-Jan-2013, 3:24PM by Michael McKinney, MD (Baptist Internal Medicine and Pediatrics)</i></TD></TR></TABLE></DIV>
eos;
$h = explode('<HR/>', $html);
$htmlHeader = $h[0];
$body = $h[1];
$htmlBody = <<<eos
<html>
<head>
<style>
DIV#head {
  font-size:9pt;
	text-align:right;
	line-height:11pt;
}
BODY {
	font-size:11pt;
  line-height:13pt;
}
DIV {
margin:0;
padding:0;
}
</style>
</head>
<body>
$body
</body>
</html>
eos;
/*
$filename = 'test.pdf';
$author = '';
$title = '';
$font = 'helvetica';
$pdf = new HTMLPDF($font);
$pdf->setDocInfo($author, $title);
$pdf->setHTML($htmlBody, $htmlHeader);
$pdf->Output($filename, 'D');
*/
$pdf = new mPDF('c', 'letter', 0, 'Helvetica', 15, 15, 38, 20);
$pdf->SetDisplayMode('fullpage');
$pdf->SetHtmlHeader($htmlHeader);
$pdf->SetFooter('|Page {PAGENO} of {nb}|');
$pdf->WriteHTML($htmlBody);
$pdf->Output('test.pdf', 'D');
//$pdf->Output();