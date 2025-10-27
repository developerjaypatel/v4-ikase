<?php
if (!isset($_GET["dmsauth"])) {
	die("not allowed");
} 
$dmsauth = $_GET["dmsauth"];
$dmsauth = strrev($dmsauth);
$dmsauth = $dmsauth / 3;
$now = mktime(0, 0, 0, date("m"), date("d"),   date("Y"));
if ($now > $dmsauth) {
	die("not allowed");
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>iKase Invoice</title>
</head>

<body>
<table width="650px" border="0" cellspacing="0" cellpadding="2" align="center">
  <tr>
    <td align="left" valign="top"><img src="https://v4.ikase.org/img/ikase_logo_login.png" alt="iKase" height="32" /><br />
      Tel: (951) 757-8539</td>
    <td align="center" valign="top" nowrap="nowrap">
    	<span style="font-weight:bold; font-size:1.5em">Monthly Invoice</span>
    </td>
    <td align="right" valign="top">
Invoice #: 1093-1610<br />
        Date:10/26/2016                <br />
			            Payments:&nbsp;$0.00                        &nbsp;|&nbsp;
            <a href="invoice_payments.php?cus_id=1093&invoice_id=30">Payments</a>
			            
        	            </td>
    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3">
    	Bill To: KONRAD KUENSTLER<br />P.o. Box 8294, Van Nuys, CA 91409    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td nowrap="nowrap" width="400"><strong>Item</strong></td>
            <td nowrap="nowrap"><strong>Qty</strong></td>
            <td nowrap="nowrap"><strong>Monthly Rate</strong></td>
            <td nowrap="nowrap"><strong>Total</strong></td>
          </tr>
          <tr>
            <td align="left" valign="top">
            	Active Users
                <div style="margin-top:10px; font-size:0.8em">
	                Shahpoor Asher                </div>
            </td>
            <td align="left" valign="top">
				1            </td>
            <td align="left" valign="top">$15.00</td>
            <td align="left" valign="top" style="font-weight:bold">$15.00</td>
          </tr>
        </table>
    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><font size="-1">Please remit copy of invoice with payment.<b><br />
 Balance Due is subject to 10% penalty and 7% interest on any unpaid balance 
  after 60 days</b></font></td>
  </tr>
</table>
</body>
</html>
