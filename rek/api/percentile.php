<?php
$app->get('/percentiles/dates', authorize('user'), 'getDatePercentiles');
$app->get('/percentiles/amounts', authorize('user'), 'getAmountPercentiles');
$app->get('/percentiles/batchsummary/:id', authorize('user'), 'getBatchSummaryPercentiles');
$app->get('/percentiles/date/set', authorize('user'), 'setDatePercentiles');
$app->get('/percentiles/zips', authorize('user'), 'getZips');
//$app->get('/percentiles/amount/set', authorize('user'), 'setInvoicedPercentiles');
$app->post('/zip/amount/set', authorize('user'), 'setInvoicedPercentilesByZip');
$app->post('/percentiles/zips/filtered', authorize('user'), 'getZipsFiltered');
$app->post('/percentiles/debtors', authorize('user'), 'getBatchDebtors');
$app->post('/percentiles/zips/specified', authorize('user'), 'getSpecificZip');

function getDatePercentiles() {
	session_write_close();
	
	$sql = "
	SELECT distinct date_histogram FROM tbl_customer
	WHERE customer_id = " . $_SESSION["user_customer_id"];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		$db = null;
		
		$arrInfo = json_decode($customer->date_histogram);
		//die(print_r($arrInfo));
		$arrLeftAxis = array();
		$arrRightAxis = array();
		$arrXValues = array();
		$min_amount = -1;
		$max_amount = -1;
		
		$min_invoice_date = "";
		$max_invoice_date = "";
		if (is_array($arrInfo)) {
			foreach($arrInfo as $date_histogram) {
				$arrXValues[] = $date_histogram->min_amount;
				$arrLeftAxis[] = json_encode(array("amount"=>$date_histogram->debtor_count, "min_amount"=>$date_histogram->min_amount, "max_amount"=>$date_histogram->max_amount));
				
				//die(print_r($date_histogram));
				$arrRightAxis[]  = json_encode(array("amount"=>$date_histogram->avg_amount, "std_dev_amount"=>$date_histogram->std_dev_amount));
				//keep track
				if ($min_amount < 0) {
					$min_amount = $date_histogram->min_amount;
				}
				if ($min_amount > $date_histogram->min_amount) {
					$min_amount = $date_histogram->min_amount;
				}
				if ($max_amount < 0) {
					$max_amount = $date_histogram->max_amount;
				}
				if ($max_amount < $date_histogram->max_amount) {
					$max_amount = $date_histogram->max_amount;
				}
				
				if ($date_histogram->min_invoice_date=="0000-00-00") {
					$date_histogram->min_invoice_date = "";
				}
				if ($min_invoice_date == "") {
					$min_invoice_date = $date_histogram->min_invoice_date;
				}
				if ($date_histogram->min_invoice_date != "") {
					if (strtotime($min_invoice_date) > strtotime($date_histogram->min_invoice_date)) {
						$min_invoice_date = $date_histogram->min_invoice_date;
					}
				}
				if ($date_histogram->max_invoice_date=="0000-00-00") {
					$date_histogram->max_invoice_date = "";
				}
				if ($max_invoice_date == "") {
					$max_invoice_date = $date_histogram->max_invoice_date;
				}
				if ($date_histogram->max_invoice_date != "") {
					if (strtotime($max_invoice_date) < strtotime($date_histogram->max_invoice_date)) {
						$max_invoice_date = $date_histogram->max_invoice_date;
					}
				}
			}
		}
		$x_axis = json_encode($arrXValues);
		$left_axis = json_encode($arrLeftAxis);
		$right_axis = json_encode($arrRightAxis);

		die(json_encode(array("xaxis"=>$x_axis, "left_axis"=>$left_axis, "right_axis"=>$right_axis, "min_invoice_date"=>$min_invoice_date, "max_invoice_date"=>$max_invoice_date, "min_amount"=>$min_amount, "max_amount"=>$max_amount)));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAmountPercentiles() {
	session_write_close();
	
	setInvoicedPercentiles(false);
	
	$sql = "
	SELECT distinct amount_histogram FROM tbl_customer
	WHERE customer_id = " . $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		$db = null;
		
		//die(print_r(json_decode($customer->amount_histogram)));
		$arrInfo = json_decode($customer->amount_histogram);
		$arrLeftAxis = array();
		$arrRightAxis = array();
		$arrXValues = array();
		$min_amount = -1;
		$max_amount = -1;
		
		$min_invoice_date = "";
		$max_invoice_date = "";
		if (is_array($arrInfo)) {
			foreach($arrInfo as $amount_histogram) {
				$arrXValues[] = $amount_histogram->min_amount;
				$arrLeftAxis[] = json_encode(array("amount"=>$amount_histogram->debtor_count, "min_amount"=>$amount_histogram->min_amount, "max_amount"=>$amount_histogram->max_amount));
				
				//die(print_r($amount_histogram));
				$arrRightAxis[]  = json_encode(array("amount"=>$amount_histogram->avg_amount, "std_dev_amount"=>$amount_histogram->std_dev_amount));
				//keep track
				if ($min_amount < 0) {
					$min_amount = $amount_histogram->min_amount;
				}
				if ($min_amount > $amount_histogram->min_amount) {
					$min_amount = $amount_histogram->min_amount;
				}
				if ($max_amount < 0) {
					$max_amount = $amount_histogram->max_amount;
				}
				if ($max_amount < $amount_histogram->max_amount) {
					$max_amount = $amount_histogram->max_amount;
				}
				
				if ($amount_histogram->min_invoice_date=="0000-00-00") {
					$amount_histogram->min_invoice_date = "";
				}
				if ($min_invoice_date == "") {
					$min_invoice_date = $amount_histogram->min_invoice_date;
				}
				if ($amount_histogram->min_invoice_date != "") {
					if (strtotime($min_invoice_date) > strtotime($amount_histogram->min_invoice_date)) {
						$min_invoice_date = $amount_histogram->min_invoice_date;
					}
				}
				if ($amount_histogram->max_invoice_date=="0000-00-00") {
					$amount_histogram->max_invoice_date = "";
				}
				if ($max_invoice_date == "") {
					$max_invoice_date = $amount_histogram->max_invoice_date;
				}
				if ($amount_histogram->max_invoice_date != "") {
					if (strtotime($max_invoice_date) < strtotime($amount_histogram->max_invoice_date)) {
						$max_invoice_date = $amount_histogram->max_invoice_date;
					}
				}
			}
		}
		$x_axis = json_encode($arrXValues);
		$left_axis = json_encode($arrLeftAxis);
		$right_axis = json_encode($arrRightAxis);
		
		//$y_axis = str_replace('"', '', $y_axis);
		
		die(json_encode(array("xaxis"=>$x_axis, "left_axis"=>$left_axis, "right_axis"=>$right_axis, "min_invoice_date"=>$min_invoice_date, "max_invoice_date"=>$max_invoice_date, "min_amount"=>$min_amount, "max_amount"=>$max_amount)));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchSummaryPercentiles($id) {
    session_write_close();
    if($id > 0){
        $join = "INNER";
    } else {
        $join = "LEFT OUTER";
    }
    $sql = "SELECT tbd.`drop_methods`, SUM(tbdr.`verified`) `verified`, SUM(tbdr.`attempts`) `attempts`, SUM(tbdr.`unsubscribed`) `unsubscribed`, 
                    SUM(tbdr.`invalid`) `invalid`, SUM(tbdr.`payment`) `payments`, SUM(tbdr.`planned`) `planned` 
            FROM `tbl_batch_drop_report` tbdr 
            LEFT OUTER JOIN `tbl_batch_drop` tbd 
            ON tbdr.`batch_drop_id` = tbd.`batch_drop_id`
            ";
            // the extra enter is above and below is to help when the querry is printed it is legiable.
    if($id > 0){
        $sql .= $join . " JOIN `tbl_batch` tb 
            ON tbdr.`batch_uuid` = tb.`batch_uuid`
            AND tb.`batch_id` = :id
            ";
    }
    
    $sql .= "WHERE 1 
            AND tbdr.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tbdr.`deleted` = 'N'
            GROUP BY tbd.`drop_methods`";
    // die($sql);
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
		$stmt->execute();
		$batch_summary = $stmt->fetchAll(PDO::FETCH_OBJ);
        // die(print_r($batch_summary));
        $count = count($batch_summary);
        // die($count);
        $total = 0;
        for ($i=0; $i < $count; $i++) { 
            $attempts_count = $batch_summary[$i]->attempts;
            // echo $attempts_count;
            // echo ($total + intval($attempts_count));
            $total = $total + intval($attempts_count);
            // die($total);
        }
        
        // echo $total;
        $db = null;
        die(json_encode(array("batch_summary"=>$batch_summary, "total"=>$total)));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function setInvoicedPercentiles($blnNeedEcho = true) {
	
	session_write_close();
	
	$sql = "
	SELECT SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) percentile, MIN(curr) min_amount, MAX(curr) max_amount, 
	AVG(curr) avg_amount, STD(curr) std_dev_amount,
	MIN(`invoice_date`) min_invoice_date, MAX(`invoice_date`) max_invoice_date,
	COUNT(debtor_id) debtor_count
FROM
(
	SELECT
		debtor_id,
		`invoice_date`,
		@prev := @curr as prev,
		@curr := invoiced as curr,
		@rank := IF(@prev > @curr, @rank+@ties, @rank) AS rank,
		@ties := IF(@prev = @curr, @ties+1, 1) AS ties,
		(1-@rank/@total) as percentrank
	FROM
		tbl_debtor,
		(SELECT
			@curr := null,
			@prev := null,
			@rank := 0,
			@ties := 1,
			@total := count(*) from tbl_debtor where invoiced is not null
		) b
	WHERE 1
	AND tbl_debtor.customer_id = " . $_SESSION["user_customer_id"] . "
	AND tbl_debtor.deleted = 'N'
	AND tbl_debtor.invoiced > 0
	AND tbl_debtor.debtor_id NOT IN (
		SELECT DISTINCT td.debtor_id 
		FROM `tbl_batch_debtor` tbd 
		INNER JOIN tbl_debtor td
		ON tbd.debtor_uuid = td.debtor_uuid
		WHERE (tbd.attribute = 'locked' OR tbd.attribute = 'launched') 
		AND tbd.deleted = 'N'
		AND td.invoiced > 0
	)
	AND invoiced is not null
	ORDER BY invoiced DESC
) percentiles
WHERE SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) != ''
GROUP BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)
ORDER BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$percentiles = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$sql = "UPDATE tbl_customer 
		SET amount_histogram = '" . addslashes(json_encode($percentiles)) . "'
		WHERE customer_id = 1";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$db = null;
		if($blnNeedEcho){
			echo json_encode($percentiles);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function setInvoicedPercentilesByZip() {
	session_write_close();
	$zips = passed_var("zips", "post");
	
	$sql = "
	SELECT SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) percentile, MIN(curr) min_amount, MAX(curr) max_amount, 
	AVG(curr) avg_amount, STD(curr) std_dev_amount,
	MIN(`invoice_date`) min_invoice_date, MAX(`invoice_date`) max_invoice_date,
	COUNT(debtor_id) debtor_count
FROM
(
	SELECT
		debtor_id,
		`invoice_date`,
		@prev := @curr as prev,
		@curr := invoiced as curr,
		@rank := IF(@prev > @curr, @rank+@ties, @rank) AS rank,
		@ties := IF(@prev = @curr, @ties+1, 1) AS ties,
		(1-@rank/@total) as percentrank
	FROM
		tbl_debtor,
		(SELECT
			@curr := null,
			@prev := null,
			@rank := 0,
			@ties := 1,
			@total := count(*) from tbl_debtor where invoiced is not null
		) b
WHERE
    `invoiced` is not null
	AND `invoiced` > 0
    ORDER BY invoiced DESC
) percentiles
WHERE SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) != ''
GROUP BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)
ORDER BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$percentiles = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$sql = "SELECT count(zip_id) zip_count FROM tbl_zip WHERE `zip` = '" . $zips . "'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$ziplookup = $stmt->fetchObject();
		
		if ($ziplookup->zip_count > 0) {
			$sql = "UPDATE tbl_zip 
			SET amount_histogram = '" . addslashes(json_encode($percentiles)) . "'
			WHERE customer_id = 1";
		} else {
			$sql = "INSERT INTO tbl_zip (customer_id, zip, amount_histogram, date_histogram)
			VALUES ('1', '". $zips . "', '" . addslashes(json_encode($percentiles)) . "', '')";
		}
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$db = null;
		
		echo json_encode($percentiles);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function setDatePercentiles() {
	session_write_close();
	
	$sql = "
	SELECT SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) percentile, MIN(`invoiced`) min_amount, MAX(`invoiced`) max_amount, 
	AVG(`invoiced`) avg_amount, STD(`invoiced`) std_dev_amount,
	MIN(`invoice_date`) min_invoice_date, MAX(`invoice_date`) max_invoice_date,
	COUNT(debtor_id) debtor_count
FROM
(

SELECT
    debtor_id,
	`invoiced`,
    `invoice_date`,
    @prev := @curr as prev,
    @curr := UNIX_TIMESTAMP(invoice_date) as curr,
    @rank := IF(@prev > @curr, @rank+@ties, @rank) AS rank,
    @ties := IF(@prev = @curr, @ties+1, 1) AS ties,
    (1-@rank/@total) as percentrank
FROM
    tbl_debtor,
    (SELECT
        @curr := null,
        @prev := null,
        @rank := 0,
        @ties := 1,
        @total := count(*) from tbl_debtor where invoiced is not null
    ) b
    WHERE UNIX_TIMESTAMP(invoice_date) > 0
	AND tbl_debtor.invoiced > 0
	ORDER BY UNIX_TIMESTAMP(invoice_date) DESC
) percentiles
WHERE SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1) != ''
GROUP BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)
ORDER BY SUBSTRING(CAST(percentrank AS CHAR(6)), 3, 1)
";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$percentiles = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$sql = "UPDATE tbl_customer 
		SET date_histogram = '" . addslashes(json_encode($percentiles)) . "'
		WHERE customer_id = 1";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$db = null;
		
		echo json_encode($percentiles);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getZips() {
	session_write_close();
	$sql = "SELECT MIN(debtor_id) id, zip, COUNT(debtor_id) debtors, AVG(`invoiced`) due, 
	MIN(`invoiced`) min_due, MAX(`invoiced`) max_due
	FROM tbl_debtor 
	LEFT OUTER JOIN `tbl_batch_debtor` tbd
	ON tbl_debtor.debtor_uuid = tbd.debtor_uuid AND tbd.deleted = 'N'
	AND (attribute = 'locked' OR attribute = 'launched')
	LEFT OUTER JOIN `tbl_batch` tb
	ON tbd.batch_uuid = tb.batch_uuid
	
	WHERE tbl_debtor.customer_id = 1
	AND tbl_debtor.deleted = 'N'
	AND tbl_debtor.invoiced > 0
	AND tbd.debtor_uuid IS NULL
	
	GROUP BY zip
	ORDER BY zip";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$zips = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($zips);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getZipsFiltered() {
	session_write_close();
    // die(print_r($_POST));
	$batch_id = passed_var("batch_id", "post");
	$filters = passed_var("originalfilters", "post");	
	$allany = passed_var("allany", "post");
	if ($allany=="all") {
		$allany = " AND ";
	} else {
		$allany = " OR ";
	}
	
	$invoicedamount = passed_var("invoicedamount", "post");
	$arrInvoicedAmounts = array();
	if($invoicedamount == "~" || $invoicedamount == "0~0" || $invoicedamount == "~0" || $invoicedamount == "0~"){
		$invoicedamount = "";
	}
	$arrInvoicedAmounts = array();
	if($invoicedamount != ""){
		$arrInvoicedRanges = explode("|", $invoicedamount);
	
		foreach($arrInvoicedRanges as $invoiced_range) {
			$arrRange = explode("~", $invoiced_range);
			$arrRange[0] = str_replace("$", "", $arrRange[0]);
			$arrRange[1] = str_replace("$", "", $arrRange[1]);
			//$arrInvoicedAmounts[] = "(`invoiced` >= " . $arrRange[0] . " AND `invoiced` < " . $arrRange[1] . ")";
			
			$arrThisFilter = array();
			if ($arrRange[0]!="") {
				$arrThisFilter[] = "`invoiced` >= " . $arrRange[0];
			}
			if ($arrRange[1]!="") {
				$arrThisFilter[] = " `invoiced` <= " . $arrRange[1];
			}
			if (count($arrThisFilter) > 0) {
				$arrInvoicedAmounts[] = implode(" AND ", $arrThisFilter);
			}
		}
	} else {
		$arrInvoicedAmounts[] = "`invoiced` > 0";
	}
	
	$invoiceddate = passed_var("invoiceddate", "post");
	
	if($invoiceddate == "__/__/____~__/__/____" || $invoiceddate == "~"){
		$invoiceddate = "";
	}	
	$arrInvoicedDates = array();	
	if($invoiceddate != ""){
		$arrInvoicedDateRanges = explode("|", $invoiceddate);
		foreach($arrInvoicedDateRanges as $invoiced_date_range) {
				$arrDateRange = explode("~", $invoiced_date_range);
				
				if ($arrDateRange[0]!="" && $arrDateRange[1]!="") {
					$arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "' AND `invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
				}
				if ($arrDateRange[0]!="" && $arrDateRange[1]=="") {
					$arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "')";
				}
				if ($arrDateRange[0]=="" && $arrDateRange[1]!="") {
					$arrInvoicedDates[] = "(`invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
				}
		}
	}
	
	$arrFilters = explode("|", $filters);
	
	$arrDebtorFilter = array();
	$arrZipFilter = array();
	foreach($arrFilters as $filter) {
		$arrFilter = explode("~", $filter);
		$fieldname = $arrFilter[0];
		$value = $arrFilter[1];
		
		if ($value=="") {
			continue;
		}
		$operator = " = ";
		$suffix = "";
		$blnFiltered = false;
		switch($fieldname) {
			case "invoiced":
				//break up the value into min/max
				$invoiced = str_replace("$", "", $value);
				$arrInvoiced = explode(" - ", $invoiced);
				$arrInvoiced[0] = str_replace("$", "", $arrInvoiced[0]);
				$arrInvoiced[1] = str_replace("$", "", $arrInvoiced[1]);
				
				$arrThisFilter = array();
				if ($arrInvoiced[0]!="") {
					$arrThisFilter[] = "`invoiced` >= " . $arrInvoiced[0];
				}
				if ($arrInvoiced[1]!="") {
					$arrThisFilter[] = " `invoiced` <= " . $arrInvoiced[1];
				}
				if (count($arrThisFilter) > 0) {
					$arrDebtorFilter[] = implode(" AND ", $arrThisFilter);
				}
				//$arrDebtorFilter[] = "`invoiced` >= " . $arrInvoiced[0] . " AND `invoiced` <= " . $arrInvoiced[1];
				$blnFiltered = true;
				break;
			case "from_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " >= ";
				break;
			case "to_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " <= ";
				break;
			case "state":
				//break up states by comma
				$arrStates = explode(",", $value);
				foreach($arrStates as $state_index=>$us_state) {
					$us_state = "'" . trim($us_state) . "'";
					$arrStates[$state_index] = $us_state;
				}
				$arrDebtorFilter[] = "`state` IN (" . implode(", ", $arrStates) . ")";
				$blnFiltered = true;
				break;
			case "zip":
				//break up zip, 3 ways
				$arrZips = explode(",", $value);
				foreach($arrZips as $us_zip) {
					$suffix = "";
					$us_zip = trim($us_zip);
					//partial
					if (strlen($us_zip) < 5) {
						$suffix = "%";
					}
					//dash
					$strpos = strpos($us_zip, "-");
					if ($strpos !== false) {
						//sub array
						$arrSubZip = explode("-", $us_zip);
						foreach($arrSubZip  as $sub_zip) {
							$suffix = "";
							if (strlen($sub_zip) < 5) {
								$suffix = "%";
							}
							$arrZipFilter[] = "`zip` LIKE '" . $sub_zip . $suffix . "'";
						}
					} else {
						$arrZipFilter[] = "`zip` LIKE '" . $us_zip . $suffix . "'";
					}
					$blnFiltered = true;
				}
				break;
		}
		if (!$blnFiltered) {
			$arrDebtorFilter[] = "`" . $arrFilter[0] . "`" . $operator .  "'" . $value . $suffix . "'";
		}
	}
	//die(print_r($arrZipFilter));
	
	$sql = "SELECT DISTINCT zip
	FROM 
	(SELECT td.*, 'N' `locked` 
		FROM `tbl_debtor` td
		LEFT OUTER JOIN `tbl_batch_debtor` tbd
		ON td.debtor_uuid = tbd.debtor_uuid AND tbd.deleted = 'N'
		AND (attribute = 'locked' OR attribute = 'launched')
		LEFT OUTER JOIN `tbl_batch` tb
		ON tbd.batch_uuid = tb.batch_uuid
		WHERE td.customer_id = 1
		AND td.deleted = 'N'
		AND td.invoiced > 0
		AND tbd.debtor_uuid IS NULL";
	if ($batch_id > 0) {
		$sql .= " UNION
			SELECT td.*, 'Y' `locked`
			FROM `tbl_debtor` td
			INNER JOIN `tbl_batch_debtor` tbd
			ON td.debtor_uuid = tbd.debtor_uuid AND tbd.deleted = 'N'
			INNER JOIN `tbl_batch` tb
			ON tbd.batch_uuid = tb.batch_uuid AND tb.deleted = 'N'
			WHERE td.customer_id = 1
			AND td.deleted = 'N'
			AND td.invoiced > 0
			AND tb.batch_id = " . $batch_id;
	}
	$sql .= " ) tbl_debtor
	WHERE 1 ";
	
	if (count($arrZipFilter) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrZipFilter) . ")";
	}
	
	//returnFilters(&$arrDebtorFilter, &$arrInvoicedAmounts, &$arrInvoicedDates);
	
	if (count($arrDebtorFilter) > 0) {
		$sql .= " AND (" . implode($allany, $arrDebtorFilter) . ")";
	}
	if (count($arrInvoicedAmounts) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedAmounts) . ")";
	}
	if (count($arrInvoicedDates) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedDates) . ")";
	}
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$zips = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($zips);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchDebtors() {
	session_write_close();
	// die(print_r($_POST));
	$batch_id = passed_var("batch_id", "post");
	// die($batch_id);
	$filters = passed_var("originalfilters", "post");
	
	$allany = passed_var("allany", "post");
	if ($allany=="all") {
		$allany = " AND ";
	} else {
		$allany = " OR ";
	}
	
	$invoicedamount = passed_var("invoicedamount", "post");
	$arrInvoicedAmounts = array();
	if($invoicedamount == "~" || $invoicedamount == "0~0" || $invoicedamount == "~0" || $invoicedamount == "0~"){
		$invoicedamount = "";
	}
	$arrInvoicedmount = array();
	if($invoicedamount != ""){
		$arrInvoicedRanges = explode("|", $invoicedamount);
        
		foreach($arrInvoicedRanges as $invoiced_range) {
			$arrRange = explode("~", $invoiced_range);
            // echo print_r($arrRange);
            
			$arrRange[0] = str_replace("$", "", $arrRange[0]);
			$arrRange[1] = str_replace("$", "", $arrRange[1]);
			//$arrInvoicedAmounts[] = "(`invoiced` >= " . $arrRange[0] . " AND `invoiced` < " . $arrRange[1] . ")";
			
			$arrThisFilter = array();
			if ($arrRange[0]!="") {
				$arrThisFilter[] = "`invoiced` >= " . $arrRange[0];
			}
			if ($arrRange[1]!="") {
				$arrThisFilter[] = " `invoiced` <= " . $arrRange[1];
			}
			if (count($arrThisFilter) > 0) {
				$arrInvoicedAmounts[] = implode(" AND ", $arrThisFilter);
			}
		}
	} else {
		$arrInvoicedAmounts[] = "`invoiced` > 0";
	}
    
	//die(print_r($_POST));
	$invoiceddate = passed_var("invoiceddate", "post");
	//die(print_r($invoiceddate));
	if($invoiceddate == "__/__/____~__/__/____" || $invoiceddate == "~"){                 
		$invoiceddate = "";
	}
	$arrInvoicedDates = array();
	if($invoiceddate != ""){
		$arrInvoicedDateRanges = explode("|", $invoiceddate);
		foreach($arrInvoicedDateRanges as $invoiced_date_range) {
				$arrDateRange = explode("~", $invoiced_date_range);
				//$arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "' AND `invoice_date` < '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
				
				if ($arrDateRange[0]!="" && $arrDateRange[1]!="") {
					$arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "' AND `invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
				}
				if ($arrDateRange[0]!="" && $arrDateRange[1]=="") {
					$arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "')";
				}
				if ($arrDateRange[0]=="" && $arrDateRange[1]!="") {
					$arrInvoicedDates[] = "(`invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
				}
		}
	}
	
	$arrFilters = explode("|", $filters);
	
	$arrDebtorFilter = array();
	$arrZipFilter = array();
	foreach($arrFilters as $filter) {
		$arrFilter = explode("~", $filter);
		$fieldname = $arrFilter[0];
		$value = $arrFilter[1];
		
		if ($value=="") {
			continue;
		}
		$operator = " = ";
		$suffix = "";
		$blnFiltered = false;
		switch($fieldname) {
			case "invoiced":
				//break up the value into min/max
				$invoiced = str_replace("$", "", $value);
				$arrInvoiced = explode(" - ", $invoiced);
				$arrInvoiced[0] = str_replace("$", "", $arrInvoiced[0]);
				$arrInvoiced[1] = str_replace("$", "", $arrInvoiced[1]);
				$arrThisFilter = array();
				if ($arrInvoiced[0]!="") {
					$arrThisFilter[] = "`invoiced` >= " . $arrInvoiced[0];
				}
				if ($arrInvoiced[1]!="") {
					$arrThisFilter[] = " `invoiced` <= " . $arrInvoiced[1];
				}
				if (count($arrThisFilter) > 0) {
					$arrDebtorFilter[] = implode(" AND ", $arrThisFilter);
				}
				$blnFiltered = true;
				break;
			case "from_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " >= ";
				break;
			case "to_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " <= ";
				break;
			case "state":
				//break up states by comma
				$arrStates = explode(",", $value);
				foreach($arrStates as $state_index=>$us_state) {
					$us_state = "'" . trim($us_state) . "'";
					$arrStates[$state_index] = $us_state;
				}
				$arrDebtorFilter[] = "`state` IN (" . implode(", ", $arrStates) . ")";
				$blnFiltered = true;
				break;
			case "zip":
				//break up zip, 3 ways
				$arrZips = explode(",", $value);
				foreach($arrZips as $us_zip) {
					$suffix = "";
					$us_zip = trim($us_zip);
					//partial
					if (strlen($us_zip) < 5) {
						$suffix = "%";
					}
					//dash
					$strpos = strpos($us_zip, "-");
					if ($strpos !== false) {
						//sub array
						$arrSubZip = explode("-", $us_zip);
						foreach($arrSubZip  as $sub_zip) {
							$suffix = "";
							if (strlen($sub_zip) < 5) {
								$suffix = "%";
							}
							$arrZipFilter[] = "`zip` LIKE '" . $sub_zip . $suffix . "'";
						}
					} else {
						$arrZipFilter[] = "`zip` LIKE '" . $us_zip . $suffix . "'";
					}
					$blnFiltered = true;
				}
				break;
		}
		if (!$blnFiltered) {
			$arrDebtorFilter[] = "`" . $arrFilter[0] . "`" . $operator .  "'" . $value . $suffix . "'";
		}
	}
	
	//die(print_r($arrDebtorFilter));
	
	$sql = "SELECT COUNT(DISTINCT tbl_debtor.debtor_id) debtor_count, 
	SUM(DISTINCT `invoiced`) invoiced_total
		FROM (
			SELECT td.*, 'N' `locked` 
			FROM `tbl_debtor` td
			LEFT OUTER JOIN `tbl_batch_debtor` tbd
			ON td.debtor_uuid = tbd.debtor_uuid AND tbd.deleted = 'N'
			AND attribute = 'locked' AND attribute = 'launched'
			LEFT OUTER JOIN `tbl_batch` tb
			ON tbd.batch_uuid = tb.batch_uuid
			WHERE td.customer_id = 1
			AND td.deleted = 'N'
			AND td.invoiced > 0
			AND td.invoiced > td.total_payments
			AND tbd.debtor_uuid IS NULL";
	if ($batch_id > 0) {
			$sql .= " UNION
			SELECT td.*, 'Y' `locked`
			FROM `tbl_debtor` td
			INNER JOIN `tbl_batch_debtor` tbd
			ON td.debtor_uuid = tbd.debtor_uuid
			INNER JOIN `tbl_batch` tb
			ON tbd.batch_uuid = tb.batch_uuid
			WHERE td.customer_id = 1
			AND td.deleted = 'N'
			AND td.invoiced > 0
			AND td.invoiced > td.total_payments
			AND tbd.deleted = 'N'
			AND tb.batch_id = " . $batch_id . "";
	}
	$sql .= ") tbl_debtor WHERE 1
	AND tbl_debtor.deleted = 'N'";
	//zip filter
	if (count($arrZipFilter) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrZipFilter) . ")";
	}
	if (count($arrDebtorFilter) > 0) {
		$sql .= " AND (" . implode($allany, $arrDebtorFilter) . ")";
	}
	if (count($arrInvoicedAmounts) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedAmounts) . ")";
	}
	if (count($arrInvoicedDates) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedDates) . ")";
	}
	// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("batch_id", $batch_id);
		//die(print_r($stmt));
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$batch = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSpecificZip (){
	session_write_close();
	//die(passed_var("zips", "post"));
	$zip = passed_var("zips", "post");
	//die($filters);
	$sql = "SELECT `zip`, `amount_histogram` 
	FROM tbl_zip 
	WHERE `zip` = '" . $zip . "'
	AND `amount_histogram` != ''
	AND `amount_histogram` != '[]'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$batch = $stmt->fetchObject();
		$db = null;
		
		
		if(is_object($batch)){
			echo json_encode($batch);
		} else {
			$sql1 = "SELECT MIN(curr) min_amount, MAX(curr) max_amount, 
					AVG(curr) avg_amount, STD(curr) std_dev_amount,
					MIN(`invoice_date`) min_invoice_date, MAX(`invoice_date`) max_invoice_date,
					COUNT(debtor_id) debtor_count
				FROM
				(
					SELECT
						debtor_id,
						`invoice_date`,
						@prev := @curr as prev,
						@curr := invoiced as curr,
						@rank := IF(@prev > @curr, @rank+@ties, @rank) AS rank,
						@ties := IF(@prev = @curr, @ties+1, 1) AS ties,
						(1-@rank/@total) as percentrank
					FROM
						tbl_debtor,
						(SELECT
							@curr := null,
							@prev := null,
							@rank := 0,
							@ties := 1,
							@total := count(*) from tbl_debtor where invoiced is not null
						) b
				WHERE
					zip IN ('" . $zip . "') 
					AND `invoiced` is not null
					AND `invoiced` > 0
					AND invoiced > total_payments
				    ORDER BY invoiced DESC
				) percentiles";
	
			$db = getConnection();
			$stmt = $db->prepare($sql1);
			$stmt->execute();
			$percentiles = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			$sql = "INSERT INTO tbl_zip (customer_id, zip, amount_histogram)
			VALUES ('" . $_SESSION["user_customer_id"] . "', '" . $zip . "', '" . addslashes(json_encode($percentiles)) . "')";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
					
			$sql = "SELECT `zip`, `amount_histogram` FROM tbl_zip WHERE `zip` = '" . $zip . "'";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$batch = $stmt->fetchObject();
			$db = null;
	
			echo json_encode($batch);
			
			//echo json_encode($percentiles);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//die($sql1);// . "\r\n");
	//die(print_r($filters)); //$_POST));	
}		
?>