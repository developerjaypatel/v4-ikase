<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/connection.php");

$next_month = mktime(0, 0, 0, date("m") + 1, date("d"),   date("Y"));
$lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"),   date("Y"));

$sql = "SELECT TRUNCATE(DATEDIFF(end_date, start_date) / 30, 0) months, MONTH(end_date) the_month, DAY(end_date) the_day, 
inv.*
FROM ikase.cse_invoice inv


INNER JOIN (
	SELECT customer_id, max(invoice_id) max_id 
	FROM ikase.cse_invoice ci
	WHERE ci.customer_id != 1033
	GROUP BY ci.customer_id
) max_inv
ON inv.invoice_id = max_inv.max_id
WHERE MONTH(end_date) = " . date("n", $next_month);

try {
	$invoices = DB::select($sql); $dbPDO = null;
	
	//die(print_r($invoices));
	
	foreach($invoices as $invoice) {
		//die(print_r($invoice));
		$end_time = strtotime($invoice->end_date);
		$next_start_date = mktime(0, 0, 0, date("m", $end_time), date("d", $end_time) + 1, date("Y", $end_time));
		if (date("y", $next_start_date) < date("y")) {
			$next_start_date = mktime(0, 0, 0, date("m", $end_time), date("d", $end_time) + 1, date("Y"));
		}
		$months = $invoice->months;
		if ($months==13) {
			$months = 12;
		}
		$next_end_date = mktime(0, 0, 0, date("m", $next_start_date) + $months, date("d", $next_start_date), date("Y", $next_start_date));
		
		$start_date = date("Y-m-d", $next_start_date);
		$end_date = date("Y-m-d", $next_end_date);
		
		//die($invoice->end_date . " // " . $start_date . " // " . $end_date);
		
		$invoice_number = $invoice->customer_id . "-" . date("ym", $next_start_date) . "-" . date("ym", $next_end_date);
		
		$invoice_id = -1;
		$cus_id = $invoice->customer_id;
		
		
		$diff = dateDiff("d", $start_date, $end_date);	// . " <-> " . $start_date.", ".$end_date;
		$diff_months = dateDiff("m", $start_date, $end_date);
		$total = ($cus->user_count * $cus->user_rate * $diff_amount);
		
		if ($diff_months==0) {
			$diff_amount = 1;
			$diff_months = 1 . " month";
		} else {
			$diff_amount = $diff_months;
			$diff_months .= " months";
		}
		
		$sql = "SELECT cau.customer_id, `user_count`, user_names, cus.user_rate
		FROM `ikase`.cse_active_users cau
		INNER JOIN ikase.cse_customer cus
		ON cau.customer_id = cus.customer_id
		WHERE (active_month = '" . date("n", $lastmonth) . "' OR active_month = '" . date("m") . "')
		AND active_year = '" . date("Y", $lastmonth) . "'
		AND cau.customer_id  = :customer_id
		ORDER BY `user_count` DESC
		LIMIT 0, 1";
		
		$dbPDO = getConnection();
		$stmt = $dbPDO->prepare($sql);
		$stmt->bindParam("customer_id", $cus_id);
		$stmt->execute();
		$cus = $stmt->fetchObject(); $dbPDO = null;
		
		$cus->user_names = str_replace(",", ", ", $cus->user_names);
		$cus->user_names = str_replace("  ", " ", $cus->user_names);
		$row = '
		<tr class="invoice_row">
			<td align="left" valign="top" style=" border-top:1px solid black">' . $cus->user_count . " users over " . $diff_months . '
			</td>
			<td align="center" valign="top" style=" border-top:1px solid black">$' . $cus->user_rate . '</td>
			<td align="right" valign="top" style="font-weight:bold; border-top:1px solid black">$' . number_format(($cus->user_count * $cus->user_rate * $diff_amount), 2) . '</td>
		</tr>';
		  
		$users_header = '<span style="font-weight:bold">Active Users</span> - ' . $cus->user_count . '<div style="margin-top:10px;">
	                ' . cleanWord($cus->user_names) . '
                </div>';
		
		$invoice_items = @processHTML($row);
		$active_users = @processHTML($users_header);
		
		//die($start_date . "\r\n" . $end_date . "\r\n" . $total . "\r\n" . $invoice_items . "\r\n" . $active_users);
		$blnIncludeGenerate = true;
		include("invoice_save.php");
	}
	
} catch(PDOException $e) {	
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
	
?>
