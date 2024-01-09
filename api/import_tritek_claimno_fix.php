<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`; ";
	$stmt = DB::run($sql_truncate);
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	WHERE 1
	#AND mc.cpointer = '201300'
	ORDER BY mc.cpointer";
	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	foreach($cases as $key=>$case){
		
		$case_uuid = $case->case_uuid;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . "  ";
		
		//carriers
		$sql_carrier = "SELECT
		ins.inspointer, insr.`iname`, insr.`iadd1`, insr.`iadd2`, insr.`icity`, 
		insr.`ist`, insr.`izip`, insr.`itel`, insr.`iext`, insr.`ifax`, 
		insr.`iemail`, insr.`imemo`, insr.`ipointer`, insr.`recno`, 
		insr.`recno2`, insr.`recno3`, insr.`linked`, insr.`visible`, 
		insr.`visundo`, insr.`searchkey`, insr.`linkpnt`, insr.`defclient`, 
		insr.`plclient`, insr.`clientid`, insr.`billrates`, insr.`inactive`, 
		ins.iadj, ins.itel adjtel, 
		ins.iext adjext, ins.ifax adjfax, ins.iemail adjemail, ins.ipolicyno, 
		ins.iclaimno, ins.isalut
		FROM `" . $data_source . "`.`ins`
		INNER JOIN `" . $data_source . "`.`client` cli
        ON ins.inspointer = cli.ipoint
		LEFT OUTER JOIN `" . $data_source . "`.`insure` insr 
		ON ins.ipointer = insr.ipointer
		WHERE  cli.cpointer = " . $case->cpointer;
		//SOME OLD DBS DON'T HAVE THESE FIELDS
		//insr.`eamsno`, `insr`.`comppi`, 
		$stmt = $db->prepare($sql_carrier);
		//echo $sql_carrier . "\r\n\r\n";
		//die();
		$stmt->execute();
		$carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//die(print_r($carriers));
		
		foreach ($carriers as $carrier) {
			//die(print_r($carrier))
			//look up
			$sql = "SELECT corporation_uuid
			FROM `" . $data_source . "`.`" . $data_source . "_corporation`
			WHERE customer_id = " . $customer_id . "
			AND corporation_uuid = parent_corporation_uuid
			AND type = 'carrier'
			AND deleted = 'N'
			AND company_name = '" . addslashes($carrier->iname) . "'
			AND full_name = '" . addslashes($carrier->iadj) . "'";
			//die($sql);
			$stmt = DB::run($sql);
			$partie = $stmt->fetchObject();
			if (is_object($partie)) {
				$parent_table_uuid = $partie->corporation_uuid;
				
				//die($parent_table_uuid . " = " . $carrier->ipolicyno . " -- " . $carrier->iclaimno);
				//find all the carriers with this parent, and update one after the other
				$sql = "SELECT DISTINCT corp.corporation_uuid
				FROM `" . $data_source . "`.`" . $data_source . "_corporation` corp
				INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` ccorp
				ON corp.corporation_uuid = ccorp.corporation_uuid
				WHERE corp.customer_id = " . $customer_id . "
				AND corp.deleted = 'N'
				AND corp.`type` = 'carrier'
				AND ccorp.case_uuid = '" . $case_uuid . "'
				AND corp.corporation_uuid != corp.parent_corporation_uuid
				AND corp.parent_corporation_uuid = '" . $parent_table_uuid . "'";
				//echo $sql . "\r\n<br>";
				$parties = DB::select($sql);
				//die(print_r($parties));
				foreach($parties as $key=>$partie){
					//die(print_r($partie));
					if ($carrier->ipolicyno!="" || $carrier->iclaimno!="") {
						$arrAdhocSet = array();
						if ($carrier->ipolicyno!="") {
							$adhoc_uuid = uniqid("PN", false);
							$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $partie->corporation_uuid . "','policy_number','" . addslashes($carrier->ipolicyno) . "'";
						}
						if ($carrier->iclaimno!="") {
							$adhoc_uuid = uniqid("CN", false);
							$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $partie->corporation_uuid . "','claim_number','" . addslashes($carrier->iclaimno) . "'";
						}
						//die(print_r($arrAdhocSet));
						if (count($arrAdhocSet)>0) {
							//inserts
							$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
							$arrValues = array();
							foreach($arrAdhocSet as $adhoc_set) {		
								$arrValues[] = "
								(" . $adhoc_set . ", '" . $customer_id . "')"; 
							}
							$sql .= implode(",\r\n", $arrValues);
							//die($sql);
							DB::run($sql);
							//$track_adhock_id = DB::lastInsertId();
							//trackAdhoc("insert", $track_adhock_id);
						}
					}
				}
			}
		}
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));

	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("CLAIM NO import completed");
</script>
