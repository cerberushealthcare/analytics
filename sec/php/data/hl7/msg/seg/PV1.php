<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';   
//
/**
 * Patient Visit
 * @author Warren Hornsby
 */
class PV1 extends HL7Segment {
  //
  public $segId = 'PV1';
  public $seq;  // Set ID - PV1 (SI)
  public $class;  // Patient Class (IS)
  public $assignedLoc;  // Assigned Patient Location (PL)
  public $admitType;  // Admission Type (IS)  
  public $preadmitNo;  // Preadmit Number (CX)  
  public $priorLoc;  // Prior Patient Location (PL)
  public $attendingDoc = 'XCN';  // Attending Doctor (XCN)  
  public $referDoc;  // Referring Doctor (XCN)  
  public $consultDoc;  // Consulting Doctor (XCN)  
  public $hospital;  // Hospital Service (IS)  
  public $tempLoc;  // Temporary Location (PL)  
  public $preadmitTest;  // Preadmit Test Indicator (IS)  
  public $readmit;  // Re-admission Indicator (IS)
  public $admitSource;  // Admit Source (IS)  
  public $ambulatory;  // Ambulatory Status (IS)  
  public $vip;  // VIP Indicator (IS)  
  public $admitDoc;  // Admitting Doctor (XCN)  
  public $patientType;  // Patient Type (IS)  
  public $visitNo = 'CX';  // Visit Number (CX)  
  public $financialClass;  // Financial Class (FC)  
  public $chargePrice;  // Charge Price Indicator (IS)  
  public $courtesyCode;  // Courtesy Code (IS)  
  public $creditRating;  // Credit Rating (IS)  
  public $contractCode;  // Contract Code (IS)
  public $contractEffective;  // Contract Effective Date (DT)
  public $contractAmt;  // Contract Amount (NM)  
  public $contractPeriod;  // Contract Period (NM)  
  public $interestCode;  // Interest Code (IS)
  public $badDebt;  // Transfer to Bad Debt Code (IS)  
  public $badDebtDate;  // Transfer to Bad Debt Date (DT)
  public $badDebtAgency;  // Bad Debt Agency Code (IS)
  public $badDebtTransfer;  // Bad Debt Transfer Amount (NM)
  public $badDebtRecovery;  // Bad Debt Recovery Amount (NM)
  public $deleteAcct;  // Delete Account Indicator (IS)
  public $deleteAcctDate;  // Delete Account Date (DT)
  public $dischargeDisp;  // Discharge Disposition (IS)  
  public $dischargeTo;  // Discharged to Location (DLD)
  public $dietType = 'CE';  // Diet Type (CE)
  public $servicingFac;  // Servicing Facility (IS)
  public $bedStatus;  // Bed Status (IS)
  public $acctStatus;  // Account Status (IS)
  public $pendingLoc;  // Pending Location (PL)
  public $priorTempLoc;  // Prior Temporary Location (PL)
  public $admitDateTime = 'TS';  // Admit Date/Time (TS)
  public $dischargeDateTime = 'TS';  // Discharge Date/Time (TS)
  public $currentBal;  // Current Patient Balance (NM)
  public $totalCharged;  // Total Charges (NM)
  public $totalAdjusted;  // Total Adjustments (NM)
  public $totalPaid;  // Total Payments (NM)
  public $altVisitId;  // Alternate Visit ID (CX)
  public $visit;  // Visit Indicator (IS)
  public $otherProvider;  // Other Healthcare Provider (XCN)
}
//
class PV1_ADT extends PV1 {
  
}
