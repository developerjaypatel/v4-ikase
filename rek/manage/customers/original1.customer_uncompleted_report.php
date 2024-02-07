<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
$headers .= "From: CAJetFile Uncompleted Report <donotreply@cajetfile.com>\r\n";

$three_days = mktime(0, 0, 0, date("m")  , date("d")-3, date("Y"));
$three_days = date("Y-m-d", $three_days);

$query = "SELECT  tbl_customer.`cus_id`, `cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_email`
FROM tbl_customer 
WHERE 1
AND cus_email != ''
ORDER BY `cus_name`";

$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

//keep overall list to send to owner
$arrOwnerCases = array();
$arrOwnerCaseID = array();
		
for ($int=0;$int<$numbs;$int++) {
	$cus_id = mysql_result($result, $int, "cus_id");
	//anything late for me
	$querylate = "SELECT DISTINCT thefiling.case_id, user.user_email, user.user_name, cast(date_submitted as date) submitted_date, status
	FROM eamsjetfiler.tbl_ftp_log thelog
	INNER JOIN eamsjetfiler.tbl_filing thefiling
	ON thelog.packet_id = thefiling.packet_id
	INNER JOIN tbl_case thecase
	ON thefiling.case_id = thecase.case_id
	INNER JOIN tbl_user user
	ON thefiling.user_id = user.user_id
	INNER JOIN tbl_client theclient
	ON thecase.client_id = theclient.client_id
	WHERE `date_submitted` < '" . $three_days . "'
	AND thecase.adj_number = ''
	AND theclient.cus_id = " . $cus_id . "
	GROUP BY thelog.packet_id, thefiling.case_id
	HAVING  max(status) < 5";
	$resultlate = mysql_query($querylate, $r_link) or die("unable to get lates");
	$numberlate = mysql_numrows($resultlate);
	
	//echo $querylate . " - " . $numberlate . "<br /><br />";
	//continue;
	if ($numberlate>0) {
		$cus_name = mysql_result($result, $int, "cus_name");
		$cus_name_first = mysql_result($result, $int, "cus_name_first");
		$cus_name_middle = mysql_result($result, $int, "cus_name_middle");
		$cus_name_last = mysql_result($result, $int, "cus_name_last");
		$cus_email = mysql_result($result, $int, "cus_email");
		$arrCases = array();
		$arrCaseID = array();
		for($intL=0;$intL<$numberlate;$intL++) {
			$the_case_id = mysql_result($resultlate, $intL, "case_id");
			if (!in_array($the_case_id, $arrCaseID)) {
				$arrCaseID[] = $the_case_id;
				$arrOwnerCaseID[] = $the_case_id;
			} else {
				continue;
			}
			$the_submitted_date = mysql_result($resultlate, $intL, "submitted_date");
			$the_user_name = mysql_result($resultlate, $intL, "user_name");
			$the_user_email = mysql_result($resultlate, $intL, "user_email");
			$the_status = mysql_result($resultlate, $intL, "status");
			$case_info = $the_case_id . " -> Originally filed on " . date("m/d/y", strtotime($the_submitted_date));
			if ($the_user_name!="") {
				$case_info .= " by " . $the_user_name . "\r\n";
			}
			//echo $case_info . "<br />";
			/*
			switch($the_status) {
				case 4:
					$feedback = "Validation Failed on Errors";
					break;
					
			}
		 	$case_info .= " [Status:" . $the_status . "]";
			*/
			$arrCases[] = $case_info;
			$arrOwnerCases[] = "Firm: " . $cus_name . " -> " . $case_info;
		}
		sort($arrCases);
		sort($arrOwnerCases);
		//die(print_r($arrCases));
		//$the_row = $cus_id . "|". $cus_name_first . " ". $cus_name_last . "|". str_replace(" ", " ", $cus_name) . "|" . $cus_email . "|" . implode("~", $arrCases);
		$the_row = "This is an automated message for ". $cus_name_first . " ". $cus_name_last . "\r\n";
		$the_row .= "\r\n";
		$the_row .= "There are " . count($arrCases) . " uncompleted cases submitted via CA JetFile for the firm " . str_replace(" ", " ", $cus_name);
		
		$the_row .= ".\r\n\r\n";
		$the_row .= "List of Case ID(s):\r\n" . implode("\r\n", $arrCases) . "\r\n\r\n";
		$the_row .= "Sent to " . $cus_email;
		$email = $cus_email;
		if ($cus_email!=$the_user_email) {
			 $the_row .= " and " . $the_user_email . "\r\n";
			 $email .= "," . $the_user_email;
		}
		$email .= ", matrixdis@gmail.com, webmaster@kustomweb.com";
		//die($the_row);
		$email = "nick@kustomweb.com";
		$subject = 'CA JetFile - ' . $cus_name . ' - Uncompleted Cases Report - ' . date("m/d/Y");
		//die($the_row);
		
		if (mail($email, $subject, $the_row, $headers)) {
			echo count($arrCases) . " case(s) sent to " . $cus_name_first . " ". $cus_name_last . "<br />";
		} else {
			$error = error_get_last();
			print_r($error);
			preg_match("/\d+/", $error["message"], $error);
			die("not sent:" . $error[0]);
			die("not sent");
		}
		
		$arrRows[] = $the_row;
	}
}
if (count($arrOwnerCases) >0 ) {
	$email = "nick@kustomweb.com";
	$subject = 'CA JetFile - Uncompleted Cases Report - ' . date("m/d/Y");
	$mail_values = "";
	$current_firm = "";
	foreach($arrOwnerCases as $case) {
		$arrCase = explode(" -> ", $case);
		$thefirm = $arrCase[0];
		if ($current_firm != $thefirm) {
			$mail_values .= "\r\n\r\n" . $thefirm . "\r\n";
			$current_firm = $thefirm;
		}
		$mail_values .= "Case ID:" . $arrCase[1] . "; " . $arrCase[2];
	}
	//$mail_values = implode("\r\n", $arrOwnerCases);
	if (mail($email, $subject, $mail_values, $headers)) {
		echo count($arrOwnerCases) . " case(s) sent to owner<br />";
	} else {
		$error = error_get_last();
		print_r($error);
		preg_match("/\d+/", $error["message"], $error);
		die("owner email not sent:" . $error[0]);
		die("not sent");
	}
}
mysql_close($r_link);
//$maincontent = implode("\n", $arrRows);
//echo $maincontent;
exit();
?>