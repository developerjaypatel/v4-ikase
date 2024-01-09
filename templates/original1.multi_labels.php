<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include("../api/connection.php");
$db = getConnection();

$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();

$blnActive = (isset($_GET["active"]));
$blnAll = (isset($_GET["all"]));
$blanks = 0;
if (isset($_GET["blanks"])) {
	$blanks = passed_var("blanks", "get");
}
$sql = "SELECT DISTINCT pers.full_name, pers.first_name, pers.last_name, pers.full_address, pers.street, pers.suite, pers.city, pers.state, pers.zip
FROM `cse_person` pers 
INNER JOIN `cse_case_person` cper
ON pers.person_uuid = cper.person_uuid AND cper.deleted = 'N'
INNER JOIN `cse_case` ccase
ON cper.case_uuid = ccase.case_uuid
WHERE pers.deleted = 'N'
AND pers.customer_id = :customer_id
AND pers.first_name != ''
AND pers.first_name NOT LIKE '%(%'
AND pers.last_name NOT LIKE '%[%'
AND pers.last_name NOT LIKE '%(%'
";
if ($blnActive) {
	$sql .= "
	AND INSTR(ccase.case_status, 'Open') > 0 
	";
} else {
	if (!$blnAll) {
		$sql .= "
		AND INSTR(ccase.case_status, 'Closed') = 0
		AND INSTR(ccase.case_status, 'Dropped') = 0
		AND INSTR(ccase.case_status, 'Sub Out') = 0
";
	}
}
$sql .= "
ORDER BY TRIM(pers.full_name)
";
//LIMIT 0, 10
//die($sql);
$stmt = $db->prepare($sql);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_OBJ);

$db = null;

//die(print_r($clients));
$arrStreets = array();
if ($blanks > 0) {
	for($int = 0; $int < $blanks; $int++) {
		$arrStreets[] = "<span class='full_name'>&nbsp;</span><br>&nbsp;<br>&nbsp;&nbsp; &nbsp; &nbsp;";
	}
}
foreach ($clients as $client) {
	$street = $client->street;
	$city = $client->city;
	$suite = $client->suite;
	$state = $client->state;
	$zip = $client->zip;
	
	//nothing
	if ($client->full_address=="" && $client->street=="" && $client->city=="" && $client->state=="" && $client->state=="") {
		continue;
	}
	//take it from the full address
	 $full_address = $client->full_address;
	 //clean up
	 $full_address = str_replace(", United States", "", $full_address);
	 
	//name
	$full_name = $client->first_name . " " . $client->last_name;
	if ($client->first_name == "" || $client->last_name=="") {
		$full_name = $client->full_name;
	}
	if (strpos($full_name, ",")!==false) {
		$arrName = explode(",", $full_name);
		$first_name = $arrName[1];
		$last_name = $arrName[0];
		
		$full_name = trim($first_name) . " " . trim($last_name);
	}
	//partial
	if ($client->street=="" && $client->city!="" && $client->state!="" && $client->state!="") {
		$street = $client->full_address;
	}
	
	if ($client->street=="" && $client->city=="" && $client->state=="" && $client->zip=="") {
		 $arrAddress = explode(", ", $full_address);
		 
		 if (count($arrAddress) > 2) {
			 //second to the last one is city
			 $city = $arrAddress[count($arrAddress) - 2];
			 
			 //break up the last to look for zip
			 $arrStateZip = explode(" ", $arrAddress[count($arrAddress) - 1]);
			 if (count($arrStateZip) == 2) {
				 $state = $arrStateZip[0];
				 $zip = $arrStateZip[1];
			 }
			 if (count($arrStateZip) == 1) {
				 $state = $arrStateZip[0];
				 $zip = "";
			 }
			
			 //now remove them both from array to get to the street
			 unset($arrAddress[count($arrAddress) - 1]);
			 unset($arrAddress[count($arrAddress) - 1]);
			 
			 $street = implode("<br>", $arrAddress);
		 }
	}
	$full_name = str_replace("-", " - ", $full_name);
	$arrStreets[] = "<span class='full_name'>" . ucwords(strtolower($full_name)) . "</span><br>" . $street . "<br>" . $city . ", " . $state . " " . $zip;
}
//die(print_r($arrStreets));

$row_counter = 0;
foreach($arrStreets as $street) {
	if (!isset($arrRow[$row_counter])) {
		$arrRow[$row_counter] = array();
	}
	$label = '<div class="label">' . $street . '</div>';
	$arrRow[$row_counter][] = $label;
	if (count($arrRow[$row_counter])==3) {
		//however, might be new page
		if (($row_counter%10)==0) {
			if ($row_counter != 0) {
				$arrRow[$row_counter][2] .='
				<div class="page-break">&nbsp;</div>';
			}
		}
		$row_counter++;
	}
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>iKase Avery Labels (5160) Report</title>
    <style>
    body {
        width: 8.5in;
        margin: 0in .1875in;
        }
    .label{
        /* Avery 5160 labels -- CSS and HTML by MM at Boulder Information Services */
        width: 2.025in; /* plus .6 inches from padding */
        height: .875in; /* plus .125 inches from padding */
        padding: .125in .3in 0;
        margin-right: .125in; /* the gutter */

        float: left;
		font-size:0.9em;

        text-align: left;
        overflow: hidden;

        outline: 1px white; /* outline doesn't occupy space like border does */
        }
    .page-break  {
        clear: left;
        display:block;
        page-break-after:always;
        }
	.full_name {
		font-size:1.1em;
		font-weight:bold;
	}
    </style>

</head>
<body ondblclick="showDotted()">
<?php if ($blanks==0) { ?>
<div id="blanks_link">
	<p>You are printing <a href="https://www.google.com/search?q=purchase+avery+labels+5160+online" title="Click to purchase labels online" target="_blank">Avery Labels (5160)</a> Address Labels :: 1" x 2-5/8" :: 30 per Sheet.</p>
	<p>If you have a partially printed sheet of labels, please <a href="javascript:enterBlanks()">click here to enter the number of blanks</a> you wish to skip.  
  </p>
  <button onClick="hideBlankLink(event)">No Thanks</button>.
</div>
<?php } ?>
<?php foreach($arrRow as $row) {
	echo $row[0];
	if (isset($row[1])){
		echo $row[1];
	}
	if (isset($row[2])){
		echo $row[2];
	}
}
?>


<script language="javascript">
var blnDotted = false;
function showDotted() {
  var cols = document.getElementsByClassName('label');
  for(i=0; i<cols.length; i++) {
	  if (!blnDotted) {
	    cols[i].style.outline = '1px dotted';
	  } else {
		  cols[i].style.outline = '1px white';
	  }
  }
  if (!blnDotted) {
	  blnDotted = true;
  } else {
	  blnDotted = false;
  }
}
function init() {
	setTimeout(function() {
		hideBlankLink();
	}, 5000);
}
function hideBlankLink(event) {
	if (typeof event != "undefined") {
		event.preventDefault();
	}
	document.getElementById("blanks_link").style.display = "none";
}
function enterBlanks() {
	var labels = prompt("Please enter the number of labels you want to skip to start", "0");

	if (labels != null) {
		document.location.href = "multi_labels.php?<?php echo $_SERVER['QUERY_STRING']; ?>&blanks=" + labels;
	}
}
setTimeout(function() {
	hideBlankLink();
}, 10000);
</script>
</body>
</html>