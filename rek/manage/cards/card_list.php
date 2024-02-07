<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");
$show_emails = passed_var("show_emails");
$labels = passed_var("labels");

$query = "SELECT `First Name`, `Last Name`, `Job Title`, `Company Name`, `Email`, `Street 1`, `Street 2`, `City`, `State`, `Zip`, `Phone`, `Mobile`, `Fax` FROM `cards` WHERE 1
ORDER BY `Last Name`";
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

$arrEmails = array();
$arrAddresses = array();
$arrRows = array();
for ($int=0;$int<$numbs;$int++) {
	$first_name = mysql_result($result, $int, "First Name");
	$last_name = mysql_result($result, $int, "Last Name");
	$job = mysql_result($result, $int, "Job Title");
	$company = mysql_result($result, $int, "Company Name");
	$email = mysql_result($result, $int, "Email");
	$street = mysql_result($result, $int, "Street 1");
	$street2 = mysql_result($result, $int, "Street 2");
	$city = mysql_result($result, $int, "City");
	$state = mysql_result($result, $int, "State");
	$zip = mysql_result($result, $int, "Zip");
	$phone = mysql_result($result, $int, "Phone");
	$mobile = mysql_result($result, $int, "Mobile");
	$fax = mysql_result($result, $int, "Fax");

	$the_row = $first_name . "|". $last_name . "|". $job . "|". $company . "|" . $email . "|" . $street . "|" . $street2 . "|" . $city . "|" . $state . "|" . $zip . "|" . $phone . "|" . $mobile . "|" . $fax;
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
	if ($email!="") {
		$arrEmails[] = $email;
	}
	if ($company!="" && $street!="" && $city!="" && $state!="" && $zip!="") {
		$address = '"' . $company . '","' . $street . '","' . $city . '","' . $state . '","' . $zip . '"';
		$company_person = $first_name . " ". $last_name;
		if ($company_person!="") {
			//echo "<br>Attn: " . $company_person;
			$address .= ', "' . $company_person . '"';
		} else {
			$address .= ', ""';
		}
		$arrAddresses[] = $address;
	}
}
mysql_close($r_link);
$maincontent = "";
if ($show_emails!="y" && $labels!="y") {
	$maincontent = implode("\n", $arrRows);
} 
if ($show_emails=="y") {
	$arrEmails = array_unique($arrEmails);
	$maincontent = implode(";", $arrEmails);
}
if ($maincontent != "") {
	echo $maincontent;
}
if ($labels=="y") {
	$arrAddresses = array_unique($arrAddresses);
	$address_list = implode(chr(13).chr(10), $arrAddresses);
	$billingname = "labels.txt";
	if (!$handle = fopen($billingname, 'w')) {
		 die( "Cannot open file ($billingname)");
		 exit;
	}
	
	// Write $somecontent to our opened file.
	if (fwrite($handle, $address_list) === FALSE) {
	   die( "Cannot write to file ($billingname)");
	   exit;
	}
	//die("<br>done");
	$reroute= $billingname;
	header("location:" . $reroute);
}
	
exit();
?>