<?php
require_once 'php/data/file/_PdfFile.php';
require_once 'php/data/LoginSession.php';
//
LoginSession::verify_forServer();
class MyPdf extends PdfFile {
  //
  static $FILENAME = 'frodo.pdf';
  //
  static function create() {
    $head = <<<eos
<DIV id=head>
<P>Foulk, Carmela&nbsp;<BR/> 02664&nbsp;<BR/> 08/21/1929&nbsp;<BR/> Terri L Snyder, NP&nbsp;<BR/> Hal Meadows, M.D. Family Practice&nbsp;<BR/> 04/14/2013&nbsp;
</P>
</DIV>
eos;
    $body = <<<eos
<DIV id=body><P><B><U>History of Present Illness</U></B></P><P>The patient presents with a 6 day history of a dry cough. There are no other associated symptoms, including wheezing, fever, facial pain, a headache, eye drainage, ear pain, ear drainage, nasal congestion, rhinorrhea, nausea, vomiting, diarrhea, chills, decreased appetite, abdominal pain, sore throat, myalgias or a rash. The patient has not had hemoptysis. The patient is not exposed to cigarette smoke. The patient has not tried anything for treatment of this illness. </P><P><B><U>Medications</U></B></P><P>Reviewed.</P><P><B><U>Allergies</U></B></P><P>Reviewed per Nurses Note</P><P><B><U>Physical Examination</U></B></P><P>Vitals:   Reviewed.</P><P>HEENT:</P><P>Neck: Thyroid- non enlarged,  symmetric and has no nodules. No bruits are detected. ROM- Normal Range of Motion with no rigidity.</P><P>Lymph Nodes: Cervical- no enlarged lymph nodes noted. Clavicular- Deferred. Axillary- Deferred. Inguinal- Deferred.</P><P>Lungs: Auscultation- Clear to auscultation bilaterally. There are no retractions, clubbing or cyanosis. The Expiratory to Inspiratory ratio is equal.</P><P>Cardiovascular: There are no carotid bruits. Heart- Normal Rate with Regular rhythm and no murmurs. There are no gallops. There are no rubs. In the lower extremities there is no edema. The upper extremities do not have edema.</P><P>Abdomen: Soft, benign, non-tender with no masses, hernias, organomegaly or scars.</P><P><B><U>Impression and Assessment</U></B></P><P>Acute Bronchitis.</P><P><B><U>Plan</U></B></P><P>Acute Bronchitis Plan: (select plan)</P><P>Add:<BR/>- ALBALON (0.1%) 4 BID</P><P><B><U>Return to Office</U></B></P><P>The patient  was instructed to return for follow-up in 6 weeks. &nbsp;<BR/>&nbsp;<BR/> The patient was instructed to return sooner if the condition changes, worsens, or doesn't resolve.</P></DIV><DIV id=sig><TABLE border=1><TR><TD align=center><b><i>Digitally Signed:</b></i><br><i>14-Apr-2013, 2:32PM by Terri L Snyder, NP (Hal Meadows, M.D. Family Practice)</i></TD></TR></TABLE></DIV>    
eos;
		return parent::create($body, $head);
  }
}
$file = MyPdf::create()->withPaging()->save();
echo 'all done 2';

