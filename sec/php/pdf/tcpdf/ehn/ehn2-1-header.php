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
 

	$html= file_get_contents('sample-note-header.html');

	//Find the <div id=header> Put it into a substring

	$start = strpos($html,'<div id=head>');
 
	$end = strpos($html,'</div>',$start) + 6; // end of div tag 
 
	$HeaderDiv = substr($html,$start,$end-$start);

	$BodyDiv = str_replace($HeaderDiv, '', $html);






// TCPDF Class to create Custom Header and Footer

class MYPDF extends TCPDF {


    //Page header
    public function Header() {
	

	$html= file_get_contents('sample-note-header.html');

	//Find the <div id=header> Put it into a substring

	$start = strpos($html,'<div id=head>');
 
	$end = strpos($html,'</div>',$start) + 6; // end of div tag 
 
	$HeaderDiv = substr($html,$start,$end-$start);
	
	//Clean The HTML
	$HeaderDiv = tidy_parse_string($HeaderDiv);
	$HeaderDiv ->cleanRepair();

        // Set font
        $this->SetFont('times', '', 10);

        //Title
        $this->writeHTML($HeaderDiv, true, false, true, false, '');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('EHN');
$pdf->SetTitle('Patient Medical Data');
$pdf->SetSubject('');
$pdf->SetKeywords('');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, true);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

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





// ---------------------------------------------------------

$tagvs = array('p' => array( array('h' => 0, 'n' => 0), array('h' => 0, 'n' => 0) ), 'div' => array( array('h' => 0, 'n' => 0.8), array('h' => 0, 'n' => 0) ), 'span' => array( array('h' => 0, 'n' => 0), array('h' => 0, 'n' => 0) )  ); 


// Clean up HTML Code
$BodyDiv = tidy_parse_string($BodyDiv);
$BodyDiv->cleanRepair();
//Removes empty tags
//$BodyDiv = preg_replace('#<(\w+)[^>]*>\s*</\1>#im','', $BodyDiv);

$pdf->setHtmlVSpace($tagvs); 

$pdf->writeHTML($BodyDiv, false, false, true, false, '');

//Close and output PDF document
$pdf->Output('Medical Summary3.pdf', 'I');
//============================================================+
// END OF FILE                                                
//============================================================+

