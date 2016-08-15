<?php

set_include_path('../');
require_once "php/dao/_util.php";
require_once "php/pdf/pdfmaker.php";
require_once "inc/uiFunctions.php";
require_once "php/dao/OldBillingDao.php";
require_once "php/forms/OldBillInfoForm.php";

$testing = false;
// If run from the web, redirect to login page

if (isset($_SERVER['HTTP_HOST'])) {

  if ($_GET['jps'] != 'zybow')
  header("Location: ../index.php");
  $myHost = 'prod';
  $testing = true;
  echo '<pre>';
  echo 'Testing...<br>';
} else {
  $myHost = $argv[1];
}

try {

  // Establish e-mail as HTML type and set up message body header
  $msg = "<html><body>Today's billing details . . . <br><br><table border=1>";
  $msg .= "<thead><tr><th>User</th><th>Name</th><th>Status</th><th>Start</th>";
  $msg .= "<th>End</th><th>Prior Balance</th><th>Billed Amount</th>";
  $msg .= "<th>Total Notes</th><th>Printed</th><th>Copied</th></thead><tbody>";

  // Get next invoice number to use
  $rInv = fetch("SELECT next_number FROM num_ranges WHERE num_desc = 'BILL_INV_NUM'");

  // Get list of users that need to be billed via this run
  $sql = "SELECT user_id, name, address1, address2, city, state, zip, card_number, exp_month, exp_year, next_bill_date, balance, start_bill_date, bill_code FROM billinfo WHERE next_bill_date <='" . date('Y-m-d', strtotime("now")) . "'";
  if ($testing)
  echo ("<br>$sql");
  $res = query($sql);

  // Go through user list and get data to calculate bill
  while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
     
     
    // If user is inactive, do not attempt to bill - just skip to next user in list
    $rUserData = fetch("SELECT active FROM users WHERE user_id=" . $row["user_id"]);
    if ($rUserData["active"] == 0)
    continue;

    if ($testing) {
      echo('<br>');
      print_r($row);
    }

    // Get billing details for this bill_code
    $rBillCodes = fetch("SELECT monthly_charge, min_charge, max_charge, discount_code, note_charge FROM bill_codes WHERE bill_code=" . $row["bill_code"]);

    // Get usage details
    $info = query("SELECT usage_type, COUNT(*) AS 'ucnt' FROM usage_details where user_id=" . $row["user_id"] . " AND date >='" . $row["start_bill_date"] . "' AND date <'" . $row["next_bill_date"] . "' GROUP BY usage_type");

    // Get download or print count and copy to clipboard count
    $cnt2 = 0;
    $cnt3 = 0;
    while ($irow = mysql_fetch_array($info, MYSQL_ASSOC)) {
      switch ($irow["usage_type"]) {
        case 0:  // default to download or print
          $cnt2 = $irow["ucnt"];
          break;
        case 2:  // download or print
          $cnt2 = $irow["ucnt"];
          break;
        case 3:
          $cnt3 = $irow["ucnt"];
          break;
      }
    }

    // Set monthly charge if applicable
    $totalamt = $rBillCodes["monthly_charge"];
    // Get total count
    $total = $cnt2 + $cnt3;
    // Add per note charge if applicable
    if ($rBillCodes["note_charge"] != 0) {
      $totalamt = $totalamt + ($total * $rBillCodes["note_charge"]);
    }
    // Adjust for min charge if applicable
    if ($rBillCodes["min_charge"] != 0) {
      if ($totalamt < $rBillCodes["min_charge"]) {
        $totalamt = $rBillCodes["min_charge"];
      }
    }
    // Adjust for max charge if applicable
    if ($rBillCodes["max_charge"] != 0) {
      if ($totalamt > $rBillCodes["max_charge"]) {
        $totalamt = $rBillCodes["max_charge"];
      }
    }
    //  Add existing balance to this month's amount to get billable amount
    $billamt = $row["balance"] + $totalamt;
    $potnewbal = $billamt;
    if ($billamt < 0)
    $billamt = 0;

    // Set fields needed to call billing function
    $bill = new BillInfoForm();
    $bill->name = $row["name"];
    $bill->unot = $row["user_id"];
    $bill->address1 = $row["address1"];
    $bill->city = $row["city"];
    $bill->state = $row["state"];
    $bill->zip = $row["zip"];
    $bill->card_number = $row["card_number"];
    $bill->exp_month = $row["exp_month"];
    $bill->exp_year = $row["exp_year"];
    $bill->balance = $billamt;

    $bresult = "Billed";
    // Charge user
    if ($myHost == "prod") {
      $bresult = "Billed";
      try {
        if ($billamt >= .01)
        $result = OldBillingDao::chargeUser($bill, $testing);
      } catch (ChargeException $e) {
        $bresult = "Failed";
      }
    }

    // Create billing history record and update billinfo if charge was successful
    if ($bresult == "Billed") {

      $sql = "INSERT INTO billhist VALUES(" . $row["user_id"];
      $sql .= ", " . quote($row["start_bill_date"]);
      $sql .= ", " . quote($row["next_bill_date"]);
      $sql .= ", " . $row["balance"];
      $sql .= ", " . $billamt;
      $sql .= ", " . $total;
      $sql .= ", " . $cnt2;
      $sql .= ", " . $cnt3;
      $sql .= ")";
      $id = binsert($sql, $testing);

      // Set new next_bill_date (old next_bill_date becomes the new start_bill_date)
      $ddate = $row["next_bill_date"];
      $dyear = substr($ddate,0,4);
      $dmth = substr($ddate,5,2);
      $dday = substr($ddate,8,2);
      $new_next_date = date('Y-m-d', strtotime("+1 month", mktime(0,0,0,$dmth,$dday,$dyear)));

      $sql = "UPDATE billinfo SET ";
      $sql .= "next_bill_date=" . quote($new_next_date);
      $sql .= ", " . "start_bill_date=" . quote($row["next_bill_date"]);
      if ($potnewbal < 0) {
        $sql .= ", balance=" . $potnewbal;
      } else {
        $sql .= ", " . "balance=0";
      }
      $sql .= " WHERE user_id=" . $row["user_id"];
      $rows = bupdate($sql, $testing);

      // Set values needed to create statement PDF
      $stmtVals = array();

      $stmtVals["date"] = date('n/j/Y', strtotime("now"));
      $stmtVals["name"] = $row["name"];
      $stmtVals["address1"] = $row["address1"];
      $stmtVals["address2"] = $row["address2"];
      $stmtVals["city"] = $row["city"];
      $stmtVals["state"] = $row["state"];
      $stmtVals["zip"] = $row["zip"];
      $stmtVals["amt_due"] = '$0.00';
      $stmtVals["items"][0][0] = date('m/d/Y', strtotime($row["start_bill_date"]));
      $stmtVals["items"][0][1] = 'Balance forward';
      $stmtVals["items"][0][2] = number_format($row["balance"], 2) . "  ";
      $stmtVals["items"][0][3] = number_format($row["balance"], 2) . "  ";
      $stmtVals["items"][1][0] = date('m/d/Y', strtotime($row["next_bill_date"]));
      $stmtVals["items"][1][1] = "INV #" . number_format($rInv["next_number"],0,'.','') . " . Due " . date('m/d/Y', strtotime($row["next_bill_date"]));
      $stmtVals["items"][1][2] = number_format($totalamt, 2) . "  ";
      if ($potnewbal < 0) {
        $stmtVals["items"][1][3] = number_format($potnewbal, 2) . "  ";
      } else {
        $stmtVals["items"][1][3] = number_format($billamt, 2) . "  ";
      }
      $stmtVals["items"][2][0] = date('m/d/Y', strtotime("now"));
      $stmtVals["items"][2][1] = 'PMT';
      $stmtVals["items"][2][2] = "-" . number_format($billamt,2) . "  ";
      if ($potnewbal < 0) {
        $stmtVals["items"][2][3] = number_format($potnewbal, 2) . "  ";
      } else {
        $stmtVals["items"][2][3] = '0.00  ';
      }
      $stmtVals["current"] = '0.00';
      $stmtVals["1-30"] = '0.00';
      $stmtVals["30-60"] = '0.00';
      $stmtVals["60-90"] = '0.00';
      $stmtVals["over90"] = '0.00';
      $stmtVals["totalamt"] = '$0.00';
      if ($testing) {
        echo('<br>');
        print_r($stmtVals);
      } else
      $stmtPDF = buildStmtPDF($stmtVals);


      // Set values needed to create invoice PDF
      $invVals = array();

      $invVals["date"] = date('n/j/Y', strtotime($row["next_bill_date"]));
      $invVals["invnum"] = number_format($rInv["next_number"],0,'.','');
      $invVals["name"] = $row["name"];
      $invVals["address1"] = $row["address1"];
      $invVals["address2"] = $row["address2"];
      $invVals["city"] = $row["city"];
      $invVals["state"] = $row["state"];
      $invVals["zip"] = $row["zip"];
      // Add monthly fee line on invoice if applicable
      $vrow = 0;
      $invTot = 0;
      if ($rBillCodes["monthly_charge"] != 0) {
        $invVals["items"][$vrow][0] = "1";
        $invVals["items"][$vrow][1] = 'clicktate monthly usage fee';
        $invVals["items"][$vrow][2] = number_format($rBillCodes["monthly_charge"], 2) . " ";
        $invVals["items"][$vrow][3] = number_format($rBillCodes["monthly_charge"], 2) . "   ";
        $vrow = $vrow + 1;
        $invTot = $rBillCodes["monthly_charge"];
      }
      // Add per note fee line on invoice if applicable
      if ($rBillCodes["note_charge"] != 0) {
        $invVals["items"][$vrow][0] = number_format($total,0);
        $invVals["items"][$vrow][1] = 'clicktate notes';
        $invVals["items"][$vrow][2] = number_format($rBillCodes["note_charge"], 2) . " ";
        $linetotal = $total * $rBillCodes["note_charge"];
        $invVals["items"][$vrow][3] = number_format($linetotal, 2) . "   ";
        $vrow = $vrow + 1;
        $invTot = $invTot + $linetotal;
      }
      // Add min. adjustment line if applicable
      $adjust = 0;
      if (($rBillCodes["min_charge"] != 0) &&  ($invTot < $rBillCodes["min_charge"])) {
        $adjust = $rBillCodes["min_charge"] - $invTot;
        $adjtext = "minimum usage adjustment";
      } else if (($rBillCodes["max_charge"] != 0) &&  ($invTot > $rBillCodes["max_charge"])) {
        $adjust = $rBillCodes["max_charge"] - $invTot;
        $adjtext = "maximum usage adjustment";
      }
      if ($adjust != 0) {
        $invVals["items"][$vrow][0] = "1";
        $invVals["items"][$vrow][1] = $adjtext;
        $invVals["items"][$vrow][2] = number_format($adjust, 2) . " ";
        $invVals["items"][$vrow][3] = number_format($adjust, 2) . "   ";
      }
      $invVals["totalamt"] = "$" . number_format($totalamt, 2) . "  ";

      if ($testing) {
        echo('<br>');
        print_r($invVals);
      } else
      $invoicePDF = buildInvoicePDF($invVals);

      if (! $testing)
      sendBillEmail($row["user_id"], $stmtPDF, $invoicePDF);

      // Update next invoice number on database
      $rInv["next_number"] = $rInv["next_number"] + 1;
      $sql = "UPDATE num_ranges SET ";
      $sql .= "next_number='" . number_format($rInv["next_number"],0,'.','') . "'";
      $sql .= " WHERE num_desc = 'BILL_INV_NUM'";
      $rows = bupdate($sql, $testing);

    }

    // Generate e-mail text for this record
    $msg .= "<tr>";
    $msg .= "<td>" . $row["user_id"];
    $msg .= "</td><td>" . $row["name"];
    $msg .= "</td><td>" . $bresult;
    $msg .= "</td><td>" . $row["start_bill_date"];
    $msg .= "</td><td>" . $row["next_bill_date"];
    $msg .= "</td><td>" . "$ " . number_format($row["balance"], 2);
    $msg .= "</td><td>" . "$ " . number_format($billamt, 2);
    $msg .= "</td><td>" . $total;
    $msg .= "</td><td>" . $cnt2;
    $msg .= "</td><td>" . $cnt3;
    $msg .= "</tr>";

  }


} catch (SqlException $e) {
  echo "<br>" . $id;
  echo "<br>SQL Failed";
}

// Finish e-mail text
$msg .= "</tbody></table></body></html>";

// Send e-mail

$to       = "pstewart@clicktatemail.com, gkowalik@clicktatemail.com";
$subject  = "clicktate.com Billing Information";
$headers  = 'From: info@clicktatemail.info' . "\r\n";
$headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
$headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
$headers .= 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

if (! $testing)
mail($to, $subject, $msg, $headers);


function sendBillEmail($userid, $statement, $invoice) {

  // Generate a boundary string
  $mime_boundary = "<<<--==+X[" . md5(time()) . "]";

  // Get user name and email address
  $rUser = fetch("SELECT name, email FROM users WHERE user_id = " . $userid);

  // Create email headers
  //	$to       = "pstewart@clicktatemail.com, gkowalik@clicktatemail.com";
  $to       = $rUser["email"];
  $subject  = "clicktate.com Billing Information";
  $headers  = 'From: info@clicktatemail.info' . "\r\n";
  $headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
  $headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
  $headers .= 'Bcc: pstewart@clicktatemail.com, gkowalik@clicktatemail.com' . "\r\n";
  $headers .= 'MIME-Version: 1.0' . "\r\n";
  $headers .= "Content-Type: multipart/mixed; ";
  $headers .= "boundary=\"".$mime_boundary."\"\r\n";

  // Create text body of email
  $txt = "<html>" . $rUser["name"] . ",<br><br>Thank you for using Clicktate. Your latest statement and payment information" .
		" are included as PDF attachments to this email. If you have problems viewing these attachments, we recommend that you visit" .
		" <a href=\"http://www.adobe.com\">http://www.adobe.com</a> to install the latest version of Adobe Reader.<br><br>If you have any further questions, please contact" .
		" us at 1-888-8CLICK8." .
		"<br><br>Thank you,<br><br>Billing<br>clicktate.com</html>";

  // Add a multipart boundary above the message body and put the email text into the body
  $message = "--".$mime_boundary."\r\n".
        	"Content-Type: text/html; charset=\"iso-8859-1\"\r\n\r\n". 
  $txt ."\r\n\r\n".
		"--".$mime_boundary."\r\n";

  // Base64 encode PDF statement
  $statement = chunk_split(base64_encode($statement));
  // Add statement file attachment to the message
  $message .= "Content-Type: application/pdf \r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: inline; filename=statement.pdf \r\n\r\n" . 
  $statement."\r\n" .
            "--".$mime_boundary."\r\n";

  // Base64 encode PDF invoice
  $invoice = chunk_split(base64_encode($invoice));
  // Add statement file attachment to the message
  $message .= "Content-Type: application/pdf \r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: inline; filename=invoice.pdf \r\n\r\n" . 
  $invoice."\r\n" .
            "--".$mime_boundary."\r\n";

  // Send mail
  mail($to, $subject, $message, $headers);
}

function binsert($sql, $testing) {
  if ($testing)
  echo "<br>$sql";
  else
  insert($sql);
}
function bupdate($sql, $testing) {
  if ($testing)
  echo "<br>$sql";
  else
  update($sql);
}
?>