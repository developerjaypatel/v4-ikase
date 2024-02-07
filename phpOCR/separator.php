<?php

//The default image that gets parsed when no parameter filename is given
$filename = $image_path;
$intFound = 0;

//*******************************************************
//This is the main function. Format of the output array is $retmas[$line_number][$letter_number][$type]
//where $type is 0 for digit and 1 for relative closeness

$lines = number_lines($filename,$conf['font_file']);	

$time_start = getmicrotime();
$retmas = parse_image($filename,$conf['font_file']);
$time = getmicrotime() - $time_start;	//execution time

$crit = "IKASESCAN";
//die(print_r($retmas));
$arrSeparators = array();
foreach($retmas as $line_number=>$line) {
	//we're looking for a close match, not exact		
	$not_found = 0;
	$arrMatches = array();
	if (count($line) < 5) {
		continue;
	}
	for ($int=0;$int<count($line);$int++) {
		//die(print_r($line));
		$val = $line[$int][0];
		//echo $line_number . "/" . $int . "  --- " . $val . " - " . $line[$int][0] . "<br />";
		//is the val in the criterion
		if (strpos($crit, $val) > 0) {
			$arrMatches[] = $val;
		}
		if ($val!=substr($crit, $int, 1)) {
			$not_found++;
		}
	}
	$intFound = strlen($crit) - $not_found;
	if ($intFound > 7) {
		echo $filename . " is a separator (" . $intFound . ")<br />";
		$arrSeparators[] = $page;
		//get out of the list
		break;
	}
	//if we're here, there was no exact match in sequence
	if (count($arrMatches) < 5) {
		continue;
	}
	//echo $filename;
	//die(print_r($arrMatches));
	
	$no_match = 0;
	//first instance of first match in criterion
	$start = strpos($crit, $arrMatches[0]);
	//try something else
	for ($int=0;$int<count($arrMatches);$int++) {
		$val = $arrMatches[$int];
		if ($val!=substr($crit, ($int + $start), 1)) {
			$no_match++;
		}
	}
	$matchFound = strlen($crit) - $no_match;
	if ($matchFound > 7) {
		echo $filename . " is a separator (" . $intFound . ")<br />";
		$arrSeparators[] = $page;
		//get out of the list
		break;
	}
}

if (count($arrSeparators)>0) {
	//get separators
	$sql = "SELECT separators FROM cse_batchscan
	WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
	
	try {
		$stmt = $db->query($sql); //FIXME: it seems not all places that include this file have $db defined as the connection
		$stmt->execute();
		$batchscan = $stmt->fetchObject();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}

	//break up into array
	if ($batchscan->separators!="") {
		$arrSep = explode("|", $batchscan->separators);
	} else {
		$arrSep = array();
	}
	//die(print_r($arrSeparators));
	if (count($arrSep) > 0) {
		//add to array
		$arrSeparators = array_merge($arrSeparators, $arrSep);
		$arrSeparators = array_filter($arrSeparators, "noEmpty");
		sort($arrSeparators);
		$arrSeparators = array_unique($arrSeparators);
	}
	
	//update separators
	$sql = "UPDATE cse_batchscan
	SET separators = '" . implode("|", $arrSeparators) . "'
	WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
	try {	
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
}
?>
