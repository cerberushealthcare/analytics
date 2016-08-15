<?php
//============================================================+
// File name   : example_002.php
// Begin       : 2008-03-04
// Last Update : 2010-08-08
//
// Description : Example 002 for TCPDF class
//               Removing Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Removing Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

require_once('../config/lang/eng.php');
require_once('../tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Papyrus');
$pdf->SetTitle('Medical Summary');
$pdf->SetSubject('Patient Medical Summary');
$pdf->SetKeywords('Medical Summary');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', '', 10);

// add a page
$pdf->AddPage();

// set some text to print
$html = file_get_contents('sample-note.html');


// ---------------------------------------------------------

$tagvs = array('p' => array( array('h' => 0, 'n' => 0), array('h' => 0, 'n' => 0) ), 'div' => array( array('h' => 0, 'n' => 0), array('h' => 0, 'n' => 0) ), 'span' => array( array('h' => 0, 'n' => 0), array('h' => 0, 'n' => 0) )  ); 

// Clean up HTML Code - Removes indents and white spaces
$html = tidy_parse_string($html);
$html->cleanRepair();

//Removes empty tags
$html = preg_replace('#<(\w+)[^>]*>\s*</\1>#im','', $html);

//Sets Line Height for <p>, <div>, <span> tags
$pdf->setHtmlVSpace($tagvs); 

$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
$pdf->Output('Medical Summary3.pdf', 'I');


//============================================================+
// END OF FILE                                                
//============================================================+

