<?php 
require_once "inc/requireLogin.php";
require_once "ui/ui.php";
?>

<?
echo"<html><head><title>Payment</title></head><body><br>";

/*
<!---------------------------------------------------------------------------------
* PAYMENT_MIN.php - A form processing example showing the minimum
* number of possible fields for a credit card SALE transaction.
*
* This script processes form data passed in from Payment_min.html
*
*
* Copyright 2003 LinkPoint International, Inc. All Rights Reserved.
*
* This software is the proprietary information of LinkPoint International, Inc.
* Use is subject to license terms.
*

    This program is based on the sample SALE_MININFO.php
    
    Depending on your server setup, this script may need to
    be placed in the cgi-bin directory, and the path in the
    calling file PHP_FORM_MIN.html may need to be adjusted
    accordingly.

    NOTE: older versions of PHP and in cases where the PHP.INI
    entry is NOT "register_globals = Off", form data can be
    accessed simply by using the form-field name as a varaible
    name, eg. $myorder["host"] = $host, instead of using the
    global $_POST[] array as we do here. Passing form fields
    as demonstrated here provides a higher level of security.

------------------------------------------------------------------------------------>
*/
  include"lphp.php";

  $mylphp=new lphp;

  # constants
  $myorder["host"] = "secure.linkpt.net";
  $myorder["port"] = "1129";
  $myorder["keyfile"] = "\\clicktate-production\\sec\\1001174271.pem"; # Change this to the name and location of your certificate file
  $myorder["configfile"] = "1001174271";   # Change this to your store number

  # form data
  $myorder["cardnumber"] = $_POST["cardnumber"];
  $myorder["cardexpmonth"] = $_POST["cardexpmonth"];
  $myorder["cardexpyear"] = $_POST["cardexpyear"];
  $myorder["chargetotal"] = $_POST["chargetotal"];
  $myorder["ordertype"] = $_POST["ordertype"];
  $myorder["result"] = $_POST["result"];

  if ($_POST["debugging"])
    $myorder["debugging"]="true";

# Send transaction. Use one of two possible methods
//	$result = $mylphp->process($myorder);       # use shared library model
	$result = $mylphp->curl_process($myorder);  # use curl methods

	if ($result["r_approved"] != "APPROVED")    // transaction failed, print the reason
	{
		print "Status:  $result[r_approved]<br>\n";
		print "Error:  $result[r_error]<br><br>\n";
		print "Message:  $result[r_message]<br><br>\n";	}
	else	// success
	{		
		print "Status: $result[r_approved]<br>\n";
		print "Transaction Code: $result[r_code]<br><br>\n";
	}

# if verbose output has been checked,
# print complete server response to a table
	if ($_POST["verbose"])
	{
		echo "<table border=1>";

		while (list($key, $value) = each($result))
		{
			# print the returned hash 
			echo "<tr>";
			echo "<td>" . htmlspecialchars($key) . "</td>";
			echo "<td><b>" . htmlspecialchars($value) . "</b></td>";
			echo "</tr>";
		}	
			
		echo "</TABLE><br>\n";
	}
?>
</body></html>
