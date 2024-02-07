<?php 
include("act_import_eams_scrape.php");
error_reporting(E_WARNING);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$continuation = $_GET["digit"];
$sql = "SELECT *
  FROM `nat_ikase`.`contact_adj_import` WHERE adj_number != '' and adj_number !='unassigned' and adj_number !='Unassigned' and adj_number !='N/A' and adj_number !='UNASSIGNED' and contact_adj_import_id >" . $continuation;
  //die($sql);
$link = mysql_connect("127.0.0.1", "root", "admin527#") or die("Cannot connect to db</br>" . mysql_error());
mysql_select_db("nat_ikase", $link);

$result = mysql_query($sql, $link);
//die($result);
$arrRows = array();
$arrMultipleADJs = array();
$numbs = mysql_num_rows($result);
for ($i = 0; $i < $numbs; $i++) {
	$contact_adj_import_id = mysql_result($result, $i, "contact_adj_import_id");
	$full_name = mysql_result($result, $i, "full_name");
	$adj_number = mysql_result($result, $i, "adj_number");
	$claim_number = mysql_result($result, $i, "claim");
	//$status = mysql_result($result, $i, "CUST_CaseStatus_094003650");
	$doi = mysql_result($result, $i, "doi");
	if ($adj_number == "") {
		continue;
	}
	if ($adj_number == "Unassigned") {
		continue;
	}
	if ($adj_number == "UNASSIGNED") {
		continue;
	}
	if ($adj_number == "ADJ8527608" ||$adj_number == "ADJ91293123") {
		continue;
	}
	if ($adj_number == "ADJ9502968ADJ9502956") {
		$adj_number = "ADJ9502968;ADJ9502956";
	}
	if ($adj_number == "; ADJ8183381") {
		$adj_number = "ADJ8183381";
	}
	if ($adj_number == "ADJ8764687ADJ:") {
		$adj_number = "ADJ8764687";
	}
	if ($adj_number == "DJ10879637") {
		$adj_number = "ADJ10879637";
	}
	if ($adj_number == "ADJ7131729,  ADJ7206319") {
		$adj_number = "ADJ7131729;ADJ7206319";
	}
	if ($adj_number == "11060626") {
		$adj_number = "ADJ11060626";
	}
	if ($adj_number == "ADj8698384") {
		$adj_number = "ADJ8698384";
	}
	if ($adj_number == "ADJ9780897;ADj8698384") {
		continue;
	}
	
	//echo $adj_number;
	/*$sql_check_spot = "SELECT adj_number FROM `ikase_nat2`.`cse_case` WHERE adj_number ='" . $adj_number . "';";
	//die($sql_check_spot . "<br/> - mysqlresult");
	$result_check_spot = mysql_query($sql_check_spot, $link);
	
	if ($result_check_spot) {
		$numbs_check_spot = mysql_num_rows($result_check_spot);
		
		//die($result_check_spot . "<br/> - mysqlresult");
		
		for ($i = 0; $i < $numbs_check_spot; $i++) {
			$adj_number_check = mysql_result($result_check_spot, $i, "adj_number");
		}
		
		if ($adj_number == $adj_number_check) {
			//die("same");
			continue;
		}
	}
	*/
	
	if (strlen($adj_number) > 15) {
		if (strpos($adj_number, " ")) {
			$adj_number = str_replace(" ; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace(" & ", ';', $adj_number);
			$adj_number = str_replace(" , ", ';', $adj_number);
			$adj_number = str_replace(", ", ';', $adj_number);
			$adj_number = str_replace(" ", ';', $adj_number);
			$adj_number = str_replace(",", ';', $adj_number);
			$adj_number = str_replace("&", ';', $adj_number);
			$adj_number = str_replace("/", ';', $adj_number);
			$adj_number = str_replace("  ", ';', $adj_number);
		}
		if (strpos($adj_number, "l")) {
			$adj_number = str_replace("l", '', $adj_number);
		}
		if (strpos($adj_number, ", ")) {
			$adj_number = str_replace(", ", ';', $adj_number);
		}
		if (strpos($adj_number, "; ")) {
			$adj_number = str_replace("; ", ';', $adj_number);
		}
		if (strpos($adj_number, ",")) {
			$adj_number = str_replace(" ; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace(" & ", ';', $adj_number);
			$adj_number = str_replace(" , ", ';', $adj_number);
			$adj_number = str_replace(", ", ';', $adj_number);
			$adj_number = str_replace(" ", ';', $adj_number);
			$adj_number = str_replace(",", ';', $adj_number);
			$adj_number = str_replace("&", ';', $adj_number);
			$adj_number = str_replace("/", ';', $adj_number);
		}
		if (strpos($adj_number, "&")) {
			$adj_number = str_replace(" ; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace(" & ", ';', $adj_number);
			$adj_number = str_replace(" , ", ';', $adj_number);
			$adj_number = str_replace(", ", ';', $adj_number);
			$adj_number = str_replace(" ", ';', $adj_number);
			$adj_number = str_replace(",", ';', $adj_number);
			$adj_number = str_replace("&", ';', $adj_number);
			$adj_number = str_replace("/", ';', $adj_number);
		}
		if (strpos($adj_number, "/")) {
			$adj_number = str_replace(" ; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace("; ", ';', $adj_number);
			$adj_number = str_replace(" & ", ';', $adj_number);
			$adj_number = str_replace(" , ", ';', $adj_number);
			$adj_number = str_replace(", ", ';', $adj_number);
			$adj_number = str_replace(" ", ';', $adj_number);
			$adj_number = str_replace(",", ';', $adj_number);
			$adj_number = str_replace("&", ';', $adj_number);
			$adj_number = str_replace("/", ';', $adj_number);
		}
		if (strpos($adj_number, "(")) {
			$adj_number = str_replace("(98;doi)", '', $adj_number);
			$adj_number = str_replace("(07;doi) ", '', $adj_number);
			//$adj_number = str_replace("; ", ';', $adj_number);
			
		}
		if (strpos($adj_number, "guzman")) {
			$adj_number = str_replace("guzman", '', $adj_number);
			//$adj_number = str_replace("(07;doi) ", '', $adj_number);
			//$adj_number = str_replace("; ", ';', $adj_number);
			
		}
		if (strpos($adj_number, ":")) {
			$adj_number = str_replace(":", ';', $adj_number);
			//$adj_number = str_replace("(07;doi) ", '', $adj_number);
			//$adj_number = str_replace("; ", ';', $adj_number);
			
		}
		//die($adj_number . " - here");
		$adj_row = $adj_number;
		
		//$adj_row
		
		//array_push($arrMultipleADJs, $adj_row);	
		//continue;
	}
	/*
	if (strpos($doi, "'")) {
		$doi = str_replace("'", ';', $doi);
	}
	*/
	//die($adj_number . " - adj");
	//echo $contact_id .'</br>';
	//die($adj_number . " - here");
	if (strpos($adj_number, ";")) {
		//die("dub");
		$arrADJS = explode(";", $adj_number);
		foreach ($arrADJS as $adj_number) {
			if (is_numeric($adj_number)) {
				//die("here less");
				//if (strpos($adj_number, "ADJ") === false) {
				$adj_number = "ADJ" . $adj_number;
				//}
			}
			echo $adj_number . "<br/> mult<br/>";
			scrapeEams($adj_number, $claim_number);	
		}		
	} else { 
		echo $adj_number . "<br/>";
		scrapeEams($adj_number, $claim_number);
	}
	//die($eams_info);
	//var_dump($eams_info_result);
	//die();
	$row = $contact_adj_import_id . ", " . $full_name . ", " . $adj_number . ", " . $doi; 
	array_push($arrRows, $row);
	/*
	$sql_insert = "INSERT INTO `nat_ikase`.`contact_adj_import` (`contact_id`, `full_name`, `adj_number`, `case_status`, `venue`, `doi`, `claim`, `contact_dob`) 
	VALUES
	('" . $contact_id . "', '" . $name . "', '" . $adj . "', '" . $status . "', '" . $venue . "', '" . $doi . "', '" . $claim . "', '" . $dob . "');";
	//die($sql_insert);
	//$link_insert = mysql_connect("127.0.0.1", "terriel", "tdm1966tdm") or die("Cannot connect to db</br>" . mysql_error());
	
	$result_insert = mysql_query($sql_insert, $link) or die(mysql_error());
	*/
}
/*$sql_count = "SELECT *
  FROM `nat_ikase`.`contact_adj_import`";
  
$result_count = mysql_query($sql_count, $link);
//die($result);
//$arrRows = array();
$numbs_count = mysql_num_rows($result_count);
 */
//echo "Done - " . $numbs_count; 
//die(print_r($arrMultipleADJs));
//die(print_r($arrRows));
?>


<html>
  <head>
  <style>
 

fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
            border-radius: 5px;
}

legend {
    display: block;
    width: 10%;
    padding: 0;
    margin-bottom: 20px;
    font-size: 28px;
    line-height: inherit;
    color: #333;
    border: 0;
    font-family: Verdana,sans-serif;
    /* border-bottom: 1px solid #e5e5e5; */
}


input[type=text], select {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  font-size: medium;
  font-family: Verdana,sans-serif;
}

input[type=submit] {
  width: 100%;
  background-color: #4CAF50;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: medium;
  font-family: Verdana,sans-serif;
}

input[type=submit]:hover {
  background-color: #45a049;
}

div {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
  font-size: medium;
  font-family: Verdana,sans-serif;
}

  </style>


  </head>
 <body>

 <fieldset class="scheduler-border">
    <legend >ACT IMPORT</legend> 
   
<div>
   <form method='post' >
   <table border=0>
   <tr>
     <td><h3>From(db): </h3></td>
     <td><input type='text' name='nextRecord' id='nextRecord'> </td> 
   </tr>
   <tr>
     <td><h3>Next Record Value: </h3></td>
     <td><input type='text' name='nextRecord' id='nextRecord'> </td> 
   </tr>
   <tr>
     <td><h3>Stop: </h3></td>
     <td><input type='text' name='stop' id='stop'> </td> 
   </tr>
   <tr>
     <td><h3>Last Imported ID: </h3></td>
     <td><span name='lastImportedID' id='lastImportedID'> --- Last Imported ID Will be Displayed Here --</span> </td> 
   </tr>
   <tr>
     <td><h3>Import Status: </h3></td>
     <td><span name='import_status' id='import_status'> 

     <!-- --- Errors will be displayed here -- -->
     <?php if (isset($resultStatus)) { ?>
        <h2> Result: <?php echo $resultStatus ?></h2>
    <?php } ?>
     </span> </td> 
   </tr>
   <tr>
     <td colspan="2"><input type='submit' name='Import' value='Import' /></td> 
   </tr>
   </table>
   </form>
   
     </div>
     </fieldset>
 </body>
</html>