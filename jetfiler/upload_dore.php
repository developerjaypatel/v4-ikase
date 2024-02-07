<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
include("../api/connection.php");
include("functions.php");

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v2.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	//header("location:../index.php");
	die("<script language='javascript'>parent.location.href='../index.php'</script>");
}

session_write_close();

$cus_id = $_SESSION['user_customer_id'];
$case_id = passed_var("case_id", "GET");
$injury_id = passed_var("injury_id", "GET");
$jetfile_id = passed_var("jetfile_id", "GET");

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("");
}

include("jetfile_kase.php");

$jetfile_id = $kase->jetfile_id;
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("<script language='javascript'>parent.location.href='dor_e.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}



$person_id = $kase->applicant_id;
$first = $kase->first_name;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
$adj_number = $kase->adj_number;
$case_injury_start = $kase->start_date;
$case_injury_end = $kase->end_date;

if ($case_injury_start!="0000-00-00") {
	$case_injury_start = date("m/d/Y", strtotime($case_injury_start));
	if ($case_injury_end!="0000-00-00") {
		$case_injury_start .= "-" . date("m/d/Y", strtotime($case_injury_end)) . " CT";
	}
} else {
	$case_injury_start = "";
}
/*
//get uploads
$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_case_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_case ccase
ON ccd.case_uuid = ccase.case_uuid
WHERE `type` = 'DORE' 
AND `document_filename` != ''
AND case_id = :case_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";
*/
$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_injury_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_injury cinjury
ON ccd.injury_uuid = cinjury.injury_uuid
WHERE doc.`type` = 'DORE' 
AND `document_filename` != ''
AND injury_id = :injury_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("injury_id", $injury_id);
	$stmt->bindParam("cus_id", $cus_id);
	$stmt->execute();
	$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	die($sql);
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrFiles = array();
$arrFilesID = array();
$arrExists = array();
$number_files = count($documents);
if (count($documents) > 0) {
	foreach($documents as $document) {
		$name = $document->name;
		$filepath = $document->filepath;
		if ($filepath!="") {
			$arrExists[$name] = (file_exists("../uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath));
			$arrFiles[$name] = "../uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath;
			$arrFilesID[$name] = $document->id;
		}
	}
}
//die(print_r($arrFiles));
//echo "cnLT:" . count($arrFiles);

$role = "";

//get the info
if ($jetfile_id!="") {
	$query = "SELECT dore_info, jetfile_dore_id
	FROM cse_jetfile
	WHERE jetfile_id = " . $jetfile_id;
	
	try {
		$stmt = DB::run($query);
		$dore = $stmt->fetchObject();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	if ($dor->dor_info!="") {
		$jetfile_info = json_decode($dore->dor_info);
		if (is_object($jetfile_info)) {
			//die(print_r($jetfile_info));
			if (is_object($jetfile_info->pagedore)) {
				$pagedore = $jetfile_info->pagedore;
				
				$entitlement_labor = $pagedore->entitlement_labor;
				$entitlement_temp_dis = $pagedore->entitlement_temp_dis;
				$established_mpn = $pagedore->established_mpn;
				$entitlement_dispute = $pagedore->entitlement_dispute;
				$comments = $pagedore->dor_e_statement;
				$doctors = $pagedore->dor_doctors;
			}
		}
	}
}
//minimum required
//how many required
$number_required = 1;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>DORE Uploader</title>
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='../lib/moment.min.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<body onload="init()">
<div id="feedback"></div>
<table width="980" border="0" cellpadding="3" cellspacing="0" bordercolor="#CCCCCC" align="center">
	<tr>
		<td align="left">
            <div style="width:100%">
                <div style="float:right">
                	<a href="dor_e.php?case_id=<?php echo $case_id; ?>&cus_id=<?php echo $cus_id; ?>&injury_id=<?php echo $injury_id; ?>">DOR-E</a>
				</div>
          </div>
        </td>
    </tr>
</table>
<form action="upload_file_dore.php" method="post" enctype="multipart/form-data" name="form1" target="_self" id="form1">
    <input type="hidden" name="cus_id" value="<?php echo $cus_id; ?>" />
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />
	<input type="hidden" name="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="uploads" value="2" />
    <input type="hidden" name="form" value="DORE" />
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0" style="border:#999999 1px solid;">
  <tr>
    <td align="center" valign="top"></td>
  </tr>
  <tr>
    <td align="center" valign="top" class="nav_links"> 
    	
        <table width="100%" border="0" cellspacing="0">
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px; padding-top:5px; font-family:Arial, Helvetica, sans-serif">
              <?php if ($number_files>=$number_required && isset($arrFiles["DOR E Proof Of Service"])) { ?><div style="float:right; color:red; font-weight:bold">ALL REQUIRED DOCUMENTS HAVE BEEN UPLOADED</div>
              <?php } ?>
              <strong><span style="color:#999999">Upload Documents for DORE</span></strong></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px; padding-right:20px"><hr color="#999999"/></td>
 			</tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <strong>Injured Worker Name:</strong></td>
  </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?>&nbsp;</td>
          </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <strong>Injury Date:</strong><br /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php echo $case_injury_start; ?>&nbsp;</td>
          </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <strong> All Medical Reports:</strong><br /></td>
          </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
				<?php 
				$required = "";
				if ($doctors!="") {
					$required = "";
				}
				if (isset($arrFiles["Medical Reports"])) {
					$required = "";
					 ?>
                    <div style="float:right">
	                    <a href="clearUpload('<?php echo $arrFilesID["Medical Reports"]; ?>', 1, '1')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                        <a href="<?php echo $arrFiles["Medical Reports"]; ?>" title="Click to review upload" target="_blank">Review Upload</a><br />
                    </div>
                <?php } else {
					$arrFiles["Medical Reports"] = "";
				} ?>
              <span id="holder_1">
              <input type="file" name="file_up_1" id="file_up_1" tabindex="0" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(1)" />
              </span>
              PDF only
              <span id="writtenmedical_link" style="display:none"></span>
              <input type="hidden" name="file_name_1" value="Medical Reports" />
              <input type="hidden" name="file_stored_1" value="<?php echo $arrFiles["Medical Reports"]; ?>" /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> Proof Of Service :</strong><br /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <?php 
			  $required = "required";
			  if (isset($arrFiles["DOR E Proof Of Service"])) {
			  	$required = ""; ?>
                    <div id="pos_review_holder" style="float:right">
                    	<a href="clearUpload('<?php echo $arrFilesID["DOR E Proof Of Service"]; ?>', 1, '1')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                <a href="<?php echo $arrFiles["DOR E Proof Of Service"]; ?>" title="Click to review upload" target="_blank">Review Upload</a>                    </div>
                <?php } else {
					$arrFiles["DOR E Proof Of Service"] = "";
				} ?>
              
              <span id="holder_2">
              <input type="file" name="file_up_2" id="file_up_2" tabindex="2" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(2)" />
              </span>
              PDF only
              <input type="hidden" name="file_name_2" value="DOR E Proof Of Service" />
              <input type="hidden" name="file_stored_2" id="file_stored_2" value="<?php echo $arrFiles["DOR E Proof Of Service"]; ?>" />
              <span id="writepos_holder">OR <a href="javascript:writePOS();" title="Click to generate a Proof of Service pdf">Generate POS</a></span> 		
              <span id="writtenpos_link" style="display:none"></span>
              <p>POS Description:<br /><textarea name="pos_description" id="pos_description" cols="65" rows="3">Declaration of Readiness to proceed expedited</textarea></p>
              </td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
                <input type="submit" class="submit" name="submit" id="submit" value="Upload" tabindex="4" disabled="disabled" />
                <span style="background:#CCFFFF">Please fill out all Required Fields</span>
              </td>
            </tr>
        </table>      
    </td>
  </tr>
</table>
</form>
<script language="javascript">
var writePOS = function() {
	var pdfURL = "pdf_pos_cover_mailing.php";
	
	var mysentData = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&nopublish=y&type=_app";
	//get the description
	var pos_description = document.getElementById("pos_description");
	mysentData += "&pos_description=" + encodeURI(pos_description.value);
	
	var url = pdfURL;
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"text",
		success:function (data) {
			//save the file as document
			saveDocument("DORE", data, "DOR E Proof of Service");
			
			response = data;
			
			setDisplayStyle("writepos_holder", "display", "none");
			setDisplayStyle("pos_review_holder", "display", "none");
			var writtenpos_link = document.getElementById("writtenpos_link");
			writtenpos_link.innerHTML = "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='../uploads/<?php echo $cus_id; ?>/<?php echo $case_id; ?>/jetfiler/" + response + "' title='Click to review generated POS' target='_blank'>Review Generated POS</a>&nbsp;Click the Upload Button to finish";
			var file_stored = document.getElementById("file_stored_2");
			file_stored.value = response;
			//show the link
			setDisplayStyle("writtenpos_link", "display", "");
			//done
			//alert("POS created");
			$("#file_up_2").removeClass("required");
			$("#file_up_2").css("background", "none");
			$("#file_up_2").css("border", "1px solid green");
			enableSave();
		}
	});
}
var clearUpload = function(upload_id, file_number, upload_type) {
	var clearUrl = "../api/document/delete";
	var mysentData = "id=" + upload_id;
	$.ajax({
		url:clearUrl,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="document marked as deleted") {
				setDisplayStyle("write" + upload_type + "_holder", "display", "");
				setDisplayStyle( "review_holder_" + upload_type, "display", "");
				
				var document_blurb = "";
				switch(upload_type) {
					case "1":
						document_blurb = "medical";
						break;
					case "2":
						document_blurb = "pos";
						break;
				}
				
				var writtenpos_link = document.getElementById("written" + document_blurb + "_link");
				
				var file_stored = document.getElementById("file_stored_" + file_number);
				file_stored.value = "";
				//show the link
				setDisplayStyle("review_holder_" + upload_type, "display", "none");
				//done
				writtenpos_link.style.display = ""
				writtenpos_link.innerHTML = "cleared";
				$("#file_up_" + file_number).addClass("required");
				enableSave();
				
				setTimeout(function() {
					writtenpos_link.innerHTML = "";
				}, 2500);
			}
		}
	});
}
var saveDocument = function(document_type, document_path, document_name) {
	var mysentData = "case_id=<?php echo $case_id; ?>&case_uuid=<?php echo $kase->uuid; ?>&injury_id=<?php echo $injury_id; ?>";
	mysentData += "&type=" + document_type + "&document_filename=" + document_path + "&parent_document_uuid=&document_name=" + document_name;
	mysentData += "&document_date=" + moment().format("YYYY-MM-DD HH:mm:ss") + "&document_extension=pdf&description=" + document_name + "&description_html=&thumbnail_folder=&verified=Y";
	
	var url = "../api/documents/add";
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="true") {
				if (document_name=="DOR E Proof of Service") {
					var writtenpos_link = document.getElementById("writtenpos_link");
				}
				writtenpos_link.innerHTML += "&nbsp;&#10003;";
				
				setTimeout(function() {
					var thelink = writtenpos_link.innerHTML;
					thelink = thelink.replace("&nbsp;? ", "");
					writtenpos_link.innerHTML = thelink;
				}, 2500);
			}
		}
	});
}
var setAction = function(destination, getname, getvariable) {
	var theaction = "";
	var cartform = Dom.get("cartform");
	if (destination=="upload") {
		theaction = "http://localhost/jetfile/card_form.html?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>";
	} else {
		theaction = String(destination) + ".php";
		//alert(theaction);
		//return;
		if (typeof getname != "undefined") {
			theaction += "?" + getname + "=" + getvariable + "&suid=<?php echo $suid; ?>";
		} else {
			theaction += "?suid=<?php echo $suid; ?>";
		}
	}
	if (theaction!="") {	
		cartform.action = theaction;
		cartform.submit();
	}
	return;
}
var clearUpload = function(upload_id, file_number, upload_type) {
	var clearUrl = "../api/document/delete";
	var mysentData = "id=" + upload_id;
	$.ajax({
		url:clearUrl,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="document marked as deleted") {
				setDisplayStyle("write" + upload_type + "_holder", "display", "");
				setDisplayStyle( "review_holder_" + upload_type, "display", "");
				
				var document_blurb = "";
				switch(upload_type) {
					case "1":
						document_blurb = "medical";
						break;
					case "2":
						document_blurb = "pos";
						break;
				}
				
				var writtenpos_link = document.getElementById("written" + document_blurb + "_link");
				
				var file_stored = document.getElementById("file_stored_" + file_number);
				file_stored.value = "";
				//show the link
				setDisplayStyle("review_holder_" + upload_type, "display", "none");
				//done
				writtenpos_link.style.display = ""
				writtenpos_link.innerHTML = "cleared";
				$("#file_up_" + file_number).addClass("required");
				enableSave();
				
				setTimeout(function() {
					writtenpos_link.innerHTML = "";
				}, 2500);
			}
		}
	});
}
var init = function() {
	initMask();
	
	var elements = $('.required');
	elements.on("blur", enableSave);
	elements.on("change", enableSave);
	
	$("#file_up_1").on("change", enableSave);
}
function initMask(){
	enableSave();
}
</script>
</body>
</html>
