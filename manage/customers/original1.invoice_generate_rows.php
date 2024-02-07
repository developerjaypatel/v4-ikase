<?php
if (!isset($blnIncludeGenerate)) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	include("../../api/manage_session.php");
	session_write_close();
	
	if (!isset($_SESSION["user_plain_id"])) {
		die("no id");
	}
	if ($_SESSION["user_role"]!="owner") {
		die("no go");
	}
	include("../../api/connection.php");
	
	$cus_id = passed_var("cus_id", "post");
	$start_date = passed_var("start_date", "post");
	$end_date = passed_var("end_date", "post");
}

$start_date = date("Y-m-d", strtotime($start_date));
$end_date = date("Y-m-d", strtotime($end_date));
$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));

$diff = dateDiff("d", $start_date, $end_date);	// . " <-> " . $start_date.", ".$end_date;
$diff_months = dateDiff("m", $start_date, $end_date);
try {
	//customer info
	$query = "SELECT `cus_name`, corporation_rate, user_rate
	FROM cse_customer cus
	WHERE cus.customer_id = :customer_id";

	$db = getConnection();
	$stmt = $db->prepare($query);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;

	$user_rate = $customer->user_rate;
	$corporation_rate = $customer->corporation_rate;

	//attorneys
	$sql = "SELECT * 
	FROM ikase.cse_user
	WHERE customer_id = :customer_id
	AND level != 'masteradmin'
	AND activated = 'Y'
	AND job LIKE 'Attorney%'";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$attorneys = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($attorneys));
	$stmt->closeCursor(); $stmt = null; $db = null;
	$arrAttorneys = array();
	foreach($attorneys as $attorney) {
		$arrAttorneys[] = $attorney->user_name;
	}
	//active users per month
	/*
	$sql = "SELECT user_count, user_names, active_year, active_month, CAST(CONCAT(active_year, '-', active_month, '-1') AS DATE) active_day
	FROM ikase.cse_active_users cau
	WHERE customer_id = :customer_id
	AND CAST(CONCAT(active_year, '-', active_month, '-1') AS DATE) BETWEEN :start_date AND :end_date
	ORDER BY active_year, active_month";
	*/
	$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
	/*
	$sql = "SELECT customer_id, `user_count`, user_names, active_month, active_year
	FROM `ikase`.cse_active_users
	WHERE active_month = '" . date("m", $lastmonth) . "'
	AND active_year = '" . date("Y", $lastmonth) . "'
	AND customer_id = :customer_id";
	*/
	
	$sql = "SELECT GROUP_CONCAT(DISTINCT user_name SEPARATOR '|')  user_names
	FROM ikase.cse_user
	WHERE customer_id = :customer_id
	AND activated = 'Y'
	AND deleted = 'N'
	AND user_name != 'Matrix Admin'";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	//$stmt->bindParam("start_date", $start_date);
	//$stmt->bindParam("end_date", $end_date);
	$stmt->execute();
	$active_users = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($active_users));
	$arrActive = array();
	$total = 0;
	$current_names = "";
	$current_user_count = 0;
	$users_header = "";
	foreach($active_users as $active) {
		$arrNames = explode("|", $active->user_names);
		//$arrNames = array_merge($arrAttorneys, $arrNames);
		$user_count = count($arrNames);
		//die(print_r($arrNames));
		$user_names = ucwords(strtolower(implode(", ", $arrNames)));
		/*
		$active_year = $active->active_year;
		$active_month = $active->active_month;
		$display_name = $active_month . '/' . $active_year;
		*/
		$display_name = "";
		//if ($current_names != $user_names) {
		if ($users_header=="") {
			$current_names = $user_names;
			$current_user_count = $user_count;
			$users_header = '<span style="font-weight:bold">Active Users</span> - ' . $user_count . '<div style="margin-top:10px;">
	                ' . cleanWord($user_names) . '
                </div>';
		} 
		$row = '<tr class="invoice_row">
            <td align="left" valign="top">
            	' . $display_name . '
            </td>
            <td align="left" valign="top">
				' . $user_count . '
            </td>
            <td align="center" valign="top">$' . $user_rate . '</td>
            <td align="right" valign="top">$' . number_format($user_count * $user_rate, 2) . '</td>
          </tr>';
		  $total += ($user_count * $user_rate);
		  //array_push($arrActive, $row);
	}
	/*
	if ($active_month < 10) {
		$active_month = "0" . $active_month;
	}
	$fullperiod = $active->active_year . $active_month;
	//die(print_r($arrActive));
	if ($fullperiod < date("Ym", strtotime($end_date))) {
		$active_month = (int)$active->active_month + 1;
		
		if ($active_month == 13) {
			$active_month = 1;
			$active_year = ((int)$active_year) + 1;
		}
		if ($active_month < 10) {
			$active_month = "0" . $active_month;
		}
		
		$fullperiod = $active_year . $active_month;
		//$display_name = $active_month . "/" . $active_year . " (estimated)";
		$display_name = $active_month . "/" . $active_year;
		$intCounter = 0;
		
		//make sure end date is beginning of next month
		$tomorrow  = mktime(0, 0, 0, date("m", strtotime($end_date))  , date("d", strtotime($end_date))+1, date("Y", strtotime($end_date)));
		
		$end_date = date("Y-m-d", $tomorrow);
		
		//echo (int)$fullperiod . " < " . (int)date("Ym", strtotime($end_date)) . "\r\n";
		while((int)$fullperiod < (int)date("Ym", strtotime($end_date))) {
			//die((int)$fullperiod . " < " . (int)date("Ym", strtotime($end_date)));
			$row = '<tr class="invoice_row">
            <td align="left" valign="top">
            	' . $display_name . '
            </td>
            <td align="left" valign="top">
				' . $user_count . '
            </td>
            <td align="center" valign="top">$' . $user_rate . '</td>
            <td align="right" valign="top">$' . number_format($user_count * $user_rate, 2) . '</td>
          </tr>';
		  
			//increment
			//echo $intCounter . "] " . $user_count ."*". $user_rate . "<br />";
			$total += ($user_count * $user_rate);
			//array_push($arrActive, $row);
		  
		  	//strip leading zero if any
			//echo "\r\n" . $active_month . " == " . strpos($active_month, "0") . "\r\n";
			if (substr($active_month, 0, 1)=="0") {
				//echo $active_month . " < 10 \r\n";
				$active_month = substr($active_month, 1, 1);
			}
			$active_month = ((int)$active_month) + 1;
			
			if ($active_month == 13) {
				$active_month = 1;
				$active_year = ((int)$active_year) + 1;
			}
			if ($active_month < 10) {
				//echo $active_month . " < 10 \r\n";
				$active_month = "0" . $active_month;
			}
			$fullperiod = $active_year . $active_month;
			
			//echo (int)$fullperiod . " < " . (int)date("Ym", strtotime($end_date)) . "\r\n";
			
			//$display_name = $active_month . "/" . $active_year . " (estimated)";
			$display_name = $active_month . "/" . $active_year;
			
			$intCounter++;
			if ($intCounter > 15) {
				break;
			}
		}
	}
	*/
	//die("count:" . count($arrActive));
	if ($diff_months==0) {
		$diff_amount = 1;
		$diff_months = 1 . " month";
	} else {
		$diff_amount = $diff_months;
		$diff_months .= " months";
	}

	$row = '<tr class="invoice_row">
		<td align="left" valign="top" style=" border-top:1px solid black">' . $current_user_count . " users over " . $diff_months . '
		</td>
		<td align="center" valign="top" style=" border-top:1px solid black">$' . $user_rate . '</td>
		<td align="right" valign="top" style="font-weight:bold; border-top:1px solid black">$' . number_format(($current_user_count * $user_rate * $diff_amount), 2) . '</td>
	  </tr>';
	array_push($arrActive, $row);

	$total = ($current_user_count * $user_rate * $diff_amount);
	$html = implode("\r\n", $arrActive);
	
	if (!isset($blnIncludeGenerate)) {
		echo json_encode(array("success"=>"true", "users"=>$users_header, "html"=>$html, "total"=>$total));
	}	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
?>